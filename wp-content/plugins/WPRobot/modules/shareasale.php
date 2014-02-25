<?php

function wpr_shareasalepost($keyword,$num,$start,$optional="",$comments="") {
	global $wpdb,$wpr_table_templates;

	$options = unserialize(get_option("wpr_options"));	// Load WP Robot Options Array
	$affid = $options['wpr_shareasale_aff']; // If necessary retreive the API key from the options...
	if(empty($affid)) {$affid = "435192";}
	
	if($keyword == "") { // If keyword is empty return error
		$return["error"]["module"] = "Shareasale";
		$return["error"]["reason"] = "No keyword";
		$return["error"]["message"] = __("No keyword specified.","wprobot");
		return $return;	
	}
	
	$template = $wpdb->get_var("SELECT content FROM " . $wpr_table_templates . " WHERE type = 'shareasale'");
	if($template == false || empty($template)) { // If module template is empty return error
		$return["error"]["module"] = "Shareasale";
		$return["error"]["reason"] = "No template";
		$return["error"]["message"] = __("Module Template does not exist or could not be loaded.","wprobot");
		return $return;	
	}	
	
	$x = 0;$itemcontent = array();
	if(empty($start)) {$start = 1;}
	if(empty($num)) {$num = 1;}	
	$total = $num + $start;
	
	if (($handle = fopen(WPR_URLPATH."datafeed.csv", "r")) !== FALSE) {
		for ($i=1; $i < $total; $i++) {
			if($i >= $start && $x != $num) {
				$data = fgetcsv($handle, 0, "|");	
				$xxx = count($data);
				//echo "<p> $xxx fields in line $i: <br /></p>\n";
				//print_r($data);
				
				$pid = $data[0];			
				$title = $data[1];								
				$url = str_replace("YOURUSERID", $affid, $data[4]);	
				$thumb = $data[5];
				$bigimage = $data[6];
				$description = $data[11];
				$merchant = $data[3];
				$custom1 = $data[12];
				$custom2 = $data[13];
				$custom3 = $data[14];				
				$category = $data[9];	
				$manufacturer = $data[19];	
				$isbn = $data[24];	
				$status = $data[18];					
				$price = "$ ".$data[7];	
				$listprice = "$ ".$data[8];	
					
				if($thumb != "") {$thumbnail = '<a href="'.$url.'" rel="nofollow"><img style="float:left;margin: 0 20px 10px 0;" src="'.$thumb.'" /></a>';} else {$thumbnail = '';}	
			
				$abort = 0;
				if($keyword != "" && $options['wpr_shareasale_filter']=='yes') {
					$keyword = str_replace('"', '', $keyword);
		
					$keywords = explode(" AND ", $keyword);
					foreach($keywords as $kwx) {
						$kw2 = " ".$kwx." ";
						//echo $kw2."<br/>";
						$c2 = stripos($description, $kw2);
						$c3 = stripos($title, $kw2);
						
						if($c2 != false || $c3 != false) {
							// echo "keyword was found $c2 $c3<br/>";
							$abort = 0;
						} else {
							//echo "keyword was not found<br/>";				
							$abort = 1;

						}						
					}
				}		
			
				$content = $template;
				$content = wpr_random_tags($content);
				$content = str_replace("{thumbnail}", $thumbnail, $content);
				$content = str_replace("{title}", $title, $content);
				$content = str_replace("{bigimage}", $bigimage, $content);
				$content = str_replace("{description}", $description, $content);
				$content = str_replace("{url}", $url, $content);						
				$content = str_replace("{keyword}", $keyword, $content);
				$content = str_replace("{merchant}", $merchant, $content);
				$content = str_replace("{custom1}", $custom1, $content);
				$content = str_replace("{custom2}", $custom2, $content);
				$content = str_replace("{custom3}", $custom3, $content);
				$content = str_replace("{category}", $category, $content);						
				$content = str_replace("{manufacturer}", $manufacturer, $content);
				$content = str_replace("{isbn}", $isbn, $content);
				$content = str_replace("{status}", $status, $content);
				$content = str_replace("{price}", $price, $content);						
				$content = str_replace("{listprice}", $listprice, $content);
					if(function_exists("wpr_translate_partial")) {
						$content = wpr_translate_partial($content);
					}
					if(function_exists("wpr_rewrite_partial")) {
						$content = wpr_rewrite_partial($content,$options);
					}
					
				$customfield = array();
				$customfield["custom1"] = $custom1;
				$customfield["custom2"] = $custom2;
				$customfield["custom3"] = $custom3;						
					
				if(empty($pid)) {$pid = $i;}
				$itemcontent[$x]["unique"] = $pid;
				$itemcontent[$x]["title"] = $title;
				$itemcontent[$x]["content"] = $content;	
				$itemcontent[$x]["customfield"] = $customfield;					
				$x++;			
			
			
			} else {
				$data = fgetcsv($handle, 0, "|");
				//echo "<p> Skipping over line $i: <br /></p>\n";				
			}
			if($abort == 1) {
				$return["error"]["module"] = "Shareasale";
				$return["error"]["reason"] = "IncNum";
				$return["error"]["message"] = __("Post skipped because your filter keyword has not been found in the datafeed entry.","wprobot");
				return $return;					
			}			
			
			if($data === false) {
				$return["error"]["module"] = "Shareasale";
				$return["error"]["reason"] = "No datafeed";
				$return["error"]["message"] = __("End of datafeed file reached.","wprobot");
				return $return;					
			}
		}
	} else {
		$return["error"]["module"] = "Shareasale";
		$return["error"]["reason"] = "No datafeed";
		$return["error"]["message"] = __("Datafeed file could not be opened or does not exist.","wprobot");
		return $return;		
	}	
	if(empty($itemcontent)) { // Return error if no content has been found.
		$itemcontent["error"]["module"] = "Shareasale";
		$itemcontent["error"]["reason"] = "No content";
		$itemcontent["error"]["message"] = __("No (more) Shareasale items found.","wprobot");	
		return $itemcontent;		
	} else {
		return $itemcontent;	
	}		
}

