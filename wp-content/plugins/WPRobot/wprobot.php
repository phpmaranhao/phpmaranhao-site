<?php
/**
 Plugin Name: WP Robot 4
 Plugin URI: http://www.wprobot.net/
 Version: 4.03
 Description: Automatically post content related to any topic of your choice to your weblog.
 Author: WP Robot
 Author URI: http://goo.gl/RmEUNt
 License: Commercial. For personal use only. Not to give away or resell
*/
/*  Copyright 2009 - 2013 Thomas Hoefter
*/
error_reporting(E_ERROR | E_PARSE);

if (version_compare(PHP_VERSION, '5.0.0.', '<'))
{
	die(__("WP Robot requires php 5 or a greater version to work.", "wprobot"));
}

if (!defined('WP_CONTENT_URL')) {
   define('WP_CONTENT_URL', get_option('siteurl') . '/wp-content');
}

define('WPR_URLPATH', WP_PLUGIN_URL.'/'.plugin_basename( dirname(__FILE__) ).'/' );
$wpr_cache = ABSPATH . "wp-content/plugins/". plugin_basename( dirname(__FILE__) )."/plr";

function wpr_plugin_init () {
	$plugin_dir = basename(dirname(__FILE__));
	load_plugin_textdomain( 'wprobot', 'wp-content/plugins/' . $plugin_dir, $plugin_dir );
}
add_action ('init', 'wpr_plugin_init');

// Global Variables
$wpr_version = "4.03";
$wpr_table_campaigns = $wpdb->prefix . "wpr_campaigns";	
$wpr_table_templates = $wpdb->prefix . "wpr_templates";	
$wpr_table_posts = $wpdb->prefix . "wpr_posts";	
$wpr_table_errors = $wpdb->prefix . "wpr_errors";	
$wpr_modules = array("amazon","article","articlebuilder","clickbank","ebay","flickr","yahooanswers","yahoonews","youtube","rss","translation","twitter","commissionjunction","oodle","pressrelease","shopzilla","itunes","linkshare","eventful","yelp","shareasale","avantlink","plr","bigcontentsearch");

/*
add_action('init', 'wpr_activate_au');  
function wpr_activate_au() {  
	global $wpr_version;
	require_once ('wpr_autoupdate.php');  
	
	$opt = unserialize(get_option("wpr_options"));	
	$lemail = $opt['wpr_email'];	

	$wpr_plugin_current_version = $wpr_version;  
	$wpr_plugin_remote_path = 'http://wprobot.net/rpl/updater.php?email='.$lemail;  
	$wpr_plugin_slug = plugin_basename(__FILE__);  
	new wpr_auto_update ($wpr_plugin_current_version, $wpr_plugin_remote_path, $wpr_plugin_slug);  
}  */

@include_once("func.php");	
foreach ($wpr_modules as $module) {$inc = @include_once("modules/$module.php");if($inc == 1) {$wpr_loadedmodules[] = $module;}} // Modules

@include_once("wprupgrader.php");
@include_once("display-cl.php");
@include_once("AllRewriters/allrewriters.php");

function wpr_default_options($update=0) {
	global $wpr_modules;

	@include_once("default-options.php");
	
	$options = unserialize(get_option("wpr_options"));	

		$options['wpr_poststatus'] = 'published';
		$options['wpr_autotag'] = 'Yes';
		$options['wpr_resetcount'] = 'no';
		$options['wpr_badwords'] = 'what;which;where;when;does;that;with;while;then;your;other;have;make;will';
		$options['wpr_randomize'] = 'yes';
		$options['wpr_randomize_comments'] = 'no';
		$options['wpr_help'] = 'Yes';
		$options['wpr_openlinks'] = 'no';
		$options['wpr_authorid'] = 1;
		$options['wpr_err_retries'] = 4;
		$options['wpr_err_maxerr'] = 2;
		$options['wpr_err_minmod'] = 1;
		$options['wpr_err_disable'] = 30;
		$options["wpr_global_exclude"] = "";
		$options['wpr_check_unique_old'] = "No";
		$options['wpr_replace_keyword'] = "0";
		$options['wpr_save_images'] = "Yes";		
		//$options['wpr_rewrite_active'] = "No";
		//$options['wpr_rewrite_level'] = "r";		
		/*$options['wpr_rewrite_active_tbs'] = 0;
		$options['wpr_rewrite_active_sc'] = 0;
		$options['wpr_rewrite_active_schimp'] = 0;
		$options['wpr_rewrite_active_ucg'] = 0;
		$options['wpr_rewrite_active_sr'] = 0;
		$options['wpr_rewrite_active_wai'] = 0;		
		$options['wpr_rewrite_email'] = "";
		$options['wpr_rewrite_key'] = "";
		$options['wpr_tbs_rewrite_email'] = "";
		$options['wpr_tbs_rewrite_pw'] = "";
		$options['wpr_tbs_spintxt'] = "No";
		$options['wpr_tbs_quality'] = 1;
		$options['wpr_rewrite_protected'] = "";
		$options['wpr_sc_rewrite_email'] = "";
		$options['wpr_sc_rewrite_pw'] = "";
		$options['wpr_sc_quality'] = 0;
		$options['wpr_sc_port'] = 9001;	
		$options['wpr_sc_thesaurus'] = "English";
		$options['wpr_schimp_rewrite_email'] = "";
		$options['wpr_schimp_rewrite_pw'] = "";
		$options['wpr_schimp_quality'] = 3;	
		$options['wpr_sr_rewrite_email'] = "";
		$options['wpr_sr_rewrite_pw'] = "";
		$options['wpr_sr_quality'] = "medium";		
		$options['wpr_wai_rewrite_email'] = "";
		$options['wpr_wai_rewrite_pw'] = "";
		$options['wpr_wai_sentence'] = "on";
		$options['wpr_wai_paragraph'] = "";
		$options['wpr_wai_nooriginal'] = "";
		$options['wpr_wai_quality'] = "Regular";	*/
		$options['wpr_tbs_rewrite_title'] = "No";
		$options['wpr_rewrite_use'] = "rand";
		$options['wpr_trans_use_proxies'] = "no";
		$options['wpr_trans_proxies'] = "";			
		foreach($wpr_modules as $module) {
			$function = "wpr_".$module."_options_default";
			if(function_exists($function)) {
				$moptions = $function();
				foreach($moptions as $moption => $default) {
					if(!empty($defaults[$moption])) {
						$options[$moption] = $defaults[$moption];
					} else {
						$options[$moption] = "$default";
					}
				}
			}
		}
		
		if(!empty($defaults['wpr_poststatus'])) {$options['wpr_poststatus'] = $defaults['wpr_poststatus'];}
		if(!empty($defaults['wpr_autotag'])) {$options['wpr_autotag'] = $defaults['wpr_autotag'];}
		if(!empty($defaults['wpr_resetcount'])) {$options['wpr_resetcount'] = $defaults['wpr_resetcount'];}
		if(!empty($defaults['wpr_badwords'])) {$options['wpr_badwords'] = $defaults['wpr_badwords'];}
		if(!empty($defaults['wpr_randomize'])) {$options['wpr_randomize'] = $defaults['wpr_randomize'];}
		if(!empty($defaults['wpr_randomize_comments'])) {$options['wpr_randomize_comments'] = $defaults['wpr_randomize_comments'];}
		if(!empty($defaults['wpr_openlinks'])) {$options['wpr_openlinks'] = $defaults['wpr_openlinks'];}
		if(!empty($defaults['wpr_authorid'])) {$options['wpr_authorid'] = $defaults['wpr_authorid'];}		
		
		if(WPLANG == "de_DE") {
			$options["wpr_aa_site"] = "de";
			$options["wpr_eb_country"] = "77";
			$options["wpr_eb_lang"] = "de";
			$options["wpr_yt_lang"] = "de";
			$options["wpr_yan_lang"] = "de";
			$options["wpr_yap_lang"] = "de";
			$options["wpr_twitter_lang"] = "de";
			$options['wpr_badwords'] = 'weil;doch;als;bei;nun;jetzt;nur;der;die;das;wir;ihr;sie;sobald;darauf';
			$options["wpr_aa_revtemplate"] = "<i>Rezession von {author} &uuml;ber {link}</i>&#13;<b>Bewertung: {rating}</b>&#13;{content}&#13;&#13;";
		}
		
		$croncode = substr(md5(time()), 0, 9);
		add_option('wpr_cron',$croncode);	
		add_option('wpr_cloak',"No");		
		
	if($update == 1) {
		update_option("wpr_options", serialize($options));	
		return $options;
	} else {
		add_option("wpr_options", serialize($options));	
	}
}

function wpr_default_options_single($module,$options) {
	//$options = unserialize(get_option("wpr_options"));	
	$function = "wpr_".$module."_options_default";
	if(function_exists($function)) {
		$moptions = $function();
		foreach($moptions as $moption => $default) {
			if(!empty($defaults[$moption])) {
				$options[$moption] = $defaults[$moption];
			} else {
				$options[$moption] = "$default";
			}
		}
	}
	//update_option("wpr_options", serialize($options));
	return $options;
}

function wpr_activate() {
   global $wpdb;
   
    $wpr_db_ver = 96;
	$wpr_table_campaigns = $wpdb->prefix . "wpr_campaigns";	
	$wpr_table_templates = $wpdb->prefix . "wpr_templates";	
	$wpr_table_posts = $wpdb->prefix . "wpr_posts";	
	$wpr_table_errors = $wpdb->prefix . "wpr_errors";		
   
	if(get_option('wpr_db_ver') != $wpr_db_ver) {

	if ( !empty($wpdb->charset) )
		$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
	
    $sql[] = "CREATE TABLE ".$wpr_table_campaigns." (
        id BIGINT(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
		name VARCHAR(255) NOT NULL,
		ctype VARCHAR(255) NOT NULL,
		keywords longtext NOT NULL,
		categories longtext NOT NULL,
		templates longtext NOT NULL,
		cinterval BIGINT(20) NOT NULL,
		period VARCHAR(255) NOT NULL,
		postspan VARCHAR(255) NOT NULL,
		replacekws longtext NOT NULL,
		excludekws longtext NOT NULL,
		amazon_department VARCHAR(255) NOT NULL,
		ebay_cat VARCHAR(255) NOT NULL,
		yahoo_cat VARCHAR(255) NOT NULL,
		translation VARCHAR(255) NOT NULL,
		customfield longtext NOT NULL,
		posts_created BIGINT(20) NOT NULL DEFAULT 0,
		pause INT(1) NOT NULL DEFAULT 0	
		) {$charset_collate};";
		
    $sql[] = "CREATE TABLE ".$wpr_table_templates." (
        id BIGINT(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
		type VARCHAR(255) NOT NULL,
		typenum INT(4) NOT NULL DEFAULT 1,	
		content longtext NOT NULL,
		title longtext NOT NULL,
		comments_amazon INT(1) NOT NULL DEFAULT 0,
		comments_flickr INT(1) NOT NULL DEFAULT 0,
		comments_yahoo INT(1) NOT NULL DEFAULT 0,
		comments_youtube INT(1) NOT NULL DEFAULT 0,
		name VARCHAR(255) NOT NULL
		) {$charset_collate};";	

    $sql[] = "CREATE TABLE ".$wpr_table_posts." (
        id BIGINT(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
		campaign BIGINT(20) NOT NULL,
		keyword VARCHAR(255) NOT NULL,		
		module VARCHAR(255) NOT NULL,
		unique_id longtext NOT NULL,
		time VARCHAR(255) NOT NULL
		) {$charset_collate};";		
		
    $sql[] = "CREATE TABLE ".$wpr_table_errors." (
        id BIGINT(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
		campaign BIGINT(20) NOT NULL,
		keyword VARCHAR(255) NOT NULL,
		module VARCHAR(255) NOT NULL,	
		reason VARCHAR(255) NOT NULL,			
		message longtext NOT NULL,
		time VARCHAR(255) NOT NULL
		) {$charset_collate};";			

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);	
	
	update_option('wpr_db_ver',$wpr_db_ver);
	
	}			
}
register_activation_hook(__FILE__, 'wpr_activate');

function wpr_import_options() {
	global $wpr_modules;
	$options = unserialize(get_option("wpr_options"));	
	$options['wpr_poststatus'] = get_option('ma_poststatus');
	$options['wpr_autotag'] = get_option('ma_autotag');
	$options['wpr_resetcount'] = get_option('ma_resetcount');
	$options['wpr_badwords'] = get_option('ma_badwords');
	$options['wpr_randomize'] = get_option('ma_randomize');
	$options['wpr_authorid'] = get_option('ma_authorid');
	$options['wpr_check_unique_old'] = "Yes";
		foreach($wpr_modules as $module) {
			$function = "wpr_".$module."_options_default";
			if(function_exists($function)) {
				$moptions = $function();
				foreach($moptions as $moption => $default) {
					$oldoption = str_replace("wpr_","ma_",$moption);
					if( $oldoption != "ma_aa_revtemplate" ) {
						$geto = get_option($oldoption);
						if($geto != " " && !empty($geto)) {
							$options[$moption] = $geto;
						}
					}
				}
			}
		}
	update_option("wpr_options", serialize($options));
	return $options;
}

function wpr_add_pages() {

    add_menu_page('WP Robot 4', 'WP Robot 4', 8, 'wpr-campaigns', 'wpr_toplevel', 'none');
    add_submenu_page('wpr-campaigns', __('Campaigns', 'wprobot'), __('Campaigns', 'wprobot'), 8, 'wpr-campaigns', 'wpr_toplevel');	
	add_submenu_page('wpr-campaigns', __('Create Campaign', 'wprobot'), __('Create Campaign', 'wprobot'), 8, 'wpr-add', 'wpr_add');	
    add_submenu_page('wpr-campaigns', __('Options', 'wprobot'), __('Options', 'wprobot'), 8, 'wpr-options', 'wpr_sub_options');
    add_submenu_page('wpr-campaigns', __('Templates', 'wprobot'), __('Templates', 'wprobot'), 8, 'wpr-templates', 'wpr_sub_templates');
	add_submenu_page('wpr-campaigns', __('Log', 'wprobot'), __('Log', 'wprobot'), 8, 'wpr-log', 'wpr_errors');		
	$aropt = add_submenu_page('wpr-campaigns', 'All Rewriters', 'All Rewriters', 'manage_options', 'ar-settings', 'ar_settings_page');
	add_action( "admin_print_scripts-$aropt", 'ar_settings_page_scripts' );		
    add_submenu_page('wpr-campaigns', '', '', 8, 'wpr-single', 'wpr_single');	
		
}
add_action('admin_menu', 'wpr_add_pages');

function wpr_transform_cats($categories) {	
	for ($i = 0; $i < count($categories); $i++) {		
		$categories[$i][0]["id"] = $categories[$i]["id"];
		$categories[$i][0]["name"] = $categories[$i]["name"];
		unset($categories[$i]["id"]);
		unset($categories[$i]["name"]);
	}
	return $categories;
}				

