<?php

function wpr_ab_curl_post($url, $data, &$info){

  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_POST, true);
  curl_setopt($ch, CURLOPT_POSTFIELDS, wpr_ab_curl_postData($data));
  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_REFERER, $url);
  $html = trim(curl_exec($ch));
  curl_close($ch);

  return $html;
}

function wpr_ab_curl_postData($data){

  $fdata = "";
  foreach($data as $key => $val){
    $fdata .= "$key=" . urlencode($val) . "&";
  }

  return $fdata;
}

function wpr_articlebuilderpost($keyword,$num,$start) {

	$keyword = explode(">", $keyword);
	$category = str_replace('"', "", $keyword[0]);
	$kws = explode(",", $keyword[1]);	

	if(empty($category)) {
		$return["error"]["module"] = "Article Builder";
		$return["error"]["reason"] = "No keyword";
		$return["error"]["message"] = __("No keyword specified.","wprobot");
		return $return;	
	}	
	/*$allcats = array(0 => "acid reflux", 1 => "acne", 2 => "acupuncture", 3 => "affiliate marketing", 4 => "aging", 5 => "allergies", 6 => "anxiety", 7 => "arthritis", 8 => "article marketing", 9 => "asthma", 10 => "auto repair", 11 => "back pain", 12 => "basketball", 13 => "beauty", 14 => "blogging", 15 => "camping", 16 => "cancer", 17 => "car shopping", 18 => "carpet cleaning", 19 => "cats", 20 => "coffee", 21 => "college", 22 => "cooking", 23 => "cosmetic surgery", 24 => "coupons", 25 => "credit cards", 26 => "credit repair", 27 => "debt consolidation", 28 => "dental care", 29 => "depression", 30 => "diabetes", 31 => "dog training", 32 => "dogs", 33 => "email marketing", 34 => "employment", 35 => "facebook marketing", 36 => "fashion", 37 => "fishing", 38 => "fitness", 39 => "forex", 40 => "furniture", 41 => "gardening", 42 => "gold", 43 => "golf", 44 => "green energy", 45 => "hair care", 46 => "hair loss", 47 => "hemorrhoids", 48 => "home business", 49 => "home improvement", 50 => "home security", 51 => "homeschooling", 52 => "insurance - auto", 53 => "insurance - general", 54 => "insurance - health", 55 => "insurance - home owner's", 56 => "insurance - life", 57 => "interior design", 58 => "internet marketing", 59 => "ipad", 60 => "iphone", 61 => "jewelry", 62 => "juicing", 63 => "landscaping", 64 => "lawyers", 65 => "massage", 66 => "memory", 67 => "mobile marketing", 68 => "muscle building", 69 => "network marketing", 70 => "nutrition", 71 => "online shopping", 72 => "organic gardening", 73 => "panic attacks", 74 => "parenting", 75 => "payday loans", 76 => "personal bankruptcy", 77 => "personal development", 78 => "personal finance", 79 => "personal injury", 80 => "pest control", 81 => "photography", 82 => "plumbing", 83 => "pregnancy", 84 => "quit smoking", 85 => "real estate - buying", 86 => "real estate - commercial", 87 => "real estate - selling", 88 => "roofing", 89 => "search engine optimization", 90 => "skin care", 91 => "sleep apnea", 92 => "snoring", 93 => "soccer", 94 => "social media marketing", 95 => "solar energy", 96 => "stock market", 97 => "stress", 98 => "teeth whitening", 99 => "tinnitus", 100 => "travel", 101 => "video games", 102 => "video marketing", 103 => "web design", 104 => "web hosting", 105 => "weddings", 106 => "weight loss", 107 => "wine", 108 => "yeast infection" );
	
	foreach($allcats as $allcat) {
		$carr[$allcat] = $allcat;
	}
	
	print_r($carr);
	
	if(!in_array($category, $allcats)) {
		$return["error"]["module"] = "Article Builder";
		$return["error"]["reason"] = "No keyword";
		$return["error"]["message"] = __('Article Builder only supports a defined lists of categories which can be used as keywords in WP Robot. Please enter one of the following: "affiliate marketing","article marketing","email marketing","forex","home business","internet marketing","mobile marketing","network marketing","search engine optimization","social media marketing","credit cards","credit repair","insurance - auto","insurance - general","insurance - life","personal bankruptcy","personal finance","real estate - buying","real estate - commercial","stock market","acne","aging","allergies","anxiety","arthritis","asthma","back pain","beauty","cancer","cosmetic surgery","depression","diabetes","fitness","hair care","hair loss","hemorrhoids","insurance - health","juicing","memory","muscle building","nutrition","panic attacks","personal development","quit smoking","skin care","snoring","stress","teeth whitening","tinnitus","weight loss","cooking","dog training","gardening","home improvement","insurance - home owner\'s","landscaping","organic gardening","parenting","plumbing","pregnancy","fishing","golf","photography","travel","jewelry","real estate - selling","weddings","blogging","green energy","web design","web hosting"',"wprobot");
		return $return;		
	}*/
	
	$options = unserialize(get_option("wpr_options"));	
	$template = "{article}";

	$url = 'http://articlebuilder.net/api.php';

	$data = array();
	$data['action'] = 'authenticate';
	$data['format'] = 'php';
	$data['username'] = $options['wpr_ab_email'];
	$data['password'] = $options['wpr_ab_pw'];

	$output = unserialize(wpr_ab_curl_post($url, $data, $info));

	/*$session = $output['session'];
	$data = array();
	$data['session'] = $session;
	$data['action'] = 'categories';
	$data['format'] = 'php';

	$asdf = unserialize(wpr_ab_curl_post($url, $data, $info));	
	
	print_r($asdf);	*/

	if($output['success']=='true'){
		$session = $output['session'];

		$data = array();
		$data['session'] = $session;
		$data['action'] = 'categories';
		$cats = wpr_ab_curl_post($url, $data, $info);	
		if (strpos($cats, $category) === false) {
			$return["error"]["module"] = "Article Builder";
			$return["error"]["reason"] = "No keyword";
			$return["error"]["message"] = __('Article Builder only supports a defined lists of categories which can be used as keywords in WP Robot. See <a href="http://wprobot.net/go/articlebuilder">articlebuilder.net for a list of supported keywords</a>.',"wprobot");
			return $return;				
		}
		
		$data = array();
		$data['session'] = $session;
		$data['format'] = 'php';
		$data['action'] = 'buildArticle';  
		$data['apikey'] = $apikey;
		$data['category'] = $category;

		$subs = "";
		if(is_array($kws)) {
			foreach($kws as $kw) {
				$subs .= $kw . "\n";
			}
		}
		$data['subtopics'] = $subs;
		$data['wordcount'] = $options['wpr_ab_wordcount'];
		$data['superspun'] = $options['wpr_ab_superspun'];
//print_r($data);
		$posts = array();
		for ($i = 0; $i < $num; $i++) {

			$output = wpr_ab_curl_post($url, $data, $info);
			$output = unserialize($output);
			
			if($output['success']=='true'){
			
				$arts = preg_split('/\r\n|\r|\n/', $output['output'], 2);
				$art = str_replace("\r", "<br>", str_replace("\n\n", "<p>", $arts[1]));
				
				$title = $arts[0];
				
				$post = $template;				
				$post = wpr_random_tags($post);		

				$post = str_replace("{article}", $art, $post);							
		
				if(function_exists("wpr_translate_partial")) {
					$post = wpr_translate_partial($post);
				}
				if(function_exists("wpr_rewrite_partial")) {
					$post = wpr_rewrite_partial($post,$options);
				}

				if($options['wpr_ab_comments'] == 1) {

					$comments = array();
					$cnum = rand(2,5);
				
					for ($x = 1; $x <= $cnum; $x++) {

						$data = array();
						$data['session'] = $session;
						$data['format'] = 'php';
						$data['action'] = 'getTip';  
						$data['apikey'] = $apikey;
						$data['category'] = $category;	
						//$data['keywords'] = $title;	
						$data['superspun'] = $options['wpr_ab_superspun'];
						$coutput = wpr_ab_curl_post($url, $data, $info);
						
						$coutput = unserialize($coutput);
						if($coutput['success']=='true'){
							$tip = $coutput['output'];
							$comments[$x]["author"] = $category . " tips ".rand(99,9999);
							$comments[$x]["content"] = $tip;	
							//$x++;						
						}
					}
					//print_r($comments);			
				}
					
				$posts[$i]["unique"] = rand(0, 999999);
				$posts[$i]["title"] = $title;
				$posts[$i]["content"] = $post;	
				$posts[$i]["comments"] = $comments;	
			} else {
				$return["error"]["module"] = "Article Builder";
				$return["error"]["reason"] = "No keyword";
				$return["error"]["message"] = $output["error"];
				return $return;	
			}
		}
		if(empty($posts)) {
			$posts["error"]["module"] = "Article Builder";
			$posts["error"]["reason"] = "No content";
			$posts["error"]["message"] = __("No Article Builder content found.","wprobot");	
			return $posts;			
		} else {
			return $posts;	
		}			
	} else {
		$return["error"]["module"] = "Article Builder";
		$return["error"]["reason"] = "No keyword";
		$return["error"]["message"] = $output["error"];
		return $return;	
	}	
}

