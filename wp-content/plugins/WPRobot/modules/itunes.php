<?php

function wpr_itunesrequest($keyword,$num,$start,$options) {	

	$media = $options['wpr_itunes_media']; 
	$country = $options['wpr_itunes_country'];	
	$lang = $options['wpr_itunes_lang'];	
	$entity = $options['wpr_itunes_entity'];	
	$count = $num + $start;

	$keyword = urlencode($keyword);
	
	// API REQUEST
    $request = "http://ax.phobos.apple.com.edgesuite.net/WebObjects/MZStoreServices.woa/wa/wsSearch?term=$keyword&country=$country&media=$media&limit=$count&lang=$lang"; // &entity=$entity The request URL used by the API service

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
				$return["error"]["module"] = "iTunes";
				$return["error"]["reason"] = "cURL Error";
				$return["error"]["message"] = __("cURL Error Number ","wprobot").curl_errno($ch).": ".curl_error($ch);	
				return $return;
			}
		}		
		curl_close($ch);
	} else { 				
		$response = @file_get_contents($request);  // if not try an alternative....
		if (!$response) { // ... and otherwise return an Error.
			$return["error"]["module"] = "iTunes";
			$return["error"]["reason"] = "cURL Error";
			$return["error"]["message"] = __("cURL is not installed on this server!","wprobot");	
			return $return;		
		}
	}
    
	$pxml = json_decode($response);

	if ($pxml === False) {
		$return["error"]["module"] = "iTunes";
		$return["error"]["reason"] = "JSON Error";
		$return["error"]["message"] = "JSON Response could not be loaded.";	
		return $return;				
	} else {
		return $pxml;
	}
}

