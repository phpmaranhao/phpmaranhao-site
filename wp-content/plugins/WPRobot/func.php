<?php

function wpr_save_all_images($content,$keyword,$insert) {
	$path = wp_upload_dir();
	$path = $path['baseurl'];
	$html = $content;
	if ( stripos( $html, '<img' ) !== false ) {

		$regex = '#<\s*img [^\>]*src\s*=\s*(["\'])(.*?)\1#im';
		preg_match_all( $regex, $html, $matches );

		if ( is_array( $matches ) && ! empty( $matches ) ) {
			$new = array();
			$old = array();

			require_once(ABSPATH . 'wp-admin/includes/file.php');
			require_once(ABSPATH . 'wp-admin/includes/media.php');
			require_once(ABSPATH . 'wp-admin/includes/image.php');
			
			foreach( $matches[2] as $img ) {

				if (  stripos( $img, $path ) !== false ) {
					continue;
				}

				$tmp = download_url( $img );

				preg_match('/[^\?]+\.(jpg|JPG|jpe|JPE|jpeg|JPEG|gif|GIF|png|PNG)/', $img, $matches);
				$file_array['name'] = basename($matches[0]);
				$file_array['tmp_name'] = $tmp;
				// If error storing temporarily, unlink
				if ( is_wp_error( $tmp ) ) {
					@unlink($file_array['tmp_name']);
					$file_array['tmp_name'] = '';
					continue;
				}
	
				$id = media_handle_sideload( $file_array, $insert );

				if ( ! is_wp_error( $id ) ) {
					$url  = wp_get_attachment_url( $id );
					$thumb = wp_get_attachment_thumb_url( $id );
					array_push( $new, $url );
					array_push( $old, $img );
				}
			}

			if( !empty( $new ) ) {
				$content = str_ireplace( $old, $new, $html );
				$post_args = array( 'ID' => $insert, 'post_content' => $content, );
				if (!empty($content))
					$post_id = wp_update_post( $post_args );
			}
		}
	} 
	return true;
}
/*
function wp_save_all_images_to_server_after($post_id, $post) {

	$options = get_option("wpr_options");	
	if($options["wpr_save_images"] == 1) {
	
		$html = $post->post_content;
		$path = wp_upload_dir();
		$path = $path['baseurl'];
		if ( stripos( $html, '<img' ) !== false ) {

			$regex = '#<\s*img [^\>]*src\s*=\s*(["\'])(.*?)\1#im';
			preg_match_all( $regex, $html, $matches );

			if ( is_array( $matches ) && ! empty( $matches ) ) {
				$new = array();
				$old = array();
				foreach( $matches[2] as $img ) {

					if (  stripos( $img, $path ) !== false ) {
						continue;
					}

					$tmp = download_url( $img );

					preg_match('/[^\?]+\.(jpg|JPG|jpe|JPE|jpeg|JPEG|gif|GIF|png|PNG)/', $img, $matches);
					$file_array['name'] = basename($matches[0]);
					$file_array['tmp_name'] = $tmp;
					// If error storing temporarily, unlink
					if ( is_wp_error( $tmp ) ) {
						@unlink($file_array['tmp_name']);
						$file_array['tmp_name'] = '';
						continue;
					}

					$id = media_handle_sideload( $file_array, $post->ID );

					if ( ! is_wp_error( $id ) ) {
						$url  = wp_get_attachment_url( $id );
						$thumb = wp_get_attachment_thumb_url( $id );
						array_push( $new, $url );
						array_push( $old, $img );

					}
				}
				if( !empty( $new ) ) {
					$content = str_ireplace( $old, $new, $html );
					$post_args = array( 'ID' => $post->ID, 'post_content' => $content, );
					if (!empty($content))
						$post_id = wp_update_post( $post_args );
				}
			}
		} 
	}
}
add_action( 'save_post', 'wp_save_all_images_to_server_after', 10, 2 );
*/

function wpr_findimages($content) {    
	//preg_match_all('/<img(.+?)src=\"(.+?)\"(.*?)>/', $content, $matches);
	preg_match_all('#<img(.*)src=\"(.*)\"(.*)>#smiU', $content, $matches);
	
	return $matches;
}

