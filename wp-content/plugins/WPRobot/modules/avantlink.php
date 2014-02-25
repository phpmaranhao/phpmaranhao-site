<?php

// This function sends the API request and returns the answer as XML
function wpr_avantlinkrequest($keyword,$num,$start) {	
	libxml_use_internal_errors(true);
	
	$options = unserialize(get_option("wpr_options"));	// Load WP Robot Options Array
	$appid = $options['wpr_avantlink_appkey']; // If necessary retreive the API key from the options...
	$website_id = $options['wpr_avantlink_website'];
	$sort = $options['wpr_avantlink_lang'];	// ... or other parameters to use in the API request

	$lowprice = $options['wpr_avantlink_lowprice'];	
	$highprice = $options['wpr_avantlink_highprice'];
	$advertisers = $options['wpr_avantlink_advertisers'];
	
	$keyword = urlencode($keyword);
	
	// API REQUEST
    $request = "https://www.avantlink.com/api.php?module=ProductSearch&affiliate_id=$appid&search_term=$keyword&website_id=$website_id&search_results_base=$start&search_results_count=$num&search_price_maximum=$highprice&search_price_minimum=$lowprice&merchant_ids=$advertisers&search_results_sort_order=$sort"; // The request URL used by the API service

	//echo $request."<br>";
	
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
				$return["error"]["module"] = "Avantlink";
				$return["error"]["reason"] = "cURL Error";
				$return["error"]["message"] = __("cURL Error Number ","wprobot").curl_errno($ch).": ".curl_error($ch);	
				return $return;
			}
		}		
		curl_close($ch);
	} else { 				
		$response = @file_get_contents($request);  // if not try an alternative....
		if (!$response) { // ... and otherwise return an Error.
			$return["error"]["module"] = "Avantlink";
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
			$return["error"]["module"] = "Avantlink";
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

function wpr_avantlinkpost($keyword,$num,$start,$optional="",$comments="") {
	global $wpdb,$wpr_table_templates;

	if($keyword == "") { // If keyword is empty return error
		$return["error"]["module"] = "Avantlink";
		$return["error"]["reason"] = "No keyword";
		$return["error"]["message"] = __("No keyword specified.","wprobot");
		return $return;	
	}
	
	$template = $wpdb->get_var("SELECT content FROM " . $wpr_table_templates . " WHERE type = 'avantlink'");
	if($template == false || empty($template)) { // If avantlink template is empty return error
		$return["error"]["module"] = "Avantlink";
		$return["error"]["reason"] = "No template";
		$return["error"]["message"] = __("avantlink Template does not exist or could not be loaded.","wprobot");
		return $return;	
	}		
	
	$x = 0;
	$itemcontent = array();
	$pxml = wpr_avantlinkrequest($keyword,$num,$start); // Send API request
	//print_r($pxml);
	if(!empty($pxml["error"]) && is_array($pxml)) {return $pxml;}
	if ($pxml === False) {
		$itemcontent["error"]["module"] = "Avantlink";
		$itemcontent["error"]["reason"] = "Request fail";
		$itemcontent["error"]["message"] = __("API request could not be sent.","wprobot");	
		return $itemcontent;	
	} else {
		if (isset($pxml->Table1)) {
			foreach($pxml->Table1 as $item) {		

				$title = $item->Product_Name;					
				$description = $item->Description;				
				$url = $item->Buy_URL;				
				$brand = $item->Brand_Name;
				$merchant = $item->Merchant_Name;
				$thumb = $item->Thumbnail_Image;
				$mediumimg = $item->Medium_Image;
				$largeimg = $item->Large_Image;
				$id = $item->Product_Id;
				$listprice = str_replace("$", "$ ", $item->Retail_Price);
				$saleprice = str_replace("$", "$ ", $item->Sale_Price);
				$id = $item->Product_Id;			
				
				if($thumb != "") {$thumbnail = '<a href="'.$url.'" rel="nofollow"><img style="float:left;margin: 0 20px 10px 0;" src="'.$thumb.'" /></a>';} else {$thumbnail = '';}
				$content = $template;
				$content = wpr_random_tags($content);
				$content = str_replace("{thumbnail}", $thumbnail, $content);
				$content = str_replace("{mediumimage}", $mediumimg, $content);
				$content = str_replace("{largeimage}", $largeimg, $content);
				$content = str_replace("{title}", $title, $content);
				$content = str_replace("{description}", $description, $content);
				$content = str_replace("{brand}", $brand, $content);
				$content = str_replace("{merchant}", $merchant, $content);
				$content = str_replace("{url}", $url, $content);						
				$content = str_replace("{keyword}", $keyword, $content);
				$content = str_replace("{price}", $saleprice, $content);
				$content = str_replace("{listprice}", $listprice, $content);
			
				$customfield = array();
				$customfield["avantlinktitle"] = $title;
				$customfield["avantlinkthumbnail"] = $thumb;
				$customfield["avantlinkprice"] = $saleprice;
				$customfield["avantlinklink"] = $url;				
			
				$itemcontent[$x]["unique"] = $id;
				$itemcontent[$x]["title"] = $title;
				$itemcontent[$x]["content"] = $content;	
				$itemcontent[$x]["customfield"] = $customfield;					
				$x++;
			}
			
			if(empty($itemcontent)) { // Return error if no content has been found.
				$itemcontent["error"]["module"] = "Avantlink";
				$itemcontent["error"]["reason"] = "No content";
				$itemcontent["error"]["message"] = __("No (more) Avantlink items found.","wprobot");	
				return $itemcontent;		
			} else {
				return $itemcontent;	
			}			
		} else {
			if (isset($pxml->Message)) { // Check for API error messages in results and if found return them.
				$message = __('There was a problem with your API request. This is the error returned:',"wprobot").' <b>'.$pxml->Message.'</b>';	
				$itemcontent["error"]["module"] = "Avantlink";
				$itemcontent["error"]["reason"] = "API fail";
				$itemcontent["error"]["message"] = $message;	
				return $itemcontent;			
			} else { // Return error if no content has been found.
				$itemcontent["error"]["module"] = "Avantlink";
				$itemcontent["error"]["reason"] = "No content";
				$itemcontent["error"]["message"] = __("No (more) Avantlink items found.","wprobot");	
				return $itemcontent;				
			}			
		}
	}	
}

function wpr_avantlink_options_default() { // default options ARRAY for this avantlink
	$options = array(
		"wpr_avantlink_appkey" => "",
		"wpr_avantlink_website" => "",
		"wpr_avantlink_lowprice" => "",
		"wpr_avantlink_highprice" => "",
		"wpr_avantlink_advertisers" => "",		
		"wpr_avantlink_lang" => ""
	);
	return $options;
}

function wpr_avantlink_options($options) { // avantlink settings for the WP Robot "Options" screen

	?>
	<h3 style="text-transform:uppercase;border-bottom: 1px solid #ccc;"><?php _e("Avantlink Options","wprobot") ?></h3>			
		<table class="addt" width="100%" cellspacing="2" cellpadding="5" class="editform"> 
			<tr <?php if($options['wpr_avantlink_appkey'] == "") {echo 'style="background:#F8E0E0;"';} ?> valign="top"> 
				<td width="40%" scope="row"><?php _e("Avantlink API ID:","wprobot") ?></td> 
				<td><input size="40" name="wpr_avantlink_appkey" type="text" id="wpr_avantlink_appkey" value="<?php echo $options['wpr_avantlink_appkey'] ;?>"/>
				<!--Tooltip--><a target="_blank" class="tooltip" href="#">?<span><?php _e('This setting is required for the Avantlink module to work!',"wprobot") ?></span></a>
			</td> 
			</tr>	
			<tr <?php if($options['wpr_avantlink_website'] == "") {echo 'style="background:#F8E0E0;"';} ?> valign="top"> 
				<td width="40%" scope="row"><?php _e("Avantlink Website ID:","wprobot") ?></td> 
				<td><input size="40" name="wpr_avantlink_website" type="text" id="wpr_avantlink_website" value="<?php echo $options['wpr_avantlink_website'] ;?>"/>
				<!--Tooltip--><a target="_blank" class="tooltip" href="#">?<span><?php _e('This setting is required for the Avantlink module to work!',"wprobot") ?></span></a>
			</td> 
			</tr>	
			<tr valign="top"> 
				<td width="40%" scope="row"><?php _e("Merchants:","wprobot") ?></td> 
				<td><input size="40" name="wpr_avantlink_advertisers" type="text" id="wpr_avantlink_advertisers" value="<?php echo $options['wpr_avantlink_advertisers'] ;?>"/>
				<!--Tooltip--><a target="_blank" class="tooltip" href="#">?<span><?php _e('A pipe-delimited list of AvantLink assigned merchant identifiers, e.g. "123|456".',"wprobot") ?></span></a>
			</td> 
			</tr>				
			<tr valign="top"> 
				<td width="40%" scope="row"><?php _e("Sort Order:","wprobot") ?></td> 
				<td>
					<select size="1" id="wpr_avantlink_lang" name="wpr_avantlink_lang">
						<option <?php if($options['wpr_avantlink_lang']==""){_e('selected');}?> value="">Default</option>
						<option <?php if($options['wpr_avantlink_lang']=="Merchant Name"){_e('selected');}?> value="Merchant Name">Merchant Name</option>
						<option <?php if($options['wpr_avantlink_lang']=="Merchant Name|desc"){_e('selected');}?> value="Merchant Name|desc">Merchant Name (descending)</option>
						<option <?php if($options['wpr_avantlink_lang']=="Product Name"){_e('selected');}?> value="Product Name">Product Name</option>
						<option <?php if($options['wpr_avantlink_lang']=="Product Name|desc"){_e('selected');}?> value="Product Name|desc">Product Name (descending)</option>
						<option <?php if($options['wpr_avantlink_lang']=="Brand Name"){_e('selected');}?> value="Brand Name">Brand Name</option>
						<option <?php if($options['wpr_avantlink_lang']=="Brand Name|desc"){_e('selected');}?> value="Brand Name|desc">Brand Name (descending)</option>
						<option <?php if($options['wpr_avantlink_lang']=="Retail Price"){_e('selected');}?> value="Retail Price">Retail Price</option>
						<option <?php if($options['wpr_avantlink_lang']=="Retail Price|desc"){_e('selected');}?> value="Retail Price|desc">Retail Price (descending)</option>
						<option <?php if($options['wpr_avantlink_lang']=="Sale Price"){_e('selected');}?> value="Sale Price">Sale Price</option>
						<option <?php if($options['wpr_avantlink_lang']=="Sale Price|desc"){_e('selected');}?> value="Sale Price|desc">Sale Price (descending)</option>
					</select>				
			</td> 
			</tr>	
			<tr valign="top"> 
				<td width="40%" scope="row"><?php _e("Minimum Price:","wprobot") ?></td> 
				<td><input size="40" name="wpr_avantlink_lowprice" type="text" id="wpr_avantlink_lowprice" value="<?php echo $options['wpr_avantlink_lowprice'] ;?>"/>
				</td> 
			</tr>	
			<tr valign="top"> 
				<td width="40%" scope="row"><?php _e("Maximum Price:","wprobot") ?></td> 
				<td><input size="40" name="wpr_avantlink_highprice" type="text" id="wpr_avantlink_highprice" value="<?php echo $options['wpr_avantlink_highprice'] ;?>"/>
				</td> 
			</tr>				
		</table>		
	<?php
}

?>