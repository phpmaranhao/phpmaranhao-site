<?php

function wpr_ebaypost($keyword,$num,$start,$ebaycat,$comments="") {

	if($keyword == "") {
		$return["error"]["module"] = "Ebay";
		$return["error"]["reason"] = "No keyword";
		$return["error"]["message"] = __("No keyword specified.","wprobot");
		return $return;	
	}	

	$options = unserialize(get_option("wpr_options"));	
	$country = $options["wpr_eb_country"];
	$sortby = $options["wpr_eb_sortby"];
	$sellerid = $options["wpr_eb_sellerid"];
	$affkey = $options[ 'wpr_eb_affkey'];
	if (empty($ebaycat) || $ebaycat == "all"){$ebaycat="-1";}	
	
	require_once ( ABSPATH . WPINC .  '/rss.php' );
	
	$keyword = str_replace('"',"",$keyword );	
	$keyword = str_replace(" ","+",$keyword);
	$keyword = str_replace("-","+",$keyword);
	$keyword = strtolower($keyword);
	//$rssurl="http://rss.api.ebay.com/ws/rssapi?FeedName=SearchResults&siteId=$country&language=$lang&output=RSS20&sacat=$ebaycat&fcl=3&satitle=" . $keyword."&sacur=0&frpp=100&afepn=" . urlencode($affkey) . "&dfsp=32&sabfmts=0&salic=$country&ftrt=1&ftrv=1&customid=" .$keyword."&fss=0&saobfmts=exsif&catref=C5&saaff=afepn&from=R6&saslop=1";

	if($country == 0) {$program = 1;}
	elseif($country == 205) {$program = 2;}
	elseif($country == 16) {$program = 3;}
	elseif($country == 15) {$program = 4;}
	elseif($country == 23) {$program = 5;}
	elseif($country == 2) {$program = 7;}
	elseif($country == 71) {$program = 10;}
	elseif($country == 77) {$program = 11;}
	elseif($country == 101) {$program = 12;}
	elseif($country == 186) {$program = 13;}
	elseif($country == 193) {$program = 14;}
	elseif($country == 3) {$program = 15;}
	elseif($country == 146) {$program = 16;}
	else {$program = $country;}	
	
	if($sortby == "&fsop=1&fsoo=1") {$sortorder = "EndTimeSoonest";}
	elseif($sortby == "&fsop=2&fsoo=2") {$sortorder = "StartTimeNewest";}
	elseif($sortby == "&fsop=34&fsoo=1") {$sortorder = "PricePlusShippingLowest";}
	elseif($sortby == "&fsop=34&fsoo=2") {$sortorder = "PricePlusShippingHighest";}
	elseif($sortby == "&fsop=3&fsoo=2") {$sortorder = "BestMatch";}
	else {$sortorder = "BestMatch";}	
	if(empty($sortorder)) {$sortorder = "BestMatch";}
	
	$rssurl= "http://rest.ebay.com/epn/v1/find/item.rss?keyword=" . $keyword."&campaignid=" . urlencode($affkey) . "&sortOrder=" . $sortorder."&programid=".$program."";	
	
	if(!empty($ebaycat) && $ebaycat != -1){
		$rssurl.="&categoryId1=".$ebaycat;
	}	
	
	if(!empty($sellerid) && $sellerid != -1){
		$rssurl.="&sellerId1=".$sellerid;
	}		
	
	$therss = fetch_rss($rssurl);
	
	$i = 0;$x = 0;
	$auctions = array();
		if ($therss){		
			if($therss->items == "" || $therss->items == null) {
				$auctions["error"]["module"] = "Ebay";
				$auctions["error"]["reason"] = "No content";
				$auctions["error"]["message"] = __("No auctions found for this keyword.","wprobot");	
				return $auctions;	
			} else {
				if($start > count($therss->items)) {$start = 0;}
				$totalresults = $start + $num;
				foreach ($therss->items as $item) { 
					if($i >= $start) {
				
						$thelink=$item['link'];
						$theurl=$thelink;

						$descr = preg_replace ('#\$#', '&#36;',$item['description']);
						
						if (count($auctions) < $num) {
							$auctions[$x]["title"] = preg_replace ('#\$#', '&#36;',$item['title']);	
							$auctions[$x]["content"] = ' [wprebay kw="'.$keyword.'" num="'.$i.'" ebcat="'.$ebaycat.'"] ';
							$x++;
						} else {
							break;
						}
					}
					$i++;
				}			
			}		
		} else {
			$auctions["error"]["module"] = "Ebay";
			$auctions["error"]["reason"] = "Request fail";
			$auctions["error"]["message"] = __("Ebay RSS feed could not be retreived.","wprobot");	
			return $auctions;			
		}
	return $auctions;		
}

