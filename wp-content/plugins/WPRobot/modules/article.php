<?php

// =============================== EZINEMARK.com ================================ //

function wpr_ezinemarkpost($keyword,$num,$start,$optional="",$comments="",$options,$template,$ua,$proxy,$proxytype,$proxyuser) {
	global $wpdb,$wpr_table_templates;

	$page = $start / 15;
	$page = (string) $page; 
	$page = explode(".", $page);	
	$page=(int)$page[0];	
	$page++;	

	if($page == 0) {$page = 1;}
	$prep = floor($start / 15);
	$numb = $start - $prep * 15;
	
	$keyword = str_replace( "+","-",$keyword );	
	$search_url = "http://ezinemark.com/a/$keyword/p-$page/";

	// make the cURL request to $search_url
	if ( function_exists('curl_init') ) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_USERAGENT, $ua);
			if($proxy != "") {
				//curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, 1); 
				curl_setopt($ch, CURLOPT_PROXY, $proxy);
				if($proxyuser) {curl_setopt($ch, CURLOPT_PROXYUSERPWD, $proxyuser);}
				if($proxytype == "socks") {curl_setopt ($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);}
			}			
		curl_setopt($ch, CURLOPT_URL,$search_url);
		curl_setopt($ch, CURLOPT_FAILONERROR, true);
		curl_setopt($ch, CURLOPT_AUTOREFERER, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 45);
		$html = curl_exec($ch);
		if (!$html) {
			$return["error"]["module"] = "Article";
			$return["error"]["reason"] = "cURL Error";
			$return["error"]["message"] = __("cURL Error Number ","wprobot").curl_errno($ch).": ".curl_error($ch);	
			return $return;
		}		
		curl_close($ch);
	} else { 				
		$html = @file_get_contents($search_url);
		if (!$html) {
			$return["error"]["module"] = "Article";
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
	$paras = $xpath->query("//div[@class='l_search']//h3/a");
	
	$x = 0;
	$end = $numb + $num;
	
		if($paras->length == 0) {
			$posts["error"]["module"] = "Article";
			$posts["error"]["reason"] = "No content";
			$posts["error"]["message"] = __("No (more) articles found.","wprobot");	
			return $posts;		
		}	
	
	if($end > $paras->length) { $end = $paras->length;}
	for ($i = $numb;  $i < $end; $i++ ) {
	
		$para = $paras->item($i);
	
		if(empty($para)) {
			$posts["error"]["module"] = "Article";
			$posts["error"]["reason"] = "No content";
			$posts["error"]["message"] = __("No (more) articles found.","wprobot");	
			//print_r($posts);
			return $posts;		
		} else {
		
			$target_url = $para->getAttribute('href'); // $target_url = "http://www.articlesbase.com" . $para->getAttribute('href');		
			
			// make the cURL request to $search_url
			if ( function_exists('curl_init') ) {
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_USERAGENT, $ua);
				if($proxy != "") {
					//curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, 1); 
					curl_setopt($ch, CURLOPT_PROXY, $proxy);
					if($proxyuser) {curl_setopt($ch, CURLOPT_PROXYUSERPWD, $proxyuser);}
					if($proxytype == "socks") {curl_setopt ($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);}
				}					
				curl_setopt($ch, CURLOPT_URL,$target_url);
				curl_setopt($ch, CURLOPT_FAILONERROR, true);
				curl_setopt($ch, CURLOPT_AUTOREFERER, true);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
				curl_setopt($ch, CURLOPT_TIMEOUT, 45);
				$html = curl_exec($ch);
				if (!$html) {
					$return["error"]["module"] = "Article";
					$return["error"]["reason"] = "cURL Error";
					$return["error"]["message"] = __("cURL Error Number ","wprobot").curl_errno($ch).": ".curl_error($ch);	
					return $return;
				}		
				curl_close($ch);
			} else { 				
				$html = @file_get_contents($target_url);
				if (!$html) {
					$return["error"]["module"] = "Article";
					$return["error"]["reason"] = "cURL Error";
					$return["error"]["message"] = __("cURL is not installed on this server!","wprobot");	
					return $return;		
				}
			}

			// parse the html into a DOMDocument  

			$dom = new DOMDocument();
			@$dom->loadHTML($html);
				
			// Grab Article Title 			
			$xpath1 = new DOMXPath($dom);
			$paras1 = $xpath1->query("//div[@class='d_ctitle']/h1");
			$para1 = $paras1->item(0);
			$title = $para1->textContent;		
			
				if (empty($title)) {
					$return["error"]["module"] = "Article";
					$return["error"]["reason"] = "IncNum";
					$return["error"]["message"] = __("Video content skipped. ","wprobot");	
					return $return;
				}				
			
			// Grab Article	
			$xpath2 = new DOMXPath($dom);
			$paras2 = $xpath2->query("//div[@id='art_content']"); 
			$para2 = $paras2->item(0);		
			$string = $dom->saveXml($para2);
		

			$string = str_replace('<div class="KonaBody">', "", $string);	
			$string = str_replace("]]>", "", $string);
			$string = str_replace("]]&gt;", "", $string);
			$string = str_replace("&nbsp;", "", $string);	
			//$string = preg_replace('#<ul>(.*)</ul>#smiU', '', $string);			
			$string = preg_replace('#<div class="related_links">(.*)</ul></div>#smiU', '', $string);
			$string = strip_tags($string,'<p><strong><b><a><br>');			
			$string = str_replace("</div>", "", $string);	
			$string = str_replace("$", "$ ", $string); 
			$string = str_replace("<div>", "", $string);		
			if ($options['wpr_eza_striplinks']=='yes') {$string = wpr_strip_selected_tags($string, array('a','iframe','script'));}	
			$articlebody .= $string . ' ';	

			

			// Grab Ressource Box	

			$xpath3 = new DOMXPath($dom);
			$paras3 = $xpath3->query("//div[@class='authorbox']/div[@class='rightbl']//p");		//$para = $paras->item(0);		
			
			$ressourcetext = "";
			for ($y = 0;  $y < $paras3->length; $y++ ) {  //$paras->length
				$para3 = $paras3->item($y);
				$ressourcetext .= $dom->saveXml($para3);	
			}	
			
			$title = utf8_decode($title);
			
			// Split into Pages
			if($options['wpr_eza_split'] == "yes") {
				$articlebody = wordwrap($articlebody, $options['wpr_eza_splitlength'], "<!--nextpage-->");
			}
			
			$post = $template;
			$post = wpr_random_tags($post);
			$post = str_replace("{article}", $articlebody, $post);			
			$post = str_replace("{authortext}", $ressourcetext, $post);	
			$noqkeyword = str_replace('"', '', $keyword2);
			$post = str_replace("{keyword}", $noqkeyword, $post);
			$post = str_replace("{Keyword}", ucwords($noqkeyword), $post);				
			$post = str_replace("{title}", $title, $post);	
			$post = str_replace("{url}", $target_url, $post);
					if(function_exists("wpr_rewrite_partial")) {
						$post = wpr_rewrite_partial($post,$options);
					}			
					if(function_exists("wpr_translate_partial")) {
						$post = wpr_translate_partial($post);
					}	
					
			$posts[$x]["unique"] = $target_url;
			$posts[$x]["title"] = $title;
			$posts[$x]["content"] = $post;				
			$x++;
		}	
	}	
	return $posts;
}

