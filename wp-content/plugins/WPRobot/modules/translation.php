<?php

function wpr_gettr( $url, $post, $referer = "") {

	$options = unserialize(get_option("wpr_options"));	
	$proxy == "";
	if($options["wpr_trans_use_proxies"] == "yes") {
		$proxies = str_replace("\r", "", $options["wpr_trans_proxies"]);
		$proxies = explode("\n", $proxies);  
		$rand = array_rand($proxies);	
		list($proxy,$proxytype,$proxyuser)=explode("|",$proxies[$rand]);
	}
	
   // echo $proxy."<br>";

	$blist[] = "Mozilla/5.0 (compatible; Konqueror/4.0; Microsoft Windows) KHTML/4.0.80 (like Gecko)";
	$blist[] = "Mozilla/5.0 (compatible; Konqueror/3.92; Microsoft Windows) KHTML/3.92.0 (like Gecko)";
	$blist[] = "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.0; WOW64; SLCC1; .NET CLR 2.0.50727; .NET CLR 3.0.04506; Media Center PC 5.0; .NET CLR 1.1.4322; Windows-Media-Player/10.00.00.3990; InfoPath.2";
	$blist[] = "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; .NET CLR 1.1.4322; InfoPath.1; .NET CLR 2.0.50727; .NET CLR 3.0.04506.30; Dealio Deskball 3.0)";
	$blist[] = "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; NeosBrowser; .NET CLR 1.1.4322; .NET CLR 2.0.50727)";
	$br = $blist[array_rand($blist)];
	if ( function_exists('curl_init') ) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_USERAGENT, $br);
			if($proxy != "") {
				curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, 1); 
				curl_setopt($ch, CURLOPT_PROXY, $proxy);
				if($proxyuser) {curl_setopt($ch, CURLOPT_PROXYUSERPWD, $proxyuser);}
				if($proxytype == "socks") {curl_setopt ($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);}
			}			
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt ($ch, CURLOPT_URL, $url);
		curl_setopt ($ch, CURLOPT_TIMEOUT, 60);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
		$buffer = curl_exec ($ch);
		if (!$buffer) {
			// remove dead
			/*if($options["wpr_trans_delete_proxies"] == "yes") {
				unset($proxies[$rand]);
				$proxies = implode("\r\n", $proxies);  
				$options["wpr_trans_proxies"] = $proxies;
				update_option("wpr_options", serialize($options));	
			}*/
			$return["error"]["module"] = "Translation";
			$return["error"]["reason"] = "cURL Error";
			$return["error"]["message"] = __("cURL Error ","wprobot").curl_errno($ch).": ".curl_error($ch);
			if(isset($proxy)) {$return["error"]["message"] .= " (Proxy $proxy)";}
			return $return;
		}				
		curl_close ($ch);
		return $buffer;
	} else { 				
		$return["error"]["module"] = "Translation";
		$return["error"]["reason"] = "cURL Error";
		$return["error"]["message"] = __("cURL is not installed on this server!","wprobot");	
		return $return;		
	}
}
function wpr_gtrns($text, $from, $to) {
	$url = "http://translate.google.com/translate_t";
	$ref = "http://translate.google.com/translate_t";
	$text=urlencode($text);
	if($to=="tw"||$to=="cn") {
		$to="zh-".strtoupper($to);
	}
	if($to=="nor") {$to=="no";}
	$postdata="hl=en&ie=UTF8&text=".$text."&langpair=".$from."%7C".$to;
	$page = wpr_gettr($url, $postdata, $ref);
	if(!empty($page["error"]["reason"])) {
		return $page;
	}
	
		$dom = new DOMDocument();
		@$dom->loadHTML($page);
		$xpath = new DOMXPath($dom);
		$paras = $xpath->query("//span[@id='result_box']"); // additional span? //span[@id='result_box']/span
		
		$para = $paras->item(0);
		$string = $dom->saveXml($para);	
		//$string = utf8_decode($string);
	if ($string!="") {
		return stripslashes(strip_tags($string));
	} else {
		return "";
	}
}

