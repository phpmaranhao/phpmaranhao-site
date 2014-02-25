<?php

function wpr_yahooanswersrequest($keyword,$num,$start,$yapcat) {
	libxml_use_internal_errors(true);
	$options = unserialize(get_option("wpr_options"));	
	$appid = $options['wpr_yap_appkey'];
	$region = $options['wpr_yap_lang'];
	$yapcat = $options['wpr_yap_cat'];

	$keyword = str_replace(" ", "+", $keyword);			
	//$keyword = urlencode($keyword);
	
    $request = "http://answers.yahooapis.com/AnswersService/V1/questionSearch?region=".$region."&appid=".$appid."&query=".$keyword."&type=resolved&start=".$start."&results=$num";
    if(!empty($yapcat)) {
		$request .= "&category_id=$yapcat";
	}
	
//echo $request . "<br>";

	if ( function_exists('curl_init') ) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (compatible; Konqueror/4.0; Microsoft Windows) KHTML/4.0.80 (like Gecko)");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_URL, $request);
		curl_setopt($ch, CURLOPT_TIMEOUT, 60);
		$response = curl_exec($ch);
		if (!$response) {
			$return["error"]["module"] = "Yahoo Answers";
			$return["error"]["reason"] = "cURL Error";
			$return["error"]["message"] = __("cURL Error Number ","wprobot").curl_errno($ch).": ".curl_error($ch);	
			return $return;
		}		
		curl_close($ch);
	} else { 				
		$response = @file_get_contents($request);
		if (!$response) {
			$return["error"]["module"] = "Yahoo Answers";
			$return["error"]["reason"] = "cURL Error";
			$return["error"]["message"] = __("cURL is not installed on this server!","wprobot");	
			return $return;		
		}
	}
    //echo $response;
	
	$pxml = simplexml_load_string($response);

	
	if ($pxml === False) {
		$emessage = __("Failed loading XML, errors returned: ","wprobot");
		foreach(libxml_get_errors() as $error) {
			$emessage .= $error->message . ", ";
		}	
		libxml_clear_errors();
		$return["error"]["module"] = "Yahoo Answers";
		$return["error"]["reason"] = "XML Error";
		$return["error"]["message"] = $emessage;	
		return $return;			
	} else {
		return $pxml;
	}
}

function wpr_yap_getanswers($qid,$answercount) {

	$options = unserialize(get_option("wpr_options"));	
	$appid = $options['wpr_yap_appkey'];
	$requesturl = 'http://answers.yahooapis.com/AnswersService/V1/getQuestion?appid='.$appid.'&question_id='.$qid;

	if ( function_exists('curl_init') ) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (compatible; Konqueror/4.0; Microsoft Windows) KHTML/4.0.80 (like Gecko)");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_URL, $requesturl);
		curl_setopt($ch, CURLOPT_TIMEOUT, 60);
		$response = curl_exec($ch);
		curl_close($ch);
	} else { 				
		$response = @file_get_contents($requesturl);
	}
    
    if ($response === False) {
    } else {
        $commentsFeed = simplexml_load_string($response);
    }	
	
	$answers = array();
	$i = 0;
	
	if(isset($commentsFeed->Question->Answers->Answer)) {
		foreach ($commentsFeed->Question->Answers->Answer as $answer) {
			$answer->Content = str_replace("$", "$ ", $answer->Content); 
			$answers[$i]["author"] = $answer->UserNick;
			$answers[$i]["content"] = $answer->Content;	
			$i++;	
		}
	}
	
	return $answers;
}

