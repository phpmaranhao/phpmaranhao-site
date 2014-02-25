<?php

// This function sends the API request and returns the answer as XML
function wpr_oodlerequest($keyword,$num,$start) {	
	libxml_use_internal_errors(true);
	
	$start = $start + 1;
	
	$options = unserialize(get_option("wpr_options"));
	$appid = $options['wpr_oodle_appkey']; 
	$region = $options['wpr_oodle_lang'];	
	$location = $options['wpr_oodle_loc'];
	$radius = $options['wpr_oodle_radius'];
	$sortby = $options['wpr_oodle_sortby'];
	$cat = $options['wpr_oodle_cat'];
	
	$keyword = urlencode($keyword);
	
	// API REQUEST
    $request = "http://api.oodle.com/api/v2/listings?key=$appid&q=$keyword&region=$region&location=$location&radius=$radius&sort=$sortby&start=$start&num=$num&category=$cat"; // The request URL used by the API service

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
				$return["error"]["module"] = "oodle";
				$return["error"]["reason"] = "cURL Error";
				$return["error"]["message"] = __("cURL Error Number ","wprobot").curl_errno($ch).": ".curl_error($ch);	
				return $return;
			}
		}		
		curl_close($ch);
	} else { 				
		$response = @file_get_contents($request);  // if not try an alternative....
		if (!$response) { // ... and otherwise return an Error.
			$return["error"]["module"] = "oodle";
			$return["error"]["reason"] = "cURL Error";
			$return["error"]["message"] = __("cURL is not installed on this server!","wprobot");	
			return $return;		
		}
	}
    
	$pxml = simplexml_load_string($response);
	
	if ($pxml === False) {
		$pxml = simplexml_load_file($request); 
		if ($pxml === False) {	
			$emessage = __("Failed loading XML, errors returned: ","wprobot");
			foreach(libxml_get_errors() as $error) {
				$emessage .= $error->message . ", ";
			}	
			libxml_clear_errors();
			$return["error"]["module"] = "oodle";
			$return["error"]["reason"] = "XML Error";
			$return["error"]["message"] = $emessage;	
			return $return;		
		} else {
			return $pxml;
		}			
	} else {
		return $pxml;
	}
}

