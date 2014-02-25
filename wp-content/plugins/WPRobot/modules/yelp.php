<?php

function wpr_yelprequest($keyword,$num,$start) {	
	libxml_use_internal_errors(true);
	
	$options = unserialize(get_option("wpr_options"));	// Load WP Robot Options Array
	$appid = $options['wpr_yelp_appkey']; // If necessary retreive the API key from the options...
	$region = $options['wpr_yelp_lang'];	// ... or other parameters to use in the API request
	$location = urlencode($options['wpr_yelp_location']);
	$radius = $options['wpr_yelp_radius'];
	
	$keyword = urlencode($keyword);
	
	// API REQUEST
    $request = "http://api.yelp.com/business_review_search?term=$keyword&location=$location&ywsid=$appid&cc=$region&radius=$radius"; // The request URL used by the API service

	if ( function_exists('curl_init') ) { // Check if cURL is installed...
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (compatible; Konqueror/4.0; Microsoft Windows) KHTML/4.0.80 (like Gecko)");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_URL, $request);
		curl_setopt($ch, CURLOPT_TIMEOUT, 60);
		$response = curl_exec($ch);
		if (!$response) { // If cURL request did not work....
			$response = @file_get_contents($request); // first try an alternative....
			if (!$response) {		// ... and otherwise return an Error.
				$return["error"]["module"] = "Yelp";
				$return["error"]["reason"] = "cURL Error";
				$return["error"]["message"] = __("cURL Error Number ","wprobot").curl_errno($ch).": ".curl_error($ch);	
				return $return;
			}
		}		
		curl_close($ch);
	} else { 				
		$response = @file_get_contents($request);  // if not try an alternative....
		if (!$response) { // ... and otherwise return an Error.
			$return["error"]["module"] = "Yelp";
			$return["error"]["reason"] = "cURL Error";
			$return["error"]["message"] = __("cURL is not installed on this server!","wprobot");	
			return $return;		
		}
	}
	
	$pxml = json_decode($response);

	if ($pxml === False) {
		$return["error"]["module"] = "Yelp";
		$return["error"]["reason"] = "JSON Error";
		$return["error"]["message"] = "JSON Response could not be loaded.";	
		return $return;				
	} else {
		return $pxml;
	}
}