function wpr_rss_handler($atts, $content = null) {
	global $wpdb,$wpr_table_templates;

	$template = $wpdb->get_var("SELECT content FROM " . $wpr_table_templates . " WHERE type = 'ebay'");
	$options = unserialize(get_option("wpr_options"));	
	$sellerid = $options["wpr_eb_sellerid"];
	$country = $options["wpr_eb_country"];
	$campID = $options["wpr_eb_affkey"];
	$sortby = $options["wpr_eb_sortby"];
	
	$ebaycat = $atts["ebcat"];
	if (empty($ebaycat) || $ebaycat == "all"){$ebaycat="-1";}		
	$arrFeeds = array();

	require_once ( ABSPATH . WPINC .  '/rss.php' );	
	
	if($country == 0) {$program = 1;}
	elseif($country == 205) {$program = 2;}
	elseif($country == 16) {$program = 3;}
	elseif($country == 15) {$program = 4;}
	elseif($country == 23) {$program = 5;}
	elseif($country == 2) {$program = 7;}
	elseif($country == 71) {$program = 10;}
	elseif($country == 77) {$program = 11;}
	elseif($country == 101) {$program = 12;}
	elseif($country == 186) {$program = 13;}
	elseif($country == 193) {$program = 14;}
	elseif($country == 3) {$program = 15;}
	elseif($country == 146) {$program = 16;}
	else {$program = $country;}	
	
	if($sortby == "&fsop=1&fsoo=1") {$sortorder = "EndTimeSoonest";}
	elseif($sortby == "&fsop=2&fsoo=2") {$sortorder = "StartTimeNewest";}
	elseif($sortby == "&fsop=34&fsoo=1") {$sortorder = "PricePlusShippingLowest";}
	elseif($sortby == "&fsop=34&fsoo=2") {$sortorder = "PricePlusShippingHighest";}
	elseif($sortby == "&fsop=3&fsoo=2") {$sortorder = "BestMatch";}
	else {$sortorder = "BestMatch";}	
	if(empty($sortorder)) {$sortorder = "BestMatch";}

	$rssurl= "http://rest.ebay.com/epn/v1/find/item.rss?keyword=" . str_replace(" ","+", ($atts['kw']))."&campaignid=" . urlencode($campID) . "&sortOrder=" . $sortorder."&programid=".$program."";	
		
	
	if(!empty($ebaycat) && $ebaycat != -1){
		$rssurl.="&categoryId1=".$ebaycat;
	}	
	
	if(!empty($sellerid) && $sellerid != -1){
		$rssurl.="&sellerId1=".$sellerid;
	}		
	
	$therss = fetch_rss($rssurl);
	
	if($therss->items != "" && $therss->items != null) {
		foreach ($therss->items as $item) { 
			$itemRSS = array (
				'title' => $item['title'],
				'desc' => $item['description'],
				'link' => $item['link'],
				'date' => $item['pubDate']
				);
			array_push($arrFeeds, $itemRSS);
		}
	}
	
	$number = $atts['num'];
	$ebcontent = $template;
	$ebcontent = wpr_random_tags($ebcontent);
	
		preg_match_all('#<td>(.*)<\/td>#iU', $arrFeeds[$number]['desc'], $matches);
		$thumbnail = $matches[0][0];	
		$description = $matches[0][1];
		
		preg_match('#<strong>(.*)<\/strong>#iU', $description, $pricem);	
		$price = $pricem[1];	
		
	$ebcontent = str_replace("{thumbnail}", $thumbnail, $ebcontent);
	$ebcontent = str_replace("{price}", $price, $ebcontent);			
	$ebcontent = str_replace('{title}', $arrFeeds[$number]['title'], $ebcontent);
	$ebcontent = str_replace("{descriptiontable}", $arrFeeds[$number]['desc'], $ebcontent);			
	$ebcontent = str_replace("{description}", $description, $ebcontent);
	$ebcontent = str_replace("{url}", $arrFeeds[$number]['link'], $ebcontent);
	if ($options['wpr_openlinks']=='yes') {$ebcontent = str_replace("<a ", '<a target="_blank" ', $ebcontent);}		
	if($arrFeeds[$number]['title'] != "") {
	} else {$ebcontent = "";}

	$content = $ebcontent;
	return $content;

}
add_shortcode('wprebay', 'wpr_rss_handler' );
add_shortcode( 'eba', 'wpr_rss_handler' );

function wpr_ebay_options_default() {
	$options = array(
		"wpr_eb_affkey" => "",
		"wpr_eb_country" => "0",
		"wpr_eb_sellerid" => "",
		"wpr_eb_sortby" => "bestmatch"
	);
	return $options;
}