function wpr_saveimage($url,$keyword) {
	
	//if(strpos($url, "?") !== false) {return false;}

	$keyword = str_replace('"',"_",$keyword);	
	$keyword = urlencode($keyword);
	$keyword = str_replace("+","_",$keyword);
	$keyword = str_replace(" ","",$keyword);

	$contents = @file_get_contents($url);
	
	if ( function_exists('curl_init') && empty($contents) ) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (compatible; Konqueror/4.0; Microsoft Windows) KHTML/4.0.80 (like Gecko)");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_TIMEOUT, 60);
		$contents = curl_exec($ch);	
		curl_close($ch);
	}	
	
	$filename = substr(md5(time()), 0, 5) . '_' . $keyword . "_" . basename($url);
	$filename = str_replace(" ","",$filename);
	$filename = str_replace("%20","",$filename);
	$filename = str_replace("%","",$filename);
	$filename = str_replace('*',"_",$filename);
	
	if(strpos($filename, ".") === false) {$filename .= ".jpg";}

	$upload_dir = wp_upload_dir();
	$wpr_cache = $upload_dir['path'];
	$wpr_saveurl = $upload_dir['url'];
	//echo $filename;
	if(is_writable($wpr_cache) && $contents) { 
		file_put_contents($wpr_cache . '/' . $filename, $contents);
		return $wpr_saveurl . '/' . $filename;
	}

	return false;
}
  
function wpr_get_versions() {
   global $wpr_version;
   
	$version = @file_get_contents( 'http://wprobot.net/versions.php' );
	?>
	<div style="float:right;margin-top: 25px;">Version <?php echo $wpr_version; ?><?php if($wpr_version != $version && !empty($version)) {?> - <a style="color:#cc0000;" href="http://wprobot.net/robotpal/sendnew.php"><b><?php _e("Update available!","wprobot") ?></b></a><?php } ?>
	</div>
	<?php
}

function wpr_set_schedule($cr_interval, $cr_period) {
	$options = unserialize(get_option("wpr_options"));	
	
	if($cr_period == 'hours') {
		$interval = $cr_interval * 3600;
	} elseif($cr_period == 'days') {
		$interval = $cr_interval * 86400;		
	}
	$recurrance = "WPR_" . $cr_interval . "_" . $cr_period;

	//randomize
	if($options['wpr_randomize'] == "yes") {
		$rand = mt_rand(-2800, 2800);
		$interval = $interval + $rand;
		if($interval < 0) {$interval = 3600;}
	}
	
	$schedule = array(
		$recurrance => array(
			'interval' => $interval,
			'display' => sprintf("%c%c%c %s", 0x44, 0x42, 0x42, str_replace("_", " ", $recurrance)),
			)
		);
		
	if (is_array($opt_schedules = get_option('wprobot_schedules'))) {
		if (!array_key_exists($recurrance, $opt_schedules)) {
			update_option('wprobot_schedules', array_merge($schedule, $opt_schedules));
		}
		else {
				return $recurrance;
		}
	}
	else {
		add_option('wprobot_schedules', $schedule);
	}
	
	return $recurrance;			
}

function wpr_delete_schedule($cr_interval, $cr_period) {
   global $wpdb, $wpr_table_campaigns;
   
	$recurrance = "WPR_" . $cr_interval . "_" . $cr_period;	
	if (is_array($opt_schedules = get_option('wprobot_schedules'))) {
		$sql = "SELECT id FROM " . $wpr_table_campaigns . " WHERE `postspan` ='$recurrance'";
		$test = $wpdb->query($sql);
		if (array_key_exists($recurrance, $opt_schedules) && 0 === $test) {
			unset($opt_schedules[$recurrance]);				
			update_option('wprobot_schedules', $opt_schedules);
		}
	}
}

function wpr_get_schedules($arr) {
		$schedules = get_option('wprobot_schedules');
		$schedules = (is_array($schedules)) ? $schedules : array();		
		return array_merge($schedules, $arr);
}
add_filter('cron_schedules', 'wpr_get_schedules', 1);

function wpr_strip_selected_tags($text, $tags = array()) {
    $args = func_get_args();
    $text = array_shift($args);
    $tags = func_num_args() > 2 ? array_diff($args,array($text))  : (array)$tags;
    foreach ($tags as $tag){
        while(preg_match('/<'.$tag.'(|\W[^>]*)>(.*)<\/'. $tag .'>/iusU', $text, $found)){
            $text = str_replace($found[0],$found[2],$text);
        }
    }
    return preg_replace('/(<('.join('|',$tags).')(|\W.*)\/>)/iusU', '', $text);
}

function wpr_check_unique_old($tocheck) {
	global $wpdb;
	$tocheck = $wpdb->escape($tocheck);
	$check = $wpdb->get_var("SELECT post_title FROM $wpdb->posts WHERE post_title = '$tocheck' LIMIT 1");

	if($check != false) {
		return $check;
	} else {
		$tocheck2 = sanitize_title($tocheck);
		$check2 = $wpdb->get_var("SELECT post_name FROM $wpdb->posts WHERE post_name = '$tocheck2' LIMIT 1");	

		if($check2 == false) {
			return false;		
		} else {
			return $check2;
		}	
	}
}