function wpr_yelppost($keyword,$num,$start,$optional="",$comments="") {
	global $wpdb,$wpr_table_templates;
	
	$end = $start + $num;
	
	if($keyword == "") { // If keyword is empty return error
		$return["error"]["module"] = "Yelp";
		$return["error"]["reason"] = "No keyword";
		$return["error"]["message"] = __("No keyword specified.","wprobot");
		return $return;	
	}
	
	if($start >= 20) { // If keyword is empty return error
		$return["error"]["module"] = "Yelp";
		$return["error"]["reason"] = "No content";
		$return["error"]["message"] = __("No more content: The Yelp API does only return the first 20 results for any keyword.","wprobot");
		return $return;	
	}	
	
	$template = $wpdb->get_var("SELECT content FROM " . $wpr_table_templates . " WHERE type = 'yelp'");
	if($template == false || empty($template)) { // If module template is empty return error
		$return["error"]["module"] = "Yelp";
		$return["error"]["reason"] = "No template";
		$return["error"]["message"] = __("Module Template does not exist or could not be loaded.","wprobot");
		return $return;	
	}		
	
	$x = 0;$yy = 0;
	$itemcontent = array();
	$pxml = wpr_yelprequest($keyword,$num,$start); // Send API request
	if(is_array($pxml) && isset($pxml["error"])) {return $pxml;}
	if ($pxml === False) {
		$itemcontent["error"]["module"] = "Yelp";
		$itemcontent["error"]["reason"] = "Request fail";
		$itemcontent["error"]["message"] = __("API request could not be sent.","wprobot");	
		return $itemcontent;	
	} else {
		if (isset($pxml->businesses)) {
			foreach($pxml->businesses as $item) {			
				if($yy >= $start && $yy < $end) {
					
					$title = $item->name;								
					$url = $item->url;					
					$address1 = $item->address1;
					$address2 = $item->address2;
					$address3 = $item->address3;
					$address = $address1 . "<br/>". $address2 . "<br/>". $address3 . "<br/>"; 
					$city = $item->city;
					$phone = $item->phone;
					$photo_url = $item->photo_url;
					$avg_rating = $item->avg_rating;
					$review_count = $item->review_count;
					
					$reviews = array();					
					if (isset($item->reviews)) {
						$r = 0;
						foreach($item->reviews as $review) {
							$reviews[$r]["author"] = $review->user_name;		
							$reviews[$r]["rating"] = $review->rating;
							$rtemplate = "<i>Review by {author}</i><br/>{content}<br/><b>Rating: {rating}</b><br/><br/>";
							$rtemplate = str_replace("{content}", strip_tags($review->text_excerpt ,'<br><strong>'), $rtemplate);	
							$rtemplate = str_replace("{rating}", $review->rating, $rtemplate);				
							$rtemplate = str_replace("{author}", $review->user_name, $rtemplate);						
							$reviews[$r]["content"] = $rtemplate;
							$r++;
						}	
					}				
					
					$source = "Read more on <a rel=\"nofollow\" href=\"$url\">$source</a><br/><br/>";
					if($photo_url != "") {$thumbnail = '<a href="'.$url.'" rel="nofollow"><img style="float:left;margin: 0 20px 10px 0;" src="'.$photo_url.'" /></a>';} else {$thumbnail = '';}
					$content = $template;
					$content = wpr_random_tags($content);
					$content = str_replace("{thumbnail}", $thumbnail, $content);
					$content = str_replace("{title}", $title, $content);
					$content = str_replace("{rating}", $avg_rating, $content);
					$content = str_replace("{reviewscount}", $review_count, $content);
					$content = str_replace("{url}", $url, $content);						
					$content = str_replace("{keyword}", $keyword, $content);
					$content = str_replace("{address}", $address1, $content);
					$content = str_replace("{city}", $city, $content);
					
					// reviews
					$reviewspost = "";				
					$reviewsnum = $review_count;
					preg_match('#\{reviews(.*)\}#iU', $content, $rmatches);
					if ($rmatches[0] == false) {			
					} elseif(empty($reviews)) {
						$content = str_replace($rmatches[0],$reviewspost , $content);				
					} else {
						$reviewnum = substr($rmatches[1], 1);
						for ($i = 0; $i < $reviewnum; $i++) {
							if($i == $reviewsnum || $i == 3) {break;} else {	
								$reviewspost .= $reviews[$i]["content"];
							}
						}
						$content = str_replace($rmatches[0], $reviewspost, $content);				
					}	
					
					if(function_exists("wpr_translate_partial")) {
						$content = wpr_translate_partial($content);
					}
					if(function_exists("wpr_rewrite_partial")) {
						$content = wpr_rewrite_partial($content,$options);
					}	
					
					$itemcontent[$x]["unique"] = $item->id;
					$itemcontent[$x]["title"] = $title;
					$itemcontent[$x]["content"] = $content;	
					$x++;
				}
				$yy++;	
			}
			
			if(empty($itemcontent)) { // Return error if no content has been found.
				$itemcontent["error"]["module"] = "Yelp";
				$itemcontent["error"]["reason"] = "No content";
				$itemcontent["error"]["message"] = __("No (more) Yelp items found.","wprobot");	
				return $itemcontent;		
			} else {
				return $itemcontent;	
			}			
		} else {
			if (isset($pxml->message->text) && $pxml->message->code != 0) { // Check for API error messages in results and if found return them.
				$message = __('There was a problem with your API request. This is the error returned by Yelp:',"wprobot").' <b>'.$pxml->message->code.': '.$pxml->message->text.'</b>';	
				$itemcontent["error"]["module"] = "Yelp";
				$itemcontent["error"]["reason"] = "API fail";
				$itemcontent["error"]["message"] = $message;	
				return $itemcontent;			
			} else { // Return error if no content has been found.
				$itemcontent["error"]["module"] = "Yelp";
				$itemcontent["error"]["reason"] = "No content";
				$itemcontent["error"]["message"] = __("No (more) Yelp items found.","wprobot");	
				return $itemcontent;				
			}			
		}
	}	
}

