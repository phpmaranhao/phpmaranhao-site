<?php

function wpr_aws_request($region, $params, $public_key, $private_key) {
	libxml_use_internal_errors(true);
	$method = "GET";
	
	if($region == "jp") {$region = "co.jp";}
	$host = "webservices.amazon.".$region;
	//$host = "ecs.amazonaws.".$region;
	$uri = "/onca/xml";

	$params["Service"] = "AWSECommerceService";
	$params["AWSAccessKeyId"] = $public_key;
	
	$t = time() + 10000;
	$params["Timestamp"] = gmdate("Y-m-d\TH:i:s\Z",$t);	
	$params["Version"] = "2011-08-01";
	ksort($params);
	
	$canonicalized_query = array();
	foreach ($params as $param=>$value) {
		$param = str_replace("%7E", "~", rawurlencode($param));
		$value = str_replace("%7E", "~", rawurlencode($value));
		$canonicalized_query[] = $param."=".$value;
	}
	$canonicalized_query = implode("&", $canonicalized_query);
	$string_to_sign = $method."\n".$host."\n".$uri."\n".$canonicalized_query;   
	$signature = base64_encode(hash_hmac("sha256", $string_to_sign, $private_key, True));  
	$signature = str_replace("%7E", "~", rawurlencode($signature));  
	$request = "http://".$host.$uri."?".$canonicalized_query."&Signature=".$signature; 

	if ( function_exists('curl_init') ) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (compatible; Konqueror/4.0; Microsoft Windows) KHTML/4.0.80 (like Gecko)");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_URL, $request);
		curl_setopt($ch, CURLOPT_TIMEOUT, 60);
		$response = curl_exec($ch);
		if (!$response) {
			$return["error"]["module"] = "Amazon";
			$return["error"]["reason"] = "cURL Error";
			$return["error"]["message"] = __("cURL Error Number ","wprobot").curl_errno($ch).": ".curl_error($ch);	
			return $return;
		}		
		curl_close($ch);
	} else { 				
		$response = @file_get_contents($request);
		if (!$response) {
			$return["error"]["module"] = "Amazon";
			$return["error"]["reason"] = "cURL Error";
			$return["error"]["message"] = __("cURL is not installed on this server!","wprobot");	
			return $return;		
		}
	}
	
	$pxml = simplexml_load_string($response);
	if ($pxml === False) {
		$emessage = __("Failed loading XML, errors returned: ","wprobot");
		foreach(libxml_get_errors() as $error) {
			$emessage .= $error->message . ", ";
		}	
		libxml_clear_errors();
		$return["error"]["module"] = "Amazon";
		$return["error"]["reason"] = "XML Error";
		$return["error"]["message"] = $emessage;	
		return $return;			
	} else {
		return $pxml;
	}
} 

function wpr_star_rating($rating) {

	$imagepath = WP_PLUGIN_URL.'/'.str_replace(basename( __FILE__),"",plugin_basename(__FILE__)); 
	$imagepath = str_replace("/modules/","/images/",$imagepath);
	
	if($rating>=0 && $rating <= 0.7) {
		$image = '<img src="'.$imagepath.'0-5.png" />';
	}
	if($rating>=1.3 && $rating <= 1.7) {
		$image = '<img src="'.$imagepath.'1-5.png" />';
	}
	if($rating>=2.3 && $rating <= 2.7) {
		$image = '<img src="'.$imagepath.'2-5.png" />';
	}
	if($rating>=3.3 && $rating <= 3.7) {
		$image = '<img src="'.$imagepath.'3-5.png" />';
	}
	if($rating>=4.3 && $rating <= 4.7) {
		$image = '<img src="'.$imagepath.'4-5.png" />';
	}
	if($rating>=0.8 && $rating <= 1.2) {
		$image = '<img src="'.$imagepath.'1.png" />';
	}
	if($rating>=1.8 && $rating <= 2.2) {
		$image = '<img src="'.$imagepath.'2.png" />';
	}
	if($rating>=2.8 && $rating <= 3.2) {
		$image = '<img src="'.$imagepath.'3.png" />';
	}
	if($rating>=3.8 && $rating <= 4.2) {
		$image = '<img src="'.$imagepath.'4.png" />';
	}
	if($rating>=4.8 && $rating <= 5) {
		$image = '<img src="'.$imagepath.'5.png" />';
	}
		 
	return $image;
}