function wpr_shareasale_options_default() { // default options ARRAY for this module
	$options = array(
		"wpr_shareasale_aff" => "",
		"wpr_shareasale_filter" => "no",
		"wpr_shareasale_filepath" => ""
	);
	return $options;
}

function wpr_shareasale_options($options) { // module settings for the WP Robot "Options" screen
$filename = str_replace("modules/","",ABSPATH . "wp-content/plugins/". plugin_basename( dirname(__FILE__) )."/datafeed.csv");
$exists = file_exists($filename);
	?>
	<h3 style="text-transform:uppercase;border-bottom: 1px solid #ccc;"><?php _e("ShareASale Options","wprobot") ?></h3>	
<a href="http://wprobot.net/documentation/#95" target="_blank"><?php _e("Important Module Instructions &rarr;","wprobot") ?></a>	
		<table class="addt" width="100%" cellspacing="2" cellpadding="5" class="editform"> 
			<tr <?php if($exists == false) {echo 'style="background:#F8E0E0;"';} ?> valign="top"> 
				<td width="40%" scope="row"><?php _e("Datafeed File:","wprobot") ?></td> 
				<td>
<?php
if ($exists == true) {
    echo 'The required "datafeed.csv" file has been found inside your WPRobot directory.';
} else {
    echo 'The required "datafeed.csv" file has not been found inside your WPRobot directory!';
}
?>

				</td> 
			</tr>			
			<tr valign="top"> 
				<td width="40%" scope="row"><?php _e("Shareasale Affiliate ID:","wprobot") ?></td> 
				<td><input size="40" name="wpr_shareasale_aff" type="text" id="wpr_shareasale_aff" value="<?php echo $options['wpr_shareasale_aff'] ;?>"/>
				<!--Tooltip--><a target="_blank" class="tooltip" href="#">?<span><?php _e('This setting is required to earn affiliate commission from your Shareasale links!',"wprobot") ?></span></a>
			</td> 
			</tr>	
			<tr valign="top"> 
				<td width="40%" scope="row"><?php _e("Filter","wprobot") ?></td> 
				<td><input name="wpr_shareasale_filter" type="checkbox" id="wpr_shareasale_filter" value="yes" <?php if ($options['wpr_shareasale_filter']=='yes') {echo "checked";} ?>/> <?php _e("Only create posts from datafeeds that contain the associated keyword.","wprobot") ?>
				</td> 
			</tr>				
		</table>		
	<?php
}

?>