function wpr_create_campaign() {
	global $wpdb, $wpr_table_campaigns, $wpr_table_templates,$wpr_loadedmodules;
	
	$options = unserialize(get_option("wpr_options"));	
	
	if($options['wpr_simple']=='Yes') {
		$totalchance = 0;
		foreach($wpr_loadedmodules as $lmodule) {
			if($_POST[$lmodule."chance"] > 0) {
				$totalchance = $totalchance + $_POST[$lmodule."chance"];
			}
		}	
			if($_POST["mixchance"] > 0) {
				$totalchance = $totalchance + $_POST["mixchance"];
			}		
	} else {
		for ($i = 1; $i <= $_POST['tnum']; $i++) {
			if($_POST["chance$i"] > 0) {
				$totalchance = $totalchance + $_POST["chance$i"];
			}
		}
	}
	
	if($_POST['keywords'] == "" && $_POST['type'] == "keyword") {
		echo '<div class="updated"><p>'.__("Please enter at least one keyword!", "wprobot").'</p></div>';	
	} elseif($_POST['feeds'] == "" && $_POST['type'] == "rss") {
		echo '<div class="updated"><p>'.__("Please enter at least one RSS feed!", "wprobot").'</p></div>';	
	} elseif($_POST['nodes'] == "" && $_POST['type'] == "nodes") {
		echo '<div class="updated"><p>'.__("Please enter at least one Amazon BrowseNode!", "wprobot").'</p></div>';			
	} elseif($_POST['categories'] == "") {
		echo '<div class="updated"><p>'.__("Please enter at least one category!", "wprobot").'</p></div>';	
	} elseif($_POST['interval'] == "") {
		echo '<div class="updated"><p>'.__("Please enter a post interval!", "wprobot").'</p></div>';		
	} elseif($_POST['name'] == "") {
		echo '<div class="updated"><p>'.__("Please enter a name for your campaign!", "wprobot").'</p></div>';	
	} elseif($_POST['title1'] == "" && $options['wpr_simple']!='Yes' || $_POST['content1'] == "" && $options['wpr_simple']!='Yes' || $_POST['chance1'] == "" && $options['wpr_simple']!='Yes') {
		echo '<div class="updated"><p>'.__('Please create at least one template for your campaign! Use the "Add Post Template" or "Quick Template Setup" buttons to add a template.', "wprobot").'</p></div>';
	} elseif($_POST['amazon_department'] == "All" && $_POST['type'] == "nodes") {
		echo '<div class="updated"><p>'.__("Amazon Department can not be 'All' in a BrowseNodes campaign! You have to select the correct Department your Nodes belong to (Amazon API requirement).", "wprobot").'</p></div>';	
	} elseif($totalchance != 100 && $options['wpr_simple']=='Yes' && $_POST['type'] == "keyword") {
		echo '<div class="updated"><p>'.__("Error: The sum of percentages for all Modules together has to be 100!", "wprobot").'</p></div>';		
	} elseif($totalchance != 100 && $options['wpr_simple']!='Yes') {
		echo '<div class="updated"><p>'.__("Error: The sum of the 'Chance of being used' fields for all post templates has to be 100! (Currently: ", "wprobot").$totalchance.')</p></div>';				
	} elseif($_POST["mixchance"] > 0 && $options['wpr_simple']=='Yes' && empty($_POST["mixcontent"]) && $_POST['type'] == "keyword") {	
		echo '<div class="updated"><p>'.__("Error: Template for Mixed Posts can not be empty if percentage for it is positive!", "wprobot").'</p></div>';		
	} else {	
	
		$type = $_POST['type'];
   
		// Keywords
		$_POST['keywords'] = stripslashes($_POST['keywords']);
		$keywordsinput = str_replace("\r", "", $_POST['keywords']);
		$keywordsinput = explode("\n", $keywordsinput);    
		
		$i=0;
		$keywords = array();
		
		if($_POST["edit"]) {// GET OLD KEYWORDS FOR NUMs $oldcamp->postspan
			$edit = 1;
			$oldcamp = $wpdb->get_row( "SELECT keywords,postspan FROM $wpr_table_campaigns WHERE id = '" . $_POST["edit"] . "'" );
			$oldkeywords = unserialize($oldcamp->keywords);	
		}

		if($type == "keyword") {
			foreach( $keywordsinput as $keyword) {
				if($keyword != "") {
				
					$keyword = explode("|", $keyword);    
				
					$keywords[$i] = array($keyword[0]);
					if($edit == 1) {
						$kwnums = false;
						foreach($oldkeywords as $key => $oldkeyword)  {
							if($oldkeyword[0] == $keyword[0]) {$kwnums = $oldkeyword[1];}
						}
					}
					if($kwnums != false) {
						$keywords[$i][] = $kwnums;
					} else {
						$keywords[$i][] = array("total" => 0);
					}
					$keywords[$i]["skipped"] = 0;
					
					// Add alternative keywords
					for ($y = 1; $y <= 5; $y++) {
						if(!empty($keyword[$y])) {
							$keywords[$i]["alternative"][$y] = $keyword[$y];
						}
					}
				}
				$i++;
			}
		} elseif($type == "rss") {
			$rssinput = str_replace("\r", "", $_POST['feeds']);
			$rssinput = explode("\n", $rssinput);
			foreach( $rssinput as $rss) {
				if($rss != "") {
					$keywords[$i] = array($keywordsinput[$i]);
					if($edit == 1) {
						$kwnums = false;
						foreach($oldkeywords as $key => $oldkeyword)  {
							if($oldkeyword[0] == $keywordsinput[$i]) {$kwnums = $oldkeyword[1];}
						}
					}					
					if($kwnums != false) {
						$keywords[$i][] = $kwnums;
					} else {
						$keywords[$i][] = array("total" => 0);
					}
					$keywords[$i]["skipped"] = 0;
					$keywords[$i]["feed"] = $rss;
				}
				$i++;	
			}
		} elseif($type == "nodes") {
			$nodesinput = str_replace("\r", "", $_POST['nodes']);
			$nodesinput = explode("\n", $nodesinput);	
			$failcount = 0;
			foreach( $nodesinput as $node) {
				$nodename = wpr_aws_getnodename($node);
				if($node != "" && $nodename != false && !is_array($nodename)) {
					$keywords[$i] = array($keywordsinput[$i]);
					if($edit == 1) {
						$kwnums = false;
						foreach($oldkeywords as $key => $oldkeyword)  {
							if($oldkeyword[0] == $keywordsinput[$i]) {$kwnums = $oldkeyword[1];}
						}
					}					
					if($kwnums != false) {
						$keywords[$i][] = $kwnums;
					} else {
						$keywords[$i][] = array("total" => 0);
					}
					$keywords[$i]["skipped"] = 0;
					$keywords[$i]["bnn"] = "$nodename";					
					$keywords[$i]["node"] = $node;
				} else {
					$failcount++;
				}
				$i++;	
			}
			if($failcount > 0) {
				echo '<div class="updated"><p>'.__("<b>Error</b>: ","wprobot").$failcount.__(" Browsenodes could not be added to your campaign! Make sure to select the correct Amazon Department and the Amazon Site matching your Node ID. This is a requirement of the Amazon API.", "wprobot");						
				//if(is_array($nodename)) {
				//	echo __("<br/><br/>The last error Amazon returned was:<br/><i>","wprobot").$nodename["message"]."</i>";
				//}
				echo '</p></div>';
			}			
		}	
		$keywords = $wpdb->escape(serialize($keywords));		

		// Categories
		if($_POST['multisingle'] == "single") {
			$categorysave = array();
			$categorysave[0][0]["id"] = $_POST['categories'];
			$categorysave[0][0]["name"] = get_cat_name( $_POST['categories'] );
		} else {
			$categorysave = array();		
			$_POST['categories'] = stripslashes($_POST['categories']);			
			$categoriesinput = str_replace("\r", "", $_POST['categories']);
			$categoriesinput = explode("\n", $categoriesinput);
			$i = 0;	
			foreach($categoriesinput as $categories) {
				$categories = explode(",",$categories);
				$k = 0;	
				foreach($categories as $category) {
					if($category != "") {

						// if category starts with "-" get previous parent ID and set variable
						if($category[0] == '-') {
							$category = substr($category, 1);
							$parent = $parentid;
							$saveparent = 0;
						} else {
							$parent = "";
							$saveparent = 1;					
						}
						
						$category = str_replace("&", "&amp;", $category);

						$catname = ucwords($category);
						$category_ID = get_cat_ID( $category );		
						
						if(!$category_ID && $_POST['createcats'] == "yes") {
							if(is_numeric($parent)) {$parent = (int)$parent;} else {$parent = 0;}
	
								if(function_exists("wp_create_category")) {
									$category_ID = wp_create_category( $catname, $parent );
								} elseif(function_exists("wp_insert_category")) {
									$category_ID = wp_insert_category( array(
									  'cat_ID' => 0,
									  'cat_name' => $catname,
									  'category_description' => "",
									  'category_nicename' => "",
									  'category_parent' => $parent,
									  'taxonomy' => 'category' ) );
								}
							
						} elseif(!$category_ID && $_POST['createcats'] != "yes") {
							$category_ID = 1;
							$catname = get_cat_name( $category_ID );
						} elseif(isset($category_ID) && $_POST['createcats'] != "yes") {
							$catname = get_cat_name( $category_ID );
						}					

						$categorysave[$i][$k]["name"] = $catname;
						$categorysave[$i][$k]["id"] = $category_ID;
						
						if($saveparent == 1) {$parentid = $category_ID;}
					}
					$k++;
				}	
				$i++;
			}	
			/*if(count($categories) == 1) {
				$categories["type"] = "single";
			} else {
				$categories["type"] = "multi";			
			}	*/	
		}//print_r($categorysave);
		$categorysave = $wpdb->escape(serialize($categorysave));
		
		// Templates
		$templates = array();
		if($options['wpr_simple']=='Yes' && $type == "keyword") {
			$i = 1;
			foreach($wpr_loadedmodules as $lmodule) {
				if($_POST[$lmodule."chance"] > 0) {
					$templates[$i]["title"] = "{".$lmodule."title}";
					$templates[$i]["content"] = "{".$lmodule."}";
					if($lmodule == "ebay" || $lmodule == "yahoonews") {$templates[$i]["content"] .= "\n{".$lmodule."}\n{".$lmodule."}";}
					$templates[$i]["chance"] = $_POST[$lmodule."chance"];
					if($lmodule == "amazon") {$templates[$i]["comments"]["amazon"] = 1;} else {$templates[$i]["comments"]["amazon"] = 0;}
					if($lmodule == "flickr") {$templates[$i]["comments"]["flickr"] = 1;} else {$templates[$i]["comments"]["flickr"] = 0;}
					if($lmodule == "yahooanswers") {$templates[$i]["comments"]["yahooanswers"] = 1;} else {$templates[$i]["comments"]["yahooanswers"] = 0;}
					if($lmodule == "youtube") {$templates[$i]["comments"]["youtube"] = 1;} else {$templates[$i]["comments"]["youtube"] = 0;}
					$i++;
				}
			}
			if($_POST["mixchance"] > 0) {
				$templates[$i]["title"] = "{title}";
				$templates[$i]["content"] = stripslashes($_POST["mixcontent"]);
				$templates[$i]["chance"] = $_POST["mixchance"];		
				$templates[$i]["comments"]["amazon"] = 1;
				$templates[$i]["comments"]["flickr"] = 1;
				$templates[$i]["comments"]["yahooanswers"] = 1;
				$templates[$i]["comments"]["youtube"] = 1;				
			}
		} else {
			for ($i = 1; $i <= $_POST['tnum']; $i++) {
				if($_POST["chance$i"] > 0) {
					$templates[$i]["title"] = stripslashes($_POST["title$i"]);
					$templates[$i]["content"] = stripslashes($_POST["content$i"]);
					$templates[$i]["chance"] = $_POST["chance$i"];
					$templates[$i]["comments"]["amazon"] = $_POST["comments_amazon$i"];
					$templates[$i]["comments"]["flickr"] = $_POST["comments_flickr$i"];
					$templates[$i]["comments"]["yahooanswers"] = $_POST["comments_yahoo$i"];
					$templates[$i]["comments"]["youtube"] = $_POST["comments_youtube$i"];
				}
			}
		}
		//$templates = array_values($templates); -- MAKES FIRST TEMPLATE "0"
		$templates = $wpdb->escape(serialize($templates));
		
		// Optional settings
		$amadept = $_POST['amazon_department'];

		$yahoocat = array();
		$yahoocat["ps"] = $_POST['wpr_poststatus'];
		$yahoocat["rw"] = $_POST['wpr_rewriter'];
		$yahoocat["a"] = $_POST['wpr_author'];
		$yahoocat["t"] = $_POST['wpr_postthumbs'];
		$yahoocat["pt"] = $_POST['wpr_posttype'];
		$yahoocat = $wpdb->escape(serialize($yahoocat));
		$ebaycat = $_POST['ebay_category'];
		
		$_POST['replace'] = stripslashes($_POST['replace']);
		$replaceinput = str_replace("\r", "", $_POST['replace']);
		$replaceinput = explode("\n", $replaceinput);    
		
		$i=0;
		$replaces = array();
		foreach( $replaceinput as $replace) {
			if($replace != "") {
				$replace = explode("|", $replace);  
				$replaces[$i]["from"] = $replace[0];
				$replaces[$i]["to"] = str_replace('\"', "", $replace[1]);
				$replaces[$i]["chance"] = $replace[2];
				$replaces[$i]["code"] = $replace[3];
			}
			$i++;
		}
		$replaces = $wpdb->escape(serialize($replaces));	
		
		$_POST['exclude'] = stripslashes($_POST['exclude']);
		$exclude = str_replace("\r", "", $_POST['exclude']);
		$exclude = explode("\n", $exclude);
		foreach($exclude as $key => $value) {
			if($value == "") {
				unset($array[$key]);
			}
		}
		$exclude = array_values($exclude); 
		$exclude = $wpdb->escape(serialize($exclude));
		
		$name = $_POST['name'];
		$postevery = $_POST['interval'];
		$cr_period = $_POST['period'];
		$postspan = "WPR_" . $postevery . "_" . $cr_period;	
		
		$customfield = array();
		for ($i = 1; $i <= $_POST['cfnum']; $i++) {
			if(!empty($_POST["cf_value$i"]) && !empty($_POST["cf_name$i"])) {
				$customfield[$i]["name"] = $_POST["cf_name$i"];
				$customfield[$i]["value"] = $_POST["cf_value$i"];
			}
		}
		$customfield = $wpdb->escape(serialize($customfield));
		
		$translation = array();
		$translation["chance"] = $_POST['transchance'];
		$translation["from"] = $_POST['trans1'];
		$translation["to1"] = $_POST['trans2'];
		$translation["to2"] = $_POST['trans3'];
		$translation["to3"] = $_POST['trans4'];
		$translation["comments"] = $_POST['trans_comments'];
		$translation = $wpdb->escape(serialize($translation));
		
		if($_POST['autopost'] == "yes") {
			$pause = 0;
		} else {
			$pause = 1;
		}
		
		if($_POST["edit"]) {
			$uid = $_POST["edit"];
			$update = "UPDATE " . $wpr_table_campaigns . " SET name = '$name', ctype = '$type', keywords = '$keywords'
					, categories = '$categorysave', templates = '$templates', cinterval = '$postevery', period = '$cr_period', postspan = '$postspan'
					, replacekws = '$replaces', excludekws = '$exclude', amazon_department = '$amadept', ebay_cat = '$ebaycat', yahoo_cat = '$yahoocat'
					, translation = '$translation', customfield = '$customfield', pause = '$pause' WHERE id = $uid";
			//echo $update . "<br>";
			$results = $wpdb->query($update);
			if ($results) {
				if($postspan != $oldcamp->postspan) {
					
					$timestamp = wp_next_scheduled( 'wprobothook', array($uid) );
					wp_unschedule_event($timestamp, 'wprobothook', array($uid) );
					
					$lag = $_POST['delaystart'] * 3600;
					wpr_set_schedule($postevery, $cr_period);
					wp_schedule_event( time()+rand(1,500)+$lag, $postspan, "wprobothook" , array($uid) );	
					//wp_reschedule_event( $oldcamp->postspan, $postspan, "wprobothook", array($uid) ); // wp_reschedule_event( time(), $postspan, "wprobothook", array($uid) );
					wpr_delete_schedule($oldcamp->cinterval, $oldcamp->period);
				}
				echo '<div class="updated"><p>'.__('Campaign has been updated! Go to the <a href="?page=wpr-single&id='.$uid.'">control panel</a> to view details.', "wprobot").'</p></div>';		
			} else {
				echo '<div class="updated"><p>'.__("Campaign could not be updated!", "wprobot").'</p></div>';			
			}		
		} else {
			$insert = "INSERT INTO " . $wpr_table_campaigns . " SET name = '$name', ctype = '$type', keywords = '$keywords'
			, categories = '$categorysave', templates = '$templates', cinterval = '$postevery', period = '$cr_period', postspan = '$postspan'
			, replacekws = '$replaces', excludekws = '$exclude', amazon_department = '$amadept', ebay_cat = '$ebaycat', yahoo_cat = '$yahoocat'
			, translation = '$translation', customfield = '$customfield', pause = '$pause'";
			$result = $wpdb->query($insert);
			$insid = mysql_insert_id();
			
			if ($result) {	
			
				$sql = "SELECT LAST_INSERT_ID() FROM " . $wpr_table_campaigns;
				$id = $wpdb->get_var($sql);	$linkid = $id;
				if($linkid != $insid) {$linkid = $insid;}
				
				wpr_set_schedule($postevery, $cr_period);	
				$lag = $_POST['delaystart'] * 3600;
				if($lag == "" || !is_numeric($lag) || $lag < 0) {$lag = 200;}	
				
				wp_schedule_event( time()+$lag, $postspan, "wprobothook" , array($id) );

				$next = wp_next_scheduled( "wprobothook", array($id) );
				if($next == 0 || $next == "0" || $next == null || $next == "") {
					wp_schedule_event( time()+$lag, $postspan, "wprobothook" , array($id) );
				}
				
				echo '<div class="updated"><p>';
				printf(__('Campaign "%1$s" has been added! Go to the <a href="?page=wpr-single&id=%2$s">control panel</a> to view details.', 'wprobot'), $name, $linkid);
				echo '</p></div>';		
			}
		}	
	}
}

function wpr_errors() {
	global $wpdb, $wpr_table_campaigns, $wpr_table_templates, $wpr_table_errors, $_wp_using_ext_object_cache;
	$_wp_using_ext_object_cache = false;
	
	$options = unserialize(get_option("wpr_options"));		
	if ($options['wpr_installed'] != "yes") {
		echo '<div class="wrap"><h2>WP Robot</h2><div class="updated"><h3>Installation</h3><p>'.__('Please go <a href="?page=wpr-campaigns">here</a> to finish the installation of WP Robot first.', 'wprobot').'</p></div></div>';
		return false;
	}		
	
	if(!$_GET['id']) {
		$where = "";
	} else {
		$id = $_GET['id'];
		$where = " WHERE campaign = '$id'";
	}
	
	if(!$_GET['keyword']) {
		$where2 = "";
	} else {
		$keyword = $_GET['keyword'];
		$where2 = " AND keyword = '$keyword'";
	}

	if($_POST['wpr_clear_log']) {
		$results = $wpdb->query("DELETE FROM $wpr_table_errors$where$where2;");	
		if($results) {
			echo '<div class="updated"><p>'.__('Log has been cleared.', 'wprobot').'</p></div>';		
		} else {
			echo '<div class="updated"><p>'.__('Log could not be cleared.', 'wprobot').'</p></div>';		
		}
	}			

	$errors = $wpdb->get_results("SELECT * FROM " . $wpr_table_errors . "$where$where2 ORDER BY id DESC LIMIT 100");  
	
	include("display-errors.php");

}

function wpr_campaign_controls() {
	global $wpdb, $wpr_table_campaigns, $wpr_table_templates;
	
	if($_POST['pause']) {
		if($_POST["delete"] == "" || $_POST["delete"] == 0 || $_POST["delete"] == null) {
			echo '<div class="updated"><p>'.__('Please select at least one campaign!', 'wprobot').'</p></div>';				
		} else {						
			foreach ($_POST['delete']  as $key => $value) {
				$result = $wpdb->update( $wpr_table_campaigns, array( 'pause' => 1 ), array( 'ID' => $value ), array( '%d' ), array( '%d' ) );
			}	
		}	
			echo '<div class="updated"><p>'.__('Campaigns have been paused.', 'wprobot').'</p></div>';			
	}

	if($_POST['continue']) {
		if($_POST["delete"] == "" || $_POST["delete"] == 0 || $_POST["delete"] == null) {
			echo '<div class="updated"><p>'.__('Please select at least one campaign!', 'wprobot').'</p></div>';				
		} else {						
			foreach ($_POST['delete']  as $key => $value) {
				$result = $wpdb->update( $wpr_table_campaigns, array( 'pause' => 0 ), array( 'ID' => $value ), array( '%d' ), array( '%d' ) );
			}	
		}	
			echo '<div class="updated"><p>'.__('Campaigns have been continued.', 'wprobot').'</p></div>';			
	}		
	
	if($_GET['pause']) {
		$pause = $_GET['pause'];
		$wpdb->update( $wpr_table_campaigns, array( 'pause' => 1 ), array( 'ID' => $pause ), array( '%d' ), array( '%d' ) );
		echo '<div class="updated"><p>'.__('Campaign has been paused.', 'wprobot').'</p></div>';	
	}	
	
	if($_GET['unpause']) {
		$pause = $_GET['unpause'];
		$wpdb->update( $wpr_table_campaigns, array( 'pause' => 0 ), array( 'ID' => $pause ), array( '%d' ), array( '%d' ) );
		echo '<div class="updated"><p>'.__('Campaign has been continued.', 'wprobot').'</p></div>';	
	}		
	
	if($_GET['delete'] && !$_POST['deleteall']) {
		$_POST["delete"] = array($_GET["delete"]);
		wpr_delete_campaign();
	}
	
	if($_POST['deleteall']) {
		if($_POST["delete"] == "" || $_POST["delete"] == 0 || $_POST["delete"] == null) {
			echo '<div class="updated"><p>Please select at least one campaign!</p></div>';				
		} else {
			wpr_delete_campaign();
		}
	}	
	
	if($_POST['wpr_runnow'] || $_GET['wpr_runnow']) {
		if($_POST['wpr_runnow']) {
			$_GET['wpr_runnow'] = false;
			if($_POST["delete"] == "" || $_POST["delete"] == 0 || $_POST["delete"] == null) {
				echo '<div class="updated"><p>'.__('Please select at least one campaign!', 'wprobot').'</p></div>';				
			} else {
				$posted = 0;
				$skipped = 0;
				$bulk = $_POST["wpr_bulk"];
				$delete = $_POST["delete"];
				$array = implode(",", $delete);
				
				if($bulk == "" || $bulk == 0 || $bulk == null) {$bulk = 1;}
				
				if($_POST['time'] && $_POST['backdate'] == "yes") {
					$sp1 = $_POST['timespace'];
					$sp2 = $_POST['timespace2'];
					$time = explode("-", $_POST['time']);					
				}			
				
				for($i=0; $i < $bulk; $i++) { 
					foreach ($_POST['delete']  as $key => $value) {
					
						if($_POST['kws']) {
							$keywords = $_POST['kws'];
							$keyword = $keywords[array_rand($keywords)];
						} else {
							$keyword = "";
						}
					
						if($_POST['time'] && $_POST['backdate'] == "yes") {				
							$comment_date = mktime(rand(0,23), rand(0, 59), rand(0, 59), $time[1], $time[2] + $i* rand($sp1,$sp2), $time[0]);
							$_POST['postdate']=date("Y-m-d H:i:s", $comment_date);									
						}			
						$result = wpr_poster($value,$keyword,1);
						if($result == true) {$posted++;} else {$skipped++;}
					}	
				}
				if($posted > 0) {

					echo '<div class="updated"><p>';
					printf(__('%1$s posts have been created successfully %2$s', 'wprobot'), $posted, $sktxt);
					if($skipped > 0) {printf(__(' and %1$s posts have been skipped. Please see the <a href="?page=wpr-log">error log</a> for details.', 'wprobot'), $skipped);} else {$sktxt = '';}					
					echo '</p></div>';	
				} else {
					echo '<div class="updated"><p>';
					printf(__('Error: %1$s posts could not be created. Please see the <a href="?page=wpr-log">error log</a> for details.', 'wprobot'), $skipped);
					echo '</p></div>';						
				}
			}
		} elseif($_GET['wpr_runnow'] && !$_POST['wpr_post'] && !$_POST['wpr_deleteit']) {
			$result = wpr_poster($_GET['wpr_runnow'],$_GET['keyword'],1);	
			if($result == true) {
				echo '<div class="updated"><p>'.__('Post has been created successfully!', 'wprobot').'</p></div>';		
			} else {
				echo '<div class="updated"><p>';
				printf(__('Error: Post could not be created. Please see the <a href="?page=wpr-log&id=%1$s">error log</a> for details.', 'wprobot'), $_GET['wpr_runnow']);
				echo '</p></div>';		
			}
		}	
	}
}

