<?PHP

/**
 * AutoBlogged admin page
 *
 * PHP version 5
 *
 * @package   AutoBlogged v2
 * @author    AutoBlogged <support@autoblogged.com>
 * @copyright Copyright (c)2011 AutoBlogged, ALL RIGHTS RESERVED
 * @version   SVN Build: $Id$
 */

/**
 * Admin page header
 *
 * @return void
 */
function ab_adminPageHeader() {
   global $wpdb;
   global $feedtypes;
   global $excerptsources;
   global $excerpttypes;
   global $excerpthtmlfilters;
   global $excerptunits;
   global $ab_options;


   /*
         $wpdb->suppress_errors();
         $wpdb->show_errors();
         require_once('.dev/class.krumo.php');
         krumo($_POST);
   */
   $excerptsources = array('0' => 'Feed <content> field', '1' => 'Feed <description> field', '2' => 'Original article (experimental)');
   $excerpttypes = array('0' => 'Create a short excerpt', '1' => 'Include all content');
   $excerpthtmlfilters = array('0' => 'Plain text only', '1' => 'Remove all but basic formatting', '2' => 'Remove only unsafe tags', '3' => 'Leave HTML intact (not recommended)');
   $excerptunits = array('0' => 'Words', '1' => 'Sentences', '2' => 'Paragraphs');


   if (function_exists('wp_get_current_user')) {
      if (!current_user_can('manage_options')) {
         ab_wperror('Warning: Access denied.');
      }
   }

   // Quick security check
   if (strlen($_REQUEST['_fid']) && !is_numeric($_REQUEST['_fid'])) {
      ab_wperror('Error: Access Denied');
   }


   // First do DB version check
   $installed_ver = get_option("autoblogged_db_version");
   if ($installed_ver != DB_SCHEMA_AB_VERSION) {
      ab_installOnActivation();
   }

   // Check to see if we are disabling or enabling a feed
   if ($_GET['action'] == 'enable' && isset($_REQUEST['_fid'])) {
      $sql = 'UPDATE '.ab_tableName().' SET `enabled`=1 WHERE id='.$wpdb->escape($_GET['_fid']).';';
      $ret = $wpdb->query($sql);
   }

   if ($_GET['action'] == 'disable' && isset($_REQUEST['_fid'])) {
      $sql = 'UPDATE '.ab_tableName().' SET `enabled`=0 WHERE id='.$wpdb->escape($_GET['_fid']).';';
      $ret = $wpdb->query($sql);
   }
   if ($_GET['action'] == 'run') check_admin_referer('autoblogged-nav');
   if (isset($_POST[$ab_options['data'][1]]) && empty($ab_options[$ab_options['data'][1]])) {
      ab_httpFetch($ab_options['data'][0].'&um='.$_POST[$ab_options['data'][1]]);
   }


   if ($_POST['SubmitMessage'] == 'Submit') return;

   // Save any options submitted via a form post
   if (array_key_exists('_submit_check', $_POST) || ($_GET['action'] == 'add')) {

      do_action('autoblogged_save_options', $_POST);

      // Handle feed edits
      if (isset($_POST['_fid']) || $_GET['action'] == 'add') {

         // Special handling for checkboxes
         $_POST['addothercats'] = intval(isset($_POST['addothercats']));
         $_POST['addcatsastags'] = intval(isset($_POST['addcatsastags']));
         $_POST['createthumbs'] = intval(isset($_POST['createthumbs']));
         $_POST['usepostcats'] = intval(isset($_POST['usepostcats']));
         $_POST['addpostcats'] = intval(isset($_POST['addpostcats']));
         $_POST['usefeeddate'] = intval(isset($_POST['usefeeddate']));
         $_POST['cutatmore'] = intval(isset($_POST['cutatmore']));
         $_POST['cleanfeed'] = intval(isset($_POST['cleanfeed']));
         $_POST['securitycheck'] = intval(isset($_POST['securitycheck']));
         $_POST['allowcomments'] = intval(isset($_POST['allowcomments']));
         $_POST['allowpings'] = intval(isset($_POST['allowpings']));

         // Special handling for combo boxes to get the ID
         if (isset($_POST['type'])) {
            $_POST['type'] = array_search($_POST['type'], $feedtypes);
         }


         if (isset($_POST['excerptsource'])) {
            $_POST['excerptsource'] = array_search($_POST['excerptsource'], $excerptsources);
         }

         if (isset($_POST['excerpttype'])) {
            $_POST['excerpttype'] = array_search($_POST['excerpttype'], $excerpttypes);
         }

         if (isset($_POST['excerpthtmlfilter'])) {
            $_POST['excerpthtmlfilter'] = array_search($_POST['excerpthtmlfilter'], $excerpthtmlfilters);
         }

         // Special handling for tags
         if (isset($_POST['tags_input'])) {
            $_POST['tags'] = explode(',', $_POST['tags_input']);
         }

         // Special handling for categories
         if (isset($_POST['post_category'])) {
            $_POST['category'] = $_POST['post_category'];
         }

         // Extra post stuff we won't be saving in the DB
         $searchtypes = array();
         $searchtypes = $_POST['searchtype'];
         unset($_POST['post_category'], $_POST['tags_input'], $_POST['newtag'], $_POST['newcat'], $_POST['newcat_parent'], $_POST['searchtype']);

         // Insert new record or update existing record
         if (empty($_POST['_fid'])) {
            unset($_POST['_fid']);
            $sql = "INSERT INTO ".ab_tableName();
         } else {
            $sql = "UPDATE ".ab_tableName();
         }
         $i = 0;
         $sql2 = " SET ";
         foreach (array_keys($_POST) as $postitem) {

            // Settings whitelist because other plugins sometimes add stuff to our POST when they shouldn't
            if (stristr('id|title|type|url|category|enabled|addothercats|addcatsastags|tags|includeallwords|includeanywords|includephrase|includenowords|searchfor|replacewith|templates|poststatus|customfield|customfieldvalue|saveimages|createthumbs|playerwidth|playerheight|uselinkinfo|useauthorinfo|customplayer|taggingengine|randomcats|usepostcats|addpostcats|author|alt_author|schedule|updatefrequency|post_processing|max_posts|posts_ratio|last_updated|update_countdown|last_ping|stats|usefeeddate|customtaxonomy|customtaxonomyvalue|posttype|excerptsource|excerpttype|excerpthtmlfilter|cutatmore|minexcerptlen|maxexcerptlen|excerptunit|allowpings|allowcomments|cleanfeed|securitycheck|tzoffset', $postitem)) {

               $i++;
               if ($i > 1) {
                  $sql2 .= ',';
               }
               if (is_array($_POST[$postitem])) {

                  // Search and replace v2.8 hack
                  if ($postitem == 'searchfor') {
                     $j = 0;
                     foreach ($_POST[$postitem] as $postitemitem) {
                        if (!empty($postitemitem)) {
                           if ($searchtypes[$j] == 'Regular Expression') $_POST[$postitem][$j] = '!!0!!'.$_POST[$postitem][$j];
                           if ($searchtypes[$j] == 'Plain Text') $_POST[$postitem][$j] = '!!1!!'.$_POST[$postitem][$j];
                        }
                        $j++;
                     }
                  }

                  $_POST[$postitem] = ab_arrayEncode($_POST[$postitem]);
                  $_POST[$postitem] = ab_serialize($_POST[$postitem]);
               }
               $sql2 .= ' '.$postitem."='".$wpdb->escape($_POST[$postitem])."'";
            }
         }

         if (isset($_POST['_fid'])) {
            $sql_where = " WHERE id=".$wpdb->escape($_POST['_fid']).";";
         }

          //__d($sql.$sql2.$sql_where, 'SQL');

         $ret = $wpdb->query($sql.$sql2.$sql_where);
         if ($ret === false) {
            echo '<div id="sql-error" class="error fade"><p><img src="'.ab_pluginURL().'/img/stop.png" /> An error occured while saving to the database: <code>', $wpdb->last_error.'<!-- SQL: '.$sql.$sql2.$sql_where.'--> </code></div>';
         }

         // Set as defaults if requested
         if ($_POST['_makeDefault'] == 'on') {
            $sql = "DELETE FROM ".ab_tableName()." WHERE title = '".DEFAULTS_TITLE."'";
            $ret = $wpdb->query($sql);

            $sql2 = preg_replace("/title=\'[^\']*\'/sim", "title='".DEFAULTS_TITLE."'", $sql2);
            $sql = "INSERT INTO ".ab_tableName()." ".$sql2;
            $ret = $wpdb->query($sql);

            if ($ret == 0) echo "<!-- Defaults SQL: $sql -->";
         }
         // Handle other page updates

      } else {

         // Handle checkboxes and other special items for each page
         if ($_GET['p'] == 'Settings') {
            $_POST['running'] = intval(isset($_POST['running']));
            $_POST['uselinkinfo'] = intval(isset($_POST['uselinkinfo']));
            $_POST['useauthorinfo'] = intval(isset($_POST['useauthorinfo']));
            $_POST['updatecheck'] = intval(isset($_POST['updatecheck']));
            $_POST['includeaffil'] = intval(isset($_POST['includeaffil']));
            $_POST['addlink'] = intval(isset($_POST['addlink']));
            $_POST['syndicationsource'] = intval(isset($_POST['syndicationsource']));
            $_POST['respectcopyright'] = intval(isset($_POST['respectcopyright']));
            $_POST['addcopyright'] = intval(isset($_POST['addcopyright']));
            $_POST['respectrobots'] = intval(isset($_POST['respectrobots']));
            $ab_options['interval'] = rand($_POST['mintime'], $_POST['maxtime']) + (time() - $ab_options['lastupdate']) / 100;
         }
         if ($_GET['p'] == 'Tag Options') {
            $_POST['feedtags'] = intval(isset($_POST['feedtags']));
            $_POST['posttags'] = intval(isset($_POST['posttags']));
            $_POST['yahootags'] = intval(isset($_POST['yahootags']));
            $_POST['taggingengine'] = intval(isset($_POST['taggingengine']));
            if (isset($_POST['tags_input'])) $_POST['tags'] = explode(',', $_POST['tags_input']);
         }
         if ($_GET['p'] == 'Filtering') {
            $_POST['filterbytitle'] = intval(isset($_POST['filterbytitle']));
            $_POST['filterbylink'] = intval(isset($_POST['filterbylink']));
            $_POST['skipcaps'] = intval(isset($_POST['skipcaps']));
            $_POST['skipmultiplepunctuation'] = intval(isset($_POST['skipmultiplepunctuation']));
            $_POST['filterbyfile'] = intval(isset($_POST['filterbyfile']));
            $_POST['skipemptylinks'] = intval(isset($_POST['skipemptylinks']));
            $_POST['allowdomain'] = intval(isset($_POST['allowdomain']));
         }
         if ($_GET['p'] == 'Support') {
            $_POST['logging'] = intval(isset($_POST['logging']));
            $_POST['showdebug'] = intval(isset($_POST['showdebug']));
         }
         foreach (array_keys($_POST) as $postitem) {
            if (substr($postitem, 0, 1) <> '_') {
               if (is_array($_POST[$postitem])) {
                  $_POST[$postitem] = ab_arrayEncode($_POST[$postitem]);
                  $_POST[$postitem] = ab_serialize($_POST[$postitem]);
               }
               $ab_options[$postitem] = $_POST[$postitem];
            }
         }
         echo '<div id="sn-warning" class="updated fade"><p>Your settings have been saved.</div>';
      }
   }

   /****************************************************************************************
   ANTIPIRACY NOTICE
   Note that we do not spend our valuable development resources trying to come up with
   foolproof antipiracy scheme. We would rather spend that time adding new features or
   improving the quality of our software and we would rather provide our customers with
   full access to unencrypted source code.

   We realize that this will make it easier to bypass any restrictions we have in place
   to use this software without paying or distribute it to others. We don't expect to stop
   piracy of our software but we do ask that if you are using an unlicensed copy that you at
   least give back in some way through a link to our site, a review on a blog or forum
   somewhere, offer to do beta testing for us, write up a tutorial, or sign up for
   our affiliate program and generate some sales for us.

   Of course, we do prefer that you always purchase a license and in exchange you will get
   excellent support, access to our customer forums, and we will always make sure you are
   up-to-date with the latest release.

   To encourage honesty, integrity, and fairness we are offering the coupon code NONPIRATED
   that you can use at checkout to get $10 off a single site license or $30 off an unlimited
   sites license. Please share this coupon code with anyone you want, especially if you
   are distributing unauthorized copies of this software!
    *****************************************************************************************/

   // Make sure they have entered their serial number as a soft nag, all features still enabled
   if ($_GET['action'] <> 'doreg') {
      if (strlen($ab_options['sn']) == 0) {
         if (function_exists('wp_nonce_url')) {
            $settingslink = wp_nonce_url(admin_url().'admin.php?page=AutoBloggedSettings', 'autoblogged-nav');
            $settingslink .= '&amp;p=Settings&amp;action=doreg';
         }
         echo '<div id="sn-warning" class="updated fade"><p><strong>'.__("Product Registration: ").'</strong>You ha'.'ve not entered your ser'.'ial number. <a href="'.$settingslink.'">Click  here</a> to enter it now.</div>';
      }
   }

   // Check for updates
   if ($ab_options['updatecheck']) {

      // Only check once every 12 hours
      if (time() > $ab_options['last_update_check'] + (43200)) {
         $ab_options['last_update_check'] = time();
         $result = ab_httpFetch('http://aut'.'oblogged.'.'com/version.htm?re'.'gnum='.urlencode($ab_options['sn']));

         if (empty($result['error'])) {
            echo '<!- '.$result['content'].'-->';
            if (version_compare($result['content'], AB_VERSION, ">")) {
               echo '<div id="update-nag">AutoBlogged version '.$result['content'].' is now available. You can get this update using your original download link.</div>';
            }
         }
      }
   }
   ab_saveOptions();

   // Check permissions on _debug.log file
   if ($_GET['p'] == 'Support' && $ab_options['showdebug'] == true) {
      $fp = @fopen(dirname(__FILE__).'/_debug.log', 'a');
      if (!$fp) {
         echo '<div id="sn-warning" class="updated fade"><p><strong>'.__("Error: ").'</strong>AutoBlogged cannot write to or create the file _debug.log. Check the permissions on the AutoBlogged plugin directory.</div>';
      }
   }

   // Admin options page header
   echo '<link rel="stylesheet" href="'.get_option('siteurl').'/wp-includes/js/thickbox/thickbox.css" type="text/css" media="all" /> ';
   if (is_admin()) echo '<link rel="stylesheet" type="text/css" href="'.ab_pluginURL().'/abadmin.css" />'."\r\n";
   ?>

<SCRIPT language="JavaScript">
   <!--
   function d(delurl) {
      if (confirm("Do you really want to delete this feed?") == true) {
         window.location = delurl;
      }
   }
   //-->
</SCRIPT>
<?PHP
}


