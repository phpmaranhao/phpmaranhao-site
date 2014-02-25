<?php

// This function sends the API request and returns the answer as XML
function wpr_commissionjunctionrequest($keyword,$num,$start) {	
	libxml_use_internal_errors(true);
	
	$options = unserialize(get_option("wpr_options"));	// Load WP Robot Options Array
	$appid = trim($options['wpr_commissionjunction_appkey']); // If necessary retreive the API key from the options...
	$region = $options['wpr_commissionjunction_lang'];	// ... or other parameters to use in the API request
	$webid = trim($options['wpr_commissionjunction_webid']);
	
	$sortby = $options['wpr_commissionjunction_sortby'];	
	$sortorder = $options['wpr_commissionjunction_sortorder'];	
	$lowprice = $options['wpr_commissionjunction_lowprice'];	
	$highprice = $options['wpr_commissionjunction_highprice'];
	$advertisers = $options['wpr_commissionjunction_advertisers'];	

	$exch = substr($keyword, 0, 1);
	$keyword = urlencode($keyword);
	if($exch == '"') {} else {$keyword = str_replace("+", "%20", $keyword);}
	
	// API REQUEST
	$start = $start + 1;
	//if($start == 0) {$start = 1;}
    $request = "https://product-search.api.cj.com/v2/product-search?website-id=$webid&advertiser-ids=$advertisers&keywords=$keyword&low-price=$lowprice&high-price=$highprice&sort-by=$sortby&sort-order=$sortorder&page-number=$start&records-per-page=$num"; // The request URL used by the API service

	if ( function_exists('curl_init') ) { // Check if cURL is installed...
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (compatible; Konqueror/4.0; Microsoft Windows) KHTML/4.0.80 (like Gecko)");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_URL, $request);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_TIMEOUT, 60);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Host: link-search.api.cj.com',
			'User-Agent: Mozilla/5.0 (Windows; U; Windows NT 6.0; en-US; rv:1.9.0.8) Gecko/2009032609 Firefox/3.0.8',
			'Authorization: '.$appid,
			'Content-Type: application/xml'
			));
		$response = curl_exec($ch);
		if (!$response) { // If cURL request did not work....
			$return["error"]["module"] = "Commission Junction";
			$return["error"]["reason"] = "cURL Error";
			$return["error"]["message"] = __("cURL Error Number ","wprobot").curl_errno($ch).": ".curl_error($ch);	
			return $return;
		}				
		curl_close($ch);
	} else { 				
		$return["error"]["module"] = "Commission Junction";
		$return["error"]["reason"] = "cURL Error";
		$return["error"]["message"] = __("cURL is not installed on this server!","wprobot");	
		return $return;		
	}
    
	$pxml = simplexml_load_string($response);
	//print_r($pxml);
	if ($pxml === False) {
		$pxml = simplexml_load_file($request); 
		if ($pxml === False) {	
			$emessage = __("Failed loading XML, errors returned: ","wprobot");
			foreach(libxml_get_errors() as $error) {
				$emessage .= $error->message . ", ";
			}	
			libxml_clear_errors();
			$return["error"]["module"] = "Commission Junction";
			$return["error"]["reason"] = "XML Error";
			$return["error"]["message"] = $emessage;	
			return $return;		
		} else {
			return $pxml;
		}			
	} else {
		return $pxml;
	}
}

