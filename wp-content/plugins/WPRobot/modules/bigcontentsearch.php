<?php

function wpr_bigcontentsearchrequest($keyword,$uniques) {
	global $wpdb,$wpr_table_posts;
	
	libxml_use_internal_errors(true);
	$options = unserialize(get_option("wpr_options"));	

	$user = $options['wpr_bcs_email'];
	$api = $options['wpr_bcs_pw'];	
	$spin = $options['wpr_bcs_spin'];	
	
	$keyword = str_replace( '"',"",$keyword );	
	$keyword = urlencode($keyword);
	
	$request = "https://members.bigcontentsearch.com/api/article_get_by_search_term";
	
	$ids_to_skip = array();

	if(is_array($uniques)) {
		foreach($uniques as $unique) {
			$ids_to_skip[] = $unique;
		}
	}
	
	$alluniques = $wpdb->get_results("SELECT unique_id FROM ".$wpr_table_posts." WHERE module = 'bigcontentsearch'", ARRAY_A);

	if(is_array($alluniques)) {
		foreach($alluniques as $unique) {
			$ids_to_skip[] = $unique["unique_id"];
		}
	}	

	$ids_to_skip = json_encode($ids_to_skip);
	
	$data = "username=" . $user . "&";
	$data .= "api_key=" . $api . "&";
	$data .= "search_term=" . $keyword . "&";
	$data .= "spin=" . $spin . "&";
	$data .= "ids_to_skip=" . $ids_to_skip;
	
	if ( function_exists('curl_init') ) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (compatible; Konqueror/4.0; Microsoft Windows) KHTML/4.0.80 (like Gecko)");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_URL, $request);
		curl_setopt($ch, CURLOPT_TIMEOUT, 60);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		$response = curl_exec($ch);
		if (!$response) {
			$return["error"]["module"] = "Big Content Search";
			$return["error"]["reason"] = "cURL Error";
			$return["error"]["message"] = __("cURL Error Number ","wprobot").curl_errno($ch).": ".curl_error($ch);	
			return $return;
		}		
		curl_close($ch);
	} else { 				
		$response = @file_get_contents($request);
		if (!$response) {
			$return["error"]["module"] = "Big Content Search";
			$return["error"]["reason"] = "cURL Error";
			$return["error"]["message"] = __("cURL is not installed on this server!","wprobot");	
			return $return;		
		}
	}
	
	$pxml = json_decode($response);	
    //print_r($pxml);
	if ($pxml === False) {
		$return["error"]["module"] = "Big Content Search";
		$return["error"]["reason"] = "JSON Error";
		$return["error"]["message"] = "JSON Response could not be loaded.";	
		return $return;				
	} else {
		return $pxml;
	}
}

function wpr_bigcontentsearchpost($keyword,$num,$start) {
	
	if(empty($keyword)) {
		$return["error"]["module"] = "Big Content Search";
		$return["error"]["reason"] = "No keyword";
		$return["error"]["message"] = __("No keyword specified.","wprobot");
		return $return;	
	}	
	
	$options = unserialize(get_option("wpr_options"));	
	$template = "{article}";

	$x = 0;
	$bcontent = array();$uniques = array();

	for ($i = 1; $i <= $num; $i++) {
		$pxml = wpr_bigcontentsearchrequest($keyword,$uniques);

		if(!empty($pxml->error_msg)) {
			$bcontent["error"]["module"] = "Big Content Search";
			$bcontent["error"]["reason"] = "Request fail";
			$bcontent["error"]["message"] = $pxml->error_msg;	
			return $bcontent;		
		} elseif ($pxml === False) {
			$bcontent["error"]["module"] = "Big Content Search";
			$bcontent["error"]["reason"] = "Request fail";
			$bcontent["error"]["message"] = __("API request could not be sent.","wprobot");	
			return $bcontent;	
		} elseif (is_array($pxml) && !empty($pxml["error"]["message"])) {
			return $pxml;				
		} elseif (isset($pxml->response->text)) {
			$title = $pxml->response->title;					
			$text = $pxml->response->text;				
			$unique = $pxml->response->uid;			
	
			$content = $template;
			$content = wpr_random_tags($content);
			$content = str_replace("{article}", $text, $content);
			$content = str_replace("{title}", $title, $content);		
				if(function_exists("wpr_translate_partial")) {
					$content = wpr_translate_partial($content);
				}
				if(function_exists("wpr_rewrite_partial")) {
					$content = wpr_rewrite_partial($content,$options);
				}	
				
			$bcontent[$x]["unique"] = $unique;
			$bcontent[$x]["title"] = $title;
			$bcontent[$x]["content"] = $content;
			$uniques[] = $unique;
			$x++;			
		}
	}
	
	if(empty($bcontent)) {
		$bcontent["error"]["module"] = "Big Content Search";
		$bcontent["error"]["reason"] = "No content";
		$bcontent["error"]["message"] = __("No (more) Big Content Search items found.","wprobot");	
		return $bcontent;		
	} else {
		return $bcontent;	
	}		
}

function wpr_bigcontentsearch_options_default() {
	$options = array(
		"wpr_bcs_email" => "",	
		"wpr_bcs_pw" => "",
		"wpr_bcs_spin" => 0,
	);
	return $options;
}

function wpr_bigcontentsearch_options($options) {
	?>
	<h3 style="text-transform:uppercase;border-bottom: 1px solid #ccc;"><?php _e("Big Content Search Options","wprobot") ?></h3>	
	
	<p><i><?php _e('Important: <a href="http://wprobot.net/go/bigcontentsearch" target="_blank">BigContentSearch.com Account required</a>',"wprobot") ?></i></p>	
	
		<table class="addt" width="100%" cellspacing="2" cellpadding="5" class="editform"> 
			<tr <?php if($options['wpr_bcs_email'] == "") {echo 'style="background:#F8E0E0;"';} ?> valign="top"> 
				<td width="40%" scope="row"><?php _e("Big Content Search Email:","wprobot") ?></td> 
				<td><input size="40" name="wpr_bcs_email" type="text" id="wpr_bcs_email" value="<?php echo $options['wpr_bcs_email'] ;?>"/>
				<!--Tooltip--><a target="_blank" class="tooltip" href="http://jvz9.com/c/55577/18783">?<span><?php _e('This setting is required for the Big Content Search module to work!<br/><br/><b>Click to go to the Big Content Search sign up page!</b>',"wprobot") ?></span></a>
			</td> 
			</tr>	
			<tr <?php if($options['wpr_bcs_pw'] == "") {echo 'style="background:#F8E0E0;"';} ?> valign="top"> 
				<td width="40%" scope="row"><?php _e("Big Content Search API Key:","wprobot") ?></td> 
				<td><input size="40" name="wpr_bcs_pw" type="text" id="wpr_bcs_pw" value="<?php echo $options['wpr_bcs_pw'] ;?>"/>
			</td> 
			</tr>				
			<!--<tr valign="top"> 
				<td width="40%" scope="row"><?php _e('Spin Content:',"wprobot") ?></td> 
				<td><input name="wpr_bcs_spin" type="checkbox" id="wpr_bcs_spin" value="1" <?php if ($options['wpr_bcs_spin']=='1') {echo "checked";} ?>/> <?php _e("Yes","wprobot") ?>
			</tr>	-->		
		</table>	
	<?php
}
?>