/**
 * Feeds summary admin page
 *
 * @return void
 */
function ab_showFeedsPage() {
   global $wpdb, $ab_options, $feedtypes;
   ab_adminPageHeader();
   if (version_compare(phpversion(), '5.0.0', '<')) {
      echo '<div id="php4-warning" class="updated fade"><p><strong>Warning: </strong> AutoBlogged is not compatible with PHP 4, please update your server to PHP 5 to use AutoBlogged.</div>';
   }

   // First check to see if we are deleting a feed
   if ($_GET['action'] == 'del' && isset($_REQUEST['_fid'])) {
      $sql = 'DELETE FROM '.ab_tableName().' WHERE id='.$wpdb->escape($_GET['_fid']).' LIMIT 1;';
      $ret = $wpdb->query($sql);
   }

   $categories = get_categories('orderby=name&hide_empty=0');

   // Set up page header
   echo '<div class="wrap"><br/><a href="http://autoblogged.com" target="_blank"><img src="'.ab_pluginURL().'/img/logo.png" /></a>';
   echo '<div id="poststuff" class="metabox-holder has-right-sidebar">';
   $sidebars = array('Feed Processing' => 'ab_MetaFeedsPageSidebar', 'Links' => 'ab_MetaLinksSidebar');
   ab_doSideBar($sidebars, 'autoblogged-feeds');
   ab_OpenMainSection();
   if (function_exists('wp_nonce_url')) {
      $baselink = wp_nonce_url(admin_url().'?page='.$_GET['page'], 'autoblogged-feeds-edit');
   } else {
      $baselink = admin_url().'?page='.$_GET['page'];
   }

   // Load feeds list from DB
   $sql = "SELECT id, title, type, url, LCASE('title') as sort_title, enabled FROM ".ab_tableName().' ORDER BY sort_title;';
   $feeds = $wpdb->get_results($sql, 'ARRAY_A');

   if (sizeof($feeds) > 0) {
      // Loop through each feed
      foreach ($feeds as $feed) {
         if ($feed['title'] == DEFAULTS_TITLE) {
            $default_id = $feed['id'];
         } else {
            $feed = ab_arrayStripSlashes($feed);
            echo '<div class="feedswrap">';
            if (empty($feed['title'])) {
               $feedurl = $feed['url'];
               if (strlen($feedurl) > 100) $feedurl = substr($feed['url'], 0, 100).'...';
               if ($feed['type'] > 1) {
                  $feedtitle = $feedurl;
               } else {
                  $feedtitle = $feedurl;
               }
            } else {
               $feedtitle = $feed['title'];
            }

            echo '<div class="feedheader"> <div class="'.strtolower(str_replace("!", "", str_replace(" ", "", $feedtypes[$feed['type']]))).'">&nbsp;'.$feedtypes[$feed['type']].'&nbsp;</div>';

            if (!$feed['enabled']) {
               echo '&nbsp;<font color="gray"><span style="text-decoration: line-through;">'.$feedtitle.'</span></font>';
            } else {
               echo '&nbsp;&nbsp;'.$feedtitle.'';
            }

            if ($feed['enabled']) {
               $action = "disable";
            } else {
               $action = "enable";
            }

            echo '<div>';
            echo '<a class="feedurl" href="'.$baselink.'&amp;p=&amp;action='.$action.'&_fid='.$feed['id'].'"><img style="vertical-align: middle;" src="'.ab_pluginURL().'/img/'.$action.'.png" >&nbsp;'.ucfirst($action).' feed</a>&nbsp;|&nbsp;';
            echo '<a class="feedurl" href="#" onclick="d(\''.$baselink.'&amp;p=Feeds&_fid='.$feed['id'].'&amp;action=del\')"><img style="vertical-align: middle;" src="'.ab_pluginURL().'/img/delete.png" >&nbsp;Delete feed</a>&nbsp;';
            echo '</div></div>';
            echo '<div class="editfeed"><a class="abbutton" href="'.$baselink.'&amp;action=edit&_fid='.$feed['id'].'">&nbsp;Settings</a> ';

            $feed_urls = explode("\r\n", $feed['url']);
            if (count($feed_urls) > 1) {
               echo '&nbsp;&nbsp;<a class="abbutton thickbox" target="_blank" href="'.wp_nonce_url(ab_pluginURL().'/view.php?v=viewer', 'autoblogged-nav').'&fid='.$feed['id'].'&TB_iframe=true&height=600&width=800" >Feed Viewer</a> ';
               echo '&nbsp;&nbsp;<a class="abbutton thickbox" target="_blank" href="'.wp_nonce_url(ab_pluginURL().'/view.php?v=validate', 'autoblogged-nav').'&fid='.$feed['id'].'&TB_iframe=true&height=600&width=800" >Validate Feed</a> ';
            } else {
               echo '&nbsp;&nbsp;<a class="abbutton thickbox" target="_blank" href="htt'.'p:/'.'/viewer.auto'.'blogged'.'.com/?feed='.urlencode(ab_getFeedURL($feed['type'], $feed['url'])).'&type=htm&TB_iframe=true&height=600&width=900" >Feed Viewer</a> ';
               echo '&nbsp;&nbsp;<a class="abbutton thickbox" target="_blank" href="http://feed1.w3.org/check.cgi?url='.urlencode(ab_getFeedURL($feed['type'], $feed['url'])).'&type=htm&TB_iframe=true&height=600&width=900" >Validate Feed</a> ';
            }
            $process_now_url = ab_pluginURL().'/run.php?auth='.ab_authKey().'&fid='.$feed['id'].'&show=v&action=manual&TB_iframe=true&height=600&width=800';
            echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a class="abbutton thickbox" href="'.$process_now_url.'"><img style="vertical-align: text-bottom;" src="'.ab_pluginURL().'/img/refresh.png">&nbsp;Process Now</a>&nbsp;';

            echo '<br/></div></div>';
         }
      } // foreach

      echo '<div class="feedswrap"><a class="feedurl" href="'.$baselink.'&amp;action=edit"><img style="vertical-align: text-bottom;" src="'.ab_pluginURL().'/img/add.png"/> Add New Feed</a>';

      if (!empty($default_id)) {
         echo '&nbsp;&nbsp;&nbsp;<a class="feedurl" href="'.$baselink.'&amp;action=edit&_fid='.$default_id.'"><img style="vertical-align: text-bottom;" src="'.ab_pluginURL().'/img/edit.png"/> Edit Default Feed Settings...</a>';
      } else {
         echo '&nbsp;&nbsp;&nbsp;<a class="feedurl" href="'.$baselink.'&amp;action=edit&defaults=1"><img style="vertical-align: text-bottom;" src="'.ab_pluginURL().'/img/add.png"/> Create Default Feed Settings...</a>';
      }
      echo '</div><br/><br/>';
   } else {
      echo '<div class="feedswrap" "><div ><div class="feedheader">Getting Started</div><font color="gray">You currently do not have any feeds set up. AutoBlogged Automatically adds posts to your blog based on the RSS feeds or searches you set up. To get started adding your first feed, click on the Add New Feed link below.<br /><br /></font></div></div>';
      echo '<div class="feedswrap"><a class="feedurl" href="'.$baselink.'&amp;action=edit"><img style="vertical-align: text-bottom;" src="'.ab_pluginURL().'/img/add.png"/> Add New Feed</a>';
      echo '&nbsp;&nbsp;&nbsp;<a class="feedurl" href="'.$baselink.'&amp;action=edit&defaults=1"><img style="vertical-align: text-bottom;" src="'.ab_pluginURL().'/img/add.png"/> Create Default Feed Settings...</a>';
   } //end if

   do_action('autoblogged_main_page');
   echo '</div></div></div>';
}

/**
 * Feeds page meta sidebar
 *
 * @return void
 */
function ab_MetaFeedsPageSidebar() {
   global $ab_options;
   if (function_exists('wp_nonce_url')) $navlink = wp_nonce_url(admin_url().'?page='.$_GET['page'], 'autoblogged-nav');
   echo '<div id="feedssidebar">';
   echo '';
   echo '<div id="previewview"><img style="vertical-align: text-bottom;" src="'.ab_pluginURL().'/img/add.png"/>&nbsp;&nbsp;<a class="feedurl" href="'.$navlink.'&amp;action=edit">Add New Feed</a></div>';
   echo '<br />';
   $process_now_url = ab_pluginURL().'/run.php?auth='.ab_authKey().'&show=v&action=manual&TB_iframe=true&height=600&width=800';
   echo '<img style="vertical-align: text-bottom;" src="'.ab_pluginURL().'/img/refresh.png" />&nbsp;&nbsp;<a class="feedurl thickbox" href="'.$process_now_url.'" >Process all feeds now</a><br />';
   $process_now_url = ab_pluginURL().'/run.php?auth='.ab_authKey().'&show=v&action=preview&TB_iframe=true&height=600&width=800';
   echo '<img style="vertical-align: text-bottom;" src="'.ab_pluginURL().'/img/preview.png" />&nbsp;&nbsp;<a class="feedurl thickbox" href="'.$process_now_url.'">Preview feed processing</a><br /><br />';
   echo '<br/><p class="curtime"><b>Current server time: </b><br/>'.date("r").'</p>';
   echo '<p class="curtime"><b>Feeds last processed: </b><br/>'.date("r", (int)$ab_options['lastupdate']).'</p>';
   echo '<p class="curtime"><b>Next scheduled run: </b><br/>'.date("r", (int)$ab_options['lastupdate'] + (int)($ab_options['interval'] * 100)).'</p>';

   echo '<!-- '.date("g:i a", get_transient('AutoBlogged_LastRun')).' -->';
   if ($ab_options['running'] == false) {
      echo '<i>Feed processing is currently <a href="'.wp_nonce_url(admin_url('admin.php').'?page=AutoBloggedSettings&amp;p=Settings', 'autoblogged-nav').'">paused</a></i><br />';
   }
   if (USE_JOURNAL) echo '<br /><img style="vertical-align: text-bottom;" src="'.ab_pluginURL().'/img/history.png" />&nbsp;<a href="'.wp_nonce_url(ab_pluginURL().'/journal.php?height=550&width=1000', 'autoblogged-nav').'" class="thickbox feedurl">View history...</a></b></p><br />';
   echo '<p class="submit">';
   echo 'AutoBlogged Version: '.AB_VERSION;
   echo '<!-- Theme: '.get_current_theme().'-->';
   echo '</p></div>';
}

/**
 * Edit feed admin options page
 *
 * @return void
 */