function wpr_commissionjunctionpost($keyword,$num,$start,$optional="",$comments="") {
	global $wpdb,$wpr_table_templates;
	
	$buyUrl = "buy-url";
	$imageUrl = "image-url";
	$rPrice = "retail-price";
	$sPrice = "sale-price";
	$advertiserName = "advertiser-name";
	$inStock = "in-stock";
	$errormessage= "error-message";
	
	if($keyword == "") { // If keyword is empty return error
		$return["error"]["module"] = "Commission Junction";
		$return["error"]["reason"] = "No keyword";
		$return["error"]["message"] = __("No keyword specified.","wprobot");
		return $return;	
	}
	
	$template = $wpdb->get_var("SELECT content FROM " . $wpr_table_templates . " WHERE type = 'commissionjunction'");
	if($template == false || empty($template)) { // If module template is empty return error
		$return["error"]["module"] = "Commission Junction";
		$return["error"]["reason"] = "No template";
		$return["error"]["message"] = __("Module Template does not exist or could not be loaded.","wprobot");
		return $return;	
	}		

	$x = 0;
	$itemcontent = array();
	$pxml = wpr_commissionjunctionrequest($keyword,$num,$start); // Send API request
		//print_r($pxml);
	if(!empty($pxml["error"])) {return $pxml;}
	if ($pxml === False) {
		$itemcontent["error"]["module"] = "Commission Junction";
		$itemcontent["error"]["reason"] = "Request fail";
		$itemcontent["error"]["message"] = __("API request could not be sent.","wprobot");	
		return $itemcontent;	
	} else {
		if (isset($pxml->products->product)) {
			foreach($pxml->products->product as $item) {		

				$title = $item->name;					
				$summary = $item->description;				
				$url = $item->$buyUrl;	
				$price = number_format($item->price, 2, '.', '');	
	
				$currency = $item->currency;
				$listprice = $item->$rPrice;if($listprice == "0.0") {$listprice = "";}
				$saleprice = $item->$sPrice;if($saleprice == "0.0") {$saleprice = "";}
				$img = $item->$imageUrl;
				$advert = $item->$advertiserName;
				$isinstock = $item->$inStock;
				
				$skipit = 0;
				$skip = $options["wpr_commissionjunction_skip"];
				if($skip == "noimg" || $skip == "nox") {if(empty($img)) {$skipit = 1;}}	
				if($skip == "nodesc" || $skip == "nox") {if(empty($summary)) {$skipit = 1;}}					
				
				if(!empty($img)) {
				$thumbnail = '<a alt="'.$title.'" href="'.$url.'" rel="nofollow"><img style="float:left;width:150px;margin: 0 20px 10px 0;" src="'.$img.'" /></a>';
				} else {$thumbnail = '';}
				
				if ($skipit == 0) {
					$content = $template;
					$content = wpr_random_tags($content);
					$content = str_replace("{thumbnail}", $thumbnail, $content);
					$content = str_replace("{imageurl}", $img, $content);
					$content = str_replace("{title}", $title, $content);
					$content = str_replace("{description}", $summary, $content);
					$content = str_replace("{price}", $price, $content);
					$content = str_replace("{currency}", $currency, $content);
					$content = str_replace("{saleprice}", $saleprice, $content);
					$content = str_replace("{listprice}", $listprice, $content);
					$content = str_replace("{url}", $url, $content);						
					$noqkeyword = str_replace('"', '', $keyword);
					$content = str_replace("{keyword}", $noqkeyword, $content);
					$content = str_replace("{Keyword}", ucwords($noqkeyword), $content);	
					$content = str_replace("{advertiser}", $advert, $content);
					$content = str_replace("{instock}", $isinstock, $content);
					if(function_exists("wpr_translate_partial")) {
						$content = wpr_translate_partial($content);
					}
					if(function_exists("wpr_rewrite_partial")) {
						$content = wpr_rewrite_partial($content,$options);
					}	

					if(!empty($item->upc) && $item->upc != "") {$unique = $item->upc;} elseif(!empty($item->sku)) {$unique = $item->sku;} elseif(!empty($item->isbn)) {$unique = $item->isbn;}

					$customfield = array();
					$customfield["cjtitle"] = $title;
					$customfield["cjthumbnail"] = $img;
					$customfield["cjprice"] = $saleprice;
					$customfield["cjlink"] = $url;
					$customfield["cjadvertiser"] = $advert;
					$itemcontent[$x]["unique"] = $unique;
					$itemcontent[$x]["title"] = $title;
					$itemcontent[$x]["content"] = $content;	
					$itemcontent[$x]["customfield"] = $customfield;	
					//print_r($itemcontent);
				}
				$x++;
			}
			
			if(empty($itemcontent)) { // Return error if no content has been found.
				$itemcontent["error"]["module"] = "Commission Junction";
				$itemcontent["error"]["reason"] = "No content";
				$itemcontent["error"]["message"] = __("No (more) CJ products found.","wprobot");	
				return $itemcontent;		
			} else {
				return $itemcontent;	
			}		
		} else {
			if (isset($pxml->$errormessage)) { // Check for API error messages in results and if found return them.
				$message = __('There was a problem with your API request. This is the error returned:',"wprobot").' <b>'.$pxml->$errormessage.'</b>';	
				$itemcontent["error"]["module"] = "Commission Junction";
				$itemcontent["error"]["reason"] = "API fail";
				$itemcontent["error"]["message"] = $message;
				return $itemcontent;			
			} else { // Return error if no content has been found.
				$itemcontent["error"]["module"] = "Commission Junction";
				$itemcontent["error"]["reason"] = "No content";
				$itemcontent["error"]["message"] = __("No (more) CJ products found.","wprobot");	
				return $itemcontent;				
			}			
		}
	}	
}