function wpr_ebay_options($options) {
	?>
	<h3 style="text-transform:uppercase;border-bottom: 1px solid #ccc;"><?php _e("eBay Options","wprobot") ?></h3>
		<table class="addt" width="100%" cellspacing="2" cellpadding="5" class="editform"> 
			<tr <?php if($options['wpr_eb_affkey'] == "") {echo 'style="background:#F8E0E0;"';} ?> valign="top"> 
				<td width="40%" scope="row"><?php _e("eBay Affiliate ID (CampID):","wprobot") ?></td> 
				<td><input size="40" name="wpr_eb_affkey" type="text" id="wpr_eb_affkey" value="<?php echo $options['wpr_eb_affkey'] ;?>"/>
				<!--Tooltip--><a class="tooltip" href="#">?<span><?php _e('This option is not required but you will only earn affiliate commission if you enter your Ebay affiliate ID.',"wprobot") ?></span></a>
			</td> 
			</tr>
			<tr valign="top"> 
				<td width="40%" scope="row"><?php _e("Country:","wprobot") ?></td> 
				<td>
				<select name="wpr_eb_country" id="wpr_eb_country">
					<option value="0" <?php if($options['wpr_eb_country']=="0"){_e('selected');}?>><?php _e("United States","wprobot") ?></option>
					<option value="2" <?php if($options['wpr_eb_country']=="2"){_e('selected');}?>><?php _e("Canada","wprobot") ?></option>
					<option value="3" <?php if($options['wpr_eb_country']=="3"){_e('selected');}?>><?php _e("United kingdom","wprobot") ?></option>
					<option value="15" <?php if($options['wpr_eb_country']=="15"){_e('selected');}?>><?php _e("Australia","wprobot") ?></option>
					<option value="16" <?php if($options['wpr_eb_country']=="16"){_e('selected');}?>><?php _e("Austria","wprobot") ?></option>
					<option value="23" <?php if($options['wpr_eb_country']=="23"){_e('selected');}?>><?php _e("Belgium (French)","wprobot") ?></option>
					<option value="71" <?php if($options['wpr_eb_country']=="71"){_e('selected');}?>><?php _e("France","wprobot") ?></option>
					<option value="77" <?php if($options['wpr_eb_country']=="77"){_e('selected');}?>><?php _e("Germany","wprobot") ?></option>
					<option value="100" <?php if($options['wpr_eb_country']=="100"){_e('selected');}?>><?php _e("eBay Motors","wprobot") ?></option>
					<option value="101" <?php if($options['wpr_eb_country']=="101"){_e('selected');}?>><?php _e("Italy","wprobot") ?></option>
					<option value="123" <?php if($options['wpr_eb_country']=="123"){_e('selected');}?>><?php _e("Belgium (Dutch)","wprobot") ?></option>
					<option value="146" <?php if($options['wpr_eb_country']=="146"){_e('selected');}?>><?php _e("Netherlands","wprobot") ?></option>
					<option value="186" <?php if($options['wpr_eb_country']=="186"){_e('selected');}?>><?php _e("Spain","wprobot") ?></option>
					<option value="193" <?php if($options['wpr_eb_country']=="193"){_e('selected');}?>><?php _e("Switzerland","wprobot") ?></option>
					<option value="196" <?php if($options['wpr_eb_country']=="196"){_e('selected');}?>><?php _e("Taiwan","wprobot") ?></option>
					<option value="223" <?php if($options['wpr_eb_country']=="223"){_e('selected');}?>><?php _e("China","wprobot") ?></option>
					<option value="203" <?php if($options['wpr_eb_country']=='203') {_e('selected');}?>><?php _e("India","wprobot") ?></option>
					<option value="205" <?php if($options['wpr_eb_country']=='205') {_e('selected');}?>><?php _e("Ireland","wprobot") ?></option>
				</select>
			</td> 
			</tr>	
			<tr valign="top"> 
				<td width="40%" scope="row"><?php _e("Sort results by:","wprobot") ?></td> 
				<td>
				<select name="wpr_eb_sortby" id="wpr_eb_sortby">
					<option value="bestmatch" <?php if($options['wpr_eb_sortby']=="bestmatch"){_e('selected');}?>><?php _e("Best Match","wprobot") ?></option>
					<option value="&fsop=1&fsoo=1" <?php if($options['wpr_eb_sortby']=="&fsop=1&fsoo=1"){_e('selected');}?>><?php _e("Time: ending soonest","wprobot") ?></option>
					<option value="&fsop=2&fsoo=2" <?php if($options['wpr_eb_sortby']=="&fsop=2&fsoo=2"){_e('selected');}?>><?php _e("Time: newly listed","wprobot") ?></option>
					<option value="&fsop=34&fsoo=1" <?php if($options['wpr_eb_sortby']=="&fsop=34&fsoo=1"){_e('selected');}?>><?php _e("Price + Shipping: lowest first","wprobot") ?></option>
					<option value="&fsop=34&fsoo=2" <?php if($options['wpr_eb_sortby']=="&fsop=34&fsoo=2"){_e('selected');}?>><?php _e("Price + Shipping: highest first","wprobot") ?></option>
					<option value="&fsop=3&fsoo=2" <?php if($options['wpr_eb_sortby']=="&fsop=3&fsoo=2"){_e('selected');}?>><?php _e("Price: highest first","wprobot") ?></option>
				</select>				
			</td> 
			</tr>	

			<tr valign="top"> 
				<td width="40%" scope="row"><?php _e("eBay Seller ID:","wprobot") ?></td> 
				<td><input size="40" name="wpr_eb_sellerid" type="text" id="wpr_eb_sellerid" value="<?php echo $options['wpr_eb_sellerid'] ;?>"/>
				<!--Tooltip--><a class="tooltip" href="#">?<span><?php _e('Optionally enter a seller ID to only return auctions by this particular ebay vendor.',"wprobot") ?></span></a>
			</td> 
			</tr>
			
		</table>	
	<?php
}
?>