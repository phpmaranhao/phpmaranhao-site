<?php

function wpr_eventfulrequest($keyword,$num,$start,$options) {	
	libxml_use_internal_errors(true);
	
	$options = unserialize(get_option("wpr_options"));	// Load WP Robot Options Array
	$appid = trim($options['wpr_eventful_appkey']); // If necessary retreive the API key from the options...
	$location = $options['wpr_eventful_location'];	// ... or other parameters to use in the API request
	$cat = $options['wpr_eventful_cat'];
	$sort_order = $options['wpr_eventful_sort'];
	
	$keyword = urlencode($keyword);
	
	// API REQUEST
    $request = "http://api.eventful.com/rest/events/search?app_key=$appid&keywords=$keyword&category=$cat&location=$location&sort_order=$sort_order&page_size=$num&page_number=$start&include=price"; // The request URL used by the API service

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
				$return["error"]["module"] = "Eventful";
				$return["error"]["reason"] = "cURL Error";
				$return["error"]["message"] = __("cURL Error Number ","wprobot").curl_errno($ch).": ".curl_error($ch);	
				return $return;
			}
		}		
		curl_close($ch);
	} else { 				
		$response = @file_get_contents($request);  // if not try an alternative....
		if (!$response) { // ... and otherwise return an Error.
			$return["error"]["module"] = "Eventful";
			$return["error"]["reason"] = "cURL Error";
			$return["error"]["message"] = __("cURL is not installed on this server!","wprobot");	
			return $return;		
		}
	}
    
	$pxml = simplexml_load_string($response);
