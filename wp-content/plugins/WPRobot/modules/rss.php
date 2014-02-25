<?php

function wpr_rsspost($keyword="",$num,$start,$url,$comments="") {
   global $wpdb, $wpr_table_templates;

	$template = $wpdb->get_var("SELECT content FROM " . $wpr_table_templates . " WHERE type = 'rss'");
	if($template == false || empty($template)) {
		$return["error"]["module"] = "RSS";
		$return["error"]["reason"] = "No template";
		$return["error"]["message"] = __("Module Template does not exist or could not be loaded.","wprobot");
		return $return;	
	}	
	$options = unserialize(get_option("wpr_options"));	

	// FILTER
	$pos = strpos($url, '*FILTER*');
	if ($pos === false) {
	} else {
		$url = str_replace("*FILTER*","",$url);
		$options['wpr_rss_filter'] = 'yes';
	}

	$kw = $keyword;
	$url = trim($url);
	$url2 = urldecode($url);
	$start = 0;
	$found = 0;

	if ( function_exists('curl_init') ) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (compatible; Konqueror/4.0; Microsoft Windows) KHTML/4.0.80 (like Gecko)");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_URL, $url2);
		curl_setopt($ch, CURLOPT_TIMEOUT, 60);
		$response = curl_exec($ch);
		if (!$response) {
			$return["error"]["module"] = "RSS";
			$return["error"]["reason"] = "cURL Error";
			$return["error"]["message"] = __("cURL Error Number ","wprobot").curl_errno($ch).": ".curl_error($ch);	
			return $return;
		}		
		curl_close($ch);
	} else { 				
		$response = @file_get_contents($url2);
		if (!$response) {
			$return["error"]["module"] = "RSS";
			$return["error"]["reason"] = "cURL Error";
			$return["error"]["message"] = __("cURL is not installed on this server!","wprobot");	
			return $return;		
		}
	}
    
    $therss = @simplexml_load_string($response);
	if (!$therss){
		$therss = @simplexml_load_file($url);
	}
	//echo "<pre>".print_r($therss)."</pre>";	
	if (empty($therss)) {
		$return["error"]["module"] = "RSS";
		$return["error"]["reason"] = "RSS Error";
		$return["error"]["message"] = __("RSS Feed could not be loaded or is empty!","wprobot");	
		return $return;		
	}
	

	$posts = array();

	if(isset($therss->entry)) {$fee = $therss->entry;$ffr="blogger";} 
	elseif(isset($therss->channel->item)) {$fee = $therss->channel->item;} 
	elseif(isset($therss->item)) {$fee = $therss->item;} 
	else {$fee = "stop";}

	if($ffr=="blogger") {
	$ctitle = $therss->title;			
	} else {
	$ctitle = $therss->channel->title;
	$clink = $therss->channel->link;
	$cdesc = $therss->channel->description;			
	}

	if($fee == "stop") {
		$return["error"]["module"] = "RSS";
		$return["error"]["reason"] = "Invalid Feed";
		$return["error"]["message"] = __("Invalid Feed","wprobot");	
		return $return;			
	} else {
		foreach ($fee as $item) {

			$allabort = 0;	
			$abort = 0;		
//print_r($item);
			$title = $item->title;
			$title = preg_replace('/[\r\n\s]+/xms', ' ', trim($title));
			$title = strip_tags($title);
		
			$guid = $item->guid;	


			
			$gattr = $guid->attributes();			
			if($gattr['isPermaLink'] != "false" && !empty($guid)) {$unguid = $guid;} elseif(!empty($item->id)) {$unguid = $item->id;} else {$unguid = "";}

			if($ffr=="blogger") {
				$link = $item->link;
				$descr = $item->summary;
				$fullcontent = $item->content; 
				$date = $item->published;

				if($kw != "" && $options['wpr_rss_filter']=='yes') {
					$kw = str_replace('"', '', $kw);
					
					$kws = explode(" AND ", $kw);
					//print_r($kws);
					foreach($kws as $kwx) {
						$kw2 = " ".$kwx." ";
						//echo $kw2."<br>";
						$c1 = stripos($fullcontent, $kw2);
						$c2 = stripos($descr, $kw2);
						$c3 = stripos($title, $kw2);
						if($c1 != false || $c2 != false || $c3 != false) {
							//echo "keyword was found<br>";
							$abort = 0;
						} else {
							//echo "keyword was not found<br>";			
							$abort = 1;
							$allabort = 1;
						}						
					}
				}
				
				foreach ($item->link as $link) {
				
					$attr = $link->attributes();
					if($link['rel'] == "replies" && $link['type'] == "application/atom+xml") {$commentRss = $link['href'];}
					if($link['rel'] == "alternate") {$sourcelink = $link['href']; }		
//echo $sourcelink . "<br><br>";			print_r($link);		
				}							

			} else {
				$sourcelink = $item->link;	

				$descr = $item->description;
				$date = $item->pubDate;	

				$dc  = $item->children("http://purl.org/dc/elements/1.1/");
				$creator = (string) $dc->creator;
				
				$ccc = $item->children("http://purl.org/rss/1.0/modules/content/");
				$fullcontent = (string)trim($ccc->encoded);				
	//echo "<br><br>LOOLOLOL " . $descr . " GAGAGAGA ". $fullcontent. "DDD<br><br>";			
				if($kw != "" && $options['wpr_rss_filter']=='yes') {
					$kw = str_replace('"', '', $kw);
					
					$kws = explode(" AND ", $kw);
					//print_r($kws);
					foreach($kws as $kwx) {
						$kw2 = " ".$kwx." ";
						//echo $kw2."<br>";
						$c1 = stripos($fullcontent, $kw2);
						$c2 = stripos($descr, $kw2);
						$c3 = stripos($title, $kw2);
						if($c1 != false || $c2 != false || $c3 != false) {
							//echo "keyword was found<br>";
							$abort = 0;
						} else {
							//echo "keyword was not found<br>";			
							$abort = 1;
							$allabort = 1;
						}						
					}
				}
				
				$wfw = $item->children("http://wellformedweb.org/CommentAPI/");
				$commentRss = $wfw->commentRss;	
				
				$enclosure = $item->enclosure["url"];
				
				$media = $item->children('http://search.yahoo.com/mrss/');
				if($media->player) {
					$attrs = $media->player->attributes();
					$mediaplayer = $attrs['url']; 					  
				}
				if($media->content) {							
					$attrs = $media->content->attributes();
					$mediacontent = $attrs['url']; 
					$mediatype = $attrs['type']; 
					$mediaheight = $attrs['height']; 
					$mediawidth = $attrs['width'];  
					$video ='
					<object width="'.$mediawidth.'" height="'.$mediaheight.'">
					<param name="movie" value="'.$mediacontent.'"></param>
					<embed src="'.$mediacontent.'" type="'.$mediatype.'" width="'.$mediawidth.'" height="'.$mediaheight.'"></embed>
					</object>';		
					}
				if($media->thumbnail) {								  
					$attrs = $media->thumbnail[0]->attributes();
					$mediathumbnail = $attrs['url']; 
				}	
			}
			
			if(!empty($unguid)) {$uid = $unguid;} else {$uid = $sourcelink;}	

			if($abort == 0 && $allabort == 0 && !wpr_check_unique($uid)) {
	
				$source = '<a rel="nofollow" href="'.$sourcelink.'">'.$ctitle.'</a>';						
				if($fullcontent != "" && $options['wpr_rss_content']=='full') {$content = $fullcontent;} else {$content = $descr;}				
				if ($options['wpr_rss_striplinks']=='yes') {$content = wpr_strip_selected_tags($content, array('a','iframe','script'));}
				
				if($options['wpr_rss_content']=='trunc3') {$content = substr($content,0,150). "...";}
				if($options['wpr_rss_content']=='trunc') {$content = substr($content,0,350). "...";}
				if($options['wpr_rss_content']=='trunc1') {$content = substr($content,0,500). "...";}
				if($options['wpr_rss_content']=='trunc2') {$content = substr($content,0,700). "...";}
				
				$post = $template;	
				$post = wpr_random_tags($post);
				$content = str_replace("$", "$ ", $content); 
				$content = str_replace("\n\r", " ", $content); 				
				$content = str_replace("\n", " ", $content); 

				$post = str_replace("{content}", $content, $post);			
				$post = str_replace("{source}", $source, $post);	
				$post = str_replace("{sourcelink}", $sourcelink, $post);	
				$post = str_replace("{author}", $creator, $post);
				$noqkeyword = str_replace('"', '', $keyword);
				$post = str_replace("{keyword}", $noqkeyword, $post);
				$post = str_replace("{Keyword}", ucwords($noqkeyword), $post);			
				$post = str_replace("{url}", $sourcelink, $post);	
				$post = str_replace("{title}", $title, $post);	
				$post = str_replace("{mediacontent}", $video, $post);
				$post = str_replace("{mediathumbnail}", $mediathumbnail, $post);							
				$post = str_replace("{enclosure}", $enclosure, $post);	

				
					if(function_exists("wpr_translate_partial")) {
						$post = wpr_translate_partial($post);
					}		
					if(function_exists("wpr_rewrite_partial")) {
						$post = wpr_rewrite_partial($post,$options);
					}					
				if(!empty($mediathumbnail)) {$customfield = $mediathumbnail;} elseif(!empty($enclosure)) {$customfield = $enclosure;} else {$customfield = "";}	
					
					$customfield2 = array();
					$customfield2["rsstitle"] = $title;
					$customfield2["rssmediacontent"] = $video;
					$customfield2["rssenclosure"] = $enclosure;
					$customfield2["rsssource"] = $sourcelink;
					$customfield2["rssimage"] = $customfield;						
					
				$posts[$found]["unique"] = $uid;
				$posts[$found]["title"] = $title;
				$posts[$found]["content"] = $post;	
				$posts[$found]["customfield"] = $customfield2;
				
				$found++;
				
			}
	
			if ($found==$num){break;}
		}
	}			