function wpr_single() {
	global $wpdb, $wpr_table_campaigns, $wpr_table_templates, $wpr_table_errors, $_wp_using_ext_object_cache;
	$_wp_using_ext_object_cache = false;
	
	wpr_campaign_controls();
	$options = unserialize(get_option("wpr_options"));	
	
	if(!$_GET['id']) {
		_e("Error: No Campaign ID specified", 'wprobot');
	} else {
		$id = $_GET['id'];
		$result = $wpdb->get_row("SELECT * FROM " . $wpr_table_campaigns . " WHERE id = '$id'");  
		$keywords = unserialize($result->keywords);	
		$categories = unserialize($result->categories);	
		if(!empty($categories[0]["id"])) {$categories = wpr_transform_cats($categories);}
		$templates = unserialize($result->templates);
	
		if($_POST['resetkws']){	
			$i = 0;
			foreach($keywords as $keyword) {
				if($keywords[$i]["skipped"] > 2) {
					$keywords[$i]["skipped"] = 0;	
				}
				$i++;
			}
			$keywords2 = $wpdb->escape(serialize($keywords));
			$sql = "UPDATE " . $wpr_table_campaigns . " SET `keywords` = '".$keywords2."' WHERE `id` = '".$id."'";

			$results = $wpdb->query($sql);	
			if ($results) {				
				echo '<div class="updated"><p>'.__('All disabled keywords have been reset.', 'wprobot').'</p></div>';
				$result = $wpdb->get_row("SELECT * FROM " . $wpr_table_campaigns . " WHERE id = '$id';");  
				$keywords = unserialize($result->keywords);					
			} else {
				echo '<div class="updated"><p>'.__('Error: Keywords could not be reset!', 'wprobot').'</p></div>';				
			}				
		}	
		
		if($_POST['deletekws']){
			$dkws = $_POST['kws'];		
			if(!empty($dkws)) {
				foreach($keywords as $key => $keyword) {
					if($result->ctype == "keyword") {$ksearch = $keyword[0];} elseif($result->ctype == "rss") {$ksearch = $keyword["feed"];} elseif($result->ctype == "nodes") {$ksearch = $keyword["node"];}
					if(in_array($ksearch, $dkws)) {
						unset($keywords[$key]);
						unset($categories[$key]);
					}
				}
				$keywords = array_values($keywords);
				$categories = array_values($categories); // PROBLEM: MAKES [type] value to [1] !!!
				$keywords2 = serialize($keywords);	
				$categories2 = serialize($categories);	
				$sql = "UPDATE " . $wpr_table_campaigns . " SET `keywords` = '".$keywords2."', `categories` = '".$categories2."' WHERE `id` = '".$id."'";
				$results = $wpdb->query($sql);
				if ($results) {				
					echo '<div class="updated"><p>'.__('Keywords have been deleted.', 'wprobot').'</p></div>';		
				} else {
					echo '<div class="updated"><p>'.__('Error: Keywords could not be deleted!', 'wprobot').'</p></div>';				
				}
			} else {
				echo '<div class="updated"><p>'.__('Error: Please select at least one keyword!', 'wprobot').'</p></div>';					
			}			
		}
		
		if($_GET['kwdelete']){
			foreach($keywords as $key => $keyword) {
				if($keyword[0] == $_GET['keyword']) {
					unset($keywords[$key]);
					unset($categories[$key]);
				}
			}
			$keywords = array_values($keywords);
			$categories = array_values($categories);
			$keywords2 = serialize($keywords);	
			$categories2 = serialize($categories);	
			$sql = "UPDATE " . $wpr_table_campaigns . " SET `keywords` = '".$keywords2."', `categories` = '".$categories2."' WHERE `id` = '".$id."'";
			$results = $wpdb->query($sql);
			if ($results) {				
				echo '<div class="updated"><p>'.__('Keyword has been deleted.', 'wprobot').'</p></div>';		
			} else {
				echo '<div class="updated"><p>'.__('Error: Keyword could not be deleted!', 'wprobot').'</p></div>';				
			}			
		}
		
		$errors = $wpdb->get_results("SELECT * FROM " . $wpr_table_errors . " WHERE campaign = '$id' ORDER BY id DESC LIMIT 10");  
		
		include("display-single.php");
	}	
}

function wpr_install_new_modules($newmodules,$options) {
	global $wpr_loadedmodules,$wpdb,$wpr_table_templates;
	
	$email = $options["wpr_email"];
	if ( function_exists('curl_init') ) {
		$request = "http://wprobot.net/robotpal/wprinstall.php";
		$newmodules2 = serialize($newmodules);
		if(WPLANG == "de_DE") {$ger = 1;} elseif(WPLANG == "zh_CN") {$ger = 2;} else {$ger = 0;}			
		$post="email=".base64_encode($email)."&modules=".$newmodules2."&ger=".$ger;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (compatible; Konqueror/4.0; Microsoft Windows) KHTML/4.0.80 (like Gecko)");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_URL, $request);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
		curl_setopt($ch, CURLOPT_TIMEOUT, 60);
		$response = curl_exec($ch);
		curl_close($ch);
	} else { 				
		$response = @file_get_contents($request);
	}	
	
	if( $response == "false" || !$response) {
	} else {
		$responses = explode("###",$response);
		$sql = $responses[1];
		$options = unserialize(get_option("wpr_options"));	

		if(isset($sql)) {
			$sql = str_replace("{wpr_template}",$wpr_table_templates,$sql);
			$result = $wpdb->query($sql);
		}
		if($result) {
			foreach($newmodules as $module) {
				$options = wpr_default_options_single($module,$options);
			}
			$options["wpr_installed_modules"] = $wpr_loadedmodules;
			update_option("wpr_options", serialize($options));	
			echo '<div class="updated"><p>'.__('New module files have been found and installed successfully.', 'wprobot').'</p></div>';	
		} else {
			echo '<div class="updated"><p>'.__('Error: New module files have been found but installation failed. Is the Paypal Email you have entered in the Options still correct?', 'wprobot').'</p></div>';		
		}
	}	
}

function wpr_install_check_modules($options) {
	global $wpr_loadedmodules;
	
	$installed_modules = $options["wpr_installed_modules"];
	
	$newmodules = array();
	if(is_array($installed_modules)) { 
		foreach($wpr_loadedmodules as $module) {
			if(!in_array($module, $installed_modules)) {
				if($module != "translation" && $module != "articlebuilder" && $module != "bigcontentsearch") {
					$newmodules[] = $module;
				}
			}
		}
	}

	if(!empty($newmodules)) {wpr_install_new_modules($newmodules,$options);}
}

function wpr_update_email($currentemail,$newemail,$updatecore=0) {

	if($currentemail == $newemail && $updatecore == 0) {
		echo '<div class="updated"><p>'.__('Error: Email has not been changed.', 'wprobot').'</p></div>';		
		return false;
	} elseif(empty($newemail)) {
		echo '<div class="updated"><p>'.__('Error: Email can not be empty.', 'wprobot').'</p></div>';	
		return false;
	}

	// Send email AND modules to Server
	if ( function_exists('curl_init') ) {
		$request = "http://wprobot.net/robotpal/wprinstall.php";
		$post="email=".base64_encode($newemail)."&modules=&site=".get_bloginfo('url');
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (compatible; Konqueror/4.0; Microsoft Windows) KHTML/4.0.80 (like Gecko)");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_URL, $request);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
		curl_setopt($ch, CURLOPT_TIMEOUT, 60);
		$response = curl_exec($ch);
		if (!$response) {
			echo '<div class="updated"><p>'.__('cURL Error: ', 'wprobot').' '.curl_error($ch).'</p></div>';	
			return false;
		}		
		curl_close($ch);
	} else { 				
		$response = @file_get_contents($request);
		if (!$response) {
			echo '<div class="updated"><p>'.__('Error: cURL is not installed on this server.', 'wprobot').'</p></div>';	
			return false;
		}
	}	

	if( $response == "false" || !$response) {
		echo '<div class="updated"><p>'.__('Error: No record was found for the email you entered.', 'wprobot').'</p></div>';
	} else {
		$responses = explode("###",$response);
		$sql = $responses[1];
		$core = $responses[0];
		$options = unserialize(get_option("wpr_options"));		
		$options["wpr_email"] = $newemail;
		$options["wpr_core"] = $core;
		// - Setup Core Option
		if($core == "elite" || $core == "advanced" || $core == "developer" || $core == "devclient") {$options["createposts"] = "no";} else {$options["createposts"] = "yes";}
				
		update_option("wpr_options", serialize($options));	
		echo '<div class="updated"><p>'.__('Paypal Email has been updated successfully.', 'wprobot').'</p></div>';
		return $options["wpr_email"];	
	}
	
}

