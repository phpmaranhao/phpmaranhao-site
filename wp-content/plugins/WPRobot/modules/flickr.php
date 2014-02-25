<?php

function wpr_flickrrequest($keyword,$apikey,$cont,$sort,$license,$start,$num) {
	libxml_use_internal_errors(true);
	$keyword = urlencode($keyword);
	//$keyword = '"'.$keyword.'"';
    $request = "http://api.flickr.com/services/rest/?method=flickr.photos.search&api_key=$apikey&text=$keyword&sort=$sort&content_type=$cont&license=$license&extras=date_taken%2C+owner_name%2C+icon_server%2C+geo%2C+tags%2C+machine_tags%2C+media%2C+path_alias%2C+url_sq%2C+url_t%2C+url_s%2C+url_m%2C+url_o%2C+description";
	$request .= "&per_page=$num&page=$start";

	if ( function_exists('curl_init') ) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (compatible; Konqueror/4.0; Microsoft Windows) KHTML/4.0.80 (like Gecko)");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_URL, $request);
		curl_setopt($ch, CURLOPT_TIMEOUT, 60);
		$response = curl_exec($ch);
		if (!$response) {
			$return["error"]["module"] = "Flickr";
			$return["error"]["reason"] = "cURL Error";
			$return["error"]["message"] = __("cURL Error Number ","wprobot").curl_errno($ch).": ".curl_error($ch);	
			return $return;
		}		
		curl_close($ch);
	} else { 				
		$response = @file_get_contents($request);
		if (!$response) {
			$return["error"]["module"] = "Flickr";
			$return["error"]["reason"] = "cURL Error";
			$return["error"]["message"] = __("cURL is not installed on this server!","wprobot");	
			return $return;		
		}
	}
    
	$pxml = simplexml_load_string($response);
	if ($pxml === False) {
		$emessage = __("Failed loading XML, errors returned: ","wprobot");
		foreach(libxml_get_errors() as $error) {
			$emessage .= $error->message . ", ";
		}	
		libxml_clear_errors();
		$return["error"]["module"] = "Flickr";
		$return["error"]["reason"] = "XML Error";
		$return["error"]["message"] = $emessage;	
		return $return;			
	} else {
		return $pxml;
	}
}

function wpr_fl_getcomments($photoid,$apikey) {
	$requesturl = "http://api.flickr.com/services/rest/?method=flickr.photos.comments.getList&api_key=$apikey&photo_id=$photoid";

	if ( function_exists('curl_init') ) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (compatible; Konqueror/4.0; Microsoft Windows) KHTML/4.0.80 (like Gecko)");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_URL, $requesturl);
		curl_setopt($ch, CURLOPT_TIMEOUT, 60);
		$response = curl_exec($ch);
		if (!$response) {
			echo "<div class=\"updated\"><p>cURL error:" . curl_error($ch) . " (Number " . curl_errno($ch).")</p></div>";
		}			
		curl_close($ch);
	} else { 				
		$response = @file_get_contents($requesturl);
	}

    if ($response === False) {
    } else {
        $commentsFeed = simplexml_load_string($response);
    }	
		
	$i = 0;
	$comments = array();
	if($commentsFeed->comments->comment) {
		foreach ($commentsFeed->comments->comment as $comment) {
			$attrs = $comment->attributes();
			$UserNick = $attrs['authorname']; 		
			$comments[$i]["author"] = $UserNick;
			$comments[$i]["content"] = $comment;	
			$i++;	
		}
	}
	 
	return $comments;		
}