function wpr_itunespost($keyword,$num,$start,$optional="",$comments="") {
	global $wpdb,$wpr_table_templates;
	
	$options = unserialize(get_option("wpr_options"));
	$uid = $options['wpr_itunes_unique'];
	
	if($keyword == "") { // If keyword is empty return error
		$return["error"]["module"] = "iTunes";
		$return["error"]["reason"] = "No keyword";
		$return["error"]["message"] = __("No keyword specified.","wprobot");
		return $return;	
	}
	
	$template = $wpdb->get_var("SELECT content FROM " . $wpr_table_templates . " WHERE type = 'itunes'");
	if($template == false || empty($template)) { // If module template is empty return error
		$return["error"]["module"] = "iTunes";
		$return["error"]["reason"] = "No template";
		$return["error"]["message"] = __("Module Template does not exist or could not be loaded.","wprobot");
		return $return;	
	}		
	
	$x = 0;
	$r = 0;
	$itemcontent = array();
	$pxml = wpr_itunesrequest($keyword,$num,$start,$options); // Send API request
	if(is_array($pxml) && isset($pxml["error"])) {return $pxml;}
	if ($pxml === False) {
		$itemcontent["error"]["module"] = "iTunes";
		$itemcontent["error"]["reason"] = "Request fail";
		$itemcontent["error"]["message"] = __("API request could not be sent.","wprobot");	
		return $itemcontent;	
	} else {
		if (isset($pxml->results)) {
			foreach($pxml->results as $item) {		
				if($r >= $start) {
					$artistName = $item->artistName;					
					$collectionName = $item->collectionName;				
					$trackId = $item->trackId;	
					$collectionId = $item->collectionId;							
					$trackName = $item->trackName;
					$artistViewUrl = $item->artistViewUrl;
					$collectionViewUrl = $item->collectionViewUrl;
					$trackViewUrl = $item->trackViewUrl;
					$previewUrl = $item->previewUrl;
					$artworkUrl30 = $item->artworkUrl30;
					$artworkUrl60 = $item->artworkUrl60;
					$artworkUrl100 = $item->artworkUrl100;
					$collectionPrice = $item->collectionPrice;
					$trackPrice = $item->trackPrice;
					$currency = $item->currency;
					$primaryGenreName = $item->primaryGenreName;
					
					if($artworkUrl60 != "") {$thumbnail = '<a href="'.$collectionViewUrl.'" rel="nofollow"><img style="float:left;margin: 0 20px 10px 0;" src="'.$artworkUrl60.'" /></a>';} else {$thumbnail = '';}
					$content = $template;
					$content = wpr_random_tags($content);
					$content = str_replace("{thumbnail}", $thumbnail, $content);
					$content = str_replace("{keyword}", $keyword, $content);				
					
					$content = str_replace("{trackname}", $trackName, $content);
					$content = str_replace("{collectionname}", $collectionName, $content);
					$content = str_replace("{artistname}", $artistName, $content);
					$content = str_replace("{artworkUrl30}", $artworkUrl30, $content);
					$content = str_replace("{artworkUrl60}", $artworkUrl60, $content);
					$content = str_replace("{artworkUrl100}", $artworkUrl100, $content);
					$content = str_replace("{collectionPrice}", $collectionPrice, $content);						
					$content = str_replace("{trackPrice}", $trackPrice, $content);
					$content = str_replace("{currency}", $currency, $content);
					$content = str_replace("{genre}", $primaryGenreName, $content);
					$content = str_replace("{artisturl}", $artistViewUrl, $content);
					$content = str_replace("{collectionurl}", $collectionViewUrl, $content);
					$content = str_replace("{trackurl}", $trackViewUrl, $content);
					$content = str_replace("{previewurl}", $previewUrl, $content);
					if(function_exists("wpr_translate_partial")) {
						$content = wpr_translate_partial($content);
					}
					if(function_exists("wpr_rewrite_partial")) {
						$content = wpr_rewrite_partial($content,$options);
					}
					
					if($uid == "track") {$uid2 = $trackId;} else {$uid2 = $collectionId;}
					
					$itemcontent[$x]["unique"] = $uid2;
					$itemcontent[$x]["title"] = $artistName ." - ".$trackName;
					$itemcontent[$x]["content"] = $content;	
					$x++;
				}
				$r++;
			}
			
			if(empty($itemcontent)) { // Return error if no content has been found.
				$itemcontent["error"]["module"] = "iTunes";
				$itemcontent["error"]["reason"] = "No content";
				$itemcontent["error"]["message"] = __("No (more) Yahoo news items found.","wprobot");	
				return $itemcontent;		
			} else {
				return $itemcontent;	
			}			
		} else {
			if (isset($pxml->Message)) { // Check for API error messages in results and if found return them.
				$message = __('There was a problem with your API request. This is the error returned:',"wprobot").' <b>'.$pxml->Message.'</b>';	
				$itemcontent["error"]["module"] = "iTunes";
				$itemcontent["error"]["reason"] = "API fail";
				$itemcontent["error"]["message"] = $message;	
				return $itemcontent;			
			} else { // Return error if no content has been found.
				$itemcontent["error"]["module"] = "iTunes";
				$itemcontent["error"]["reason"] = "No content";
				$itemcontent["error"]["message"] = __("No (more) iTunes items found.","wprobot");	
				return $itemcontent;				
			}			
		}
	}	
}

