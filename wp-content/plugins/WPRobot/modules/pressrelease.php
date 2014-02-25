<?php

function wpr_pressreleasepost($keyword,$num,$start,$optional="",$comments="") {
	global $wpdb,$wpr_table_templates;
	
	if($keyword == "") {
		$return["error"]["module"] = "Press Release";
		$return["error"]["reason"] = "No keyword";
		$return["error"]["message"] = __("No keyword specified.","wprobot");
		return $return;	
	}	
	
	$template = $wpdb->get_var("SELECT content FROM " . $wpr_table_templates . " WHERE type = 'pressrelease'");
	if($template == false || empty($template)) {
		$return["error"]["module"] = "Press Release";
		$return["error"]["reason"] = "No template";
		$return["error"]["message"] = __("Module Template does not exist or could not be loaded.","wprobot");
		return $return;	
	}		
	$options = unserialize(get_option("wpr_options"));
 	$posts = array();
	
	$keyword2 = $keyword;	
	$keyword = str_replace( " ","+",$keyword );	
	$keyword = urlencode($keyword);
	
	$blist[] = "Mozilla/5.0 (compatible; Konqueror/4.0; Microsoft Windows) KHTML/4.0.80 (like Gecko)";
    $blist[] = "Mozilla/5.0 (compatible; Konqueror/3.92; Microsoft Windows) KHTML/3.92.0 (like Gecko)";
    $blist[] = "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.0; WOW64; SLCC1; .NET CLR 2.0.50727; .NET CLR 3.0.04506; Media Center PC 5.0; .NET CLR 1.1.4322; Windows-Media-Player/10.00.00.3990; InfoPath.2";
    $blist[] = "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; .NET CLR 1.1.4322; InfoPath.1; .NET CLR 2.0.50727; .NET CLR 3.0.04506.30; Dealio Deskball 3.0)";
    $blist[] = "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; NeosBrowser; .NET CLR 1.1.4322; .NET CLR 2.0.50727)";
    $ua = $blist[array_rand($blist)];	

	$page = $start / 25;
	$page = (string) $page; 
	$page = explode(".", $page);	
	$page=(int)$page[0];	
	$page++;	

	if($page == 0) {$page = 1;}
	$prep = floor($start / 25);
	$numb = $start - $prep * 25;	
	
	$search_url = "http://www.prweb.com/Search.aspx?Search-releases=$keyword&start=$page";

	// make the cURL request to $search_url
	if ( function_exists('curl_init') ) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_USERAGENT, 'Firefox (WindowsXP) - Mozilla/5.0 (Windows; U; Windows NT 5.1; en-GB; rv:1.8.1.6) Gecko/20070725 Firefox/2.0.0.6');
		curl_setopt($ch, CURLOPT_URL,$search_url);
		curl_setopt($ch, CURLOPT_FAILONERROR, true);
		curl_setopt($ch, CURLOPT_AUTOREFERER, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 45);
		$html = curl_exec($ch);
		if (!$html) {
			$return["error"]["module"] = "Press Release";
			$return["error"]["reason"] = "cURL Error";
			$return["error"]["message"] = __("cURL Error Number ","wprobot").curl_errno($ch).": ".curl_error($ch);	
			return $return;
		}		
		curl_close($ch);
	} else { 				
		$html = @file_get_contents($search_url);
		if (!$html) {
			$return["error"]["module"] = "Press Release";
			$return["error"]["reason"] = "cURL Error";
			$return["error"]["message"] = __("cURL is not installed on this server!","wprobot");	
			return $return;		
		}
	}	

	// parse the html into a DOMDocument  

	$dom = new DOMDocument();
	@$dom->loadHTML($html);

	// Grab Product Links  

	$xpath = new DOMXPath($dom);
	$paras = $xpath->query("//div[@id='releases']//h3//a");
	
	$x = 0;
	$end = $numb + $num;
	if($end > $paras->length) { $end = $paras->length;}	
	
	if($end == 0 || $end == $numb) {
		$posts["error"]["module"] = "Press Release";
		$posts["error"]["reason"] = "No content";
		$posts["error"]["message"] = __("No (more) pressreleases found.","wprobot");	
		return $posts;		
	}	
	
	for ($i = $numb;  $i < $end; $i++ ) {
		$para = $paras->item($i);

		if($para == '' | $para == null) {
			$posts["error"]["module"] = "Press Release";
			$posts["error"]["reason"] = "No content";
			$posts["error"]["message"] = __("No (more) pressreleases found.","wprobot");	
			return $posts;		
		} else {
		
			$target_url = $para->getAttribute('href');
			
			// make the cURL request to $search_url
			if ( function_exists('curl_init') ) {
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_USERAGENT, 'Firefox (WindowsXP) - Mozilla/5.0 (Windows; U; Windows NT 5.1; en-GB; rv:1.8.1.6) Gecko/20070725 Firefox/2.0.0.6');
				curl_setopt($ch, CURLOPT_URL, $target_url);
				curl_setopt($ch, CURLOPT_FAILONERROR, true);
				curl_setopt($ch, CURLOPT_AUTOREFERER, true);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
				curl_setopt($ch, CURLOPT_TIMEOUT, 45);
				$html = curl_exec($ch);
				if (!$html) {
					$return["error"]["module"] = "Press Release";
					$return["error"]["reason"] = "cURL Error";
					$return["error"]["message"] = __("cURL Error Number ","wprobot").curl_errno($ch).": ".curl_error($ch);	
					return $return;
				}		
				curl_close($ch);
			} else { 				
				$html = @file_get_contents($target_url);
				if (!$html) {
					$return["error"]["module"] = "Press Release";
					$return["error"]["reason"] = "cURL Error";
					$return["error"]["message"] = __("cURL is not installed on this server!","wprobot");	
					return $return;		
				}
			}

			// parse the html into a DOMDocument  

			$dom = new DOMDocument();
			@$dom->loadHTML($html);
				
			// Grab Press Release Title 			
			$xpath1 = new DOMXPath($dom);
			$paras1 = $xpath1->query("//h1[@class='title']");
			$para1 = $paras1->item(0);
			$title = $para1->textContent;	

			// Grab Press Release Summary 			
			$xpath1 = new DOMXPath($dom);
			$paras1 = $xpath1->query("//h2[@class='subtitle']");
			$para1 = $paras1->item(0);
			$summary = $para1->textContent;			

			// Grab Press Release Thumbnail 			
			$xpath1 = new DOMXPath($dom);
			$paras1 = $xpath1->query("//div/div[@class='mediaBox']/div/img[@class='newsImage']"); 
			$para1 = $paras1->item(0);
			if(isset($para1)) {
				$imgurl = $para1->getAttribute('src');
			}
			if(!empty($imgurl)) {$thumbnail = '<img style="float:left;margin: 0 20px 10px 0;" src="'.$imgurl.'" />';} else {$thumbnail = "";}
			
			// Grab Press Release	
			$xpath2 = new DOMXPath($dom);
			//$paras2 = $xpath2->query("//div/div[@class='fullWidth floatLeft dottedTop']"); 
			$paras2 = $xpath2->query("//div/div[@class='one content']/div[@class='fullWidth floatLeft dottedTop']"); 
			$para2 = $paras2->item(0);		
			$string = $dom->saveXml($para2);


		//	$string = preg_replace('#\#\#\#(.*)#smiU', '', $string);
		//	$string = preg_replace('Share: (.*)#smiU', '', $string);	
			
		//	$string = preg_replace('#PRWeb News Center(.*)#smiU', '', $string);	
		//	$string = preg_replace('#Create Account(.*)#smiU', '', $string);	
			
		//	$string = preg_replace('#(.*)Printer Friendly Version#smiU', '', $string);	
		//	$string = preg_replace('#<h1 class="h1">(.*)</h1>#smiU', '', $string);
			$string = preg_replace('#<div(.*)<p class="releaseDateline">#smiU', '', $string);
			$string = preg_replace('#<div(.*)</div>#smiU', '', $string);
			$string = preg_replace('#<p style=\"text-align: center; font-weight: bold; clear: both;\">(.*)</p>#smiU', '', $string);
			$string = preg_replace('#<p style=\"text-align: center;  font-weight: bold;clear:both\">(.*)</p>#smiU', '', $string);
			$string = str_replace("clear:both", "", $string);
			$string = str_replace("clear: both", "", $string);				
			$string = str_replace("]]>", '', $string);			
			$string = str_replace('xml:lang="en" lang="en" xmlns="http://www.w3.org/1999/xhtml"&gt;', '', $string);			
			
			$string = strip_tags($string,'<a><p><strong><b><br><i><img>');		
			$string = str_replace("$", "$ ", $string);
			//$string = utf8_decode($string);
			//if (function_exists('iconv')) {$string = iconv('UTF-8','ISO-8859-1//IGNORE', $string);}
			$pressreleasebody = $string;
			$pos2 = strpos($string, "301 Moved");	

				if ($pos2 !== false) {
					$return["error"]["module"] = "Press Release";
					$return["error"]["reason"] = "IncNum";
					$return["error"]["message"] = __("Press release has been deleted or moved and was skipped.","wprobot");	
					return $return;		
				}
			
				if (empty($pressreleasebody)) {
					$return["error"]["module"] = "Press Release";
					$return["error"]["reason"] = "No Content";
					$return["error"]["message"] = __("No press release found.","wprobot");	
					return $return;		
				}
				
			//$title = utf8_decode($title);
			if (function_exists('iconv')) {$summary = iconv('UTF-8','ISO-8859-1//IGNORE', $summary);}
			if (function_exists('iconv')) {$title = iconv('UTF-8','ISO-8859-1//IGNORE', $title);}
			
					$customfield = array();
					$customfield["prthumbnail"] = $thumbnail;
			
			$post = $template;
			$post = wpr_random_tags($post);
			$post = str_replace("{pressrelease}", $pressreleasebody, $post);	
			$post = str_replace("{summary}", $summary, $post);	
			$post = str_replace("{thumbnail}", $thumbnail, $post);				
			$noqkeyword = str_replace('"', '', $keyword2);
			$post = str_replace("{keyword}", $noqkeyword, $post);
			$post = str_replace("{Keyword}", ucwords($noqkeyword), $post);					
			
			$post = str_replace("{title}", $title, $post);	
			$post = str_replace("{url}", $target_url, $post);				
					if(function_exists("wpr_translate_partial")) {
						$post = wpr_translate_partial($post);
					}
					if(function_exists("wpr_rewrite_partial")) {
						$post = wpr_rewrite_partial($post,$options);
					}					
			$posts[$x]["unique"] = $target_url;
			$posts[$x]["title"] = $title;
			$posts[$x]["content"] = $post;	
			$posts[$x]["customfield"] = $customfield;					
			$x++;
		}	
	}	
	return $posts;
}

function wpr_pressrelease_options_default() {
	$options = array(
	);
	return $options;
}

function wpr_pressrelease_options($options) {
	?>
	<h3 style="text-transform:uppercase;border-bottom: 1px solid #ccc;"><?php _e("Press Release Options","wprobot") ?></h3>
		<table class="addt" width="100%" cellspacing="2" cellpadding="5" class="editform"> 	
			<tr valign="top"> 
				<td width="40%" scope="row"><?php _e("No options are available for this module.","wprobot") ?></td> 
			</tr>						
		</table>		
	<?php
}
?>