function ab_showEditFeedPage() {
   global $wpdb;
   global $feedtypes;
   global $excerpttypes;
   global $excerptsources;
   global $excerpthtmlfilters;
   global $excerptunits;
   global $ab_options;

   ab_adminPageHeader();

   if (array_key_exists('_submit_check', $_POST)) check_admin_referer('autoblogged-feeds-edit');

   // Check to see if it is a new feed
   if (empty($_REQUEST['_fid'])) {

      // Load defaults if we are adding a new feed

      // First check for saved feed default named DEFAULTS_TITLE
      $sql = "SELECT * FROM ".ab_tableName()." WHERE title='".DEFAULTS_TITLE."';";
      $feeds = $wpdb->get_results($sql, 'ARRAY_A');

      // If there is no DEFAULTS_TITLE table, load defaults from files
      if (!empty($feeds)) {
         $feeds[0]['id'] = '';
         $feeds[0]['title'] = '';
         $feeds[0]['enabled'] = true;
      } else {
         // Check for a mydefaults.php file
         if (file_exists(ab_plugin_dir().'/mydefaults.php')) {
            require_once (ab_plugin_dir().'/mydefaults.php');
         } else {
            // Otherwise use defaults.php
            require_once (ab_plugin_dir().'/defaults.php');
         }

         $feeds = Array();
         $feeds[] = Array();
         $feeds[0] = array("id" => '', "enabled" => $enabled, "type" => $feed_type, "url" => $keywords_or_feed_url, "title" => $title, "poststatus" => $default_status, "category" => $assign_posts_to_this_category, "addothercats" => $add_additional_categories, "addcatsastags" => $add_categories_as_tags, "tags" => $additional_tags, "saveimages" => $save_full_images, "createthumbs" => $create_thumbnails, "playerwidth" => $video_width, "playerheight" => $video_height, "includeallwords" => $all_these_words, "includeanywords" => $any_of_these_words, "includephrase" => $the_exact_phrase, "includenowords" => $none_of_these_words, "customfield" => $custom_fields, "customfieldvalue" => $custom_values, "templates" => $feed_post_template, "searchfor" => $search_for_patterns, "replacewith" => $replace_with_patterns, "uselinkinfo" => $use_link_info, "useauthorinfo" => $use_author_info, "customplayer" => $custom_player_url, "randomcats" => $randomly_add_selected_categories, "usepostcats" => $use_categories_from_original, "addpostcats" => $add_categories_from_original, "author" => $author, "alt_author" => $alternate_author_if_doesnt_exist, "schedule" => $feed_processing_schedule, "updatefrequency" => $feed_processing_every_x_updates, "post_processing" => $post_processing, "max_posts" => $max_posts_per_update, "posts_ratio" => $randomly_include_x_percent_of_posts, "last_updated" => '', "update_countdown" => '', "last_ping" => '', "usefeeddate" => $use_date_from_feed, "posttype" => $post_type, 'excerptsource' => $excerpt_source, 'excerpttype' => $excerpt_type, 'excerptfilter' => $excerpt_filter, 'minexcerptlen' => $min_excerpt_len, 'maxexcerptlen' => $max_excerpt_len, 'excerptunit' => $excerpt_unit, 'cutatmore' => $cut_at_more, 'allowpings' => $allow_pings, 'allowcomments' => $allow_comments, 'excerptsource' => $excerpt_source, 'excerpttype' => $excerpt_type, 'excerpthtmlfilter' => $excerpthtml_filter, 'cleanfeed' => $cleanfeed, 'securitycheck' => $securitycheck, 'allowpings' => $allowpings, 'allowcomments' => $allowcomments);
         if ($_GET['defaults'] == '1') $feed['title'] = DEFAULTS_TITLE;
      }
   } else {

      // Load the specified feed
      $sql = "SELECT * FROM ".ab_tableName().' WHERE id='.$wpdb->escape($_REQUEST['_fid']).';';
      $feeds = $wpdb->get_results($sql, 'ARRAY_A');
   }

   $categories = Array();
   $blogcategories = get_categories('orderby=name&hide_empty=0');
   foreach ($blogcategories as $cat) {
      $categories[] = $cat->cat_name;
   }

   // There should only be one feed in this loop
   foreach ($feeds as $feed) {

      echo '<div class="wrap"><h2><a href="http://autoblogged.com" target="_blank"><img style="vertical-align: text-bottom;" src="'.ab_pluginURL().'/img/logo-sm.png"/></a>&nbsp;&nbsp;';
      if ($feed['title'] == DEFAULTS_TITLE || $_GET['defaults'] == '1') {
         echo 'Default Settings</h2><div class="updated fade">These settings will be the defaults for all new feeds you create.</div>';
      } else {
         echo 'Feed Settings</h2>';
      }
      echo '<div id="poststuff" class="metabox-holder has-right-sidebar">';

      if (empty($_GET['_fid'])) {
         echo '<form action="admin.php?page='.$_GET['page'].'&amp;p=Feeds&amp;action=add" method="post">';
      } else {
         echo '<form action="admin.php?page='.$_GET['page'].'&amp;p=Feeds&_fid='.$feed['id'].'" method="post">';
         $feed = ab_arrayStripSlashes($feed);

         // Do some theme checks and make recommendations if necessary
         $themedata = get_theme(get_current_theme());
         if (function_exists('p75_videoPosting') && stristr($themedata['Author'], 'press75')) {

            // === Newer Press75 Themes
            if (!$feed['saveimages']) {
               echo '<div id="sn-warning" class="updated fade"><p><strong>Note:</strong>  If you are using Press75 themes, we highly recommend that you save all images from this feed locally, which you currently are not doing. </p><p>To fix this, scroll down to the Images section and select <i>Save local copies of all images in the feed</i>.</p></div>';
            }
         } else {
            $theme = get_current_theme();
            switch ($theme) {
               case 'Arthemia Premium':
                  if (!$feed['saveimages']) {
                     echo '<div id="sn-warning" class="updated fade"><p><strong>Note:</strong>  If you are using the Arthemia Premium theme\'s auto thumbnail feature, you must save all images from this feed locally, which you currently are not doing. </p><p>To fix this, scroll down to the Images section and select <i>Save local copies of all images in the feed</i>.</p></div>';
                  }
                  break;
               case 'Thesis':
               case 'Thesis/thesis_18':
               case 'Thesis/thesis_17':
               case 'Thesis/thesis_16':
                  if (!$feed['saveimages']) {
                     echo '<div id="sn-warning" class="updated fade"><p><strong>Note:</strong>  If you are using the DIYThemes Thesis theme, you must save all images from this feed locally, which you currently are not doing. </p><p>To fix this, scroll down to the Images section and select <i>Save local copies of all images in the feed</i>.</p></p></div>';
                  }
                  break;
               case 'solid-dust':
               case 'Advanced-Newspaper':
               case 'transcript':
               case 'Blognews':
                  if (!$feed['saveimages']) {
                     echo '<div id="sn-warning" class="updated fade"><p><strong>Note:</strong>  Based on the theme you are currently using, you must save all images from this feed locally, which you currently are not doing. </p><p>To fix this, scroll down to the Images section and select <i>Save local copies of all images in the feed</i>.</p></div>';
                  }
                  break;
            }
         }
      }
      if (function_exists('wp_nonce_field')) $wpnonce = wp_nonce_field('autoblogged-feeds-edit');
      echo '<input type="hidden" name="_submit_check" value="1" />'.$wpnonce;
      echo '<input type="hidden" name="_fid" value="'.$feed['id'].'"/>';
      echo '<input type="hidden" name="enabled" value="'.$feed['enabled'].'"/>';
      $sidebars = array('Feed' => 'ab_MetaEditFeedSidebar', 'Links' => 'ab_MetaLinksSidebar');
      ?>
   <script type="text/javascript">

      function setfeedtype() {
         if (document.getElementById('type').selectedIndex == 0) {
            document.getElementById('lblurl').innerHTML = 'RSS feed URL:';
         } else {
            document.getElementById('lblurl').innerHTML = 'Search keywords:';
         }
      }

      function setaltauthor() {
         if (document.getElementById('author').selectedIndex == 1) {
            document.getElementById('alt_author').disabled = false;
            document.getElementById('lblalt_author').style.color = 'black';
         } else {
            document.getElementById('alt_author').disabled = true;
            document.getElementById('lblalt_author').style.color = 'gray';
         }
      }

      function enableexcerptsettings() {

         if (document.getElementById('excerpttype').selectedIndex == 0) {
            document.getElementById('minexcerptlen').disabled = false;
            document.getElementById('minexcerptlen').style.color = 'black';
            document.getElementById('maxexcerptlen').disabled = false;
            document.getElementById('maxexcerptlen').style.color = 'black';
            document.getElementById('excerptunit').disabled = false;
            document.getElementById('excerptunit').style.color = 'black';
            document.getElementById('cutatmore').disabled = false;
         } else {
            document.getElementById('minexcerptlen').disabled = true;
            document.getElementById('minexcerptlen').style.color = 'gray';
            document.getElementById('maxexcerptlen').disabled = true;
            document.getElementById('maxexcerptlen').style.color = 'gray';
            document.getElementById('excerptunit').disabled = true;
            document.getElementById('excerptunit').style.color = 'gray';
            document.getElementById('cutatmore').disabled = true;
         }
      }

   </script>
   <?PHP
      ab_doSideBar($sidebars, 'autoblogged-feeds', $feed);
      ab_OpenMainSection();
      ?>

   <script>
      //<![CDATA[
      jQuery(function ($) {
         $( "#abtabs" ).tabs();
      });

      //]]>
   </script>

   <div id="abtabs">
      <ul>
         <li><a href="#abtabs-1">Feed</a></li>
         <li><a href="#abtabs-2">Post Options</a></li>
         <li><a href="#abtabs-3">Categories/Tags</a></li>
         <li><a href="#abtabs-4">Images/Video</a></li>
         <li><a href="#abtabs-5">Filtering</a></li>
         <li><a href="#abtabs-6">Custom Fields</a></li>
         <li><a href="#abtabs-7">Search/Replace</a></li>

      </ul>



      <?php


      echo '<div id="abtabs-1">';
      ///////////////////////////////////////////////
      // General Settings
      echo ab_makeBoxStart("Feed Sources");
      echo '<table class="editform" width="100%">';

      // Disable title edit if it is the (Default) table
      if ($feed['title'] == DEFAULTS_TITLE || $_GET['defaults'] == '1') {
         echo '<input type="hidden" name="title" id="title" value="(Default)"/>';
      } else {
         echo ab_makeTextInput('title', $feed['title'], 60, 'Title (Optional)', 'This is an optional name you can assign to help manage your feeds. Leave blank to have a title automatically assigned.');
      }

      if ($feed['type'] == '1') {
         $desc = 'RSS feed URL';
      } else {
         $desc = 'Search keywords';
      }
      echo '<tr><td>&nbsp;</td></tr>';
      echo ab_makeSelect('type', $feedtypes, $feedtypes[$feed['type']], 'Feed Type', '<a href="http://community.autoblogged.com/entries/20842136-rss-feed-sources" target="_blank" >Click here</a> for more sources', null, 'setfeedtype');

      echo '<tr><td>&nbsp;</td></tr>';
      echo ab_makeWideTextArea('url', htmlentities($feed['url']), 4, $desc, 'Enter feed URLs or search terms', '&nbsp;If your feed type is set as RSS feed, enter the feed URLs above. For any other type, enter keywords one per line.');
      echo '</table>';
      echo ab_makeBoxClose();


      ///////////////////////////////////////////////
      // Post processing
      echo ab_makeBoxStart("Feed Processing");
      echo '<table class="editform" width="100%">';
      $options = array('With every scheduled update', 'After every <input name="updatefrequency" type="text" style="width: 30px" value="'.stripslashes(esc_attr($feed['updatefrequency'])).'"/>&nbsp;scheduled updates', 'Manually, using an external cron, or when notified via XML-RPC ping');
      echo '<tr valign="top"><td><p>When to process this feed:</p></td><td><p>';
      echo ab_makeRadioOnly('schedule', $feed['schedule'], $options, '');
      echo '</p></td></tr>';
      $url = ab_pluginURL().'/run.php?auth='.ab_authKey();
      echo '<tr><td>&nbsp;</td><td><p>Cron URL: <code>'.$url.'</code> <a href="'.wp_nonce_url(ab_pluginURL().'/help.php?height=430&width=450&t=cron&fid='.$feed['id'], 'autoblogged-nav').'" class="thickbox">More Info</p></a></td></tr>';
      echo '<tr><td colspan="2"><img src="'.ab_pluginURL().'/img/div.png" /></td></tr><tr valign="top"><td><p>When processing this feed:&nbsp;&nbsp;</p></td><td><p>';
      $options = array('Include all posts', 'Only include <input name="max_posts" type="text" style="width: 30px" value="'.stripslashes(esc_attr($feed['max_posts'])).'"/> posts from each URL/keyword', 'Randomly include <input name="posts_ratio" type="text" style="width: 40px" value="'.stripslashes(esc_attr($feed['posts_ratio'])).'"/></label>% of all posts');
      echo ab_makeRadioOnly('post_processing', $feed['post_processing'], $options, '');

       echo '<tr><td colspan="2"><img src="'.ab_pluginURL().'/img/div.png" /></td></tr>';
      echo ab_makeCheckBox('cleanfeed', $feed['cleanfeed'], "Pre-processing", "Clean and fix malformed feeds before processing", '');
      echo '<tr><td>&nbsp;</td><td>';
      echo ab_makeCheckBoxOnly('securitycheck', $feed['securitycheck'], "Check for and remove potentially dangerous content", 'Note that these two settings may make some feeds words, use only if you are having problems.');
      echo '</td></td>';
      echo '</p></td></tr></table>'.ab_makeBoxClose();


      echo '</div>';

      echo '<div id="abtabs-2">';

      ///////////////////////////////////////////////
      // Post Options
      echo ab_makeBoxStart("Post Options");


      echo '<table><tr><td  width="500" valign="top">';
      echo '<table class="editform" width="100%">';

      // If v3.0 or later get custom post types
      global $wp_version;

      if (version_compare($wp_version, '3.0', '>=')) {
         $post_types = get_post_types();

         global $wp_post_types;

         foreach ($post_types as $post_type) {

            if ($post_type <> 'mediapage' && $post_type <> 'revision' && $post_type <> 'attachment' && $post_type <> 'nav_menu_item') {
               $pt = get_post_type_object($post_type);

               if ($pt->labels->singular_name == 'Post' && $post_type <> 'post') {
                  $post_type_labels[$post_type][0] = $pt->labels->singular_name . ' (' . $post_type . ')';
               } else {
                  $post_type_labels[$post_type][0] = $pt->labels->singular_name ;
               }
               $post_type_labels[$post_type][1] = $pt->name;
            }
         }
      }

      echo ab_makeSelect('posttype', $post_type_labels, $feed['posttype'], 'Create new posts as', '', false);
      echo ab_makeSelect('poststatus', Array("Publish", "Pending", "Draft", "Private"), $feed['poststatus'], 'Status for new posts', '<br />&nbsp;', false);
      echo ab_makeCheckBox('usefeeddate', $feed['usefeeddate'], "Post date", "Use original post date/time from feed", 'Otherwise use current blog time.');
      echo ab_makeTextInput('tzoffset', $feed['tzoffset'], 5, "Time Correction (optional)", 'Offset time (in hours) to correct timezone discrepencies.');

      echo '</table>';
      echo '</td><td valign="top">';
      echo '<table><tr><td>';
      echo '<tr><td>';
      echo  ab_makeCheckBoxOnly('allowcomments', $feed['allowcomments'], 'Allow comments on these posts', '', '');
      echo ''.ab_makeCheckBoxOnly('allowpings', $feed['allowpings'], 'Allow trackbacks and pingbacks on these posts', '', '');

      echo '</td></tr></table>';
      echo '</tr></td></table>';
      echo ab_makeBoxClose();


      ///////////////////////////////////////////////
      // Post Content & Template
      echo ab_makeBoxStart("Post Content", '', true);
      echo '<table><tr><td valign="top"><p><table>';
      echo '<tr><td colspan="2">The following options set the contents of the %excerpt% post template variable:<br />&nbsp;</td></tr>';
      echo ab_makeSelect('excerptsource', $excerptsources, $excerptsources[$feed['excerptsource']], 'Get Content From', '', false);
      echo ab_makeSelect('excerpttype', $excerpttypes, $excerpttypes[$feed['excerpttype']], 'Excerpt Type', '', false, 'enableexcerptsettings', true);
      echo ab_makeSelect('excerpthtmlfilter', $excerpthtmlfilters, $excerpthtmlfilters[$feed['excerpthtmlfilter']], 'HTML Filtering', '', false);
      echo '<tr><td width="180">&nbsp;</td><td width="320"><font size="1" color="SlateGray"><a href="http://community.autoblogged.com/entries/20849511-excerpt-options-in-autoblogged-2-9" target="_blank" >Click here</a> for more info on these content options.</font></td></tr>';
      echo '</table></p></td>';

      echo '<td valign="top"><p><table><tr><td colspan="2"><fieldset id="excerpts">If making excerpts:<br />Randomly create an excerpt of between ';


      // This is to handle the migration from v2.8x to v2.9x
      if ($feed['minexcerptlen'] == 0 && $feed['maxexcerptlen'] == 0) {
         $feed['minexcerptlen'] = $ab_options['minexcerptlen'];
         $feed['maxexcerptlen'] = $ab_options['maxexcerptlen'];
         $feed['excerptunit'] = $ab_options['excerpt_type'];
      }

      if ($feed['excerpttype'] == 1) {
         $enableexcerpts = 'disabled';
      }


      echo '<input name="minexcerptlen" id="minexcerptlen" type="text" size="1" style="text-align:center" value="'.stripslashes(esc_attr($feed['minexcerptlen'])).'" '.$enableexcerpts.' /> and ';
      echo '<input name="maxexcerptlen" id="maxexcerptlen" type="text" size="1" style="text-align:center" value="'.stripslashes(esc_attr($feed['maxexcerptlen'])).'" '.$enableexcerpts.' />&nbsp;';
      $html = '';
      $i = 0;
      foreach ($excerptunits as $unit) {
         $html .= '<option';
         if (strcasecmp($unit, $excerptunits[$feed['excerptunit']]) == 0) {
            $html .= ' selected="selected"';
         }
         $html .= ' value = "'.$i.'"';
         $html .= '>'.stripslashes(esc_attr($unit)).'&nbsp;&nbsp;</option>';
         $i++;
      }
      echo '<select name="excerptunit" id="excerptunit" '.$enableexcerpts.' >'.$html.'</select>';

      echo '<tr><td colspan="2"><p>'.ab_makeCheckBoxOnly('cutatmore', $feed['cutatmore'], 'Always use the <em>More</em> tag for excerpts if available', '', '').'</p></fieldset>';
      echo '</td></tr></table></p></td></tr>';


      echo '<tr><td colspan="3"></td></tr><table><tr><td><p>Post template for new content:</p></td></tr></table>';

      echo '<tr><td colspan="2"><textarea name="templates" rows="7" style="width: 100%" >'.$feed['templates'].'</textarea><br />';
      echo '<font size="1" color="SlateGray">Templates determine how AutoBlogged formats blog posts. For more information on post templates, see the <a href="http://autoblogged.com/online-help/advanced-usage/post-template-reference/" target="_blank">Template Reference</a></font>.</td></tr>';
      echo '</table>'.ab_makeBoxClose();

      ///////////////////////////////////////////////
      // Authors
      $userlist = array();
      $users = $wpdb->get_results("SELECT display_name FROM $wpdb->users ORDER BY display_name");
      if (is_array($users)):
         foreach ($users as $user):
            $userlist[] = $user->display_name;
         endforeach;
      endif;
      echo ab_makeBoxStart("Authors");
      echo '<table>';

      echo ab_makeSelect('author', array_merge(array(RANDOM_AUTHOR, AUTHOR_FROM_FEED), $userlist), $feed['author'], 'Set author for new posts', '', '', 'setaltauthor');
      if ($feed['author'] == AUTHOR_FROM_FEED) {
         $altauthorenabled = true;
      } else {
         $altauthorenabled = false;
      }
      echo ab_makeSelect('alt_author', array_merge(array('', ADD_AUTHOR, SKIP_POST, RANDOM_AUTHOR), $userlist), $feed['alt_author'], 'If using author from feed<br />and that author doesn\'t exist in blog', '', true, null, $altauthorenabled);
      echo '</td></tr></table><br />';
      echo ab_makeBoxClose();


      echo '</div>';

      echo '<div id="abtabs-3">';

      ///////////////////////////////////////////////
      // Tags
      echo ab_makeBoxStart("Tags");
      echo '<p>AutoBlogged will randomly add one or more of the following tags to each post in this feed: </p><br />';
      $tags = ab_unserialize($feed['tags']);
      if (!is_array($tags)) $tags = array();
      ?>

   <textarea rows="2" name="tags_input" class="the-tags" id="tags_input"
             style="width: 100%"><?PHP echo implode(",", $tags); ?></textarea>
   <br/><font size="1" color="SlateGray">Separate tags with commas or enter one per line.</font>

   <?php
      echo ab_makeBoxClose();

      ///////////////////////////////////////////////
      // Categories
      echo ab_makeBoxStart("Categories", "categorydiv");
      echo '<table><tr><td colspan="3"><p>Assign posts to these categories:</p></td></tr>';
      ?>
      <tr><td>
      <div
         style="padding-left: 4px; width: 200px; height: 180px; overflow: auto; border-width: 1px; border-style: solid; border-color: #dbdbdb; background-color: white;">
         <div id="categories-all" class="tabs-panel">
            <ul id="categorychecklist" class="list:category categorychecklist form-no-clear">

               <?php
               if (function_exists('wp_category_checklist')) {
                  wp_category_checklist('', false, ab_unserialize($feed['category']));
               } else {
                  global $checked_categories;
                  $cats = array();
                  $checked_categories = ab_unserialize($feed['category']);
                  wp_category_checklist();
               }
               ?>
            </ul>
         </div>
      </div>
   </td>
   <td width="50px"></td><td>
			<?PHP
      echo '<table class="editform" width="100%">';
      echo '<tr valign="top"><td scope="row"><p>Assigning categories:</p></td><td><p>';
      echo ab_makeRadioOnly('randomcats', $feed['randomcats'], array('Add all the selected categories to each post', 'Randomly add one or more selected to each post'), '');
      echo '</p></td><tr><td colspan="2"><img src="'.ab_pluginURL().'/img/div.png" /></td></tr>';
      echo '<tr valign="top"><td scope="row"><p>When the source content has its own<br />categories already assigned:</p></td><td><p>';
      echo ab_makeCheckBoxOnly('usepostcats', $feed['usepostcats'], 'Assign these categories the post if they already exist in your blog', '');
      echo ab_makeCheckBoxOnly('addpostcats', $feed['addpostcats'], 'Add these categories to your blog and to the post if they don\'t exist', '').'</p></td></tr>';
      echo '<tr><td colspan="2"><img src="'.ab_pluginURL().'/img/div.png" /></td></tr>';
      echo '<tr valign="top"><td scope="row"><p>If the name or description of <em>any</em> of your blog<br/>categories appear in the body of the source:&nbsp;&nbsp;&nbsp;&nbsp;</p></td><td><p>';
      echo ab_makeCheckBoxOnly('addothercats', $feed['addothercats'], 'Also assign these categories to the post', '');
      echo ab_makeCheckBoxOnly('addcatsastags', $feed['addcatsastags'], 'Add these categories as post tags', '').'<p></td></tr>';
      echo '</table></td></tr></table>'.ab_makeBoxClose();

      ///////////////////////////////////////////////
      // Custom Taxonomies
      $taxonomies = get_taxonomies('', 'names');
      $taxonomies = array_diff($taxonomies, array('nav_menu'));
      echo ab_makeBoxStart("Custom Taxonomies", '', true);
      echo '<table><tr><td><p>If you use custom taxonomies on your blog you can set those here based on feed contents. To create and manage custom taxonomies, you will need to install <a href="http://wordpress.org/extend/plugins/search.php?q=taxonomies" target="_blank">an external plugin</a>. Note that the custom taxonomy value uses the same <a href="http://autoblogged.com/online-help/advanced-usage/post-template-reference/" target="_blank">syntax</a> as post templates.</p></td></tr></table><br/>';
      echo '<table>';
      echo ab_makeValuePairTable(array("Custom Taxonomy", "Custom Taxonomy Value"), ab_unserialize($feed['customtaxonomy']), ab_unserialize($feed['customtaxonomyvalue']), null, $taxonomies);
      echo '</table>'.ab_makeBoxClose();

      echo '</div>';


      echo '<div id="abtabs-4">';
      ///////////////////////////////////////////////
      // Images
      echo ab_makeBoxStart("Images and Video");
      echo '<table><tr valign="top"><td><table>';
      echo '<tr valign="top"><td valign="top"><p>Image Options:&nbsp;&nbsp;&nbsp; </p></td><p>';
      echo '<td>'.ab_makeRadioOnly('saveimages', $feed['saveimages'], array('Do not save images locally', 'Save local copies of all images in the feed', 'Save local copy of first image only'), '');
      echo ab_makeCheckBoxOnly('createthumbs', $feed['createthumbs'], 'Create local thumbnails for each image', 'Note that if you save full-size images locally autoblogged will always create thumbnails.').'</p></td></tr></table></td>';


      // Video
      echo '<td width="75px">&nbsp;</td><td valign="top"><table><tr valign="top"><td valign="top"><p>Embedded Video Player:&nbsp;&nbsp;&nbsp;</p></td><td><table>';
      echo ab_makeTextInput('playerwidth', $feed['playerwidth'], 10, 'Video width', '');
      echo ab_makeTextInput('playerheight', $feed['playerheight'], 10, 'Video height', '');
      echo '</table></td></tr>';
      echo '</table></td></tr>';
      echo '<tr><td colspan="2"><p><a href="'.get_option('siteurl').'/wp-admin/options-media.php">Click here</a> to configure WordPress thumbnail settings.</p></td></tr></table></td>';
      echo ab_makeBoxClose();
      //echo '</div></div></div><br /><br /><div class="wrap"><div id="poststuff"><h2>Advanced Settings</h2>';

      echo '</div>';
      echo '<div id="abtabs-5">';
      ///////////////////////////////////////////////
      // Filtering
      echo ab_makeBoxStart("Filtering", '', true);
      echo '<table><tr><td><p>Include posts that contain (separate words with commas):</p></td></tr></table><br/>';
      echo '<table>';
      echo ab_makeTextInput('includeallwords', $feed['includeallwords'], 70, 'All these words', '');
      echo ab_makeTextInput('includeanywords', $feed['includeanywords'], 70, 'Any of these words', '');
      echo ab_makeTextInput('includephrase', $feed['includephrase'], 70, 'The exact phrase', '');
      echo ab_makeTextInput('includenowords', $feed['includenowords'], 70, 'None of these words', '');
      echo '</table>'.ab_makeBoxClose();

      echo '</div>';
      echo '<div id="abtabs-6">';
      ///////////////////////////////////////////////
      // Custom Fields
      echo ab_makeBoxStart("Custom Fields", '', true);
      echo '<table><tr><td><p>Set these additional custom fields to use in post templates or as custom fields in WordPress. Note that the custom field value uses the same <a href="http://autoblogged.com/online-help/advanced-usage/post-template-reference/" target="_blank">syntax</a> as post templates.</p></td></tr></table><br />';
      echo '<table>';
      echo ab_makeValuePairTable(array("Custom Field", "Custom Field Value"), ab_unserialize($feed['customfield']), ab_unserialize($feed['customfieldvalue']));
      echo '</table>'.ab_makeBoxClose();

      echo '</div>';
      echo '<div id="abtabs-7">';
      ///////////////////////////////////////////////
      // Search and Replace
      echo ab_makeBoxStart("Search and Replace", '', true);
      echo '<table><tr><td><p>Here you can add any strings or regular expressions to search and replace in feed items before adding them as a post. You can use this to change words, replace affiliate IDs, rewrite URLs, fix invalid feeds, etc. Seach items may contain regular expressions and replace items may refer to search matches such as $1, $2, etc.</p></td></tr></table><br/>';
      echo ab_makeValuePairTable(array("Search for", "Replace with"), ab_unserialize($feed['searchfor']), ab_unserialize($feed['replacewith']), true);
      echo '</table>'.ab_makeBoxClose();
      echo '</div>';
      do_action('autoblogged_edit_feeds_page');
   }
   echo '</table></form></div></div></div>';
}