// =============================== BUKISA.com ================================ //

function wpr_bukisapost($keyword,$num,$start,$optional="",$comments="",$options,$template,$ua,$proxy,$proxytype,$proxyuser) {
	global $wpdb,$wpr_table_templates;

	$page = $start / 10;
	$page = (string) $page; 
	$page = explode(".", $page);	
	$page=(int)$page[0];	
	$page++;	

	if($page == 0) {$page = 1;}
	$prep = floor($start / 10);
	$numb = $start - $prep * 10;

	$search_url = "http://www.bukisa.com/search?q=$keyword&where=0&page=$page";

	// make the cURL request to $search_url
	if ( function_exists('curl_init') ) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_USERAGENT, $ua);
			if($proxy != "") {
				//curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, 1); 
				curl_setopt($ch, CURLOPT_PROXY, $proxy);
				if($proxyuser) {curl_setopt($ch, CURLOPT_PROXYUSERPWD, $proxyuser);}
				if($proxytype == "socks") {curl_setopt ($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);}
			}			
		curl_setopt($ch, CURLOPT_URL,$search_url);
		curl_setopt($ch, CURLOPT_FAILONERROR, true);
		curl_setopt($ch, CURLOPT_AUTOREFERER, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 45);
		$html = curl_exec($ch);
		if (!$html) {
			$return["error"]["module"] = "Article";
			$return["error"]["reason"] = "cURL Error";
			$return["error"]["message"] = __("cURL Error Number ","wprobot").curl_errno($ch).": ".curl_error($ch);	
			return $return;
		}		
		curl_close($ch);
	} else { 				
		$html = @file_get_contents($search_url);
		if (!$html) {
			$return["error"]["module"] = "Article";
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
	$paras = $xpath->query("//h3[@class='entry_title']/a");
	
	$x = 0;
	$end = $numb + $num;
	
		if($paras->length == 0) {
			$posts["error"]["module"] = "Article";
			$posts["error"]["reason"] = "No content";
			$posts["error"]["message"] = __("No (more) articles found.","wprobot");	
			return $posts;		
		}	
	
	if($end > $paras->length) { $end = $paras->length;}
	for ($i = $numb;  $i < $end; $i++ ) {
	
		$para = $paras->item($i);
	
		if(empty($para)) {
			$posts["error"]["module"] = "Article";
			$posts["error"]["reason"] = "No content";
			$posts["error"]["message"] = __("No (more) articles found.","wprobot");	
			//print_r($posts);
			return $posts;		
		} else {
		
			$target_url = $para->getAttribute('href'); // $target_url = "http://www.articlesbase.com" . $para->getAttribute('href');		
			
			// make the cURL request to $search_url
			if ( function_exists('curl_init') ) {
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_USERAGENT, $ua);
				if($proxy != "") {
					//curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, 1); 
					curl_setopt($ch, CURLOPT_PROXY, $proxy);
					if($proxyuser) {curl_setopt($ch, CURLOPT_PROXYUSERPWD, $proxyuser);}
					if($proxytype == "socks") {curl_setopt ($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);}
				}					
				curl_setopt($ch, CURLOPT_URL,$target_url);
				curl_setopt($ch, CURLOPT_FAILONERROR, true);
				curl_setopt($ch, CURLOPT_AUTOREFERER, true);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
				curl_setopt($ch, CURLOPT_TIMEOUT, 45);
				$html = curl_exec($ch);
				if (!$html) {
					$return["error"]["module"] = "Article";
					$return["error"]["reason"] = "cURL Error";
					$return["error"]["message"] = __("cURL Error Number ","wprobot").curl_errno($ch).": ".curl_error($ch);	
					return $return;
				}		
				curl_close($ch);
			} else { 				
				$html = @file_get_contents($target_url);
				if (!$html) {
					$return["error"]["module"] = "Article";
					$return["error"]["reason"] = "cURL Error";
					$return["error"]["message"] = __("cURL is not installed on this server!","wprobot");	
					return $return;		
				}
			}

			// parse the html into a DOMDocument  

			$dom = new DOMDocument();
			@$dom->loadHTML($html);
				
			// Grab Article Title 			
			$xpath1 = new DOMXPath($dom);
			$paras1 = $xpath1->query("//div/h1");
			$para1 = $paras1->item(0);
			$title = $para1->textContent;		
			
			// Grab Article	
			$xpath2 = new DOMXPath($dom);
			$paras2 = $xpath2->query("//div[@id='article_section']/div[@class='KonaBody']"); 
			$para2 = $paras2->item(0);		
			$string = $dom->saveXml($para2);
		
			$string = strip_tags($string,'<p><strong><b><a><br>');
			$string = str_replace('<div class="KonaBody">', "", $string);	
			$string = str_replace("</div>", "", $string);
			$string = str_replace("]]>", "", $string);
			$string = str_replace("]]&gt;", "", $string);
			$string = str_replace("$", "$ ", $string); 
			$string = str_replace("&nbsp;", "", $string);	
			$string = preg_replace('#<strong>RELATED CONTENT(.*)#smiU', '', $string);
			if ($options['wpr_eza_striplinks']=='yes') {$string = wpr_strip_selected_tags($string, array('a','iframe','script'));}	
			$articlebody .= $string . ' ';			

			// Grab Ressource Box	

			$xpath3 = new DOMXPath($dom);
			$paras3 = $xpath3->query("//div[@id='bio_section']/p");		//$para = $paras->item(0);		
			
			$ressourcetext = "";
			for ($y = 0;  $y < $paras3->length; $y++ ) {  //$paras->length
				$para3 = $paras3->item($y);
				$ressourcetext .= $dom->saveXml($para3);	
			}	
			
			$title = utf8_decode($title);
			
			// Split into Pages
			if($options['wpr_eza_split'] == "yes") {
				$articlebody = wordwrap($articlebody, $options['wpr_eza_splitlength'], "<!--nextpage-->");
			}
			
			$post = $template;
			$post = wpr_random_tags($post);
			$post = str_replace("{article}", $articlebody, $post);			
			$post = str_replace("{authortext}", $ressourcetext, $post);	
			$noqkeyword = str_replace('"', '', $keyword2);
			$post = str_replace("{keyword}", $noqkeyword, $post);
			$post = str_replace("{Keyword}", ucwords($noqkeyword), $post);				
			$post = str_replace("{title}", $title, $post);	
			$post = str_replace("{url}", $target_url, $post);
					if(function_exists("wpr_rewrite_partial")) {
						$post = wpr_rewrite_partial($post,$options);
					}			
					if(function_exists("wpr_translate_partial")) {
						$post = wpr_translate_partial($post);
					}	
					
			$posts[$x]["unique"] = $target_url;
			$posts[$x]["title"] = $title;
			$posts[$x]["content"] = $post;				
			$x++;
		}	
	}	
	return $posts;
}