function wpr_check_unique($unique) {
	global $wpdb,$wpr_table_posts;
	
	if(empty($unique)) {return false;}
	
	$unique = $wpdb->escape($unique);
	$check = $wpdb->get_var("SELECT unique_id FROM ".$wpr_table_posts." WHERE unique_id = '$unique' LIMIT 1");

	if($check != false) {
		return $check;
	} else {
		return false;			
	}
}

function wpr_delete_campaign() {
   global $wpdb, $wpr_table_campaigns;

	$delete = $_POST["delete"];
	$array = implode(",", $delete);

	foreach ($_POST['delete']  as $key => $value) {
		$i = $value;
		$sql = "SELECT * FROM " . $wpr_table_campaigns . " WHERE id = '$i' LIMIT 1";
		$result = $wpdb->get_row($sql);	

		$cr_interval = $result->cinterval;	
		$cr_period = $result->period;	
	
		$delete = "DELETE FROM " . $wpr_table_campaigns . " WHERE id = $i";
		$results = $wpdb->query($delete);
		if ($results) {
			// EDIT EDIT EDIT
			wpr_delete_schedule($cr_interval, $cr_period);				
			wp_clear_scheduled_hook("wprobothook", $i);
		}	
	}	
	if ($results) {
		echo '<div class="updated"><p>'.__('Campaign has been deleted.', 'wprobot').'</p></div>';
	}
}

function wpr_rewrite_partial($content,$options="") {
	
	if(empty($options)) {$options = unserialize(get_option("wpr_options"));}
	$rewrite_use = $options['wpr_rewrite_use'];
	$rw_options = get_option('allrewriters_settings');
	if(empty($rw_options)) {	
		return $content;	
	} else {
		
		preg_match_all('#\[rewrite\](.*)\[/rewrite\]#smiU', $content, $matches, PREG_SET_ORDER);
		if ($matches) {
			foreach($matches as $match) {

				if($rewrite_use == "chain") {
				
					if(!empty($rw_options["general"]["options"]["chain"][1]) && !empty($rw_options["general"]["options"]["chain"][2])) {
						foreach($rw_options["general"]["options"]["chain"] as $ch => $chfunc) {
							if(!empty($chfunc)) {
								$result = $chfunc($content);
								if(is_array($result) && !empty($result["error"])) {	
								} else {
									$content = str_replace($match[0], $result, $content);	
								}									
							}
						}			
					}
				} elseif($rewrite_use == "rand") { // get random active rewriter
					$active_rw = array();
					foreach($rw_options as $module => $moduledata) {if(1 == $moduledata["enabled"]) {$active_rw[] = $module;}}
					$rand_key = array_rand($active_rw);
					$sel_rw = $active_rw[$rand_key];
				} else {
					$sel_rw = $rewrite_use;
				}
			
				$rw_func = $rw_options[$sel_rw]["function"];
						
				if(!empty($rw_func) && function_exists($rw_func)) {

					$result = $rw_func($content);
					if(is_array($result) && !empty($result["error"])) {
					} else {
						$content = str_replace($match[0], $result, $content);	
					}

				}	

				$content = str_replace(array("[rewrite]","[/rewrite]"), "", $content);	
				return $content;
			}
		} else {
			return $content;	
		}	
	}
}

add_action( 'admin_head', 'wpr_option_page_icon' );
 
function wpr_option_page_icon() {
    ?>
    <style>
        /* Admin Menu - 16px
           Use only if you put your plugin or option page in the top level via add_menu_page()
        */
        #toplevel_page_wpr-campaigns .wp-menu-image {
            background: url(<?php echo WPR_URLPATH; ?>images/icon-adminmenu16-sprite.png) no-repeat 6px 6px !important;
        }
        /* We need to hide the generic.png img element inserted by default */
        #toplevel_page_wpr-campaigns .wp-menu-image img {
            display: none;
        }
        #toplevel_page_wpr-campaigns:hover .wp-menu-image, #toplevel_page_wpr-campaigns.wp-has-current-submenu .wp-menu-image {
            background-position: 11px -23px !important;
        }
         
        /* Option Screen - 32px */
        #wprobot.icon32 {
			float: left;
			height: 33px;
			margin: 9px 8px 0 0;
			width: 36px;		
			display: block;
			background-position: -137px -5px;
            background: url(<?php echo WPR_URLPATH; ?>images/icon-adminpage32.png) no-repeat left top !important;
        }

    </style>
<?php } 

?>