function wpr_install_function($email) {
	global $wpr_loadedmodules,$wpdb,$wpr_table_templates;
	
	$options = wpr_default_options(1);	
	
	if($email == "demo" || $email == "Demo" || $email == "DEMO") {

		// Insert Templates
		$sql = "INSERT INTO $wpr_table_templates ( type, typenum, content, title, comments_amazon, comments_flickr, comments_yahoo, comments_youtube, name ) VALUES ";		
		$sql .= " ( 'clickbank', '0', '<strong>{title}</strong>\r\n{description}\r\n{link}', '', '0', '0', '0', '0', '' ),";
		$sql .= " ( 'twitter', '0', '{tweet} - <i>by {author}</i>\r\n', '', '0', '0', '0', '0', '' ),";
		$sql .= " ( 'pressrelease', '0', '{pressrelease}\r\n', '', '0', '0', '0', '0', '' ),";
		$sql .= " ( 'shopzilla', '0', '<strong><a href=\"{url}\" rel=\"nofollow\">{title} [select:Price Comparison|Best Prices|Top Offers|Top Deals]</a></strong>\r\n{description}\r\n{offers}', '', '0', '0', '0', '0', '' ),";
		$sql .= " ( 'post', '5', '{shopzilla}', '{shopzillatitle}', '0', '0', '0', '0', 'Shopzilla Default' ),";
		$sql .= " ( 'post', '5', '{pressrelease}\r\n[random:50][select:More <a href=\"{catlink}\">{Keyword} Press Releases</a>|Related <a href=\"{catlink}\">{Keyword} Press Releases</a>|Find More <a href=\"{catlink}\">{Keyword} Press Releases</a>][/random]', '{pressreleasetitle}', '0', '0', '0', '0', 'Press Release Default' ),";
		$sql .= " ( 'post', '4', '[random:25]<p>[select:Check out these {keyword} products:|A few {keyword} products I can recommend:]</p>[/random]\r\n{clickbank}\r\n\r\n{clickbank}\r\n\r\n[random:25]{clickbank}[/random]', '{clickbanktitle}', '0', '0', '0', '0', 'Clickbank Default' );";		
		$result = $wpdb->query($sql);
					
		// - Setup Installed Option 	
		if($result) {
			$options["wpr_installed"] = "yes";
			$options["wpr_installed_modules"] = $wpr_loadedmodules;
			$options["wpr_email"] = "demo";
			$options["wpr_core"] = "basic";
			$options["createposts"] = "yes";
			update_option("wpr_options", serialize($options));
			$optionsurl = get_option('siteurl') . '/wp-admin/options-general.php';   
			$cronurl = WPR_URLPATH . 'cron.php?code='.get_option("wpr_cron");
			echo '<div class="wrap"><h2>WP Robot</h2><div class="updated" style="line-height:18px;">'.__('<h3 style="margin: 5px 0 0 0;">Success!</h3><b>Thanks for installing the WP Robot 4 Demo Version!</b> This demo can be used for an unlimited amount of time but only the Clickbank Module is active. Go here to <a href="http://wprobot.net/order/">purchase additional modules or the full version</a>. You can now navigate to the WP Robot admin area with the links on the bottom of the sidebar and might want to start by <a href="?page=wpr-options">setting up your options</a> and then <a href="?page=wpr-add">creating your first campaign</a>.
			<br/><br/>
			<h3 style="margin: 5px 0 0 0;">Autoposting in WP Robot</h3>
			Please read the following to understand how autoposting works in WP Robot and how to make sure that the plugin functions correctly for you.<br/><br/>
			
			<b>Wordpress Cron Functions</b><br/>
			By default WP Robot uses Wordpress\' built in cron functions to schedule posts. While no additional setup is necessary for those they have a few disadvantages, namely they are only run when a visitor visits your site, consume more server ressources and are not 100% reliable.<br/><br/>', 'wprobot');
			
			printf(__('To use the Wordpress cron functions make sure your server time setting and <a href="%1$s">Wordpress time settings</a> (i.e. timezone) are correct. The following should show the right time for where you are:', 'wprobot'), $optionsurl);
			echo '<br />'.
			__('<strong>UTC time: </strong>', 'wprobot').gmdate('d F, Y H:i:s', current_time('timestamp', true)).'<br />'.
			__('<strong>Your time: </strong>', 'wprobot').gmdate('d F, Y H:i:s', current_time('timestamp')).'<br /><br />'.
			
			__('Please also note that certain other 3rd party Wordpress plugins can interfere with WP Robots autoposting. If you ever see the year for next post dates as "1970" or "1969" this is most likely such a conflict and you should try disabling all your other plugins temporarily first of all before contacting support.<br/><br/>
			
			<b>Unix Cron Job</b><br/>
			As an alternative you can set up an Unix Cron Job in your webhosts control panel (often cPanel) to create automatic posts. This will use less ressources on your server and be more reliable than the Wordpress solution. To set up a cron job you need to use the following URL:', 'wprobot').'<br /><strong>'.
			$cronurl.
			'</strong><br /><br /></div></div>';		
		} else {
			echo '<div class="updated"><p>'.__('Error: WP Robot could not be installed.', 'wprobot').'</p></div>';		
		}	
	
	} else {

		// Send email AND modules to Server
		if ( function_exists('curl_init') ) {		
			$request = "http://wprobot.net/robotpal/wprinstall.php";
			$wpr_loadedmodules2 = serialize($wpr_loadedmodules);
			if(WPLANG == "de_DE") {$ger = 1;} else {$ger = 0;}		
			$post="email=".base64_encode($email)."&modules=".$wpr_loadedmodules2."&site=".get_bloginfo('url')."&ger=".$ger;
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (compatible; Konqueror/4.0; Microsoft Windows) KHTML/4.0.80 (like Gecko)");
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_URL, $request);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
			curl_setopt($ch, CURLOPT_TIMEOUT, 60);
			$response = curl_exec($ch);
			if (!$response) {
				$response = @file_get_contents($request);
				if (!$response) {			
				echo '<div class="updated"><p>'.__('cURL Error: ', 'wprobot').' '.curl_error($ch).'</p></div>';	
				return false;
				}
			}		
			curl_close($ch);
		} else { 				
			$response = @file_get_contents($request."?".$post);
			if (!$response) {
				echo '<div class="updated"><p>'.__('Error: cURL is not installed on this server.', 'wprobot').'</p></div>';	
				return false;
			}
		}	
//echo $response;
		// If FALSE -> Error	
		if( $response == "false" || !$response) {
			echo '<div class="updated"><p>'.__('Error: No record was found for the email you entered.', 'wprobot').'</p></div>';
			return false;
		} elseif( $response == "sitelimit") {	
			echo '<div class="updated"><p>'.__('Error: The maximum limit of installations has been reached for your license. Please note a license bought from a developer can only be used on up to three sites.', 'wprobot').'</p></div>';		
		} else {
			$responses = explode("###",$response);
			$sql = $responses[1];
			$core = $responses[0];

			if($core == "elite" || $core == "advanced" || $core == "developer" || $core == "devclient") {$options["createposts"] = "no";} else {$options["createposts"] = "yes";}
			
			if(isset($sql)) {
				$sql = str_replace("{wpr_template}",$wpr_table_templates,$sql);
				$result = $wpdb->query($sql);
			}
			
			if($result) {
				$options["wpr_installed"] = "yes";
				$options["wpr_installed_modules"] = $wpr_loadedmodules;
				$options["wpr_email"] = $email;
				$options["wpr_core"] = $core;
				$options["wpr_sql"] = "";
				update_option("wpr_options", serialize($options));				
				update_option("wpr_sql", serialize($sql));
				$optionsurl = get_option('siteurl') . '/wp-admin/options-general.php';   
				$cronurl = WPR_URLPATH . 'cron.php?code='.get_option("wpr_cron");
				echo '<div class="wrap"><h2>WP Robot</h2><div class="updated" style="line-height:18px;">'.__('<h3 style="margin: 5px 0 0 0;">Success!</h3><b>WP Robot has been installed successfully!</b> You can now navigate to the WP Robot admin area with the links on the bottom of the sidebar and might want to start by <a href="?page=wpr-options">setting up your options</a> and then <a href="?page=wpr-add">creating your first campaign</a>. Before that please read on.
				<br/><br/>
				<h3 style="margin: 5px 0 0 0;">Autoposting in WP Robot</h3>
				Please read the following to understand how autoposting works in WP Robot and how to make sure that the plugin functions correctly for you.<br/><br/>
				
				<b>Wordpress Cron Functions</b><br/>
				By default WP Robot uses Wordpress\' built in cron functions to schedule posts. While no additional setup is necessary for those they have a few disadvantages, namely they are only run when a visitor visits your site, consume more server ressources and are not 100% reliable.<br/><br/>', 'wprobot');
				
				printf(__('To use the Wordpress cron functions make sure your server time setting and <a href="%1$s">Wordpress time settings</a> (i.e. timezone) are correct. The following should show the right time for where you are:', 'wprobot'), $optionsurl);
				echo '<br />'.
				__('<strong>UTC time: </strong>', 'wprobot').gmdate('d F, Y H:i:s', current_time('timestamp', true)).'<br />'.
				__('<strong>Your time: </strong>', 'wprobot').gmdate('d F, Y H:i:s', current_time('timestamp')).'<br /><br />'.
				
				__('Please also note that certain other 3rd party Wordpress plugins can interfere with WP Robots autoposting. If you ever see the year for next post dates as "1970" or "1969" this is most likely such a conflict and you should try disabling all your other plugins temporarily first of all before contacting support.<br/><br/>
				
				<b>Unix Cron Job</b><br/>
				As an alternative you can set up an Unix Cron Job in your webhosts control panel (often cPanel) to create automatic posts. This will use less ressources on your server and be more reliable than the Wordpress solution. To set up a cron job you need to use the following URL:', 'wprobot').'<br /><strong>'.
				$cronurl.
				'</strong><br /><br /></div></div>';		
			} else {
				echo '<div class="updated"><p>'.__('Error: WP Robot could not be installed.', 'wprobot').'</p></div>';		
			}
		}
	}
}

function wpr_install_message() {

	if(isset($_POST['wpr_install_message'])) {
		if(empty($_POST['wpr_install_email']) || !strpos($_POST['wpr_install_email'], "@") && $_POST['wpr_install_email'] != "demo") {
			echo '<div class="updated"><p>'.__('Error: Please enter a valid email address or "demo".', 'wprobot').'</p></div>';	
		} else {
			wpr_install_function($_POST['wpr_install_email']);
		}
	} else {
		echo '<div class="wrap"><h2>WP Robot</h2><div class="updated"><h3>Installation</h3><p>'.__('Please enter your Paypal email you bought WP Robot with below and click "install" to finish the installation of WP Robot.', 'wprobot').'</p>
		<form method="post" id="wpr_install">	
		<strong>Paypal Email:</strong> <input id="wpr_install_email" class="regular-text" type="text" value="" name="wpr_install_email"/>
		<input class="button-primary" type="submit" name="wpr_install_message" value="'.__('Install', 'wprobot').'" />
		</form>	
		<br/><br/>
		'.__('- If you want to use the WP Robot Demo enter "<b>demo</b>"!', 'wprobot').'<br/>		
		'.__('- Otherwise enter the exact Paypal email you have used to purchase your copy of WP Robot.', 'wprobot').'<br/>		
		'.__('- Please note some Paypal accounts have several emails associated with them. If this is the case for you try all emails!', 'wprobot').'<br/>		
		'.__('- Do not tell other people your Paypal email in order to use it with WP Robot. Doing this purposefully can get your license suspended!', 'wprobot').'<br/>		
		<br/>
		</div>
		</div>';
	}
}

function wpr_cc($core) {
	global $wpr_loadedmodules,$wpr_modules;
	if($core == "basic") {_e('Your WP Robot includes the <b>Basic Core</b>. You can use the plugin on up to 20 of your own websites. <a href="http://wprobot.net/order/ordercustom.php">Go here to upgrade!</a>', 'wprobot');}
	elseif($core == "advanced") {_e('Your WP Robot includes the <b>Advanced Core</b>. You can use the plugin on up to 40 of your own websites. <a href="http://wprobot.net/order/ordercustom.php">Go here to upgrade!</a>', 'wprobot');}
	elseif($core == "elite") {_e('Your WP Robot includes the <b>Elite Core</b>. You can use the plugin on an unlimited number of your own websites. To sell websites that include WP Robot you have to upgrade to the <a href="http://wprobot.net/order/dev.php">developer license</a>, otherwise you risk that your license gets banned!', 'wprobot');}	
	elseif($core == "developer") {_e('You are using a <b>Developer Version</b> of WP Robot. This means you can sell this website and include WP Robot with the sale. If you do so please <a href="http://wprobot.net/robotpal/devregister.php">register a sublicense for your client</a> here and make sure to insert the clients email in the WP Robot Options BEFORE transfering the website to him!', 'wprobot');}	
	elseif($core == "devclient") {_e('You are using a <b>Developer (client) Version</b> of WP Robot you received with a website you purchased. You may use WP Robot on two additional websites besides the one you received the plugin with. <b>If you want to use WP Robot on an unlimited number of your own websites you can <a href="/order/devupgrade.php">upgrade here</a>!</b>', 'wprobot');}		

	echo "<br/><br/>";
	$lcount = count($wpr_loadedmodules);
	$mcount = count($wpr_modules);
	printf(__('You have %1$s modules of %2$s available modules installed. You can <a href="http://wprobot.net/order/ordercustom.php">get new modules</a> here.', 'wprobot'), $lcount, $mcount);
}

function wpr_toplevel() {
	global $wpdb, $wpr_table_campaigns, $wpr_table_templates, $_wp_using_ext_object_cache;

	$_wp_using_ext_object_cache = false;
	
	$options = get_option("wpr_options");	
	if(is_array($options) || empty($options)) {} else {$options = unserialize($options);}

	if ($options['wpr_installed'] != "yes") {wpr_install_message();} else {

		$rw_options = get_option("allrewriters_settings");	
		if(empty($rw_options)) {$rw_options = ar_activate($options);}	
		
		wpr_install_check_modules($options);
		
		wpr_campaign_controls();	

		include("display-campaigns.php");
		
	}
}

function wpr_add() {
	global $wpdb, $wpr_table_campaigns, $wpr_table_templates,$wpr_loadedmodules, $_wp_using_ext_object_cache;
	$_wp_using_ext_object_cache = false;
	
	$options = unserialize(get_option("wpr_options"));	
	$wpr_disabledmodules = $options["disabled_modules"];
	if(!is_array($wpr_disabledmodules)) {$wpr_disabledmodules = array();}
	
	if ($options['wpr_installed'] != "yes") {
		echo '<div class="wrap"><h2>WP Robot</h2><div class="updated"><h3>Installation</h3><p>'.__('Please go <a href="?page=wpr-campaigns">here</a> to finish the installation of WP Robot first.', 'wprobot').'</p></div></div>';
		return false;
	}
	
	if($options["wpr_core"] == "advanced" || $options["wpr_core"] == "basic") {
		if(!$_GET['edit']) {
			if($options["wpr_core"] == "basic") {$lmt = 3;}
			if($options["wpr_core"] == "advanced") {$lmt = 6;}
			$count = $wpdb->get_var("SELECT COUNT(*) FROM ".$wpr_table_campaigns);
			if($count >= $lmt) {
				echo '<div class="wrap"><h2>WP Robot</h2><div class="updated"><h3>Limit Reached</h3><p>'.__('Only '.$lmt.' Campaigns per site can be used with the '.$options["wpr_core"].' Core. You can upgrade <a href="http://wprobot.net/order/ordercustom.php">here</a>.', 'wprobot').'</p></div></div>';			
				return false;		
			}
		}
	}

	$skippedx = 0;
	$templates = $wpdb->get_results("SELECT * FROM " . $wpr_table_templates . " WHERE `type` = 'post'");	
	foreach($templates as $template) {
		$skipmod = 0;
		$modulen = strtolower(str_replace(" ", "", str_replace(" Default", "", $template->name)));
		if(!in_array($modulen, $wpr_disabledmodules)) {
		
			// amazon, articlebuilder, flickr, yahooanswers, commissionjunction, oodle, shopzilla, linkshare, eventful, yelp, avantlink
			if($modulen == "amazon" && empty($options['wpr_aa_apikey'])) {$skipmod = 1;}
			if($modulen == "articlebuilder" && empty($options['wpr_ab_email'])) {$skipmod = 1;}
			if($modulen == "flickr" && empty($options['wpr_fl_apikey'])) {$skipmod = 1;}
			if($modulen == "yahooanswers" && empty($options['wpr_yap_appkey'])) {$skipmod = 1;}
			if($modulen == "commissionjunction" && empty($options['wpr_commissionjunction_appkey'])) {$skipmod = 1;}
			if($modulen == "oodle" && empty($options['wpr_oodle_appkey'])) {$skipmod = 1;}
			if($modulen == "shopzilla" && empty($options['wpr_shopzilla_appkey'])) {$skipmod = 1;}
			if($modulen == "linkshare" && empty($options['wpr_linkshare_appkey'])) {$skipmod = 1;}
			if($modulen == "eventful" && empty($options['wpr_eventful_appkey'])) {$skipmod = 1;}
			if($modulen == "yelp" && empty($options['wpr_yelp_appkey'])) {$skipmod = 1;}
			if($modulen == "avantlink" && empty($options['wpr_avantlink_appkey'])) {$skipmod = 1;}
			
			if($skipmod == 0) {
				$presets[$template->name]["content"] = $template->content;
				$presets[$template->name]["title"] = $template->title;
				$presets[$template->name]["comments_amazon"] = $template->comments_amazon;
				$presets[$template->name]["comments_flickr"] = $template->comments_flickr;
				$presets[$template->name]["comments_yahoo"] = $template->comments_yahoo;
				$presets[$template->name]["comments_youtube"] = $template->comments_youtube;
			} else {
				$skippedx++;
			}
		}
	}		
	
	
    if($_POST['catbut']){
		if($_POST['multisingle'] == "multi") {$_POST['multisingle'] = "single";} else {$_POST['multisingle'] = "multi";$_POST['categories'] = "";}
	}
	
    if($_POST['importwpr2']) {
		$ma_dbtable = $wpdb->prefix . "wprobot";	
		$records = $wpdb->get_results("SELECT * FROM " . $ma_dbtable . " ORDER BY id ASC"); 
		if ($records) {
			$_POST["keywords"] = "";
			foreach ($records as $record) { 
				if(!empty($record->keyword)) { 
					$_POST["keywords"] .= '"'.$record->keyword .'"'. "\n";
				}
			}
		}
	}

    if($_POST['exact']) {
		$keywordsinput = str_replace("\r", "", $_POST['keywords']);
		$keywordsinput = explode("\n", $keywordsinput);    
		$_POST["keywords"] = "";
		for ($i = 0; $i < count($keywordsinput); $i++) {
			if($keywordsinput[$i] != "") {
				$_POST["keywords"] .= '"'.$keywordsinput[$i] .'"'. "\n";
			}
		}
	}
	
    if($_POST['evenchance']) {
		$chance = floor(100 / $_POST['tnum']);
		$tchance = 0;
		for ($i = 1; $i <= $_POST['tnum']; $i++) {
			$_POST["chance$i"] = $chance;
			$tchance = $tchance + $chance;
		}
		if($tchance != 100) {$_POST["chance1"] = $_POST["chance1"] + (100 - $tchance); }	
	}

    if($_POST['quickrand']) {
		$count = count($presets);
		$_POST['tnum'] = rand(2,$count);
		$rand_keys = array_rand($presets, $_POST['tnum']);
		$chance = floor(100 / $_POST['tnum']);
		$i = 1;
		$tchance = 0;
		foreach($rand_keys as $rand_key) {
			$_POST["chance$i"] = $chance;
			$tchance = $tchance + $chance;
			$_POST["title$i"] = $presets[$rand_key]["title"];
			$_POST["content$i"] = $presets[$rand_key]["content"];	
			$_POST["comments_amazon$i"] = $presets[$rand_key]["comments_amazon"];
			$_POST["comments_flickr$i"] = $presets[$rand_key]["comments_flickr"];
			$_POST["comments_yahoo$i"] = $presets[$rand_key]["comments_yahoo"];
			$_POST["comments_youtube$i"] = $presets[$rand_key]["comments_youtube"];	
			$i++;
		}
		if($tchance != 100) {$_POST["chance1"] = $_POST["chance1"] + (100 - $tchance); }
	}	
	
    if($_POST['quick']) {
		$count = count($presets);
		$_POST['tnum'] = $count;
		$chance = floor(100 / $count);
		$i = 1;
		$tchance = 0;
		foreach($presets as $prx => $preset) {
			$_POST["chance$i"] = $chance;
			$tchance = $tchance + $chance;			
			$_POST["title$i"] = $preset["title"];
			$_POST["content$i"] = $preset["content"];
			$_POST["name$i"] = $prx;	
			$_POST["comments_amazon$i"] = $preset["comments_amazon"];
			$_POST["comments_flickr$i"] = $preset["comments_flickr"];
			$_POST["comments_yahoo$i"] = $preset["comments_yahoo"];
			$_POST["comments_youtube$i"] = $preset["comments_youtube"];	
			$i++;
		}
		if($tchance != 100) {$_POST["chance1"] = $_POST["chance1"] + (100 - $tchance); }		
	}
	
	if($_POST['type1']) {
		$_POST['type'] = "keyword";
		if($options['wpr_simple']=='Yes') {
			$lmcount = 0;$tchance = 0;
			foreach($wpr_loadedmodules as $lmodule) { if($lmodule != "translation" && $lmodule != "rss") {$lmcount++;} }
			$lchance = floor(100 / $lmcount);
			foreach($wpr_loadedmodules as $lmodule) { if($lmodule != "translation" && $lmodule != "rss") {
				if(empty($firstmodule)) {$firstmodule = $lmodule;}
				$_POST[$lmodule."chance"] = $lchance;	
				$tchance = $tchance + $lchance;			
			} }
			if($tchance != 100) {$_POST[$firstmodule."chance"] = $_POST[$firstmodule."chance"] + (100 - $tchance); }
			$_POST["mixchance"] = 0;	
		}
	}
	if($_POST['type2']) {
		$_POST["tnum"] = 1;
		$_POST["chance1"] = 100;
		$_POST['type'] = "rss";
		$_POST["content1"] = "{rss}";
		$_POST["title1"] = "{rsstitle}";
		$_POST["name1"] = "RSS Default";
		$_POST["comments_amazon1"] = 0;
		$_POST["comments_flickr1"] = 0;
		$_POST["comments_yahoo1"] = 0;
		$_POST["comments_youtube1"] = 0;
	}
	if($_POST['type3']) {
		if(empty($_POST["tnum"])) {$_POST["tnum"] = 1;}
		$_POST['type'] = "nodes";
		if(isset($presets["Amazon Default"])) {
			$_POST["chance1"] = 100;
			$_POST["content1"] = $presets["Amazon Default"]["content"];
			$_POST["title1"] = $presets["Amazon Default"]["title"];
			$_POST["name1"] = "Amazon Default";
			$_POST["comments_amazon1"] = $presets["Amazon Default"]["comments_amazon"];
			$_POST["comments_flickr1"] = $presets["Amazon Default"]["comments_flickr"];
			$_POST["comments_yahoo1"] = $presets["Amazon Default"]["comments_yahoo"];
			$_POST["comments_youtube1"] = $presets["Amazon Default"]["comments_youtube"];	
		}
	}
	
	if(!$_POST['evenchance'] && !$_POST['wpr_cf_remove'] && !$_POST['wpr_cf_add'] && !$_POST['wpr_add_template'] && !$_POST['wpr_add'] && !$_POST['multisingle'] && !$_POST['exact'] && !$_POST['importwpr2'] && !$_POST['quick'] && !$_POST['quickrand'] && !$_POST['type1'] && !$_POST['type2'] && !$_POST['type3']) {
		if($_GET['edit'] || $_GET['ccopy']){
			// EDIT or COPY CAMPAIGN
			$id = $_GET['edit']; if(!$id) {$id = $_GET['ccopy'];}
			$campaign = $wpdb->get_row("SELECT * FROM " . $wpr_table_campaigns . " WHERE `id` = '$id'");
			$_POST["name"] = $campaign->name;
			
			$yahoocat = unserialize($campaign->yahoo_cat);
			if(!is_array($yahoocat)) {
				$yahoocat = array();
				$yahoocat["a"] = "";
				$yahoocat["rw"] = 0;
				$yahoocat["ps"] = $campaign->yahoo_cat;
				$yahoocat["pt"] = "";
			}
			
			$_POST["wpr_poststatus"] = $yahoocat["ps"];
			$_POST["wpr_rewriter"] = $yahoocat["rw"];
			$_POST["wpr_author"] = $yahoocat["a"];
			$_POST['wpr_postthumbs'] = $yahoocat["t"];
			$_POST['wpr_posttype'] = $yahoocat["pt"];
			
			$_POST["type"] = $campaign->ctype;
			if($_GET['ccopy']) {$_POST["name"] .= " Copy";}
			$_POST["interval"] = $campaign->cinterval;
			$_POST["period"] = $campaign->period;
			if($campaign->pause == 0) {$_POST["autopost"] = "yes";}
			$_POST['multisingle'] = "multi";
			$_POST["amazon_department"] = $campaign->amazon_department;
			$_POST["ebay_category"] = $campaign->ebay_cat;
		
			$exclude = unserialize($campaign->excludekws);
			for ($i = 0; $i < count($exclude); $i++) {		
				$_POST["exclude"] .= $exclude[$i] . "\n";
			}	
			$replace = unserialize($campaign->replacekws);
			for ($i = 0; $i < count($replace); $i++) {		
				$_POST["replace"] .= $replace[$i]["from"] ."|".$replace[$i]["to"]."|".$replace[$i]["chance"]."|".$replace[$i]["code"]."\n";
			}				
			$keywords = unserialize($campaign->keywords);
			$_POST["keywords"] = "";$_POST["feeds"] = "";$_POST["nodes"] = "";
			for ($i = 0; $i < count($keywords); $i++) {		
				$_POST["keywords"] .= $keywords[$i][0] . "\n";
				if($campaign->ctype == "rss") {
					$_POST["feeds"] .= $keywords[$i]["feed"] . "\n";
				} elseif($campaign->ctype == "nodes") {
					$_POST["nodes"] .= $keywords[$i]["node"] . "\n";
				}				
			}
			$categories = unserialize($campaign->categories);

			if(!empty($categories[0]["id"])) {$categories = wpr_transform_cats($categories);}
			
			if(count($categories) == 1) {
				$_POST['multisingle'] = "single";
				$_POST['categories'] = $categories[0][0]["id"];
			} else {
				for ($i = 0; $i < count($categories); $i++) {
					for ($o = 0; $o < count($categories[$i]); $o++) {
						$_POST["categories"] .= $categories[$i][$o]["name"];	
						if($o != count($categories[$i])-1) {$_POST["categories"] .= ",";	}
					}
					$_POST["categories"] .= "\n";
				}
			}
			
			$templates = unserialize($campaign->templates);
			if($options['wpr_simple']=='Yes' && $campaign->ctype == "keyword") {
				foreach($wpr_loadedmodules as $lmodule) { if($lmodule != "translation" && $lmodule != "rss") {$_POST[$lmodule."chance"] = 0;} }
				$_POST["mixchance"] = 0;
				for ($i = 1; $i <= count($templates); $i++) {	
					$lmodule = str_replace('{', '', $templates[$i]["title"]);
					$lmodule = str_replace('title}', '', $lmodule);
					$_POST[$lmodule."chance"] = $templates[$i]["chance"];
					if($lmodule == "") {$_POST["mixcontent"] = $templates[$i]["content"];}
				}
				if($_POST["chance"] > 0) {
					$_POST["mixchance"] = $_POST["chance"];
				}
			} else {
				$_POST['tnum'] = count($templates);
				for ($i = 1; $i <= count($templates); $i++) {	
					$_POST["chance$i"] = $templates[$i]["chance"];
					$_POST["title$i"] = $templates[$i]["title"];
					$_POST["content$i"] = $templates[$i]["content"];	
					$_POST["comments_amazon$i"] = $templates[$i]["comments"]["amazon"];
					$_POST["comments_flickr$i"] = $templates[$i]["comments"]["flickr"];
					$_POST["comments_yahoo$i"] = $templates[$i]["comments"]["yahooanswers"];
					$_POST["comments_youtube$i"] = $templates[$i]["comments"]["youtube"];				
				}
			}
			$customfields = unserialize($campaign->customfield);
			if(isset($customfields["name"])) {
			$_POST["cf_name1"] = $customfields["name"];
			$_POST["cf_value1"] = $customfields["value"];
			} else {
				$_POST['cfnum'] = count($customfields);	
				for ($i = 1; $i <= count($customfields); $i++) {	
					$_POST["cf_name$i"] = $customfields[$i]["name"];
					$_POST["cf_value$i"] = $customfields[$i]["value"];		
				}				
			}
			$translation = unserialize($campaign->translation);	
			$_POST["transchance"] = $translation["chance"];
			$_POST["trans1"] = $translation["from"];
			$_POST["trans2"] = $translation["to1"];		
			$_POST["trans3"] = $translation["to2"];	
			$_POST["trans4"] = $translation["to3"];	
			$_POST['trans_comments'] = $translation["comments"];
		} else {
		$lid = $wpdb->get_var("SELECT id FROM $wpr_table_campaigns ORDER BY id DESC;");if($lid =="") {$lid=0;}
		$lid++;	
		if(WPLANG == "de_DE") {$cnm = "Kampagne";} else {$cnm = "Campaign";}
		$_POST["name"] = "$cnm $lid";
		$_POST["interval"] = rand(12,96);
		$_POST["chance1"] = 100;
		if($options['wpr_simple']=='Yes') {
			$lmcount = 0;$tchance = 0;
			foreach($wpr_loadedmodules as $lmodule) { if($lmodule != "translation" && $lmodule != "rss") {$lmcount++;} }
			$lchance = floor(100 / $lmcount);
			foreach($wpr_loadedmodules as $lmodule) { if($lmodule != "translation" && $lmodule != "rss") {
				if(empty($firstmodule)) {$firstmodule = $lmodule;}
				$_POST[$lmodule."chance"] = $lchance;	
				$tchance = $tchance + $lchance;			
			} }
			if($tchance != 100) {$_POST[$firstmodule."chance"] = $_POST[$firstmodule."chance"] + (100 - $tchance); }
			$_POST["mixchance"] = 0;	
		} else {
			/*$rand = array_rand($presets); NO RAND TEMPLATE
			$_POST["content1"] = $presets[$rand]["content"];
			$_POST["title1"] = $presets[$rand]["title"];
			$_POST["comments_amazon1"] = $presets[$rand]["comments_amazon"];
			$_POST["comments_flickr1"] = $presets[$rand]["comments_flickr"];
			$_POST["comments_yahoo1"] = $presets[$rand]["comments_yahoo"];
			$_POST["comments_youtube1"] = $presets[$rand]["comments_youtube"];*/
		}
		$_POST['multisingle'] = "multi";
		$_POST['autopost'] = "yes";
		$_POST['createcats'] = "yes";
		$_POST['transchance'] = "0";
		$_POST['delaystart'] = "0";
		$_POST['type'] = "keyword";
		}
	}

    if($_POST['wpr_cf_add']){
		$_POST['cfnum'] = $_POST['cfnum'] + 1;
	}	
    if($_POST['wpr_cf_remove']){
		$_POST['cfnum'] = $_POST['cfnum'] - 1;
	}	

    if($_POST['wpr_add_template']){
		$_POST['tnum'] = $_POST['tnum'] + 1;
		$xi = $_POST['tnum'];			
		if($_POST['wpr_add_template_preset'] == "Random") {
			$rand = array_rand($presets);
			$_POST["content$xi"] = $presets[$rand]["content"];
			$_POST["title$xi"] = $presets[$rand]["title"];
			$_POST["name$xi"] = $rand;
			$_POST["comments_amazon$xi"] = $presets[$rand]["comments_amazon"];
			$_POST["comments_flickr$xi"] = $presets[$rand]["comments_flickr"];
			$_POST["comments_yahoo$xi"] = $presets[$rand]["comments_yahoo"];
			$_POST["comments_youtube$xi"] = $presets[$rand]["comments_youtube"];		
		} else {
			$_POST["content$xi"] = $presets[$_POST['wpr_add_template_preset']]["content"];
			$_POST["title$xi"] = $presets[$_POST['wpr_add_template_preset']]["title"];
			$_POST["name$xi"] = $_POST['wpr_add_template_preset'];
			$_POST["comments_amazon$xi"] = $presets[$_POST['wpr_add_template_preset']]["comments_amazon"];
			$_POST["comments_flickr$xi"] = $presets[$_POST['wpr_add_template_preset']]["comments_flickr"];
			$_POST["comments_yahoo$xi"] = $presets[$_POST['wpr_add_template_preset']]["comments_yahoo"];
			$_POST["comments_youtube$xi"] = $presets[$_POST['wpr_add_template_preset']]["comments_youtube"];				
		}
		
		$chance = floor(100 / $_POST['tnum']);
		$tchance = 0;
		for ($i = 1; $i <= $_POST['tnum']; $i++) {
			$_POST["chance$i"] = $chance;
			$tchance = $tchance + $chance;
		}
		if($tchance != 100) {$_POST["chance1"] = $_POST["chance1"] + (100 - $tchance); }			
    }	
	
	for ($i = 1; $i <= $_POST["tnum"]; $i++) {
		if($_POST["load$i"]){ // LOAD PRESET
			$_POST["content$i"] = $presets[$_POST["p$i"]]["content"];
			$_POST["title$i"] = $presets[$_POST["p$i"]]["title"];
			$_POST["name$i"] = $_POST["p$i"]["name"];
			$_POST["comments_amazon$i"] = $presets[$_POST["p$i"]]["comments_amazon"];
			$_POST["comments_flickr$i"] = $presets[$_POST["p$i"]]["comments_flickr"];
			$_POST["comments_yahoo$i"] = $presets[$_POST["p$i"]]["comments_yahoo"];
			$_POST["comments_youtube$i"] = $presets[$_POST["p$i"]]["comments_youtube"];			
		} elseif($_POST["delete$i"]) { // DELETE TEMPLATE
			$starter = $i;
			for ($x = $starter; $x <= $_POST["tnum"]; $x++) {
				$y = $x+1;
				if($y <= $_POST["tnum"]) {
					$_POST["content$x"] = $_POST["content$y"];
					$_POST["title$x"] = $_POST["title$y"];
					$_POST["name$x"] = $_POST["name$y"];
					$_POST["comments_amazon$x"] = $_POST["comments_amazon$y"];
					$_POST["comments_flickr$x"] = $_POST["comments_flickr$y"];
					$_POST["comments_yahoo$x"] = $_POST["comments_yahoo$y"];
					$_POST["comments_youtube$x"] = $_POST["comments_youtube$y"];
				} else {
					$_POST["content$x"] = "";
					$_POST["title$x"] = "";
					$_POST["name$x"] = "";
					$_POST["comments_amazon$x"] = "";
					$_POST["comments_flickr$x"] = "";
					$_POST["comments_yahoo$x"] = "";
					$_POST["comments_youtube$x"] = "";			
				}	
			}
			$_POST["tnum"] = $_POST["tnum"] - 1;
			
			$chance = floor(100 / $_POST['tnum']);
			$tchance = 0;
			for ($i = 1; $i <= $_POST['tnum']; $i++) {
				$_POST["chance$i"] = $chance;
				$tchance = $tchance + $chance;
			}
			if($tchance != 100) {$_POST["chance1"] = $_POST["chance1"] + (100 - $tchance); }						
		}
	}
	
	if($_POST['wpr_add']) {
		wpr_create_campaign();
	}
   
	include("add-campaigns.php");	
}
/*
function wpr_add_page_head() {
	?>
    <script type="text/javascript">	
	jQuery(document).ready(function($) {

		jQuery('a.deletetemplate').click(function(e) {
			var id = jQuery(this).attr('id').replace('del-','pt-');
			jQuery( "#" + id ).remove();
			return false;
		});				
	});
	</script>
	<?php
}

if(isset($_GET['page']) && $_GET['page'] == 'wpr-add' ) {
	add_action('admin_head', 'wpr_add_page_head');	
}	*/

// Header
function wpr_sub_templates_head() {
?>
    <script type="text/javascript">
	function addtxt(input,txt) {
		var obj = document.getElementById(input);
		obj.value += txt;
	}
	</script>

<?php		
}
	if(isset($_GET['page']) && $_GET['page'] == 'wpr-templates' ) {
		add_action('admin_head', 'wpr_sub_templates_head');	
	}	

function wpr_sub_templates() {
	global $wpdb, $wpr_table_campaigns, $wpr_table_templates,$wpr_loadedmodules, $_wp_using_ext_object_cache;
	$_wp_using_ext_object_cache = false;
	
	$options = unserialize(get_option("wpr_options"));	
	$wpr_disabledmodules = $options["disabled_modules"];
	
	if ($options['wpr_installed'] != "yes") {
		echo '<div class="wrap"><h2>WP Robot</h2><div class="updated"><h3>Installation</h3><p>'.__('Please go <a href="?page=wpr-campaigns">here</a> to finish the installation of WP Robot first.', 'wprobot').'</p></div></div>';
		return false;
	}	

	if($_GET['add'] && !$_POST['tdelete'] && !$_POST['tcopy'] && !$_POST['tsave']){
		$type = $_GET['add'];
		$sql = "INSERT INTO " . $wpr_table_templates . " SET type = '$type'";
		$results = $wpdb->query($sql);
		
		if ($results) {				
			echo '<div class="updated"><p>'.__('Empty  '.$type.' template has been added!', 'wprobot').'</p></div>';		
		} else {
			echo '<div class="updated"><p>'.__('Error: Template could not be added!', 'wprobot').'</p></div>';				
		}		
	}

	if($_POST['tmodsave']){
		for ($i = 0; $i <= $_POST['modnum']; $i++) {
			$content = $_POST[$i."c"];
			$id = $_POST[$i."id"];
			$sql = "UPDATE " . $wpr_table_templates . " SET `content` = '".$content."' WHERE `id` = '".$id."'";
			$results = $wpdb->query($sql);
		}
		echo '<div class="updated"><p>'.__('Module Templates have been updated!', 'wprobot').'</p></div>';			
	}
	
	$tids = explode(",",$_POST["tids"]);
	 foreach ($tids as $tid) { 
		$i = $tid;
		if($_POST["tsave$i"]) {$id = $i;} else {$tsave = false;}
		if($_POST["tdelete$i"]) {$id = $i;} else {$tdelete = false;}
		if($_POST["tcopy$i"]) {$id = $i;} else {$tcopy = false;}
	}	
	
	if($_POST['tsaveall']){
		foreach ($tids as $tid) { 
			$id = $tid;
			$content = $_POST["tcontent$id"];
			$title = $_POST["ttitle$id"];
			$name = $_POST["tname$id"];
			$comments_amazon = $_POST["comments_amazon$id"];
			$comments_yahoo = $_POST["comments_yahoo$id"];
			$comments_flickr = $_POST["comments_flickr$id"];
			$comments_youtube = $_POST["comments_youtube$id"];
			$sql = "UPDATE " . $wpr_table_templates . " SET `content` = '".$content."',`title` = '".$title."',`name` = '".$name."',`comments_amazon` = '".$comments_amazon."',`comments_flickr` = '".$comments_flickr."',`comments_yahoo` = '".$comments_yahoo."',`comments_youtube` = '".$comments_youtube."' WHERE `id` = '".$id."'";

			$results = $wpdb->query($sql);			
		}			
			echo '<div class="updated"><p>'.__('All Templates have been updated!', 'wprobot').'</p></div>';			
	}
	
	if($_POST["tsave$id"]){
		$content = $_POST["tcontent$id"];
		$title = $_POST["ttitle$id"];
		$name = $_POST["tname$id"];
		$comments_amazon = $_POST["comments_amazon$id"];
		$comments_yahoo = $_POST["comments_yahoo$id"];
		$comments_flickr = $_POST["comments_flickr$id"];
		$comments_youtube = $_POST["comments_youtube$id"];
		$sql = "UPDATE " . $wpr_table_templates . " SET `content` = '".$content."',`title` = '".$title."',`name` = '".$name."',`comments_amazon` = '".$comments_amazon."',`comments_flickr` = '".$comments_flickr."',`comments_yahoo` = '".$comments_yahoo."',`comments_youtube` = '".$comments_youtube."' WHERE `id` = '".$id."'";

		$results = $wpdb->query($sql);	

		if ($results) {	
			echo '<div class="updated"><p>'.__('Template has been updated!', 'wprobot').'</p></div>';		
		} else {
			echo '<div class="updated"><p>'.__('Error: Template could not be updated!', 'wprobot').'</p></div>';				
		}			
	}
	
	if($_POST["tdelete$id"]){
		$sql = "DELETE FROM " . $wpr_table_templates . " WHERE `id` = '".$id."'";
		$results = $wpdb->query($sql);	

		if ($results) {				
			echo '<div class="updated"><p>'.__('Template has been deleted!', 'wprobot').'</p></div>';		
		} else {
			echo '<div class="updated"><p>'.__('Error: Template could not be deleted!', 'wprobot').'</p></div>';				
		}			
	}	
	
	if($_POST["tcopy$id"]){
		$templ = $wpdb->get_row("SELECT * FROM " . $wpr_table_templates . " WHERE `id` = '".$id."'");
		$content = $templ->content;
		$type = $templ->type;
		$title = $templ->title;
		$name = $templ->name;
		$comments_amazon = $templ->comments_amazon;
		$comments_yahoo = $templ->comments_yahoo;
		$comments_flickr = $templ->comments_flickr;
		$comments_youtube = $templ->comments_youtube;
		$sql = "INSERT INTO " . $wpr_table_templates . " SET type = '$type', content = '$content',`title` = '".$title."',`name` = '".$name."',`comments_amazon` = '".$comments_amazon."',`comments_flickr` = '".$comments_flickr."',`comments_yahoo` = '".$comments_yahoo."',`comments_youtube` = '".$comments_youtube."'";
		$results = $wpdb->query($sql);	

		if ($results) {				
			echo '<div class="updated"><p>'.__('Template has been copied!', 'wprobot').'</p></div>';		
		} else {
			echo '<div class="updated"><p>'.__('Error: Template could not be copied!', 'wprobot').'</p></div>';				
		}			
	}	
	
	if($_GET["which"] == "post") {$where = "WHERE type = 'post'";$order = "id DESC";} else {$where = "WHERE type != 'post'";$order = "type ASC";}
	$records = $wpdb->get_results("SELECT * FROM " . $wpr_table_templates . " $where ORDER BY $order"); 	
	
	$tids = array();
	foreach ($records as $record) {$tids[] = $record->id;}	
	
	include("display-templates.php");
}

function wpr_sub_options() {
	global $wpdb, $wpr_table_campaigns, $wpr_table_templates, $wpr_table_errors, $wpr_table_posts, $wpr_modules, $_wp_using_ext_object_cache, $wpr_loadedmodules;
	$_wp_using_ext_object_cache = false;

	$options = unserialize(get_option("wpr_options"));	
	$wpr_disabledmodules = $options["disabled_modules"];
	if(!is_array($wpr_disabledmodules)) {$wpr_disabledmodules = array();}
	
	$rw_options = get_option("allrewriters_settings");	
	if(empty($rw_options)) {$rw_options = ar_activate($options);}
	
	if($_POST['wpr_disable_save']) {
		$wpr_disabledmodules = array();
		foreach($wpr_loadedmodules as $lmodule) {
			if($_POST['disable_'.$lmodule] == 1) {
			} else {
				$wpr_disabledmodules[] = $lmodule;
			}
		}
		$options["disabled_modules"] = $wpr_disabledmodules;
		update_option("wpr_options", serialize($options));	
		echo '<div class="updated"><p>'.__('Your module selection has been saved.', 'wprobot').'</p></div>';		
	}
	
	if($_POST['wpr_uninstall']) {
		$results = $wpdb->query("DROP TABLE $wpr_table_posts,$wpr_table_templates,$wpr_table_campaigns,$wpr_table_errors;");
		delete_option("wpr_options");
		delete_option("wpr_cron");	
		delete_option("wpr_cloak");	
		delete_option('wpr_db_ver');
		ar_deactivate();
		$options = "";
		echo '<div class="updated"><p>'.__('WP Robot has been uninstalled. You can now disable and delete the plugin.<br/><br/><strong>If you intend to reinstall WP Robot please first disable and reenable the plugin on your blogs "Plugins" page - otherwise the installation will not work!</strong>', 'wprobot').'</p></div>';		
	}		
	
	if ($options['wpr_installed'] != "yes") {
		echo '<div class="wrap"><h2>WP Robot</h2><div class="updated"><h3>Installation</h3><p>'.__('Please go <a href="?page=wpr-campaigns">here</a> to finish the installation of WP Robot first.', 'wprobot').'</p></div></div>';
		return false;
	}		
	
	if($_POST['wpr_options_default']) {
		$options = wpr_default_options(1);
		echo '<div class="updated"><p>'.__('Options have been reset.', 'wprobot').'</p></div>';			
	}		
	
	if($_POST['wpr_templates_default']) {
		$results = $wpdb->query("TRUNCATE TABLE $wpr_table_templates;");
		$sql = unserialize(get_option("wpr_sql"));
		if(empty($sql)) {$sql = $options["wpr_sql"];}
		$results2 = $wpdb->query($sql);
		if($results2) {echo '<div class="updated"><p>'.__('Templates have been reset.', 'wprobot').'</p></div>';}	
		else {echo '<div class="updated"><p>'.__('Templates could not be reset.', 'wprobot').'</p></div>';}
	}			
	
	if($_POST['wpr_import']) {
		$options = wpr_import_options();			
		echo '<div class="updated"><p>'.__('Settings have been imported.', 'wprobot').'</p></div>';		
	}		

	if($_POST['wpr_clear_log']) {
		$results = $wpdb->query("TRUNCATE TABLE $wpr_table_errors;");
		echo '<div class="updated"><p>'.__('Log has been cleared.', 'wprobot').'</p></div>';		
	}		

	if($_POST['wpr_clear_posts']) {
		$results = $wpdb->query("TRUNCATE TABLE $wpr_table_posts;");			
		echo '<div class="updated"><p>'.__('History has been cleared.', 'wprobot').'</p></div>';		
	}
	
	if($_POST['wpr_update_email']) {
		$options['wpr_email'] = wpr_update_email($options['wpr_email'],$_POST['wpr_email']);
	}
	
	if($_POST['wpr_update_core']) {	
		wpr_update_email($options['wpr_email'],$options['wpr_email'],1);		
	}
	
	if($_POST['wpr_options_save']) {
	
		if($_POST['wpr_cloak'] == "Yes" && get_option('wpr_cloak') != "Yes") {
		echo '<div class="updated"><p>'.__('<b>Warning:</b> Link cloaking has been enabled but <a href="http://wprobot.net/blog/how-to-set-up-wp-robot-link-cloaking/">additional steps</a> are required to finish the setup. If you do not follow these steps affiliate links on your blog will not work!', 'wprobot').'</p></div>';			
		}
	
		$options['wpr_resetcount'] = $_POST['wpr_resetcount'];
		$options['wpr_autotag'] = $_POST['wpr_autotag'];
		$options['wpr_badwords'] = $_POST['wpr_badwords'];
		$options['wpr_randomize'] = $_POST['wpr_randomize'];
		$options['wpr_randomize_comments'] = $_POST['wpr_randomize_comments'];
		$options['wpr_help'] = $_POST['wpr_help'];
		$options['wpr_poststatus'] = $_POST['wpr_poststatus'];
		$options['wpr_cb_affkey'] = $_POST['wpr_cb_affkey'];		
		$options['wpr_cb_filter'] = $_POST['wpr_cb_filter'];	
		$options['wpr_openlinks'] = $_POST['wpr_openlinks'];
		$options['wpr_authorid'] = $_POST['wpr_authorid'];	
		$options['wpr_err_retries'] = $_POST['wpr_err_retries'];
		$options['wpr_err_maxerr'] = $_POST['wpr_err_maxerr'];
		$options['wpr_err_minmod'] = $_POST['wpr_err_minmod'];	
		$options['wpr_err_disable'] = $_POST['wpr_err_disable'];
		$options["wpr_global_exclude"] = $_POST['wpr_global_exclude'];
		$options['wpr_check_unique_old'] = $_POST['wpr_check_unique_old'];
		$options['wpr_simple'] = $_POST['wpr_simple'];
		$options['wpr_save_images'] = $_POST['wpr_save_images'];	
		$options['wpr_replace_keyword'] = $_POST['wpr_replace_keyword'];	
		//$options['wpr_rewrite_active'] = $_POST['wpr_rewrite_active'];	
		/*$options['wpr_rewrite_active_tbs'] = $_POST['wpr_rewrite_active_tbs'];
		$options['wpr_rewrite_active_sc'] = $_POST['wpr_rewrite_active_sc'];
		$options['wpr_rewrite_active_schimp'] = $_POST['wpr_rewrite_active_schimp'];
		$options['wpr_rewrite_active_ucg'] = $_POST['wpr_rewrite_active_ucg'];	
		$options['wpr_rewrite_active_sr'] = $_POST['wpr_rewrite_active_sr'];	
		$options['wpr_rewrite_active_wai'] = $_POST['wpr_rewrite_active_wai'];			
		$options['wpr_rewrite_email'] = $_POST['wpr_rewrite_email'];
		$options['wpr_rewrite_key'] = $_POST['wpr_rewrite_key'];
		$options['wpr_rewrite_level'] = $_POST['wpr_rewrite_level'];
		$options['wpr_tbs_rewrite_email'] = $_POST['wpr_tbs_rewrite_email'];
		$options['wpr_tbs_rewrite_pw'] = $_POST['wpr_tbs_rewrite_pw'];	
		$options['wpr_tbs_spintxt'] = $_POST['wpr_tbs_spintxt'];	
		$options['wpr_tbs_quality'] = $_POST['wpr_tbs_quality'];
		$options['wpr_rewrite_protected'] = $_POST['wpr_rewrite_protected'];
		$options['wpr_sc_rewrite_email'] = $_POST['wpr_sc_rewrite_email'];
		$options['wpr_sc_rewrite_pw'] = $_POST['wpr_sc_rewrite_pw'];
		$options['wpr_sc_quality'] = $_POST['wpr_sc_quality'];
		$options['wpr_sc_port'] = $_POST['wpr_sc_port'];
		$options['wpr_sc_thesaurus'] = $_POST['wpr_sc_thesaurus'];
		$options['wpr_schimp_rewrite_email'] = $_POST['wpr_schimp_rewrite_email'];
		$options['wpr_schimp_rewrite_pw'] = $_POST['wpr_schimp_rewrite_pw'];
		$options['wpr_schimp_quality'] = $_POST['wpr_schimp_quality'];	
		$options['wpr_sr_rewrite_email'] = $_POST['wpr_sr_rewrite_email'];	
		$options['wpr_sr_rewrite_pw'] = $_POST['wpr_sr_rewrite_pw'];	
		$options['wpr_sr_quality'] = $_POST['wpr_sr_quality'];	
		$options['wpr_wai_rewrite_email'] = $_POST['wpr_wai_rewrite_email'];	
		$options['wpr_wai_rewrite_pw'] = $_POST['wpr_wai_rewrite_pw'];	
		$options['wpr_wai_quality'] = $_POST['wpr_wai_quality'];
		$options['wpr_wai_sentence'] = $_POST['wpr_wai_sentence'];
		$options['wpr_wai_paragraph'] = $_POST['wpr_wai_paragraph'];
		$options['wpr_wai_nooriginal'] = $_POST['wpr_wai_nooriginal'];*/
		$options['wpr_tbs_rewrite_title'] = $_POST['wpr_tbs_rewrite_title'];		
		$options['wpr_rewrite_use'] = $_POST['wpr_rewrite_use'];
		$options['wpr_trans_use_proxies'] = $_POST['wpr_trans_use_proxies'];	
		$options['wpr_trans_proxies'] = $_POST['wpr_trans_proxies'];	
		foreach($wpr_modules as $module) {
			$function = "wpr_".$module."_options_default";
			if(function_exists($function)) {
				if(is_array($wpr_disabledmodules) && in_array($module, $wpr_disabledmodules)) {
				} else {
					$moptions = $function();
					foreach($moptions as $moption => $default) {
						$options[$moption] = $_POST[$moption];
					}
				}
			}
		}
		update_option("wpr_options", serialize($options));	
		update_option('wpr_cloak',$_POST['wpr_cloak']);
		echo '<div class="updated"><p>'.__('Options have updated.', 'wprobot').'</p></div>';				
	}	
	
	include("display-options.php");	
}

function wpr_insertpost($content,$title,$cat=array(),$status,$author="",$thumbnail="",$parent="",$posttype="") {
	remove_filter('the_content', 'make_clickable');
	remove_filter('content_save_pre', 'wp_filter_post_kses');

	if($content == "" || $title == "") {return false;}
	
	$options = unserialize(get_option("wpr_options"));	
	$content = str_replace("$", "$ ", $content);
	
	if($_POST['postdate']) {
		$post_date= $_POST['postdate'];
		$post_date_gmt= $post_date;			
	} else {
		$post_date= current_time('mysql');
		$post_date_gmt= current_time('mysql', 1);
	}

	if(!empty($author)) {
		if(is_numeric($author)) {
			$post_author = $author;
		} else {
			$user = get_user_by('login',$author);
			if($user) {
				$post_author = $user->ID;
			} else {
				$random_password = wp_generate_password( $length=12, $include_standard_special_chars=false );
				$post_author = wp_create_user( $author, $random_password, $author . "@asdf.com" );
			}			
		}
	} else {
		if ($options['wpr_authorid']!='') {
			$authors = explode(";",$options['wpr_authorid']);		
			if(count($authors) == 0) {$post_author=1;} elseif(count($authors) > 0) {

				$rnd = array_rand($authors);
				if(is_numeric($authors[$rnd])) {
					$post_author = $authors[$rnd];
				} else {
					$user = get_user_by('login',$authors[$rnd]);
					if($user) {
						$post_author = $user->ID;
					} else {
						$random_password = wp_generate_password( $length=12, $include_standard_special_chars=false );
						$post_author = wp_create_user( $authors[$rnd], $random_password, $authors[$rnd] . "@asdf.com" );
					}			
				}				
			}
		}
	}
	if(empty($post_author)) {
		$post_author = 1;
	}
	
	if($status=='published') {
		$post_status = 'publish';
	} elseif ($status=='draft') {
		$post_status = 'draft';
	} else {
		if ($options['wpr_poststatus']=='published') {
			$post_status = 'publish';
		} elseif ($options['wpr_poststatus']=='draft') {
			$post_status = 'draft';
		}
	}
	
	if(empty($cat)) {$post_category = array(1);} else {$post_category = $cat;}
	
	if ($options['wpr_openlinks']=='yes') {$content = str_replace("<a ", '<a target="_blank" ', $content);}
	$post_content=$content;

	$badwords = explode(";",$options['wpr_badwords']);
	$badchars = array(",", ":", "(", ")", "]", "[", "?", "!", ";", "-", '"');
	
	$title2 = str_replace($badchars, "", $title);		
	
	$items = explode(' ', $title2);
	$tags_input = array();				
	for($k = 0, $l = count($items); $k < $l; ++$k){		
		$long = strlen($items[$k]);
		if ($long > 3) {
			if (!in_array(strtolower($items[$k]), $badwords)) {
				if(!empty($items[$k])) {
					$tags_input[] = $items[$k];
				}
			}
		}
	}		
	if($options['wpr_autotag'] != 'Yes') {$tags_input = "";}
	
	$post_title = trim($title);
	
	$tax_input = "";//array( 'ad_cat' => array( 6764 ) );
	
	if(!empty($posttype)){
		$post_type = $posttype;
	} else {
		$post_type = "post";	
	}
	
	if(!empty($thumbnail)) {
		$post_status = 'inherit';
		$post_parent = $parent;
		$post_type = 'attachment';
		$post_mime_type = 'image/jpeg';
		$uploads = wp_upload_dir();
		$guid = $upload_dir['url']."/".$thumbnail;
		$thumbnail = str_replace(".jpg", "", $thumbnail);
		$post_title = trim($thumbnail);		

		$post_data = compact('post_content','post_title','post_date','post_date_gmt','post_author','post_category', 'post_status', 'tags_input','post_parent','post_type','post_mime_type','guid','tax_input');		
	} else {
		$post_data = compact('post_content','post_title','post_date','post_date_gmt','post_author','post_category', 'post_status', 'tags_input','post_type','tax_input');		
	}
	
	$post_data = add_magic_quotes($post_data);
	$post_ID = wp_insert_post($post_data);
	return $post_ID;		
}

function wpr_insertcomments($postid,$commentsarray,$time="",$transcomments=0,$translation="") {
	remove_filter('comment_text', 'make_clickable', 9);
	$options = unserialize(get_option("wpr_options"));	
	
	if($_POST['postdate']) {
		$comment_date = $_POST['postdate'];	
	} else {
		$comment_date = current_time('mysql');
	}	
	
    foreach ($commentsarray as $comments) {

		if($options['wpr_randomize_comments'] == 'Yes') {
			$ccount = count($comments);
			$chalf = ceil($ccount / 2);
			$cnum = rand($chalf,$ccount);
		} else {
			$cnum = count($comments);
		}

		$i = 0;
		foreach ($comments as $comment) {
			if($i < $cnum) {
				$comment_post_ID=$postid;
				
				if(function_exists("preg_split")) {
					list( $today_year, $today_month, $today_day, $hour, $minute, $second ) = preg_split( '([^0-9])', $comment_date );
				} else {
					list( $today_year, $today_month, $today_day, $hour, $minute, $second ) = split( '([^0-9])', $comment_date );	
				}	
				$comment_date = mktime($hour, $minute + rand(0, 59), $second + rand(0, 59), $today_month, $today_day, $today_year);
				$comment_date=date("Y-m-d H:i:s", $comment_date); 		
				$comment_date_gmt = $comment_date;					

				$rnd= rand(1,9999);
				$comment_author_email="someone$rnd@domain.com";
				$comment_author=$comment["author"];
				$comment_author_url='';  
				$comment_content="";
				$comment_content.=$comment["content"];
				if($transcomments==1) {$comment_content = wpr_translate($comment_content,$translation["from"],$translation["to1"],$translation["to2"],$translation["to3"]);}				
				$comment_type='';
				$user_ID='';
				$comment_approved = 1;
				$commentdata = compact('comment_post_ID', 'comment_date', 'comment_date_gmt', 'comment_author', 'comment_author_email', 'comment_author_url', 'comment_content', 'comment_type', 'user_ID', 'comment_approved');
				$comment_id = wp_insert_comment( $commentdata );
			}
			$i++;
		}
	}
}

function wpr_random_tags($content) {

	preg_match_all('#\[select(.*)\]#smiU', $content, $matches, PREG_SET_ORDER);
	if ($matches) {
		foreach($matches as $match) {
			$match[1] = substr($match[1], 1);
			$paras = explode("|",$match[1]);
			$randp = array_rand($paras);
			
			$content = str_replace($match[0], $paras[$randp], $content);			
		}
	}	
	//preg_match_all('#\[random(.*)](.*)[\/random]\]#smiU', $content, $matches, PREG_SET_ORDER);
	preg_match_all('#\[random(.*)\](.*)\[/random\]#smiU', $content, $matches, PREG_SET_ORDER);
	if ($matches) {
		foreach($matches as $match) {
			$match[1] = substr($match[1], 1);
			if($match[1] >= rand(1,100)) {
				//$match[2] = str_replace("[/rando", "", $match[2]);	
				$content = str_replace($match[0], $match[2], $content);	
			} else {
				$content = str_replace($match[0], "", $content);				
			}
		}
	}
	
	return $content;
}

function wpr_run_cron($camp_id="",$mincamp="",$maxcamp="",$minpost="",$maxpost="",$randtime=10,$chance=100) {
	global $wpdb, $wpr_table_campaigns;

	if(empty($chance) || $chance == "" || $chance == " ") {$chance = 100;}
	if($chance >= rand(1,100)) {
	
		$rtime = rand(0,$randtime);
		$comment_date = time() - $rtime * 60;
		$_POST['postdate']=date("Y-m-d H:i:s", $comment_date);		
		$csql = "";
		
		if(isset($camp_id)) {	
			if(empty($minpost) || empty($maxpost)) {$randpost = 1;} else {$randpost = rand($minpost,$maxpost);}
			for ($i = 1; $i <= $randpost; $i++) {
				wpr_poster($camp_id, "", 1);
			}
		} else {
			if(!empty($mincamp) && !empty($maxcamp)) {$campnumber = rand($mincamp,$maxcamp);$csql = " ORDER BY RAND() LIMIT $campnumber";}
			
			$results = $wpdb->get_results("SELECT id FROM " . $wpr_table_campaigns . "$csql"); 
			foreach($results as $result) {
				if(empty($minpost) || empty($maxpost)) {$minpost = 1;$randpost = 1;} else {$randpost = rand($minpost,$maxpost);}
				for ($i = $minpost; $i <= $randpost; $i++) {
					$rtime = rand(0,$randtime);
					$comment_date = time() - $rtime * 60;
					$_POST['postdate']=date("Y-m-d H:i:s", $comment_date);					
					wpr_poster($result->id, "", 1);
				}			
			}
		}
	}
}

function wpr_poster($camp_id, $keyword="", $manual=0) {

	$options = unserialize(get_option("wpr_options"));			
	$maxretry = $options['wpr_err_retries'];
	$retry = 0;
	while($posted != true) {
		$posted = wpr_post($camp_id, $keyword, $retry, $manual);
		$retry++;
		
		if($retry > $maxretry) {return false;}
	}
	if($posted) {
		return true;
	}
}

function wpr_post($camp_id, $keyword="", $retry=1, $manual=0) {
	global $wpdb, $wpr_table_campaigns, $wpr_table_posts, $wpr_table_errors;

	if($retry > 0) {$retrymsg = " <b>(".__("Retry","wprobot")." $retry)</b>";} else {$retrymsg = "";}	
	$time = current_time('mysql');	
	$errors = array();
	
    $options = unserialize(get_option("wpr_options"));	
	$result = $wpdb->get_row("SELECT * FROM " . $wpr_table_campaigns . " WHERE id = '$camp_id'"); 
	if($result->pause == 1 && $manual == 0) {return false;}

	// select KEYWORD
	$keywords = unserialize($result->keywords);
	if(!$keyword) {
		if(empty($keywords)) {
			// SAVE and DISPLAY error
			$esql = "INSERT INTO ".$wpr_table_errors." ( campaign, keyword, module, reason, message, time ) VALUES";
			$esql .= " ( '$camp_id', '$keyword', '', 'Inactive', ".__("'Post skipped because no keywords were found in the campaign.$retrymsg'", 'wprobot').", '$time' );";
			$results = $wpdb->query($esql);			
			return false;		
		}			
		// REMOVE KEYWORDS WHERE SKIPPED = 5 (better way without loop?)
		$keywords2 = $keywords;
		foreach($keywords as $key => $keyword) {
			if($keyword["skipped"] >= $options['wpr_err_disable'] && !$keyword["feed"]) {
				unset($keywords2[$key]);
			}
		}
		//echo "<pre>";print_r($keywords2);echo "</pre>";	
		//$keywords = array_values($keywords);		
		$rnd = array_rand($keywords2);
		$keyword = $keywords[$rnd][0];
		if($rnd === "" || $keyword === "" && !empty($keyword["feed"]) || !is_numeric($rnd)) {
			// SAVE and DISPLAY error
			$esql = "INSERT INTO ".$wpr_table_errors." ( campaign, keyword, module, reason, message, time ) VALUES";
			$esql .= " ( '$camp_id', '$keyword', '', 'Inactive', ".__("'No active keywords or feeds for this campaign, possibly because they were disabled due to repeated errors. Check the status and reenable them on the campaign detail page.$retrymsg'", 'wprobot').", '$time' );";
			$results = $wpdb->query($esql);			
			return false;		
		}		
	} else {
		foreach($keywords as $key => $kw) {
			if($kw[0] == $keyword) {
				$rnd = $key;
			}
		}
	}
	$keywordsbackup = $keywords[$rnd];

	// select CATEGORY
	$categories = unserialize($result->categories);
	if(!empty($categories[0]["id"])) {$categories = wpr_transform_cats($categories);}
	$catarray = array();
	if(count($categories) == 1) {
		foreach($categories[0] as $cats) {$catarray[] = $cats["id"];}		
		//$category = $categories[0]["id"];
	} else {
		foreach($categories[$rnd] as $cats) {$catarray[] = $cats["id"];}
		//$category = $categories[$rnd]["id"];	
	}	
	
	// select TEMPLATE
	$templates = unserialize($result->templates);
	$i = 1;
	foreach($templates as $tchance) {
		$tch[$i] = $templates[$i]["chance"];
		$i++;
	}
	
	$random = rand(1,100);
	foreach($tch as $name => $chance){
		$luck += $chance;
		if($random <= $luck && empty($templatenum)){
			$templatenum = $name;
		}
	} 	
	$template = $templates[$templatenum]["content"];
	
	$templateexcerpt = substr(strip_tags($template), 0, 130);
	$templateusedmsg = '<a target="_blank" class="tooltip" href="#">?<span>'.__('The template used for this post was: <strong>Post Template ',"wprobot").$templatenum.__('</strong>, starting with:<br/>',"wprobot").$templateexcerpt.__('<br/><br/><strong>If there was an error please check the associated module messages directly below this one!</strong>',"wprobot") .'</span></a>';

	// Wordpress action hook
	do_action('wpr_before_post', $insert); 	
	
	$content = $template;//echo $template."<br>";
	$noqkeyword = str_replace('"', '', $keyword);
	$content = str_replace("{keyword}", $noqkeyword, $content);
	$content = str_replace("{Keyword}", ucwords($noqkeyword), $content);	
	$content = str_replace("{title}", $title, $content);	
	$catreplace = get_category_link( $catarray[0] );
	if (!is_wp_error($catreplace)) {
	$content = str_replace("{catlink}", $catreplace, $content);	
	} else {$content = str_replace("{catlink}", "", $content);	}
	
	// AMAZON
	preg_match_all('#\{amazonlist(.*)\}#iU', $content, $matches, PREG_SET_ORDER);
	if ($matches) {
		foreach($matches as $match) {
			$match[1] = substr($match[1], 1);
			if($match[1]) {$amanum = $match[1];} else {$amanum = 1;}
			$amalist = wpr_amazon_getlist($keywords[$rnd][0],$amanum);
			if(isset($amalist["error"]) && is_array($amalist)) {$errors[] = $amalist["error"];$content = str_replace($match[0], "", $content);}
			$content = str_replace($match[0], $amalist, $content);				
		}
	}		
	// THUMBNAIL
	preg_match_all('#\{thumbnail(.*)\}#iU', $content, $matches, PREG_SET_ORDER);
	if ($matches) {
		foreach($matches as $match) {
			$match[1] = substr($match[1], 1);
			if($match[1]) {$tkw = $match[1];} else {$tkw = $keywords[$rnd][0];}
			$thumbnail = wpr_flickr_getthumbnail($tkw);
			if(!empty($thumbnail["error"])) {$errors[] = $thumbnail["error"];}
			$content = str_replace($match[0], $thumbnail[0]["content"], $content);				
		}
	}	
	// RANDOM Tags
	$content = wpr_random_tags($content);
	
	$title = $templates[$templatenum]["title"];
	$noqkeyword = str_replace('"', '', $keyword);
	$title = str_replace("{keyword}", $noqkeyword, $title);
	$title = str_replace("{Keyword}", ucwords($noqkeyword), $title);
	if(!empty($keywords[$rnd]["alternative"])) {
		$ll = 1;
		foreach($keywords[$rnd]["alternative"] as $alternative) {
			$content = str_replace("{keyword$ll}", $alternative, $content);	
			$title = str_replace("{keyword$ll}", $alternative, $title);
			$ll++;
		}
	}
	
	// RANDOM Tags
	$title = wpr_random_tags($title);

	$raz[0] = "{";$raz[1] = "}";
	preg_match_all("/\\".$raz[0]."[^\\".$raz[1]."]+\\".$raz[1]."/s", $content, $matches);
	$counts = array_count_values  (  $matches[0]  ); //echo $counts["{amazon}"];	

	$wpr_modulesnum = count($matches[0]);
	if($wpr_modulesnum <= 0) {
		// SAVE and DISPLAY error
		$esql = "INSERT INTO ".$wpr_table_errors." ( campaign, keyword, module, reason, message, time ) VALUES";
		$esql .= " ( '$camp_id', '$keyword', '', 'Inactive', ".__("'Post skipped because no modules were found in the template.$retrymsg'", 'wprobot').", '$time' );";
		$results = $wpdb->query($esql);			
		return false;		
	}		

	$usql = "INSERT INTO ".$wpr_table_posts." ( campaign, keyword, module, unique_id, time ) VALUES";
	// Get Content for each Module
	// ["content"] = Content of the item (replaced module template code)
	// ["title"] = Title of the item (i.e. Product title)
	// ["unique"] = Unique ID (i.e. ASIN)
	// ["comments"] = Possible comment content of the item (i.e. reviews)
	$contents = array();
	$commentsarray = array();
	$titleerror = 0;
	$duplicatecount = 0;
	$errorcount = 0;
	$replacecount = 0;
	
	foreach($counts as $module => $count) {

		$modulname = str_replace(array("{","}"), "", $module);
		$function = "wpr_".$modulname."post";
		
		$optional = "";$comments = "";
		switch ($modulname) {
			case "amazon":
				$optional = array($result->amazon_department,$keywords[$rnd]["node"]);
				break;
			case "ebay":
				$optional = $result->ebay_cat;
				break;
			case "flickr":			
				$comments = $templates[$templatenum]["comments"]["$modulname"];
				break;
			case "youtube":
				$comments = $templates[$templatenum]["comments"]["$modulname"];
				break;
			case "yahooanswers":
				$optional = $result->yahoo_cat;
				$comments = $templates[$templatenum]["comments"]["$modulname"];
				break;	
			case "rss":
				$optional = $keywords[$rnd]["feed"];
				break;							
		}
		
		// GET CONTENT
		if(function_exists($function)) {
			$contents["$modulname"] = $function($keyword, $count, $keywords[$rnd][1]["$modulname"], $optional, $comments);
			if(!empty($contents["$modulname"]["error"])) {
				$errors[] = $contents["$modulname"]["error"];
				$errorcount = $errorcount + 1 * $counts["{".$modulname."}"];
			}
		} else {
			$title = str_replace("{".$modulname."title}", "", $title);
			$errors[] = array("module" => ucwords($modulname), "reason" => "Not Installed", "message" => __("$modulname is not installed on this weblog and has been skipped.", 'wprobot'));						
			$errorcount = $errorcount + 1;
		}
		
		//print_r($contents);
		
		// KEYWORD REPLACE
		if($replacecount == 0 && !empty($contents["$modulname"][0]["title"]) && $options['wpr_replace_keyword'] == 1) {
			$keyword = preg_replace("/[^A-Za-z0-9 ]/", '', $contents["$modulname"][0]["title"]);
			$replacecount = 1;
		}		

		// CONTENT REPLACE
		$$modulname = 0;
		$dupl = 0;
	
		foreach ($matches[0] as $element) {
			if($element=="{".$modulname."}") {
				$unique = $contents["$modulname"][$$modulname]["unique"];
				if($unique != "" || $modulname == "ebay" ) {	
					$dcheck = wpr_check_unique($unique);
					//echo "CHECK".$dcheck;
					if($options['wpr_check_unique_old'] == "Yes") {$dcheck2 = wpr_check_unique_old($contents["$modulname"][$$modulname]["title"]);} else {$dcheck2 = false;}
					if($dcheck == false && $dcheck2 == false) {
						$content = preg_replace('/\{'.$modulname.'\}/', $contents["$modulname"][$$modulname]["content"], $content, 1);
						if(!isset($contents["$modulname"]["error"]) ) {
							$unique = $wpdb->escape($unique);
							$ekeyword = $wpdb->escape($keyword);
							$usql .= " ( '$camp_id', '$ekeyword', '$modulname', '$unique', '$time' ),";
						}
						

							if($templates[$templatenum]["comments"]["$modulname"] == 1) {$commentsarray[] = $contents["$modulname"][$$modulname]["comments"];}

							if(!empty($contents["$modulname"][$$modulname]["comments"]) && $modulname == "articlebuilder") {$commentsarray[] = $contents["$modulname"][$$modulname]["comments"];}

					} else {
						// DUPLICATE POST
						$dupl = 1;
						$duplicatecount++;
						$content = preg_replace('/\{'.$modulname.'\}/', "", $content, 1);	
						$errors[] = array("module" => ucwords($modulname), "reason" => "Duplicate", "message" => __("Skipping ", 'wprobot').ucwords($modulname).__(" module because the content has already been posted.", 'wprobot'));						
						$errorcount = $errorcount + 1;
					}
				}
				
				//echo "------------DEBUG START------------<br/>";
				//echo $dupl." DUPLICATE<br/>";
				//echo $contents["$modulname"]["error"]." ERROR<br/>";
				//echo $counts["{".$modulname."}"]." FIRST COUNT<br/>";
				//echo $$modulname." SECOND COUNT<br/>";
				// TITLE REPLACE	
				$titlecheck = strpos($title, "{".$modulname."title}");
				$titlecheck2 = strpos($title, "{title}");				
				$modulecheckvar = $$modulname + 1;
				if ($titlecheck !== false || $titlecheck2 !== false) {
					if ($counts["{".$modulname."}"] > $modulecheckvar && $dupl == 1 || $counts["{".$modulname."}"] > $modulecheckvar && !empty($contents["$modulname"]["error"])) {
						// Dont Replace Title because there is another title module item
					} elseif($counts["{".$modulname."}"] <= $modulecheckvar && $dupl == 1) {
						// Title Duplicate					
						$titleduplicate = 1;		
					} elseif($counts["{".$modulname."}"] <= $modulecheckvar && !empty($contents["$modulname"]["error"]) || $counts["{".$modulname."}"] <= $modulecheckvar && empty($contents["$modulname"][0]["title"])) {
						// Title Error
						$titleerror = 1;
					} else {
						// Replace Title
						$title = str_replace("{".$modulname."title}", $contents["$modulname"][$$modulname]["title"], $title);
						$title = str_replace("{title}", $contents["$modulname"][$$modulname]["title"], $title);	
					}
				}
				$$modulname++;				
			}		
		}	

		if($contents["$modulname"]["error"]["reason"] == "IncNum") {$incnum = 1;} else {$incnum = 0;}
		// INCREASE NUMS
		if(empty($contents["$modulname"]["error"]) || $contents["$modulname"]["error"]["reason"] == "IncNum") { // don't increase NUMs if ERROR for module
			$keywords[$rnd][1]["$modulname"] = $keywords[$rnd][1]["$modulname"] + $$modulname;

			// RESET POST COUNT
			if($keywords[$rnd][1]["$modulname"] > $options['wpr_resetcount'] && $options['wpr_resetcount'] != "no") {$keywords[$rnd][1]["$modulname"] = 0;}
			if($modulename == "ebay" && $keywords[$rnd][1]["$modulname"] > 50) {$keywords[$rnd][1]["$modulname"] = 0;}		
		}	
		$content = str_replace("{".$modulname."}","",$content);
	}

	// check ERRORS
	//$errorsnum = count($errors);	
	$errorsnum = $errorcount;	
	$skip = 0;
	if(!empty($keywords[$rnd]["feed"])) {$errkeyword = "Feed";} elseif(!empty($keywords[$rnd]["node"])) {$errkeyword = "Node:".$keywords[$rnd]["node"];} else {$errkeyword = $wpdb->escape($keyword);}
	
	// EXCLUDE KEYWORDS Check $options["wpr_global_exclude"]
	$excludeskip = 0;
	$excludes = unserialize($result->excludekws);
	
	$globals = str_replace("\r", "", $options["wpr_global_exclude"]);
	$globals = explode("\n", $globals);
	$excludes = array_merge($excludes, $globals);
	
	if(!empty($excludes)) {
		foreach($excludes as $exclude) {
			if($exclude != "" && $exclude != " " ) {
				$excheck = stripos($content, $exclude);
				$texcheck = stripos($title, $exclude);
				if($excheck === false && $texcheck === false) {
				} else {
					$excludeskip = 1;	$errors = array();
					$errors[] = array("module" => "", "reason" => "Exclude", "message" => __("Skipping post because exclude keyword $exclude was found.$retrymsg $templateusedmsg", 'wprobot'), "time" => "$time");
					break;
				}
			}
		}
	}
	
	if($excludeskip == 0) {
		// MARK KEYWORD AS yellow, orange, etc
		$wpr_modulessuccess = $wpr_modulesnum - $errorsnum;
		$duplsuccess = $wpr_modulesnum - $duplicatecount;
		if($duplicatecount > $options["wpr_err_maxerr"] || $duplsuccess < $options["wpr_err_minmod"] || $titleduplicate == 1) { // DUPLICATE ERROR - WiTHOUT skipped INCREASE
			$errors[] = array("module" => "", "reason" => "Duplicate Content", "message" => __("Skipping post to prevent duplicate content.", 'wprobot')."$retrymsg $templateusedmsg", "time" => "$time");	
			$skip = 1;	
		} elseif($titleerror == 1) { 			
			if($incnum != 1) {$keywords[$rnd] = $keywordsbackup;}
			$keywords[$rnd]["skipped"] = $keywords[$rnd]["skipped"] + 1;
			$errors[] = array("module" => "", "reason" => "Post skipped", "message" => __("Skipping post because the main module (title module) returned an error.", 'wprobot')."$retrymsg $templateusedmsg", "time" => "$time");	
			$skip = 1;	
		} elseif($errorsnum > $options["wpr_err_maxerr"] || $wpr_modulessuccess < $options["wpr_err_minmod"]) {
			if($incnum != 1) {$keywords[$rnd] = $keywordsbackup;}		
			$keywords[$rnd]["skipped"] = $keywords[$rnd]["skipped"] + 1;
			$errors[] = array("module" => "", "reason" => "Post skipped", "message" => __("Skipping post because too many module errors were encountered.", 'wprobot')."$retrymsg $templateusedmsg", "time" => "$time");	
			$skip = 1;
		} else {
			$keywords[$rnd]["skipped"] = 0;	
		}	
	}		
		
	// SAVE ERRORS
	if($errorsnum > 0 || $excludeskip == 1 || $titleerror == 1) {
		$esql = "INSERT INTO ".$wpr_table_errors." ( campaign, keyword, module, reason, message, time ) VALUES";
		foreach($errors as $error) {
			$error["message"] = $wpdb->escape($error["message"]);
			$esql .= " ( '$camp_id', '$errkeyword', '".$error["module"]."', '".$error["reason"]."', '".$error["message"]."', '$time' ),";
		}
		$esql = substr_replace($esql ,";",-1);
		$results = $wpdb->query($esql);		
	}		
	
	// SKIP if EXCLUDE found, errors > MAXERRORS or modules < MINMODULES
	if($excludeskip == 1 || $skip == 1) {
		// - update campaign entry with new NUMS	

		$keywords = serialize($keywords);
		$keywords = $wpdb->escape($keywords);
		$sql = "UPDATE " . $wpr_table_campaigns . " SET `keywords` = '".$keywords."' WHERE `id` = '".$camp_id."'";
		$results = $wpdb->query($sql);		
		return false;
	}
	
    // SAVE IMAGES
	/*if($options['wpr_save_images'] == "Yes") {

		$allimages = wpr_findimages($content);
		$imageurls = $allimages[2];

		if(sizeof($imageurls)) {
			foreach($imageurls as $oldurl) {
				if(strpos($oldurl, plugin_basename( dirname(__FILE__) )) === false) {
					$newurl = wpr_saveimage($oldurl,$keyword);
					if($newurl) {$content = str_replace($oldurl, $newurl, $content);}	
				}
			} 
		}	
    }	*/
	
	// TRANSLATION
	if(function_exists("wpr_translate_partial")) {
		$title = wpr_translate_partial($title);
	}	
	$translation = unserialize($result->translation);
	if($translation["chance"] >= rand(1,100)) {
		if($translation["from"] != "no" && $translation["to1"] != "no") {
			$translationcontent = wpr_translate($content,$translation["from"],$translation["to1"],$translation["to2"],$translation["to3"]);
			//print_r($translationcontent);
			if($options["wpr_trans_titles"] == "yes") {
				$translationtitle = wpr_translate($title,$translation["from"],$translation["to1"],$translation["to2"],$translation["to3"]);
			}
			if(!empty($translationcontent["error"]["reason"])) {
				$esql = "INSERT INTO ".$wpr_table_errors." ( campaign, keyword, module, reason, message, time ) VALUES";
				$esql .= " ( '$camp_id', '$errkeyword', 'Translation', '".$translationcontent["error"]["reason"]."', '".$translationcontent["error"]["message"]."', '$time' );";
				$results = $wpdb->query($esql);		
				if($options['wpr_trans_fail'] == "skip") {
					$esql = "INSERT INTO ".$wpr_table_errors." ( campaign, keyword, module, reason, message, time ) VALUES";
					$esql .= " ( '$camp_id', '$errkeyword', '', 'Post skipped', '".__("Skipping post because translation failed ","wprobot")."$retrymsg $templateusedmsg', '$time' );";
					$results = $wpdb->query($esql);		
					return false;
				}
			} elseif(empty($translationcontent)) {
				$esql = "INSERT INTO ".$wpr_table_errors." ( campaign, keyword, module, reason, message, time ) VALUES";
				$esql .= " ( '$camp_id', '$errkeyword', 'Translation', 'Translation Failed', '".__("The post could not be translated.","wprobot")."', '$time' );";
				$results = $wpdb->query($esql);		
				if($options['wpr_trans_fail'] == "skip") {
					$esql = "INSERT INTO ".$wpr_table_errors." ( campaign, keyword, module, reason, message, time ) VALUES";
					$esql .= " ( '$camp_id', '$errkeyword', '', 'Post skipped', '".__("Skipping post because translation failed ","wprobot")."$retrymsg $templateusedmsg', '$time' );";
					$results = $wpdb->query($esql);		
					return false;
				}
			} else {
				$content = $translationcontent;
				if($options["wpr_trans_titles"] == "yes") {$title = $translationtitle;}
				if($translation["comments"] == 1) {$transcomments = 1;} else {$transcomments = 0;}
			}
		}
	}

	$yahoocat = unserialize($result->yahoo_cat);	
	if(!is_array($yahoocat)) {
		$yahoocat = array();
		$yahoocat["rw"] = 0;
		$yahoocat["ps"] = $result->yahoo_cat;
		$yahoocat["a"] = "";
	}

	// REWRITE
	if($yahoocat["rw"] == 1) {
	
		$rewrite_use = $options['wpr_rewrite_use'];
		$rewrite_titles = $options['wpr_tbs_rewrite_title'];
		
		$rw_options = get_option('allrewriters_settings');
		
		if(empty($rw_options)) {
			$esql = "INSERT INTO ".$wpr_table_errors." ( campaign, keyword, module, reason, message, time ) VALUES";
			$esql .= " ( '$camp_id', '$errkeyword', 'Rewriter', 'Rewriter', 'Rewriter settings are empty! Please go to the All Rewriters page to set them up.', '$time' );";
			$results = $wpdb->query($esql);			
		} else {
	
			if($rewrite_use == "chain") {
			
				if(!empty($rw_options["general"]["options"]["chain"][1]) && !empty($rw_options["general"]["options"]["chain"][2])) {
					foreach($rw_options["general"]["options"]["chain"] as $ch => $chfunc) {
						if(!empty($chfunc)) {
							$result = $chfunc($content);
							if(is_array($result) && !empty($result["error"])) {
								$esql = "INSERT INTO ".$wpr_table_errors." ( campaign, keyword, module, reason, message, time ) VALUES";
								$esql .= " ( '$camp_id', '$errkeyword', 'Chain Rewrite', 'Chain Rewrite', '".$result["error"]."', '$time' );";
								$results = $wpdb->query($esql);		
							} else {
								$content = $result;
							}									
						}
					}			
				} else {
					$esql = "INSERT INTO ".$wpr_table_errors." ( campaign, keyword, module, reason, message, time ) VALUES";
					$esql .= " ( '$camp_id', '$errkeyword', 'Chain Rewrite', 'Chain Rewrite', 'Please set up your rewriting chain on the All Rewriters settings page under <i>Settings > All Rewriters</i> first', '$time' );";
					$results = $wpdb->query($esql);					
				}
			
			} elseif($rewrite_use == "rand" || empty($rewrite_use)) { // get random active rewriter
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
					$esql = "INSERT INTO ".$wpr_table_errors." ( campaign, keyword, module, reason, message, time ) VALUES";
					$esql .= " ( '$camp_id', '$errkeyword', '".$rw_options[$sel_rw]["name"]."', '".$rw_options[$sel_rw]["name"]."', '".$result["error"]."', '$time' );";
					$results = $wpdb->query($esql);		
				} else {
					$content = $result;
				}
				
	//echo "RW: $sel_rw <br><br>RW FUNC: $rw_func <br><br> RESULT: $result";			

				if($rewrite_titles == "Yes") {
				
					$rewritetitle = $rw_func($title);
					if(is_array($rewritetitle) && !empty($rewritetitle["error"])) {
					} else {
						$title = $rewritetitle;
					}			
				}	

			} elseif($rewrite_use != "chain") {
				$esql = "INSERT INTO ".$wpr_table_errors." ( campaign, keyword, module, reason, message, time ) VALUES";
				$esql .= " ( '$camp_id', '$errkeyword', 'Rewriter', 'Rewriter', 'Rewriter Function not found! Please install our free All Rewriter plugin to use rewriting with WP Robot!', '$time' );";
				$results = $wpdb->query($esql);				
			}
		}
	}
	
	if(function_exists("wpr_rewrite_partial")) {
		$content = wpr_rewrite_partial($content,$options);
	}		
	
	// REPLACES
	$replaces = unserialize($result->replacekws);
	if(!empty($replaces)) {	
		foreach($replaces as $replace) {
			if($replace["chance"] >= rand(1,100)) {
				$replace["from"] = trim($replace["from"]);
				$replace["to"] = trim($replace["to"]);
				if($replace["code"] == "1") {
					$content = str_replace($replace["from"], $replace["to"], $content);
					$title = str_replace($replace["from"], $replace["to"], $title);					
				} else {
					$content = str_replace(" ".$replace["from"], " ".$replace["to"], $content);
					$title = str_replace(" ".$replace["from"], " ".$replace["to"], $title);				
				}
			}
		}
	}
	
	// Wordpress action hook
	do_action('wpr_before_post_save', $content,$title,$catarray); 	
	
	// INSERT POST into db	
	$insert = wpr_insertpost($content,$title,$catarray,$yahoocat["ps"],$yahoocat["a"],"","",$yahoocat["pt"]);
	if (is_wp_error($insert)) {
		$errormessage = $insert->get_error_message();
		// SAVE and DISPLAY error
		$esql = "INSERT INTO ".$wpr_table_errors." ( campaign, keyword, module, reason, message, time ) VALUES";
		$esql .= " ( '$camp_id', '$errkeyword', '', 'Insert failed', ".__("'Wordpress Error: ", 'wprobot')."$errormessage $retrymsg $templateusedmsg', '$time' );";
		$results = $wpdb->query($esql);			
		return false;		
	} elseif(isset($insert)) {
	
		// - update campaign entry with new NUMS	
		$posts_created = $result->posts_created + 1;
		$keywords[$rnd][1]["total"] = $keywords[$rnd][1]["total"] + 1;	
		$keywords = serialize($keywords);
		$keywords = $wpdb->escape($keywords);
		$sql = "UPDATE " . $wpr_table_campaigns . " SET `keywords` = '".$keywords."', `posts_created` = '".$posts_created."' WHERE `id` = '".$camp_id."'";
		$results = $wpdb->query($sql);		
	
		// - SAVE IMAGES
		if($options['wpr_save_images'] == "Yes") {
			$resultimgs = wpr_save_all_images($content,$keyword,$insert);
			
			$attachments = get_posts(array('numberposts' => '1', 'post_parent' => $insert, 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => 'ASC'));

			if(sizeof($attachments) > 0){
				set_post_thumbnail($insert, $attachments[0]->ID);	
			}		
		}			

		// - save UNIQUE IDS to post table
		$usql = substr_replace($usql ,";",-1);
		$results = $wpdb->query($usql);	

		// - insert COMMENTS 
		wpr_insertcomments($insert,$commentsarray,"",$transcomments,$translation);
		
		// - insert CUSTOM FIELDS
		//if($counts["{amazon}"] > 0) {
		//	add_post_meta($insert, 'ma_amazonpost', $contents["amazon"][0]["unique"]);
		//}
			//add_post_meta($insert, 'wpr_keyword', $keyword);
				
		$customfield = unserialize($result->customfield);

		if(isset($customfield["name"])) {
			$customfields = array();
			$customfields[1]["name"] = $customfield["name"];
			$customfields[1]["value"] = $customfield["value"];
			$customfield = $customfields;
		}	
	
		if($yahoocat["t"] == 1 || !empty($customfield)) {
			if(!empty($contents["amazon"][0]["customfield"]["amazonthumbnail"]))
			{$ximage = $contents["amazon"][0]["customfield"]["amazonthumbnail"];
			} elseif(!empty($contents["commissionjunction"][0]["customfield"]["cjthumbnail"]))
			{$ximage = $contents["commissionjunction"][0]["customfield"]["cjthumbnail"];
			} elseif(!empty($contents["rss"][0]["customfield"]["rssimage"]))
			{$ximage = $contents["rss"][0]["customfield"]["rssimage"];
			} elseif(!empty($contents["flickr"][0]["customfield"]["flickrimage"]))
			{$ximage = $contents["flickr"][0]["customfield"]["flickrimage"];
			} elseif(!empty($thumbnail[0]["customfield"]["thumbnail"]))
			{$ximage = $thumbnail[0]["customfield"]["thumbnail"];
			} elseif(!empty($contents["youtube"][0]["customfield"]["youtubethumbnail"]))
			{$ximage = $contents["youtube"][0]["customfield"]["youtubethumbnail"];
			} elseif(!empty($contents["oodle"][0]["customfield"]["oodlethumbnail"]))
			{$ximage = $contents["oodle"][0]["customfield"]["oodlethumbnail"];
			} elseif(!empty($contents["shopzilla"][0]["customfield"]["shopzillathumbnail"]))
			{$ximage = $contents["shopzilla"][0]["customfield"]["shopzillathumbnail"];
			} elseif(!empty($contents["avantlink"][0]["customfield"]["avantlinkthumbnail"]))
			{$ximage = $contents["avantlink"][0]["customfield"]["avantlinkthumbnail"];
			} elseif(!empty($contents["pressrelease"][0]["customfield"]["prthumbnail"]))
			{$ximage = $contents["pressrelease"][0]["customfield"]["prthumbnail"];			
			} else {$ximage = "";}		
		
			if($options['wpr_save_images'] == "Yes" && !empty($ximage)) {
				if(strpos($ximage, plugin_basename( dirname(__FILE__) )) === false) {
					$newurl = wpr_saveimage($ximage,$keyword);
					if($newurl) {$ximage = $newurl;}					
				}
			}	

			$blogurl = get_bloginfo( "url" );
			$yimage = str_replace($blogurl."/","",$newurl);	
			//echo $ximage."<br>";	
		}
		
		for ($i = 1; $i <= count($customfield); $i++) {
			$cfname = $wpdb->escape($customfield[$i]["name"]);
			$cfcontent = $customfield[$i]["value"];
			if($cfcontent != "" && $cfname != "") {

				$cfcontent = wpr_random_tags($cfcontent);
				$cfcontent = str_replace("{image}", $ximage, $cfcontent);
				$cfcontent = str_replace("{image-relative}", $yimage, $cfcontent);
				$cfcontent = str_replace("{keyword}", $keyword, $cfcontent);	

				// NEW CUSTOM FIELD REPLACE
				foreach($contents as $content) {
					if(!empty($content[0]["customfield"])) {
						foreach($content[0]["customfield"] as $key => $value) {
							$cfcontent = str_replace("{".$key."}", $value, $cfcontent);
						}
					}
				}
				
				$cfcontent = preg_replace('#\{(.*)\}#smiU', '', $cfcontent);
				//$cfcontent = $wpdb->escape($cfcontent);
				if(!empty($cfcontent)) {add_post_meta($insert, $cfname, $cfcontent);}
			}
		}

		// Post Thumbnails
		/*if($yahoocat["t"] == 1 && !empty($ximage)) {
			$upload_dir = wp_upload_dir();
			$zimage = str_replace($upload_dir['baseurl']. "/","",$ximage);	
			//echo $zimage." ZIMAGE<br>";
			$post_thumbnail = wpr_insertpost($content,$title,$catarray,$yahoocat["ps"],$yahoocat["a"],$zimage,$insert);
			if(isset($post_thumbnail)) {
				add_post_meta($post_thumbnail, "_wp_attached_file", $zimage);

				list($width, $height) = getimagesize($ximage);
				$wpameta = array(
					"file" => $zimage, 
					"width" => $width, 
					"height" => $height,
					"sizes" => array(
						"thumbnail" => array(
						  'file' => $zimage,
						  'width' => $width,
						  'height' => $height,
						  'mime-type' => 'image/jpeg'					
						),
						"post-thumbnail" => array(
						  'file' => $zimage,
						  'width' => $width,
						  'height' => $height,
						  'mime-type' => 'image/jpeg'												
						)
					)
				);
				//print_r($wpameta);
				add_post_meta($post_thumbnail, "_wp_attachment_metadata", $wpameta);
				
				add_post_meta($insert, "_thumbnail_id", $post_thumbnail);
			}
		}*/

		// Wordpress action hook
		do_action('wpr_after_post', $insert); 
		
		// - display success message IN OTHER FUNCTION
		$esql = "INSERT INTO ".$wpr_table_errors." ( campaign, keyword, module, reason, message, time ) VALUES";
		$esql .= " ( '$camp_id', '$errkeyword', '', 'Post created', ".__("'The post has been created successfully.", 'wprobot')."$retrymsg $templateusedmsg'".", '$time' );";
		$results = $wpdb->query($esql);		
		return true;
		
	} else {
		// SAVE and DISPLAY error
		$esql = "INSERT INTO ".$wpr_table_errors." ( campaign, keyword, module, reason, message, time ) VALUES";
		$esql .= " ( '$camp_id', '$errkeyword', '', 'Insert failed', ".__("'The post could not be inserted into the Wordpress database.", 'wprobot')."$retrymsg $templateusedmsg', '$time' );";
		$results = $wpdb->query($esql);			
		return false;
	}
}