function wpr_oodlepost($keyword,$num,$start,$optional="",$comments="") {
	global $wpdb,$wpr_table_templates;

	if($keyword == "") { // If keyword is empty return error
		$return["error"]["module"] = "oodle";
		$return["error"]["reason"] = "No keyword";
		$return["error"]["message"] = __("No keyword specified.","wprobot");
		return $return;	
	}
	
	$template = $wpdb->get_var("SELECT content FROM " . $wpr_table_templates . " WHERE type = 'oodle'");
	if($template == false || empty($template)) { // If module template is empty return error
		$return["error"]["module"] = "oodle";
		$return["error"]["reason"] = "No template";
		$return["error"]["message"] = __("Module Template does not exist or could not be loaded.","wprobot");
		return $return;	
	}		
	
	$x = 0;
	$itemcontent = array();
	$pxml = wpr_oodlerequest($keyword,$num,$start); // Send API request
	
	if(!empty($pxml["error"])) {return $pxml;}
	if ($pxml === False) {
		$itemcontent["error"]["module"] = "oodle";
		$itemcontent["error"]["reason"] = "Request fail";
		$itemcontent["error"]["message"] = __("API request could not be sent.","wprobot");	
		return $itemcontent;	
	} else {
		if (isset($pxml->listings->element)) {
			foreach($pxml->listings->element as $item) {		

				$title = $item->title;					
				$listing = $item->body;				
				$url = $item->url;		
				
				$img = $item->images->element[0]->src;				
				
				$address = $item->location->address;				
				$city = $item->location->zip . " ". $item->location->name. ", ". $item->location->country;				
				$lat = $item->location->latitude;				
				$long = $item->location->longitude;		

				$price = $item->attributes->price_display;		
				$price = str_replace("$", "$ ", $price);		
				$condition = $item->attributes->condition;		
				$features = $item->attributes->features;		
				$seller_type = $item->attributes->seller_type;		
				$delivery = $item->attributes->delivery;		
				
				if($img != "") {$thumbnail = '<a href="'.$url.'" rel="nofollow"><img style="float:left;margin: 0 20px 10px 0;" src="'.$img.'" /></a>';} else {$thumbnail = '';}
				$content = $template;
				$content = wpr_random_tags($content);
				$content = str_replace("{thumbnail}", $thumbnail, $content);
				$content = str_replace("{title}", $title, $content);
				$content = str_replace("{content}", $listing, $content);
				$content = str_replace("{url}", $url, $content);		

				$content = str_replace("{city}", $city, $content);		
				$content = str_replace("{address}", $address, $content);	
				$content = str_replace("{latitude}", $latitude, $content);	
				$content = str_replace("{longitude}", $longitude, $content);	
				
				$content = str_replace("{price}", $price, $content);		
				$content = str_replace("{condition}", $condition, $content);	
				$content = str_replace("{features}", $features, $content);	
				$content = str_replace("{seller_type}", $seller_type, $content);	
				$content = str_replace("{delivery}", $delivery, $content);	
				
				$noqkeyword = str_replace('"', '', $keyword);
				$content = str_replace("{keyword}", $noqkeyword, $content);
				$content = str_replace("{Keyword}", ucwords($noqkeyword), $content);					
					if(function_exists("wpr_translate_partial")) {
						$content = wpr_translate_partial($content);
					}
					if(function_exists("wpr_rewrite_partial")) {
						$content = wpr_rewrite_partial($content,$options);
					}
					
					$customfield = array();
					$customfield["oodletitle"] = $title;
					$customfield["oodleprice"] = $price;
					$customfield["oodlethumbnail"] = $img;						
					
				if(!empty($listing)) {
					$itemcontent[$x]["unique"] = $item->id;		
					$itemcontent[$x]["title"] = $title;
					$itemcontent[$x]["content"] = $content;	
					$itemcontent[$x]["customfield"] = $customfield;
				}
				$x++;
			}
			
			if(empty($itemcontent)) { // Return error if no content has been found.
				$itemcontent["error"]["module"] = "oodle";
				$itemcontent["error"]["reason"] = "No content";
				$itemcontent["error"]["message"] = __("No (more) oodle items found.","wprobot");	
				return $itemcontent;		
			} else {
				return $itemcontent;	
			}			
		} else {
			if (isset($pxml->Message)) { // Check for API error messages in results and if found return them.
				$message = __('There was a problem with your API request. This is the error returned:',"wprobot").' <b>'.$pxml->Message.'</b>';	
				$itemcontent["error"]["module"] = "oodle";
				$itemcontent["error"]["reason"] = "API fail";
				$itemcontent["error"]["message"] = $message;	
				return $itemcontent;			
			} else { // Return error if no content has been found.
				$itemcontent["error"]["module"] = "oodle";
				$itemcontent["error"]["reason"] = "No content";
				$itemcontent["error"]["message"] = __("No (more) oodle items found.","wprobot");	
				return $itemcontent;				
			}			
		}
	}	
}

function wpr_oodle_options_default() { // default options ARRAY for this module
	$options = array(
		"wpr_oodle_appkey" => "",
		"wpr_oodle_lang" => "usa",
		"wpr_oodle_loc" => "",
		"wpr_oodle_radius" => "30",
		"wpr_oodle_sortby" => "ctime_reverse",
		"wpr_oodle_cat" => ""
	);
	return $options;
}