//echo "<br>RRR<br><br>";print_r($posts);
	
	if($found > 0) {
		return $posts;		
	} elseif($abort == 1) {
		// error: no posts found for keywords
		$posts["error"]["module"] = "RSS";
		$posts["error"]["reason"] = "No content";
		$posts["error"]["message"] = __("No post found in RSS feed for keyword $kw","wprobot");	
		return $posts;					
	} elseif($found == 0) {
		// error: no posts found
		$posts["error"]["module"] = "RSS";
		$posts["error"]["reason"] = "No content";
		$posts["error"]["message"] = __("No unposted RSS items found!","wprobot");	
		return $posts;	
	} elseif(empty($posts)) {
		// error: no posts found
		$posts["error"]["module"] = "RSS";
		$posts["error"]["reason"] = "No content";
		$posts["error"]["message"] = __("No items found in this feed!","wprobot");	
		return $posts;		
	} else {
		// error: no posts found
		$posts["error"]["module"] = "RSS";
		$posts["error"]["reason"] = "No content";
		$posts["error"]["message"] = __("No items found in this feed!","wprobot");	
		return $posts;			
	}
}

function wpr_rss_options_default() {
	$options = array(
		"wpr_rss_content" => "full",
		"wpr_rss_filter" => "no",
		"wpr_rss_striplinks" => "no"
	);
	return $options;
}