// =============================== GOARTICLES.com ================================ //

function wpr_goarticlepost($keyword,$num,$start,$optional="",$comments="",$options,$template,$ua,$proxy,$proxytype,$proxyuser) {
	global $wpdb,$wpr_table_templates;

	$search_url = "http://goarticles.com/search/?q=$keyword&start=$start&limit=$num";
	
	$proxy == "";
	if($options["wpr_trans_use_proxies"] == "yes") {
		$proxies = str_replace("\r", "", $options["wpr_trans_proxies"]);
		$proxies = explode("\n", $proxies);  
		$rand = array_rand($proxies);	
		list($proxy,$proxytype,$proxyuser)=explode("|",$proxies[$rand]);
	}	
	
//echo $search_url. " <br/>";
	// make the cURL request to $search_url
	if ( function_exists('curl_init') ) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_USERAGENT, $ua);		
			if($proxy != "") {
				//curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, 1); 
				curl_setopt($ch, CURLOPT_PROXY, $proxy);
				if($proxyuser) {curl_setopt($ch, CURLOPT_PROXYUSERPWD, $proxyuser);}
				if($proxytype == "socks") {curl_setopt ($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);}
			}			
		curl_setopt($ch, CURLOPT_URL,$search_url);
		curl_setopt($ch, CURLOPT_FAILONERROR, true);
		curl_setopt($ch, CURLOPT_AUTOREFERER, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 45);
		$html = curl_exec($ch);
		if (!$html) {
			//echo "CURL NO 1 <br/>";
			$return["error"]["module"] = "Article";
			$return["error"]["reason"] = "cURL Error";
			$return["error"]["message"] = __("cURL Error Number ","wprobot").curl_errno($ch).": ".curl_error($ch);	
			return $return;
		}		
		curl_close($ch);
	} else { 				
		$html = @file_get_contents($search_url);
		if (!$html) {
			$return["error"]["module"] = "Article";
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
	$paras = $xpath->query("//div//a[@class='article_title_link']");
	
	$x = 0;
	
		if($paras->length == 0) {
			$posts["error"]["module"] = "Article";
			$posts["error"]["reason"] = "IncNum";
			$posts["error"]["message"] = __("No (more) articles found.","wprobot");	
			return $posts;		
		}	
	
	$end = $paras->length;
	for ($i = 0;  $i < $end; $i++ ) {

		$para = $paras->item($i);
	
		if(empty($para)) {
			$posts["error"]["module"] = "Article";
			$posts["error"]["reason"] = "IncNum";
			$posts["error"]["message"] = __("No (more) articles found.","wprobot");	
			//print_r($posts);
			return $posts;		
		} else {
		
			$target_url = "http://goarticles.com".$para->getAttribute('href');	

			// make the cURL request to $search_url
			if ( function_exists('curl_init') ) {
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_USERAGENT, $ua);
					if($proxy != "") {
						//curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, 1); 
						curl_setopt($ch, CURLOPT_PROXY, $proxy);
						if($proxyuser) {curl_setopt($ch, CURLOPT_PROXYUSERPWD, $proxyuser);}
						if($proxytype == "socks") {curl_setopt ($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);}
					}					
				curl_setopt($ch, CURLOPT_URL,$target_url);
				curl_setopt($ch, CURLOPT_FAILONERROR, true);
				curl_setopt($ch, CURLOPT_AUTOREFERER, true);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
				curl_setopt($ch, CURLOPT_TIMEOUT, 45);
				$html = curl_exec($ch);
				if (!$html) {
			//echo $html. " <br/>";	
			//echo "CURL NO 2 <br/>";				
					$return["error"]["module"] = "Article";
					$return["error"]["reason"] = "cURL Error";
					$return["error"]["message"] = __("cURL Error Number ","wprobot").curl_errno($ch).": ".curl_error($ch);	
					return $return;
				}		
				curl_close($ch);
			} else { 				
				$html = @file_get_contents($target_url);				
				if (!$html) {
					$return["error"]["module"] = "Article";
					$return["error"]["reason"] = "cURL Error";
					$return["error"]["message"] = __("cURL is not installed on this server!","wprobot");	
					return $return;		
				}
			}

			$dom = new DOMDocument();
			@$dom->loadHTML($html);
				
			// Grab Article Author 			
			$xpath1 = new DOMXPath($dom);
			$paras1 = $xpath1->query("//div/h1[@class='art_head']/em");
			$para1 = $paras1->item(0);
			$author = $para1->textContent;					
				
			// Grab Article Title 			
			$xpath1 = new DOMXPath($dom);
			$paras1 = $xpath1->query("//div/h1[@class='art_head']");
			$para1 = $paras1->item(0);
			//$title = $dom->saveXml($para1);	
			//$title = preg_replace('#<em(.*)</em>#smiU', '', $title);
			//$title = strip_tags($title,'<p><strong><b><a><br>');
			$title = $para1->textContent;
			$title = str_replace($author, "", $title);
			$title = str_replace("by", "", $title);
			$title = trim($title);
			$title = substr($title,0,-4); 

			// Grab Article	
			$xpath2 = new DOMXPath($dom);
			$paras2 = $xpath2->query("//div[@id='main-col']/div[@class='article']"); 
			$para2 = $paras2->item(0);		
			$string = $dom->saveXml($para2);
			$string = str_replace("&#13;", '', $string);
			$string = trim($string);
			$string = preg_replace('#<h1(.*)</h1>#smiU', '', $string);
			$string = preg_replace('#<h2(.*)</h2>#smiU', '', $string);
			$string = strip_tags($string,'<p><strong><b><a><br>');
			$string = str_replace('<div class="KonaBody">', "", $string);	
			$string = str_replace("</div>", "", $string);
			$string = str_replace("&nbsp;", "", $string);	
			$string = str_replace("$", "$ ", $string); 
			$string = str_replace(chr(13), '', $string);
			if (function_exists('iconv')) {$string = iconv('UTF-8','ISO-8859-1//IGNORE', $string);}
			if ($options['wpr_eza_striplinks']=='yes') {$string = wpr_strip_selected_tags($string, array('a','iframe','script'));}		
			$articlebody = "<p>Article $author</p>".$string . ' ';			

			// Grab Ressource Box	
			
			$xpath2 = new DOMXPath($dom);
			$paras2 = $xpath2->query("//div[@id='main-col']/div[@class='article']"); 
			$para2 = $paras2->item(0);		
			$string = $dom->saveXml($para2);	
			$string = preg_replace('#(.*)</h3>#smiU', '', $string);
			$string = str_replace('<div class="KonaBody">', "", $string);	
			$string = str_replace("</div>", "", $string);	
			$string = str_replace("<div>", "", $string);	
			$string = strip_tags($string,'<p><strong><b><a><br>');			
			$ressourcetext = ''.$string;
			
			// Split into Pages
			if($options['wpr_eza_split'] == "yes") {
				$articlebody = wordwrap($articlebody, $options['wpr_eza_splitlength'], "<!--nextpage-->");
			}
			
			$post = $template;
			$post = wpr_random_tags($post);
			$post = str_replace("{article}", $articlebody, $post);			
			$post = str_replace("{authortext}", $ressourcetext, $post);	
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
			$x++;
		}	
	}	
	return $posts;
}