function wpr_articlebuilder_options_default() {
	$options = array(
		"wpr_ab_email" => "",	
		"wpr_ab_pw" => "",
		"wpr_ab_wordcount" => 300,
		"wpr_ab_superspun" => 0,
		"wpr_ab_comments" => 0,
	);
	return $options;
}

function wpr_articlebuilder_options($options) {
	?>
	<h3 style="text-transform:uppercase;border-bottom: 1px solid #ccc;"><?php _e("Article Builder Options","wprobot") ?></h3>	
	
	<p><i><?php _e('Important: <a href="http://paydotcom.net/r/114431/thoefter/26922760/" target="_blank">ArticleBuilder.net Account required</a>, please see instructions on <a href="http://wprobot.net/documentation/#97" target="_blank">how to use this module</a>!',"wprobot") ?></i></p>	
	
		<table class="addt" width="100%" cellspacing="2" cellpadding="5" class="editform"> 
			<tr <?php if($options['wpr_ab_email'] == "") {echo 'style="background:#F8E0E0;"';} ?> valign="top"> 
				<td width="40%" scope="row"><?php _e("Article Builder Username:","wprobot") ?></td> 
				<td><input size="40" name="wpr_ab_email" type="text" id="wpr_ab_email" value="<?php echo $options['wpr_ab_email'] ;?>"/>
				<!--Tooltip--><a target="_blank" class="tooltip" href="http://paydotcom.net/r/114431/thoefter/26922760/">?<span><?php _e('This setting is required for the Article Builder module to work!<br/><br/><b>Click to go to the Article Builder sign up page!</b>',"wprobot") ?></span></a>
			</td> 
			</tr>	
			<tr <?php if($options['wpr_ab_pw'] == "") {echo 'style="background:#F8E0E0;"';} ?> valign="top"> 
				<td width="40%" scope="row"><?php _e("Article Builder Password:","wprobot") ?></td> 
				<td><input size="40" name="wpr_ab_pw" type="text" id="wpr_ab_pw" value="<?php echo $options['wpr_ab_pw'] ;?>"/>
			</td> 
			</tr>				
			<tr valign="top"> 
				<td width="40%" scope="row"><?php _e('SuperSpun Content:',"wprobot") ?></td> 
				<td><input name="wpr_ab_superspun" type="checkbox" id="wpr_ab_superspun" value="1" <?php if ($options['wpr_ab_superspun']=='1') {echo "checked";} ?>/> <?php _e("Yes","wprobot") ?>			
			</tr>
			<tr valign="top"> 
				<td width="40%" scope="row"><?php _e('Add Comments:',"wprobot") ?></td> 
				<td><input name="wpr_ab_comments" type="checkbox" id="wpr_ab_comments" value="1" <?php if ($options['wpr_ab_comments']=='1') {echo "checked";} ?>/> <?php _e('Yes - randomly add 2-5 short comments from <a href="http://wprobot.net/go/articlebuilder" target="_blank">Articlebuilder.net</a> to articles.',"wprobot") ?>
			</tr>				
			<tr valign="top"> 
				<td width="40%" scope="row"><?php _e("Article Length:","wprobot") ?></td> 
				<td>
				<select name="wpr_ab_wordcount" id="wpr_ab_wordcount">
							<option value="300" <?php if($options['wpr_ab_wordcount']=="300"){_e('selected');}?>><?php _e("300","wprobot") ?></option>
							<option value="400" <?php if($options['wpr_ab_wordcount']=="400"){_e('selected');}?>><?php _e("400","wprobot") ?></option>
							<option value="500" <?php if($options['wpr_ab_wordcount']=="500"){_e('selected');}?>><?php _e("500","wprobot") ?></option>
							<option value="600" <?php if($options['wpr_ab_wordcount']=="600"){_e('selected');}?>><?php _e("600","wprobot") ?></option>
							<option value="700" <?php if($options['wpr_ab_wordcount']=="700"){_e('selected');}?>><?php _e("700","wprobot") ?></option>
							<option value="800" <?php if($options['wpr_ab_wordcount']=="800"){_e('selected');}?>><?php _e("800","wprobot") ?></option>
							<option value="900" <?php if($options['wpr_ab_wordcount']=="900"){_e('selected');}?>><?php _e("900","wprobot") ?></option>
							<option value="1000" <?php if($options['wpr_ab_wordcount']=="1000"){_e('selected');}?>><?php _e("1000","wprobot") ?></option>
				</select>
			</td> 
			</tr>			
		</table>	
	<?php
}
?>