function wpr_yahooanswerspost($keyword,$num,$start,$yapcat,$getcomments) {
	global $wpdb,$wpr_table_templates;
	
	if($keyword == "") {
		$return["error"]["module"] = "Yahoo Answers";
		$return["error"]["reason"] = "No keyword";
		$return["error"]["message"] = __("No keyword specified.","wprobot");
		return $return;	
	}	
	
	$options = unserialize(get_option("wpr_options"));	
	$template = $wpdb->get_var("SELECT content FROM " . $wpr_table_templates . " WHERE type = 'yahooanswers'");	
	if($template == false || empty($template)) {
		$return["error"]["module"] = "Yahoo Answers";
		$return["error"]["reason"] = "No template";
		$return["error"]["message"] = __("Module Template does not exist or could not be loaded.","wprobot");
		return $return;	
	}	
	$pxml = wpr_yahooanswersrequest($keyword,$num,$start,$yapcat);
	if(!empty($pxml["error"])) {return $pxml;}
	$x = 0;
	$posts = array();
	
	if ($pxml === False) {
		$posts["error"]["module"] = "Yahooanswers";
		$posts["error"]["reason"] = "Request fail";
		$posts["error"]["message"] = __("API request could not be sent.","wprobot");	
		return $posts;		
	} else {
		if (isset($pxml->Question)) {
			foreach($pxml->Question as $question) {
			
				$attrs = $question->attributes();
				$qid = $question['id']; 			
				$title = $question->Subject;
				$content = $question->Content;
				$url = $question->Link;
				$user = $question->UserNick;
				$answercount = $question->NumAnswers;
				
				if ($options['wpr_ya_striplinks_q']=='yes') {$content = wpr_strip_selected_tags($content, array('a','iframe','script'));}
				
				$post = $template;				
				$post = wpr_random_tags($post);
				
				// Answers
				$answerpost = "";
				preg_match('#\{answers(.*)\}#iU', $post, $rmatches);			
				if ($rmatches[0] != false || $getcomments == 1 || $options['wpr_show_comments_settings'] != "Yes") {
					$answers = wpr_yap_getanswers($qid,$answercount);				
				}
				if ($rmatches[0] != false && !empty($answers)) {
					$answernum = substr($rmatches[1], 1);
					for ($i = 0; $i < $answercount; $i++) {
						if($i == $answernum) {break;} else {	
							$answerpost .= "<p><i>Answer by ".$answers[$i]["author"]."</i><br/>".$answers[$i]["content"]."</p>";
							// Remove posted answer from comments array
							unset($answers[$i]);
						}
					}
					$answers = array_values($answers);
					$post = str_replace($rmatches[0], $answerpost, $post);				
				} else {
					$post = str_replace($rmatches[0], "", $post);					
				}				
				
				$content = str_replace("$", "$ ", $content); 
				$post = str_replace("{question}", $content, $post);							
				$noqkeyword = str_replace('"', '', $keyword);
				$post = str_replace("{keyword}", $noqkeyword, $post);
				$post = str_replace("{Keyword}", ucwords($noqkeyword), $post);					
				$post = str_replace("{url}", $url, $post);	
				$post = str_replace("{user}", $user, $post);	
				$post = str_replace("{title}", $title, $post);	
					if(function_exists("wpr_translate_partial")) {
						$post = wpr_translate_partial($post);
					}
					if(function_exists("wpr_rewrite_partial")) {
						$post = wpr_rewrite_partial($post,$options);
					}					
				$posts[$x]["unique"] = $qid;
				$posts[$x]["title"] = $title;
				$posts[$x]["content"] = $post;	
				$posts[$x]["comments"] = $answers;	
				$x++;
			}
			
			if(empty($posts)) {
				$posts["error"]["module"] = "Yahooanswers";
				$posts["error"]["reason"] = "No content";
				$posts["error"]["message"] = __("No (more) Yahoo Answers content found.","wprobot");	
				return $posts;			
			} else {
				return $posts;	
			}				
			
		} else {
			if (isset($pxml->Message)) {
				$message = __('There was a problem with your API request. This is the error Yahoo returned:',"wprobot").' <b>'.$pxml->Message.'</b>';	
				$posts["error"]["module"] = "Yahooanswers";
				$posts["error"]["reason"] = "API fail";
				$posts["error"]["message"] = $message;	
				return $posts;				
			} else {
				$posts["error"]["module"] = "Yahooanswers";
				$posts["error"]["reason"] = "No content";
				$posts["error"]["message"] = __("No (more) Yahoo Answers content found.","wprobot");	
				return $posts;				
			}			
		}
	}	
}

function wpr_yahooanswers_options_default() {
	$options = array(
		"wpr_yap_appkey" => "",
		"wpr_yap_lang" => "us",
		"wpr_yap_yatos" => "no",
		"wpr_ya_striplinks_q" => "no",
		"wpr_ya_striplinks_a" => "no",
		"wpr_yap_cat" => ""
	);
	return $options;
}

