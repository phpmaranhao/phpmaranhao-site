<?php

// This function sends the API request and returns the answer as XML
function wpr_twitterrequest($keyword,$num,$start) {	
	libxml_use_internal_errors(true);
	
	$options = unserialize(get_option("wpr_options"));	// Load WP Robot Options Array
	$appid = $options['wpr_twitter_appkey']; // If necessary retreive the API key from the options...
	$region = $options['wpr_twitter_lang'];	// ... or other parameters to use in the API request
	$res = $options['wpr_twitter_results'];
	$users = $options['wpr_twitter_users'];
	
	if(!empty($users)) {$users = str_replace(",", "+OR+", $users);$keyword .= "+from%3A".$users;}
	
	$keyword = urlencode($keyword);
	if(empty($start)) {$start = 1;} 
	// API REQUEST
    $request = "http://search.twitter.com/search.atom?lang=$region&q=$keyword&rpp=$num&page=$start&result_type=$res"; // The request URL used by the API service
	
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
				$return["error"]["module"] = "Twitter";
				$return["error"]["reason"] = "cURL Error";
				$return["error"]["message"] = __("cURL Error Number ","wprobot").curl_errno($ch).": ".curl_error($ch);	
				return $return;
			}
		}		
		curl_close($ch);
	} else { 				
		$response = @file_get_contents($request);  // if not try an alternative....
		if (!$response) { // ... and otherwise return an Error.
			$return["error"]["module"] = "Twitter";
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
			$return["error"]["module"] = "Twitter";
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

function wpr_twitterpost($keyword,$num,$start,$optional="",$comments="") {
	global $wpdb,$wpr_table_templates;
	
	$options = unserialize(get_option("wpr_options"));
	if($keyword == "") { // If keyword is empty return error
		$return["error"]["module"] = "Twitter";
		$return["error"]["reason"] = "No keyword";
		$return["error"]["message"] = __("No keyword specified.","wprobot");
		return $return;	
	}
	
	$template = $wpdb->get_var("SELECT content FROM " . $wpr_table_templates . " WHERE type = 'twitter'");
	if($template == false || empty($template)) { // If module template is empty return error
		$return["error"]["module"] = "Twitter";
		$return["error"]["reason"] = "No template";
		$return["error"]["message"] = __("Module Template does not exist or could not be loaded.","wprobot");
		return $return;	
	}		
	
	$x = 0;
	$itemcontent = array();
	$pxml = wpr_twitterrequest($keyword,$num,$start); // Send API request
	if(!empty($pxml["error"])) {return $pxml;}
	if ($pxml === False) {
		$itemcontent["error"]["module"] = "Twitter";
		$itemcontent["error"]["reason"] = "Request fail";
		$itemcontent["error"]["message"] = __("API request could not be sent.","wprobot");	
		return $itemcontent;	
	} else {
		if (isset($pxml->entry)) {
			foreach($pxml->entry as $news) {		

				$title = $news->title;					
				$summary = $news->content;		
				if ($options['wpr_twitter_striplinks']=='yes') {$summary = wpr_strip_selected_tags($summary, array('a','iframe','script'));}	
				$authorurl = $news->author->uri;				
				$authorname = $news->author->name;
				$date = $news->published;
				
				$author = "<a rel=\"nofollow\" href=\"$authorurl\">$authorname</a><br/><br/>";
				$content = $template;
				$content = wpr_random_tags($content);
				$content = str_replace("{title}", $title, $content);
				$content = str_replace("{tweet}", $summary, $content);
				$content = str_replace("{author}", $author, $content);
				$content = str_replace("{authorname}", $authorname, $content);
				$content = str_replace("{authorurl}", $authorurl, $content);	
				$content = str_replace("{date}", $date, $content);				
				$noqkeyword = str_replace('"', '', $keyword);
				$content = str_replace("{keyword}", $noqkeyword, $content);
				$content = str_replace("{Keyword}", ucwords($noqkeyword), $content);					
					if(function_exists("wpr_translate_partial")) {
						$content = wpr_translate_partial($content);
					}
					if(function_exists("wpr_rewrite_partial")) {
						$content = wpr_rewrite_partial($content,$options);
					}
					
				$itemcontent[$x]["unique"] = $news->id;
				$itemcontent[$x]["title"] = $title;
				$itemcontent[$x]["content"] = $content;	
				$x++;
			}
			
			if(empty($itemcontent)) { // Return error if no content has been found.
				$itemcontent["error"]["module"] = "Twitter";
				$itemcontent["error"]["reason"] = "No content";
				$itemcontent["error"]["message"] = __("No (more) Twitter items found.","wprobot");	
				return $itemcontent;		
			} else {
				return $itemcontent;	
			}			
		} else {
			if (isset($pxml->Message)) { // Check for API error messages in results and if found return them.
				$message = __('There was a problem with your API request. This is the error returned:',"wprobot").' <b>'.$pxml->Message.'</b>';	
				$itemcontent["error"]["module"] = "Twitter";
				$itemcontent["error"]["reason"] = "API fail";
				$itemcontent["error"]["message"] = $message;	
				return $itemcontent;			
			} else { // Return error if no content has been found.
				$itemcontent["error"]["module"] = "Twitter";
				$itemcontent["error"]["reason"] = "No content";
				$itemcontent["error"]["message"] = __("No (more) Twitter items found.","wprobot");	
				return $itemcontent;				
			}			
		}
	}	
}

function wpr_twitter_options_default() { // default options ARRAY for this module
	$options = array(
		"wpr_twitter_appkey" => "",
		"wpr_twitter_lang" => "en",
		"wpr_twitter_striplinks" => "no",
		"wpr_twitter_results" => "recent",
		"wpr_twitter_users" => ""
	);
	return $options;
}

function wpr_twitter_options($options) { // module settings for the WP Robot "Options" screen

	?>
	<h3 style="text-transform:uppercase;border-bottom: 1px solid #ccc;"><?php _e("Twitter Options","wprobot") ?></h3>			
		<table class="addt" width="100%" cellspacing="2" cellpadding="5" class="editform"> 		
			<tr valign="top"> 
				<td width="40%" scope="row"><?php _e("Language:","wprobot") ?></td> 
				<td>
				<select name="wpr_twitter_lang" id="wpr_twitter_lang">
					<option value="en" <?php if($options['wpr_twitter_lang']=="en"){_e('selected');}?>><?php _e("English","wprobot") ?></option>
					<option value="de" <?php if($options['wpr_twitter_lang']=="de"){_e('selected');}?>><?php _e("German","wprobot") ?></option>
					<option value="fr" <?php if($options['wpr_twitter_lang']=="fr"){_e('selected');}?>><?php _e("French","wprobot") ?></option>
					<option value="it" <?php if($options['wpr_twitter_lang']=="it"){_e('selected');}?>><?php _e("Italian","wprobot") ?></option>
					<option value="es" <?php if($options['wpr_twitter_lang']=="es"){_e('selected');}?>><?php _e("Spanish","wprobot") ?></option>
					<option value="nl" <?php if($options['wpr_twitter_lang']=="nl"){_e('selected');}?>><?php _e("Dutch","wprobot") ?></option>
					<option value="cn" <?php if($options['wpr_twitter_lang']=="cn"){_e('selected');}?>><?php _e("Chinese","wprobot") ?></option>
					<option value="ja" <?php if($options['wpr_twitter_lang']=="ja"){_e('selected');}?>><?php _e("Japanese","wprobot") ?></option>								
				</select>
			</td> 
			</tr>	
			<tr valign="top"> 
				<td width="40%" scope="row"><?php _e("Result Type:","wprobot") ?></td> 
				<td>
				<select name="wpr_twitter_results" id="wpr_twitter_results">
					<option value="popular" <?php if($options['wpr_twitter_results']=="popular"){_e('selected');}?>><?php _e("Popular","wprobot") ?></option>
					<option value="recent" <?php if($options['wpr_twitter_results']=="recent"){_e('selected');}?>><?php _e("Recent","wprobot") ?></option>
					<option value="mixed" <?php if($options['wpr_twitter_results']=="mixed"){_e('selected');}?>><?php _e("Mixed","wprobot") ?></option>							
				</select>
			</td> 
			</tr>	
			<tr valign="top"> 
				<td width="40%" scope="row"><?php _e("Strip All Links from...","wprobot") ?></td> 
				<td><input name="wpr_twitter_striplinks" type="checkbox" id="wpr_twitter_striplinks" value="yes" <?php if ($options['wpr_twitter_striplinks']=='yes') {echo "checked";} ?>/> <?php _e("Tweet Content","wprobot") ?>
				</td> 
			</tr>
			<tr valign="top"> 
				<td width="40%" scope="row"><?php _e("Users:","wprobot") ?></td> 
				<td><input size="40" name="wpr_twitter_users" type="text" id="wpr_twitter_users" value="<?php echo $options['wpr_twitter_users'] ;?>"/>
				<!--Tooltip--><a class="tooltip" href="#">?<span><?php _e('Limit your results to certain Twitter users. Enter one or several Twitter usernames separated by comma.',"wprobot") ?></span></a>
			</td> 
			</tr>				
		</table>		
	<?php
}

?>