function wpr_trans_format($transtext) {
		$transtext =str_replace('&lt; / ','</',$transtext);
		$transtext =str_replace('&lt;/ ','</',$transtext);
		$transtext =str_replace('&lt; /','</',$transtext);
		$transtext =str_replace('&lt; ','<',$transtext);
		$transtext =str_replace('&lt;','<',$transtext);
		$transtext =str_replace('&gt;','>',$transtext);
		$transtext =str_replace('num = "','num="',$transtext);
		$transtext =str_replace('kw = "','kw="',$transtext);
		$transtext =str_replace('ebcat = "','ebcat="',$transtext);
		$transtext =str_replace('[Wprebay','[wprebay',$transtext);
		$transtext =str_replace('[/ ','[/',$transtext);
		$transtext =str_replace('Has_rating','has_rating',$transtext);
		//echo $transtext . "<br/>--------------------------------------------<br/>";
		//$transtext = html_entity_decode($transtext);
		//echo $transtext . "<br/>--------------------------------------------<br/>";		
		//$transtext = stripslashes($transtext);

		return $transtext;

}

function wpr_translate($text,$t1="",$t2="",$t3="",$t4="") {

	if(empty($text)) {
		$return["error"]["module"] = "Translation";
		$return["error"]["reason"] = "Translation Failed";
		$return["error"]["message"] = __("Empty text given.","wprobot");	
		return $return;		
	}
	
	if(empty($t2)) {
		$return["error"]["module"] = "Translation";
		$return["error"]["reason"] = "Translation Failed";
		$return["error"]["message"] = __("No target language specified.","wprobot");	
		return $return;		
	}		
	
	if($t1 == $t2) {
		$return["error"]["module"] = "Translation";
		$return["error"]["reason"] = "Translation Failed";
		$return["error"]["message"] = __("Same languages specified.","wprobot");	
		return $return;		
	}		
	
	// SAVE URLS
	//echo "<br/>------------------SAVE-----------------<br/>";
	preg_match_all('#href\s*=\s*"(.*)"#siU', $text, $matches, PREG_SET_ORDER);
	//print_r($matches);
	// SAVE SRC
	preg_match_all('#src\s*=\s*"(.*)"#siU', $text, $matches2, PREG_SET_ORDER);

	if ($t1!='no' && $t2!='no') {
		$transtext = wpr_gtrns($text, $t1, $t2);
		if(!empty($transtext["error"]["reason"])) {
			return $transtext;
		}
		
		$transtext = wpr_trans_format($transtext);
	}
	if ($t1!='no'  && $t2!='no'  && $t3!='no') {
		$transtext = wpr_gtrns($transtext, $t2, $t3);
		if(!empty($transtext["error"]["reason"])) {
			return $transtext;
		}			
		$transtext = wpr_trans_format($transtext);
	}
	if ($t1!='no'  && $t2!='no'  && $t3!='no'  && $t4!='no') {
		$transtext = wpr_gtrns($transtext, $t3, $t4);
		if(!empty($transtext["error"]["reason"])) {
			return $transtext;
		}			
		$transtext = wpr_trans_format($transtext);
	}	

	$pos = strpos($transtext, "302 Moved");
	$pos2 = strpos($transtext, "301 Moved");	
	$pos3 = strpos($transtext, "404 Not Found");							
	if ($pos === false && $pos2 === false && $pos3 === false) {
		$moved = 2;
	} else {	
		$moved = 1;
	}				 

	if ( !empty($transtext) && $transtext != ' ' && $moved != 1) {
		$transtext = html_entity_decode($transtext);	
		// REPLACE URLS
		//echo "<br/>------------------REPLACE-----------------<br/>";\s*=\s*
		//preg_match_all('#href = "(.*)"#siU', $transtext, $rmatches, PREG_SET_ORDER);
		preg_match_all('#href\s*=\s*"(.*)"#siU', $transtext, $rmatches, PREG_SET_ORDER);
		if ($rmatches) {
			$i=0;
			foreach($rmatches as $rmatch) {	// HREF = $match[1]	
				//echo "<br/>ORIGINAL: ".$matches[$i][1];
				//echo "<br/>REPLACEMENT: ".$rmatch[1];
				$transtext = str_replace($rmatch[1], $matches[$i][1], $transtext);
				$i++;
			}
		}		//print_r($rmatches);
		// REPLACE SRC
		//preg_match_all('#src ="(.*)"#siU', $transtext, $rmatches2, PREG_SET_ORDER);
		preg_match_all('#src\s*=\s*"(.*)"#siU', $transtext, $rmatches2, PREG_SET_ORDER);
		if ($rmatches2) {
			$i=0;
			foreach($rmatches2 as $rmatch2) {	// HREF = $match[1]	
				$transtext = str_replace($rmatch2[1], $matches2[$i][1], $transtext);
				$i++;
			}
		}

		return $transtext;
	} else {
		$return["error"]["module"] = "Translation";
		$return["error"]["reason"] = "Translation Failed";
		$return["error"]["message"] = __("The post could not be translated.","wprobot");	
		return $return;		
	}
}