function wpr_checkpost() {
	$options = unserialize(get_option("wpr_options"));	
	if ($options["createposts"] == "yes") {
		echo '<p>Powered by <a href="http://wprobot.net/" title="Wordpress autoblogging plugin">WP Robot</a></p>';
	}
}
add_action('wp_footer', 'wpr_checkpost'); 

// LINK CLOAKING
class wpr_linkcloaker {	
	var $linkno=0;
	var $myfile='';
	var $myfolder='';
	 
	function wpr_linkcloaker($linkno=0){
		$this->linkno = $linkno;
		$this->myfile = str_replace('\\', '/',__FILE__);
		$this->myfile = preg_replace('/^.*wp-content[\\\\\/]plugins[\\\\\/]/', '', $this->myfile);
		add_filter('the_content', array(&$this,'contentcloaker'), 11); // was 15 
		add_filter('mod_rewrite_rules', array(&$this,'rewriterules'));
	}
	 
	function ma_esc($text){
		$text=strip_tags($text);
		$text=preg_replace('/[^a-z0-9_]+/i', '_', $text);	
		if(strlen($text)<1) { $text='link'; };	
		return $text;
	}

	function rewriter($matches){
		global $post;

		$this->linkno++;	
		$url = $matches[3];	
		$parts = @parse_url($url);
	
		if(!$parts || !isset($parts['scheme']) || !isset($parts['host'])) return $matches[0];
	  
		if( preg_match('/www.amazon/i', $matches[3]) || preg_match('/clickbank/i', $matches[3]) || preg_match('/rover.ebay/i', $matches[3]) || preg_match('/jdoqocy/i', $matches[3])) {
			$base = get_option( 'home' );
			if ( $base == '' ) {
				$base = get_option( 'siteurl' );
			}
			
			$url = trailingslashit($base)."go".'/'.$this->ma_esc($matches[5]).
				 '/'.($post->ID)."/".$this->linkno;
				 
			//$url = trailingslashit($base)."go".'/to'.
			//	 '/'.($post->ID)."/".$this->linkno;				 
			
			$link = $matches[1].$matches[2].$url.$matches[2].$matches[4].$matches[5].$matches[6]; 

			return $link;	
		} else {return $matches[0];}
	}