function wpr_flickrpost($keyword,$num,$start,$optional="",$getcomments) {
	global $wpdb,$wpr_table_templates;
	
	if($keyword == "") {
		$return["error"]["module"] = "Flickr";
		$return["error"]["reason"] = "No keyword";
		$return["error"]["message"] = __("No keyword specified.","wprobot");
		return $return;	
	}	
	
	$options = unserialize(get_option("wpr_options"));	
	$width = $options["wpr_fl_width"];
	$apikey = trim($options["wpr_fl_apikey"]);		
	$cont = $options["wpr_fl_content"];
	$sort = $options["wpr_fl_sort"];
	$license = $options["wpr_fl_license"];
	
	$template = $wpdb->get_var("SELECT content FROM " . $wpr_table_templates . " WHERE type = 'flickr' AND name = 'standard'");
	if($template == false || empty($template)) {
		$return["error"]["module"] = "Flickr";
		$return["error"]["reason"] = "No template";
		$return["error"]["message"] = __("Module Template does not exist or could not be loaded.","wprobot");
		return $return;	
	}				
	$pxml = wpr_flickrrequest($keyword,$apikey,$cont,$sort,$license,$start,$num);
	if(!empty($pxml["error"])) {return $pxml;}
		//echo "<br/>---FLICKR---<br/>";print_r($pxml);echo "<br/>---FLICKR---<br/>";
	$end = $start + $num;
	//$i = 0;
	$x = 0;			
	$photos = array();
	
	if ($pxml === False) {
		$photos["error"]["module"] = "Flickr";
		$photos["error"]["reason"] = "API fail";
		$photos["error"]["message"] = __("Flickr API request did not work.","wprobot");	
		return $photos;	
	} else {
		if (isset($pxml->err)) {
			$message = '<p>'.__("There was a problem with your Flickr API request. This is the error Flickr returned:","wprobot").'</p>
			<p><i><b>'.$pxml->err['code'].':</b> '.$pxml->err['msg'].'</i></p>';	
			$photos["error"]["module"] = "Flickr";
			$photos["error"]["reason"] = "API fail";
			$photos["error"]["message"] = $message;	
			return $photos;			
		}
		if (isset($pxml->photos->photo)) {	
			foreach($pxml->photos->photo as $photo) {
				//if($i >= $start && $i <= $end) { // PROBLEM HERE ???!!!

					$title = $photo['title'];
					$date = $photo['datetaken'];	
					$owner = $photo['ownername'];
					$urlt = $photo['url_t'];
					$urls = $photo['url_s'];
					$urlm = $photo['url_m'];
					$urlo = $photo['url_o'];
					//$description = $photo['description'];	
					$description = $photo->description;	
					$ownerid = $photo['owner'];
					$photoid = $photo['id'];					
				
					if($options["wpr_fl_size"] == "small") {
						$img = '<img alt="'.$keyword.'" src="'.$urls.'" width="'.$width.'"/><br/>';
					} elseif($options["wpr_fl_size"] == "med") {
						$img = '<img alt="'.$keyword.'" src="'.$urlm.'" width="'.$width.'"/><br/>';
					} else {
						$img = '<img alt="'.$keyword.'" src="'.$urlo.'" width="'.$width.'"/><br/>';
					}
					
					$link = 'http://www.flickr.com/photos/'.$ownerid.'/'.$photoid;				
				
					$photo = $template;
					$photo = wpr_random_tags($photo);
					
					// Comments
					$commentspost = "";
					preg_match('#\{comments(.*)\}#iU', $photo, $rmatches);
					if ($rmatches[0] != false || $getcomments == 1 || $options['wpr_show_comments_settings'] != "Yes") {	
						$comments = wpr_fl_getcomments($photoid,$apikey);				
					}
					if ($rmatches[0] != false && !empty($comments)) {
						$cnum = substr($rmatches[1], 1);
						for ($i = 0; $i < count($comments); $i++) {
							if($i == $cnum) {break;} else {	
								$commentspost .= "<p><b>Comment by ".$comments[$i]["author"]."</b><br/>".$comments[$i]["content"]."</p>";
							}
						}
						$photo = str_replace($rmatches[0], $commentspost, $photo);				
					}						
					
					$photo = str_replace("{image}", $img, $photo);	
					$photo = str_replace("{date}", $date, $photo);
					$photo = str_replace("{owner}", $owner, $photo);
					$photo = str_replace("{url}", $link, $photo);
					$photo = str_replace("{keyword}", $keyword, $photo);
					$photo = str_replace("{description}", $description, $photo);	
					$photo = str_replace("{title}", $title, $photo);	
					if(function_exists("wpr_translate_partial")) {
						$photo = wpr_translate_partial($photo);
					}
					if(function_exists("wpr_rewrite_partial")) {
						$photo = wpr_rewrite_partial($photo,$options);
					}					
					$customfield = array();
					$customfield["flickrtitle"] = $title;
					$customfield["flickrimage"] = $urlm;
					$customfield["flickrowner"] = $owner;			
					
					$photos[$x]["unique"] = $photoid;
					$photos[$x]["title"] = $title;
					$photos[$x]["content"] = $photo;	
					$photos[$x]["comments"] = $comments;
					$photos[$x]["customfield"] = $customfield;
					$x++;
				//}
				//$i++;
			}
			if(empty($photos)) {
				$photos["error"]["module"] = "Flickr";
				$photos["error"]["reason"] = "No content";
				$photos["error"]["message"] = __("No (more) Flickr images found.","wprobot");	
				return $photos;	
			} else {
				return $photos;	
			}
		} else {
			$photos["error"]["module"] = "Flickr";
			$photos["error"]["reason"] = "No content";
			$photos["error"]["message"] = __("No (more) Flickr images found.","wprobot");	
			return $photos;	
		}
	}	
}