/**
 * Edit feeds page meta sidebar
 *
 * @param  array $feed
 *
 * @return void
 */
function ab_MetaEditFeedSidebar($feed = null) {
   global $ab_options;
   if (function_exists('wp_nonce_url')) $navlink = wp_nonce_url(admin_url().'?page='.$_GET['page'], 'autoblogged-nav');
   echo '<div id="feedsidebar">';
   if (!empty($feed['id']) && ($feed['title'] <> DEFAULTS_TITLE) && $_GET['defaults'] <> '1') {
      $process_now_url = ab_pluginURL().'/run.php?auth='.ab_authKey().'&fid='.$feed['id'].'&show=v&action=manualTB_iframe=true&height=600&width=800';
      echo '<br/><img style="vertical-align: text-bottom;" src="'.ab_pluginURL().'/img/refresh.png" />&nbsp;&nbsp;<a class="feedurl thickbox" href="'.$process_now_url.'">Process this feed now</a><br />';
      $process_now_url = ab_pluginURL().'/run.php?auth='.ab_authKey().'&fid='.$feed['id'].'&show=v&action=previewTB_iframe=true&height=600&width=800';
      echo '<img style="vertical-align: text-bottom;" src="'.ab_pluginURL().'/img/preview.png" />&nbsp;&nbsp;<a class="feedurl thickbox" href="'.$process_now_url.'">Preview this feed now</a><br />';
      echo '<p>&nbsp;</p>';

      if ($feed['enabled']) {
         $action = "disable";
      } else {
         $action = "enable";
      }
      echo '<img style="vertical-align: middle;" src="'.ab_pluginURL().'/img/'.$action.'.png" />&nbsp;&nbsp;<a class="feedurl" href="'.$navlink.'&amp;p=&amp;action='.$action.'&_fid='.$feed['id'].'">'.ucfirst($action).' this feed</a><br />';
   }
   echo '<img style="vertical-align: middle;"  src="'.ab_pluginURL().'/img/delete.png" />&nbsp;&nbsp;<a class="feedurl" href="#" onclick="d(\''.$navlink.'&amp;p=Feeds&_fid='.$feed['id'].'&amp;action=del\')">Delete this feed</a>';
   echo '<p>&nbsp;</p>';

   echo '<p class="curtime">Current server time: <b><br />'.date("r").'</b></p>';
   if ($feed['title'] <> DEFAULTS_TITLE && $_GET['defaults'] <> '1') {
      echo '<p class="curtime">Feed last processed: <b><br />'.date("r", (int)$feed['last_updated']).'</b></p>';
   }
   echo '<p class="curtime">Next scheduled run: <b><br />'.date("r", $ab_options['lastupdate'] + ($ab_options['interval'] * 100)).'</b></p>';
   if ($ab_options['running'] == false) {
      echo '<i>Feed processing is currently <a href="'.wp_nonce_url(admin_url('admin.php').'?page=AutoBloggedSettings&amp;p=Settings', 'autoblogged-nav').'">paused</a></i><br />';
   }
   if (USE_JOURNAL) echo '<br /><img style="vertical-align: text-bottom;" src="'.ab_pluginURL().'/img/history.png" />&nbsp;<a href="'.wp_nonce_url(ab_pluginURL().'/journal.php?height=550&width=1000', 'autoblogged-nav').'" class="thickbox feedurl">View history...</a></b></p><br />';
   echo '<div class="clear"></div><div id="major-publishing-actions">';

   echo '<input type="submit" name="_Submit" id="publish" value="Save Changes" tabindex="4" class="button-primary" />';


   if ($feed['title'] <> DEFAULTS_TITLE && $_GET['defaults'] <> '1') {
      echo '<div style="font-size: .9em; color: #666; margin-left: 8px; margin-top: 15px; padding: 5px; ">';
      echo '<label><input name="_makeDefault" id="_makeDefault" type="checkbox" >&nbsp;Save current settings as defaults for all new feeds</label></div>';
   }
   echo '</div>';
}