	function contentcloaker($content){
		if(is_page()){
			return $content;
		} else {
		$this->linkno=0;
		$wplc_url_pattern='/(<a[\s]+[^>]*href\s*=\s*)([\"\'])([^\2>]+?)\2([^<>]*>)((?sU).*)(<\/a>)/i';
		
		$content=preg_replace_callback($wplc_url_pattern, array(&$this,'rewriter'), $content);
		return $content;
		}
	}
	 
	function rewriterules($rules){
		global $wp_rewrite;

		$myfolder = basename(dirname(__FILE__));
		
		$redirector = WP_PLUGIN_URL . '/' . $myfolder . '/cloak.php';	

		$pattern = '^' . "go".'/([^/]*)/([0-9]+)/([0-9]+)/?$';
		$replacement=$redirector.'?post_id=$2&link_num=$3&cloaked_url=$0';
		
		$pattern_static = '^' . "go".'/([^/]+)[/]?$';
		$replacement_static=$redirector.'?name=$1&cloaked_url=$0';
		
		$cloakrules="\n# WP Robot Link Cloaking START\n";
		$cloakrules.="<IfModule mod_rewrite.c>\n";
		$cloakrules.="RewriteEngine On\n";
		$cloakrules.="RewriteRule $pattern $replacement [L]\n";
		$cloakrules.="RewriteRule $pattern_static $replacement_static [L]\n";
		$cloakrules.="</IfModule>\n";
		$cloakrules.="# WP Robot Link Cloaking END\n\n";
		
		$rules=$cloakrules.$rules;
		
		return $rules;
	}

}
 