function wpr_itunes_options_default() { // default options ARRAY for this module
	$options = array(
		"wpr_itunes_media" => "all",
		"wpr_itunes_lang" => "en_us",
		"wpr_itunes_country" => "us",
		"wpr_itunes_unique" => "track",
		"wpr_itunes_entity" => "all"
	);
	return $options;
}	
function wpr_itunes_options($options) {
	?>
	<h3 style="text-transform:uppercase;border-bottom: 1px solid #ccc;"><?php _e("iTunes Options","wprobot") ?></h3>
		<table class="addt" width="100%" cellspacing="2" cellpadding="5" class="editform"> 
			<tr valign="top"> 
				<td width="40%" scope="row"><?php _e("Language:","wprobot") ?></td> 
				<td>
				<select name="wpr_itunes_lang" id="wpr_itunes_lang">
					<option value="en_us" <?php if($options['wpr_itunes_lang']=="en_us"){_e('selected');}?>><?php _e("English","wprobot") ?></option>
					<option value="ja_jp" <?php if($options['wpr_itunes_lang']=="ja_jp"){_e('selected');}?>><?php _e("Japanese","wprobot") ?></option>								
				</select>
			</td> 
			</tr>				
			<tr valign="top"> 
				<td width="40%" scope="row"><?php _e("Country:","wprobot") ?></td> 
				<td>
				<select name="wpr_itunes_country" id="wpr_itunes_country">
					<option value="US" <?php if($options['wpr_itunes_country']=="us"){_e('selected');}?>><?php _e("USA","wprobot") ?></option>
					<option value="DE" <?php if($options['wpr_itunes_country']=="de"){_e('selected');}?>><?php _e("Germany","wprobot") ?></option>
					<option value="FR" <?php if($options['wpr_itunes_country']=="fr"){_e('selected');}?>><?php _e("France","wprobot") ?></option>
					<option value="CA" <?php if($options['wpr_itunes_country']=="ca"){_e('selected');}?>><?php _e("Canada","wprobot") ?></option>
					<option value="ES" <?php if($options['wpr_itunes_country']=="es"){_e('selected');}?>><?php _e("Spain","wprobot") ?></option>
					<option value="AU" <?php if($options['wpr_itunes_country']=="au"){_e('selected');}?>><?php _e("Australia","wprobot") ?></option>
					<option value="CN" <?php if($options['wpr_itunes_country']=="cn"){_e('selected');}?>><?php _e("China","wprobot") ?></option>
					<option value="JP" <?php if($options['wpr_itunes_country']=="jp"){_e('selected');}?>><?php _e("Japan","wprobot") ?></option>								
				</select>
			</td> 
			</tr>	
			<tr valign="top"> 
				<td width="40%" scope="row"><?php _e("Media Type:","wprobot") ?></td> 
				<td>
				<select name="wpr_itunes_media" id="wpr_itunes_media">
					<option value="all" <?php if($options['wpr_itunes_media']=="all"){_e('selected');}?>><?php _e("All","wprobot") ?></option>
					<option value="movie" <?php if($options['wpr_itunes_media']=="movie"){_e('selected');}?>><?php _e("Movies","wprobot") ?></option>
					<option value="podcast" <?php if($options['wpr_itunes_media']=="podcast"){_e('selected');}?>><?php _e("Podcasts","wprobot") ?></option>
					<option value="music" <?php if($options['wpr_itunes_media']=="music"){_e('selected');}?>><?php _e("Music","wprobot") ?></option>
					<option value="musicVideo" <?php if($options['wpr_itunes_media']=="musicVideo"){_e('selected');}?>><?php _e("Music Videos","wprobot") ?></option>
					<option value="audiobook" <?php if($options['wpr_itunes_media']=="audiobook"){_e('selected');}?>><?php _e("Audiobooks","wprobot") ?></option>
					<option value="shortFilm" <?php if($options['wpr_itunes_media']=="shortFilm"){_e('selected');}?>><?php _e("Short Films","wprobot") ?></option>
					<option value="tvShow" <?php if($options['wpr_itunes_media']=="tvShow"){_e('selected');}?>><?php _e("TV Shows","wprobot") ?></option>								
				</select>
			</td> 
			</tr>			
			<tr valign="top"> 
				<td width="40%" scope="row"><?php _e("Unique ID:","wprobot") ?></td> 
				<td>
				<select name="wpr_itunes_unique" id="wpr_itunes_unique">
					<option value="track" <?php if($options['wpr_itunes_unique']=="track"){_e('selected');}?>><?php _e("Tracks","wprobot") ?></option>
					<option value="collection" <?php if($options['wpr_itunes_unique']=="collection"){_e('selected');}?>><?php _e("Collections","wprobot") ?></option>								
				</select>
			</td> 
			</tr>				
		</table>		
	<?php
}

?>