/**
 * Options page meta sidebar
 *
 * @return void
 */
function ab_MetaOptionPagesSidebar() {
   echo '<div id="major-publishing-actions">';
   echo '<div class="clear"></div><div id="major-publishing-actions">';
   echo '<input type="submit" name="_Submit" id="publish" value="Save Changes" tabindex="4" class="button-primary" />';
   echo '</div></div>';
}

/**
 * Tag Options admin page
 *
 * @return void
 */
function ab_showTagOptionsPage() {
   global $ab_options;
   ab_adminPageHeader();
   ?>
<script type="text/javascript">

   function enableyahooid() {
      if (document.getElementById('yahootags').checked == false) {
         document.getElementById('yahooappid').disabled = true;

      } else {
         document.getElementById('yahooappid').disabled = false;
      }
   }
</script>
   <?PHP
   echo '<div class="wrap"><h2><a href="http://autoblogged.com" target="_blank"><img style="vertical-align: text-bottom;" src="'.ab_pluginURL().'/img/logo-sm.png"/></a>&nbsp;&nbsp;Tag Options</h2>';
   echo '<div id="poststuff" class="metabox-holder has-right-sidebar">';
   if (array_key_exists('_submit_check', $_POST)) check_admin_referer('autoblogged-tag-options');
   echo '<form action="admin.php?page='.$_GET['page'].'&amp;p=Tag Options" method="post">';
   if (function_exists('wp_nonce_field')) $wpnonce = wp_nonce_field('autoblogged-tag-options');
   echo '<input type="hidden" name="_submit_check" value="1" />'.$wpnonce;
   if (function_exists('wp_nonce_url')) $navlink = wp_nonce_url(admin_url().'?page='.$_GET['page'], 'autoblogged-nav');
   $sidebars = array('Feeds' => 'ab_MetaOptionPagesSidebar', 'Links' => 'ab_MetaLinksSidebar');
   ab_doSideBar($sidebars, 'autoblogged-feeds');
   ab_OpenMainSection();

   // General Settings
   echo ab_makeBoxStart("General Settings");
   echo '<table>';
   echo ab_makeTextInput('mintaglen', $ab_options['mintaglen'], 10, 'Minimum Tag Length', '');
   echo ab_makeTextInput('maxtaglen', $ab_options['maxtaglen'], 10, 'Maximum Tag Length', '');
   echo ab_makeTextInput('maxtags', $ab_options['maxtags'], 10, 'Maximum Tags per Post', '');
   echo '</table>'.ab_makeBoxClose();

   // Tag Sources
   echo ab_makeBoxStart("Tag Sources");
   echo '<table>';
   echo '<tr valign="top"><td><p>Sources for post tags:</p></td><td>';
   echo ab_makeCheckBoxOnly('feedtags', $ab_options['feedtags'], 'Use original tags from feed', '');
   echo ab_makeCheckBoxOnly('taggingengine', $ab_options['taggingengine'], 'Use internal tagging engine to add tags from content', '');
   echo ab_makeCheckBoxOnly('posttags', $ab_options['posttags'], 'Visit source URL to extract additional tags', '');
   echo ab_makeCheckBoxOnly('yahootags', $ab_options['yahootags'], 'Get tags using Yahoo! API (requires Application ID)', '', 'enableyahooid').'</td></tr>';
   echo '<tr><td>&nbsp;</td></tr>';
   echo ab_makeTextInput('yahooappid', $ab_options['yahooappid'], 70, 'Yahoo! Application ID', 'If you don\'t have an application ID, you can get one <a target="_blank" href="http://developer.yahoo.com/wsregapp/index.php">here</a>', '', $ab_options['yahootags']);
   echo '</table>'.ab_makeBoxClose();

   // Tags
   echo ab_makeBoxStart("Tags");
   echo '<table><tr><td><p>AutoBlogged will randomly add one or more of the following tags to each post in this feed:</p></td></tr></table>';
   $tags = $ab_options['tags'];
   if (!is_array($tags)) {
      $tags = ab_unserialize($ab_options['tags']);
   } else {
      $tags = $ab_options['tags'];
   }
   if (!is_array($tags)) {
      $tags = array();
   }
   ?>

<div class="tagsdiv" id="post_tag">
   <div class="jaxtag">
      <textarea name="tags_input" class="the-tags" id="tags_input"
                style="width: 100%"><?PHP echo implode(",", $tags); ?></textarea>
   </div>
   <br/><font size="1" color="SlateGray">Separate tags with commas or enter them one per line.</font>

</div>

   <?php
   echo ab_makeBoxClose();

   // Tag filtering
   echo ab_makeBoxStart("Tag Filtering");
   echo ab_makeWideTextArea('notags', $ab_options['notags'], 5, '', 'Do not use any of the following words as tags.  If you see tags appearing in your posts that you do not want, add those tags here', 'Add one per line or on a single line separated by commas. Note that this will not delete existing tags assigned to posts.').ab_makeBoxClose();
   echo '</table>';

   do_action('autoblogged_tag_options_page');

   echo '</form></div></div></div></div>';
}

/**
 * Settings admin page
 *
 * @return void
 */
