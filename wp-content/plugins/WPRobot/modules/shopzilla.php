<?php

// This function sends the API request and returns the answer as XML
function wpr_shopzillarequest($keyword,$num,$start) {	
	libxml_use_internal_errors(true);
	
	$options = unserialize(get_option("wpr_options"));	// Load WP Robot Options Array
	$appid = trim($options['wpr_shopzilla_appkey']); // If necessary retreive the API key from the options...
	$pubid = trim($options['wpr_shopzilla_pubkey']);
	
		if (empty($appid) || empty($appid)) { // ... and otherwise return an Error.
			$return["error"]["module"] = "Shopzilla";
			$return["error"]["reason"] = "cURL Error";
			$return["error"]["message"] = __("Missing API information: Enter your API Keys on the Options page or the Shopzilla module can not work!","wprobot");	
			return $return;		
		}	
	
	$offers = $options['wpr_shopzilla_offers'];
	$sort = $options['wpr_shopzilla_sort'];
	$minprice = $options['wpr_shopzilla_minprice'] * 100;
	$maxprice = $options['wpr_shopzilla_maxprice'] * 100;
	$keyword = urlencode($keyword);
	
	// API REQUEST
	$lang = $options['wpr_shopzilla_lang'];
	if($lang == "uk") {	
		$request = "http://catalog.bizrate.co.uk/services/catalog/v1/api/product/3?apiKey=$appid&publisherId=$pubid&categoryId=&keyword=$keyword&minPrice=$minprice&maxPrice=$maxprice&start=$start&results=$num&resultsOffers=$offers&sort=$sort&offersOnly=false&biddedOnly=true&minRelevancyScore=100";	
	} elseif($lang == "fr") {
		$request = "http://catalog.shopzilla.fr/services/catalog/v1/api/product/4?apiKey=$appid&publisherId=$pubid&categoryId=&keyword=$keyword&minPrice=$minprice&maxPrice=$maxprice&start=$start&results=$num&resultsOffers=$offers&sort=$sort&offersOnly=false&biddedOnly=true&minRelevancyScore=100";
	} elseif($lang == "de") {
		$request = "http://catalog.shopzilla.de/services/catalog/v1/api/product/5?apiKey=$appid&publisherId=$pubid&categoryId=&keyword=$keyword&minPrice=$minprice&maxPrice=$maxprice&start=$start&results=$num&resultsOffers=$offers&sort=$sort&offersOnly=false&biddedOnly=true&minRelevancyScore=100";
	} else {
		$request = "http://catalog.bizrate.com/services/catalog/v1/us/product?apiKey=$appid&publisherId=$pubid&keyword=$keyword&start=$start&results=$num&resultsOffers=$offers&sort=$sort&minPrice=$minprice&maxPrice=$maxprice&biddedOnly=true"; // The request URL used by the API service	
	}
  
  
  // $request = "http://catalog.shopzilla.de/services/catalog/v1/us/product?apiKey=$appid&publisherId=$pubid&keyword=$keyword&start=$start&results=$num&resultsOffers=$offers&sort=$sort&minPrice=$minprice&maxPrice=$maxprice&biddedOnly=true"; // The request URL used by the API service
///echo $request. "<br/>";
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
				$return["error"]["module"] = "Shopzilla";
				$return["error"]["reason"] = "cURL Error";
				$return["error"]["message"] = __("cURL Error Number ","wprobot").curl_errno($ch).": ".curl_error($ch);	
				return $return;
			}
		}		
		curl_close($ch);
	} else { 				
		$response = @file_get_contents($request);  // if not try an alternative....
		if (!$response) { // ... and otherwise return an Error.
			$return["error"]["module"] = "Shopzilla";
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
			$return["error"]["module"] = "Shopzilla";
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

function wpr_shopzillapost($keyword,$num,$start,$optional="",$comments="") {
	global $wpdb,$wpr_table_templates;

	if($keyword == "") { // If keyword is empty return error
		$return["error"]["module"] = "Shopzilla";
		$return["error"]["reason"] = "No keyword";
		$return["error"]["message"] = __("No keyword specified.","wprobot");
		return $return;	
	}
	
	$template = $wpdb->get_var("SELECT content FROM " . $wpr_table_templates . " WHERE type = 'shopzilla'");
	if($template == false || empty($template)) { // If module template is empty return error
		$return["error"]["module"] = "Shopzilla";
		$return["error"]["reason"] = "No template";
		$return["error"]["message"] = __("Module Template does not exist or could not be loaded.","wprobot");
		return $return;	
	}		
	
	$x = 0;
	$itemcontent = array();
	$pxml = wpr_shopzillarequest($keyword,$num,$start); // Send API request
	if(!empty($pxml["error"])) {return $pxml;}
	if ($pxml === False) {
		$itemcontent["error"]["module"] = "Shopzilla";
		$itemcontent["error"]["reason"] = "Request fail";
		$itemcontent["error"]["message"] = __("API request could not be sent.","wprobot");	
		return $itemcontent;	
	} else {
		if (isset($pxml->Products->Product)) {
			foreach($pxml->Products->Product as $item) {		

				$attrs = $item->attributes();
				$id = $attrs['id']; 			

				$title = $item->title;					
				$description = $item->description;	
				$manufacturer = $item->manufacturer;		
				$url = $item->url;				
				$thumb = $item->Images->Image[0];
				$detailurl = $item->detailUrl;			

				$minprice = str_replace("$", "$ ", $item->PriceSet->minPrice);
				$maxprice = str_replace("$", "$ ", $item->PriceSet->maxPrice);
				
				
				if (isset($item->Offers->Offer)) {
					$offertable = '<table class="offertable">';
					
					foreach($item->Offers->Offer as $offer) {
						$offertable .= "<tr>";
						$condition = $offer->condition;
						$offerurl = $offer->url;				
						$price = str_replace("$", "$ ", $offer->price);
						$shipamount = str_replace("$", "$ ", $offer->shipAmount);
						$merchantname = $offer->merchantName;
						$merchantlogo = $offer->merchantLogoUrl;
						
						$offertable .= '<td style="padding:5px;"><a href="'.$offerurl.'" rel="nofollow"><strong>'.$merchantname.'</strong><br/><img src="'.$merchantlogo.'" /></a></td>';
					//	$offertable .= "<td>$merchantname</td>";
						$offertable .= "<td style=\"padding:5px;\">$price";
						if(!empty($shipamount)) {$offertable .= "<br/><small>+ $shipamount shipping</small>";}						
						$offertable .= "</td>";					
						$offertable .= "</tr>";
					}
					$offertable .= "</table>";
				} else {$offertable = "";}
				

				if($thumb != "") {$thumbnail = '<a href="'.$url.'" rel="nofollow"><img style="float:left;margin: 0 20px 10px 0;" src="'.$thumb.'" /></a>';} else {$thumbnail = '';}
				$content = $template;
				$content = wpr_random_tags($content);
				$content = str_replace("{thumbnail}", $thumbnail, $content);
				$content = str_replace("{title}", $title, $content);
				$content = str_replace("{description}", $description, $content);
				$content = str_replace("{manufacturer}", $manufacturer, $content);
				$content = str_replace("{url}", $url, $content);
				$content = str_replace("{detailurl}", $url, $content);
				$content = str_replace("{minprice}", $minprice, $content);		
				$content = str_replace("{maxprice}", $maxprice, $content);	
				$content = str_replace("{offers}", $offertable, $content);		
				$noqkeyword = str_replace('"', '', $keyword);
				$content = str_replace("{keyword}", $noqkeyword, $content);
				$content = str_replace("{Keyword}", ucwords($noqkeyword), $content);					
					if(function_exists("wpr_translate_partial")) {
						$content = wpr_translate_partial($content);
					}
					if(function_exists("wpr_rewrite_partial")) {
						$content = wpr_rewrite_partial($content,$options);
					}
					
					$customfield = array();
					$customfield["shopzillatitle"] = $title;
					$customfield["shopzillaprice"] = $minprice;
					$customfield["shopzillathumbnail"] = $thumb;	
					
				$itemcontent[$x]["unique"] = $id;
				$itemcontent[$x]["title"] = $title;
				$itemcontent[$x]["content"] = $content;	
				$itemcontent[$x]["customfield"] = $customfield;
				$x++;
			}
			
			if(empty($itemcontent)) { // Return error if no content has been found.
				$itemcontent["error"]["module"] = "Shopzilla";
				$itemcontent["error"]["reason"] = "No content";
				$itemcontent["error"]["message"] = __("No (more) Shopzilla items found.","wprobot");	
				return $itemcontent;		
			} else {
				return $itemcontent;	
			}			
		} else {
		
			if (isset($pxml->Products->Offer)) { // Check for API error messages in results and if found return them.
				$itemcontent["error"]["module"] = "Shopzilla";
				$itemcontent["error"]["reason"] = "IncNum";
				$itemcontent["error"]["message"] = __("Product skipped because Shopzilla returned no comparison data for it.","wprobot");	
				return $itemcontent;			
			} elseif (isset($pxml->Message)) { // Check for API error messages in results and if found return them.
				$message = __('There was a problem with your API request. This is the error returned:',"wprobot").' <b>'.$pxml->Message.'</b>';	
				$itemcontent["error"]["module"] = "Shopzilla";
				$itemcontent["error"]["reason"] = "API fail";
				$itemcontent["error"]["message"] = $message;	
				return $itemcontent;			
			} else { // Return error if no content has been found.
				$itemcontent["error"]["module"] = "Shopzilla";
				$itemcontent["error"]["reason"] = "No content";
				$itemcontent["error"]["message"] = __("No (more) Shopzilla items found.","wprobot");	
				return $itemcontent;				
			}			
		}
	}	
}

function wpr_shopzilla_options_default() { // default options ARRAY for this module
	$options = array(
		"wpr_shopzilla_appkey" => "",
		"wpr_shopzilla_pubkey" => "",
		"wpr_shopzilla_offers" => "5",
		"wpr_shopzilla_sort" => "relevancy_desc",
		"wpr_shopzilla_minprice" => "",
		"wpr_shopzilla_maxprice" => "",
		"wpr_shopzilla_lang" => "us"
	);
	return $options;
}

function wpr_shopzilla_options($options) { // module settings for the WP Robot "Options" screen

	?>
	<h3 style="text-transform:uppercase;border-bottom: 1px solid #ccc;"><?php _e("Shopzilla Options","wprobot") ?></h3>			
		<table class="addt" width="100%" cellspacing="2" cellpadding="5" class="editform"> 
			<tr <?php if($options['wpr_shopzilla_appkey'] == "") {echo 'style="background:#F8E0E0;"';} ?> valign="top"> 
				<td width="40%" scope="row"><?php _e("Shopzilla API Key:","wprobot") ?></td> 
				<td><input size="40" name="wpr_shopzilla_appkey" type="text" id="wpr_shopzilla_appkey" value="<?php echo $options['wpr_shopzilla_appkey'] ;?>"/>
				<!--Tooltip--><a target="_blank" class="tooltip" href="#">?<span><?php _e('This setting is required for the Shopzilla module to work!',"wprobot") ?></span></a>
			</td> 
			</tr>
			<tr <?php if($options['wpr_shopzilla_pubkey'] == "") {echo 'style="background:#F8E0E0;"';} ?> valign="top"> 
				<td width="40%" scope="row"><?php _e("Shopzilla Publisher ID:","wprobot") ?></td> 
				<td><input size="40" name="wpr_shopzilla_pubkey" type="text" id="wpr_shopzilla_pubkey" value="<?php echo $options['wpr_shopzilla_pubkey'] ;?>"/>
				<!--Tooltip--><a target="_blank" class="tooltip" href="#">?<span><?php _e('This setting is required for the Shopzilla module to work!',"wprobot") ?></span></a>
			</td> 
			</tr>	

			<tr valign="top"> 
				<td width="40%" scope="row"><?php _e("Language:","wprobot") ?></td> 
				<td>
				<select name="wpr_shopzilla_lang" id="wpr_shopzilla_lang">
					<option value="us" <?php if($options['wpr_shopzilla_lang']=="us"){_e('selected');}?>><?php _e("Shopzilla USA","wprobot") ?></option>
					<option value="uk" <?php if($options['wpr_shopzilla_lang']=="uk"){_e('selected');}?>><?php _e("Shopzilla UK","wprobot") ?></option>
					<option value="de" <?php if($options['wpr_shopzilla_lang']=="de"){_e('selected');}?>><?php _e("Shopzilla Germany","wprobot") ?></option>
					<option value="fr" <?php if($options['wpr_shopzilla_lang']=="fr"){_e('selected');}?>><?php _e("Shopzilla France","wprobot") ?></option>
				</select>
			</td> 			
			</tr>
			
			<tr valign="top"> 
				<td width="40%" scope="row"><?php _e("Number of Offers:","wprobot") ?></td> 
				<td><input size="40" name="wpr_shopzilla_offers" type="text" id="wpr_shopzilla_offers" value="<?php echo $options['wpr_shopzilla_offers'] ;?>"/>
			</td> 
			</tr>				
			<tr valign="top"> 
				<td width="40%" scope="row"><?php _e("Sort by:","wprobot") ?></td> 
				<td>
				<select name="wpr_shopzilla_sort" id="wpr_shopzilla_sort">
					<option value="relevancy_desc" <?php if($options['wpr_shopzilla_sort']=="relevancy_desc"){_e('selected');}?>><?php _e("Sort by relevancy of results","wprobot") ?></option>
					<option value="price_asc" <?php if($options['wpr_shopzilla_sort']=="price_asc"){_e('selected');}?>><?php _e("Sort by price, ascending","wprobot") ?></option>
					<option value="price_desc" <?php if($options['wpr_shopzilla_sort']=="price_desc"){_e('selected');}?>><?php _e("Sort by price, descending","wprobot") ?></option>							
				</select>
			</td> 
			</tr>	
			<tr valign="top"> 
				<td width="40%" scope="row"><?php _e("Minimum Price:","wprobot") ?></td> 
				<td><input size="40" name="wpr_shopzilla_minprice" type="text" id="wpr_shopzilla_minprice" value="<?php echo $options['wpr_shopzilla_minprice'] ;?>"/>
				</td> 
			</tr>	
			<tr valign="top"> 
				<td width="40%" scope="row"><?php _e("Maximum Price:","wprobot") ?></td> 
				<td><input size="40" name="wpr_shopzilla_maxprice" type="text" id="wpr_shopzilla_maxprice" value="<?php echo $options['wpr_shopzilla_maxprice'] ;?>"/>
				</td> 
			</tr>			
		</table>		
	<?php
}

?>