if(get_option('wpr_cloak') == 'Yes') {
	$wpr_linkcloaker = new wpr_linkcloaker();
}
add_action( "wprobothook", 'wpr_poster' );

// WP ROBOT SHORTCODE          // {wprobot module=ebay keyword=wordpress start=1}
function wpr_shortcode( $data , $postarr="" ) {
	$content = $data['post_content'];
	
	if(is_array($content)) {$content = "";}
	
	preg_match_all('#\{wprobot module=(.*) keyword=(.*) start=(.*) num=(.*)\}#iU', $content, $matches, PREG_SET_ORDER);//print_r($matches);	
	if (empty($matches)) {preg_match_all('#\[wprobot module=(.*) keyword=(.*) start=(.*) num=(.*)\]#iU', $content, $matches, PREG_SET_ORDER);}//print_r($matches);		
	if ($matches) {
		foreach($matches as $match) {
			$module = str_replace('\"','',$match[1]);
			$keyword = str_replace('\"','',$match[2]);
			$start = str_replace('\"','',$match[3]);	
			$num = str_replace('\"','',$match[4]);
			$module = str_replace('"','',$module);
			$keyword = str_replace('"','',$keyword);
			$start = str_replace('"','',$start);
			$num = str_replace('"','',$num);
			if(empty($num)) {$num = 1;}
			if(empty($start)) {$start = 1;}
			if($num == "r") {$num = rand(1,20);}
			
			$function = "wpr_".$module."post";
			if(function_exists($function)) {
				$gets = $function($keyword, $num, $start, "", "");
				if($num == 1) {$get = $gets[0]["content"];} else {
					$get = "";
					foreach($gets as $geti) {$get .= $geti["content"];}
				}		
			} else {$get = "ERROR: Function does not exist.";}

			$content = str_replace($match[0], $get, $content);			
		}
	}	
	
	$data['post_content'] = $content;
	return ( $data );
	
	
}
add_filter ( 'wp_insert_post_data' , 'wpr_shortcode' , 99 );