function wpr_yelp_options_default() { // default options ARRAY for this module
	$options = array(
		"wpr_yelp_appkey" => "",
		"wpr_yelp_lang" => "US",
		"wpr_yelp_location" => "",
		"wpr_yelp_radius" => "25"
	);
	return $options;
}

function wpr_yelp_options($options) { // module settings for the WP Robot "Options" screen

	?>
	<h3 style="text-transform:uppercase;border-bottom: 1px solid #ccc;"><?php _e("Yelp Options","wprobot") ?></h3>			
		<table class="addt" width="100%" cellspacing="2" cellpadding="5" class="editform"> 
			<tr <?php if($options['wpr_yelp_appkey'] == "") {echo 'style="background:#F8E0E0;"';} ?> valign="top"> 
				<td width="40%" scope="row"><?php _e("Yelp API ID:","wprobot") ?></td> 
				<td><input size="40" name="wpr_yelp_appkey" type="text" id="wpr_yelp_appkey" value="<?php echo $options['wpr_yelp_appkey'] ;?>"/>
				<!--Tooltip--><a target="_blank" class="tooltip" href="#">?<span><?php _e('This setting is required for the Yelp module to work!',"wprobot") ?></span></a>
			</td> 
			</tr>	
			<tr <?php if($options['wpr_yelp_location'] == "") {echo 'style="background:#F8E0E0;"';} ?> valign="top"> 
				<td width="40%" scope="row"><?php _e("Location:","wprobot") ?></td> 
				<td><input size="40" name="wpr_yelp_location" type="text" id="wpr_yelp_location" value="<?php echo $options['wpr_yelp_location'] ;?>"/>
				<!--Tooltip--><a target="_blank" class="tooltip" href="#">?<span><?php _e('This setting is required for the Yelp module to work! Specifies the combination of "address, neighborhood, city, state or zip, optional country" to be used when searching for businesses. ',"wprobot") ?></span></a>
			</td> 
			</tr>				
			<tr valign="top"> 
				<td width="40%" scope="row"><?php _e("Country:","wprobot") ?></td> 
				<td>
				<select name="wpr_yelp_lang" id="wpr_yelp_lang">
					<option value="US" <?php if($options['wpr_yelp_lang']=="US"){_e('selected');}?>><?php _e("USA","wprobot") ?></option>
					<option value="GB" <?php if($options['wpr_yelp_lang']=="GB"){_e('selected');}?>><?php _e("United Kingdom","wprobot") ?></option>
					<option value="CA" <?php if($options['wpr_yelp_lang']=="CA"){_e('selected');}?>><?php _e("Canada","wprobot") ?></option>							
				</select>
			</td> 
			</tr>		
			<tr valign="top"> 
				<td width="40%" scope="row"><?php _e("Radius:","wprobot") ?></td> 
				<td><input size="40" name="wpr_yelp_radius" type="text" id="wpr_yelp_radius" value="<?php echo $options['wpr_yelp_radius'] ;?>"/>
				<!--Tooltip--><a target="_blank" class="tooltip" href="#">?<span><?php _e('Radius to use while searching around specified geo-point. Maximum value = 25. If a radius is not specified, it will be chosen based on the specificity of the location.',"wprobot") ?></span></a>
			</td> 
			</tr>				
		</table>		
	<?php
}

?>