function wpr_amazonpost($keywords,$count,$start,$optional,$comments="") {
	global $wpdb,$wpr_table_templates;

	$searchindex = $optional[0];
	$browsenode = $optional[1];	
	if($searchindex == "") {$searchindex = "All";}
	
	if($keywords == "" && $browsenode  == "") {
		$return["error"]["module"] = "Amazon";
		$return["error"]["reason"] = "No keyword";
		$return["error"]["message"] = __("No keyword or BrowseNode specified.","wprobot");	
		return $return;	
	}

	$start2 = $start / 10;
	$start2 = (string) $start2; 
	$start2 = explode(".", $start2);
	$page=(int)$start2[0];	
	$page++;				
	$cnum=(int)$start2[1]; 
	
	$template = $wpdb->get_var("SELECT content FROM " . $wpr_table_templates . " WHERE type = 'amazon'");
	if($template == false || empty($template)) {
		$return["error"]["module"] = "Amazon";
		$return["error"]["reason"] = "No template";
		$return["error"]["message"] = __("Module Template does not exist or could not be loaded.","wprobot");
		return $return;	
	}		
	
	$imagepath = WP_PLUGIN_URL.'/'.str_replace(basename( __FILE__),"",plugin_basename(__FILE__)); 
	$imagepath = str_replace("/modules/","/images/",$imagepath);
	$options = unserialize(get_option("wpr_options"));	
	$public_key = $options['wpr_aa_apikey'];
	$private_key = $options['wpr_aa_secretkey'];
	$affid = $options['wpr_aa_affkey'];
	$added_post = 0;

	$return = array();
	$site = $options['wpr_aa_site'];	
	if($site == "us") {$site = "com";}
	if($site == "uk") {$site = "co.uk";} 	
	
	if($browsenode == "") {$browsenode = 0;}	

	while($added_post < $count) {
		if($searchindex == "All") {
		$pxml = wpr_aws_request($site, array(
		"Operation"=>"ItemSearch",
		"AssociateTag"=>$affid,
		"Keywords"=>$keywords,
		"SearchIndex"=>$searchindex,
		"MerchantId"=>"All",
		"ItemPage"=>$page,
		"ReviewSort"=>"-HelpfulVotes",
		"TruncateReviewsAt"=>"5000",
		"IncludeReviewsSummary"=>"False",
		"ResponseGroup"=>"Large"
		), $public_key, $private_key);	
		} elseif($browsenode != 0) {
		$pxml = wpr_aws_request($site, array(
		"Operation"=>"ItemSearch",
		"AssociateTag"=>$affid,
		"SearchIndex"=>$searchindex,
		"Keywords"=>$keywords,
		"BrowseNode"=>$browsenode,
		"MerchantId"=>"All",
		"ItemPage"=>$page,
		"ReviewSort"=>"-HelpfulVotes",
		"TruncateReviewsAt"=>"5000",
		"IncludeReviewsSummary"=>"False",
		"ResponseGroup"=>"Large"
		), $public_key, $private_key);					
		} else {
		$pxml = wpr_aws_request($site, array(
		"Operation"=>"ItemSearch",
		"AssociateTag"=>$affid,
		"Keywords"=>$keywords,
		"SearchIndex"=>$searchindex,
		"MerchantId"=>"All",
		"ItemPage"=>$page,
		"ReviewSort"=>"-HelpfulVotes",
		"TruncateReviewsAt"=>"5000",
		"IncludeReviewsSummary"=>"False",
		"ResponseGroup"=>"Large"
		), $public_key, $private_key);					
		}
		if(!empty($pxml["error"])) {return $pxml;}
		//echo "<pre>";print_r($pxml);echo "</pre>";

		if($count<=10){
			$count_this = $count;
		} else{
			$count_this = 10;
		}
		$rr=0;
		$yy=1;
		
		if (!$pxml) {
			$return["error"]["module"] = "Amazon";
			$return["error"]["reason"] = "Request fail";
			$return["error"]["message"] = __("API request could not be sent.","wprobot");	
			return $return;						
		}
		
		if (isset($pxml->Error)) {
			$message = '<p>'.__("There was a problem with your Amazon API request. This is the error Amazon returned:","wprobot").'</p>
			<p><i><b>'.$pxml->Error->Code.':</b> '.$pxml->Error->Message.'</i></p>';	
			$return["error"]["module"] = "Amazon";
			$return["error"]["reason"] = "API fail";
			$return["error"]["message"] = $message;	
			return $return;
		}	

		if (isset($pxml->Items->Request->Errors->Error->Code)) {
			$message = '<p>'.__("There was a problem with your Amazon API request. This is the error Amazon returned:","wprobot").'</p>
			<p><i><b>'.$pxml->Items->Request->Errors->Error->Code.':</b> '.$pxml->Items->Request->Errors->Error->Message.'</i></p>';
			$return["error"]["module"] = "Amazon";
			$return["error"]["reason"] = "API fail";
			$return["error"]["message"] = $message;	
			return $return;
		}			
		
		if (!$pxml->Items->Item) {
			$return["error"]["module"] = "Amazon";
			$return["error"]["reason"] = "No content";
			$return["error"]["message"] = __("No (more) products found for this keyword.","wprobot");	
			return $return;			
		}			

		foreach($pxml->Items->Item as $item) {	
			if($yy >= $cnum) {
			
				$skipit = 0;
				$skip = $options["wpr_aa_skip"];
				if($skip == "noimg" || $skip == "nox") {if(empty($item->MediumImage->URL) && empty($item->SmallImage->URL)) {$skipit = 1;}}	
				if($skip == "nodesc" || $skip == "nox") {if(empty($item->EditorialReviews->EditorialReview->Content)) {$skipit = 1;}}			
				/*
				if($skipit == 1 && $count == 1) {
					$return["error"]["module"] = "Amazon";
					$return["error"]["reason"] = "No content";
					$return["error"]["message"] = __("Product skipped because no image and/or description was found.","wprobot");	
					return $return;					
				}*/

				if ($rr<$count_this && $skipit == 0) {
					$desc = "";					
					if (isset($item->EditorialReviews->EditorialReview)) {
						foreach($item->EditorialReviews->EditorialReview as $descs) {
							$desc .= $descs->Content;
						}		
					}	
					
					$elength = ($options['wpr_aa_excerptlength']);
					if ($elength != 'full') {
						$desc = strip_tags($desc,'<br>');
						$desc = substr($desc, 0, $elength);
					}				
					
					$features = "";
					if (isset($item->ItemAttributes->Feature)) {	
						$features = "<ul>";
						foreach($item->ItemAttributes->Feature as $feature) {
							$posx = strpos($feature, "href=");
							if ($posx === false) {
								$features .= "<li>".$feature."</li>";		
							}
						}	
						$features .= "</ul>";				
					}
					
					$timg = $item->MediumImage->URL;
					if($timg == "") {$timg = $item->SmallImage->URL;}				
					$thumbnail = '<a href="'.$item->DetailPageURL.'" rel="nofollow"><img style="float:left;margin: 0 20px 10px 0;" src="'.$timg.'" alt="'.$item->ItemAttributes->Title.'" /></a>';					
					$link = '<a href="'.$item->DetailPageURL.'" rel="nofollow">'.$item->ItemAttributes->Title.'</a>';	

					// REVIEWS -  Replace for Nov 11 API Change

					$product_review = $item->CustomerReviews->IFrameURL;
					$reviewsiframe = '<iframe style="margin-top: 10px;" src="'.$product_review.'" width="100%" height="450px"><p>Your browser does not support iframes.</p></iframe>';
					$revcontent = file_get_contents($product_review); 
					if (preg_match('~<body[^>]*>(.*?)</body>~si', $revcontent, $body)) { $reviewsnoiframe = str_replace('class="crVotingButtons">', "", $body[1]); } else {$reviewsnoiframe = "";} 
				

					$price = str_replace("$", "$ ", $item->OfferSummary->LowestNewPrice->FormattedPrice);
					
					$listprice = str_replace("$", "$ ", $item->ItemAttributes->ListPrice->FormattedPrice);

					if($price == "Too low to display" || $price == "Price too low to display") {
						$price = $listprice;
					}
					if(empty($listprice) && !empty($price)) {$listprice = $price;}
					if(empty($price) && !empty($listprice)) {$price = $listprice;}
					
					$content = $template;
		
					preg_match('#\[has_reviews\](.*)\[/has_reviews\]#smiU', $template, $check);
					if ($check[0] != false) {
						if(empty($item->CustomerReviews->TotalReviews)) {
							$content = str_replace($check[0], "", $content);
						}
					}	
					$content = str_replace(array("[has_reviews]","[/has_reviews]"), "", $content);		
					preg_match('#\[has_listprice\](.*)\[/has_listprice\]#smiU', $template, $matches);//print_r($matches);
					if ($matches[0] != false) {
						if(empty($listprice)) {
							$content = str_replace($matches[0], "", $content);
						}
					}
					$content = str_replace(array("[has_listprice]","[/has_listprice]"), "", $content);			
					
					$asin = $item->ASIN;
					$content = wpr_random_tags($content);				
					$content = str_replace("{title}", $item->ItemAttributes->Title, $content);
					$content = str_replace("{description}", $desc, $content);
					$content = str_replace("{features}", $features, $content);
					$content = str_replace("{timestamp}", date('m/d/Y h:i:s a', time()), $content);
					$content = str_replace("{thumbnail}", $thumbnail, $content);
					$content = str_replace("{smallimage}", $item->SmallImage->URL, $content);	
					$content = str_replace("{mediumimage}", $item->MediumImage->URL, $content);	
					$content = str_replace("{largeimage}", $item->LargeImage->URL, $content);
					$content = str_replace("{buynow}", '<a href="'.$item->DetailPageURL.'" rel="nofollow"><img src="'.$imagepath.'buynow-small.gif" /></a>', $content);		
					$content = str_replace("{buynow-big}", '<a href="'.$item->DetailPageURL.'" rel="nofollow"><img src="'.$imagepath.'buynow-big.gif" /></a>', $content);					
					$content = str_replace("{buynow-ger}", '<a href="'.$item->DetailPageURL.'" rel="nofollow"><img src="'.$imagepath.'buynow-ger.gif" /></a>', $content);					
					$content = str_replace("{price}", $price, $content);
					$content = str_replace("{listprice}", $listprice, $content);
					$savings = str_replace("$ ", "", $listprice) - str_replace("$ ", "", $price);
					$content = str_replace("{savings}", $savings, $content);
					//$discount = (($listprice - $price) / $listprice) * 100;
					//print_r($item);
					if($item->ItemAttributes->ListPrice->FormattedPrice > 0) {$discount = round (( (int) $savings / $item->ItemAttributes->ListPrice->FormattedPrice) * 100);}
					
					$content = str_replace("{discount}", $discount, $content);					
					$content = str_replace("{url}", $item->DetailPageURL, $content);	
					$content = str_replace("{avgrating}", $item->CustomerReviews->AverageRating, $content);	
					$content = str_replace("{reviewsnum}", $item->CustomerReviews->TotalReviews, $content);		
					$noqkeyword = str_replace('"', '', $keywords);
					$content = str_replace("{keyword}", $noqkeyword, $content);
					$content = str_replace("{Keyword}", ucwords($noqkeyword), $content);					
					$content = str_replace("{link}", $link, $content);
					$content = str_replace("{asin}", $asin, $content);
					$content = str_replace("{price-updating}", '[wpramaprice asin="'.$asin.'"]', $content);
					
					// rating
					if (strpos($content, "{rating}") != false) {			 
						$image = wpr_star_rating($item->CustomerReviews->AverageRating);
						$content = str_replace("{rating}",$image,$content);
					}	
					
					// reviews
					$content = str_replace("{reviews-url}", $product_review, $content);	
					//$content = str_replace("{reviews-iframe}", $reviewsiframe, $content);
					$content = str_replace("{reviews-iframe}", '[wpramareviews asin="'.$asin.'"]', $content);
					$content = str_replace("{reviews-noiframe}", $reviewsnoiframe, $content);					
					preg_match('#\{reviews(.*)\}#iU', $content, $rmatches);
					if ($rmatches[0] == false) {					
					} else {
						$content = str_replace($rmatches[0], '[wpramareviews asin="'.$asin.'"]', $content);				
					}	
					
					// split reviews into comments
					$dom = new DOMDocument();
					@$dom->loadHTML($reviewsnoiframe);	

					$xpath = new DOMXPath($dom);
					$paras = $xpath->query("//table[@class='crIFrameReviewList']//tr/td/div");
					
					$reviews = array();
					for ($y = 0;  $y < $paras->length; $y++ ) {  //$paras->length
						$para = $paras->item($y);
									
						$review = $dom->saveXml($para);	
						//$review = str_replace('', "", $review);
						//$review = str_replace('', "", $review);
						$review = str_replace('Permalink', "", $review);
						$review = str_replace('Report abuse', "", $review);
						$review = str_replace('See all my reviews', "", $review);
						$review = str_replace('<img src="http://g-ecx.images-amazon.com/images/G/01/x-locale/communities/reputation/c7y_badge_rn_1._V192249968_.gif" width="70" align="absmiddle" alt="(REAL NAME)" height="15" border="0" />', "", $review);
						//$review = preg_replace('#<div style="padding-top: 10px; clear: both; width: 100%;">(.*)<div class="reply">#smiU', '<div><div>', $review);
						$reviews[$y]["content"] = $review;						
					}	
					
					$xpath = new DOMXPath($dom);
					$paras = $xpath->query("//table[@class='crIFrameReviewList']//tr/td/div/div[3]/div/div/a/span");					
					for ($y = 0;  $y < $paras->length; $y++ ) {  //$paras->length
						$para = $paras->item($y);
						$author = $para->textContent; // $dom->saveXml($para);	
						
						$reviews[$y]["author"] = $author;						
					}
					
					//print_r($reviews);
					
					if(function_exists("wpr_translate_partial")) {
						$content = wpr_translate_partial($content);
					}
					if(function_exists("wpr_rewrite_partial")) {
						$content = wpr_rewrite_partial($content,$options);
					}
					
					if(!empty($item->MediumImage->URL)) {$largestimage = $item->MediumImage->URL;}
					elseif(!empty($item->SmallImage->URL)) {$largestimage = $item->SmallImage->URL;}
					elseif(!empty($item->LargeImage->URL)) {$largestimage = $item->LargeImage->URL;}					
					
					$customfield = array();
					$customfield["amazontitle"] = $item->ItemAttributes->Title;
					$customfield["amazonthumbnail"] = $largestimage;
					$customfield["amazonthumbnaillarge"] = $item->LargeImage->URL;					
					$customfield["amazonprice"] = $price;
					$customfield["amazonlink"] = $item->DetailPageURL;
					$customfield["amazonasin"] = $asin;
					$customfield["asin"] = $asin;
					$customfield["amazonrating"] = $item->CustomerReviews->AverageRating;
					
					$single = array();
					$single["unique"] = $asin;
					$single["title"] = $item->ItemAttributes->Title;
					if($options['wpr_aa_shortcode'] == "Yes") {
						$single["content"] = '[wpramazon asin="'.$asin.'"]';
					} else {
						$single["content"] = $content;
					}
					
					if($options['wpr_show_comments_settings'] != "Yes") {
						$single["comments"] = $reviews;	
					}
					
					$single["customfield"] = $customfield;	
					$added_post = $added_post + 1;
					array_push($return, $single);			
					$rr++;
				}
			}
			$yy++;
		}
		$page++;$cnum=0;
	}
	return $return;
}