function wpr_yahooanswers_options($options) {
	?>
	<h3 style="text-transform:uppercase;border-bottom: 1px solid #ccc;"><?php _e("Yahoo Answers Options","wprobot") ?></h3>	
		<table class="addt" width="100%" cellspacing="2" cellpadding="5" class="editform"> 
			<tr <?php if($options['wpr_yap_appkey'] == "") {echo 'style="background:#F8E0E0;"';} ?> valign="top"> 
				<td width="40%" scope="row"><?php _e("Yahoo Application ID:","wprobot") ?></td> 
				<td><input size="40" name="wpr_yap_appkey" type="text" id="wpr_yap_appkey" value="<?php echo $options['wpr_yap_appkey'] ;?>"/>
				<!--Tooltip--><a target="_blank" class="tooltip" href="http://developer.yahoo.com/answers/">?<span><?php _e('This setting is required for the Yahoo Answers module to work!<br/><br/><b>Click to go to the Yahoo API sign up page!</b>',"wprobot") ?></span></a>
			</td> 
			</tr>			
			<tr valign="top"> 
				<td width="40%" scope="row"><?php _e('Add "Powered by Yahoo! Answers" text to footer?',"wprobot") ?></td> 
				<td><input name="wpr_yap_yatos" type="checkbox" id="wpr_yap_yatos" value="yes" <?php if ($options['wpr_yap_yatos']=='yes') {echo "checked";} ?>/> <?php _e("Yes","wprobot") ?>
				<!--Tooltip--><a class="tooltip" href="#">?<span><?php _e('By the Yahoo Answers TOS it is required that you display the text \'Powered by Yahoo! Answers\' on pages you use the API on. If you disable this option you can display the text anywhere else on your weblog.',"wprobot") ?></span></a></td> 
			</tr>	
			<tr valign="top"> 
				<td width="40%" scope="row"><?php _e("Country:","wprobot") ?></td> 
				<td>
				<select name="wpr_yap_lang" id="wpr_yap_lang">
							<option value="us" <?php if($options['wpr_yap_lang']=="us"){_e('selected');}?>><?php _e("USA","wprobot") ?></option>
							<option value="uk" <?php if($options['wpr_yap_lang']=="uk"){_e('selected');}?>><?php _e("United Kingdom","wprobot") ?></option>								
							<option value="ca" <?php if($options['wpr_yap_lang']=="ca"){_e('selected');}?>><?php _e("Canada","wprobot") ?></option>	
							<option value="au" <?php if($options['wpr_yap_lang']=="au"){_e('selected');}?>><?php _e("Australia","wprobot") ?></option>			
							<option value="de" <?php if($options['wpr_yap_lang']=="de"){_e('selected');}?>><?php _e("Germany","wprobot") ?></option>
							<option value="fr" <?php if($options['wpr_yap_lang']=="fr"){_e('selected');}?>><?php _e("France","wprobot") ?></option>
							<option value="it" <?php if($options['wpr_yap_lang']=="it"){_e('selected');}?>><?php _e("Italy","wprobot") ?></option>	
							<option value="es" <?php if($options['wpr_yap_lang']=="es"){_e('selected');}?>><?php _e("Spain","wprobot") ?></option>		
							<option value="br" <?php if($options['wpr_yap_lang']=="br"){_e('selected');}?>><?php _e("Brazil","wprobot") ?></option>
							<option value="ar" <?php if($options['wpr_yap_lang']=="ar"){_e('selected');}?>><?php _e("Argentina","wprobot") ?></option>
							<option value="mx" <?php if($options['wpr_yap_lang']=="mx"){_e('selected');}?>><?php _e("Mexico","wprobot") ?></option>
							<option value="sg" <?php if($options['wpr_yap_lang']=="sg"){_e('selected');}?>><?php _e("Singapore","wprobot") ?></option>								
				</select>
			</td> 
			</tr>	

			<tr valign="top"> 
				<td width="40%" scope="row"><?php _e("Category:","wprobot") ?></td> 
				<td>
				<select name="wpr_yap_cat">				
					<option <?php if($options['wpr_yap_cat']==""){_e('selected');}?> value=""><?php _e("All","wprobot") ?></option>
					<option <?php if($options['wpr_yap_cat']=="396545012"){_e('selected');}?> value="396545012"><?php _e("Arts &amp; Humanities","wprobot") ?></option>
					<option <?php if($options['wpr_yap_cat']=="396545144"){_e('selected');}?> value="396545144"><?php _e("Beauty &amp; Style","wprobot") ?></option>
					<option <?php if($options['wpr_yap_cat']=="396545013"){_e('selected');}?> value="396545013"><?php _e("Business &amp; Finance","wprobot") ?></option>
					<option <?php if($options['wpr_yap_cat']=="396545311"){_e('selected');}?> value="396545311"><?php _e("Cars &amp; Transportation","wprobot") ?></option>
					<option <?php if($options['wpr_yap_cat']=="396545660"){_e('selected');}?> value="396545660"><?php _e("Computers &amp; Internet","wprobot") ?></option>
					<option <?php if($options['wpr_yap_cat']=="396545014"){_e('selected');}?> value="396545014"><?php _e("Consumer Electronics","wprobot") ?></option>
					<option <?php if($options['wpr_yap_cat']=="396545327"){_e('selected');}?> value="396545327"><?php _e("Dining Out","wprobot") ?></option>
					<option <?php if($options['wpr_yap_cat']=="396545015"){_e('selected');}?> value="396545015"><?php _e("Education &amp; Reference","wprobot") ?></option>
					<option <?php if($options['wpr_yap_cat']=="396545016"){_e('selected');}?> value="396545016"><?php _e("Entertainment &amp; Music","wprobot") ?></option>
					<option <?php if($options['wpr_yap_cat']=="396545451"){_e('selected');}?> value="396545451"><?php _e("Environment","wprobot") ?></option>
					<option <?php if($options['wpr_yap_cat']=="396545433"){_e('selected');}?> value="396545433"><?php _e("Family &amp; Relationships","wprobot") ?></option>
					<option <?php if($options['wpr_yap_cat']=="396545367"){_e('selected');}?> value="396545367"><?php _e("Food &amp; Drink","wprobot") ?></option>
					<option <?php if($options['wpr_yap_cat']=="396545019"){_e('selected');}?> value="396545019"><?php _e("Games &amp; Recreation","wprobot") ?></option>
					<option <?php if($options['wpr_yap_cat']=="396545018"){_e('selected');}?> value="396545018"><?php _e("Health","wprobot") ?></option>
					<option <?php if($options['wpr_yap_cat']=="396545394"){_e('selected');}?> value="396545394"><?php _e("Home &amp; Garden","wprobot") ?></option>
					<option <?php if($options['wpr_yap_cat']=="396545401"){_e('selected');}?> value="396545401"><?php _e("Local Businesses","wprobot") ?></option>
					<option <?php if($options['wpr_yap_cat']=="396545439"){_e('selected');}?> value="396545439"><?php _e("News &amp; Events","wprobot") ?></option>
					<option <?php if($options['wpr_yap_cat']=="396545443"){_e('selected');}?> value="396545443"><?php _e("Pets","wprobot") ?></option>
					<option <?php if($options['wpr_yap_cat']=="396545444"){_e('selected');}?> value="396545444"><?php _e("Politics &amp; Government","wprobot") ?></option>
					<option <?php if($options['wpr_yap_cat']=="396546046"){_e('selected');}?> value="396546046"><?php _e("Pregnancy &amp; Parenting","wprobot") ?></option>
					<option <?php if($options['wpr_yap_cat']=="396545122"){_e('selected');}?> value="396545122"><?php _e("Science &amp; Mathematics","wprobot") ?></option>
					<option <?php if($options['wpr_yap_cat']=="396545301"){_e('selected');}?> value="396545301"><?php _e("Social Science","wprobot") ?></option>
					<option <?php if($options['wpr_yap_cat']=="396545454"){_e('selected');}?> value="396545454"><?php _e("Society &amp; Culture","wprobot") ?></option>
					<option <?php if($options['wpr_yap_cat']=="396545213"){_e('selected');}?> value="396545213"><?php _e("Sports","wprobot") ?></option>
					<option <?php if($options['wpr_yap_cat']=="396545469"){_e('selected');}?> value="396545469"><?php _e("Travel","wprobot") ?></option>
					<option <?php if($options['wpr_yap_cat']=="396546089"){_e('selected');}?> value="396546089"><?php _e("Yahoo! Products","wprobot") ?></option>				
				</select>
			</td> 
			</tr>	
			
			<tr valign="top"> 
				<td width="40%" scope="row"><?php _e("Strip All Links from...","wprobot") ?></td> 
				<td><input name="wpr_ya_striplinks_q" type="checkbox" id="wpr_ya_striplinks_q" value="yes" <?php if ($options['wpr_ya_striplinks_q']=='yes') {echo "checked";} ?>/> <?php _e("Questions","wprobot") ?><br/>
				<input name="wpr_ya_striplinks_a" type="checkbox" id="wpr_ya_striplinks_a" value="yes" <?php if ($options['wpr_ya_striplinks_a']=='yes') {echo "checked";} ?>/> <?php _e("Answers","wprobot") ?></td> 
			</tr>				
		</table>	
	<?php
}

function wpr_yap_showtos() {
	$options = unserialize(get_option("wpr_options"));	
	if ($options['wpr_yap_yatos'] == 'yes') {
		echo '<p>Powered by Yahoo! Answers</p>';
	}
}
add_action('wp_footer', 'wpr_yap_showtos'); 
?>