function wpr_flickr_getthumbnail($keyword) {
	global $wpdb,$wpr_table_templates;
	
	$options = unserialize(get_option("wpr_options"));	
	$width = $options["wpr_fl_twidth"];
	$apikey = $options["wpr_fl_apikey"];		
	$cont = $options["wpr_fl_content"];
	$sort = $options["wpr_fl_sort"];
	$license = $options["wpr_fl_license"];
	
	$template = $wpdb->get_var("SELECT content FROM " . $wpr_table_templates . " WHERE type = 'flickr' AND name = 'thumbnail'");
	if($template == false || empty($template)) {
		$return["error"]["module"] = "Flickr";
		$return["error"]["reason"] = "No template";
		$return["error"]["message"] = __("Module Template does not exist or could not be loaded.","wprobot");
		return $return;	
	}			
	$pxml = wpr_flickrrequest($keyword,$apikey,$cont,$sort,$license,1,100);

	$end = $start + $num;
	$i = 0;$x = 0;			
	$photos = array();
	
	if ($pxml === False) {
		$photos["error"]["module"] = "Thumbnail";
		$photos["error"]["reason"] = "API fail";
		$photos["error"]["message"] = __("Flickr API request did not work.","wprobot");	
		return $photos;	
	} else {
		if (isset($pxml->err)) {
			$message = '<p>'.__("There was a problem with your Flickr API request. This is the error Flickr returned:","wprobot").'</p>
			<p><i><b>'.$pxml->err['code'].':</b> '.$pxml->err['msg'].'</i></p>';	
			$photos["error"]["module"] = "Thumbnail";
			$photos["error"]["reason"] = "API fail";
			$photos["error"]["message"] = $message;	
			return $photos;			
		}
		if (isset($pxml->photos->photo)) {
			// COUNT RESULTS 
			$numresults = count($pxml->photos->photo);
			$randresult = rand(0,$numresults);
			foreach($pxml->photos->photo as $photo) {
				if($i == $randresult) {

					$title = $photo['title'];
					$date = $photo['datetaken'];	
					$owner = $photo['ownername'];
					$urlt = $photo['url_t'];
					$urls = $photo['url_s'];
					$urlm = $photo['url_m'];
					$urlo = $photo['url_o'];
					$description = $photo['description'];	
					$ownerid = $photo['owner'];
					$photoid = $photo['id'];					
				
					//if($options["wpr_fl_size"] == "small") {
						$img = '<img alt="'.$keyword.'" src="'.$urls.'" width="'.$width.'"/><br/>';
					//} elseif($options["wpr_fl_size"] == "med") {
					//	$img = '<img alt="'.$keyword.'" src="'.$urlm.'" width="'.$width.'" /><br/>';
					//} else {
					//	$img = '<img alt="'.$keyword.'" src="'.$urlo.'" width="'.$width.'" /><br/>';
					//}
					
					$link = 'http://www.flickr.com/photos/'.$ownerid.'/'.$photoid;				
				
					$photo = $template;
					$photo = wpr_random_tags($photo);									
					$photo = str_replace("{image}", $img, $photo);	
					$photo = str_replace("{date}", $date, $photo);
					$photo = str_replace("{owner}", $owner, $photo);
					$photo = str_replace("{url}", $link, $photo);
					$noqkeyword = str_replace('"', '', $keyword);
					$photo = str_replace("{keyword}", $noqkeyword, $photo);
					$photo = str_replace("{Keyword}", ucwords($noqkeyword), $photo);	
					$photo = str_replace("{description}", $description, $photo);	
					$photo = str_replace("{title}", $title, $photo);	
					
					$customfield = array();
					$customfield["thumbnail"] = $urlm;
					
					$photos[$x]["unique"] = $photoid;
					$photos[$x]["title"] = $title;
					$photos[$x]["content"] = $photo;
					$photos[$x]["customfield"] = $customfield;
					$x++;
				}
				$i++;
			}
			if(empty($photos)) {
				$photos["error"]["module"] = "Thumbnail";
				$photos["error"]["reason"] = "No content";
				$photos["error"]["message"] = __("No Flickr images found.","wprobot");	
				return $photos;	
			} else {
				return $photos;	
			}
		} else {
			$photos["error"]["module"] = "Thumbnail";
			$photos["error"]["reason"] = "No content";
			$photos["error"]["message"] = __("No Flickr images found.","wprobot");	
			return $photos;	
		}
	}	
}