function wpr_amazon_getlist($keywords,$count) {
	global $wpdb,$wpr_table_templates;

	$searchindex = "All";
	$maxitems = $count;
	$itemcount = 0;
	
	if($keywords == "") {
		$return["error"]["module"] = "Amazon";
		$return["error"]["reason"] = "No keyword";
		$return["error"]["message"] = __("No keyword specified.","wprobot");	
		return $return;	
	}
	
	$start = rand(1, 17);
	$start2 = $start / 10;
	$start2 = (string) $start2; 
	$start2 = explode(".", $start2);
	$page=(int)$start2[0];	
	$page++;				
	$cnum=(int)$start2[1]; 
		
	$imagepath = WP_PLUGIN_URL.'/'.str_replace(basename( __FILE__),"",plugin_basename(__FILE__)); 
	$imagepath = str_replace("/modules/","/images/",$imagepath);
	$options = unserialize(get_option("wpr_options"));	
	$public_key = $options['wpr_aa_apikey'];
	$private_key = $options['wpr_aa_secretkey'];
	$affid = $options['wpr_aa_affkey'];
	$added_post = 0;

	$return = array();
	$ads = "";
	$site = $options['wpr_aa_site'];	
	if($site == "us") {$site = "com";}
	if($site == "uk") {$site = "co.uk";} 	

	if($browsenode == "") {$browsenode = 0;}	

	while($added_post < $count) {	
		
		$pxml = wpr_aws_request($site, array(
		"Operation"=>"ItemSearch",
		"AssociateTag"=>$affid,
		"SearchIndex"=>"All",
		"Keywords"=>$keywords,
		"ItemPage"=>$page,
		"ReviewSort"=>"-HelpfulVotes",
		"ResponseGroup"=>"Large"
		), $public_key, $private_key);			
		if(!empty($pxml["error"])) {return $pxml;}
		//echo "<pre>";print_r($pxml);echo "</pre>";

		if($count<=10){
			$count_this = $count;
		} else{
			$count_this = 10;
		}
		$i=0;
		$yy=0;
		
		if (!$pxml) {
			$return["error"]["module"] = "Amazon";
			$return["error"]["reason"] = "Request fail";
			$return["error"]["message"] = __("API request could not be sent.","wprobot");	
			return $return;						
		}
		
		if (isset($pxml->Error)) {
			$message = '<p>'.__("There was a problem with your Amazon API request. This is the error Amazon returned:","wprobot").'</p>
			<p><i><b>'.$pxml->Error->Code.':</b> '.$pxml->Error->Message.'</i></p>';	
			$return["error"]["module"] = "Amazon";
			$return["error"]["reason"] = "API fail";
			$return["error"]["message"] = $message;	
			return $return;
		}	

		if (isset($pxml->Items->Request->Errors->Error->Code)) {
			$message = '<p>'.__("There was a problem with your Amazon API request. This is the error Amazon returned:","wprobot").'</p>
			<p><i><b>'.$pxml->Items->Request->Errors->Error->Code.':</b> '.$pxml->Items->Request->Errors->Error->Message.'</i></p>';
			$return["error"]["module"] = "Amazon";
			$return["error"]["reason"] = "API fail";
			$return["error"]["message"] = $message;	
			return $return;
		}			
		
		if (!$pxml->Items->Item) {
			$return["error"]["module"] = "Amazon";
			$return["error"]["reason"] = "No content";
			$return["error"]["message"] = __("No (more) products found for this keyword.","wprobot");	
			return $return;			
		}			

		foreach($pxml->Items->Item as $item) {
			if($yy >= $cnum) {
			
				$skipit = 0;
				$skip = $options["wpr_aa_skip"];
				if($skip == "noimg" || $skip == "nox") {if($item->MediumImage->URL == "" && $item->SmallImage->URL) {$skipit = 1;}}	
				if($skip == "nodesc" || $skip == "nox") {if($item->EditorialReviews->EditorialReview->Content == "") {$skipit = 1;}}			
			
				if ($i<$count_this && $skipit == 0) {
					
					$dcontent = "";
					if (isset($item->EditorialReviews->EditorialReview)) {	
						foreach($item->EditorialReviews->EditorialReview as $descs) {
							$dcontent .= $descs->Content;
						}

							$dcontent = strip_tags($dcontent);
							$dcontent = substr($dcontent, 0, 130);
							$dcontent .= '... ';
					}		

					$timg = $item->SmallImage->URL;			
					$thumbnail = '<a href="'.$item->DetailPageURL.'" rel="nofollow"><img style="float:left;margin: 0 20px 10px 0;" src="'.$timg.'" /></a>';					
					$link = '<a href="'.$item->DetailPageURL.'" rel="nofollow">'.$item->ItemAttributes->Title.'</a>';	
			
					if($maxitems >= $itemcount) {
						$ads .= '<div>'.$thumbnail.$link.'<br/>'.$dcontent.'<br/><div style="clear:both;"></div></div>';
						$itemcount++;
					}

					$added_post++;	
					$i++;
				}
			}
			$yy++;
		}
		$page++;$cnum=0;
	}
	return $ads;		
}

