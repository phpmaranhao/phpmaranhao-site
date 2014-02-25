<?php

function wpr_clickbankpost($keyword,$num,$start,$optional="",$comments="") {
	global $wpdb,$wpr_table_templates;

	if($keyword == "") {
		$return["error"]["module"] = "Clickbank";
		$return["error"]["reason"] = "No keyword";
		$return["error"]["message"] = __("No keyword specified.","wprobot");
		return $return;	
	}
	
	$template = $wpdb->get_var("SELECT content FROM " . $wpr_table_templates . " WHERE type = 'clickbank'");
	if($template == false || empty($template)) {
		$return["error"]["module"] = "Clickbank";
		$return["error"]["reason"] = "No template";
		$return["error"]["message"] = __("Module Template does not exist or could not be loaded.","wprobot");
		return $return;	
	}			
	$options = unserialize(get_option("wpr_options"));
	$affid = $options['wpr_cb_affkey'];if ($affid == '') { $affid = 'lun4tic' ;}
	$keyword = str_replace( '"',"",$keyword );	
	$keyword = str_replace( " ","+",$keyword );
	
	$page = $start / 50;
	$page = (string) $page; 
	$page = explode(".", $page);	
	$page=(int)$page[0];	
	$page++;	

	if($page == 0 || empty($page)) {$page = 1;}
	$prep = floor($start / 50);
	$numb = $start - $prep * 50;		
	
	$search_url = "http://www.clickbank.com/mkplSearchResult.htm?dores=true&includeKeywords=$keyword&firstResult=$start";
	$search_url = "http://www.cbengine.com/res/search.cfm?search=1&op=AND&field=any&ap=0&ao=Or&am=0&sc=rank&so=asc&mode=engine&custom1=mntm&kw=$keyword&pg=$page";
	
	$wpr_cb_cat = $options['wpr_cb_cat'];
	if(!empty($wpr_cb_cat)) {$search_url .= "&cat=".$wpr_cb_cat.".all";}
	
	$posts = array();

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
			$return["error"]["module"] = "Clickbank";
			$return["error"]["reason"] = "cURL Error";
			$return["error"]["message"] = __("cURL Error Number ","wprobot").curl_errno($ch).": ".curl_error($ch);	
			return $return;
		}		
		curl_close($ch);
	} else { 				
		$html = @file_get_contents($search_url);
		if (!$html) {
			$return["error"]["module"] = "Clickbank";
			$return["error"]["reason"] = "cURL Error";
			$return["error"]["message"] = __("cURL is not installed on this server!","wprobot");	
			return $return;		
		}
	}

	// parse the html into a DOMDocument  

	$dom = new DOMDocument();
	@$dom->loadHTML($html);
	
	$xpath1 = new DOMXPath($dom);			
	$paras1 = $xpath1->query("//a[@class='a18 dblue']");

	$end = $numb + $num;
	$x = 0;
		if($paras1->length == 0) {
			$posts["error"]["module"] = "Clickbank";
			$posts["error"]["reason"] = "No content";
			$posts["error"]["message"] = __("No (more) Clickbank products found.","wprobot");	
			return $posts;		
		}	
	
	if($end > $paras1->length) { $end = $paras1->length;}
	for ($i = $numb;  $i < $end; $i++ ) {	
	//for ($i = $numb;  $i < $paras1->length; $i++ ) {		//$paras->length
		/*if($i < $num) {
			$para1 = $paras1->item($i);
			$urlt = $para1->textContent;	

			if($urlt == '' | $urlt == null) {
				$posts["error"]["module"] = "Clickbank";
				$posts["error"]["reason"] = "No content";
				$posts["error"]["message"] = __("No (more) Clickbank products found.","wprobot");	
				return $posts;	
			} else {*/

				$xpath2 = new DOMXPath($dom);
				$paras2 = $xpath2->query("//span[@class='v13']");
					$para2 = $paras2->item($i);
					$description = $para2->textContent;	
					
				$xpath3 = new DOMXPath($dom);			
				$paras3 = $xpath3->query("//a[@class='a18 dblue']");
					$para3 = $paras3->item($i);
					$title = $para3->textContent;	
					
					$url = $para3->getAttribute('href');	
					$url = explode("/id/", $url);	
					$url = "http://".$affid.".".$url[1].".hop.clickbank.net";

				$xpath4 = new DOMXPath($dom);			
				$paras4 = $xpath4->query("//div[@class='screenshot']/a/img");
					$para4 = $paras4->item($i);
					$thumbnail = $para4->getAttribute('src');			
									
				
				$link = '<a rel="nofollow" href="'.$url.'">'.$title . '</a>';	
				$image = '<a href="'.$url.'" rel="nofollow"><img style="float:left;margin: 0 20px 10px 0;" src="'.$thumbnail.'" alt="'.$title.'" /></a>';					

				$ff = $options['wpr_cb_filter'];
				$stop = 0;
				if($ff == "yes") {
					$pos = strpos($description, "Commission");
					if ($pos !== false) {$stop = 1;}				
					$pos = strpos($description, "commission");
					if ($pos !== false) {$stop = 1;}
					$pos = strpos($description, "affiliate");
					if ($pos !== false) {$stop = 1;}	
					$pos = strpos($description, "Affiliate");
					if ($pos !== false) {$stop = 1;}						
					$pos = strpos($description, "affiliates");
					if ($pos !== false) {$stop = 1;}						
				}								
				
				if($stop == 0) {				
					$post = $template;
					$post = wpr_random_tags($post);
					$post = str_replace("{thumbnail}", $image, $post);
					$post = str_replace("{imageurl}", $thumbnail, $post);		
					$post = str_replace("{link}", $link, $post);							
					$post = str_replace("{description}", $description, $post);
					$post = str_replace("{url}", $url, $post);	
					$noqkeyword = str_replace('"', '', $keyword);
					$post = str_replace("{keyword}", $noqkeyword, $post);
					$post = str_replace("{Keyword}", ucwords($noqkeyword), $post);				
					$post = str_replace("{title}", $title, $post);	
					if(function_exists("wpr_translate_partial")) {
						$post = wpr_translate_partial($post);
					}
					if(function_exists("wpr_rewrite_partial")) {
						$post = wpr_rewrite_partial($post,$options);
					}					
					$posts[$x]["unique"] = $title;
					$posts[$x]["title"] = $title;
					$posts[$x]["content"] = $post;	
					$x++;
				}	
			//}
		//}
	}
	
	if(empty($posts)) {
		$posts["error"]["module"] = "Clickbank";
		$posts["error"]["reason"] = "No content";
		$posts["error"]["message"] = __("No (more) Clickbank ads found.","wprobot");	
		return $posts;			
	} else {
		return $posts;	
	}					
}