// =============================== EZA.com ================================ //

function wpr_articlepost($keyword,$num,$start,$optional="",$comments="") {
	global $wpdb,$wpr_table_templates;

	if($keyword == "") {
		$return["error"]["module"] = "Article";
		$return["error"]["reason"] = "No keyword";
		$return["error"]["message"] = __("No keyword specified.","wprobot");
		return $return;	
	}	
	
	$template = $wpdb->get_var("SELECT content FROM " . $wpr_table_templates . " WHERE type = 'article'");
	if($template == false || empty($template)) {
		$return["error"]["module"] = "Article";
		$return["error"]["reason"] = "No template";
		$return["error"]["message"] = __("Module Template does not exist or could not be loaded.","wprobot");
		return $return;	
	}		

 	$posts = array();
	$options = unserialize(get_option("wpr_options"));	
	
	$proxy == "";
	if($options["wpr_trans_use_proxies"] == "yes") {
		$proxies = str_replace("\r", "", $options["wpr_trans_proxies"]);
		$proxies = explode("\n", $proxies);  
		$rand = array_rand($proxies);	
		list($proxy,$proxytype,$proxyuser)=explode("|",$proxies[$rand]);
	}	

	$keyword2 = $keyword;	
	$keyword = str_replace( " ","+",$keyword );	
	$keyword = urlencode($keyword);
	
	$blist[] = "Mozilla/5.0 (compatible; Konqueror/4.0; Microsoft Windows) KHTML/4.0.80 (like Gecko)";
    $blist[] = "Mozilla/5.0 (compatible; Konqueror/3.92; Microsoft Windows) KHTML/3.92.0 (like Gecko)";
    $blist[] = "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.0; WOW64; SLCC1; .NET CLR 2.0.50727; .NET CLR 3.0.04506; Media Center PC 5.0; .NET CLR 1.1.4322; Windows-Media-Player/10.00.00.3990; InfoPath.2";
    $blist[] = "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; .NET CLR 1.1.4322; InfoPath.1; .NET CLR 2.0.50727; .NET CLR 3.0.04506.30; Dealio Deskball 3.0)";
    $blist[] = "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; NeosBrowser; .NET CLR 1.1.4322; .NET CLR 2.0.50727)";
    $ua = $blist[array_rand($blist)];		
	
	// SOURCE
	$source = array();
	if($options['wpr_eza_source_ab'] == 1 || $options['wpr_eza_source'] == "ab" || $options['wpr_eza_source'] == "rand") {
		$source[] = "ab";
	} if($options['wpr_eza_source_goa'] == 1 || $options['wpr_eza_source'] == "goa" || $options['wpr_eza_source'] == "rand") {
		$source[] = "goa";
	} if($options['wpr_eza_source_buk'] == 1 || $options['wpr_eza_source'] == "buk" || $options['wpr_eza_source'] == "rand") {
		$source[] = "buk";
	} if($options['wpr_eza_source_ezm'] == 1 || $options['wpr_eza_source'] == "ezm" || $options['wpr_eza_source'] == "rand") {
		$source[] = "ezm";
	}
	
	$rand_key = array_rand($source);
	if($source[$rand_key] == "ab") {
		$options['wpr_eza_source'] = "ab";
	} elseif($source[$rand_key] == "goa") {
		$options['wpr_eza_source'] = "goa";
	} elseif($source[$rand_key] == "buk") {
		$options['wpr_eza_source'] = "buk";
	} elseif($source[$rand_key] == "ezm") {
		$options['wpr_eza_source'] = "ezm";
	}

	//print_r($source);echo "source: ".$rand_key . " ". $options['wpr_eza_source'] . "\n";	

	/*if($options["wpr_eza_source"] == "rand") {
		$rand = rand(0,3);
		if($rand == 0) {$options["wpr_eza_source"] = "buk";
		} elseif($rand == 1) {$options["wpr_eza_source"] = "goa";
		} elseif($rand == 2) {$options["wpr_eza_source"] = "ezm";		
		} else {$options["wpr_eza_source"] = "ab";}	
	}*/

	if($options["wpr_eza_source"] == "buk") {
		return wpr_bukisapost($keyword,$num,$start,$optional,$comments,$options,$template,$ua,$proxy,$proxytype,$proxyuser);
	} elseif($options["wpr_eza_source"] == "goa") {
		return wpr_goarticlepost($keyword,$num,$start,$optional,$comments,$options,$template,$ua,$proxy,$proxytype,$proxyuser);
	} elseif($options["wpr_eza_source"] == "ezm") {
		return wpr_ezinemarkpost($keyword,$num,$start,$optional,$comments,$options,$template,$ua,$proxy,$proxytype,$proxyuser);		
	}

	if(empty($start)) {$start = 0;}		
	$search_url = "http://ezinearticles.com/search/?q=$keyword&index=$start"; // 

	// make the cURL request to $search_url
	if ( function_exists('curl_init') ) {
		$ch = curl_init();	
		curl_setopt($ch, CURLOPT_USERAGENT, $ua);
			if($proxy != "") {			
				//curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, 1); 
				curl_setopt($ch, CURLOPT_PROXY, $proxy);
				if($proxyuser) {curl_setopt($ch, CURLOPT_PROXYUSERPWD, $proxyuser);}
				if($proxytype == "socks") {curl_setopt ($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);}
			}			
		curl_setopt($ch, CURLOPT_URL,$search_url);
		curl_setopt($ch, CURLOPT_FAILONERROR, true);
		curl_setopt($ch, CURLOPT_AUTOREFERER, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 45);
		$html = curl_exec($ch);
		if (!$html) {
			$return["error"]["module"] = "Article";
			$return["error"]["reason"] = "cURL Error";
			$return["error"]["message"] = __("cURL Error Number ","wprobot").curl_errno($ch).": ".curl_error($ch);	
			return $return;
		}	
		curl_close($ch);
	} else { 	
		$html = @file_get_contents($search_url);
		if (!$html) {
			$return["error"]["module"] = "Article";
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
	$paras = $xpath->query("//div/h3/a");
	$x = 0;
	$end = $num;

		if($paras->length == 0) {
			$posts["error"]["module"] = "Article";
			$posts["error"]["reason"] = "No content";
			$posts["error"]["message"] = __("No (more) articles found.","wprobot");	
			return $posts;		
		}	

	if($end > $paras->length) { $end = $paras->length;}

	for ($i = 0;  $i <= $end; $i++ ) {

		$para = $paras->item($i);

		if(empty($para)) {
			$posts["error"]["module"] = "Article";
			$posts["error"]["reason"] = "No content";
			$posts["error"]["message"] = __("No (more) articles found.","wprobot");	
			return $posts;		
		} else {	
			$target_url = "http://ezinearticles.com" . $para->getAttribute('href'); // $target_url = "http://www.articlesbase.com" . $para->getAttribute('href');		

			// make the cURL request to $search_url
			if ( function_exists('curl_init') ) {
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_USERAGENT, $ua);
				if($proxy != "") {
					//curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, 1); 
					curl_setopt($ch, CURLOPT_PROXY, $proxy);
					if($proxyuser) {curl_setopt($ch, CURLOPT_PROXYUSERPWD, $proxyuser);}
					if($proxytype == "socks") {curl_setopt ($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);}
				}					
				curl_setopt($ch, CURLOPT_URL,$target_url);
				curl_setopt($ch, CURLOPT_FAILONERROR, true);
				curl_setopt($ch, CURLOPT_AUTOREFERER, true);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
				curl_setopt($ch, CURLOPT_TIMEOUT, 45);
				$html = curl_exec($ch);
				if (!$html) {
					$return["error"]["module"] = "Article";
					$return["error"]["reason"] = "cURL Error";
					$return["error"]["message"] = __("cURL Error Number ","wprobot").curl_errno($ch).": ".curl_error($ch);	
					return $return;
				}		
				curl_close($ch);
			} else { 				
				$html = @file_get_contents($target_url);
				if (!$html) {
					$return["error"]["module"] = "Article";
					$return["error"]["reason"] = "cURL Error";
					$return["error"]["message"] = __("cURL is not installed on this server!","wprobot");	
					return $return;		
				}
			}

			// parse the html into a DOMDocument  

			$dom = new DOMDocument();
			@$dom->loadHTML($html);
				
			// Grab Article Title 			
			$xpath1 = new DOMXPath($dom);
			$paras1 = $xpath1->query("//div/h1");
			$para1 = $paras1->item(0);
			$title = $para1->textContent;		
			
			// Grab Article	
			$xpath2 = new DOMXPath($dom);
			$paras2 = $xpath2->query("//div[@id='article-content']"); 
			$para2 = $paras2->item(0);		
			$string = $dom->saveXml($para2);
			
			//$string = preg_replace('#<div class="articles">(.*)</div>#smiU', '', $string);		
			//$string = preg_replace('#<a title="(.*)" href="/authors/(.*)</a>#smiU', '', $string);
			//$string = preg_replace('#<strong>(.*)</strong>#smiU', '', $string);
			
			$string = strip_tags($string,'<p><strong><b><a><br>');
			$string = str_replace("&nbsp;", "", $string);	
			$string = str_replace("]]>", "", $string);	
			$string = str_replace("$", "$ ", $string); 
			if ($options['wpr_eza_striplinks']=='yes') {$string = wpr_strip_selected_tags($string, array('a','iframe','script'));}			
			$articlebody .= $string . ' ';			

			// Grab Ressource Box	

			$xpath3 = new DOMXPath($dom);
			$paras3 = $xpath3->query("//div[@id='article-resource']");		//$para = $paras->item(0);		
			
			$ressourcetext = "";
			for ($y = 0;  $y < $paras3->length; $y++ ) {  //$paras->length
				$para3 = $paras3->item($y);
				$ressourcetext .= $dom->saveXml($para3);	
			}	
			
			$xpath2 = new DOMXPath($dom);
			$paras2 = $xpath2->query("//div[@id='article-body']/p"); 
			$para2 = $paras2->item(0);		
			$ressourcetext .= str_replace('href="', 'href="http://ezinearticles.com', $dom->saveXml($para2));			
			
			$title = utf8_decode($title);
			
			// Split into Pages
			if($options['wpr_eza_split'] == "yes") {
				$articlebody = wordwrap($articlebody, $options['wpr_eza_splitlength'], "<!--nextpage-->");
			}
			
			$post = $template;
			$post = wpr_random_tags($post);
			$post = str_replace("{article}", $articlebody, $post);			
			$post = str_replace("{authortext}", $ressourcetext, $post);	
			$noqkeyword = str_replace('"', '', $keyword2);
			$post = str_replace("{keyword}", $noqkeyword, $post);
			$post = str_replace("{Keyword}", ucwords($noqkeyword), $post);				
			$post = str_replace("{title}", $title, $post);	
			$post = str_replace("{url}", $target_url, $post);
					if(function_exists("wpr_rewrite_partial")) {
						$post = wpr_rewrite_partial($post,$options);
					}			
					if(function_exists("wpr_translate_partial")) {
						$post = wpr_translate_partial($post);
					}	
					
			$posts[$x]["unique"] = $target_url;
			$posts[$x]["title"] = $title;
			$posts[$x]["content"] = $post;				
			$x++;
		}	
	}	
	return $posts;
}

function wpr_article_options_default() {
	$options = array(
		"wpr_eza_lang" => "en",
		"wpr_eza_split" => "no",
		"wpr_eza_striplinks" => "no",
		"wpr_eza_splitlength" => "10000",
		"wpr_eza_source" => "",
		"wpr_eza_source_ab" => 1,
		"wpr_eza_source_goa" => 0,
		//"wpr_eza_source_buk" => 1,
		"wpr_eza_source_ezm" => 1,
	);
	return $options;
}

function wpr_article_options($options) {
	?>
	<h3 style="text-transform:uppercase;border-bottom: 1px solid #ccc;"><?php _e("Article Options","wprobot") ?></h3>
		<table class="addt" width="100%" cellspacing="2" cellpadding="5" class="editform"> 	

			<tr valign="top"> 
				<td width="40%" scope="row"><?php _e("Article Source:","wprobot") ?></td> 
				<td>
				<input name="wpr_eza_source_ab" type="checkbox" value="1" id="ab" <?php if ($options['wpr_eza_source_ab']=='1') {echo 'checked';} ?>> <label for="ab"><?php _e("Ezinearticles.com","wprobot") ?></label><br/>
				<input name="wpr_eza_source_goa" type="checkbox" value="1" id="goa" <?php if ($options['wpr_eza_source_goa']=='1') {echo 'checked';} ?>> <label for="goa"><?php _e("Goarticles.com","wprobot") ?></label> <br/>
				<!--<input name="wpr_eza_source_buk" type="checkbox" value="1" id="buk" <?php if ($options['wpr_eza_source_buk']=='1') {echo 'checked';} ?>> <label for="buk"><?php _e("Bukisa.com","wprobot") ?></label> <br/>-->
				<input name="wpr_eza_source_ezm" type="checkbox" value="1" id="ezm" <?php if ($options['wpr_eza_source_ezm']=='1') {echo 'checked';} ?>> <label for="ezm"><?php _e("Ezinemark.com","wprobot") ?></label> <br/>				
				<input name="wpr_eza_source" type="hidden" value="0">			
								
				<!--
				<select name="wpr_eza_source" id="wpr_eza_source">
					<option value="ab" <?php if($options['wpr_eza_source']=="ab"){_e('selected');}?>><?php _e("Articlesbase.com","wprobot") ?></option>
					<option value="goa" <?php if($options['wpr_eza_source']=="goa"){_e('selected');}?>><?php _e("Goarticles.com","wprobot") ?></option>
					<option value="buk" <?php if($options['wpr_eza_source']=="buk"){_e('selected');}?>><?php _e("Bukisa.com","wprobot") ?></option>
					<option value="ezm" <?php if($options['wpr_eza_source']=="ezm"){_e('selected');}?>><?php _e("Ezinemark.com","wprobot") ?></option>
					<option value="rand" <?php if($options['wpr_eza_source']=="rand"){_e('selected');}?>><?php _e("Random","wprobot") ?></option>					
				</select>-->
			</td> 
			</tr>

			<!--<tr valign="top"> 
				<td width="40%" scope="row"><?php _e("Article Language:","wprobot") ?> <?php _e("(articlesbase.com only)","wprobot") ?></td> 
				<td>
				<select name="wpr_eza_lang" id="wpr_eza_lang">
					<option value="en" <?php if($options['wpr_eza_lang']=="en"){_e('selected');}?>><?php _e("English","wprobot") ?></option>
					<option value="fr" <?php if($options['wpr_eza_lang']=="fr"){_e('selected');}?>><?php _e("French","wprobot") ?></option>
					<option value="es" <?php if($options['wpr_eza_lang']=="es"){_e('selected');}?>><?php _e("Spanish","wprobot") ?></option>
					<option value="pg" <?php if($options['wpr_eza_lang']=="pg"){_e('selected');}?>><?php _e("Portuguese","wprobot") ?></option>
					<option value="ru" <?php if($options['wpr_eza_lang']=="ru"){_e('selected');}?>><?php _e("Russian","wprobot") ?></option>
				</select>
				Tooltip<a target="_blank" class="tooltip" href="#">?<span><?php _e('This setting has no effect if Goarticles.com is selected as Article Source below! Only English is supported and used by default in this case.',"wprobot") ?></span></a>					
			</td> 			
			</tr>-->
			
			<tr valign="top"> 
				<td width="40%" scope="row"><?php _e("Pages:","wprobot") ?></td> 
				<td>
					<input name="wpr_eza_split" type="checkbox" value="yes" <?php if ($options['wpr_eza_split']=='yes') {echo "checked";} ?>/> <?php _e("Split long articles into several pages after every","wprobot") ?> <input size="5" name="wpr_eza_splitlength" type="text" value="<?php echo $options['wpr_eza_splitlength'];?>"/> <?php _e("characters.","wprobot") ?>
				</td> 
			</tr>		
			<tr valign="top"> 
				<td width="40%" scope="row"><?php _e("Strip All Links from...","wprobot") ?></td> 
				<td><input name="wpr_eza_striplinks" type="checkbox" id="wpr_eza_striplinks" value="yes" <?php if ($options['wpr_eza_striplinks']=='yes') {echo "checked";} ?>/> <?php _e("Article Body","wprobot") ?>
				<!--Tooltip--><a target="_blank" class="tooltip" href="#">?<span><?php _e('<strong>Warning:</strong> Removing links from the articles content is against the article directory and authors terms of use. Use this setting at your own risk!',"wprobot") ?></span></a>				
				</td> 
			</tr>				
		</table>		
	<?php
}
?>