function wpr_amazon_options_default() {
	$options = array(
		"wpr_aa_affkey" => "",
		"wpr_aa_apikey" => "",
		"wpr_aa_secretkey" => "",
		"wpr_aa_skip" => "",
		"wpr_aa_revtemplate" => "<i>Review by {author} for {link}</i>&#13;<b>Rating: {rating}</b>&#13;{content}&#13;&#13;",
		"wpr_aa_excerptlength" => "500",
		"wpr_aa_site" => "us",
		"wpr_aa_shortcode" => "No"
	);
	return $options;
}

function wpr_amazon_options($options) {
	?>
	<h3 style="text-transform:uppercase;border-bottom: 1px solid #ccc;"><?php _e("Amazon Options","wprobot") ?></h3>
		<table class="addt" width="100%" cellspacing="2" cellpadding="5" class="editform"> 
			<tr <?php if($options['wpr_aa_affkey'] == "") {echo 'style="background:#F8E0E0;"';} ?> valign="top"> 
				<td width="40%" scope="row"><?php _e("Amazon Affiliate ID:","wprobot") ?></td> 
				<td><input size="40" name="wpr_aa_affkey" type="text" id="wpr_aa_affkey" value="<?php echo $options['wpr_aa_affkey'] ;?>"/>
				<!--Tooltip--><a class="tooltip" href="#">?<span><?php _e('This option is not required but you will only earn affiliate commission if you enter your Amazon affiliate ID.',"wprobot") ?></span></a>
			</td> 
			</tr>	
			<tr <?php if($options['wpr_aa_apikey'] == "") {echo 'style="background:#F8E0E0;"';} ?> valign="top"> 
				<td width="40%" scope="row"><?php _e("API Key (Access Key ID):","wprobot") ?></td> 
				<td><input size="40" name="wpr_aa_apikey" type="text" id="wpr_aa_apikey" value="<?php echo $options['wpr_aa_apikey'] ;?>"/>
				<!--Tooltip--><a target="_blank" class="tooltip" href="https://affiliate-program.amazon.com/gp/advertising/api/detail/main.html">?<span><?php _e('This setting is required for the Amazon module to work!<br/><br/><b>Click to get to the Amazon API sign up page!</b>',"wprobot") ?></span></a>
			</td> 
			</tr>	
			<tr <?php if($options['wpr_aa_secretkey'] == "") {echo 'style="background:#F8E0E0;"';} ?> valign="top"> 
				<td width="40%" scope="row"><?php _e("Secret Access Key:","wprobot") ?></td> 
				<td><input size="40" type="text" name="wpr_aa_secretkey" value="<?php echo $options['wpr_aa_secretkey'] ;?>"/>
				<!--Tooltip--><a target="_blank" class="tooltip" href="https://affiliate-program.amazon.com/gp/advertising/api/detail/main.html">?<span><?php _e('This setting is required for the Amazon module to work!<br/><br/><b>Click to get to the Amazon API sign up page!</b>',"wprobot") ?></span></a>
			</td> 
			</tr>				
			<tr valign="top"> 
				<td width="40%" scope="row"><?php _e("Skip Products If:","wprobot") ?></td> 
				<td>
				<select name="wpr_aa_skip" id="wpr_aa_skip">
					<option value="" <?php if ($options['wpr_aa_skip']==""){echo "selected";}?>><?php _e("Don't skip","wprobot") ?></option>
					<option value="nodesc" <?php if ($options['wpr_aa_skip']=="nodesc"){echo "selected";}?>><?php _e("No description found","wprobot") ?></option>
					<option value="noimg" <?php if ($options['wpr_aa_skip']=="noimg"){echo "selected";}?>><?php _e("No thumbnail image found","wprobot") ?></option>
					<option value="nox" <?php if ($options['wpr_aa_skip']=="nox"){echo "selected";}?>><?php _e("No description OR no thumbnail","wprobot") ?></option>
				</select>
				</td> 
			</tr>			
			<tr valign="top"> 
				<td width="40%" scope="row"><?php _e("Amazon Description Length:","wprobot") ?></td> 
				<td>
				<select name="wpr_aa_excerptlength" id="wpr_aa_excerptlength">
					<option value="250" <?php if ($options['wpr_aa_excerptlength']==250){echo "selected";}?>><?php _e("250 Characters","wprobot") ?></option>
					<option value="500" <?php if ($options['wpr_aa_excerptlength']==500){echo "selected";}?>><?php _e("500 Characters","wprobot") ?></option>
					<option value="750" <?php if ($options['wpr_aa_excerptlength']==750){echo "selected";}?>><?php _e("750 Characters","wprobot") ?></option>
					<option value="1000" <?php if ($options['wpr_aa_excerptlength']==1000){echo "selected";}?>><?php _e("1000 Characters","wprobot") ?></option>
					<option value="full" <?php if ($options['wpr_aa_excerptlength']=='full'){echo "selected";}?>><?php _e("Full Description","wprobot") ?></option>
				</select>				
				</td> 
			</tr>
			<tr valign="top"> 
				<td width="40%" scope="row"><?php _e("Amazon Website:","wprobot") ?></td> 
				<td>
				<select name="wpr_aa_site" id="wpr_aa_site">
					<option value="com" <?php if ($options['wpr_aa_site']=='com'){echo "selected";}?>>Amazon.com</option>
					<option value="co.uk" <?php if ($options['wpr_aa_site']=='co.uk'){echo "selected";}?>>Amazon.co.uk</option>
					<option value="de" <?php if ($options['wpr_aa_site']=='de'){echo "selected";}?>>Amazon.de</option>
					<option value="ca" <?php if ($options['wpr_aa_site']=='ca'){echo "selected";}?>>Amazon.ca</option>
					<option value="jp" <?php if ($options['wpr_aa_site']=='jp'){echo "selected";}?>>Amazon.jp</option>
					<option value="fr" <?php if ($options['wpr_aa_site']=='fr'){echo "selected";}?>>Amazon.fr</option>	
					<option value="it" <?php if ($options['wpr_aa_site']=='it'){echo "selected";}?>>Amazon.it</option>
					<option value="es" <?php if ($options['wpr_aa_site']=='es'){echo "selected";}?>>Amazon.es</option>
					<option value="cn" <?php if ($options['wpr_aa_site']=='cn'){echo "selected";}?>>Amazon.cn</option>
				</select>				
				</td> 
			</tr>	

			<tr valign="top"> 
				<td width="40%" scope="row"><?php _e("Post Shortcode:","wprobot") ?></td> 
				<td>
				<input name="wpr_aa_shortcode" type="checkbox" id="wpr_aa_shortcode" value="Yes" <?php if ($options['wpr_aa_shortcode']=='Yes') {echo "checked";} ?>/> <?php _e("Yes, create posts as self-updating WP shortcode.","wprobot") ?>		
				<!--Tooltip--><a class="tooltip" href="#">?<span><?php _e('If selected only a Wordpress shortcode will be entered for each Amazon product and the content is always retreived directly from the Amazon API, making sure all the information are always up to date.',"wprobot") ?></span></a>
				</td> 
			</tr>				
		<!--	<tr valign="top"> 
				<td width="40%" scope="row"><?php _e("Review Template:","wprobot") ?></td> 
				<td>			
				<textarea name="wpr_aa_revtemplate" rows="2" cols="32"><?php echo $options['wpr_aa_revtemplate'];?></textarea>	
				<a target="_blank" class="tooltip" href="http://wprobot.net/test/documentation/#33">?<span><?php _e('How the product reviews will look in posts and comments. <b>Click to see all available template tags in the documentation.</b>',"wprobot") ?></span></a>				
				</td> 
			</tr>	-->			
		</table>	
	<?php
}