class wpr_post_shortcode_button {
	var $pluginname = "wprobot";

	function wpr_post_shortcode_button()  {
		// Modify the version when tinyMCE plugins are changed.
		add_filter('tiny_mce_version', array (&$this, 'change_tinymce_version') );
		
		// init process for button control
		add_action('init', array (&$this, 'add_buttons') );
	}

	function add_buttons() {
		// Don't bother doing this stuff if the current user lacks permissions
		if ( !current_user_can('edit_posts') && !current_user_can('edit_pages') ) return;
		
		// Add only in Rich Editor mode
		if ( get_user_option('rich_editing') == 'true') {
			// add the button for wp2.5 in a new way
			add_filter("mce_external_plugins", array (&$this, "add_tinymce_plugin" ), 5);
			add_filter('mce_buttons', array (&$this, 'register_button' ), 5);
		}
	}
	
	// used to insert button in wordpress 2.5x editor
	function register_button($buttons) {
		array_push($buttons, "separator", $this->pluginname );
		return $buttons;
	}
	
	// Load the TinyMCE plugin : editor_plugin.js (wp2.5)
	function add_tinymce_plugin($plugin_array) {    
		$plugin_array[$this->pluginname] =  WPR_URLPATH.'tinymce/editor_plugin.js';
		return $plugin_array;
	}
	
	function change_tinymce_version($version) {
		return ++$version;
	}
}

$tinymce_button = new wpr_post_shortcode_button();	

function wpr_init_filesystem($args) { 
	global $wp_filesystem;
	
	if (!$wp_filesystem || !is_object($wp_filesystem))
	{
		WP_Filesystem($args);
	}

	if (!is_object($wp_filesystem)) 
		return FALSE;
	
	return TRUE;
}

?>