function wpr_oodle_options($options) { // module settings for the WP Robot "Options" screen

	?>
	<h3 style="text-transform:uppercase;border-bottom: 1px solid #ccc;"><?php _e("Oodle Options","wprobot") ?></h3>			
		<table class="addt" width="100%" cellspacing="2" cellpadding="5" class="editform"> 
			<tr <?php if($options['wpr_oodle_appkey'] == "" && $options['wpr_yap_appkey'] == "") {echo 'style="background:#F8E0E0;"';} ?> valign="top"> 
				<td width="40%" scope="row"><?php _e("oodle API ID:","wprobot") ?></td> 
				<td><input size="40" name="wpr_oodle_appkey" type="text" id="wpr_oodle_appkey" value="<?php echo $options['wpr_oodle_appkey'];?>"/>
				<!--Tooltip--><a target="_blank" class="tooltip" href="#">?<span><?php _e('This setting is required for the oodle module to work!',"wprobot") ?></span></a>
			</td> 
			</tr>	
			<tr valign="top"> 
				<td width="40%" scope="row"><?php _e("Category:","wprobot") ?></td> 
				<td><input size="40" name="wpr_oodle_cat" type="text" id="wpr_oodle_cat" value="<?php echo $options['wpr_oodle_cat'];?>"/>
				<!--Tooltip--><a target="_blank" class="tooltip" href="http://developer.oodle.com/categories-list">?<span><?php _e('Enter a oodle category to limit your results to that category. <b>Click for details and to see all available categories!</b>',"wprobot") ?></span></a>
			</td> 
			</tr>				
			<tr valign="top"> 
				<td width="40%" scope="row"><?php _e("Country:","wprobot") ?></td> 
				<td>
				<select name="wpr_oodle_lang" id="wpr_oodle_lang">
					<option value="usa" <?php if($options['wpr_oodle_lang']=="usa"){_e('selected');}?>><?php _e("USA","wprobot"); ?></option>
					<option value="uk" <?php if($options['wpr_oodle_lang']=="uk"){_e('selected');}?>><?php _e("United Kingdom","wprobot"); ?></option>
					<option value="canada" <?php if($options['wpr_oodle_lang']=="canada"){_e('selected');}?>><?php _e("Canada","wprobot"); ?></option>
					<option value="ireland" <?php if($options['wpr_oodle_lang']=="ireland"){_e('selected');}?>><?php _e("Ireland","wprobot"); ?></option>
					<option value="india" <?php if($options['wpr_oodle_lang']=="india"){_e('selected');}?>><?php _e("India","wprobot"); ?></option>								
				</select>
			</td> 
			</tr>	
			<tr valign="top"> 
				<td width="40%" scope="row"><?php _e("Location:","wprobot") ?></td> 
				<td><input size="40" name="wpr_oodle_loc" type="text" id="wpr_oodle_loc" value="<?php echo $options['wpr_oodle_loc'] ;?>"/>
				<!--Tooltip--><a target="_blank" class="tooltip" href="http://developer.oodle.com/listings#location">?<span><?php _e('You can enter a ZIP code, city, state, location and other values. <b>Click for details and instructions!</b>',"wprobot") ?></span></a>
			</td> 
			</tr>	
			<tr valign="top"> 
				<td width="40%" scope="row"><?php _e("Radius:","wprobot") ?></td> 
				<td><input size="40" name="wpr_oodle_radius" type="text" id="wpr_oodle_radius" value="<?php echo $options['wpr_oodle_radius'] ;?>"/> <?php _e('miles',"wprobot"); ?>
				<!--Tooltip--><a target="_blank" class="tooltip" href="#">?<span><?php _e('Distance (in miles) from the specified location above for which results should be returned. Default is 30.',"wprobot") ?></span></a>
			</td> 
			</tr>				
			<tr valign="top"> 
				<td width="40%" scope="row"><?php _e("Sort by:","wprobot") ?></td> 
				<td>
				<select name="wpr_oodle_sortby" id="wpr_oodle_sortby">
					<option value="ctime" <?php if($options['wpr_oodle_sortby']=="ctime"){_e('selected');}?>><?php _e("by listing's creation time - oldest to newest","wprobot") ?></option>
					<option value="ctime_reverse" <?php if($options['wpr_oodle_sortby']=="ctime_reverse"){_e('selected');}?>><?php _e("by listing's creation time, reverse order - newest to oldest","wprobot") ?></option>
					<option value="distance" <?php if($options['wpr_oodle_sortby']=="distance"){_e('selected');}?>><?php _e("by distance from location value above - closest to farthest","wprobot") ?></option>
					<option value="distance_reverse" <?php if($options['wpr_oodle_sortby']=="distance_reverse"){_e('selected');}?>><?php _e("by distance from location parameter, reverse order - farthest to closest","wprobot") ?></option>
					<option value="eventdate" <?php if($options['wpr_oodle_sortby']=="eventdate"){_e('selected');}?>><?php _e("by date of event (e.g. tickets listings) - in chronological order","wprobot") ?></option>
					<option value="eventdate_reverse" <?php if($options['wpr_oodle_sortby']=="eventdate_reverse"){_e('selected');}?>><?php _e("by date of event - in reverse chronological order","wprobot") ?></option>
					<option value="price" <?php if($options['wpr_oodle_sortby']=="price"){_e('selected');}?>><?php _e("by listing's price - lowest to highest","wprobot") ?></option>
					<option value="price_reverse" <?php if($options['wpr_oodle_sortby']=="price_reverse"){_e('selected');}?>><?php _e("by listing's price, reverse order - highest to lowest","wprobot") ?></option>						
				</select>
			</td> 
			</tr>	
			
		</table>		
	<?php
}

?>