function wpr_aws_getnodename($nodeid) {

	$options = unserialize(get_option("wpr_options"));	
	$public_key = $options['wpr_aa_apikey'];
	$affid = $options['wpr_aa_affkey'];	
	$private_key = $options['wpr_aa_secretkey'];
	$locale = $options['wpr_aa_site'];		
	if($locale == "us") {$locale = "com";}
	if($locale == "uk") {$locale = "co.uk";}	
	$pxml = wpr_aws_request($locale, array(
	"Operation"=>"BrowseNodeLookup",
	"BrowseNodeId"=>$nodeid,
	"AssociateTag"=>$affid,
	"ResponseGroup"=>"BrowseNodeInfo"
	), $public_key, $private_key);
	//echo "<pre>";print_r($pxml);echo "</pre>";
	if ($pxml === False) {
		return false;
	} else {
		if($pxml->BrowseNodes->BrowseNode->Name) {
			return $pxml->BrowseNodes->BrowseNode->Name;
		} elseif($pxml->BrowseNodes->Request->Errors->Error->Message) {
			$return["message"] = $pxml->BrowseNodes->Request->Errors->Error->Message;	
			return $return;		
		} else {
			return false;		
		}
	}
}

function wpr_ama_price($atts, $content = null) {

	$options = unserialize(get_option("wpr_options"));	
	$public_key = $options['wpr_aa_apikey'];
	$private_key = $options['wpr_aa_secretkey'];
	$affid = $options['wpr_aa_affkey'];	
	$locale = $options['wpr_aa_site'];		
	if($locale == "us") {$locale = "com";}
	if($locale == "uk") {$locale = "co.uk";}	
	$pxml = wpr_aws_request($locale, array(
	"Operation"=>"ItemLookup",
	"ItemId"=>$atts["asin"],
	"AssociateTag"=>$affid,
	//"IncludeReviewsSummary"=>"False",
	//"TruncateReviewsAt"=>"9216",
	"ResponseGroup"=>"OfferSummary"
	), $public_key, $private_key);
	//echo "<pre>";print_r($pxml);echo "</pre>";
	if ($pxml === False) {
		return false;
	} else {
		if($pxml->Items->Item->OfferSummary->LowestNewPrice->FormattedPrice) {
			$price = str_replace("$", "$ ", $pxml->Items->Item->OfferSummary->LowestNewPrice->FormattedPrice);
			if($price == "Too low to display" || $price == "Price too low to display") {
				$price = str_replace("$", "$ ", $item->ItemAttributes->ListPrice->FormattedPrice);
			}
			return $price;
		} else {
			return $content;	
		}
	}
}
add_shortcode('wpramaprice', 'wpr_ama_price' );