function wpr_clickbank_options_default() {
	$options = array(
		"wpr_cb_filter" => "",
		"wpr_cb_cat" => "",
		"wpr_cb_affkey" => ""
	);
	return $options;
}

function wpr_clickbank_options($options) {
	?>
	<h3 style="text-transform:uppercase;border-bottom: 1px solid #ccc;"><?php _e("Clickbank Options","wprobot") ?></h3>
		<table class="addt" width="100%" cellspacing="2" cellpadding="5" class="editform"> 	
			<tr <?php if($options['wpr_cb_affkey'] == "") {echo 'style="background:#F8E0E0;"';} ?> valign="top"> 
				<td width="40%" scope="row"><?php _e("Clickbank Affiliate ID:","wprobot") ?></td> 
				<td><input size="40" name="wpr_cb_affkey" type="text" id="wpr_cb_affkey" value="<?php echo $options['wpr_cb_affkey'];?>"/>
				<!--Tooltip--><a class="tooltip" href="#">?<span><?php _e('This option is not required but you will only earn affiliate commission if you enter your Clickbank affiliate ID.',"wprobot") ?></span></a>
			</td> 
			</tr>

			<tr valign="top"> 
				<td width="40%" scope="row"><?php _e("Category:","wprobot") ?></td> 
				<td>
				<select name="wpr_cb_cat" id="wpr_cb_cat">
					<option <?php if($options['wpr_cb_cat']==""){_e('selected');}?> value="">ALL</option>		
					<option <?php if($options['wpr_cb_cat']=="media"){_e('selected');}?> value="media">Arts &amp; Entertainment</option>		
					<option <?php if($options['wpr_cb_cat']=="betting"){_e('selected');}?> value="betting">Betting</option>		
					<option <?php if($options['wpr_cb_cat']=="b2b"){_e('selected');}?> value="b2b">Biz / Investing</option>		
					<option <?php if($options['wpr_cb_cat']=="web"){_e('selected');}?> value="web">Computers / Web</option>		
					<option <?php if($options['wpr_cb_cat']=="food"){_e('selected');}?> value="food">Cooking &amp; Food</option>		
					<option <?php if($options['wpr_cb_cat']=="ebiz"){_e('selected');}?> value="ebiz">E-biz &amp; E-marketing</option>		
					<option <?php if($options['wpr_cb_cat']=="jobs"){_e('selected');}?> value="jobs">Employment</option>		
					<option <?php if($options['wpr_cb_cat']=="fiction"){_e('selected');}?> value="fiction">Fiction</option>		
					<option <?php if($options['wpr_cb_cat']=="games"){_e('selected');}?> value="games">Games</option>		
					<option <?php if($options['wpr_cb_cat']=="green"){_e('selected');}?> value="green">Green</option>		
					<option <?php if($options['wpr_cb_cat']=="health"){_e('selected');}?> value="health">Health</option>		
					<option <?php if($options['wpr_cb_cat']=="education"){_e('selected');}?> value="education">Education</option>		
					<option <?php if($options['wpr_cb_cat']=="home"){_e('selected');}?> value="home">Home &amp; Garden</option>		
					<option <?php if($options['wpr_cb_cat']=="language"){_e('selected');}?> value="language">Languages</option>
					<option <?php if($options['wpr_cb_cat']=="mobile"){_e('selected');}?> value="mobile">Mobile</option>	
					<option <?php if($options['wpr_cb_cat']=="p2p"){_e('selected');}?> value="p2p">P2P</option>
					<option <?php if($options['wpr_cb_cat']=="family"){_e('selected');}?> value="family">Parent &amp; Family</option>
					<option <?php if($options['wpr_cb_cat']=="politics"){_e('selected');}?> value="politics">Politics</option>
					<option <?php if($options['wpr_cb_cat']=="reference"){_e('selected');}?> value="reference">Reference</option>
					<option <?php if($options['wpr_cb_cat']=="selfhelp"){_e('selected');}?> value="selfhelp">Self-Help</option>
					<option <?php if($options['wpr_cb_cat']=="software"){_e('selected');}?> value="software">Software</option>		
					<option <?php if($options['wpr_cb_cat']=="alternative"){_e('selected');}?> value="alternative">Spiritual / Alternative</option>		
					<option <?php if($options['wpr_cb_cat']=="sports"){_e('selected');}?> value="sports">Sports</option>		
					<option <?php if($options['wpr_cb_cat']=="travel"){_e('selected');}?> value="travel">Travel</option>								
				</select>
			</td> 
			</tr>
			
			<tr valign="top"> 
				<td width="40%" scope="row"><?php _e("Filter Ads?","wprobot") ?></td> 
				<td><input name="wpr_cb_filter" type="checkbox" id="wpr_cb_filter" value="yes" <?php if ($options['wpr_cb_filter']=='yes') {echo "checked";} ?>/> <?php _e("Yes","wprobot") ?>
				<!--Tooltip--><a class="tooltip" href="#">?<span><?php _e('When enabled ads that contain the words "Commission" or "Affiliate" are skipped and removed from the results in order to weed out ads targeted at potential affiliates that are unfortunatelly not uncommon in the Clickbank marketplace.',"wprobot") ?></span></a></td> 
			</tr>					
		</table>	
	<?php
}
?>