function ab_showSettingsPage() {
   global $ab_options;
   ab_adminPageHeader();
   ?>
<script type="text/javascript">
   function enableaffil() {
      if (document.getElementById('addlink').checked == false) {
         document.getElementById('includeaffil').checked = false;
         document.getElementById('includeaffil').disabled = true;
         document.getElementById('lblincludeaffil').style.color = "gray";
      } else {
         document.getElementById('includeaffil').disabled = false;
         document.getElementById('lblincludeaffil').style.color = "black";

      }
   }
</script>
<?PHP

   echo '<div class="wrap"><h2><a href="http://autoblogged.com" target="_blank"><img style="vertical-align: text-bottom;" src="'.ab_pluginURL().'/img/logo-sm.png"/></a>&nbsp;&nbsp;Settings</h2>';
   echo '<div id="poststuff" class="metabox-holder has-right-sidebar">';
   if (array_key_exists('_submit_check', $_POST)) check_admin_referer('autoblogged-settings');
   echo '<form name="settings" action="admin.php?page='.$_GET['page'].'&amp;p=Settings" method="post">';
   if (function_exists('wp_nonce_field')) $wpnonce = wp_nonce_field('autoblogged-settings');
   echo '<input type="hidden" name="_submit_check" value="1" />'.$wpnonce;
   if (function_exists('wp_nonce_url')) $navlink = wp_nonce_url(admin_url().'?page='.$_GET['page'], 'autoblogged-nav');
   $sidebars = array('Settings' => 'ab_MetaOptionPagesSidebar', 'Links' => 'ab_MetaLinksSidebar');
   ab_doSideBar($sidebars, 'autoblogged-feeds');
   ab_OpenMainSection();

   // Registration
   echo ab_makeBoxStart("Registration");
   echo '<table>';
   if ($_GET['action'] == 'doreg') $backcolor = "#FFFFCC";
   echo ab_makeTextInput('sn', $ab_options['sn'], 50, 'Serial Number', '', $backcolor);
   echo ab_makeCheckBox('updatecheck', $ab_options['updatecheck'], 'Updates', 'Automatically check for newer versions of AutoBlogged.<br /><br />', '');
   echo ab_makeCheckBox('addlink', $ab_options['addlink'], 'Include Link', 'Link to AutoBlogged.com in your blog footer.', '', 'enableaffil');
   echo ab_makeCheckBox('includeaffil', $ab_options['includeaffil'], '', 'Include your AutoBlogged affiliate id in link:', '', null, $ab_options['addlink']);
   echo ab_makeTextInput('affilid', $ab_options['affilid'], 10, 'Affiliate ID', 'How to find your <a href="'.wp_nonce_url(ab_pluginURL().'/help.php?&height=430&width=450&t=affil', 'autoblogged-nav').'" class="thickbox">affiliate id.</a>');
   echo '</table>'.ab_makeBoxClose();

   // General Options
   echo ab_makeBoxStart("General Options");
   echo '<table>';
   echo ab_makeCheckBox('running', $ab_options['running'], 'AutoBlogged Enabled', 'Uncheck this if you wish to pause AutoBlogged.', '');
   echo ab_makeTextInput('mintime', $ab_options['mintime'], 10, 'Minimum time between updates', '(minutes)');
   echo ab_makeTextInput('maxtime', $ab_options['maxtime'], 10, 'Maximum time between updates', '(minutes)');
   echo '</table>'.ab_makeBoxClose();

   // WordPress integration
   echo ab_makeBoxStart("WordPress Integration");
   echo '<table>';
   echo ab_makeCheckBox('uselinkinfo', $ab_options['uselinkinfo'], 'WordPress Links', 'Use stored link info if site already appears in links list.', '<br />');
   echo ab_makeCheckBox('useauthorinfo', $ab_options['useauthorinfo'], 'WordPress Authors', 'Use stored author info if author is a registered user.', '');
   echo '</table>'.ab_makeBoxClose();

   // Playing Nice
   echo ab_makeBoxStart("Playing Nice");
   echo '<table>';
   echo ab_makeCheckBox('syndicationsource', $ab_options['syndicationsource'], 'Syndication Source', 'Add the syndication-source linktag in page header to give credit to the original source.', 'Read more about <a href="http://www.google.com/support/news_pub/bin/answer.py?answer=191283" target="_blank">syndication-source</a>');
   echo '<tr><td>&nbsp;</td></tr>';
   //echo ab_makeCheckBox('respectcopyright', $ab_options['respectcopyright'], 'Respect Copyright', 'Skip articles if copyright restricts redistribution', '');
   //echo ab_makeCheckBox('addcopyright', $ab_options['addcopyright'], 'Add Copyright', 'Add original author\'s copyright after post if one exists', '');
   echo ab_makeCheckBox('respectrobots', $ab_options['respectrobots'], 'Respect Meta Tags', 'Skip articles if robots meta tag or googlebot meta tag restricts access', '');
   echo '</table>'.ab_makeBoxClose();

   // HTTP Options
   echo ab_makeBoxStart("HTTP Options");
   echo '<table>';
   echo ab_makeTextInput('referer', $ab_options['referer'], 40, 'HTTP Referrer', 'All requests made to external sites will use this as the <i>referer</i> string');
   echo '<tr><td>&nbsp;</td></tr>';
   echo ab_makeTextInput('useragent', $ab_options['useragent'], 40, 'HTTP User-Agent', 'All requests made to external sites will use this as the <i>User-Agent</i>. <a href="http://www.user-agents.org/">Click here</a> for a list of User-Agent strings.');
   echo '</table>'.ab_makeBoxClose();

   // RSS Options
   echo ab_makeBoxStart("RSS Retrieval Options");
   echo '<table>';
   echo ab_makeTextInput('rss_cache_timeout', $ab_options['rss_cache_timeout'], 10, 'Cache Timeout', 'Cache RSS requests for this many seconds.');
   if (function_exists('wp_nonce_url')) $navlink = wp_nonce_url(admin_url().'?page='.$_GET['page'], 'autoblogged-nav');
   echo '</table>'.ab_makeBoxClose();

   do_action('autoblogged_settings_page');

   echo '</div></form></div></div></div>';
}

/**
 * Filtering admin page
 *
 * @return void
 */
function ab_showFilteringPage() {
   global $ab_options;
   global $blog_id;
   ab_adminPageHeader();
   echo '<div class="wrap"><h2><a href="http://autoblogged.com" target="_blank"><img style="vertical-align: text-bottom;" src="'.ab_pluginURL().'/img/logo-sm.png"/></a>&nbsp;&nbsp;Filtering Options</h2>';
   echo '<div id="poststuff" class="metabox-holder has-right-sidebar">';
   if (array_key_exists('_submit_check', $_POST)) check_admin_referer('autoblogged-filtering');
   echo '<form action="admin.php?page='.$_GET['page'].'&amp;p=Filtering" method="post">';
   if (function_exists('wp_nonce_field')) $wpnonce = wp_nonce_field('autoblogged-filtering');
   echo '<input type="hidden" name="_submit_check" value="1" />'.$wpnonce;
   if (function_exists('wp_nonce_url')) $navlink = wp_nonce_url(admin_url().'?page='.$_GET['page'], 'autoblogged-nav');
   $sidebars = array('Filtering' => 'ab_MetaOptionPagesSidebar', 'Links' => 'ab_MetaLinksSidebar');
   ab_doSideBar($sidebars, 'autoblogged-feeds');
   ab_OpenMainSection();

   // Duplicate Matching
   echo ab_makeBoxStart("Duplicate Posts");
   echo '<table>';
   echo '<tr valign="top"><td><p>Duplicate filtering:&nbsp;&nbsp;&nbsp;</p></td><td>';
   echo ab_makeCheckBoxOnly('filterbytitle', $ab_options['filterbytitle'], 'Match duplicates based on post title', '').'';
   echo ab_makeCheckBoxOnly('filterbylink', $ab_options['filterbylink'], 'Match duplicates based on link', '').'<br />';
   $url = get_option('siteurl').'/wp-admin/plugin-editor.php?file='.ab_dirname().'/'.DUPES_FILENAME.'&plugin='.ab_dirname().'/autoblogged.php';
   echo ab_makeCheckBox('filterbyfile', $ab_options['filterbyfile'], '', 'Match post titles using a local text file', 'Note: <a href="'.$url.'" >Click here</a> to view or edit the posts file.').'</td></tr></table><br />'.ab_makeBoxClose();

   // Title Filtering
   echo ab_makeBoxStart("Title Filtering");
   echo '<table>';
   echo ab_makeTextInput('maxtitlelen', $ab_options['maxtitlelen'], 2, 'Maximum post title length&nbsp;&nbsp;<br />(characters)', '');
   $options = array('Truncate to the nearest word', 'Skip the post');
   echo '<tr valign="top"><td><p>When title is too long:</p></td><td>';
   echo ab_makeRadioOnly('longtitlehandling', $ab_options['longtitlehandling'], $options, '');
   echo '<tr><td>&nbsp;</td></tr>';
   echo ab_makeCheckBox('skipcaps', $ab_options['skipcaps'], 'Title filtering', 'Skip titles in all caps', '');
   echo ab_makeCheckBox('skipmultiplepunctuation', $ab_options['skipmultiplepunctuation'], '', 'Skip titles with multiple consecutive punctuation marks', 'Skip titles such as "What is this?!" or "Amazing!!!!"');
   echo '</td></tr></table>'.ab_makeBoxClose();

   // Other Filtering
   echo ab_makeBoxStart("Other Filters");
   echo '<table>';
   echo '<tr valign="top"><td>';
   echo ab_makeCheckBox('skipemptylinks', $ab_options['skipemptylinks'], '', 'Skip posts with empty links', '');
   echo ab_makeCheckBox('allowdomain', $ab_options['allowdomain'], '', 'Allow posts from your own domain', '');

   //echo ab_makeCheckBox('filterchars', $ab_options['filterchars'], '', 'Remove all non-latin characters', '');
   echo '</td></tr></table>'.ab_makeBoxClose();

   // Blacklists
   echo ab_makeBoxStart("Blacklists");
   echo '<table>';
   echo ab_makeHalfWidthTextArea('domains_blacklist', $ab_options['domains_blacklist'], 10, 'URL Blacklist', 'Reject posts from any of the domains or URL sequences listed here. <br />Add one per line or on a single line separated by commas.');
   echo ab_makeHalfWidthTextArea('keywords_blacklist', $ab_options['keywords_blacklist'], 10, 'Keywords Blacklist', 'Reject posts that contain any of these keywords. <br />Add one per line or on a single line separated by commas.');
   echo '</table>';

   do_action('autoblogged_filtering_page');

   echo '</form>'.ab_makeBoxClose().'</div></div></div></div>';
}

/**
 * Support admin page
 *
 * @return void
 */