function wpr_ama_reviews($atts, $content = null) {

	$options = unserialize(get_option("wpr_options"));	
	$public_key = $options['wpr_aa_apikey'];
	$private_key = $options['wpr_aa_secretkey'];
	$locale = $options['wpr_aa_site'];		
	$affid = $options['wpr_aa_affkey'];	
	if($locale == "us") {$locale = "com";}
	if($locale == "uk") {$locale = "co.uk";}	
	$pxml = wpr_aws_request($locale, array(
	"Operation"=>"ItemLookup",
	"ItemId"=>$atts["asin"],
	"IncludeReviewsSummary"=>"False",
	"AssociateTag"=>$affid,
	"TruncateReviewsAt"=>"5000",
	"ResponseGroup"=>"Reviews"
	), $public_key, $private_key);
	//echo "<pre>";print_r($pxml);echo "</pre>";
	if ($pxml === False) {
		return false;
	} else {
		if($pxml->Items->Item->CustomerReviews->IFrameURL) {
		
			$product_review = $pxml->Items->Item->CustomerReviews->IFrameURL;
			$reviewsiframe = '<iframe style="margin-top: 10px;" src="'.$product_review.'" width="100%" height="450px"><p>Your browser does not support iframes.</p></iframe>';		
	
			return $reviewsiframe;
		} else {
			return $content;	
		}
	}
}
add_shortcode('wpramareviews', 'wpr_ama_reviews' );