function wpr_commissionjunction_options_default() { // default options ARRAY for this module
	$options = array(
		"wpr_commissionjunction_appkey" => "",
		"wpr_commissionjunction_webid" => "",
		"wpr_commissionjunction_sortby" => "",
		"wpr_commissionjunction_sortorder" => "desc",
		"wpr_commissionjunction_lowprice" => "",
		"wpr_commissionjunction_skip" => "nodesc",
		"wpr_commissionjunction_highprice" => "",
		"wpr_commissionjunction_advertisers" => "joined"
	);
	return $options;
}

function wpr_commissionjunction_options($options) { // module settings for the WP Robot "Options" screen

	?>
	<h3 style="text-transform:uppercase;border-bottom: 1px solid #ccc;"><?php _e("Commission Junction Options","wprobot") ?></h3>			
		<table class="addt" width="100%" cellspacing="2" cellpadding="5" class="editform"> 
			<tr <?php if($options['wpr_commissionjunction_appkey'] == "") {echo 'style="background:#F8E0E0;"';} ?> valign="top"> 
				<td width="40%" scope="row"><?php _e("Commission Junction API ID:","wprobot") ?></td> 
				<td><input size="40" name="wpr_commissionjunction_appkey" type="text" id="wpr_commissionjunction_appkey" value="<?php echo $options['wpr_commissionjunction_appkey'] ;?>"/>
				<!--Tooltip--><a target="_blank" class="tooltip" href="#">?<span><?php _e('This setting is required for the Commission Junction module to work!',"wprobot") ?></span></a>
			</td> 
			</tr>	
			<tr <?php if($options['wpr_commissionjunction_webid'] == "") {echo 'style="background:#F8E0E0;"';} ?> valign="top"> 
				<td width="40%" scope="row"><?php _e("Website ID (PID):","wprobot") ?></td> 
				<td><input size="40" name="wpr_commissionjunction_webid" type="text" id="wpr_commissionjunction_webid" value="<?php echo $options['wpr_commissionjunction_webid'] ;?>"/>
				<!--Tooltip--><a target="_blank" class="tooltip" href="#">?<span><?php _e('This setting is required for the Commission Junction module to work!',"wprobot") ?></span></a>
			</td> 
			</tr>
			<tr valign="top"> 
				<td width="40%" scope="row"><?php _e("Advertisers:","wprobot") ?></td> 
				<td><input size="40" name="wpr_commissionjunction_advertisers" type="text" id="wpr_commissionjunction_advertisers" value="<?php echo $options['wpr_commissionjunction_advertisers'] ;?>"/>
				<!--Tooltip--><a target="_blank" class="tooltip" href="#">?<span><?php _e('Decides which advertisers are used to display products. Possible Values:
				<br/><br/><strong>joined</strong> - This special value restricts the search to advertisers which you have signed up for in your CJ account.
				<br/><br/><strong>not-joined</strong> - This special value restricts the search to advertisers with which you do not have a relationship.
				<br/><br/><strong>CIDs</strong> - You may provide list of one or more advertiser CIDs, separated by commas, to limit the results to a specific sub-set of merchants.
				<br/><br/><strong>Empty String</strong> - You may leave the field empty to remove any advertiser-specific restrictions on the search.
				<br/><br/><strong>Important: You can only earn commission for advertisers you have signed up for in your CJ account!</strong>				
				',"wprobot") ?></span></a>
			</td> 
			</tr>				
			<tr valign="top"> 
				<td width="40%" scope="row"><?php _e("Sort by:","wprobot") ?></td> 
				<td>
				<select name="wpr_commissionjunction_sortby" id="wpr_commissionjunction_sortby">
					<option value="name" <?php if($options['wpr_commissionjunction_sortby']=="name"){_e('selected');}?>><?php _e("Name","wprobot") ?></option>
					<option value="price" <?php if($options['wpr_commissionjunction_sortby']=="price"){_e('selected');}?>><?php _e("Price","wprobot") ?></option>
					<option value="sale-price" <?php if($options['wpr_commissionjunction_sortby']=="sale-price"){_e('selected');}?>><?php _e("salePrice","wprobot") ?></option>
					<option value="manufacturer" <?php if($options['wpr_commissionjunction_sortby']=="manufacturer"){_e('selected');}?>><?php _e("Manufacturer","wprobot") ?></option>							
				</select>
			</td> 
			</tr>		
			<tr valign="top"> 
				<td width="40%" scope="row"><?php _e("Sort Order:","wprobot") ?></td> 
				<td>
				<select name="wpr_commissionjunction_sortorder" id="wpr_commissionjunction_sortorder">
					<option value="asc" <?php if($options['wpr_commissionjunction_sortorder']=="asc"){_e('selected');}?>><?php _e("Ascending","wprobot") ?></option>
					<option value="desc" <?php if($options['wpr_commissionjunction_sortorder']=="desc"){_e('selected');}?>><?php _e("Descending","wprobot") ?></option>							
				</select>
			</td> 
			</tr>				
			<tr valign="top"> 
				<td width="40%" scope="row"><?php _e("Minimum Price:","wprobot") ?></td> 
				<td><input size="40" name="wpr_commissionjunction_lowprice" type="text" id="wpr_commissionjunction_lowprice" value="<?php echo $options['wpr_commissionjunction_lowprice'] ;?>"/>
				</td> 
			</tr>	
			<tr valign="top"> 
				<td width="40%" scope="row"><?php _e("Maximum Price:","wprobot") ?></td> 
				<td><input size="40" name="wpr_commissionjunction_highprice" type="text" id="wpr_commissionjunction_highprice" value="<?php echo $options['wpr_commissionjunction_highprice'] ;?>"/>
				</td> 
			</tr>	
			<tr valign="top"> 
				<td width="40%" scope="row"><?php _e("Skip Products If:","wprobot") ?></td> 
				<td>
				<select name="wpr_commissionjunction_skip" id="wpr_commissionjunction_skip">
					<option value="" <?php if ($options['wpr_commissionjunction_skip']==""){echo "selected";}?>><?php _e("Don't skip","wprobot") ?></option>
					<option value="nodesc" <?php if ($options['wpr_commissionjunction_skip']=="nodesc"){echo "selected";}?>><?php _e("No description found","wprobot") ?></option>
					<option value="noimg" <?php if ($options['wpr_commissionjunction_skip']=="noimg"){echo "selected";}?>><?php _e("No thumbnail image found","wprobot") ?></option>
					<option value="nox" <?php if ($options['wpr_commissionjunction_skip']=="nox"){echo "selected";}?>><?php _e("No description OR no thumbnail","wprobot") ?></option>
				</select>
				</td> 
			</tr>				
		</table>		
	<?php
}

?>