//print_r($pxml);
	if ($pxml === False) {
		$pxml = simplexml_load_file($request); 
		if ($pxml === False) {	
			$emessage = __("Failed loading XML, errors returned: ","wprobot");
			foreach(libxml_get_errors() as $error) {
				$emessage .= $error->message . ", ";
			}	
			libxml_clear_errors();
			$return["error"]["module"] = "Eventful";
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

function wpr_eventfulpost($keyword,$num,$start,$optional="",$comments="") {
	global $wpdb,$wpr_table_templates;
	
	$options = unserialize(get_option("wpr_options"));
	
	if($keyword == "") { // If keyword is empty return error
		$return["error"]["module"] = "Eventful";
		$return["error"]["reason"] = "No keyword";
		$return["error"]["message"] = __("No keyword specified.","wprobot");
		return $return;	
	}
	
	$template = $wpdb->get_var("SELECT content FROM " . $wpr_table_templates . " WHERE type = 'eventful'");
	if($template == false || empty($template)) { // If module template is empty return error
		$return["error"]["module"] = "Eventful";
		$return["error"]["reason"] = "No template";
		$return["error"]["message"] = __("Module Template does not exist or could not be loaded.","wprobot");
		return $return;	
	}		
	
	$x = 0;
	$itemcontent = array();
	$pxml = wpr_eventfulrequest($keyword,$num,$start,$options); // Send API request
	if(!empty($pxml["error"])) {return $pxml;}
	if ($pxml === False) {
		$itemcontent["error"]["module"] = "Eventful";
		$itemcontent["error"]["reason"] = "Request fail";
		$itemcontent["error"]["message"] = __("API request could not be sent.","wprobot");	
		return $itemcontent;	
	} else {
		if (isset($pxml->events)) {
			foreach($pxml->events->event as $item) {		

				$attrs = $item->attributes();
				$uid = $attrs['id']; 			
			
				$title = $item->title;					
				$description = trim($item->description);				
				$url = $item->url;				
				$start_time = $item->start_time;
				$stop_time = $item->stop_time;
				
				$venue_name = $item->venue_name;
				$venue_url = $item->venue_url;
				$venue_address = $item->venue_address;
				
				$city_name = $item->city_name;
				$country_name = $item->country_name;
				
				$content = $template;
				$content = wpr_random_tags($content);
				$content = str_replace("{title}", $title, $content);
				$content = str_replace("{description}", $description, $content);
				$content = str_replace("{url}", $url, $content);						
				$content = str_replace("{keyword}", $keyword, $content);
				$content = str_replace("{begin}", $start_time, $content);
				$content = str_replace("{end}", $stop_time, $content);
				$content = str_replace("{venuename}", $venue_name, $content);
				$content = str_replace("{venueurl}", $venue_url, $content);
				$content = str_replace("{venueaddress}", $venue_address, $content);						
				$content = str_replace("{city}", $city_name, $content);
				$content = str_replace("{country}", $country_name, $content);
					if(function_exists("wpr_translate_partial")) {
						$content = wpr_translate_partial($content);
					}
					if(function_exists("wpr_rewrite_partial")) {
						$content = wpr_rewrite_partial($content,$options);
					}
					
				if("yes" == $options['wpr_eventful_skip'] && empty($description) && $num == 1) {
					$itemcontent["error"]["module"] = "Eventful";
					$itemcontent["error"]["reason"] = "No content";
					$itemcontent["error"]["message"] = __("Event skipped because of empty description.","wprobot");	
					return $itemcontent;					
				}
				
				$itemcontent[$x]["unique"] = $uid;
				$itemcontent[$x]["title"] = $title;
				$itemcontent[$x]["content"] = $content;	
				$x++;
			}
			
			if(empty($itemcontent)) { // Return error if no content has been found.
				$itemcontent["error"]["module"] = "Eventful";
				$itemcontent["error"]["reason"] = "No content";
				$itemcontent["error"]["message"] = __("No (more) Yahoo news items found.","wprobot");	
				return $itemcontent;		
			} else {
				return $itemcontent;	
			}			
		} else {
			if (isset($pxml->description)) { // Check for API error messages in results and if found return them.
				$message = __('There was a problem with your API request. This is the error returned:',"wprobot").' <b>'.$pxml->description.'</b>';	
				$itemcontent["error"]["module"] = "Eventful";
				$itemcontent["error"]["reason"] = "API fail";
				$itemcontent["error"]["message"] = $message;	
				return $itemcontent;			
			} else { // Return error if no content has been found.
				$itemcontent["error"]["module"] = "Eventful";
				$itemcontent["error"]["reason"] = "No content";
				$itemcontent["error"]["message"] = __("No (more) Eventful items found.","wprobot");	
				return $itemcontent;				
			}			
		}
	}	
}

function wpr_eventful_options_default() { // default options ARRAY for this module
	$options = array(
		"wpr_eventful_appkey" => "",
		"wpr_eventful_location" => "",
		"wpr_eventful_cat" => "",
		"wpr_eventful_sort" => "popularity",
		"wpr_eventful_skip" => "",
		"wpr_eventful_tos" => "no"
	);
	return $options;
}

function wpr_eventful_options($options) { // module settings for the WP Robot "Options" screen

	?>
	<h3 style="text-transform:uppercase;border-bottom: 1px solid #ccc;"><?php _e("Eventful Options","wprobot") ?></h3>			
		<table class="addt" width="100%" cellspacing="2" cellpadding="5" class="editform"> 
			<tr <?php if($options['wpr_eventful_appkey'] == "") {echo 'style="background:#F8E0E0;"';} ?> valign="top"> 
				<td width="40%" scope="row"><?php _e("Eventful API Key:","wprobot") ?></td> 
				<td><input size="40" name="wpr_eventful_appkey" type="text" id="wpr_eventful_appkey" value="<?php echo $options['wpr_eventful_appkey'] ;?>"/>
				<!--Tooltip--><a target="_blank" class="tooltip" href="http://api.eventful.com/">?<span><?php _e('This setting is required for the Eventful module to work! Click the link to get to the sign up page.',"wprobot") ?></span></a>
			</td> 
			</tr>	
			<tr valign="top"> 
				<td width="40%" scope="row"><?php _e("Location:","wprobot") ?></td> 
				<td><input size="40" name="wpr_eventful_location" type="text" id="wpr_eventful_location" value="<?php echo $options['wpr_eventful_location'] ;?>"/>
				<!--Tooltip--><a target="_blank" class="tooltip" href="#">?<span><?php _e('A location name to use in filtering the search results. Locations in the form "San Diego", "San Diego, TX", "London, United Kingdom", and "Calgary, Alberta, Canada" are accepted, as are postal codes ("92122") and venue IDs ("V0-001-000268633-5"). Full addresses ("1 Infinite Loop, Cupertino, CA") and common geocoordinate formats ("32.746682, -117.162741") are also accepted.',"wprobot") ?></span></a>
			</td> 
			</tr>
			<tr valign="top"> 
				<td width="40%" scope="row"><?php _e("Category:","wprobot") ?></td> 
				<td><input size="40" name="wpr_eventful_cat" type="text" id="wpr_eventful_cat" value="<?php echo $options['wpr_eventful_cat'] ;?>"/>
				<!--Tooltip--><a target="_blank" class="tooltip" href="http://api.evdb.com/rest/categories/list?app_key=<?php echo $options['wpr_eventful_appkey'] ;?>">?<span><?php _e('Click this link to see a list of possible values.',"wprobot") ?></span></a>
			</td> 
			</tr>				
			<tr valign="top"> 
				<td width="40%" scope="row"><?php _e("Sort by:","wprobot") ?></td> 
				<td>
				<select name="wpr_eventful_sort" id="wpr_eventful_sort">
					<option value="popularity" <?php if($options['wpr_eventful_sort']=="popularity"){_e('selected');}?>><?php _e("popularity","wprobot") ?></option>
					<option value="date" <?php if($options['wpr_eventful_sort']=="date"){_e('selected');}?>><?php _e("date","wprobot") ?></option>
					<option value="title" <?php if($options['wpr_eventful_sort']=="title"){_e('selected');}?>><?php _e("title","wprobot") ?></option>
					<option value="relevance" <?php if($options['wpr_eventful_sort']=="relevance"){_e('selected');}?>><?php _e("relevance","wprobot") ?></option>
					<option value="venue_name" <?php if($options['wpr_eventful_sort']=="venue_name"){_e('selected');}?>><?php _e("venue name","wprobot") ?></option>
				</select>
			</td> 
			</tr>	
			<!--<tr valign="top"> 
				<td width="40%" scope="row"><?php _e("Skip Events with empty description:","wprobot") ?></td> 
				<td>
				<input name="wpr_eventful_skip" type="checkbox" id="wpr_eventful_skip" value="yes" <?php if ($options['wpr_eventful_skip']=='yes') {echo "checked";} ?>/> <?php _e("Yes","wprobot") ?>		
				</td> 
			</tr>	-->			
			<tr valign="top"> 
				<td width="40%" scope="row"><?php _e('Add "Events by Eventful" link to footer?',"wprobot") ?></td> 
				<td><input name="wpr_eventful_tos" type="checkbox" id="wpr_eventful_tos" value="yes" <?php if ($options['wpr_eventful_tos']=='yes') {echo "checked";} ?>/> <?php _e("Yes","wprobot") ?>
				<!--Tooltip--><a class="tooltip" href="#">?<span><?php _e('By the Eventful API TOS it is required that you display the link \'Events by Eventful\' on pages you use the API on. If you disable this option you can add the text anywhere else on your weblog yourself.',"wprobot") ?></span></a></td> 
			</tr>				
		</table>		
	<?php
}

function wpr_eventful_showtos() {
	$options = unserialize(get_option("wpr_options"));	
	if ($options['wpr_eventful_tos'] == 'yes') {
		echo '<p><img src="http://api.eventful.com/images/powered/eventful_58x20.gif" alt="Local Events, Concerts, Tickets" /><br/><a href="http://eventful.com/">Events</a> by Eventful</p>';
	}
}
add_action('wp_footer', 'wpr_eventful_showtos'); 
?>