function wpr_ama_handler($atts, $content = null) {
	global $wpdb,$wpr_table_templates;
	
	$template = $wpdb->get_var("SELECT content FROM " . $wpr_table_templates . " WHERE type = 'amazon'");

	$imagepath = WP_PLUGIN_URL.'/'.str_replace(basename( __FILE__),"",plugin_basename(__FILE__)); 
	$imagepath = str_replace("/modules/","/images/",$imagepath);	
	$options = unserialize(get_option("wpr_options"));	
	$public_key = $options['wpr_aa_apikey'];
	$private_key = $options['wpr_aa_secretkey'];
	$locale = $options['wpr_aa_site'];		
	$affid = $options['wpr_aa_affkey'];	
	if($locale == "us") {$locale = "com";}
	if($locale == "uk") {$locale = "co.uk";}	
	$pxml = wpr_aws_request($locale, array(
	"Operation"=>"ItemLookup",
	"ItemId"=>$atts["asin"],
	"IncludeReviewsSummary"=>"False",
	"AssociateTag"=>$affid,
	"TruncateReviewsAt"=>"5000",
	"ResponseGroup"=>"Large"
	), $public_key, $private_key);
	//echo "<pre>";print_r($pxml);echo "</pre>";
	if ($pxml === False) {
		return false;
	} else {
		if($pxml->Items->Item->CustomerReviews->IFrameURL) {
			foreach($pxml->Items->Item as $item) {	

				$desc = "";					
				if (isset($item->EditorialReviews->EditorialReview)) {
					foreach($item->EditorialReviews->EditorialReview as $descs) {
						$desc .= $descs->Content;
					}		
				}	
				
				$elength = ($options['wpr_aa_excerptlength']);
				if ($elength != 'full') {
					$desc = strip_tags($desc,'<br>');
					$desc = substr($desc, 0, $elength);
				}				
				
				$features = "";
				if (isset($item->ItemAttributes->Feature)) {	
					$features = "<ul>";
					foreach($item->ItemAttributes->Feature as $feature) {
						$posx = strpos($feature, "href=");
						if ($posx === false) {
							$features .= "<li>".$feature."</li>";		
						}
					}	
					$features .= "</ul>";				
				}
				
				$timg = $item->MediumImage->URL;
				if($timg == "") {$timg = $item->SmallImage->URL;}				
				$thumbnail = '<a href="'.$item->DetailPageURL.'" rel="nofollow"><img style="float:left;margin: 0 20px 10px 0;" src="'.$timg.'" /></a>';					
				$link = '<a href="'.$item->DetailPageURL.'" rel="nofollow">'.$item->ItemAttributes->Title.'</a>';	

				$product_review = $item->CustomerReviews->IFrameURL;
				$reviewsiframe = '<iframe style="margin-top: 10px;" src="'.$product_review.'" width="100%" height="450px"><p>Your browser does not support iframes.</p></iframe>';
				$revcontent = file_get_contents($product_review); 
				if (preg_match('~<body[^>]*>(.*?)</body>~si', $revcontent, $body)) { $reviewsnoiframe = str_replace('class="crVotingButtons">', "", $body[1]); } else {$reviewsnoiframe = "";} 
			

				$price = str_replace("$", "$ ", $item->OfferSummary->LowestNewPrice->FormattedPrice);
				$listprice = str_replace("$", "$ ", $item->ItemAttributes->ListPrice->FormattedPrice);

				if($price == "Too low to display" || $price == "Price too low to display") {
					$price = $listprice;
				}
				
				$acontent = $template;
	
				preg_match('#\[has_reviews\](.*)\[/has_reviews\]#smiU', $template, $check);
				if (!empty($check) && is_array($check) && $check[0] != false) {
					if(empty($item->CustomerReviews->TotalReviews)) {
						$acontent = str_replace($check[0], "", $acontent);
					}
				}	
				$acontent = str_replace(array("[has_reviews]","[/has_reviews]"), "", $acontent);		
				preg_match('#\[has_listprice\](.*)\[/has_listprice\]#smiU', $template, $matches);//print_r($matches);
				if ($matches[0] != false) {
					if(empty($listprice)) {
						$acontent = str_replace($matches[0], "", $acontent);
					}
				}
				$acontent = str_replace(array("[has_listprice]","[/has_listprice]"), "", $acontent);
				
				$asin = $item->ASIN;
				$acontent = wpr_random_tags($acontent);				
				$acontent = str_replace("{title}", $item->ItemAttributes->Title, $acontent);
				$acontent = str_replace("{description}", $desc, $acontent);
				$acontent = str_replace("{features}", $features, $acontent);
				$acontent = str_replace("{thumbnail}", $thumbnail, $acontent);
				$acontent = str_replace("{smallimage}", $item->SmallImage->URL, $acontent);	
				$acontent = str_replace("{mediumimage}", $item->MediumImage->URL, $acontent);	
				$acontent = str_replace("{largeimage}", $item->LargeImage->URL, $acontent);
				$acontent = str_replace("{buynow}", '<a href="'.$item->DetailPageURL.'" rel="nofollow"><img src="'.$imagepath.'buynow-small.gif" /></a>', $acontent);		
				$acontent = str_replace("{buynow-big}", '<a href="'.$item->DetailPageURL.'" rel="nofollow"><img src="'.$imagepath.'buynow-big.gif" /></a>', $acontent);					
				$acontent = str_replace("{buynow-ger}", '<a href="'.$item->DetailPageURL.'" rel="nofollow"><img src="'.$imagepath.'buynow-ger.gif" /></a>', $acontent);					
				$acontent = str_replace("{price}", $price, $acontent);
				$acontent = str_replace("{listprice}", $listprice, $acontent);
				$savings = str_replace("$ ", "", $listprice) - str_replace("$ ", "", $price);
				$acontent = str_replace("{savings}", $savings, $acontent);
				$acontent = str_replace("{url}", $item->DetailPageURL, $acontent);	
				$acontent = str_replace("{avgrating}", $item->CustomerReviews->AverageRating, $acontent);	
				$acontent = str_replace("{reviewsnum}", $item->CustomerReviews->TotalReviews, $acontent);	
				if(!empty($keywords)) {$noqkeyword = str_replace('"', '', $keywords);} else {$noqkeyword = "";}
				$acontent = str_replace("{keyword}", $noqkeyword, $acontent);
				$acontent = str_replace("{Keyword}", ucwords($noqkeyword), $acontent);					
				$acontent = str_replace("{link}", $link, $acontent);
				$acontent = str_replace("{asin}", $asin, $acontent);
				$acontent = str_replace("{price-updating}", $price, $acontent);
				
				// rating
				if (strpos($acontent, "{rating}") != false) {			 
					$image = wpr_star_rating($item->CustomerReviews->AverageRating);
					$acontent = str_replace("{rating}",$image,$acontent);
				}	
				
				// reviews
				$acontent = str_replace("{reviews-url}", $product_review, $acontent);	
				$acontent = str_replace("{reviews-iframe}", $reviewsiframe, $acontent);
				$acontent = str_replace("{reviews-noiframe}", $reviewsnoiframe, $acontent);					
				preg_match('#\{reviews(.*)\}#iU', $acontent, $rmatches);
				if (!empty($rmatches) && is_array($rmatches) && $rmatches[0] == false) {					
				} else {
					$acontent = str_replace($rmatches[0], $reviewsiframe, $acontent);				
				}			
			}		

			return $acontent;
		} else {
			return $content;	
		}
	}
}
add_shortcode('wpramazon', 'wpr_ama_handler' );

?>