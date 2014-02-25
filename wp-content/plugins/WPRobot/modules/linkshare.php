<?php

function wpr_linksharerequest($keyword,$num,$start) {	
	libxml_use_internal_errors(true);
	
	$options = unserialize(get_option("wpr_options"));	// Load WP Robot Options Array
	$appid = trim($options['wpr_linkshare_appkey']); // If necessary retreive the API key from the options...
	$sort = $options['wpr_linkshare_sort'];	// ... or other parameters to use in the API request
	$cat = $options['wpr_linkshare_cat'];
	$merchant = $options['wpr_linkshare_merchant'];
	
	// API REQUEST
	if(empty($start)) {$start = 1;}
    $request = 'http://feed.linksynergy.com/productsearch?token='.$appid.'&keyword="'.urlencode($keyword).'"&MaxResults='.$num.'&pagenumber='.$start.'&cat="'.urlencode($cat).'"&sort='.$sort.'&sorttype=asc&merchant='.urlencode($merchant); // The request URL used by the API service
    $request = 'http://productsearch.linksynergy.com/productsearch?token='.$appid.'&keyword="'.urlencode($keyword).'"&MaxResults='.$num.'&pagenumber='.$start.'&cat="'.urlencode($cat).'"&sort='.$sort.'&sorttype=asc&merchant='.urlencode($merchant); // The request URL used by the API service
	
	if ( function_exists('curl_init') ) { // Check if cURL is installed...
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (compatible; Konqueror/4.0; Microsoft Windows) KHTML/4.0.80 (like Gecko)");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_URL, $request);
		curl_setopt($ch, CURLOPT_TIMEOUT, 60);
		$response = curl_exec($ch);
		if (!$response) { // If cURL request did not work....
			$response = @file_get_contents($request); // first try an alternative....
			if (!$response) {		// ... and otherwise return an Error.
				$return["error"]["module"] = "Linkshare";
				$return["error"]["reason"] = "cURL Error";
				$return["error"]["message"] = __("cURL Error Number ","wprobot").curl_errno($ch).": ".curl_error($ch);	
				return $return;
			}
		}		
		curl_close($ch);
	} else { 				
		$response = @file_get_contents($request);  // if not try an alternative....
		if (!$response) { // ... and otherwise return an Error.
			$return["error"]["module"] = "Linkshare";
			$return["error"]["reason"] = "cURL Error";
			$return["error"]["message"] = __("cURL is not installed on this server!","wprobot");	
			return $return;		
		}
	}
    
	$pxml = simplexml_load_string($response);

	if ($pxml === False) {
		$pxml = simplexml_load_file($request); 
		if ($pxml === False) {	
			$emessage = __("Failed loading XML, errors returned: ","wprobot");
			foreach(libxml_get_errors() as $error) {
				$emessage .= $error->message . ", ";
			}	
			libxml_clear_errors();
			$return["error"]["module"] = "Linkshare";
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

function wpr_linksharepost($keyword,$num,$start,$optional="",$comments="") {
	global $wpdb,$wpr_table_templates;

	if($keyword == "") { // If keyword is empty return error
		$return["error"]["module"] = "Linkshare";
		$return["error"]["reason"] = "No keyword";
		$return["error"]["message"] = __("No keyword specified.","wprobot");
		return $return;	
	}
	
	$template = $wpdb->get_var("SELECT content FROM " . $wpr_table_templates . " WHERE type = 'linkshare'");
	if($template == false || empty($template)) { // If module template is empty return error
		$return["error"]["module"] = "Linkshare";
		$return["error"]["reason"] = "No template";
		$return["error"]["message"] = __("Module Template does not exist or could not be loaded.","wprobot");
		return $return;	
	}		
	
	$x = 0;
	$itemcontent = array();
	$pxml = wpr_linksharerequest($keyword,$num,$start); // Send API request
	if(!empty($pxml["error"])) {return $pxml;}
	if ($pxml === False) {
		$itemcontent["error"]["module"] = "Linkshare";
		$itemcontent["error"]["reason"] = "Request fail";
		$itemcontent["error"]["message"] = __("API request could not be sent.","wprobot");	
		return $itemcontent;	
	} else {
		if (isset($pxml->item) ) {
			foreach($pxml->item as $item) {		
			
				$unique = $item->sku;				
				$title = $item->productname;	
				$merchantname = $item->merchantname;			
				$shortdesc = $item->description->short;	
				$longdesc = $item->description->long;	
				if(strlen($shortdesc) > strlen($longdesc)) {$longdesc = $shortdesc;}
				$longdesc = htmlspecialchars_decode($longdesc);		//echo html_entity_decode($longdesc);		

				$url = $item->linkurl;				
				$cat = $item->category->primary;
				$thumb = $item->imageurl;
				$price = "$ ". number_format($item->price, 2, '.', '');
				
				if($thumb != "") {$thumbnail = '<a href="'.$url.'" rel="nofollow"><img style="float:left;margin: 0 20px 10px 0;" src="'.$thumb.'" /></a>';} else {$thumbnail = '';}
				$content = $template;
				$content = wpr_random_tags($content);
				$content = str_replace("{thumbnail}", $thumbnail, $content);
				$content = str_replace("{imageurl}", $thumb, $content);
				$content = str_replace("{title}", $title, $content);
				$content = str_replace("{summary}", $shortdesc, $content);
				$content = str_replace("{description}", $longdesc, $content);
				$content = str_replace("{category}", $cat, $content);
				$content = str_replace("{price}", $price, $content);
				$content = str_replace("{url}", $url, $content);						
				$content = str_replace("{keyword}", $keyword, $content);
				$content = str_replace("{merchant}", $merchantname, $content);
					if(function_exists("wpr_translate_partial")) {
						$content = wpr_translate_partial($content);
					}
					if(function_exists("wpr_rewrite_partial")) {
						$content = wpr_rewrite_partial($content,$options);
					}
					
					$customfield = array();
					$customfield["linksharetitle"] = $title;
					$customfield["linksharethumbnail"] = $thumb;
					$customfield["linkshareprice"] = $price;	
					$customfield["lsmerchant"] = $merchantname;
					
				$itemcontent[$x]["unique"] = $unique;
				$itemcontent[$x]["title"] = $title;
				$itemcontent[$x]["content"] = $content;	
				$itemcontent[$x]["customfield"] = $customfield;					
				$x++;
			}
			
			if(empty($itemcontent)) { // Return error if no content has been found.
				$itemcontent["error"]["module"] = "Linkshare";
				$itemcontent["error"]["reason"] = "No content";
				$itemcontent["error"]["message"] = __("No (more) Linkshare items found.","wprobot");	
				return $itemcontent;		
			} else {
				return $itemcontent;	
			}			
		} else {
			if (isset($pxml->Errors)) { // Check for API error messages in results and if found return them.
				$message = __('There was a problem with your API request. This is the error returned:',"wprobot").' <b>'.$pxml->Errors->ErrorText.'</b>';	
				$itemcontent["error"]["module"] = "Linkshare";
				$itemcontent["error"]["reason"] = "API fail";
				$itemcontent["error"]["message"] = $message;	
				return $itemcontent;			
			} else { // Return error if no content has been found.
				$itemcontent["error"]["module"] = "Linkshare";
				$itemcontent["error"]["reason"] = "No content";
				$itemcontent["error"]["message"] = __("No (more) Linkshare items found.","wprobot");	
				return $itemcontent;				
			}
		}
	}
}

function wpr_linkshare_options_default() { // default options ARRAY for this module
	$options = array(
		"wpr_linkshare_appkey" => "",
		"wpr_linkshare_sort" => "retailprice",
		"wpr_linkshare_cat" => "",
		"wpr_linkshare_merchant" => ""
	);
	return $options;
}

function wpr_linkshare_options($options) {

	?>
	<h3 style="text-transform:uppercase;border-bottom: 1px solid #ccc;"><?php _e("Linkshare Options","wprobot") ?></h3>			
		<table class="addt" width="100%" cellspacing="2" cellpadding="5" class="editform"> 
			<tr <?php if($options['wpr_linkshare_appkey'] == "") {echo 'style="background:#F8E0E0;"';} ?> valign="top"> 
				<td width="40%" scope="row"><?php _e("Web Services Token:","wprobot") ?></td> 
				<td><input size="40" name="wpr_linkshare_appkey" type="text" id="wpr_linkshare_appkey" value="<?php echo $options['wpr_linkshare_appkey'] ;?>"/>
				<!--Tooltip--><a target="_blank" class="tooltip" href="#">?<span><?php _e('This setting is required for the Linkshare module to work!',"wprobot") ?></span></a>
			</td> 
			</tr>	
<!--
			<tr valign="top"> 
				<td width="40%" scope="row"><?php _e("Category:","wprobot") ?></td> 
				<td>
				<select name="wpr_linkshare_sort" id="wpr_linkshare_sort">
					<option value="retailprice" <?php if($options['wpr_linkshare_sort']=="retailprice"){_e('selected');}?>><?php _e("Price","wprobot") ?></option>
					<option value="productname" <?php if($options['wpr_linkshare_sort']=="productname"){_e('selected');}?>><?php _e("Product Name","wprobot") ?></option>
					<option value="categoryname" <?php if($options['wpr_linkshare_sort']=="categoryname"){_e('selected');}?>><?php _e("Category Name","wprobot") ?></option>
					<option value="mid" <?php if($options['wpr_linkshare_sort']=="mid"){_e('selected');}?>><?php _e("Merchant ID","wprobot") ?></option>							
				</select>
			</td> 
			</tr>	
-->			
			<tr valign="top"> 
				<td width="40%" scope="row"><?php _e("Sort by:","wprobot") ?></td> 
				<td>
				<select name="wpr_linkshare_sort" id="wpr_linkshare_sort">
					<option value="retailprice" <?php if($options['wpr_linkshare_sort']=="retailprice"){_e('selected');}?>><?php _e("Price","wprobot") ?></option>
					<option value="productname" <?php if($options['wpr_linkshare_sort']=="productname"){_e('selected');}?>><?php _e("Product Name","wprobot") ?></option>
					<option value="categoryname" <?php if($options['wpr_linkshare_sort']=="categoryname"){_e('selected');}?>><?php _e("Category Name","wprobot") ?></option>
					<option value="mid" <?php if($options['wpr_linkshare_sort']=="mid"){_e('selected');}?>><?php _e("Merchant ID","wprobot") ?></option>							
				</select>
			</td> 
			</tr>	
			<tr valign="top"> 
				<td width="40%" scope="row"><?php _e("Merchant (MID):","wprobot") ?></td> 
				<td><input size="40" name="wpr_linkshare_merchant" type="text" id="wpr_linkshare_merchant" value="<?php echo $options['wpr_linkshare_merchant'] ;?>"/>
				<!--Tooltip--><a target="_blank" class="tooltip" href="#">?<span><?php _e('You can specify a Linkshare merchant ID (MID) to limit your results to a specific merchant you have partnered with. Otherwise all the merchants you are signed up for in Linkshare will be used.',"wprobot") ?></span></a>
			</td> 
			</tr>				
		</table>		
	<?php
}

?>