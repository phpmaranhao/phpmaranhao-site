<?php

function wpr_plr_randomfile($folder='', $extensions='.*') {

    $folder = trim($folder);
    $folder = ($folder == '') ? './' : $folder;

    if (!is_dir($folder)){
		$plrcontent["error"]["module"] = "PLR";
		$plrcontent["error"]["reason"] = "No content";
		$plrcontent["error"]["message"] = __('The required "plr" folder does not exist inside your WP Robot directory.',"wprobot");	
		return $plrcontent;		
	}
 
    $files = array();
 
    if ($dir = @opendir($folder)){

        while($file = readdir($dir)){
 
            if (!preg_match('/^\.+$/', $file) and 
                preg_match('/\.('.$extensions.')$/', $file)){
 
                // feed the array:
                $files[] = $file;                
            }            
        }        
        // close directory
        closedir($dir);    
    }
    else {
		$plrcontent["error"]["module"] = "PLR";
		$plrcontent["error"]["reason"] = "No content";
		$plrcontent["error"]["message"] = __('The "plr" folder in your WP Robot directory could not be opened.',"wprobot");	
		return $plrcontent;	
    }
 
    if (count($files) == 0){
		$plrcontent["error"]["module"] = "PLR";
		$plrcontent["error"]["reason"] = "No content";
		$plrcontent["error"]["message"] = __('No files were found in the "plr" folder in your WP Robot directory - please copy your PLR content there before using the module.',"wprobot");	
		return $plrcontent;
    }
 
    mt_srand((double)microtime()*1000000); // seed random function:
    $rand = mt_rand(0, count($files)-1);// get an random index:
 
    if (!isset($files[$rand])) {
		$plrcontent["error"]["module"] = "PLR";
		$plrcontent["error"]["reason"] = "No content";
		$plrcontent["error"]["message"] = __('The "plr" folder in your WP Robot directory could not be opened.',"wprobot");	
		return $plrcontent;	
    }
 
    return $folder . $files[$rand];
 
}

function wpr_plrpost($keyword,$num,$start,$optional="",$comments="") {
	global $wpdb,$wpr_table_templates,$wpr_cache;

	/*if($keyword == "") {
		$return["error"]["module"] = "Yahoo News";
		$return["error"]["reason"] = "No keyword";
		$return["error"]["message"] = __("No keyword specified.","wprobot");
		return $return;	
	}
	*/
	$options = unserialize(get_option("wpr_options"));	
	
	$template = $wpdb->get_var("SELECT content FROM " . $wpr_table_templates . " WHERE type = 'plr'");
	if($template == false || empty($template)) {
		$template = "{article}";
	}
	
	$wpr_saveurl = WPR_URLPATH . "plr";

	$folder = $wpr_cache."/";
	$plrcontent = array();
	
	for ($i = 0; $i < $num; $i++) {
	
		$filename = wpr_plr_randomfile($folder,'txt|pdf');
			//echo $filename . "<br>";	
		$output = "";
		$title = "";
		
		if(is_array($filename) && !empty($filename["error"]["module"])) {
			return $filename;		
		}		
		
		$file = fopen($filename, "r");

		if(empty($file) || $file == false) {
			$plrcontent["error"]["module"] = "PLR";
			$plrcontent["error"]["reason"] = "No content";
			$plrcontent["error"]["message"] = __("No (more) PLR items found.","wprobot");	
			return $plrcontent;			
		}
		
		while(!feof($file)) {	
			if(empty($title)) {$title = fgets($file, 4096);}
			$output = $output . fgets($file, 4096);//read file line by line into variable		 
		}
		fclose($file);
		
		//$output = utf8_encode($output);
		if (function_exists('iconv')) {$output = iconv('UTF-8','ISO-8859-1//IGNORE', $output);}
			//echo $title . "<br>";
			//echo $output . "<br>";

		if(!empty($output) && !empty($title)) {
		
			// Keyword filter
			$kw = $keyword;
			if($kw != "" && $options['wpr_plr_filter']=='yes') {
				$kw = str_replace('"', '', $kw);
				
				$kws = explode(" AND ", $kw);
				//print_r($kws);
				foreach($kws as $kwx) {
					$kw2 = " ".$kwx." ";
					//echo $kw2."<br>";
					$c1 = stripos($output, $kw2);
					$c2 = stripos($title, $kw2);
					if($c1 != false || $c2 != false) {
						//echo "keyword was found<br>";
						$abort = 0;
					} else {
						//echo "keyword was not found<br>";			
						$plrcontent["error"]["module"] = "PLR";
						$plrcontent["error"]["reason"] = "IncNum";
						$plrcontent["error"]["message"] = __("File skipped because filter keyword was not found.","wprobot");	
						return $plrcontent;	
					}						
				}
			}			
		
			$content = $template;
			$content = wpr_random_tags($content);
			$content = str_replace("{article}", $output, $content);
			$content = str_replace("{title}", $title, $content);		
			$noqkeyword = str_replace('"', '', $keyword);
			$content = str_replace("{keyword}", $noqkeyword, $content);
			$content = str_replace("{Keyword}", ucwords($noqkeyword), $content);									
				if(function_exists("wpr_translate_partial")) {
					$content = wpr_translate_partial($content);
				}
				if(function_exists("wpr_rewrite_partial")) {
					$content = wpr_rewrite_partial($content,$options);
				}	
					
			$ren = rename($filename, $filename."2");
			if($ren == true) {} else {}		
		
			$plrcontent[$i]["unique"] = rand(1,10000);
			$plrcontent[$i]["title"] = $title;
			$plrcontent[$i]["content"] = $content;
		}
	}
		
	if(empty($plrcontent)) {
		$plrcontent["error"]["module"] = "PLR";
		$plrcontent["error"]["reason"] = "No content";
		$plrcontent["error"]["message"] = __("No (more) PLR items found.","wprobot");	
		return $plrcontent;		
	} else {
		return $plrcontent;	
	}
}

function wpr_plr_options_default() {
	$options = array(
		"wpr_plr_filter" => "no"
	);
	return $options;
}

function wpr_plr_options($options) {
	if(empty($options['wpr_yan_appkey']) && !empty($options['wpr_yap_appkey'])) {$options['wpr_yan_appkey'] = $options['wpr_yap_appkey'];}
	?>
	<h3 style="text-transform:uppercase;border-bottom: 1px solid #ccc;"><?php _e("PLR Module Options","wprobot") ?></h3>
	<p><i><?php _e("Please refer to the <a href=\"http://wprobot.net/documentation/#96\">documentation entry</a> for instructions on how to use this module!","wprobot") ?></i></p>	
		<table class="addt" width="100%" cellspacing="2" cellpadding="5" class="editform"> 	
			<tr valign="top"> 
				<td width="40%" scope="row"><?php _e("Filter","wprobot") ?></td> 
				<td><input name="wpr_plr_filter" type="checkbox" id="wpr_plr_filter" value="yes" <?php if ($options['wpr_plr_filter']=='yes') {echo "checked";} ?>/> <?php _e("Only create posts from text files that contain the associated keyword.","wprobot") ?>
				</td> 
			</tr>			
		</table>	
	<?php
}

?>