function wpr_flickr_options_default() {
	$options = array(
		"wpr_fl_apikey" => "",
		"wpr_fl_sort" => "relevance",
		"wpr_fl_license" => "1,2,3,4,5,6,7",
		"wpr_fl_size" => "med",
		"wpr_fl_width" => "400",
		"wpr_fl_twidth" => "160",
		"wpr_fl_striplinks" => "yes"
	);
	return $options;
}

function wpr_flickr_options($options) {
	?>
	<h3 style="text-transform:uppercase;border-bottom: 1px solid #ccc;"><?php _e("Flickr Options","wprobot") ?></h3>			
		<table class="addt" width="100%" cellspacing="2" cellpadding="5" class="editform"> 
			<tr <?php if($options['wpr_fl_apikey'] == "") {echo 'style="background:#F8E0E0;"';} ?> valign="top"> 
				<td width="40%" scope="row"><?php _e("Flickr API Key:","wprobot") ?></td> 
				<td><input size="40" name="wpr_fl_apikey" type="text" id="wpr_fl_apikey" value="<?php echo $options['wpr_fl_apikey'] ;?>"/>
				<!--Tooltip--><a target="_blank" class="tooltip" href="http://www.flickr.com/services/">?<span><?php _e('This setting is required for the Flickr module to work!<br/><br/><b>Click to go to the Flickr API sign up page!</b>',"wprobot") ?></span></a>
				</td> 
			</tr>	
			<tr valign="top"> 
				<td width="40%" scope="row"><?php _e("License:","wprobot") ?></td> 
				<td>
				<select name="wpr_fl_license" id="wpr_fl_license">
					<option value="1,2,3,4,5,6,7" <?php if ($options['wpr_fl_license']=="1,2,3,4,5,6,7"){echo "selected";}?>><?php _e("All licenses","wprobot") ?></option>
					<option value="1,2,3,4,5,6" <?php if ($options['wpr_fl_license']=="1,2,3,4,5,6"){echo "selected";}?>><?php _e("Only licenses requiring attribution","wprobot") ?></option>
					<option value="7" <?php if ($options['wpr_fl_license']=="7"){echo "selected";}?>><?php _e("Only licenses not requiring attribution","wprobot") ?></option>
				</select>						
				</td> 
			</tr>	
			<tr valign="top"> 
				<td width="40%" scope="row"><?php _e("Image Size:","wprobot") ?></td> 
				<td>
				<select name="wpr_fl_size" id="wpr_fl_size">
					<option value="small" <?php if ($options['wpr_fl_size']=="small"){echo "selected";}?>><?php _e("Small","wprobot") ?></option>
					<option value="med" <?php if ($options['wpr_fl_size']=="med"){echo "selected";}?>><?php _e("Medium","wprobot") ?></option>
					<option value="orig" <?php if ($options['wpr_fl_size']=="orig"){echo "selected";}?>><?php _e("Original","wprobot") ?></option>
				</select>				
				</td> 
			</tr>	
			<tr valign="top"> 
				<td width="40%" scope="row"><?php _e("Image Width:","wprobot") ?></td> 
				<td><?php _e("Posts:","wprobot") ?> <input name="wpr_fl_width" type="text" id="wpr_fl_width" size="4" value="<?php echo $options['wpr_fl_width'] ;?>"/>px <?php _e("Thumbnails:","wprobot") ?> <input name="wpr_fl_twidth" type="text" id="wpr_fl_twidth" size="4" value="<?php echo $options['wpr_fl_twidth'] ;?>"/>px
				</td> 
			</tr>				
			<tr valign="top"> 
				<td width="40%" scope="row"><?php _e("Sort by:","wprobot") ?></td> 
				<td>
				<select name="wpr_fl_sort" id="wpr_fl_sort">
					<option value="relevance" <?php if ($options['wpr_fl_sort']=="relevance"){echo "selected";}?>><?php _e("Relevance","wprobot") ?></option>
					<option value="date-posted-asc" <?php if ($options['wpr_fl_sort']=="date-posted-asc"){echo "selected";}?>><?php _e("Date posted, ascending","wprobot") ?></option>
					<option value="date-posted-desc" <?php if ($options['wpr_fl_sort']=="date-posted-desc"){echo "selected";}?>><?php _e("Date posted, descending","wprobot") ?></option>
					<option value="date-taken-asc" <?php if ($options['wpr_fl_sort']=="date-taken-asc"){echo "selected";}?>><?php _e("Date taken, ascending","wprobot") ?></option>
					<option value="date-taken-desc" <?php if ($options['wpr_fl_sort']=="date-taken-desc"){echo "selected";}?>><?php _e("Date taken, descending","wprobot") ?></option>
					<option value="interestingness-desc" <?php if ($options['wpr_fl_sort']=="interestingness-desc"){echo "selected";}?>><?php _e("Interestingness, descending","wprobot") ?></option>
					<option value="interestingness-asc" <?php if ($options['wpr_fl_sort']=="interestingness-asc"){echo "selected";}?>><?php _e("Interestingness, ascending","wprobot") ?></option>			
				</select>				
				</td> 
			</tr>						
			<tr valign="top"> 
				<td width="40%" scope="row"><?php _e("Strip All Links from...","wprobot") ?></td> 
				<td><input name="wpr_fl_striplinks" type="checkbox" id="wpr_fl_striplinks" value="yes" <?php if ($options['wpr_fl_striplinks']=='yes') {echo "checked";} ?>/> <?php _e("Comments","wprobot") ?><br/></td> 
			</tr>				
		</table>	
	<?php
}

?>