function wpr_rss_options($options) {
	?>
	<h3 style="text-transform:uppercase;border-bottom: 1px solid #ccc;"><?php _e("RSS Options","wprobot") ?></h3>			
		<table class="addt" width="100%" cellspacing="2" cellpadding="5" class="editform"> 
			<tr valign="top"> 
				<td width="40%" scope="row"><?php _e("Content:","wprobot") ?></td> 
				<td>
				<select name="wpr_rss_content" id="wpr_rss_content">
					<option value="full" <?php if($options['wpr_rss_content']=="full"){_e('selected');}?>><?php _e("Post full content if possible, descriptions otherwise.","wprobot") ?></option>
					<option value="desc" <?php if($options['wpr_rss_content']=="desc"){_e('selected');}?>><?php _e("Only post descriptions.","wprobot") ?></option>
					<option value="trunc3" <?php if($options['wpr_rss_content']=="trunc3"){_e('selected');}?>><?php _e("Truncate post to first 150 characters even if full content available.","wprobot") ?></option>					
					<option value="trunc" <?php if($options['wpr_rss_content']=="trunc"){_e('selected');}?>><?php _e("Truncate post to first 300 characters even if full content available.","wprobot") ?></option>					
					<option value="trunc1" <?php if($options['wpr_rss_content']=="trunc1"){_e('selected');}?>><?php _e("Truncate post to first 500 characters even if full content available.","wprobot") ?></option>					
					<option value="trunc2" <?php if($options['wpr_rss_content']=="trunc2"){_e('selected');}?>><?php _e("Truncate post to first 700 characters even if full content available.","wprobot") ?></option>					
				</select>
			</td> 
			</tr>	
			<tr valign="top"> 
				<td width="40%" scope="row"><?php _e("Filter","wprobot") ?></td> 
				<td><input name="wpr_rss_filter" type="checkbox" id="wpr_rss_filter" value="yes" <?php if ($options['wpr_rss_filter']=='yes') {echo "checked";} ?>/> <?php _e("Only create posts from feeds that contain the associated keyword.","wprobot") ?>
				</td> 
			</tr>				
			<tr valign="top"> 
				<td width="40%" scope="row"><?php _e("Strip All Links from...","wprobot") ?></td> 
				<td><input name="wpr_rss_striplinks" type="checkbox" id="wpr_rss_striplinks" value="yes" <?php if ($options['wpr_rss_striplinks']=='yes') {echo "checked";} ?>/> <?php _e("Feed Content","wprobot") ?>
				</td> 
			</tr>				
		</table>	
	<?php
}
?>