function wpr_translate_partial($content) {

	$checkcontent = $content;
	
	preg_match_all('#\[translate(.*)\](.*)\[/translate\]#smiU', $checkcontent, $matches, PREG_SET_ORDER);
	if ($matches) {
		foreach($matches as $match) {
			$match[1] = substr($match[1], 1);
			$langs = explode("|", $match[1]);
			if(!empty($langs)) {

				if(empty($langs[0])) {$langs[0] = "no";}
				if(empty($langs[1])) {$langs[1] = "no";}
				if(empty($langs[2])) {$langs[2] = "no";}
				if(empty($langs[3])) {$langs[3] = "no";}
				$transcontent = wpr_translate($match[2],$langs[0],$langs[1],$langs[2],$langs[3]);

			}
			
			if(!empty($transcontent) && !is_array($transcontent)) {
				$content = str_replace($match[0], $transcontent, $content);	
				return $content;
			} else {
				$content = str_replace($match[0], "", $content);	
				return $content;
			}
		}
	} else {
		return $content;	
	}	
	
	if(!empty($transcontent) && !is_array($transcontent)) {
		return $transcontent;
	} else {
		return $content;
	}

}

function wpr_translation_options_default() {
	$options = array(
		//"wpr_trans_use_proxies" => "no",
		//"wpr_trans_proxies" => "",
		"wpr_trans_fail" => "post",
		"wpr_trans_delete_proxies" => "yes",
		"wpr_trans_titles" => "yes"
	);
	return $options;
}

function wpr_translation_options($options) {
	?>
	<h3 style="text-transform:uppercase;border-bottom: 1px solid #ccc;"><?php _e("Translation Options","wprobot") ?></h3>			
		<table class="addt" width="100%" cellspacing="2" cellpadding="5" class="editform"> 		
			<tr valign="top"> 
				<td width="40%" scope="row"><?php _e("If translation fails...","wprobot") ?></td> 
				<td>
				<select name="wpr_trans_fail" id="wpr_trans_fail">
					<option value="skip" <?php if($options['wpr_trans_fail']=="skip"){_e('selected');}?>><?php _e("Skip Post","wprobot") ?></option>
					<option value="post" <?php if($options['wpr_trans_fail']=="post"){_e('selected');}?>><?php _e("Create Untranslated Post","wprobot") ?></option>

				</select>				
				</td> 
			</tr>		
			<tr valign="top"> 
				<td width="40%" scope="row"><?php _e("Translate Titles","wprobot") ?></td> 
				<td>
				<input name="wpr_trans_titles" type="checkbox" id="wpr_trans_titles" value="yes" <?php if ($options['wpr_trans_titles']=='yes') {echo "checked";} ?>/> <?php _e("Yes","wprobot") ?>
				<!--Tooltip--><a class="tooltip" href="#">?<span><?php _e('Choose wether to translate post titles for translated content. If you are translating to a foreign language this has to be enabled or otherwise the titles will stay English. If using the translation feature for rewriting it is recommended to disable this setting in order to reduce requests to Google Translate.',"wprobot") ?></span></a>
				</td> 
			</tr>			
		</table>	
	<?php
}

?>