function ab_showSupportPage() {
   global $ab_options, $wpdb, $wp_version, $feedtypes;
   ab_adminPageHeader();

   // Handle forced DB upgrade
   if ($_GET['upgrade_db'] == '1') {
      update_option("autoblogged_db_version", '');
      ab_installOnActivation(true);
   }

   // Handle support request e-mail
   if ($_POST['SubmitMessage'] == 'Submit') {
      check_admin_referer('autoblogged-support');

      // Build subject
      $subject = $_POST['su'];
      if (isset($_POST['ti'])) {
         $subject .= ' (#'.$_POST['ti'].')';
      }
      if (empty($_POST['msg'])) {
         echo '<div id="message" class="updated fade"><p>Error: Message field is empty, e-mail message was NOT sent.</p></div>';
      } else {
         $email = AB_SUPPORT_EMAIL;
         $name = $_POST['na'];
         $message = str_replace('&#13', chr(13), $_POST['msg']);
         $message = str_replace('&#10', chr(10), $message);
         $message = str_replace('&#39', chr(39), $message);
         $message = html_entity_decode($message);
         $message_headers = 'From: "'.$name.'"<'.$_POST['em'].'>';
         if (function_exists('filter_var')) $name = filter_var($name, FILTER_SANITIZE_STRING);
         $message = filter_var($message, FILTER_SANITIZE_SPECIAL_CHARS);

         if (intval(isset($_POST['attachinfo']))) {
            $message .= "\n\n\n".'---SYSTEM CONFIGURATION---';
            if (intval(isset($_POST['abver']))) $message .= "\nAutoBlogged Version: ".AB_VERSION;
            if (intval(isset($_POST['absernum']))) $message .= "\nSerial Number: ".$ab_options['sn'];
            if (intval(isset($_POST['blogurl']))) $message .= "\nBlog URL: ".@site_url();
            if (intval(isset($_POST['phpver']))) $message .= "\nPHP Version: ".@phpversion();
            if (intval(isset($_POST['wpver']))) $message .= "\nWordPress Version: ".$wp_version;
            if (intval(isset($_POST['myver']))) $message .= "\nMySQL Version: ".mysql_get_server_info();
            if (intval(isset($_POST['server']))) $message .= "\nWeb Server: ".@getenv('SERVER_SOFTWARE');
            if (intval(isset($_POST['wptheme']))) $message .= "\nWordPress Theme: ".@get_current_theme();
            if (intval(isset($_POST['plugins']))) {
               $plugins = get_plugins();
               $message .= "\nActive Plugins:\n";
               foreach ($plugins as $plugin) {
                  if ($plugin['Name'] <> 'AutoBlogged') {
                     $message .= $plugin['Name'].' v'.$plugin['Version']."\n";
                  }
               }
            }

            if (intval(isset($_POST['abfeeds']))) {
               $message .= "\nFeeds:\n";
               $sql = "SELECT id, title, type, url, LCASE('title') as sort_title, enabled FROM ".ab_tableName().' ORDER BY sort_title;';
               $feeds = $wpdb->get_results($sql, 'ARRAY_A');
               if (sizeof($feeds) > 0) {
                  foreach ($feeds as $feed) {
                     $message .= $feedtypes[$feed['type']].' - '.$feed['url']."\n";
                  }
               } else {
                  $message .= "(none)\n";
               }
            }
         }
         //krumo(array($email, subject, $message, $message_headers));
         // Attach the configuration if necessary
         if (intval(isset($_POST['attachconfig']))) {
            $attach = '---AUTOBLOGGED CONFIGURATION---';
            $attach .= "\n\n".var_export($ab_options, true);
            $attach .= "\n\n\n".'---FEEDS CONFIGURATION---';
            $sql = "SELECT * FROM ".ab_tableName();
            $feeds = $wpdb->get_results($sql, 'ARRAY_A');
            $attach .= "\n\n".var_export($feeds, true);
         }

         $configfile = dirname(__FILE__).'/sendconfig.txt';
         $cfile = @fopen($configfile, "w"); // Create blog config text file to send to support if checked
         if (@fwrite($cfile, $attach) === false) { // Save blog config to a file to send to support if checked

            //If we can't write to the file, just append it to the email body
            $message .= "\n\n\n".$attach;
            $mres = wp_mail($email, $subject, $message, $message_headers);
         } else {
            $mres = wp_mail($email, $subject, $message, $message_headers, $configfile);
         }
         @fclose($cfile);
         @unlink($configfile);
         if ($mres) {
            echo '<div id="message" class="updated fade"><p>Support message sent. You should receive an automated response in the next few minutes. If you do not get a response, we may have not properly received the message.</p></div>';
         } else {
            echo '<div id="message" class="updated fade"><p>Error: Message could not be sent. Please visit <a href="http://support.autoblogged.com">support.autoblogged.com</a></p></div>';
         }
      }
   }

   echo '<form action="admin.php?page='.$_GET['page'].'&amp;p=Support" method="post">';
   if (function_exists('wp_nonce_field')) $wpnonce = wp_nonce_field('autoblogged-tag-options');
   echo '<input type="hidden" name="_submit_check" value="1" />'.$wpnonce;
   echo '<div class="wrap"><h2><a href="http://autoblogged.com" target="_blank"><img style="vertical-align: text-bottom;" src="'.ab_pluginURL().'/img/logo-sm.png"/></a>&nbsp;&nbsp;Technical Support</h2>';
   echo '<div id="poststuff"><div class="post-body">';
   echo ab_makeBoxStart('Diagnostic tools');
   echo '<p>&nbsp;&nbsp;<a href="'.wp_nonce_url(ab_pluginURL().'/diag.php?height=550&width=650', 'autoblogged-nav').'" class="thickbox"><img style="vertical-align: text-bottom;" src="'.ab_pluginURL().'/img/info.png" />&nbsp;View AutoBlogged diagnostics...</a></p>';
   echo '<p>&nbsp;&nbsp;<a href="'.wp_nonce_url(ab_pluginURL().'/diag.php?type=info&height=550&width=650', 'autoblogged-nav').'" class="thickbox"><img style="vertical-align: text-bottom;" src="'.ab_pluginURL().'/img/php.png" />&nbsp;View PHPInfo...</a></p>';
   echo '<p>&nbsp;&nbsp;<a href="'.wp_nonce_url(ab_pluginURL().'//diag.php?type=time&height=550&width=650', 'autoblogged-nav').'" class="thickbox"><img style="vertical-align: text-bottom;" src="'.ab_pluginURL().'/img/time.png" />&nbsp;View Time Settings...</a></p>';
   echo '<p>&nbsp;&nbsp;<a href="'.wp_nonce_url(ab_pluginURL().'/journal.php?height=550&width=1000', 'autoblogged-nav').'" class="thickbox"><img style="vertical-align: text-bottom;" src="'.ab_pluginURL().'/img/history.png" />&nbsp;View AutoBlogged Processing History...</a><br/><br/></p>';

   $error_log = ini_get('error_log');
   $msg = '<a href="'.wp_nonce_url(ab_pluginURL().'/view.php?f=php&height=550&width=850', 'autoblogged-nav').'" class="thickbox">View error log ('.ab_fileSize($error_log).')</a>';
   echo '<p>PHP server log is located at: <code>'.ini_get('error_log').'</code>&nbsp;&nbsp;'.$msg.'</p><br/>';

   if (file_exists(dirname(__FILE__).'/_debug.log')) {
      $msg = '<a href="'.wp_nonce_url(ab_pluginURL().'/view.php?f=debug&height=550&width=850', 'autoblogged-nav').'" class="thickbox">View log ('.ab_fileSize(dirname(__FILE__).'/_debug.log').')</a>';
   } else {
      $msg = '';
   }
   echo ab_makeCheckBox('logging', $ab_options['logging'], '', 'Enable logging to a file when processing feeds.&nbsp;&nbsp;'.$msg, 'This will create a file named _debug.log in the AutoBlogged directory to help troubleshoot problems.');
   echo '<br /><br />';
   echo ab_makeCheckBox('showdebug', $ab_options['showdebug'], '', 'Show verbose debug info.', 'This will include verbose debug messages in the _debug.log file or display them on web pages if you are logged in as a site administrator.');
   echo '<br /><br /><input name="Submit" type="submit" value="Save" />';
   //echo '<br /><br /><br />If the AutoBlogged tables were not properly created upon installation, you may manually <a href="admin.php?page='.$_GET['page'].'&amp;upgrade_db=1">create them now</a><br/>';
   echo ab_makeBoxClose();

   do_action('autoblogged_support_page');

   echo ab_makeBoxStart('Submit Support Request');
   echo 'Please search our  <a href="http://community.autoblogged.com/forums/275308-kb-articles" target="_blank">knowledge base</a> or <a href="http://community.autoblogged.com/forums" target="_blank">forums</a> before sending a support e-mail. To get direct support, send an e-mail to <a href="'.AB_SUPPORT_EMAIL.'">'.AB_SUPPORT_EMAIL.'</a> or use the form below.<br /><br />';
   echo '<form action="admin.php?page='.$_GET['page'].'&amp;p=Support&action=sendmsg" method="post">';
   echo 'Your Name:<br /><input name="na" type="text" style="width: 250px;"/><br /><br />';
   echo 'Your E-Mail Address:<br /><input name="em" type="text" style="width: 250px;" value="'.get_option('admin_email').'" /><br /><br />';
   echo 'Subject:<br /><input name="su" type="text" style="width: 400px;"/><br /><br />';
   echo 'If this is related to an open support ticket, enter that number here: <input name="ti" type="text" style="width: 75px;"/><br /><br />';
   echo 'Message:<br />';
   echo '<textarea name="msg" style="width: 700px; height: 163px"></textarea><br/>';
   echo '<input name="attachinfo" type="checkbox" checked="checked" value="1" />Attach system info&nbsp;&nbsp;<a href="#TB_inline?height=700&width=600&inlineId=settingsConfig" class="thickbox">View/Configure...</a>&nbsp;&nbsp;';
   echo '<br /><br /><input name="SubmitMessage" type="submit" value="Submit" />';
   // Advanced system info configuration
   echo '<div id="settingsConfig" style="visibility: hidden;"><p>Please uncheck any items you do not want sent:</p><p>';
   echo '<table><tr>';
   echo '<tr><td valign="top"><input type="checkbox" name="abver" id="abver" checked="checked" /> <label for="abver">AutoBlogged Ver:</label></td>';
   echo '<td class="asetting">'.AB_VERSION.'</td></tr>';

   echo '<tr><td valign="top"><input type="checkbox" name="absernum" id="absernum" checked="checked" /> <label for="absernum">AutoBlogged SN:</label></td>';
   echo '<td class="asetting">'.$ab_options['sn'].'</td></tr>';

   echo '<tr><td valign="top"><input type="checkbox" name="blogurl" id="blogurl" checked="checked" /> <label for="blogurl">Blog URL:</label></td>';
   echo '<td class="asetting">'.@site_url().'</td></tr>';

   echo '<tr><td valign="top"><input type="checkbox" name="phpver" id="phpver" checked="checked" /> <label for="phpver">PHP Version:</label></td>';
   echo '<td class="asetting">'.@phpversion().'</td></tr>';

   echo '<tr><td valign="top"><input type="checkbox" name="wpver" id="wpver" checked="checked" /> <label for="wpver">WordPress Version:</label></td>';
   echo '<td class="asetting">'.$wp_version.'</td></tr>';

   echo '<tr><td valign="top"><input type="checkbox" name="myver" id="myver" checked="checked" /> <label for="myver">MySQL Version:</label></td>';
   echo '<td class="asetting">'.@mysql_get_server_info().'</td></tr>';

   echo '<tr><td valign="top"><input type="checkbox" name="server" id="server" checked="checked" /> <label for="server">Web Server:</label></td>';
   echo '<td class="asetting">'.@getenv('SERVER_SOFTWARE').'</td></tr>';

   echo '<tr><td valign="top"><input type="checkbox" name="platform" id="platform" checked="checked" /> <label for="platform">Platform:</label></td>';
   echo '<td class="asetting">'.@php_uname('s').'<br />'.@php_uname('r').' '.@php_uname('v').'</td></tr>';

   echo '<tr><td valign="top"><input type="checkbox" name="wptheme" id="wptheme" checked="checked" /> <label for="wptheme">WordPress Theme:</label></td>';
   echo '<td class="asetting">'.@get_current_theme().'</td></tr>';

   echo '<tr><td valign="top"><input type="checkbox" name="plugins" id="plugins" checked="checked" /> <label for="plugins">Active Plugins:</label></td>';
   echo '<td class="asetting">';
   $plugins = get_plugins();
   foreach ($plugins as $plugin) {
      if ($plugin['Name'] <> 'AutoBlogged') {
         echo $plugin['Name'].' v'.$plugin['Version'].'<br />';
      }
   }
   echo '</td></tr>';

   echo '<tr><td valign="top"><input type="checkbox" name="abfeeds" id="abfeeds" checked="checked" /> <label for="abfeeds">AutoBlogged Feeds:&nbsp;&nbsp;</label></td>';
   echo '<td class="asetting">';
   $sql = "SELECT id, title, type, url, LCASE('title') as sort_title, enabled FROM ".ab_tableName().' ORDER BY sort_title;';
   $feeds = $wpdb->get_results($sql, 'ARRAY_A');
   if (sizeof($feeds) > 0) {
      foreach ($feeds as $feed) {
         echo str_replace(chr(13), '<br />', $feed['url']).'<br />';
      }
   } else {
      echo '(none)';
   }
   echo '</td></tr>';


   echo '<tr><td valign="top" colspan="2"><input type="checkbox" name="attachconfig" id="attachconfig"   /> <label for="attachconfig">Attach Entire AutoBlogged Config</label></td>';
   echo '</tr></table></p></div>';

   if (function_exists('wp_nonce_field')) $wpnonce = wp_nonce_field('autoblogged-support');
   Echo '<input type="hidden" name="_submit_check" value="1" />'.$wpnonce;
   echo '</form>';
   echo '<br /><br /></div></div>';
   echo ab_makeBoxClose();


   echo '<br /><br />';
}

// Show the sidebar links

/**
 * Short description for function
 *
 * @return void
 */
function ab_MetaLinksSidebar() {

   $url = ab_pluginURL();

   $html = '<ul><li><img src="'.$url.'/img/twitter.png" />&nbsp;<a href="http://twitter.com/AutoBlogged" target="_blank">Follow us on Twitter</a></li>';
   $html .= '<li><img src="'.$url.'/img/facebook.png" />&nbsp;<a href="http://facebook.com/AutoBlogged" target="_blank">Like AutoBlogged on Facebook</a></li>';
   $html .= '<li><img src="'.$url.'/img/email_add.png" />&nbsp;<a class="thickbox" href="http://eepurl.com/diNu2?TB_iframe=true&height=475&width=530" target="_blank">Subscribe to our mailing list</a></li></ul>';

   $links = array("Online Documentation" => "http://autoblogged.com/online-help/", "Support Forums" => "http://community.autoblogged.com/forums", "AutoBlogged Website" => "http://autoblogged.com", "Become an Affiliate" => "http://autoblogged.com/affiliate-program/");
   $feeds = array("Announcements Feed" => "http://community.autoblogged.com/forums/6305-announcements/posts.rss", "Latest Forum Posts" => "http://community.autoblogged.com/forums/6310-technical-support/posts.rss");

   $html .= '<ul>';
   foreach (array_keys($links) as $link) {
      $html .= '<li><a href="'.$links[$link].'" target="_blank">'.$link.'</a></li>';
   }
   $html .= '</ul><ul>';


   foreach (array_keys($feeds) as $feed) {
      $html .= '<li><img src="'.$url.'/img/rss.png" />&nbsp;&nbsp;<a href="'.$feeds[$feed].'" target="_blank">'.$feed.'</a></li>';
   }
   $html .= '</ul><br /><br /><br />';
   echo $html;
   echo '<br />';
}

/**
 * Common opening function for boxes
 *
 * @param  string  $title
 * @param  string  $div
 * @param  boolean $closed
 *
 * @return string
 */
function ab_makeBoxStart($title, $div = '', $closed = false) {
   if (empty($div)) $div = str_replace(' ', '', $title).'div';

   $html = '<div id="'.$div.'" class="absetting postbox "';
   if ($closed) $html .= 'if-js-closed';
   $html .= '"><h3>'.$title.'</h3><div class="inside">';
   $html .= "\r\n";
   return $html;
}

/**
 * Common closing function for boxes
 *
 * @return string
 */
function ab_makeBoxClose() {
   $html = '</div></div>';
   return $html;
}

/**
 * Common function for checkboxes in a table
 *
 * @param  string  $field
 * @param  boolean $val
 * @param  string  $title
 * @param  string  $label
 * @param  string  $help
 * @param  string  $onclick
 * @param  boolean $enabled
 *
 * @return string  Returns html
 */
function ab_makeCheckBox($field, $val, $title, $label, $help = '', $onclick = null, $enabled = true) {
   if (strlen($title)) $title .= ': ';
   $html = '<tr valign="top"><td scope="row">'.$title.'</td><td><label for="'.$field.'" id="lbl'.$field.'" ';
   if (!$enabled) {
      $html .= 'style="color: gray" ';
   }
   $html .= '>';
   $html .= '<input name="'.$field.'" id="'.$field.'" type="checkbox" ';

   // Initial checked value
   if ($val == true) {
      $html .= 'checked="checked" value="checked" ';
   }

   // Set onclick function
   if (isset($onclick)) {
      $html .= 'onclick="'.$onclick.'()"';
   }

   // Initial enabled state
   if (!$enabled) {
      $html .= 'disabled';
   }
   $html .= '/> '.$label."</label>";
   if (!empty($help)) $html .= '<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<font size="1" color="SlateGray">'.$help.'</font>';
   $html .= "</td></tr>\r\n";
   return $html;
}

/**
 * Common functions for simple checkboxes
 *
 * @param  string  $field
 * @param  boolean $val
 * @param  string  $label
 * @param  string    $help
 * @param  string  $onclick
 *
 * @return string  Returns html
 */
function ab_makeCheckBoxOnly($field, $val, $label, $help, $onclick = null) {
   $html = '<p><label for="'.$field.'"><input name="'.$field.'" id="'.$field.'" type="checkbox" ';
   if ($val == true) {
      $html .= 'checked="checked" value="checked"';
   }
   if (isset($onclick)) {
      $html .= 'onclick="'.$onclick.'()"';
   }
   $html .= '/> '.$label.'</label></p>';
   return $html;
}

/**
 * Common function for radio buttons
 * $label is , $val is the index of the selected option
 *
 * @param  string  $field
 * @param  string  $val    the index of the selected option
 * @param  array   $label  an array of options
 * @param  string  $help
 *
 * @return string  Returns html
 */
function ab_makeRadioOnly($field, $val, $label, $help) {
   if (is_array($label)) {
      $i = 0;
      $html = '';
      foreach ($label as $itemlabel) {
         $html .= '<p><label for="'.$field.$i.'"><input name="'.$field.'" id="'.$field.$i.'" type="radio" ';
         if ($val == $i) {
            $html .= 'checked value="'.$i.'"';
         } else {
            $html .= 'value="'.$i.'"';
         }
         $html .= '/> '.$itemlabel.'</label></p>';
         $i++;
      }
      return $html;
   } else {
      // Why use a radio if there's only one option?

   }
}

/**
 * Common for making select lists
 *
 * @param  string  $field
 * @param  array   $values
 * @param  string  $selected
 * @param  string  $title
 * @param  string  $help
 * @param  boolean $allowblank
 * @param  string  $onchange
 * @param  boolean $enabled
 *
 * @return string  Returns html
 */
function ab_makeSelect($field, $values, $selected, $title, $help = null, $allowblank = true, $onchange = null, $enabled = true) {
   if (empty($values)) return null;
   $html = '<tr><td scope="row"><p id="lbl'.$field.'" ';
   if (!$enabled) {
      $html .= 'style="color:gray" ';
   }
   $html .= '>'.$title.':</p></td><td><p><select name="'.$field.'" id="'.$field.'" ';
   if (isset($onchange)) {
      $html .= 'onChange="'.$onchange.'()" ';
   }
   if (!$enabled) {
      $html .= 'disabled ';
   }
   $html .= '>';
   if ($allowblank) echo '<option></option>';
   foreach ($values as $value) {
      if (is_array($value)) {
         $label = $value[0];
         $val = $value[1];
      } else {
         $label = $value;
         $val = null;
      }
      $html .= '<option';

      if (strcasecmp($val, $selected) == 0 || strcasecmp($label, $selected) == 0) {
         $html .= ' selected="selected"';
      }
      if (!empty($val)) {
         $html .= ' value = "'.$val.'"';
      }
      $html .= '>'.stripslashes(esc_attr($label)).'</option>';
   }
   $html .= "</select>";
   if (!empty($help)) {
      $html .= '<br /><font size="1" color="SlateGray">'.$help.'</font>';
   }
   $html .= "</p></td></tr>\r\n";
   return $html;
}

/**
 * Common function for text input boxes
 *
 * @param  string  $field
 * @param  string  $value
 * @param  string  $defaultWidth
 * @param  string  $title
 * @param  string  $help
 * @param  string  $backcolor
 * @param  boolean $enabled
 *
 * @return string  Returns html
 */
function ab_makeTextInput($field, $value, $defaultWidth, $title, $help, $backcolor = '', $enabled = true) {
   $html = '<tr><td scope="row"><p id="lbl'.$field.'">'.$title.': </p></td><td><input class="abtext" ';
   if (strlen($backcolor)) $html .= 'style="background-color: '.$backcolor.';" ';
   $html .= 'name="'.$field.'" id="'.$field.'" value="'.stripslashes(esc_attr($value)).'"';
   if (!empty($defaultWidth)) {
      $html .= ' size="'.$defaultWidth.'"';
   }
   if (!$enabled) {
      $html .= ' disabled';
   }
   $html .= ' />';
   if (!empty($help)) {
      $html .= '<br /><font size="1" color="SlateGray">'.$help.'</font>';
   }
   $html .= "</td></tr>\r\n";
   return $html;
}

/**
 * Common function for text areas
 *
 * @param  string  $field
 * @param  string    $value
 * @param  string  $rows
 * @param  string    $title
 * @param  string  $caption
 * @param  string  $help
 *
 * @return string  Returns html
 */
function ab_makeWideTextArea($field, $value, $rows, $title, $caption, $help = '') {

   //$html = '<h3>'.$title.'</h3><table>';
   $html = '<table width="90%">';
   $html .= '<tr valign="top"><td><p>'.$caption.'</p></td></tr><tr><td><textarea name="'.$field.'" rows="'.$rows.'" class="abtext" style="width: 100%">'.stripslashes(esc_attr($value)).'</textarea>';
   if (!empty($help)) {
      $html .= '<br /><font size="1" color="SlateGray">'.$help.'</font>';
   }
   $html .= "</td></tr>\r\n</table>";
   return $html;
}

/**
 * Common function for half-width text areas
 *
 * @param  string  $field
 * @param  string    $value
 * @param  string  $rows
 * @param  string  $title
 * @param  string  $help
 *
 * @return string  Returns html
 */
function ab_makeHalfWidthTextArea($field, $value, $rows, $title, $help) {
   $html = '<tr valign="top"><td scope="row"><p>'.$title.'</p></td><td><textarea name="'.$field.'" rows="'.$rows.'" class="abtext" style="width: 50%">'.stripslashes(esc_attr($value)).'</textarea>';
   if (!empty($help)) {
      $html .= '<br /><font size="1" color="SlateGray">'.$help.'</font>';
   }
   $html .= "</td></tr>\r\n";
   return $html;
}

/**
 * Common function for value pair tables
 *
 * @param  array   $headings
 * @param  array   $colOneItems
 * @param  array   $colTwoItems
 * @param  boolean $sar_hack
 * @param  array   $taxonomies
 *
 * @return string  Returs html
 */
function ab_makeValuePairTable($headings, $colOneItems, $colTwoItems, $sar_hack = false, $taxonomies = null) {
   $name = strtolower(str_replace(" ", "", $headings[0]));

   // Search/replace v2.7 hack to avoid a table def update
   if ($sar_hack) {
      $headings[] = 'Search Type';
   }
   $html = '<table id="tbl'.$name.'"><tr><td>&nbsp;</td>';
   foreach ($headings as $heading) {
      $html .= '<td><b>'.$heading.'</b></td>';
   }
   $html .= '<td>&nbsp;</td></tr>';
   $i = 0;
   if (is_array($colOneItems)) {
      foreach ($colOneItems as $item) {
         if (!empty($colOneItems[$i])) {

            // Search/replace hack, the type is prepended to col 1
            $ret = preg_match("/!!(\d)!!/", $colOneItems[$i], $matches);
            if ($ret && $sar_hack) {
               $colthree = $matches[1];
               $colOne = str_replace($matches[0], '', $colOneItems[$i]);
            } else {
               $colOne = $colOneItems[$i];
               $colthree = 0;
            }

            // Create the html
            $html .= '<tr><td><input type="checkbox" name="chk'.$name.'" /></td>';

            // Special case for custom taxonomies, create combo box
            if (isset($taxonomies)) {
               $html .= '<td><select name="'.$name.'[]" style="width: 150px">';
               $html .= '<option></option>';
               foreach ($taxonomies as $taxonomy) {
                  $html .= '<option';
                  if (strcasecmp($taxonomy, esc_attr($colOne)) == 0) {
                     $html .= ' selected="selected"';
                  }
                  $html .= '>'.stripslashes(esc_attr($taxonomy)).'</option>';
               }
               $html .= '</select></td>';
            } else {
               $html .= '<td><input class="abtext" name="'.strtolower(str_replace(" ", "", $headings[0])).'[]" value="'.stripslashes(htmlentities($colOne)).'" size="40"></td>';
            }
            $html .= '<td><input class="abtext" name="'.strtolower(str_replace(" ", "", $headings[1])).'[]" value="'.stripslashes(htmlentities($colTwoItems[$i])).'" size="50"></td>';

            // More hackiness
            if ($sar_hack) {
               if ($colthree == 1) {
                  $html .= '<td><select name="searchtype[]"><option>Regular Expression</option><option selected="selected">Plain Text</option></select></td>';
               } elseif ($colthree == 0) {
                  $html .= '<td><select name="searchtype[]"><option selected="selected">Regular Expression</option><option>Plain Text</option></select></td>';
               }
            }
            $html .= '</tr>';
            $i++;
         }
      }
   }

   // Add a blank line

   // Special case for custom taxonomies, create combo box
   $bhtml = '<td><input class="abtext" type="checkbox" name="chk'.$name.'" /></td>';
   if (isset($taxonomies)) {
      $bhtml .= '<td><select name="'.strtolower(str_replace(" ", "", $headings[0])).'[]" style="width: 150px">';
      $bhtml .= '<option></option>';
      foreach ($taxonomies as $taxonomy) {
         $bhtml .= '<option>'.stripslashes($taxonomy).'</option>';
      }
      $bhtml .= '</select></td>';
      ;
   } else {
      $bhtml .= '<td><input class="abtext" name="'.strtolower(str_replace(" ", "", $headings[0])).'[]" size="40" value=""></td>';
   }
   $bhtml .= '<td><input class="abtext" name="'.strtolower(str_replace(" ", "", $headings[1])).'[]" size="50" value=""></td>';
   if ($sar_hack) {
      $bhtml .= '<td><select name="searchtype[]"><option selected value=""></option><option>Regular Expression</option><option>Plain Text</option></select><br />';
   }
   $html .= '<tr>'.$bhtml.'</tr><tr>'.$bhtml.'</tr><tr>'.$bhtml.'</tr>';
   $html .= "</table>\r\n";

   // Create function for adding a blank line
   echo '<script language="javascript" type="text/javascript">';
   echo 'function add'.$name.'() { ';
   echo 'var table=document.getElementById(\'tbl'.$name.'\');';
   echo 'var rc=table.rows.length;';
   echo 'var row=table.insertRow(rc);';
   echo 'row.innerHTML=\''.$bhtml.'\';';
   echo '}';

   // Create function to remove line
   echo 'function del'.$name.'() { ';
   echo 'var table=document.getElementById(\'tbl'.$name.'\');';
   echo 'var rc=table.rows.length;';
   echo 'for(var i=0; i<rc; i++) {';
   echo 'var row = table.rows[i];';
   echo 'var chk = row.cells[0].childNodes[0];';
   echo 'if(null != chk && true == chk.checked) { ';
   echo 'table.deleteRow(i);';
   echo 'rc--;';
   echo 'i--; } }';
   echo '}</script>';
   $html .= '<input type="button" onClick="del'.$name.'()" value="Delete checked">&nbsp;&nbsp;'; //<input type="button" onClick="add'.$name.'()" value="Add new line">';
   return $html;
}

/**
 *
 * @param  array   $sidebars    An array of titles as keys, functions as values
 * @param  string  $page
 * @param  string  $feed
 *
 * @return void
 */
function ab_doSideBar($sidebars, $page, $feed = '') {
   echo '<div class="submitbox" id="submitlink">';
   foreach (array_keys($sidebars) as $sidebar) {
      add_meta_box(str_replace(' ', '', $sidebar).'div', __($sidebar), $sidebars[$sidebar], $page, 'side', 'core');
   }
   echo '<div id="side-info-column" class="inner-sidebar">';
   $side_meta_boxes = do_meta_boxes($page, 'side', $feed);
   echo '</div></div>';
}

/**
 * @return void
 */
function ab_OpenMainSection() {
   echo '<div id="post-body" class="has-sidebar">';
   echo '<div id="post-body-content" class="has-sidebar-content">';
}
