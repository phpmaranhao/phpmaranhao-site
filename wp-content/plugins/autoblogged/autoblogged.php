<?php

/*
Plugin Name: AutoBlogged
Plugin URI: http://www.autoblogged.com
Description: AutoBlogged automatically creates WordPress posts and builds fresh, targeted content from RSS or ATOM feeds, blog searches, and other sources.
Author: Autoblogged.com (support@autoblogged.com)
Author URI: http://www.autoblogged.com
Version: 2.9.1
License: Commercial and/or GPLv2
*/
 
/**
 * AutoBlogged automatically creates WordPress posts and builds fresh, targeted content from RSS or ATOM feeds, blog searches, and other sources.
 *
 * PHP version 5
 *
 * @package   AutoBlogged v2
 * @author    AutoBlogged <support@autoblogged.com>
 * @copyright Copyright (c)2008 AutoBlogged.com
 * @version   SVN: $Id:$
 */

define("AB_VERSION", "2.9.1");
define("DB_SCHEMA_AB_VERSION", "1.9.28");
define("AB_BETA", false);

/***************************************************************************
 * Below are additional options not available on the admin page that you can
 * customize here
 *
 * Note that if you make any changes below you must make these same changes
 * every time you update AutoBlogged.
 *
 ****************************************************************************/

/*
* Only change one of the following unless you have file dupechecking enabled:
*/
define("DUPECHECK_WP_API", true); // Use the WordPress API for dupechecking (fastest but can create dupes on busy servers)
define("DUPECHECK_DB_QUERY", true); // More accurate but slower

/**
 * Only dupecheck titles based on the first x characters specified here
 */
define("DUPECHECK_FILE_TITLE_LENGTH", 45);

/**
 * High limit that will trigger cleanup of posts.txt, removing 25% of the oldest posts.
 */
define("MAX_DUPESFILE_POSTS", 500);

/**
 * Filename used for local file dupe checking
 */
define("DUPES_FILENAME", 'posts.txt');

/**
 * Include all image fields, appending the number to each variable, i.e. %image_1%, %image2%, etc.
 */
define("EXTRA_IMAGE_FIELDS", false);

/**
 *
 */
define("ALWAYS_ATTACH_IMAGES", true);

/**
 * The delimiter to use between words in tags
 */
define("TAGS_DELIMITER", "-");

/**
 * Always rebuild sitemaps after processing feeds
 */
define("REBUILD_SITEMAPS", true);

/* The following variables set the weight given to different post elements
*  e.g., a value of 10 would be the same as that word/phrase appearing 10 extra
*  times in the article context. A value of 0 means no extra weight given (which
*  will speed up processing).
*/

define("META_KEYWORDS_WEIGHT", "15");
define("H1_WEIGHT", "12");
define("H2_WEIGHT", "10");
define("H3_WEIGHT", "6");
define("REL_TAGS_WEIGHT", "10"); // gives bonus to the last word in the link url if rel="tag" is set
define("LINK_TEXT_WEIGHT", "4");
define("ALT_TAGS_WEIGHT", "3");
define("URL_TAGS_WEIGHT", "3"); // gives bonus to words in the url that follow "tag", "category", or "wiki" even if rel="tag" isn't set
define("LINK_TITLE_WEIGHT", "3");
define("BOLD_WORD_WEIGHT", "10");
define("TAGS_TXT_WEIGHT", "25"); // Bonus for words found in tags.txt
define("YAHOO_TAGS_WEIGHT", "3");

/*
* SimplePie by default filters out certain HTML tags for security purposes. You can override this by changing
* the following settings. Use these settings with caution.
*/

define("ALLOW_OBJECT_AND_EMBED_TAGS", true); // Allows object, embed, param
define("ALLOW_FORM_TAGS", true); // Allows form, input
define("ALLOW_FRAME_TAGS", true); // Allows frame, iframe, frameset
define("ALLOW_SCRIPT_TAGS", true); // Allows class, expr, script, noscript, onclick, onerror, onfinish, onmouseover, onmouseout, onfocus, onblur
define("ALLOW_ALL_TAGS", true); // This turns off all HTML tag and attribute filtering.

define("ENCODE_INSTEAD_OF_STRIP", false); // Set to true if you want HTML tags encoded rather than stripped out

/**
 * If SimplePie doesn't recognize a malformed feed, set FORCE_FEED to true to force processing anyway
 */
define("FORCE_FEED", false);

// Control the use and number of journal entries

/**
 * Allows AutoBlogged to create a history log of all feeds processed.
 */
define('USE_JOURNAL', true);

/**
 * Maximum number of entries in the journal log
 */
define('MAX_JOURNAL_ENTRIES', 100);

/**
 * Sets the minimum time that must elapse between feed runs
 */
define('MIN_UPDATE_SECONDS', 5);

/*
* /////////////////////////////////////////////////////////////////////////////////////
*  Do not edit below this line
* /////////////////////////////////////////////////////////////////////////////////////
*/

define("DEFAULTS_TITLE", "(Default)");

/**
 *  Constants for combo boxes
 */
define("RANDOM_AUTHOR", "(Use random author)");
define("AUTHOR_FROM_FEED", "(Use author from feed)");
define("ADD_AUTHOR", "(Create new author)");
define("SKIP_POST", "(Skip the post)");

/**
 * Other constants
 */
define("AB_MANUAL_UPDATES", 2);
define("AB_EVERY_X_UPDATES", 1);
define("AB_ITEM_MAX_POSTS", 1);
define("AB_ITEM_PERCENT_POSTS", 2);
define("AB_TITLE_TRUNCATE", 0);
define("AB_TITLE_SKIP", 1);

/**
 * Logging constants
 */
define("AB_LOG_ERROR", 3);
define("AB_LOG_WARNING", 4);
define("AB_LOG_NOTICE", 5);
define("AB_LOG_INFO", 6);
define("AB_LOG_DEBUG", 7);
define("AB_LOG_ONLY", 256);
define("AB_DISPLAY_ONLY", 512);

/**
 * File attachment extensions
 */
define("AB_ATTACH_EXT", "jpg|jpeg|png|bmp|ico|tiff|tif|gif|aac|ac3|aif|aiff|m3a|m4a|m4b|mka|mp1|mp2|mp3|ogg|oga|ram|wav|wma|asf|avi|divx|dv|flv|m4v|mkv|mov|mp4|mpeg|mpg|mpv|ogm|ogv|qt|rm|vob|wmv");

/**
 * Technical support contact
 */
define("AB_SUPPORT_EMAIL", "support@autoblogged.com");

$ab_options = array();
$feedtypes = array();
$autoblogged = object;
$rss = object;

if (!class_exists('autoblogged')) {

   /**
    * Main AutoBlogged class
    *
    *
    * @package   AutoBlogged
    * @author    AutoBlogged <support@autoblogged.com>
    * @category  plugins
    * @copyright 2011 AutoBlogged
    * @version   Release: @package_version@
    */
   class autoblogged {

      var $db_table_name = '';
      var $tags = array();
      var $keywords = array();
      var $rssmodules = array();
      var $exclude_domains = array();
      var $exclude_words = array();
      var $global_extra_tags = '';
      var $categories = array();
      var $bookmarks = array();
      var $own_domain = '';
      var $filtered_tags = array();
      var $upload_dir = '';
      var $upload_url = '';
      var $current_feed = array();
      var $current_item = array();
      var $postinfo = array();
      var $logger = object;
      var $show_output = false;
      var $debug = true;
      var $processing = false;
      var $profiiler;

      /**
       * @var instance - Static property to hold the singleton instance
       */
      static $instance = false;


      /**
       *
       */
      function __construct() {
         global $ab_options, $feedtypes;

         // WordPress hooks
         add_filter('xmlrpc_methods', 'autoblogged_xmlrpc');
         add_action("admin_menu", array(&$this, "ab_addAdminPages"));
         add_action('shutdown', array(&$this, 'ab_shutdownIntercept'));
         add_action('wp_footer', 'ab_footer');
         add_action('wp_head', array(&$this, 'on_wp_head'), 1, 1);
         add_action('admin_init', array(&$this, 'on_admin_init'), 1);
         add_action('admin_print_scripts', array(&$this, 'on_load_plugin_admin_scripts'));
         register_activation_hook(__FILE__, "ab_installOnActivation");


         // Load common functions
         require_once (dirname(__FILE__).'/ab-functions.php');
         $ab_options = ab_getOptions();
         $feedtypes = array("1" => "RSS Feed", "2" => "Google Blog Search", "3" => "IceRocket Blog Search", "4" => "Twitter Search", "7" => "Yahoo! News Search", "8" => "Flickr Tag Search", "9" => "YouTube Tag Search", "10" => "Yahoo! Video Search (Disabled)",);
         $feedtypes = apply_filters('autoblogged_feed_types', $feedtypes);
         //if (file_exists('.dev/class.krumo.php')) require_once('.dev/class.krumo.php');
      }

      function on_load_plugin_admin_scripts() {
         wp_enqueue_script('jquery');
         wp_enqueue_script('jquery-ui-core');
         wp_enqueue_script('jquery-ui-tabs');
      }

      /**
       * Creates hooks for all admin pages
       *
       * @return void
       * @access public
       */
      function ab_addAdminPages() {
         add_menu_page('AutoBlogged', 'AutoBlogged', 'manage_options', 'AutoBlogged', 'ab_FeedsPage', ab_pluginURL().'/img/icon.png');
         add_submenu_page('AutoBlogged', 'AutoBlogged Feeds', 'Feeds', 'manage_options', 'AutoBlogged', 'ab_FeedsPage');
         add_submenu_page('AutoBlogged', 'AutoBlogged Tag Options', 'Tag Options', 'manage_options', 'AutoBloggedTagOptions', 'ab_TagOptionsPage');
         add_submenu_page('AutoBlogged', 'AutoBlogged Filtering Options', 'Filtering', 'manage_options', 'AutoBloggedFiltering', 'ab_FilteringPage');
         add_submenu_page('AutoBlogged', 'AutoBlogged Settings', 'Settings', 'manage_options', 'AutoBloggedSettings', 'ab_SettingsPage');
         add_submenu_page('AutoBlogged', 'AutoBlogged Support', 'Support', 'manage_options', 'AutoBloggedSupport', 'ab_SupportPage');

         // load the scripts we will need
         if (isset($_REQUEST['page'])) {
            if (stristr($_REQUEST['page'], 'AutoBlogged')) {
               wp_enqueue_script('post');
               wp_enqueue_script('thickbox');
               wp_enqueue_script('postbox');
            }
         }

         add_filter('plugin_row_meta', array(&$this, 'onPluginRowMeta'), 10, 2);
         add_filter('plugin_action_links', array(&$this, 'onPluginActionLinks'), 10, 3);
      }

      /**
       * @param $links
       * @param $file
       *
       * @return array
       */
      public function onPluginRowMeta($links, $file) {
         if (stristr($file, 'autoblogged')) {
            $links = array();

            // Version
            $links[] = __('Version').' '.AB_VERSION;

            // Homepage
            $links[] = '<a target="_blank" href="http://autoblogged.com" style="color:#777F25;">Visit AutoBlogged.com</a><img src="'.ab_pluginURL().'/img/link.gif"/>';

            // Support
            $links[] = '<a target="_blank" href="http://community.autoblogged.com" style="color:#777F25;">Forums & Support</a><img src="'.ab_pluginURL().'/img/link.gif"/>';

            // Social
            $links[] = '<a target="_blank" href="http://twitter.com/AutoBlogged"><img style="vertical-align: text-bottom;" src="'.ab_pluginURL().'/img/twitter.png"/>Twitter</a><img src="'.ab_pluginURL().'/img/link.gif"/>';
            $links[] = '<a target="_blank" href="http://facebook.com/AutoBlogged"><img style="vertical-align: text-bottom;" src="'.ab_pluginURL().'/img/facebook.png"/> Facebook</a><img src="'.ab_pluginURL().'/img/link.gif"/>';
         }
         return $links;
      }

      /**
       * @param $links
       * @param $file
       *
       * @return array
       */
      public function onPluginActionLinks($links, $file) {

         if (stristr($file, 'autoblogged')) {
            // Add a settings link to the plugin row
            $settingsPageURL = admin_url().'admin.php?page=AutoBlogged';
            $newlink = '<a href="'.$settingsPageURL.'">'.__('Settings').'</a>';
            array_unshift($links, $newlink);
         }
         return $links;
      }

      /**
       * Custom error handler
       *
       * @param  string  $code
       * @param  string  $message
       * @param  string  $file
       * @param  string  $line
       *
       * @return void
       * @access public
       */
      function ab_errorHandler($code, $message, $file, $line) {
         if (stristr($file, 'wp-includes') || stristr($file, 'lib')) return;
         switch ($code) {
            case E_WARNING:
            case E_USER_WARNING:
               $priority = PEAR_LOG_WARNING;
               break;

            case E_NOTICE:
            case E_USER_NOTICE:
               return;
               break;

            case E_ERROR:
            case E_USER_ERROR:
               $priority = PEAR_LOG_ERR;
               break;

            default:
               return;
         }
         $this->ab_logMsg($message.' in '.$file.' at line '.$line, $priority);
      }

/*
      protected function addProfiler($title) {
         if (isset($this->profiler)) {
            $this->profiler->increase();
            $this->profiler->add('Start: '.$title);
         }
      }

      protected function removeProfiler($title) {
         if (isset($this->profiler)) {
            $this->profiler->add('End: '.$title);
            $this->profiler->decrease();
         }
      }
*/

      /**
       * Process Feeds
       *
       * Main AutoBlogged feed processing procedure
       *
       * @param  string  $fid          Optional feed ID
       * @param  string  $type         'scheduled', 'manual', 'cron', 'xmlrpc', 'preload-cache', or 'preview'
       * @param  boolean $show_output  Echo status messages to output
       * @param  string  $debug_mode   Log debug-level messages
       *
       * @return boolean
       * @access public
       */
      function ab_processFeeds($fid = '', $type, $show_output = false, $debug_mode) {
         global $wpdb;
         global $ab_options;
         global $rss;

         if ($this->processing == true) {
            $this->ab_logMsg('AutoBlogged is currently processing feeds, please try again in a few minutes.', AB_LOG_WARNING);
            return 0;
         }

         /* Profiling
         if (file_exists('.dev/class.profiling.php')) {
            require_once('.dev/class.profiling.php');
            $this->profiler = new profiling();
            $this->profiler->add("Processing feeds--------------------------------------");
         }
         */

         $this->processing = true;
         @set_time_limit(300);
         ignore_user_abort();
         flush();
         $ab_options = ab_getOptions();
         $added_post_ids = array();

         // Include our dupecheck hook
         if ($ab_options['filterbytitle'] == true) {
            add_filter('wp_insert_post_data', array(&$this, 'on_wp_insert_post_data'), 1, 2);
         }

         // Logging, debugging, and error handling initialization
         $this->ab_initlogger();
         set_error_handler(array(&$this, 'ab_errorHandler'));
         $job_id = uniqid(substr(session_id(), -3), true);
         $request_ip = $_SERVER['REMOTE_ADDR'];
         $this->show_output = $show_output;
         $this->type = $type;


         // If provided, debug_mode overrides global setting
         $this->debug_mode = $ab_options['showdebug'];
         if (isset($debug_mode)) $this->debug_mode = $debug_mode;

         echo '<link rel="stylesheet" type="text/css" href="'.ab_pluginURL().'/abadmin.css" />'."\r\n";

         // First a quick check to make sure enough time has passed since the last time a feed was processed
         $this->ab_logMsg('Time: '.date('H:i:s', time()).'  Last update: '.date('H:i:s', get_transient('AutoBlogged_LastRun')), AB_LOG_DEBUG);
         if (time() < (float)get_transient('AutoBlogged_LastRun') + (float)MIN_UPDATE_SECONDS) {
            if ($type <> 'preview') {
               // Just wait for a few seconds if it was too soon since the last run
               //sleep(MIN_UPDATE_SECONDS);
               $this->ab_logMsg('Too soon since last run, please try again in '.MIN_UPDATE_SECONDS.' seconds', AB_LOG_WARNING);
               return 0;
            }
         }

         /** @noinspection PhpUndefinedConstantInspection */
         if (defined(DOING_CRON) || defined(DOING_AJAX)) {
            /** @noinspection PhpUndefinedConstantInspection */
            if (DOING_CRON === true || DOING_AJAX === true) {
               if ($type <> 'preview') {
                  $this->ab_logMsg('Unable to process feeds while WordPres is currently performing a CRON event, please try again.', AB_LOG_WARNING);
                  return 0;
               }
            }
         }

         // Check to make sure the last process completed
         $sql = 'SELECT MAX(started) as t_stamp, feed_id, completed, last_url FROM '.ab_tableName('autoblogged_journal').' GROUP BY started;';
         $lastupdated = $wpdb->get_results($sql, ARRAY_A);
         if (empty($lastupdated['completed'])) {
            if (isset($lastupdated['feed_id'])) $this->unfinished = $lastupdated['feed_id'];
            if (isset($lastupdated['last_url'])) $this->last_url = $lastupdated['last_url'];
         }

         // Set global last updated time
         $ab_options['lastupdate'] = time();
         ab_saveOptions();

         $this->ab_logMsg('Setting last update time to '.date("g:i a", $ab_options['lastupdate']), AB_LOG_INFO + AB_LOG_ONLY, 'debug');
         set_transient('AutoBlogged_LastRun', time(), 3600);
         set_transient('AutoBlogged_Interval', rand($ab_options['mintime'], $ab_options['maxtime']), 86400);

         // Misc
         $box_not_closed = false;
         kses_remove_filters();

         // Log header
         $this->ab_logMsg('=== v'.AB_VERSION.'  ==============================================', AB_LOG_INFO + AB_LOG_ONLY, 'debug');
         $this->ab_logMsg('Starting '.$type.' feed processing', AB_LOG_INFO + AB_LOG_ONLY, 'debug');
         flush();

         // Includes
         $this->ab_logMsg('Loading required modules...', AB_LOG_DEBUG);
         if (!class_exists('SimplePie')) require_once (ab_plugin_dir().'/lib/simplepie.php');
         require_once (ab_plugin_dir().'/modules.php');

         // Get the feed info from the db
         $this->ab_logMsg('Loading feed details...', AB_LOG_DEBUG);
         $sql = "SELECT * FROM ".ab_tableName();
         if (strlen($fid)) {
            $sql .= ' WHERE id = '.$wpdb->escape($fid);
         }
         $feeds = $wpdb->get_results($sql, 'ARRAY_A');

         // Check to make sure we have feeds
         if (count($feeds) < 1) {
            $this->ab_logMsg('There are no feeds to process.', AB_LOG_WARNING);
            return 0;
         }

         // Get some global settings
         $this->exclude_domains = ab_splitList(strtolower($ab_options['domains_blacklist']));
         $this->exclude_words = ab_splitList($ab_options['keywords_blacklist']);
         $this->global_extra_tags = $ab_options['tags'];
         $this->categories = get_categories('orderby=name&hide_empty=0');
         $this->bookmarks = get_bookmarks();
         $this->own_domain = str_ireplace("http://", "", get_option('siteurl'));
         $this->filtered_tags = ab_splitList($ab_options['notags']);
         $uploaddir_t = wp_upload_dir();
         $this->upload_dir = $uploaddir_t['path'];
         $this->upload_url = $uploaddir_t['url'];

         // Populate list of authors
         $this->ab_logMsg('Loading authors...', AB_LOG_DEBUG);
         $this->userlist = array();
         $users = $wpdb->get_results("SELECT display_name FROM $wpdb->users ORDER BY display_name");
         if (is_array($users)) {
            foreach ($users as $user) {
               $this->userlist[] = $user->display_name;
            }
         }

         // Verbose output
         $this->ab_logMsg('Processing feeds...', AB_LOG_INFO);

         // Set up the DB connection for dupechecking
         if (DUPECHECK_DB_QUERY) {
            if ($ab_options['filterbytitle'] == true || $ab_options['filterbylink'] == true) {
               $ab_options['link'] = mysql_connect(DB_HOST, DB_USER, DB_PASSWORD) or $this->ab_logMsg('MySQL Error: '.mysql_error(), AB_LOG_DEBUG);
               mysql_select_db(DB_NAME) or die('Could not select database');

               // Get character set of the wp_posts table
               $SQL = "SELECT CHARACTER_SET_NAME FROM information_schema.COLUMNS WHERE `TABLE_SCHEMA` = '".DB_NAME."' AND `TABLE_NAME` = '".ab_tableName('posts')."' AND `COLUMN_NAME` = 'post_title' AND post_status <> 'trash'";
               $re = mysql_query($SQL);
               if ($re) {
                  $r = mysql_fetch_row($re);
                  $ab_options['charset'] = $r[0];
                  mysql_free_result($re);
               }
            }
         }

         // Load dupes file if using file-based dupe checking
         if ($ab_options['filterbyfile']) {
            $this->dupesfilename = dirname(__FILE__).'/'.DUPES_FILENAME;
            if (!is_file($this->dupesfilename)) {
               $handle = fopen($this->dupesfilename, "a+");
               fclose($handle);
            }
            else {
               $this->post_titles = file($this->dupesfilename, FILE_IGNORE_NEW_LINES + FILE_SKIP_EMPTY_LINES);

               // Clean out large dupes files
               $c = count($this->post_titles);
               if ($c > MAX_DUPESFILE_POSTS) {
                  $this->ab_logMsg('Trimming '.DUPES_FILENAME, AB_LOG_DEBUG);

                  // Slice up the array and write all but the oldest 25% of titles back to the dupes file
                  $this->post_titles = array_slice($this->post_titles, MAX_DUPESFILE_POSTS * .25, $c);

                  // Write back to dupes file
                  if (is_writable($this->dupesfilename)) {
                     $posts = implode("\n", $this->post_titles);
                     sleep(1);
                     $handle = fopen($this->dupesfilename, "w");
                     if (fwrite($handle, $posts."\n") === false) { // Save the post titles to the dupes file
                        $this->ab_logmsg("Cannot write to dupecheck file: ".$this->dupesfilename, AB_LOG_WARNING);
                     }
                  }
                  else {
                     $this->ab_logmsg("Dupecheck file is not writeable: ".$this->dupesfilename, AB_LOG_WARNING);
                     if (isset($handle)) fclose($handle);
                  }
               }
            }
         }

         require_once 'lib/htmLawed.php';
         require_once 'lib/webReader.php';

         // Import feeds - main loop
         foreach ($feeds as $feed) {
            flush();
            unset($this->current_feed);

            // Pick up where the last feed failed if necessary
            if (isset($this->unfinished)) {
               if ($feed['id'] < $this->unfinished) {
                  if (USE_JOURNAL) $this->ab_closeJournal(false);
                  continue;
               }
               else {
                  unset($this->unfinished);
               }
            }

            $this->current_feed = $feed;
            $this->current_feed['messages'] = '';
            $this->ab_logMsg('', AB_LOG_INFO + AB_LOG_ONLY);
            $this->ab_logMsg('-------------------------------------------', AB_LOG_INFO + AB_LOG_ONLY);

            if ($box_not_closed == true) {
               if ($this->show_output) echo '</div><br />';
            }
            $box_not_closed = false;

            // Make sure the feed is enabled
            if ($this->current_feed['enabled'] == false) {
               $this->ab_logMsg('Feed is disabled', AB_LOG_WARNING);
               if (USE_JOURNAL) $this->ab_closeJournal(false);
               continue;
            }

            // Check the feed schedule
            $this->ab_logMsg('Checking feed schedule...', AB_LOG_DEBUG);
            if ($type == 'scheduled') {
               if (!$this->ab_checkFeedSchedule($feed)) continue;
            }

            // Update feed timestamp
            $feed['last_updated'] = time();
            $sql = 'UPDATE '.ab_tableName().' SET `last_updated`='.$feed['last_updated'].' WHERE id='.$wpdb->escape($this->current_feed['id']).';';
            $ret = $wpdb->query($sql);

            // Initialize feed journal values
            if (USE_JOURNAL) {
               $feedname = substr($this->current_feed['url'], 0, 255);
               $this->current_feed['new_posts'] = 0;
               $this->current_feed['skipped_posts'] = 0;
               $this->current_feed['started'] = microtime(true);
               $this->current_feed['errors'] = 0;
               $this->current_feed['post_count'] = 0;
               unset($this->current_feed['messages']);
               $sql = "INSERT INTO ".ab_tableName('autoblogged_journal')." (job_id, started, type, feed_name, request_ip) VALUES ('$job_id', ".(float)$this->current_feed['started'].", '$type', '$feedname', '$request_ip')";
               $ret = $wpdb->query($sql);
               $this->current_feed['journal_id'] = mysql_insert_id();
            }

            // Load custom fields for this feed
            if (count(ab_unserialize($this->current_feed['customfield'])) > 0 && count(ab_unserialize($this->current_feed['customfieldvalue'])) > 0) {
               $this->current_feed['customFields'] = ab_arrayCombine(ab_unserialize($this->current_feed['customfield']), ab_unserialize($this->current_feed['customfieldvalue']));
            }

            // Load custom taxonomies
            if (count(ab_unserialize($this->current_feed['customtaxonomy'])) > 0 && count(ab_unserialize($this->current_feed['customtaxonomyalue'])) > 0) {
               $this->current_feed['customTaxonomies']['taxonomy'] = (array)ab_unserialize($this->current_feed['customtaxonomy']);
               $this->current_feed['customTaxonomies']['value'] = (array)ab_unserialize($this->current_feed['customtaxonomyvalue']);
            }

            // Load other feed-level settings
            $this->current_feed['feed_extra_tags'] = ab_unserialize($this->current_feed['tags']);
            $this->current_feed['feed_extra_tags'] = ab_arrayTrim(array_filter($this->current_feed['feed_extra_tags'], 'strlen'));
            $this->current_feed['nowords'] = ab_splitList($this->current_feed['includenowords']);
            $this->current_feed['allwords'] = ab_splitList($this->current_feed['includeallwords']);
            $this->current_feed['anywords'] = ab_splitList($this->current_feed['includeanywords']);
            $this->current_feed['phrase'] = ab_splitList($this->current_feed['includephrase']);
            if (is_array(ab_unserialize($this->current_feed['searchfor']))) $this->current_feed['search'] = array_merge(ab_unserialize($this->current_feed['searchfor']));
            $this->current_feed['replace'] = ab_unserialize($this->current_feed['replacewith']);

            if (strlen($this->current_feed['title'])) {
               if ($this->show_output) {
                  echo '<div class="feedurl"><h1 class="abadmin">Feed Set: '.$this->current_feed['title'].'</h1>';
               }
            }

            // Loop through each URL
            $feed_urls = explode("\r\n", $this->current_feed['url']);
            foreach ($feed_urls as $feed_url) {

               // Profiling
               //$this->addProfiler('Processing feed item-----');

               // Pick up where last run failed if necessary
               if (isset($this->unfinished)) {
                  if ($feed['id'] == $this->unfinished && isset($this->last_url) && $feed_url <> $this->last_url) {
                     continue;
                  }
                  else {
                     if ($feed_url == $this->last_url) {
                        $this->ab_logMsg('Picking up where last left off.', AB_LOG_DEBUG);
                        unset($this->last_url);
                     }
                  }
               }

               if (empty($feed_url)) continue;

               // Retrieve the feed URL
               if (!$this->ab_grabFeed($feed_url)) {
                  if (USE_JOURNAL) $this->ab_closeJournal(false);
                  continue;
               }

               // Populate the feed items
               $items = apply_filters('autoblogged_rss_feed_items', $rss->get_items(), $this->postinfo);

               // Preload cache only
               if ($this->type == 'preload-cache') {
                  $this->ab_logMsg('Feed loaded into cache from '.$feed_url, AB_LOG_INFO);
                  continue;
               }
               $sql = "UPDATE ".ab_tableName('autoblogged_journal')." SET `last_url` = '$feed_url' WHERE `id` = ".$this->current_feed['journal_id'];
               $ret = $wpdb->query($sql);

               // Verbose output
               if ($this->show_output) {
                  echo '<div class="feedurl"><h2 class="abadmin">'.stripslashes($rss->get_title()).'</h2>';
                  echo '<br />&nbsp;&nbsp;<a href="'.$rss->subscribe_url().'" target="_blank"><img src="'.ab_pluginURL().'/img/feed.png" />&nbsp;'.$rss->subscribe_url().'</a></div>';
                  $this->ab_logMsg('Processing URL: '.$rss->subscribe_url(), AB_LOG_INFO + AB_LOG_ONLY);
                  $box_not_closed = true;
               }

               if (count($rss->get_item_quantity()) < 1) {
                  $this->ab_logMsg('Feed URL returned no items: '.$feed_url, AB_LOG_WARNING);
                  if (USE_JOURNAL) $this->ab_closeJournal(true);
                  continue;
               }

               $this->current_feed['post_count'] = 0;
               $this->ab_logMsg('Feed has '.count($items).' item(s).<br />', AB_LOG_INFO, 'view');

               // Temp placeholders for [[ and ]] and {{ and }}
               $this->current_feed['templates'] = str_replace('[[', '~~@-$', $this->current_feed['templates']);
               $this->current_feed['templates'] = str_replace(']]', '$-@~~', $this->current_feed['templates']);
               $this->current_feed['templates'] = str_replace('{{', '$!@~~', $this->current_feed['templates']);
               $this->current_feed['templates'] = str_replace('}}', '~~@!$', $this->current_feed['templates']);
               if (is_array($this->current_feed['customFields'])) {
                  foreach ($this->current_feed['customFields'] as $customField) {
                     $customField = str_replace('[[', '~~@-$', $customField);
                     $customField = str_replace(']]', '$-@~~', $customField);
                     $customField = str_replace('{{', '$!@~~', $customField);
                     $customField = str_replace('}}', '~~@!$', $customField);
                  }
               }
               if ($this->current_feed['replace']) {
                  foreach ($this->current_feed['replace'] as $pattern) {
                     $pattern = str_replace('[[', '~~@-$', $pattern);
                     $pattern = str_replace(']]', '$-@~~', $pattern);
                     $pattern = str_replace('{{', '$!@~~', $pattern);
                     $pattern = str_replace('}}', '~~@!$', $pattern);
                  }
               }

               // Select x posts from list of items, save ID's to array
               // in foreach, check to make sure current ID is in array

               // Loop through each item in the feed
               foreach ($items as $item) {
                  flush();
                  unset($this->postinfo);

                  $this->ab_logMsg('', AB_LOG_INFO + AB_LOG_ONLY);
                  $this->ab_logMsg('----- ', AB_LOG_INFO + AB_LOG_ONLY);
                  $this->current_item = $item;
                  $this->postinfo = array();

                  // Check to make sure we haven't hit max_posts
                  if (($this->current_feed['post_processing'] == AB_ITEM_MAX_POSTS) && ($this->current_feed['post_count'] >= $this->current_feed['max_posts'])) {
                     $this->ab_logMsg('Maximum posts reached for this URL.', AB_LOG_NOTICE, 'check');
                     if (USE_JOURNAL) $this->ab_closeJournal(true);
                     continue 2;
                  }

                  // Check percentage of posts
                  if (($this->current_feed['post_processing'] == AB_ITEM_PERCENT_POSTS) && (rand(100, 0) > $this->current_feed['posts_ratio'])) {
                     $this->ab_logMsg('Randomly skipping '.$this->current_feed['posts_ratio'].'% of this feed\'s posts.', AB_LOG_NOTICE, 'skip');
                     if (USE_JOURNAL) $this->ab_closeJournal(true);
                     continue;
                  }

                  //$this->addProfiler('Get misc info 1');

                  $this->postinfo['feed_title'] = $this->current_feed['title'];
                  if ($this->show_output) echo '<div style="border-style: dotted none none none; border-width: thin;border-color: #A0A0A0;margin-bottom: 5px;margin-top: 5px;">&nbsp;</div>';


                  // Get item link
                  $this->ab_logMsg('Getting Item Link...', AB_LOG_DEBUG);
                  if (!$this->ab_itemGetLink()) {
                     if (USE_JOURNAL) $this->ab_closeJournal(false);
                     continue;
                  }

                  // Get item title
                  $this->ab_logMsg('Getting item title...', AB_LOG_DEBUG);
                  if (!$this->ab_itemGetTitle()) {
                     if (USE_JOURNAL) $this->ab_closeJournal(false);
                     continue;
                  }
                  //$this->removeProfiler('Get misc info 1');

                  // Dupecheck
                  //$this->addProfiler('Dupecheck');
                  $this->ab_logMsg('Dupecheck...', AB_LOG_DEBUG);
                  if (!$this->ab_itemDupeCheck()) {
                     if (USE_JOURNAL) $this->ab_closeJournal(false);
                     continue;
                  }
                  //$this->removeProfiler('Dupecheck');

                  // Get content
                  //$this->addProfiler('Get content');
                  $this->ab_logMsg('Getting content...', AB_LOG_DEBUG);
                  if (!$this->ab_itemGetContent()) {
                     if (USE_JOURNAL) $this->ab_closeJournal(false);
                     continue;
                  }
                  //$this->removeProfiler('Get content');

                  // Playing nice
                  if (!$this->ab_itemCheckRobots()) continue;

                  // Filtering
                  //$this->addProfiler('Filtering');
                  $this->ab_logMsg('Filtering...', AB_LOG_DEBUG);
                  if (!$this->ab_itemFilter()) {
                     if (USE_JOURNAL) $this->ab_closeJournal(false);
                     continue;
                  }
                  //$this->removeProfiler('Filtering');


                  //$this->addProfiler('Get misc info 2');

                  // Get author
                  $this->ab_logMsg('Getting author...', AB_LOG_DEBUG);
                  if (!$this->ab_itemGetAuthor()) {
                     if (USE_JOURNAL) $this->ab_closeJournal(false);
                     continue;
                  }

                  // Get date
                  $this->ab_logMsg('Getting date...', AB_LOG_DEBUG);
                  if (!$this->ab_itemGetDate()) {
                     if (USE_JOURNAL) $this->ab_closeJournal(false);
                     continue;
                  }

                  // Get copyright
                  if (!$this->ab_itemGetCopyright()) continue;

                  // Get item source
                  $this->ab_logMsg('Getting item source...', AB_LOG_DEBUG);
                  if (!$this->ab_itemGetSource()) continue;

                  //$this->removeProfiler('Get misc info 2');

                  // Get attachments
                  //$this->addProfiler('Find attachments');
                  $this->ab_logMsg('Checking for attachments...', AB_LOG_DEBUG);
                  if (!$this->ab_itemGetAttachments()) {
                     if (USE_JOURNAL) $this->ab_closeJournal(false);
                     continue;
                  }
                  //$this->removeProfiler('Find attachments');

                  // Get categories and tags
                  //$this->addProfiler('Categories and tags');
                  $this->ab_logMsg('Getting categories and tags...', AB_LOG_DEBUG);
                  if (!$this->ab_itemGetCategoriesAndTags()) {
                     if (USE_JOURNAL) $this->ab_closeJournal(false);
                     continue;
                  }
                  //$this->removeProfiler('Categories and tags');

                  // Custom taxonomies
                  //$this->addProfiler('Custom taxonomies');
                  $this->ab_logMsg('Processing custom taxonomies...', AB_LOG_DEBUG);
                  if (!$this->ab_itemGetCustomTaxonomies()) {
                     if (USE_JOURNAL) $this->ab_closeJournal(false);
                     continue;
                  }
                  //$this->removeProfiler('Custom taxonomies');

                  // Custom fields
                  //$this->addProfiler('Custom fields');
                  $this->ab_logMsg('Getting custom fields...', AB_LOG_DEBUG);
                  $this->ab_logMsg('Getting custom fields...', AB_LOG_DEBUG);
                  if (!$this->ab_itemGetCustomFields()) {
                     if (USE_JOURNAL) $this->ab_closeJournal(false);
                     continue;
                  }
                  //$this->removeProfiler('Custom fields');

                  // Post templates
                  //$this->addProfiler('Post templates');
                  $this->ab_logMsg('Post templates...', AB_LOG_DEBUG);
                  $this->postinfo['post'] = $this->ab_applyTemplate($this->current_feed['templates']);
                  //$this->removeProfiler('Post templates');

                  //$this->addProfiler('Search and replace');
                  $this->ab_itemDoSearchReplace();
                  //$this->removeProfiler('Search and replace');

                  // Put back the replaced double brackets
                  $this->postinfo['post'] = str_replace('~~@-$', '[', $this->postinfo['post']);
                  $this->postinfo['post'] = str_replace('$-@~~', ']', $this->postinfo['post']);
                  $this->postinfo['post'] = str_replace('$!@~~', '{', $this->postinfo['post']);
                  $this->postinfo['post'] = str_replace('~~@!$', '}', $this->postinfo['post']);


                  // Print out feed info if we are doing a visible run
                  if ($this->show_output) {
                     echo '<div class="feedswrap">'.ab_convert_encoding($this->postinfo['post']).'</div>';
                  }

                  sleep(rand(0, 2) / 3);

                  //$this->addProfiler('Add post');
                  $pid = $this->ab_itemAddPost();
                  if (!$pid) {
                     if (USE_JOURNAL) $this->ab_closeJournal(false);
                     continue;
                  }
                  else {
                     $added_post_ids[] = $pid;
                  }
                  //$this->removeProfiler('Add post');


                  $this->current_feed['new_posts']++;
                  //$this->removeProfiler('Processing feed item-----');
               }

               // Update journal
               if (USE_JOURNAL) {
                  $sql = "UPDATE ".ab_tableName('autoblogged_journal')." SET `last_url` = '$feed_url' WHERE `id` = ".$this->current_feed['journal_id'];
                  $ret = $wpdb->query($sql);
               }

               // Update history journal
               if (USE_JOURNAL) $this->ab_closeJournal(true);
            }
         }

         // Calculate and then show stats if requested:
         $stats['started'] = date(DATE_RSS, $this->current_feed['started']);
         $stats['processing_time'] = round((microtime(true) - $this->current_feed['started']) / 100, 4);
         $stats['new_posts'] = $this->current_feed['new_posts'];
         $stats['skipped_posts'] = $this->current_feed['skipped_posts'];
         $stats['errors'] = $this->current_feed['errors'];
         if (function_exists('memory_get_usage')) $stats['memory_used'] = memory_get_usage();
         $x = @sys_getloadavg();
         $stats['avg_sys_load'] = $x[0];

         if (ab_isTrue($_GET['stats']) || $type == 'manual') {
            echo '<br /><p class="abadmin"><strong>Feed Processing Statistics</strong><br />';
            echo 'Time started: '.$stats['started'].'<br />';
            echo 'Time taken: '.$stats['processing_time'].' seconds<br />';
            echo 'Posts added: '.$stats['new_posts'].'<br />';
            echo 'Posts skipped: '.$stats['skipped_posts'].'<br />';
            echo 'Errors: '.$stats['errors'].'<br />';
            echo 'Memory usage: '.number_format($stats['memory_used']).' bytes<br />';
            echo 'Average system load: '.$stats['avg_sys_load'].'<br/><br/></p>';
         }

         do_action('autoblogged_finished_processing', $added_post_ids, $stats);

         unset($feed);
         unset($feeds);
         $this->processing = false;

         // Remove the filter intercept
         remove_filter('wp_insert_post_data', array(&$this, 'on_wp_insert_post_data'), 1, 2);

         // Close db link if open
         if ($ab_options['link']) mysql_close($ab_options['link']);

         // Google XML Sitemaps Support
         if (has_action('sm_rebuild') && REBUILD_SITEMAPS) {
            if ($this->show_output) echo '<br /><br />Rebuilding XML sitemap...<br />';
            do_action('sm_rebuild');
         }

         if ($this->show_output) echo '<br /><br /><br /><p class="abadmin">Feed processing completed.</p>';


         if ($this->show_output) echo '<br />';
         $box_not_closed = false;
         if ($this->show_output) echo '<br /></div><br /><br /><br /><br /><br /><br />';
         if (isset($rss)) {
            @$rss->__destruct();
            unset($items);
            unset($rss);
         }
         if (is_object($this->logger)) {
            $this->logger->flush();
            $this->logger->close();
         }

         /*
         if (isset($this->profiler)) {
            $this->profiler->end();
         }
         */

         return true;
      }


      /**
       * Close journal
       *
       * @param  boolean $completed
       *
       * @return void
       * @access public
       */
      function ab_closeJournal($completed = true) {
         global $wpdb;

         $endtime = microtime(true);
         $time_taken = round(($endtime - $this->current_feed['started']) / 100, 4);

         if (function_exists('memory_get_usage')) $memusage = memory_get_usage();
         if (function_exists('sys_getloadavg')) $load = sys_getloadavg();
         if (empty($load)) {
            $sysload = 0;
         } else {
            $sysload = $load[0];
         }
         $messages = $this->current_feed['messages'];

         $sql = "UPDATE ".ab_tableName('autoblogged_journal')." SET `ended` = '$endtime', `completed` = ".intval($completed).", `new_posts` = ".$this->current_feed['new_posts'].", `skipped_posts` = ".$this->current_feed['skipped_posts'].", `errors` = ".$this->current_feed['errors'].", `mem_usage` = $memusage, `time_taken` = $time_taken, `sys_load_avg` = $sysload, `messages` = '".$messages."' WHERE `id` = ".$this->current_feed['journal_id'];

         $ret = $wpdb->query($sql);

         // Purge old journal records
         $oldrecords = (int)$this->current_feed['journal_id'] - 100;
         $sql = "DELETE FROM ".ab_tableName('autoblogged_journal')." WHERE ID < $oldrecords";
         $ret = $wpdb->query($sql);
         /*
         if (isset($this->profiler)) {
            echo $this->profiler->get_result();
         }
         */
      }

      /**
       * Check for robots or googlebot restrictions if necessary
       *
       * @return boolean Return
       * @access public
       */
      function ab_itemCheckRobots() {
         global $ab_options;
         $directives = array();

         if ($ab_options['respectrobots'] == true) {
            $skip_values = array('noindex', 'noarchive', 'nosnippet', 'none', 'noautobloggers', 'noautoblogs');
            if (preg_match_all('/<meta\s+name\s*=\s*["\']?(?:autoblogged|robots|googlebot|autoblogs|msnbot|bingbot|slurp)["\']?\s*content\s*=\s*["\']?([^"\']*)["\']?/i', $this->current_feed['page_content'], $matches)) {
               foreach ($matches[1] as $match) {
                  $directives = array_merge((array)$directives, (array)preg_split('/\s?,\s?/', $match));
               }
               $directives = array_unique($directives);
               if (count(array_intersect($skip_values, (array)$directives))) {
                  $this->ab_logMsg('Skipping post due meta tag restrictions.', AB_LOG_NOTICE, 'skip');
                  return false;
               }
            }
         }
         return true;
      }

      /**
       * Check feed schedule
       *
       * @param  string &$feed
       *
       * @return boolean Return
       * @access public
       */
      function ab_checkFeedSchedule(&$feed) {
         global $wpdb;

         switch ($this->current_feed['schedule']) {
            case AB_MANUAL_UPDATES:
               $this->ab_logMsg('Feed configured for manual updates only.', AB_LOG_DEBUG);
               return false;
               break;
            case AB_EVERY_X_UPDATES:
               if ($this->current_feed['update_countdown'] > 0) {

                  // Decrement the counter
                  $sql = 'UPDATE '.ab_tableName().' SET `update_countdown`='.($this->current_feed['update_countdown'] - 1).' WHERE id='.$wpdb->escape($this->current_feed['id']).';';
                  $ret = $wpdb->query($sql);

                  $this->ab_logMsg('Feed not scheduled for updating.', AB_LOG_DEBUG);
                  return false;
               }
               else {

                  // Reset the counter
                  $sql = 'UPDATE '.ab_tableName().' SET `update_countdown`='.$this->current_feed['updatefrequency'].' WHERE id='.$wpdb->escape($this->current_feed['id']).';';
                  $ret = $wpdb->query($sql);

                  return true;
               }
               break;
            default:

               // Always update this feed
               return true;
               break;
         }
      }


      /**
       * Grab RSS or Atom feed
       *
       * @param  string &$url    Feed URL
       *
       * @internal param string $items
       * @return boolean
       * @access   public
       */
      function ab_grabFeed(&$url) {
         global $ab_options, $rss;
         do_action('autoblogged_before_grab_feed', $this->postinfo);

         // Get URL and handle variations of feed urls
         $feedurl = ab_getFeedURL($this->current_feed['type'], $url);
         $feedurl = str_replace("feed://", "http://", $feedurl);
         $feedurl = str_replace("feed:http", "http", $feedurl);

         // Very basic URL validity check at this point
         if (!preg_match('#.+\..{2,6}#', $feedurl)) {
            $editurl = '"'.wp_nonce_url($_SERVER['SCRIPT_URI'].'?page='.$_GET['page'], 'autoblogged-feeds-edit').'&amp;action=edit&_fid='.$this->current_feed['type'].'"';
            $this->ab_logMsg("\"$feedurl\" is not a valid URL, check your <a href=$editurl>feed settings</a>.", AB_LOG_ERROR, 'stop');
            $this->current_feed['errors']++;
            return false;
         }

         // Initialize SimplePie
         $this->ab_logMsg('Initializing SimplePie...', AB_LOG_DEBUG);
         //unset($rss);
         $rss = new SimplePie();

         // Special handling for Yahoo! pipes if they just enter the Pipe URL itself
         if (stristr($feedurl, 'pipes.yahoo') && (!strstr($feedurl, 'rss'))) $feedurl .= '&_render=rss';

         // Special handling for feedburner feeds
         if (stristr($feedurl, 'feedburner.com') && !stristr($feedurl, 'format=xml')) {
            $feedurl .= '?format=xml';
         }

         // Cache settings (silently skip if not writable)
         if (is_writable(ab_plugin_dir().'/cache')) {
            $rss->enable_cache(true);
            $rss->set_cache_location(ab_plugin_dir().'/cache');
            $rss->set_cache_duration($ab_options['rss_cache_timeout']);
         }

         // Autodiscovery settings
         $rss->set_autodiscovery_level(SIMPLEPIE_LOCATOR_ALL);
         $rss->set_autodiscovery_cache_duration(1209600); // 2 weeks
         $rss->set_max_checked_feeds(10);


         // Other settings
         $rss->enable_order_by_date(false);
         $rss->set_useragent($ab_options['useragent'].' ('.mt_rand().')');
         $rss->set_item_limit(50);
         $rss->set_url_replacements(array('a' => 'href', 'img' => 'src'));

         // Timeout
         if (stristr($feedurl, 'pipes')) {
            $rss->set_timeout(60);
         } else {
            $rss->set_timeout(20);
         }

         // HTML tag and attribute stripping
         $strip_htmltags = $rss->strip_htmltags;
         if (ALLOW_ALL_TAGS) {
            $strip_htmltags = array();
            $rss->strip_attributes(false);
         }
         else {
            if (ALLOW_OBJECT_AND_EMBED_TAGS) {
               unset($strip_htmltags[array_search('object', $strip_htmltags)]);
               unset($strip_htmltags[array_search('embed', $strip_htmltags)]);
               unset($strip_htmltags[array_search('param', $strip_htmltags)]);
            }
            if (ALLOW_FORM_TAGS) {
               unset($strip_htmltags[array_search('form', $strip_htmltags)]);
               unset($strip_htmltags[array_search('input', $strip_htmltags)]);
            }
            if (ALLOW_FRAME_TAGS) {
               unset($strip_htmltags[array_search('frame', $strip_htmltags)]);
               unset($strip_htmltags[array_search('iframe', $strip_htmltags)]);
               unset($strip_htmltags[array_search('frameset', $strip_htmltags)]);
            }
            if (ALLOW_SCRIPT_TAGS) {
               unset($strip_htmltags[array_search('class', $strip_htmltags)]);
               unset($strip_htmltags[array_search('expr', $strip_htmltags)]);
               unset($strip_htmltags[array_search('script', $strip_htmltags)]);
               unset($strip_htmltags[array_search('noscript', $strip_htmltags)]);
               unset($strip_htmltags[array_search('onclick', $strip_htmltags)]);
               unset($strip_htmltags[array_search('onerror', $strip_htmltags)]);
               unset($strip_htmltags[array_search('onfinish', $strip_htmltags)]);
               unset($strip_htmltags[array_search('onmouseover', $strip_htmltags)]);
               unset($strip_htmltags[array_search('onmouseout', $strip_htmltags)]);
               unset($strip_htmltags[array_search('onfocus', $strip_htmltags)]);
               unset($strip_htmltags[array_search('onblur', $strip_htmltags)]);
            }
         }
         $strip_htmltags = array_values($strip_htmltags);
         $rss->strip_htmltags($strip_htmltags);
         if (ENCODE_INSTEAD_OF_STRIP) {
            $rss->encode_instead_of_strip(true);
         }

         $rss->cleanfeed = $this->current_feed['cleanfeed'];
         //$rss->securitycheck = $this->current_feed['securitycheck'];

         // Force feed handling with unrecognized or malformed feeds
         if (FORCE_FEED) {
            $rss->force_feed(true);
         }

         // Set feed URL
         $rss->set_feed_url($feedurl);

         // Retrieve the feed
         $this->ab_logMsg('Retrieving feed...', AB_LOG_DEBUG);
         $rss->init();
         $this->ab_logMsg('Checking feed results...', AB_LOG_DEBUG);


         // Handle errors
         if ($rss->error()) {
            if ($this->show_output) echo '<h2 class="abadmin">'.$feedurl.'</h2>';

            // Special handling for urls that aren't really feeds
            if (stristr($rss->error(), 'syntax error at line')) {
               $this->ab_logMsg('Error occurred retrieving feed or feed is invalid.<br />Feed URI: '.$rss->subscribe_url(), AB_LOG_ERROR, 'stop');
            }
            elseif (stristr($rss->error(), 'XML error')) {
               $this->ab_logMsg('Unable to parse feed, check the feed to make sure it is valid: '.$rss->error().'.<br /><a href="'.$rss->subscribe_url().'" target="_blank">'.$rss->subscribe_url().'</a>', AB_LOG_ERROR, 'stop');
            }
            elseif (stristr($rss->error(), 'cURL error 6')) {
               $this->ab_logMsg('Could not find server, check to make sure the URL is valid:<br /><a href="'.$rss->subscribe_url().'" target="_blank">'.$rss->subscribe_url().'</a>'.$rss->error(), AB_LOG_ERROR, 'stop');
            }
            elseif (stristr($rss->error(), 'cURL error 7')) {
               $this->ab_logMsg('Unable to connect to server, check your URL to make sure it is valid:<br /><a href="'.$rss->subscribe_url().'" target="_blank">'.$rss->subscribe_url().'</a>', AB_LOG_ERROR, 'stop');
            } else {
               $this->ab_logMsg('Unable to retrieve feed: '.$rss->error().'.<br />Feed URL: <a href="'.$rss->subscribe_url().'" target="_blank">'.$rss->subscribe_url().'</a>', AB_LOG_ERROR, 'stop');
            }

            if ($this->current_feed['type'] == 2) {
               $this->ab_logMsg('Note that Google blog search sometimes blocks automated searches. Please see <a target="_blank" href="http://community.autoblogged.com/entries/20268103-important-announcement-regarding-google-blog-search">this article</a> for more information.', AB_LOG_ERROR, 'stop');
            }

            $this->current_feed['errors']++;
            return false;
         }

         return true;
      }


      /**
       * Get the link for the feed item
       *
       * @return boolean Return
       * @access public
       */
      function ab_itemGetLink() {
         global $ab_options;

         $link = $this->current_item->get_link();

         // Skip if the link is empty
         if (empty($link) && ($ab_options['skipemptylinks'] == true)) {
            $this->ab_logMsg('Skipping post with empty link.', AB_LOG_NOTICE, 'skip');
            $this->current_feed['skipped_posts']++;
            return false;
         }

         // Skip the blog's own domain
         if (strlen($link) > 0) {
            if ($ab_options['allowdomain'] == false) {

               if (stristr($link, $this->own_domain)) {
                  $this->ab_logMsg('Skipping post from own domain.', AB_LOG_NOTICE, 'skip');
                  $this->current_feed['skipped_posts']++;
                  return false;
               }
            }
         }

         // Check for blacklisted domains and url sequences in the link
         $this->ab_logMsg('Checking for blacklisted urls...', AB_LOG_DEBUG);
         if (is_array($this->exclude_domains)) {
            foreach ($this->exclude_domains as $domain) {
               if (strlen($domain)) {
                  if (stristr($link, $domain)) {
                     $this->ab_logMsg('Skipping post with blacklisted domain or URL sequence: "'.$domain.'"', AB_LOG_NOTICE, 'skip');
                     $this->current_feed['skipped_posts']++;
                     return false;
                  }
               }
            }
         }

         // Fix for Yahoo links
         $link = urldecode(preg_replace('#http://rds.yahoo.com.*\*\*(http.*)#i', "$1", $link));

         $this->postinfo['link'] = $link;
         do_action('autoblogged_link', $this->postinfo);
         $this->ab_logMsg('Link: '.$link, AB_LOG_DEBUG);
         return true;
      }


      /**
       * Get item title
       *
       * @return boolean Return
       * @access public
       */
      function ab_itemGetTitle() {
         global $ab_options;

         $title = strip_tags($this->current_item->get_title());
         if ($this->show_output) echo '<h3 class="abadmin">&nbsp;&nbsp;&nbsp;&nbsp;'.ab_convert_encoding(html_entity_decode($title)).'</h3></p>';
         $this->ab_logMsg('Processing item: '.$title, AB_LOG_DEBUG);

         // Check for multiple punctuation marks
         if ($ab_options['skipmultiplepunctuation'] && preg_match("/[!$%&*?]{2,}/", $title)) {
            $this->ab_logMsg('Skipping post with multiple punctuation marks in title.', AB_LOG_NOTICE, 'skip');
            $this->current_feed['skipped_posts']++;
            return false;
         }

         // Check for all-caps titles
         if ($ab_options['skipcaps'] && $title == strtoupper($title)) {
            $this->ab_logMsg('Skipping post in all caps.', AB_LOG_NOTICE, 'skip');
            $this->current_feed['skipped_posts']++;
            return false;
         }

         // Title filtering
         If (strlen($title) > $ab_options['maxtitlelen']) {
            if ($ab_options['longtitlehandling'] == AB_TITLE_TRUNCATE) {

               // Truncate
               $this->ab_logMsg('Truncating title', AB_LOG_DEBUG);
               $lines = explode("\n", wordwrap($title, $ab_options['maxtitlelen'], "\n", true));
               $title = $lines[0].'...';
            }
            else {

               // Skip
               $this->ab_logMsg('Skipping post with long title.', AB_LOG_NOTICE, 'skip');
               $this->current_feed['skipped_posts']++;
               return false;
            }
         }
         $this->postinfo['title'] = $title;
         return true;
      }

      /**
       * Dupe Check
       *
       * @return boolean Return
       * @access public
       */
      function ab_itemDupeCheck() {
         global $ab_options;
         global $wpdb;

         $titledupesfound = false;
         $wpdb->flush();

         // Check for duplicate title
         if ($ab_options['filterbytitle'] == true) {

            // Attempt to get consistent character encoding
            $title = sanitize_title($this->postinfo['title']);
            $this->ab_logMsg('Checking for duplicate post slug "'.$title.'"', AB_LOG_DEBUG);
            $title = ab_convert_encoding($title);

            // Level 1: Try the WordPress wp_unique_post_slug API
            if (DUPECHECK_WP_API) {
               $this->ab_logMsg('Dupecheck using WordPress API...', AB_LOG_DEBUG);
               $checktitle = wp_unique_post_slug($title, 0, 'published', 'post', 0);
               $this->ab_logMsg('Post slug returned by WordPress: '.$checktitle, AB_LOG_DEBUG);
               if ($checktitle <> $title) {
                  $titledupesfound = true;
                  if ($ab_options['filterbyfile']) $this->ab_append_dupesfile($title);
                  $this->ab_logMsg('Dupe found, post slug returned by WordPress: '.$checktitle, AB_LOG_DEBUG);
               }
               else {
                  $this->ab_logMsg('No dupe found, post slug returned by WordPress: '.$checktitle, AB_LOG_DEBUG);
               }
            }

            // Try it again but this time ignore the index and use SQL_NO_CACHE
            if (DUPECHECK_DB_QUERY && $titledupesfound == 0) {
               $this->ab_logMsg('Dupecheck via direct DB connection...', AB_LOG_DEBUG);
               usleep(250000); // Halt execution for 1/4 second

               // Show some stats if debug output is enabled
               if (isset($this->show_debug)) {
                  if ($this->show_debug) {
                     if (function_exists('sys_getloadavg')) {
                        $load = sys_getloadavg();
                        $this->ab_logMsg('Avg server load time: '.$load[0].' (1 min) '.$load[1].' (5 mins)', AB_LOG_DEBUG);
                     }
                     if (function_exists('mysql_stat')) {
                        $this->ab_logMsg('MySQL Server Stats: '.mysql_stat($ab_options['link']), AB_LOG_DEBUG);
                     }
                  }
               }

               // Set client charset to match the wp_posts table
               if (isset($ab_options['charset'])) if (function_exists('mysql_set_charset')) mysql_set_charset($ab_options['charset'], $ab_options['link']);
               $title = mysql_real_escape_string($this->postinfo['title']);
               $query = "SELECT SQL_NO_CACHE post_title FROM $wpdb->posts  WHERE post_title = '$title' LIMIT 1";
               $result = mysql_query($query) or $this->ab_logMsg('MySQL Error: '.mysql_error(), AB_LOG_WARNING);
               if (is_resource($result)) {
                  if (mysql_num_rows($result)) {
                     $titledupesfound = true;
                     if ($ab_options['filterbyfile']) $this->ab_append_dupesfile($title);
                     $check_title = mysql_fetch_array($result);
                     $this->ab_logMsg(mysql_num_rows($result).' dupe(s) found, MySQL No Index Result: '.$check_title['post_name'], AB_LOG_DEBUG);
                  }
                  else {
                     $this->ab_logMsg('No dupes found.', AB_LOG_DEBUG);
                  }
               }
               else {
                  $this->ab_logMsg('No dupes found.', AB_LOG_DEBUG);
               }
            }

            if (isset($result)) if (is_resource($result)) mysql_free_result($result);
            if ($titledupesfound > 0) {
               $this->ab_logMsg('Skipping post with duplicate title.', AB_LOG_NOTICE, 'skip');
               $this->current_feed['skipped_posts']++;
               return false;
            }
         }

         // Do dupecheck based on a local text file
         if ($ab_options['filterbyfile']) {
            $title = sanitize_title($this->postinfo['title']);
            $checktitle = strtolower(substr($title, 0, DUPECHECK_FILE_TITLE_LENGTH));
            if ($titledupesfound == false) {
               $this->ab_logMsg('Checking posts file for "'.$checktitle.'"', AB_LOG_DEBUG);
               if (is_array($this->post_titles)) {
                  $titlesearch = in_array($checktitle, $this->post_titles);
                  $titledupesfound = ($titlesearch === true);
               }
            }

            // Now add this title to the list if it isn't already there
            if ($titledupesfound == false) {
               $this->ab_append_dupesfile($checktitle);
            }
            else {
               $this->ab_logMsg('Skipping post, duplicate title found.', AB_LOG_NOTICE, 'skip');
               $this->current_feed['skipped_posts']++;
               return false;
            }
         }

         // Check for dupe link
         $linkdupesfound = false;
         if ($ab_options['filterbylink'] == true) {
            $this->ab_logMsg('Checking for duplicate link...', AB_LOG_DEBUG);
            $sql = "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = 'link' AND meta_value = '".addslashes($this->postinfo['link'])."'";
            $linkdupesfound = $wpdb->query($sql);
            if (isset($wpdb->last_error)) {
               $this->ab_logMsg('Database error: '.$wpdb->last_error, AB_LOG_NOTICE);
            }
            if ($linkdupesfound === false) {
               $this->ab_logMsg('Error connecting to database to check for duplicate links.', AB_LOG_WARNING, 'stop');
               return false;
            }
            if ($linkdupesfound > 0) {
               $this->ab_logMsg('Skipping post with duplicate link.', AB_LOG_NOTICE, 'skip');
               $this->current_feed['skipped_posts']++;
               return false;
            }
         }

         // Otherwise, let the post through
         $this->ab_logMsg('No duplicate posts found.', AB_LOG_DEBUG);
         return true;
      }


      /**
       * Append title to dupecheck file
       *
       * @param  string $title
       *
       * @return void
       * @access public
       */
      function ab_append_dupesfile($title) {
         $cleantitle = strtolower(substr($title, 0, DUPECHECK_FILE_TITLE_LENGTH));
         $this->post_titles[] = $cleantitle;
         if (is_writable($this->dupesfilename)) {
            if (!$handle = fopen($this->dupesfilename, 'a')) {
               $this->ab_logmsg("Cannot open dupecheck file: ".$this->dupesfilename, AB_LOG_WARNING);
            }
            else {
               if (fwrite($handle, $cleantitle."\n") === FALSE) { // Save the post title to the dupes file
                  $this->ab_logmsg("Cannot write to dupecheck file: ".$this->dupesfilename, AB_LOG_WARNING);
               }
            }
            fclose($handle);
         }
         else {
            $this->ab_logmsg("Warning, dupecheck file is not writeable: ".$this->dupesfilename, AB_LOG_WARNING);
         }
      }


      /**
       * Get the feed item's content
       *
       * @return boolean Return
       * @access public
       */
      function ab_itemGetContent() {

         // Variable Declarations
         global $ab_options; // Global options
         $result = '';

         // -----  Extract content, description, and grab original article if needed

         //$this->addProfiler('Extract content');

         // Extract content and description from feed
         // These are populated for backwards compatibility with versions < 2.9


         $this->postinfo['content'] = ab_convert_encoding($this->current_item->get_content());
         $this->postinfo['description'] = ab_convert_encoding($this->current_item->get_description());

         // If content is empty, try the description
         if (empty($this->postinfo['content'])) {
            $this->postinfo['content'] = $this->postinfo['description'];
         }

         // If the content is still empty, try a media description which some video sites use
         if (empty($this->postinfo['content'])) {
            $elements = $this->current_item->get_item_tags('http://search.yahoo.com/mrss/', 'group');
            $this->postinfo['content'] = $elements[0]['child']['http://search.yahoo.com/mrss/']['description'][0]['data'];
         }

         if (empty($this->postinfo['content'])) {
            $this->ab_logmsg("Unable to extract content from feed.", AB_LOG_WARNING);
         }

         // Remove &nbsp; from posts so WP won't truncate it
         $this->postinfo['content'] = preg_replace('/(&amp;)?&?nbsp;|&#160/i', ' ', $this->postinfo['content']);
         $this->postinfo['description'] = preg_replace('/(&amp;)?&?nbsp;|&#160/i', ' ', $this->postinfo['description']);

         //$this->removeProfiler('Extract content');

         //$this->addProfiler('Grab article');
         // Get the original article if necessary
         //  Conditions:
         //    If we are extracting tags from original content
         $grab_page = ($ab_options['posttags'] == true);

         //    or we are grabbing full text content
         $grab_page = $grab_page || ($this->current_feed['excerptsource'] == 2);

         //    We are checking for copyright info
         $grab_page = $grab_page || ($ab_options['respectrobots'] == true);

         // Get the page if necessary
         if ($grab_page) {
            if (empty($this->postinfo['link'])) {
               $this->ab_logMsg('Cannot retreive original article, the item\'s link is not set.', AB_LOG_DEBUG);
            }
            else {
               // Check for and correct feedburner links
               if (stristr($this->postinfo['link'], 'feedproxy.google')) {
                  $elements = $this->current_item->get_item_tags('http://rssnamespace.org/feedburner/ext/1.0', 'origLink');

                  if (!empty($elements[0]['data'])) {
                     $this->postinfo['link'] = $elements[0]['data'];
                  }
               }
               $this->ab_logMsg('Fetching ' . $this->postinfo['link'], AB_LOG_DEBUG);

               $result = ab_httpFetch(str_replace(' ', '+', $this->postinfo['link']));
               if (strlen($result['error'])) {
                  // Warn but don't stop
                  $this->ab_logMsg('Unable to retrieve the original post: '.$result['error'], AB_LOG_WARNING);
               }
               if ($result['http_code'] >= 400) {
                  // Warn but don't stop
                  $this->ab_logMsg('Cannot retrieve URL '.$this->postinfo['link'].' ('.$result['http_code'].')', AB_LOG_WARNING);
               } else {


               $results = $result['content'];
               $this->ab_logMsg('Article retrieved successfully', AB_LOG_DEBUG);
               }

              // $results = maybe_unserialize(wp_remote_retrieve_body($response));

               if (empty($results)) {
                  // Warn but don't stop
                  $this->ab_logMsg('Cannot retrieve original post due to an error: '.$this->postinfo['link'].' ('.$result['error'].')', AB_LOG_WARNING);
               }
            }


            // Assign $page_content or fall back to feed content if empty
            if (!empty($results)) {
               $this->postinfo['page_content'] = ab_convert_encoding($results);
            }
            else {
               $this->ab_logMsg('Using content from feed itself.', AB_LOG_DEBUG);
               $this->postinfo['page_content'] = htmlentities2(html_entity_decode($this->postinfo['content']));
            }


            // todo: utf8
            // Clean up the HTML then pass to webReader
            $this->postinfo['page_content'] = preg_replace('/(&amp;)?&?nbsp;|&#160/i', ' ', $this->postinfo['page_content']);

            //$this->removeProfiler('Grab article');


            // Remove CDATA
            $this->postinfo['page_content'] = preg_replace(' %/?/?<!\[CDATA\[.*?//\]\]>%si', ' ', $this->postinfo['page_content']);

            // Run Tidy if it is installed
            //$this->addProfiler('Tidy');
            if (function_exists('tidy_clean_repair')) {
               $options = array(
                  'clean' => true,
                  'output-xhtml' => true,
                  'logical-emphasis' => true,
                  'show-body-only' => false,
                  'wrap' => 0,
                  'drop-empty-paras' => true,
                  'drop-proprietary-attributes' => false,
                  'enclose-text' => true,
                  'enclose-block-text' => true,
                  'char-encoding' => 'utf8',
                  'hide-comments' => true
               );
               $tidy = @tidy_parse_string($this->postinfo['page_content'], $options, 'UTF8');
               if (tidy_clean_repair($tidy)) {
                  $cleanedHTML = $tidy->value;
               }
               unset($tidy);
               //$this->removeProfiler('Tidy');
            }

            /*          //$this->addProfiler('htmLawed');
                        if (empty($cleanedHTML)) $cleanedHTML = $this->postinfo['page_content'];
                           $options = array(
                              'abs_url' => 1,
                              'comment' => 1,
                              'elements' => '*-script',
                              'keep_bad' => 0
                           );

                        $cleanedHTML = htmLawed($cleanedHTML, $options);
                        //$this->removeProfiler('htmLawed');
            */


            //$this->addProfiler('webReader');

            // If at this point we still have nothing (which is unlikely) fall back to original content
            if (empty($cleanedHTML)) $cleanedHTML = $this->postinfo['page_content'];

            // Remove article content using webReader
            $webReader = new webReader($cleanedHTML);
            $result = @$webReader->init();

            if ($result) {
               $this->postinfo['html_full'] = html_entity_decode($webReader->getContent()->innerHTML);
               $this->ab_logMsg('Original article extracted.', AB_LOG_DEBUG);
            } else {
               $this->ab_logMsg('Web Reader article extraction failed.', AB_LOG_DEBUG);
            }
            //$this->removeProfiler('webReader');
         }


         // Assign the appropriate excerpt source to the content we will be working with

         //$this->addProfiler('Excerpts');

         switch ($this->current_feed['excerptsource']) {

            // Content
            case 0:
               $content = $this->postinfo['content'];
               $this->ab_logMsg('Extracted content from feed.', AB_LOG_DEBUG);
               break;

            // Description
            case 1:
               $content = $this->postinfo['description'];
               $this->ab_logMsg('Extracted description from feed.', AB_LOG_DEBUG);
               break;

            // Full original article
            case 2:
               $content = $this->postinfo['html_full'];
               break;
         }
         //$this->removeProfiler('Excerpts');


         if ($this->current_feed['securitycheck']) {
            $options = array('safe' => 1,
                             'deny_attribute'=>'style',
                             'comment' => 0,
            );

            $content = htmLawed($content, $options);

         }

         // Do HTML filtering
         //$this->addProfiler('HTML Filtering');
         switch ($this->current_feed['excerpthtmlfilter']) {

            // Plain text
            case 0:
               $options = array(
                  'elements' => 'p br',
                  'keep_bad' => 0,
                  'comment' => 1,
                  'safe' => 1);
               $content = htmLawed($content, $options);
               $this->ab_logMsg('Filtered using plain text.', AB_LOG_DEBUG);
               break;

            // Basic
            case 1:
               $options = array(
                  'elements' => 'a, b, blockquote, br, code, em, font, h1, h2, h3, h4, h5, h6, hr, i, li, ol, p, pre, q, s, small, strike, strong, sub, sup',
                  'keep_bad' => 0,
                  'comment' => 1,
                  'safe' => 1);
               $content = htmLawed($content, $options);
               $this->ab_logMsg('Filtered all but basic formatting.', AB_LOG_DEBUG);
               break;

            // Remove unsafe
            case 2:
               $options = array(
                  'elements' => 'a, abbr, acronym, address, b, bdo, big, blockquote, br, caption, center, cite, code, col, colgroup, dd, del, dfn, dir, div, dl, dt, em, embed, fieldset, font, h1, h2, h3, h4, h5, h6, hr, i, iframe, img, ins, label, legend, li, noscript, ol, p, param, pre, q, s, samp, small, span, strike, strong, style, sub, sup, table, tbody, td, tfoot, th, thead, tr, tt, u, ul',
                  'keep_bad' => 0,
                  'comment' => 1,
                  'safe' => 1);
               $content = htmLawed($content, $options);
               $this->ab_logMsg('Filtered unsafe HTML.', AB_LOG_DEBUG);
               break;

            // Case none
            case 3:
               break;
         }
         //$this->removeProfiler('HTML Filtering');



         // Create the excerpt
         //$this->addProfiler('Creating excerpt');
         switch ($this->current_feed['excerpttype']) {

            // Short excerpt
            case '0':
               $content = $this->ab_getExcerpt($content);
               $this->ab_logMsg('Plain text excerpt created.', AB_LOG_DEBUG);
               break;
         }

         unset($webReader);
         $this->postinfo['excerpt'] = ab_convert_encoding(apply_filters('autoblogged_content', $content));
         //$this->removeProfiler('Creating excerpt');

         return true;
      }


      /**
       * @return bool
       */
      function ab_itemFilter() {

         // Check for globally blacklisted words
         $this->ab_logMsg('Checking for blacklisted words...', AB_LOG_DEBUG);
         foreach ($this->exclude_words as $word) {
            if (!stristr($this->postinfo['page_content'], $word) === false) {
               $this->ab_logMsg('Skipping post with blacklisted word: '.$word, AB_LOG_NOTICE, 'skip');
               $this->current_feed['skipped_posts']++;
               return false;
            }
         }

         // Perform per-feed filtering
         $this->ab_logMsg('Feed-level filtering...', AB_LOG_DEBUG);

         // Default to allowing the post through
         $filterpass = true;

         // None of these words
         if (strlen($this->current_feed['includenowords'])) {
            $filterpass = (ab_countItemsFound($this->postinfo['content'].' '.$this->postinfo['title'], $this->current_feed['nowords']) == 0);
            if (!$filterpass) {
               $this->ab_logMsg('Skipping post due to "None of these words" filter.', AB_LOG_NOTICE, 'skip');
               $this->current_feed['skipped_posts']++;
               return false;
            }
            else {
               $this->ab_logMsg('"None of these words" filter: no matches', AB_LOG_DEBUG);
            }
         }

         // All of these words
         if (strlen($this->current_feed['includeallwords']) && $filterpass == true) {
            $filterpass = (ab_countItemsFound($this->postinfo['content'].' '.$this->postinfo['title'], $this->current_feed['allwords'] >= count($this->current_feed['allwords'])));
            if (!$filterpass) {
               $this->ab_logMsg('Skipping post due to "All of these words" filter.', AB_LOG_NOTICE, 'skip');
               $this->current_feed['skipped_posts']++;
               return false;
            }
            else {
               $this->ab_logMsg('"All of these words" filter: no matches', AB_LOG_DEBUG);
            }
         }

         // Any of these words
         if (strlen($this->current_feed['includeanywords']) && $filterpass == true) {
            $filterpass = (ab_countItemsFound($this->postinfo['content'].' '.$this->postinfo['title'], $this->current_feed['anywords']) > 0);
            if (!$filterpass) {
               $this->ab_logMsg('Skipping post due to "Any of these ords" filter.', AB_LOG_NOTICE, 'skip');
               $this->current_feed['skipped_posts']++;
               return false;
            }
            else {
               $this->ab_logMsg('"Any of these words" filter: no matches', AB_LOG_DEBUG);
            }
         }

         // The exact phrase
         if (strlen($this->current_feed['includephrase']) && $filterpass == true) {
            $filterpass = (ab_countItemsFound($this->postinfo['content'].' '.$this->postinfo['title'], $this->current_feed['phrase']) > 0);
            if (!$filterpass) {
               $this->ab_logMsg('Skipping post due to "Exact phrase" filter.', AB_LOG_NOTICE, 'skip');
               $this->current_feed['skipped_posts']++;
               return false;
            }
            else {
               $this->ab_logMsg('"Exact phrase" filter: no matches', AB_LOG_DEBUG);
            }
         }

         // We passed all filters
         return true;
      }

      /**
       * Create an excerpt
       *
       * @param      $content
       * @param bool $textonly
       *
       * @internal param $content
       * @internal param bool $textonly
       *
       * @return boolean Return
       * @access   public
       */
      function ab_getExcerpt($content, $textonly = true) {
         global $ab_options;
         $excerpt = '';
         $excerpt_delim = array('#([\s,-;:\?\!\.]+)#', '#([\.\?\!]\s)#', '#</p>|<br\s?/>#');


         // Cleanup
         if ($textonly) $content = strip_tags($content);

         $content = str_replace('>', '> ', $content);
         $content = str_replace('[...]', '', $content);
         $content = preg_replace('/\s+/', ' ', $content);

         // Once again we need to convert encoding thanks to PHP
         $content = ab_convert_encoding($content);

         if (strlen($content)) {
            // Create excerpt to More tag if options set and HTML excerpt
            if ($this->current_feed['cutatmore']) {
               if (preg_match('%(.*)(?:<span[^>]*id[^>]*more[^>]*>|<a href[^>]*class\s*=\s*.more[^>]*>[^>]*</a>)%Ssi', $content, $matches)) {
                  return $matches[1];
               } else {

               }
            }

            if (!strlen($excerpt)) {
               $words = preg_split($excerpt_delim[$this->current_feed['excerptunit']].'uiS', $content, -1, PREG_SPLIT_DELIM_CAPTURE + PREG_SPLIT_NO_EMPTY);
               $wordcount = count($words);
               $words = array_slice((array)$words, 0, rand($this->current_feed['minexcerptlen'] * 2, $this->current_feed['maxexcerptlen'] * 2)); //doubled because we are capturing delimiters
               $excerpt = implode($excerpt_delim[$this->current_feed['excerptunit']], $words);
               if ($ab_options['excerpt_type'] == 0 && $wordcount > $this->current_feed['maxexcerptlen'] * 2) {
                  $words[] = '...';
               }
               $excerpt = implode('', $words);
               return $excerpt;
            }
         }
         else {
            // Return original content if excerpt failed
            return $content;
         }
      }

      /**
       * Get the item's date
       *
       * @return boolean Return
       * @access public
       */
      function ab_itemGetDate() {
         if ($this->current_feed['usefeeddate']) {

            // Date
            $date = strtotime($this->current_item->get_date());
            if ($this->current_item->get_date('Y') <= 1990) $date = time();  // In case get_date fails
            //if (is_numeric($date)) $date = date('Y-m-d H:i:s', $date);
            $this->ab_logMsg('Using date from feed: '.$date, AB_LOG_DEBUG);
            $this->postinfo['date'] = $date;
         }
         else {
            $date = time();
            $this->ab_logMsg('Using current date: '.$date, AB_LOG_DEBUG);
         }

         if ($this->current_feed['tzoffset'] <> 0) {
             $date += (3600 * $this->current_feed['tzoffset']);
         }

         $this->postinfo['date'] = date('Y-m-d H:i:s', $date);
         return true;
      }

      /**
       * @return bool
       */
      function ab_itemGetAuthor() {
         global $wpdb;
         switch ($this->current_feed['author']) {
            case AUTHOR_FROM_FEED:
               $this->ab_logMsg('Getting author from the feed.', AB_LOG_DEBUG);
               $feed_author = $this->current_item->get_author();

               if (!empty($feed_author->name)) {
                  $this->postinfo['author'] = $wpdb->escape($feed_author->get_name());
                  $this->postinfo['author_email'] = $wpdb->escape($feed_author->get_email());
                  $this->postinfo['author_url'] = $wpdb->escape($feed_author->get_link());
               }
               else {
                  $feed_author_info = $this->current_item->get_item_tags('', 'author');
                  if (!empty($feed_author_info[0]['child']['']['name'][0]['data'])) {
                     $this->postinfo['author'] = $feed_author_info[0]['child']['']['name'][0]['data'];
                     $this->postinfo['author_email'] = $feed_author_info[0]['child']['']['email'][0]['data'];
                     $this->postinfo['author_url'] = $feed_author_info[0]['child']['']['uri'][0]['data'];
                  }
               }

               if (empty($this->postinfo['author'])) {
                  $this->ab_logMsg('Feed item did not return an author.', AB_LOG_DEBUG);
               }
               else {
                  $this->ab_logMsg('Feed author: '.$this->postinfo['author'], AB_LOG_DEBUG);

                  // Find the author in WordPress
                  $user = $this->ab_findAuthor($this->postinfo['author'], $this->postinfo['author_email'], $this->postinfo['author_url']);
                  if ($user) {

                     // User exists in WordPress
                     $this->ab_logMsg('Feed author found in WordPress: ('.$user['ID'].')', AB_LOG_DEBUG);
                     $this->postinfo['author_id'] = $user['ID'];
                     if ($this->current_feed['useauthorinfo']) {

                        // Override feed data with that stored in WordPress user table
                        $this->postinfo['author_display_name'] = $user['display_name'];
                        $this->postinfo['author'] = $user['display_name'];
                        $this->postinfo['author_email'] = $user['user_email'];
                        $this->postinfo['author_url'] = $user['user_url'];
                        $this->postinfo['author_bio'] = $user['user_description'];
                     }
                  }
                  else {

                     // User not found
                     $this->ab_logMsg('Feed author not found in WordPress', AB_LOG_DEBUG);
                     switch ($this->current_feed['alt_author']) {
                        case SKIP_POST:
                           $this->ab_logMsg('Skipping post from unrecognized author.', AB_LOG_NOTICE, 'skip');
                           $this->current_feed['skipped_posts']++;
                           return false;
                           break;

                        case ADD_AUTHOR:
                           $this->ab_logMsg('Adding author...', AB_LOG_DEBUG);
                           $this->postinfo['author_email'] = 'nothing-'.rand(1, 99999999).'@example.com';

                           $adduser = array('user_login' => sanitize_user($this->postinfo['author']), 'user_nicename' => $this->postinfo['author'], 'user_email' => $this->postinfo['author_email'], 'user_url' => $this->postinfo['author_url'], 'role' => 'Subscriber', 'user_pass' => md5(uniqid(mt_rand(), true)));
                           if (function_exists('wp_insert_user')) {
                              $uid = wp_insert_user($adduser);
                              if (is_wp_error($uid)) $this->ab_logMsg('Error creating user', AB_LOG_DEBUG);
                           }
                           else {
                              require_once(ABSPATH.WPINC.'/registration.php');
                              if (!function_exists('wp_insert_user')) $this->ab_logMsg('Unable to insert user, incompatible WordPress version', AB_LOG_WARNING);
                           }
                           $this->postinfo['author_id'] = $uid;
                           break;

                        case RANDOM_AUTHOR:
                           $user = $this->ab_findAuthor();
                           $this->postinfo['author_id'] = $user['ID'];
                           $this->postinfo['author_display_name'] = $user['display_name'];
                           $this->postinfo['author'] = $user['display_name'];
                           $this->postinfo['author_email'] = $user['user_email'];
                           $this->postinfo['author_url'] = $user['user_url'];
                           $this->postinfo['author_bio'] = $user['user_description'];
                           $this->ab_logMsg('Using random author: '.$this->postinfo['author'], AB_LOG_DEBUG);
                           break;

                        default:
                           $user = $this->ab_findAuthor($this->current_feed['alt_author']);
                           $this->postinfo['author_id'] = $user['ID'];
                           $this->postinfo['author_display_name'] = $user['display_name'];
                           $this->postinfo['author'] = $user['display_name'];
                           $this->postinfo['author_email'] = $user['user_email'];
                           $this->postinfo['author_url'] = $user['user_url'];
                           $this->postinfo['author_bio'] = $user['user_description'];
                           break;
                     }
                  }
               }
               break;

            case RANDOM_AUTHOR:
               $user = $this->ab_findAuthor();
               $this->postinfo['author_id'] = $user['ID'];
               $this->postinfo['author_display_name'] = $user['display_name'];
               $this->postinfo['author'] = $user['display_name'];
               $this->postinfo['author_email'] = $user['user_email'];
               $this->postinfo['author_url'] = $user['user_url'];
               $this->postinfo['author_bio'] = $user['user_description'];
               $this->ab_logMsg('Using random author: '.$this->postinfo['author'], AB_LOG_DEBUG);
               break;

            default:
               $user = $this->ab_findAuthor($this->current_feed['author']);
               $this->postinfo['author_id'] = $user['ID'];
               $this->postinfo['author_display_name'] = $user['display_name'];
               $this->postinfo['author'] = $user['display_name'];
               $this->postinfo['author_email'] = $user['user_email'];
               $this->postinfo['author_url'] = $user['user_url'];
               $this->postinfo['author_bio'] = $user['user_description'];
               $this->ab_logMsg('Using author: '.$this->postinfo['author'], AB_LOG_DEBUG);
               break;
         }

         return true;
      }

      /**
       * Find author
       *
       * @param  string $login
       * @param  string $email
       * @param  string $uri
       *
       * @return boolean Return
       * @access public
       */
      function ab_findAuthor($login = null, $email = null, $uri = null) {
         global $wpdb;

         // If all parameters are empty, return a random author
         if (empty($login) && empty($email) && empty($uri)) {
            $sql = "SELECT * FROM $wpdb->users ORDER BY rand() LIMIT 1";
         }
         else {
            $sql = 'SELECT * FROM '.$wpdb->users.' WHERE ';
            if (!empty($login)) {
               $where = " `user_login` = '$login' OR `user_nicename` = '$login' OR `display_name` = '$login' ";
            }
            if (!empty($email)) {
               if (!empty($where)) $where .= 'OR ';
               $where .= "`user_email` = '$email' ";
            }
            if (!empty($uri)) {
               if (!empty($where)) $where .= 'OR ';
               $where .= "`user_url` = '$uri' ";
            }
         }

         // Execute the query
         $user = $wpdb->get_row($sql.$where, ARRAY_A);
         if (empty($user)) {
            return false;
         }
         else {
            return $user;
         }
      }

      /**
       * Get the copyright notice
       *
       * @return boolean Return
       * @access public
       */
      function ab_itemGetCopyright() {

         // Copyright
         $copyright = $this->current_item->get_copyright();
         if (is_object($copyright)) {
            $this->postinfo['copyright'] = $copyright->get_attribution();
            $this->postinfo['copyright_url'] = $copyright->get_url();
         }
         return true;
      }

      /**
       * Get Item Source
       *
       * @return boolean Return
       * @access public
       */
      function ab_itemGetSource() {
         global $rss;
         $linkmatch = '';
         $this->postinfo['source'] = $rss->get_title();
         $this->postinfo['source_url'] = $rss->get_permalink();
         $this->postinfo['source_description'] = $rss->get_description();
         $this->postinfo['icon'] = $rss->get_favicon();
         $this->postinfo['logo_url'] = $rss->get_image_url();
         $this->postinfo['logo_link'] = $rss->get_image_link();
         $this->postinfo['logo_title'] = $rss->get_image_title();

         // Pull extra info from blogroll if that option is selected
         if ($this->current_feed['uselinkinfo']) {
            foreach ($this->bookmarks as $bookmark) {
               if (stristr($this->postinfo['link'], str_replace('http://', '', $bookmark->link_url))) {
                  if (strlen($bookmark->link_url) > strlen($linkmatch->link_url)) $linkmatch = $bookmark;
               }
               if (stristr(str_replace('http://', '', $bookmark->link_url), str_replace('http://', '', $this->postinfo['link']))) {
                  if (strlen($bookmark->link_url) > strlen($linkmatch->link_url)) $linkmatch = $bookmark;
               }
            }
            if ($linkmatch) {
               $this->postinfo['source_url'] = $this->postinfo['link'];
               $this->postinfo['source'] = $linkmatch->link_name;
               $this->postinfo['logo_url'] = $linkmatch->link_image;
               $this->postinfo['source_description'] = $linkmatch->link_description;
            }
         }
         return true;
      }

      /**
       * Get Attachments
       *
       * @return boolean Return
       * @access public
       */
      function ab_itemGetAttachments() {
         global $ab_options;
         global $wpdb;

         // Images and video
         $this->ab_logMsg('Processing images and video...', AB_LOG_DEBUG);
         $enclosures = $this->current_item->get_enclosures();
         $image_urls = array();
         $enclosure_tags = array();
         $this->postinfo['attachments'] = array();
         $this->ab_logMsg('Attachments found: '.count($enclosures), AB_LOG_DEBUG);

         // Get images from all fields
         $this->ab_logMsg('Searching all fields for images...', AB_LOG_DEBUG);
         require_once ABSPATH.'/wp-admin/includes/image.php';
         foreach (array_keys($this->postinfo) as $field) {
            if ($field <> 'page_content') {
               if (is_array($this->postinfo[$field])) {
                  preg_match_all('%img[^>]*src\s*=\s*[\'"]([^"\'\s]*)|(http://[^"<:]{5,255}\.(?:jpg|jpeg|gif|png))%', htmlspecialchars_decode(implode(' ', $this->postinfo[$field])), $extractedimageurls);
               }
               else {
                  if (is_string($this->postinfo[$field])) preg_match_all('%img[^>]*src\s*=\s*[\'"]([^"\'\s]*)|(http://[^"<:]{5,255}\.(?:jpg|jpeg|gif|png))%', htmlspecialchars_decode($this->postinfo[$field]), $extractedimageurls);
               }

               if (is_array($extractedimageurls[1])) {
                  foreach ($extractedimageurls[1] as $extractedimageurl) {
                     if (!empty($extractedimageurl)) {
                        $image_urls = array_merge((array)$image_urls, (array)$extractedimageurl);
                     }
                  }
               }
            }
         }

         // Add any media:thumbnail elements
         $elements = $this->current_item->get_item_tags('http://search.yahoo.com/mrss/', 'group');
         $thumbnails = $elements[0]['child']['http://search.yahoo.com/mrss/']['thumbnail'];
         if (is_array($thumbnails)) {
            foreach ($thumbnails as $thumbnail) {
               $media_thumbnails[] = $thumbnail['attribs']['']['url'];
            }
         }

         // Same thing but not using the group tag
         $thumbnails = $this->current_item->get_item_tags('http://search.yahoo.com/mrss/', 'thumbnail');
         if (is_array($thumbnails)) {
            foreach ($thumbnails as $thumbnail) {
               if (isset($thumbnail['attribs']['']['url'])) $media_thumbnails[] = $thumbnail['attribs']['']['url'];
            }
         }

         if (isset($media_thumbnails)) if (is_array($media_thumbnails)) $image_urls = array_merge($image_urls, $media_thumbnails);

         // Step through the enclosures
         if (is_array($enclosures)) {
            $j = 0;
            foreach ($enclosures as $enclosure) {
               $j++;

               // Get additional tags from each enclosure
               if ($ab_options['feedtags']) {
                  $kw = $enclosure->get_keywords();
                  if (is_array($kw)) $enclosure_tags = array_merge($enclosure_tags, $kw);
                  $enc_cats = $enclosure->get_categories();
                  if (is_array($enc_cats)) {
                     foreach ($enc_cats as $enc_cat) {
                        $enclosure_tags[] = $enc_cat->get_label();
                     }
                  }
                  if (is_array($enclosure_tags)) array_unique($enclosure_tags);
               }
               $enc_link = $enclosure->get_link();
               $enc_type = $enclosure->get_type();
               if (stristr($enc_type, "image")) {
                  $image_urls[] = $enc_link;
               }
               else {

                  // Extract the video
                  $enc_link = apply_filters('autoblogged_video_attachment', $enc_link, $this->postinfo);

                  if (!empty($enc_link)) {

                     $this->ab_logMsg('Adding video attachment: '.$enc_link, AB_LOG_DEBUG);
                     $vid_embed = ab_getEmbeddedVideo($enc_link, $this->current_feed['playerwidth'], $this->current_feed['playerheight'], $enclosure->get_handler(), $this->postinfo['link']);
                     if ($j == 1) $this->postinfo['video'] = $vid_embed.' ';
                     $this->postinfo['videos'][] = $vid_embed;
                     if (!empty($enc_link)) $this->postinfo['video_urls'][] = $enc_link;
                  }
               }
               $this->postinfo['video_url'] = $this->postinfo['video_urls'][0];
            }
         }

         // Grab additional embedded videos
         $vids = $this->current_item->get_item_tags('http://search.yahoo.com/mrss/', 'player');
         if (is_array($vids)) {
            foreach ($vids as $vid) {
               $media_thumbnails[] = $vid['attribs']['']['url'];
            }
         }
         if (is_array($image_urls)) {
            $image_urls = array_unique($image_urls);

            /* Special handling for YouTube images
				*  YouTube includes three small screencap thumbnails and one large HQ thumbnail
				*  We will put the HQ thumbnail first
				*/
            foreach ($image_urls as $image) {
               if (stristr($image, 'hqdefault')) {
                  array_unshift($image_urls, $image);
                  $image_urls = array_unique($image_urls);
               }
            }

            // Add image attachments if there are any
            $this->ab_logMsg('Adding image attachments...', AB_LOG_DEBUG);
            foreach ($image_urls as $image) {

               // Skip these images
               if (preg_match('#(icn_star|bullet|dot|blank|spacer|star|rating|adsvr|tracking|quantserve)\.(gif|jpg|jpeg|png)#i', $image)) continue;


               // Stars, icons, spacers, blanks, etc.
               if ($image == $this->postinfo['logo_url']) continue;

               // Skip the feed's logo image
               if (strlen($image) > 255) continue; // Very long image paths
               if (!preg_match('/(https?|ftp):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|]/i', $image)) continue; // Not a url
               if (stristr($image, 'feeds.feedburner.com/~ff')) continue; // Skip FeedBurner feedflares
               if (stristr($image, 'feeds.feedburner.com/~r')) continue; // Skip FeedBurner feedflares
               if (preg_match('#addtomyyahoo4|ngsub1|myAOLButtonSmall|sub_modern11|add2netvibes|add\.gif|ATP_blu_91x17#i', $image)) continue; // Subscription icons
               if (stristr($image, 'feedads.g.doubleclick.net')) continue; // Skip google ads
               if (stristr($image, 'blogger.googleusercontent.com')) continue; // Skip google tracker
               if (stristr($image, 'button')) continue; // Skip buttons

               $attachment_info = array();

               // Only need to do this if we are saving images or creating thumbs
               if ($this->current_feed['saveimages'] || $this->current_feed['createthumbs']) {
                  $upload = array();
                  $this->ab_logMsg('Processing image: '.$image, AB_LOG_DEBUG);

                  // First check to see if we already have the image cached
                  $imageurl = parse_url($image);
                  $pathinfo = pathinfo($imageurl['path']);
                  $x = preg_match('#[^/]*\.('.AB_ATTACH_EXT.')#', $imageurl['path'], $matches);
                  if ($x) {
                     $basename = $matches[0];
                  }
                  else {
                     $basename = basename($imageurl['path']);
                  }

                  $basename = sanitize_file_name(substr($basename, -50));
                  $basename = preg_replace('/[^a-zA-Z0-9-_\.]/', '', $basename);
                  // The MD5 function allows using images of the same filename from different sites\
                  // E.g., example.com/logo.jpg would have a different local filename than example.net/logo.jpg
                  // Only using 4 characters from the hash but that should be enough to prevent collision
                  $filehash = substr(md5($image), -4).$basename;

                  $this->ab_logMsg('Cache filename: '.$filehash, AB_LOG_DEBUG);

                  if (file_exists($this->upload_dir.'/'.$filehash)) {
                     // Found in cache
                     $this->ab_logMsg('File exists in cache.', AB_LOG_DEBUG);
                     $the_url = $this->upload_url.'/'.$filehash;
                     $the_file = $this->upload_dir.'/'.$filehash;
                     $this->postinfo['content'] = str_replace($image, $the_url, $this->postinfo['content']);

                     // Find the existing attachment ID
                     $check_name = basename(dirname($this->upload_dir)).'/'.basename($this->upload_dir).'/'.$filehash;
                     if (!empty($attach_id)) {
                        $attach_id = $wpdb->get_var("SELECT ID FROM $wpdb->posts WHERE post_type = 'attachment' AND guid like '%".$check_name."'");
                        $this->ab_logMsg('Adding existing attachment ID '.$attach_id, AB_LOG_DEBUG);
                        $this->postinfo['attachment_ids'][] = $attach_id;
                        continue;
                     }
                  }
                  else {

                     // Grab the original image
                     unset($upload);
                     $upload = ab_httpFetch($image, $this->postinfo['link']);

                     // Make sure we actually got something
                     if ($upload['headers']['status'] >= 400) {
                        $this->ab_logMsg('Unable to retrieve image ('.$upload['headers']['status'].'): '.$image, AB_LOG_WARNING);
                        continue;
                     }

                     // Check image size
                     if (is_numeric($upload['headers']['content-length']) && $upload['headers']['content-length'] <= 75) {
                        $this->ab_logMsg('Skipping small image (content-length: '.$upload['headers']['content-length'].')', AB_LOG_DEBUG);
                        continue;
                     }

                     // Special handling for blogger.com, blogspot.com, wikipedia.com, etc.
                     if (stristr($upload['headers']['content-type'], 'text')) {
                        $this->ab_logMsg('Searching for image in '.$upload['headers']['content-type'].' content...', AB_LOG_DEBUG);
                        if (preg_match('/<img[^>]*src="([^"]*)"/i', $upload['content'], $matches)) {

                           // If we found an image in the text, try it again
                           $this->ab_logMsg('Retrying URL: '.$matches[1], AB_LOG_DEBUG);
                           $urlParsed = parse_url($matches[1]);
                           $upload = ab_httpFetch($matches[1], $urlParsed['host']);
                        }
                        else {
                           $this->ab_logMsg('Server did not return a valid image for the URL '.$image, AB_LOG_WARNING);
                           continue;
                        }
                     }

                     // Check again to make sure we are dealing with an image
                     if (!empty($upload['headers']['content-type']) && !stristr($upload['headers']['content-type'], 'image')) {

                        // Additional check of the actual content
                        $header = substr($upload['content'], 0, 10);
                        $ext = '';
                        if (stristr($header, 'GIF8')) $content_type = 'image/gif';
                        if (stristr($header, 'PNG')) $content_type = 'image/png';
                        if (stristr($header, 'JFIF')) $content_type = 'image/jpeg';
                     }
                     else {
                        $content_type = $upload['headers']['content-type'];
                     }
                     if (stristr($content_type, 'jpeg')) $ext = '.jpg';
                     if (stristr($content_type, 'png')) $ext = '.png';
                     if (stristr($content_type, 'gif')) $ext = '.gif';
                     if (empty($ext)) {
                        $this->ab_logMsg('Server did not return valid image type ('.$upload['headers']['content-type'].') for '.$image, AB_LOG_WARNING);
                        continue;
                     }

                     // Now that we know for sure the content type, add an extension if necessary
                     if (!stristr($pathinfo['extension'], AB_ATTACH_EXT)) {
                        $filehash .= $ext;
                        // Remove multiple extensions
                        $filehash = preg_replace('#\.('.AB_ATTACH_EXT.')(\.\1)+#si', '.$1', $filehash);
                        if (file_exists($this->upload_dir.'/'.$filehash)) {
                           // Found in cache
                           $this->ab_logMsg('File found in cache.', AB_LOG_DEBUG);
                           $the_url = $this->upload_url.'/'.$filehash;
                           $the_file = $this->upload_dir.'/'.$filehash;
                           $this->postinfo['content'] = str_replace($image, $the_url, $this->postinfo['content']);

                           // Find the existing attachment ID
                           $attach_id = $wpdb->get_var("SELECT ID FROM posts WHERE post_type = 'attachment' AND guid like %".$this->upload_dir.'/'.$filehash);
                           if (!empty($attach_id)) {
                              $this->ab_logMsg('Adding existing attachment ID '.$attach_id, AB_LOG_DEBUG);
                              $this->postinfo['attachment_ids'][] = $attach_id;
                              continue;
                           }
                        }
                     }
                     /* Save the image locally
							*   Create an empty placeholder file in the upload dir
							*   returns array with 'file', 'url', and 'error'
							*/
                     $result = wp_upload_bits($filehash, 0, '');
                     if ($result['error']) {
                        $this->ab_logMsg('Unable to write to upload directory: '.$result['error'], AB_LOG_WARNING);
                        $this->postinfo['error'] .= "Unable to write to upload directory.\r\n";
                        $the_url = $image;
                        continue;
                     }

                     // Create a handle to the destination file
                     $fp = @fopen($result['file'], 'w');
                     if (!$fp) {
                        $this->ab_logMsg('Unable to save image to upload directory.', AB_LOG_WARNING);
                        $this->postinfo['error'] .= "Unable to save image to upload directory.\r\n";
                        $the_url = $image;
                        continue;
                     }

                     // Write the file
                     fwrite($fp, $upload['content']); // Save the image file to disk
                     @fclose($fp);
                     $this->ab_logMsg('Image saved locally at '.$result['url'], AB_LOG_DEBUG);
                     if ($this->current_feed['saveimages'] || (($this->current_feed['saveimages'] == 2 && count($this->postinfo['attachments']) < 1))) {
                        $the_url = $result['url'];
                        $this->postinfo['content'] = str_replace($image, $result['url'], $this->postinfo['content']);
                        $attachment_info = array();
                        $attachment_info['post_title'] = 'Image '.sanitize_file_name(basename($imageurl['path']));
                        $attachment_info['post_content'] = '';
                        $attachment_info['post_status'] = strtolower($this->current_feed['poststatus']);
                        $attachment_info['post_mime_type'] = $content_type;
                        $attachment_info['guid'] = $result['url'];
                        $this->postinfo['attachments'][$result['file']] = $attachment_info;
                     }
                     else {
                        $the_url = $image;
                     }
                     $the_file = $result['file'];
                  }
                  $this->postinfo['images'][] = '<img src="'.$the_url.'" class="alignleft" />';
                  $this->postinfo['image_urls'][] = $the_url;
                  $parse_url = parse_url($the_url);
                  $this->postinfo['image_paths'][] = $parse_url['path'];

                  // Now create a thumbnail for it and get the thumbnail's path
                  if ($this->current_feed['createthumbs']) {
                     $this->ab_logMsg('Creating thumbnail for '.$the_file.'...', AB_LOG_DEBUG);
                     $thumbpath = image_resize($the_file, get_option('thumbnail_size_w'), get_option('thumbnail_size_h'), get_option('thumbnail_crop'));
                     if ($thumbpath) {
                        if (is_string($thumbpath)) {
                           $postdata['guid'] = str_replace(basename($the_file), basename($thumbpath), $result['url']);
                           $attachment_info = array();
                           $attachment_info['post_title'] = 'Thumbnail';
                           $attachment_info['post_content'] = '';
                           $attachment_info['post_status'] = strtolower($this->current_feed['poststatus']);
                           $attachment_info['post_mime_type'] = $content_type;
                           $attachment_info['guid'] = $thumbpath;
                           $this->postinfo['attachments'][$thumbpath] = $attachment_info;
                           $this->ab_logMsg('Thumbnail created at  '.$thumbpath, AB_LOG_DEBUG);

                           // Kill the original file if the option is not set to save
                           if (!$this->current_feed['saveimages']) @unlink($the_file);
                        }
                        else {
                           if (is_wp_error($thumbpath)) $this->ab_logMsg('WordPress notice: '.$thumbpath->get_error_message(), AB_LOG_DEBUG);

                           // use the image itself as the url if we have an error here
                           $thumbpath = $the_url;
                           $postdata['guid'] = $the_url;
                        }
                     }
                     else {

                        // The image is small enough to be its own thumbnail
                        $thumbpath = $the_url;
                        $postdata['guid'] = $the_url;
                     }
                     $this->postinfo['thumbnails'][] = '<img src="'.$postdata['guid'].'" />';
                     $this->postinfo['thumbnail_urls'][] = $postdata['guid'];
                     $url_parsed = parse_url($thumbpath);
                     $this->postinfo['thumbnail_paths'][] = stristr($url_parsed['path'], '/wp-content');
                  }
               }
               else {
                  $this->postinfo['images'][] = '<img src="'.$image.'" />';
                  ;
                  $this->postinfo['image_urls'][] = $image;
               }
               if ($this->current_feed['saveimages'] == 2 && count($this->postinfo['images']) >= 1) {
                  break;
               }
            }

            if (isset($this->postinfo['images'])) $this->postinfo['image'] = $this->postinfo['images'][0];
            if (isset($this->postinfo['image_paths'])) $this->postinfo['image_path'] = $this->postinfo['image_paths'][0];
            if (isset($this->postinfo['image_urls'])) $this->postinfo['image_url'] = $this->postinfo['image_urls'][0];
            if (isset($this->postinfo['thumbnails'])) $this->postinfo['thumbnail'] = $this->postinfo['thumbnails'][0];
            if (isset($this->postinfo['thumbnail_paths'])) $this->postinfo['thumbnail_path'] = $this->postinfo['thumbnail_paths'][0];
            if (isset($this->postinfo['thumbnail_urls'])) $this->postinfo['thumbnail_url'] = $this->postinfo['thumbnail_urls'][0];

            // Add extra image fields if option specified
            if (EXTRA_IMAGE_FIELDS) {
               for ($j = 0; $j <= 5; $j++) {
                  $this->postinfo['image'.'_'.$j] = $this->postinfo['images'][$j];
                  $this->postinfo['thumbnail'.'_'.$j] = $this->postinfo['thumbnails'][$j];
                  $this->postinfo['video'.'_'.$j] = $this->postinfo['videos'][$j];
               }
            }
         }
         return true;
      }


      /**
       * Get categories and tags
       *
       * @return boolean Return
       * @access public
       */
      function ab_itemGetCategoriesAndTags() {
         global $ab_options, $notags;
         $this->ab_logMsg('Building categories and tags...', AB_LOG_DEBUG);

         // Clear out any keywords and tags from previous item
         $feed_tags = array();
         $original_post_tags = array();
         $more_categories = array();
         $enclosure_tags = array();
         $this->current_feed['tags_list'] = array();

         // Grab tags from feed
         if (count($this->current_item->get_categories()) > 0) {
            if ($ab_options['feedtags']) {
               foreach ($this->current_item->get_categories() as $cat) {
                  $feed_tags[] = $cat->get_label();
               }
            }
         }
         if (count($feed_tags) > 0) $this->ab_logMsg('Tags from feed: '.implode(', ', $feed_tags), AB_LOG_DEBUG);


         // Add categories from original source
         //$this->addProfiler('Categories from source');
         $original_categories = array();
         if ($this->current_feed['usepostcats'] == 1) {
            $source_cats = $this->current_item->get_categories();
            if (count($source_cats)) {
               foreach ($source_cats as $category) {
                  if (strlen($category->get_label()) < $ab_options['maxtaglen']) {
                     if ($this->current_feed['addpostcats'] == 0) {
                        /** @noinspection PhpDeprecationInspection */
                        if (is_term($category->get_label(), 'category') == 0) continue;
                     }
                     $original_categories[] = $category->get_label();
                  }
               }
            }
            //$this->removeProfiler('Categories from source');
         }

         if (count($original_categories) > 0) $this->ab_logMsg('Categories from feed: '.implode(', ', $original_categories), AB_LOG_DEBUG);

         // Add all or random categories set by user
         $newcategories = array();
         $feedcategory_ids = ab_unserialize($this->current_feed['category']);
         if (is_array($feedcategory_ids)) {
            shuffle($feedcategory_ids);
         }
         else {
            $feedcategory_ids[0] = get_option('default_category');
         }

         $newcategories[] = get_term_field('name', $feedcategory_ids[0], 'category');
         $this->ab_logMsg('Main category: '.$newcategories[0], AB_LOG_DEBUG);
         if (count($feedcategory_ids) > 0) {
            for ($i = 1; $i <= count($feedcategory_ids) - 1; $i++) {
               if ($this->current_feed['randomcats'] == 0 || (rand(0, 2) == 0)) {
                  $newcategories[] = get_term_field('name', $feedcategory_ids[$i], 'category');
                  $this->ab_logMsg('Additional category: '.$newcategories[count($newcategories) - 1], AB_LOG_DEBUG);
               }
            }
         }

         // Add blog categories as tags or additional categories if they exist in the post
         if (($this->current_feed['addcatsastags'] == true) || ($this->current_feed['addothercats'] == true)) {
            //$this->addProfiler('Blog categories as tags');
            $more_categories = array();
            foreach ($this->categories as $cat) {
               if ($cat->name) {
                  if ((stristr($this->postinfo['page_content'], $cat->name)) || (stristr($this->postinfo['content'], $cat->name))) {
                     if ($this->current_feed['addcatsastags'] == true) {
                        $feed_tags[] = $cat->name;
                     }
                     if ($this->current_feed['addothercats'] == true) {
                        $more_categories[] = $cat->name;
                     }
                  }
               }

               // Try again but with the slug
               if ($cat->slug) {
                  if ((stristr($this->postinfo['page_content'], $cat->slug)) || (stristr($this->postinfo['content'], $cat->slug))) {
                     if ($this->current_feed['addcatsastags'] == true) {
                        $feed_tags[] = $cat->name;
                     }
                     if ($this->current_feed['addothercats'] == true) {
                        $more_categories[] = $cat->name;
                     }
                  }
               }
            }
            //$this->removeProfiler('Blog categories as tags');
         }

         // Put them all together
         $this->current_feed['feedcategories'] = array_merge($original_categories, $newcategories, $more_categories);

         // Temporary hack for v2
         $object_item = array('Object');
         $this->current_feed['feedcategories'] = array_diff($this->current_feed['feedcategories'], $object_item);

         // Randomly add additional tags from global and per-feed lists
         $num = rand(0, min((count($this->global_extra_tags) + count($this->current_feed['feed_extra_tags']) / 2), 4));
         for ($i = 0; $i <= $num; $i++) {
            if (isset($this->global_extra_tags)) if (is_array($this->global_extra_tags)) $feed_tags[] = $this->global_extra_tags[array_rand((array)$this->global_extra_tags)];
         }
         for ($i = 0; $i <= $num; $i++) {
            if ($this->current_feed['feed_extra_tags']) $feed_tags[] = $this->current_feed['feed_extra_tags'][array_rand($this->current_feed['feed_extra_tags'], 1)];
         }
         $feed_tags = array_unique($feed_tags);
         if (count($feed_tags) > 0) $this->ab_logMsg('Tags after adding global tags: '.implode(', ', $feed_tags), AB_LOG_DEBUG);

         // Add tags based on the original post
         //$this->addProfiler('Tags from original post');
         if ($ab_options['taggingengine']) {
            if (!empty($this->postinfo['html_full'])) {
            }
            $original_post_tags = $this->ab_getKeywords($this->postinfo['html_full']);
         }
         else {
            $original_post_tags = $this->ab_getKeywords($this->postinfo['page_content']);
         }
         //$this->removeProfiler('Tags from original post');

         // Grab Yahoo Tags
         $yhkeywords = array();
         if ($ab_options['yahootags']) {
            $getyahootags = $this->ab_getYahooTags($this->postinfo['content']);
            if ($getyahootags) $this->ab_matchKeywords($yhkeywords, $getyahootags, '#\w*#', YAHOO_TAGS_WEIGHT, 0);
            if (isset($yhkeywords)) {
               arsort($yhkeywords);
               $yhkeywords = array_slice($yhkeywords, 0, $ab_options['maxtags'] * 1.5);
               $yahootags = array_keys($yhkeywords);
            }
         }
         if (count($original_post_tags)) $this->ab_logMsg('Tags from original post: '.implode(', ', (array)$original_post_tags), AB_LOG_DEBUG);
         if (count($original_post_tags)) $feed_tags = array_merge((array)$feed_tags, (array)$original_post_tags, (array)$enclosure_tags);
         if (isset($yahootags)) if (count($yahootags) > 0) $feed_tags = array_merge($feed_tags, $yahootags);


         //$this->addProfiler('Tag cleanup');
         // Load notags.txt if not already loaded
         if (count($notags) == 0) {

            // Load notags
            //$this->addProfiler('Notags.txt checking');
            $fd = fopen(dirname(__FILE__)."/notags.txt", "r");
            if ($fd) {
               $notags1 = explode("\n", fread($fd, filesize((dirname(__FILE__)."/notags.txt"))));
               fclose($fd);
               foreach ($notags1 as $tag) {
                  if (strlen($tag) == 0 || substr($tag, 0, 1) == "#") continue;
                  $notags[] = $tag;
               }
            }
            //$this->removeProfiler('Notags.txt checking');
         }
         if (count($feed_tags) > 0) $this->ab_logMsg('Tags before cleanup: '.implode(', ', $feed_tags), AB_LOG_DEBUG);

         // Clean up the tags
         $this->ab_logMsg('Tag cleanup...', AB_LOG_DEBUG);
         if (is_array($feed_tags)) {
            foreach ($feed_tags as $post_tag) {
               $post_tag = trim($post_tag);
               $flagged = false;
               $i = 0;
               if (in_array($post_tag, $this->filtered_tags)) {
                  continue;
               }
               else {
                  if (strlen($post_tag) < $ab_options['mintaglen']) {
                     $flagged = true;
                     continue;
                  }
                  if (strlen($post_tag) > $ab_options['maxtaglen']) {
                     $flagged = true;
                     continue;
                  }
                  foreach ($notags as $pattern) {
                     $i++;
                     if (preg_match('/'.$pattern.'/ism', $post_tag)) {
                        $flagged = true;
                        continue 2;
                     }
                  }
               }
               if ($flagged == false) {
                  $this->current_feed['tags_list'][] = strtolower($post_tag);
               }
            }
         }
         //$this->removeProfiler('Tag cleanup');

         if (is_array($this->current_feed['tags_list'])) {
            shuffle($this->current_feed['tags_list']);
            $this->current_feed['tags_list'] = array_slice($this->current_feed['tags_list'], 0, $ab_options['maxtags'] - rand(3, $ab_options['maxtags'] / 2));
            $this->ab_logMsg('Final tags list: '.implode(', ', $this->current_feed['tags_list']), AB_LOG_DEBUG);
         }

         $this->current_feed['tags_list'] = apply_filters('autoblogged_tags', $this->current_feed['tags_list'], $this->postinfo);
         $this->current_feed['feedcategories'] = apply_filters('autoblogged_categories', $this->current_feed['feedcategories'], $this->postinfo);

         return true;
      }

      /**
       * Get custom taxonomies
       *
       * @return boolean Return
       * @access public
       */
      function ab_itemGetCustomTaxonomies() {
         if (isset($this->current_feed['customTaxonomies']['taxonomy'])) {
            if (is_array($this->current_feed['customTaxonomies']['taxonomy'])) {
               for ($i = 0; $i < count($this->current_feed['customTaxonomies']['taxonomy']); $i++) {
                  $customTax = $this->current_feed['customTaxonomies']['taxonomy'][$i];
                  $this->postinfo['customTaxonomies'][$customTax] = $this->ab_applyTemplate($this->current_feed['customTaxonomies']['value'][$i]);

                  $this->ab_logMsg('Setting '.$this->current_feed['customTaxonomies']['taxonomy'][$i].' to "'.$this->postinfo['customTaxonomies'][$customTax].'"', AB_LOG_DEBUG);
               }
            }
         }
         return true;
      }


      /**
       * Get custom fields
       *
       * @return boolean Return
       * @access public
       */
      function ab_itemGetCustomFields() {

         // Custom Fields
         $customfields = array();
         $this->ab_logMsg('Custom fields...', AB_LOG_DEBUG);

         if (is_array($this->current_feed['customFields'])) {
            foreach (array_keys($this->current_feed['customFields']) as $fieldItem) {
               $new = $this->ab_applyTemplate($this->current_feed['customFields'][$fieldItem]);
               $customfields[$fieldItem] = $new;
               $this->ab_logMsg('Setting '.$fieldItem.' to "'.$new.'"', AB_LOG_DEBUG);
            }
            if (is_array($customfields)) {
               $this->postinfo = array_merge($this->postinfo, $customfields);
            }
         }
         return true;
      }


      /**
       * Do search and replace
       *
       * @return boolean Return
       * @access public
       */
      function ab_itemDoSearchReplace() {

         // Search and replace
         $this->ab_logMsg('Search and replace...', AB_LOG_DEBUG);
         if ($this->current_feed['search']) {
            foreach (array_keys($this->postinfo) as $postfield) {
               if (!empty($this->postinfo[$postfield])) {
                  $i = 0;
                  foreach ($this->current_feed['search'] as $pattern) {
                     $ret = preg_match("/!!(\d)!!/", $pattern, $matches);
                     if ($ret) {
                        $searchtype = $matches[1];
                        $realpattern = str_replace($matches[0], '', $pattern);
                     }
                     else {
                        $searchtype = 0;
                        $realpattern = $pattern;
                     }
                     if ($searchtype == 0) {

                        // Regex search
                        if (preg_match('/([^a-zA-Z0-9]).*\1[ismx]/', stripslashes($realpattern), $z)) {
                           // Separater is already included
                           $ret = @preg_replace(stripslashes($realpattern), $this->current_feed['replace'][$i], $this->postinfo[$postfield]);
                        }
                        else {
                           $ret = @preg_replace('~'.stripslashes($realpattern).'~i', $this->current_feed['replace'][$i], $this->postinfo[$postfield]);
                        }

                        if ($ret) {
                           if (is_array($ret)) {
                              foreach ($ret as $retitem) {
                                 $retitem = $this->ab_applyTemplate($retitem);
                              }
                           }
                           else {
                              $ret = $this->ab_applyTemplate($ret);
                           }
                           $this->postinfo[$postfield] = $ret;
                        }
                        else {
                           // $this->ab_logMsg('Error: Invalid regular expression: '.htmlentities(stripslashes($pattern)), 'stop');
                        }
                     }
                     else {

                        // Plaintext search
                        if (is_array($this->postinfo[$postfield])) {
                           foreach ($this->postinfo[$postfield] as $apostfield) {
                              if (!is_array($postfield)) {
                                 // Skip 2nd-level arrays
                                 $apostfield = str_ireplace($realpattern, $this->current_feed['replace'][$i], $apostfield);
                                 $apostfield = $this->ab_applyTemplate($apostfield);
                                 $this->postinfo[$postfield] = $apostfield;
                              }
                           }
                        }
                        else {
                           $this->postinfo[$postfield] = str_ireplace($realpattern, $this->current_feed['replace'][$i], $this->postinfo[$postfield]);
                           $this->postinfo[$postfield] = $this->ab_applyTemplate($this->postinfo[$postfield]);
                        }
                     } //end if
                     $i++;
                  } //end foreach
               } //end if
            }
         }
         return true;
      }

      /**
       * Add the post
       *
       * @return boolean Return
       * @access public
       */
      function ab_itemAddPost() {
         $this->ab_logMsg('Adding post...', AB_LOG_DEBUG);

         // Add fields to post array
         $post = array();
         $post['post_content'] = $this->postinfo['post'];
         $post['post_title'] = $this->postinfo['title'];
         $post['post_excerpt'] = $this->postinfo['excerpt'];
         $post['post_date'] = $this->postinfo['date'];
         $post['post_status'] = strtolower($this->current_feed['poststatus']);

         // Custom post type
         global $wp_version;
         if (version_compare($wp_version, '2.9', '>=')) {
            $post['post_type'] = $this->current_feed['posttype'];
            $this->ab_logMsg('Setting post type to '.$this->current_feed['posttype'], AB_LOG_DEBUG);
         }

         // Set the author
         $post['post_author'] = $this->postinfo['author_id'];
         foreach (array_keys($this->postinfo) as $postfield) {
            if (strlen($postfield)) {

               // Include any WordPress built-in field names
               if (stristr('post_author|post_date|post_date_gmt|post_content|post_title|post_category|post_excerpt|post_status|comment_status|ping_status|post_password|post_name|to_ping|pinged|post_modified|post_modified_gmt|post_content_filtered|post_parent|guid|menu_order|post_type|post_mime_type', $postfield)) {

                  // Special handling for author field
                  if ($postfield == 'author') {
                     /** @noinspection PhpDeprecationInspection */
                     $user = get_userdatabylogin($this->postinfo['author']);
                     $uid = $user->ID;
                     if ($uid) {
                        $userdata = get_userdata($uid);
                        $post['post_author'] = $uid;
                     }
                     else {
                        // Add the user?
                     }
                  }
                  else {
                     $post[$postfield] = $this->postinfo[$postfield];
                  }
               }
            }
         }

         // Customization for specific themes
         $themedata = get_theme(get_current_theme());
         if (stristr($themedata['Description'], 'gabfire')) {

            // === GabFire Themes
            $this->ab_logMsg('Creating custom fields for GabFire Theme', AB_LOG_DEBUG);
            if (isset($this->postinfo['image_url'])) $this->postinfo['thumbnail'] = $this->postinfo['image_url'];
            if (isset($this->postinfo['video_url'])) $this->postinfo['video'] = $this->postinfo['video_url'];
         }
         elseif (function_exists('p75_videoPosting') && stristr($themedata['Author'], 'press75')) {

            // === Newer Press75 Themes
            if (isset($this->postinfo['video_url'])) $this->postinfo['_videoembed'] = $this->postinfo['video_url'];
         }
         else {
            switch ($themedata['Name']) {

               // === Elegant Themes

               case 'eNews':
                  $this->ab_logMsg('Creating custom fields for eNews Theme', AB_LOG_DEBUG);
                  if (isset($this->postinfo['thumbnail_path'])) $this->postinfo['Thumbnail'] = $this->postinfo['thumbnail_path'];
                  break;

               // === Colorlabs Project

               case 'Arthemia Premium':
                  $this->ab_logMsg('Creating custom fields for Arthemia Theme', AB_LOG_DEBUG);
                  if (!empty($this->postinfo['image_path'])) {
                     if (stristr($this->postinfo['image_path'], 'wp-content')) {
                        if (isset($this->postinfo['image_path'])) $this->postinfo['Image'] = $this->postinfo['image_path'];
                        if (isset($this->postinfo['image_path'])) $this->postinfo['image'] = $this->postinfo['image_path'];
                     }
                  }
                  break;

               // === WPThemesmarket

               case 'MagazineNews':
                  $this->ab_logMsg('Creating custom fields for MagazineNews Theme', AB_LOG_DEBUG);
                  if (isset($this->postinfo['image_url'])) $this->postinfo['image'] = $this->postinfo['image_url'];
                  break;

               // === WooThemes

               case 'Ambience':
               case 'BlogTheme':
               case 'Busy Bee':
               case 'Flash News':
               case 'Fresh Folio':
               case 'Fresh News':
               case 'Gazette Edition':
               case 'Geometric':
               case 'Gotham News':
               case 'Live Wire':
               case 'NewsPress':
               case 'OpenAir':
               case 'Over Easy':
               case 'Papercut':
               case 'Original Premium News':
               case 'ProudFolio':
               case 'Snapshot':
               case 'THiCK':
               case 'Typebased':
               case 'Vibrant CMS':

                  $this->ab_logMsg('Creating custom fields for WooThemes Theme', AB_LOG_DEBUG);
                  if (isset($this->postinfo['image_url'])) $this->postinfo['image'] = $this->postinfo['image_url'];
                  if (isset($this->postinfo['image_url'])) $this->postinfo['preview'] = $this->postinfo['image_url'];
                  if (isset($this->postinfo['thumbnail_urls'][0])) $this->postinfo['thumb'] = $this->postinfo['thumbnail_urls'][0];
                  if (isset($this->postinfo['link'])) $this->postinfo['url'] = $this->postinfo['link'];

                  // Specific theme settings
                  if (stristr("Gotham News", $themedata['Title'])) {
                     if (!isset($this->postinfo['post_thumbnail_value'])) $this->postinfo['thumb'] = $this->postinfo['thumbnail_urls'][0];
                  }
                  if (stristr("OpenAir", $themedata['Title'])) {

                     // Videos
                     if (!isset($this->postinfo['video'])) {
                        if (isset($this->postinfo['video'])) $this->postinfo['url'] = $this->postinfo['video'];
                        $this->postinfo['video'] = $this->postinfo['title'];
                     }
                  }
                  if (stristr("Snapshot", $themedata['Title'])) {
                     if (!isset($this->postinfo['image'])) $this->postinfo['large-image'] = $this->postinfo['image_url'];
                  }
                  break;

               // === Press75

               case 'Video Elements':
                  $this->ab_logMsg('Creating custom fields for Video Elements Theme', AB_LOG_DEBUG);
                  $post['post_excerpt'] = $this->postinfo['image'];
                  if (isset($this->postinfo['video_urls'][0])) $this->postinfo['videolink'] = $this->postinfo['video_urls'][0];
                  $this->postinfo['videowidth'] = $this->current_feed['playerwidth'];
                  $this->postinfo['videoheight'] = $this->current_feed['playerheight'];
                  if (isset($this->postinfo['video'])) $this->postinfo['videoembed'] = $this->postinfo['video'];
                  if (isset($this->postinfo['thumbnail_urls'][0])) $this->postinfo['thumbnail'] = $this->postinfo['thumbnail_urls'][0];
                  break;
               case 'On Demand':
                  $this->ab_logMsg('Creating custom fields for On Demand Theme', AB_LOG_DEBUG);
                  $post['post_excerpt'] = $this->postinfo['image'];
                  if (isset($this->postinfo['video'])) $this->postinfo['videoembed'] = $this->postinfo['video'];
                  if (isset($this->postinfo['thumbnail_urls'][0])) $this->postinfo['thumbnail'] = $this->postinfo['thumbnail_urls'][0];
                  break;
               default:
                  if (isset($this->postinfo['image_url'])) $this->postinfo['Image'] = $this->postinfo['image_url'];
                  if (isset($this->postinfo['image_urls'])) $this->postinfo['Images'] = $this->postinfo['image_urls'];

                  // Capitalized for Revolution and Options and other themes
                  if (!isset($this->postinfo['Thumbnail'])) {
                     if (isset($this->postinfo['thumbnail_urls'][0])) $this->postinfo['Thumbnail'] = $this->postinfo['thumbnail_urls'][0];
                  }
                  if (!isset($this->postinfo['Thumbnails'])) {
                     if (isset($this->postinfo['thumbnail_urls'])) $this->postinfo['Thumbnails'] = $this->postinfo['thumbnail_urls'];
                  }
                  if (!isset($this->postinfo['Video'])) {
                     if (isset($this->postinfo['video_urls'][0])) $this->postinfo['Video'] = $this->postinfo['video_urls'][0];
                  }
                  if (!isset($this->postinfo['Videos'])) {
                     if (isset($this->postinfo['video_urls'])) $this->postinfo['Videos'] = $this->postinfo['video_urls'];
                  }
            }
         }

         // API to allow other themes and plugins to modify post data
         $this->postinfo = apply_filters('before_autoblogged_post', $this->postinfo);
         do_action('autoblogged_post', $this->postinfo);

         // Check for skip_post
         if (!empty($this->postinfo['skip_post'])) {
            $this->ab_logMsg('Skipping post: '.$this->postinfo['skip_post'], AB_LOG_NOTICE, 'skip');
            return false;
         }

         // We don't want these saved as post metadata
         unset($this->postinfo['author']);
         unset($this->postinfo['author_display_name']);
         unset($this->postinfo['author_email']);
         unset($this->postinfo['author_url']);
         unset($this->postinfo['source']);
         unset($this->postinfo['source_url']);
         unset($this->postinfo['logo_url']);
         unset($this->postinfo['author_id']);
         unset($this->postinfo['content']);
         unset($this->postinfo['post']);
         unset($this->postinfo['title']);
         unset($this->postinfo['date']);
         unset($this->postinfo['poststatus']);
         unset($this->postinfo['category']);
         unset($this->postinfo['categories']);
         unset($this->postinfo['thumbnails']);
         unset($this->postinfo['videos']);
         unset($this->postinfo['page_content']);
         unset($this->postinfo['description']);
         unset($this->postinfo['tags']);
         unset($this->postinfo['image_url']);
         unset($this->postinfo['image_urls']);
         unset($this->postinfo['thumbnail_url']);
         unset($this->postinfo['thumbnail_urls']);
         unset($this->postinfo['html_full']);

         $this->postinfo['post_excerpt'] = '';

         // Also unset any custom fields that are set as blank
         if (!empty($this->current_feed['customFields'])) {
            foreach ($this->current_feed['customFields'] as $key => $value) {
               if (empty($value)) {
                  unset($this->postinfo[$key]);
                  unset($this->current_feed['customFields'][$key]);
               }
            }
         }

         //  Add the post
         if ($this->type <> 'preview') {
            $pid = wp_insert_post($post);

            // Add categories and tags for this post
            $res = wp_set_object_terms($pid, $this->current_feed['feedcategories'], 'category');
            wp_set_object_terms($pid, $this->current_feed['tags_list'], 'post_tag');

            // Add custom taxonomies
            if (isset($this->postinfo['customTaxonomies'])) {
               if (is_array($this->postinfo['customTaxonomies'])) {
                  foreach ($this->postinfo['customTaxonomies'] as $key => $value) {
                     $ret = wp_set_object_terms($pid, $value, $key, true);
                  }
               }
            }

            // Add all other info as custom fields
            foreach (array_keys($this->postinfo) as $itemfield) {
               if (is_array($this->postinfo[$itemfield])) {
                  // [removed]
               }
               else {
                  if (strlen($this->postinfo[$itemfield])) {
                     if (is_string($this->postinfo[$itemfield])) {
                        add_post_meta($pid, $itemfield, $this->postinfo[$itemfield]);
                     }
                  }
               }
            }

            $thumbnail_set = false;
            // Add first thumbnail as post thumbnail
            if (isset($this->postinfo['attachment_ids'])) {
               if (is_array($this->postinfo['attachment_ids'])) {
                  if (function_exists('set_post_thumbnail')) set_post_thumbnail($pid, $this->postinfo['attachment_ids'][0]);
                  $thumbnail_set = true;
               }
            }

            if (is_array($this->postinfo['attachments'])) {
               array_unique($this->postinfo['attachments']);
               if (ALWAYS_ATTACH_IMAGES) {
                  foreach (array_keys($this->postinfo['attachments']) as $attachment) {
                     if (!empty($attachment)) {
                        $this->ab_logMsg('Adding file attachment: '.$attachment, AB_LOG_DEBUG);
                        $attach_post_id = wp_insert_attachment($this->postinfo['attachments'][$attachment], $attachment, $pid);
                        wp_update_attachment_metadata($attach_post_id, wp_generate_attachment_metadata($attach_post_id, $attachment));
                        if (function_exists('set_post_thumbnail')) if (!$thumbnail_set) set_post_thumbnail($pid, $attach_post_id);
                        $thumbnail_set = true;
                     }
                  }
               }
            }

            $editlink = '<a href="'.get_option('siteurl').'/wp-admin/post.php?action=edit&post='.$pid.'" target="_blank">Edit</a>';
            $viewlink = '<a href="'.get_option('siteurl').'/?p='.$pid.'" target="_blank">View</a>';
            $this->ab_logMsg(ucfirst($post['post_type']).' added.', AB_LOG_INFO, 'check');
            $this->ab_logMsg(ucfirst($post['post_type']).' ID: '.$pid.'&nbsp;&nbsp;<span style="font-size: 8pt;">['.$viewlink.'] ['.$editlink.']</span>', AB_LOG_INFO + AB_DISPLAY_ONLY, 'info');
         }
         $this->current_feed['post_count']++;
         return $pid;
      }

      /**
       * Apply post template
       *
       * @param  string $templates
       *
       * @return string
       * @access public
       */

      function ab_applyTemplate($templates) {
         // Split multiple templates if there are any
         if (preg_match('/<!--\s*template\s*-->/', (string)$templates)) {
            $working_templates = preg_split('/<!--\s*template\s*-->/', $templates);
            $this->ab_logMsg(count($working_templates).' templates parsed.', AB_LOG_DEBUG);
         }
         else {
            $working_templates[0] = $templates;
         }

         $post_template = stripslashes((string)$working_templates[array_rand($working_templates)]);

         // Timestamp function
         // %timestamp(format, %field%)%
         $matches = array();
         preg_match_all('~%timestamp\("?([^",]*)(?:[",\s%]{2,6})([^"%]*)(?:["%)]{1,3})%~si', $post_template, $matches);
         $i = 0;

         foreach ($matches[0] as $match) {
            $post_template = str_replace($matches[0][$i], date($matches[1][0], strtotime($this->postinfo[$matches[2][$i]])), $post_template);
            $i++;
         }

         // Post timestamp (left here for backwards compatibility, undocumented)
         // %posttime(format)%
         $matches = array();
         preg_match_all('~%posttime\([\s\"]{0,3}([^\"\)]*)[\"\s]{0,3}\)%~si', $post_template, $matches);
         $i = 0;
         foreach ($matches[0] as $match) {
            $post_template = str_ireplace($match, date($matches[1][$i], strtotime($this->postinfo['date'])), $post_template);
            $i++;
         }


         // Regex Function - %regex(<variable>, <pattern>, [element], [delimiter])%  ex: %regex("%content%", "www\..*\.com", "1")%
         // Notes:
         //  For % in regex just use \x25, for " use \x22
         //  For namespace elements that contain :, use a custom var
         //  To do submatches, create two custom fields using this function

         $matches = array();
         preg_match_all('~%regex\(\s*\"(%[^%]+%)\s*"\s*,\s*\"([^"]*)\"\s*(?:,\s*\"([^"]*?)\s*\")?(?:,\s*\"([^"]*?)\s*\")?\)%~si', $post_template, $matches);

         if (count($matches)) {
            // We are doing a regex match
            if (isset($matches[0][$i])) $fullmatch = $matches[0][$i];
            // Recursively call to parse the field name
            if (isset($matches[1][$i])) $search_in = $this->ab_applyTemplate($matches[1][$i]);
            if (isset($matches[2][$i])) $matchpattern = $matches[2][$i];
            if (isset($matches[3][$i])) $element = $matches[3][$i];
            if (isset($matches[4][$i])) $delimeter = $matches[4][$i];


            // Check to see if modifiers included and run regex
            if (!empty($matchpattern)) $this->ab_logMsg('Regex pattern: ' . $matchpattern, AB_LOG_DEBUG);
            if (isset($matchpattern)) {
               if (preg_match('~^/[^/]*/$~', $matchpattern, $z)) {
                  $ret = preg_match_all($matchpattern, $search_in, $submatches, PREG_PATTERN_ORDER);
               }
               else {
                  $ret = preg_match_all('~'.$matchpattern.'~si', $search_in, $submatches, PREG_PATTERN_ORDER);
               }
            }

            if ($ret === false) {
               $this->ab_logMsg('Regex error occured with pattern '. $matchpattern, AB_LOG_DEBUG);
            }

            // Clean empty elements from the array
            if (isset($submatches)) {
               if (is_array($submatches)) {

                  $empty_elements = array_keys($submatches[0], "");
                  foreach ($empty_elements as $e) {
                     unset($submatches[0][$e]);
                  }
                  $submatches[0] = array_unique($submatches[0]);

                  // Grab specified element
                  if (!is_numeric($element)) {
                     $element = 0;
                  }

                  $matched = $submatches[(int)($element)];
                  $matched = array_unique((array)$matched);

                  if (empty($delimeter)) {
                     if (isset($matched[0])) $matched = $matched[0];
                  }
                  else {
                     $matched = implode($matched, $delimeter);
                  }

                  if (empty($matched)) {
                     $post_template = str_replace($fullmatch, '', $post_template);
                  } else {
                     $post_template = str_replace($fullmatch, $matched, $post_template);
                  }
               }
            } else {
            }
         }


         // Conditional tags - %if:image% <img src=%image%/> %endif%
         preg_match_all('/%if:([^%]+)%(.*)%endif:\1%/si', $post_template, $matches);
         $i = 0;
         foreach ($matches[0] as $match) {
            if (!empty($this->postinfo[$matches[1][$i]])) {
               $post_template = str_replace($match, $matches[2][$i], $post_template);
            }
            else {
               $post_template = str_replace($match, '', $post_template);
            }
            $i++;
         }

         // Random sets - [one|two|three]
         preg_match_all("/\[[^\]]+\]/s", $post_template, $matches);
         foreach ($matches as $matchset) {
            foreach ($matchset as $match) {
               $tmp = preg_split("/[\[|\]]/", $match, -1, PREG_SPLIT_NO_EMPTY);
               $selected = $tmp[array_rand($tmp)];
               $post_template = str_replace($match, $selected, $post_template);
            }
         }

         // Loops - %foreach:images%  <img src="%images%" /> %endfor:images%
         preg_match_all('/%foreach:([^%]+)%(.*)%endfor:\\1%/si', $post_template, $matches);
         $i = 0;
         foreach ($matches[0] as $match) {
            $var = $matches[1][$i];
            $subtemplate = $matches[2][$i];
            $values = array();
            $replacement = '';
            if (!is_array($this->postinfo[$var])) {
               $values[0] = $this->postinfo[$var];
            }
            else {
               $values = $this->postinfo[$var];
            }
            foreach ($values as $value) {
               $replacement .= str_ireplace('%'.$var.'%', $value, $subtemplate);
            }
            $i++;
            $post_template = str_replace($match, $replacement, $post_template);
         }

         // Namespace elements
         // Examples:
         //   %gd:rating%
         //   %http://schemas.google.com/g/2005:rating%
         //   %media:group/media:category%
         //   %http://schemas.google.com/g/2005:rating%
         //   %gd:rating@test%
         //   %http://schemas.google.com/g/2005:rating@test%
         //   %media:group/category@test%

         // Grab variable placeholders for this pattern



         preg_match_all("#%((?:http://[^:]*)?\w*):([^@%()]*)(@[^(%]*)?(?:\((\d+)\))?%#s", $post_template, $matches);
         $placeholders = $matches[0];
         $namespaces = $matches[1];
         $elements = $matches[2];
         $attributes = $matches[3];
         $counters = $matches[4];

         // Loop through each placeholder
         $i = 0;
         if (count($placeholders)) {
            foreach ($placeholders as $placeholder) {

               // Get the primary (first) namespace
               if (stristr($placeholder, 'http://')) {
                  $namespace = $namespaces[$i];
               } else {
                  $namespace = $this->rssmodules[strtolower($namespaces[$i])];
               }

               // Get the element
               If (!strstr($elements[$i], '/')) {

                  // Simple element: %media:content%
                  $element = $elements[$i];
               } else {

                  // Element with subelements:
                  // group/media:content
                  // or group/content
                  // or group/http://namespace.com/ns-definition:content

                  // Parse elements into subnamespaces/subelements
                  preg_match('/([^\/]*)\/((?:http:\/\/[^:]*)?\w*:)?(\w*)/i', $elements[$i], $elems_parsed);
                  $element = $elems_parsed[1];
                  $sub_ns = rtrim($elems_parsed[2], ':');
                  $sub_elem = $elems_parsed[3];
                  if (!stristr($sub_ns, 'http://')) {
                     $sub_ns = $this->rssmodules[strtolower($sub_ns)];
                  }
               }

               // Get the attribute if there is one
               $attribute = $attributes[$i];

               // Get the array offset if there is one
               $counter = intval($counters[$i]);

               // Call get_item_tags on the feed item
               $item_tags = $this->current_item->get_item_tags($namespace, $element);

               // Parse out the data we need
               if (empty($sub_elem)) {
                  // If there is only a simple element (i.e., media:content)
                  if (empty($attribute)) {
                     // e.g. %media:content%
                     $the_data = $item_tags[$counter]['data'];
                  }
                  else {
                     // e.g. %media:content@url%
                     $the_data = $item_tags[$counter]['attribs'][''][$attribute];
                  }
               }
               else {
                  // If there are subelements
                  if (empty($attribute)) {
                     // e.g. %media:group/media:content%
                     $the_data = $item_tags[$counter]['child'][$sub_ns][$sub_elem][0]['data'];
                  }
                  else {
                     // e.g. %media:group/media:content@url%
                     $the_data = $item_tags[$counter]['child'][$sub_ns][$sub_elem][0]['attribs'][''][$attribute];
                  }
               }

               // Do the replacement
               $post_template = str_ireplace($placeholder, $the_data, $post_template);
               $i++;
            }
         }

         // Replace all remaining variables with the actual values
         foreach (array_keys($this->postinfo) as $variable) {
            if (!empty($this->postinfo[$variable])) {
               if (is_array($this->postinfo[$variable])) {
                  $this->postinfo[$variable] = array_merge($this->postinfo[$variable]);
                  $delim = '&nbsp;';
                  $var = implode($delim, $this->postinfo[$variable]);
               }
               else {
                  $var = $this->postinfo[$variable];
               }
               $post_template = str_ireplace('%'.$variable.'%', $var, $post_template);
            }
         }

         // Remove common variables if they are not matched
         if (is_array($this->current_feed['customFields'])) $customfields = implode('|', array_keys($this->current_feed['customFields'])).'|';
         $post_template = preg_replace('/%('.$customfields.'excerpt|thumbnail|video|title|link|content|date|source|source_url|source_description|author|copyright|copyright_url|icon|logo_url|logo_link|logo_title|tags|images|image|videos|author_id|author_display_name|author_email|author_url|description|video_url|fail)%/si', '', $post_template);

         // Remove any remaining unmatched variables
         if (preg_match_all('/%[^%\s]{3,}%/', $post_template, $leftovers)) {
            $this->ab_logMsg('Note: Unmatched variables found in post template, you may need to check your syntax if your post does not look correct.', AB_LOG_DEBUG);
         }

         // First available sets - {%content%|%description%|%excerpt%}\
         // Now that all variables are filled in, we can do these.
         preg_match_all("/{[^}]+}/s", $post_template, $matches);
         foreach ($matches as $matchset) {
            foreach ($matchset as $match) {

               // Remove opening brace and any pipes: "{" or "{|" or "{||" etc
               $tmp = preg_replace("/{\|*/", "", $match);

               // Remove everything after the first non-empty content field
               $selected = preg_replace("/\|[^}]*}/", "", $tmp);

               // Remove all closing braces "}"
               $selected = str_replace("}", "", $selected);
               $post_template = str_replace($match, $selected, $post_template);
            }
         }
         return $post_template;
      }


      /**
       * Get keywords
       *
       * Extract significant keywords from the given content
       *
       * @param  string $content
       *
       * @return array   Return
       * @access public
       */
      function ab_getKeywords($content) {
         global $ab_options;
         static $tags;
         $keywords = array();

         require_once 'lib/htmLawed.php';

         if ($content) {
            $content = str_replace("8217", "", $content);
            $content = str_replace("8220", "", $content);

            // Make the content a bit smaller to work with
            $content = preg_replace("#\s+#", " ", $content);

            // And strip off any footer junk
            $content = preg_replace('/(<div\\sid\\s?=\\s?"footer">.*)/sm', '', $content);

            // Strip out most HTML content
            $options = array(
               'elements' => 'a, b, h1, h2, h3, strong, meta, img',
               'keep_bad' => 0,
               'comment' => 1,
               'safe' => 1);
            $content = htmLawed($content, $options);
            $content .= ' '.$this->postinfo['excerpt'];


            //  Search for keywords

            // Meta tags
            $this->ab_matchKeywords($keywords, $content, '#<meta\s+name[^=]*=[^"]*"keywords"\s+content[^=]*=[^"]*"([^"]+)"#Ss', META_KEYWORDS_WEIGHT, 1);
            $this->ab_matchKeywords($keywords, $content, '#<meta\s+content[^=]*=[^"]*"([^"]+)"\s+name[^=]*=[^"]*"keywords"#Sim', META_KEYWORDS_WEIGHT, 1);

            // H1, H2, and H3 headings
            $this->ab_matchKeywords($keywords, $content, '#<h1>(.*?)</h1>#Ss', H1_WEIGHT, 1);
            $this->ab_matchKeywords($keywords, $content, '#<h2>(.*?)</h2>#Ss', H2_WEIGHT, 1);
            $this->ab_matchKeywords($keywords, $content, '#<h3>(.*?)</h3>#Ss', H3_WEIGHT, 1);

            // rel tags
            $this->ab_matchKeywords($keywords, $content, '#<a\s+href[^=]*=[^"]*"[^"]*\/([^"\/?]*)"\s+rel[^=]*=[^"]*"tag"[^>]*>([^<]*)<\/a>#Ssi', REL_TAGS_WEIGHT, 1);

            // link text
            $this->ab_matchKeywords($keywords, $content, '#\<a[^\>]*\>(.*?)\</a\>#Ss', LINK_TEXT_WEIGHT, 1);

            // URL tags
            $this->ab_matchKeywords($keywords, $content, '#/(category|wiki|tags?)/([^/=\s"\'<]*)#Ssi', URL_TAGS_WEIGHT, 2);

            // Link title
            $this->ab_matchKeywords($keywords, $content, '#<a[^>]*title="([^"]*)"#Ssi', LINK_TITLE_WEIGHT, 1);

            // Alt tags
            $this->ab_matchKeywords($keywords, $content, '#<img\s.*alt="([^"]*)"#Ssi', ALT_TAGS_WEIGHT, 1);

            // Bold words
            $this->ab_matchKeywords($keywords, $content, '#<(?:strong>.*?</strong|b>.*?</b)>#Ssi', BOLD_WORD_WEIGHT, 1);
            $content = strip_tags($content);
            $content = preg_replace("#\s+#", " ", $content);

            // Other misc matches that produce good results

            // Single words or two 3-10 char words next to each other

            // This gets the bulk of the keywords on the page
            $this->ab_matchKeywords($keywords, $content, '#[a-z0-9\.-]{3,10}|\W([a-z0-9\.-]{3,10}\s[a-z0-9\.-]{3,20})#Ssi', 1, 1);

            // 3-5 char upper-case words
            $this->ab_matchKeywords($keywords, $content, '#\b[A-Z]{3,7}\b#Ss', 10, 0);

            // Capitalized words/proper nouns
            $this->ab_matchKeywords($keywords, $content, '#[^.?!]\b([A-Z][a-z]{2,10}[.\-]?[a-z]{2,10})#Ss', 10, 1);

            // Proper Nouns/Names & Titles
            $this->ab_matchKeywords($keywords, $content, '#((?:M(?:a(?:d(?:emoiselle|ame?)|(?:[jy]o|ste)r)|o(?:nsi(?:gno|eu)|the)r|is(?:ter|s)|r(?:s.?|.)?|s.?)|C(?:o(?:unt(?:ess)?|ngressman|mmander|lonel)|apt(?:ain|.)|hief)|S(?:e(?:cretary|rgeant|nator|aman)|i(?:ste)?r|heikh)|Pr(?:i(?:nce(?:ss)?|vate)|of(?:essor|.)|esident)|D(?:eacon(?:ess)?|octor|ame|r.?)|G(?:ov(?:ernor|.)|en(?:eral|.))|(?:Fa(?:rm|th)|Offic)er|R(?:ev(?:erend|.)|abbi)|B(?:ro(?:ther|.)|aron)|L(?:ieutenant|ady|ord)|Ju(?:stic|dg)e|Admiral|Queen|King))\s[A-Z][a-z]+#sSi', 15, 1);

            // Words in quotes
            $this->ab_matchKeywords($keywords, $content, '#\"([a-zA-Z\s-]{3,10})\"#Ssi', 5, 1);

            // ly+word
            $this->ab_matchKeywords($keywords, $content, '#\w{4,15}ly\s\w{8,20}#Si', 1, 0);

            // Two capitalized words next to each other
            $this->ab_matchKeywords($keywords, $content, '#\b[A-Z][[a-z0-9\.-]{4,8}\s?[A-Z][a-z0-9\.-]{3,20}\b#Ss', 8, 0);

            // Camel case (i.e., eBay, iPhone, etc.)
            $this->ab_matchKeywords($keywords, $content, '#\b[a-z]{1,4}[A-Z][a-z0-9\.-]{3,20}#Ss', 9, 0);

            // Word plus some common conjunctions/prepositions, then another word
            $this->ab_matchKeywords($keywords, $content, '#(\w{4,10}\s(?:a(?:[ts])|o(?:ff|[nr]|ut)|the|i[ft]|by|[fn]like|yet){1,2}\s\w{3,20})#Ssi', 5, 1);

            // Words that follow the, my, our, her, his, their
            $this->ab_matchKeywords($keywords, $content, '#(?:the|my|our|her|his|their|of)\s(\w{4,20})#Ssi', 3, 1);

            // Domain name
            $this->ab_matchKeywords($keywords, $content, '#\b(([a-z0-9]+(-[a-z0-9]+)*))\.com|net|org\b#Ssi', 4, 1);

            // Add weight to words that are common tags (disable to make script faster)
            $wordslist = array_keys($keywords);

            if ($wordslist) {
               if (!count($tags)) {
                  // load tags
                  $tags = file(dirname(__FILE__)."/tags.txt", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
               }

               foreach ($wordslist as &$word) {
                  $word = mb_strtolower($word);

                  foreach ($tags as $tag) {
                     if ($word == $tag) {
                        $keywords[$word] += TAGS_TXT_WEIGHT;
                     }
                  }
               }
            }
            arsort($keywords);

            // Trim it down but leave extras there to work with
            $keywords = array_slice($keywords, 0, $ab_options['maxtags'] * 1.5);
            return array_keys($keywords);
         }
         return true;
      }

      /**
       * Match keywords
       *
       * @param  array   &$keywords
       * @param  string  $content
       * @param  string  $regex
       * @param  integer $weight
       * @param  string  $matchindex
       *
       * @return unknown Return
       * @access public
       */
      function ab_matchKeywords(&$keywords, $content, $regex, $weight, $matchindex) {
         global $ab_options;
         if ($weight == 0) return;
         $matches = array();

         // Check the content for matches
         preg_match_all($regex, $content, $matches);
         if (count($matches) > 0) {
            foreach ((array)$matches[$matchindex] as $key => $match) {
               $words = explode(',', strip_tags($match));
               foreach ($words as $word) {

                  // Clean up keywords and format to follow a common format

                  // We are using hyphens between keywords here
                  $word = preg_replace("#[\+_\s\.\,\\\/\&]#", TAGS_DELIMITER, trim(strip_tags($word)));
                  $word = preg_replace("#[^a-zA-Z0-9-\x".dechex(ord(TAGS_DELIMITER))."]#", '', $word);
                  $word = str_replace("---", TAGS_DELIMITER, $word);
                  $word = str_replace("--", TAGS_DELIMITER, $word);
                  $word = htmlentities(strtolower($word));
                  if (!is_numeric($word)) {
                     if (strlen($word) >= 3) {

                        // Add as a keyword
                        $word = trim(preg_replace('/[^a-zA-Z0-9\.,-]/', '', $word));
                        if (!array_key_exists($word, $keywords)) {
                           $ln = strlen($word);
                           if ($ln <= $ab_options['maxtaglen']) {

                              // Give bonus for longer tags, unless they are over 15 characters
                              $lengthbonus = 1;
                              if ($ln <= 15) $lengthbonus = ($ln / 2);

                              // Add word
                              $keywords[$word] = $weight + $lengthbonus;


                              $tmpkeywords[] = $word;
                           }
                        }
                        else {
                           $keywords[htmlentities(strtolower($word))] += $weight;
                        }
                     }
                  }
               }
            }
         }
      }


      /**
       * Get Yahoo Tags
       *
       * Returns a comma-separated list of tags.
       *
       * @param  string $content
       *
       * @return mixed   Return
       * @access public
       */
      function ab_getYahooTags($content) {
         global $ab_options;
         if (empty($content)) {
            $this->ab_logMsg('Empty content sent to Yahoo Taging API.', AB_LOG_DEBUG);
            return 0;
         }
         $stripped = array();
         $url = "http://search.yahooapis.com/ContentAnalysisService/V1/termExtraction";
         $ch = curl_init();
         curl_setopt($ch, CURLOPT_URL, $url);
         curl_setopt($ch, CURLOPT_POST, 1);
         curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
         curl_setopt($ch, CURLOPT_POSTFIELDS, "appid=".$ab_options['yahooappid']."&query=null&context=".urlencode($content));
         $result = curl_exec($ch);
         curl_close($ch);
         if (stristr($result, 'errors were detected')) {
            $this->ab_logMsg('Yahoo Taging API error: '.$result, AB_LOG_DEBUG);
            $stripped = '';
         }
         else {
            $stripped = strip_tags($result, '<result>');
            $stripped = preg_replace("/<\/result>/i", ",", $stripped);
            $stripped = str_replace(" ", "-", $stripped);
            $stripped = str_replace(",", " ", $stripped);
         }
         $this->ab_logMsg('Yahoo Tags: '.$stripped, AB_LOG_DEBUG);
         return $stripped;
      }


      /**
       * Log Message
       *
       * @param  string    $message
       * @param int|number $level
       * @param  string    $icon
       *
       * @return void
       * @access public
       */
      function ab_logMsg($message, $level = AB_LOG_INFO, $icon = '') {
         do_action('autoblogged_message', $message, $level, $icon);
         global $ab_options;
         $log_only = false;
         $display_only = false;

         // Handle AB_DISPLAY_ONLY flagged
         if ($level & AB_DISPLAY_ONLY) {
            $display_only = true;
            $level = $level - AB_DISPLAY_ONLY;
         }

         // Handle AB_LOG_ONLY flag
         if ($level & AB_LOG_ONLY) {
            $log_only = true;
            $level = $level - AB_LOG_ONLY;
         }

         // Set default icons
         if (empty($icon)) {
            switch ($level) {
               case AB_LOG_ERROR:
                  $icon = 'error';
                  break;

               case AB_LOG_WARNING:
                  $icon = 'warn';
                  break;

               case AB_LOG_NOTICE:
                  $icon = 'info';
                  break;

               case AB_LOG_INFO:
                  $icon = 'info';
                  break;

               case AB_LOG_DEBUG:
                  $icon = 'debug';
                  break;

               default:
                  $icon = 'debug';
                  break;
            }
         }
         else {
            $icon = strtolower($icon);
         }

         // Log the message if logging and not AB_DISPLAY_ONLY
         if (!$display_only && $ab_options['logging']) {
            if (!is_object($this->logger)) $this->ab_initlogger();
            $this->logger->log(strip_tags($message), $level);
         }

         // Create an output message to display if required
         if (!$log_only && $this->show_output) {
            $output_msg = '';

            if ($level == AB_LOG_DEBUG) {
               if ($this->debug_mode) {
                  if ($this->type == 'cron' || $this->type == 'manual' || $this->type == 'preview') {
                     $output_msg = '<p class="abdebug"><img style="vertical-align: middle;" src="'.ab_pluginURL().'/img/'.$icon.'.png" />&nbsp;&nbsp;'.$message.'</p>';
                  }
               }
            }
            else {
               $output_msg = '<p class="abadmin"><img style="vertical-align: middle;" src="'.ab_pluginURL().'/img/'.$icon.'.png" />&nbsp;&nbsp;'.$message.'</p>';
            }
            $output_msg .= "\r\n";
            if (!empty($message)) echo $output_msg;
         }

         // Accumulate error and warning messages for the current feed running
         if ($level == AB_LOG_ERROR || $level == AB_LOG_WARNING) {
            $this->current_feed['messages'] .= "- $message\r\n";
         }
      }

      /**
       * Initialize logger.
       *
       * @return void
       * @access public
       */
      function ab_initlogger() {
         global $autoblogged;
         if (!is_object($autoblogged->logger)) {
            if (file_exists(ab_plugin_dir().'/log.php')) {
               require_once (ab_plugin_dir().'/log.php');
            }
            else {
               require_once (ab_plugin_dir().'/Log.php');
            }
            $conf = array('mode' => 0600, 'timeFormat' => '%X %x');
            $this->logger = Log::singleton('file', dirname(__FILE__).'/_debug.log', '', $conf);
         }
      }

      /**
       * Insert post intercept callback
       *
       * @param $data
       * @param $postarr
       *
       * @return bool
       */
      function on_wp_insert_post_data($data, $postarr) {
         global $ab_options;
         if ($ab_options['filterbytitle'] <> true) return $data;

         if (isset($data['post_type'])) {
            if ($data['post_type'] == 'post') {
               if ((substr($data['post_title'], -1) <> '2' && substr($data['post_name'], -2) == '-2') || (substr($data['post_title'], -1) <> '3' && substr($data['post_name'], -2) == '-3')) {
                  // Skip the post
                  return false;
               }
            }
         }
         return $data;
      }

      /**
       * Adds syndication-source meta header if set to do so
       *
       */
      function on_wp_head() {
         global $ab_options, $wp_the_query;
         if ($ab_options['syndicationsource'] == true && is_singular()) {
            $id = $wp_the_query->get_queried_object_id();
            if ($id) {
               $syndication_source = get_post_meta($id, 'link', true);

               if (!empty($syndication_source)) {
                  echo '<link rel="syndication-source" href="'.$syndication_source.'" />';
               }
            }
         }
      }

      function on_admin_init() {
      }


      /**
       * Shutdown intercept callback
       *
       * Used to trigger the scheduler
       *
       * @return void
       * @access public
       */
      function ab_shutdownIntercept() {
         if (is_home() || is_front_page() || is_feed()) {
            global $ab_options;
            $ab_options = ab_getOptions();

            // Check schedule
            $this->ab_logMsg('Checking schedule at '.date('d/m/yy G:i:s'), AB_LOG_DEBUG);
            if (time() >= $ab_options['lastupdate'] + ($ab_options['interval'] * 100)) {
               $this->ab_processFeeds(null, 'scheduled', false, null);
            }
         }
      }

      /**
       * Instantiates this class as a singleton
       */
      public static function get_instance() {
         if (!self::$instance) {
            self::$instance = new self;
            if (is_admin()) require_once (dirname(__FILE__).'/ab-admin.php');
         }
         return self::$instance;
      }
   }
}

/**
 * XMLRPC handling
 *
 * @param  array $args
 *
 * @return array Return
 */
if (!function_exists('autoblogged_xmlrpc')) {
   /**
    * @param array $args
    *
    * @return array
    */
   function autoblogged_xmlrpc($args = array()) {
      $args['weblogUpdates.ping'] = 'autoblogged_ping';
      return $args;
   }
}


/**
 * AutoBlogged ping handling
 *
 * @param  array $args
 *
 * @return mixed Return
 */
if (!function_exists('autoblogged_ping')) {
   /**
    * @param $args
    *
    * @return array|bool
    */
   function autoblogged_ping($args) {
      global $ab_options;
      global $wpdb;
      $max_xmlrpc_interval = 3600;
      if ($ab_options['accept_xmlrpc_pings']) {
         $sql = 'SELECT id, last_ping FROM '.ab_tableName().' WHERE url LIKE '.$args[1].'%';
         $feeds = $wpdb->get_results($sql, 'ARRAY_A');
         if (count($feeds)) {
            foreach ($feeds as $feed) {
               if (time() > $feed['last_update'] + ($max_xmlrpc_interval * 100)) {
                  $this->ab_processFeeds($feed['id'], 'xmlrpc', false, null);
                  return array('flerror' => false, 'message' => 'Thanks for the ping.');
               }
               else {
                  return array('flerror' => true, 'message' => 'Not enough time has passed since your last ping.');
               }
            }
         }
         else {
            return array('flerror' => true, 'message' => 'Your blog is not registered with our service. Please contact the administrator for details.');
         }
      }
      return true;
   }
}


/**
 * Add footer links
 *
 * @return void
 */
if (!function_exists('ab_footer')) {
   function ab_footer() {
      global $ab_options;
      require_once 'ab-functions.php';
      $ab_options = ab_getOptions();

      if ($ab_options['addlink']) {
         if (rand(0, 4) == 0) {

            $links = array('Powered by AutoBlogged', 'AutoBlogged', 'AutoBlogged', 'WordPress AutoBlog Plugin', 'Automate your Blog', 'Automated Blogging by AutoBlogged', 'Automatically Feed Your Blog', 'Premium AutoBlog Plugin', 'Get AutoBlogged', 'Automated WordPress Blogs', 'RSS Feeds to WordPress Posts', 'WordPress Automation', 'WordPress Automatic Blog', 'WP Auto Blog', 'Automatic Blog Software', 'WordPress Auto Blog', 'Automatic Blog Posting Plugin', 'Autoblog', 'WordPress Auto Post', 'Autoblogging Software', 'RSS to WordPress', 'Autoblogging', 'What is autoblogging?', 'FeedBurner Alternative', 'WordPress Autoblog', 'WordPress Autoblog Plugin', 'WordPress Autoblogging Plugin', 'Auto Blogging Software', 'WordPress AutoBlogger', 'Best Autoblogging Plugin');
            if ($ab_options['includeaffil'] == 1) {
               $url = 'https://www.e-junkie.com/ecom/gb.php?aff='.$ab_options['affilid'].'&c=ib&cl=25009';
            }
            else {
               $url = 'http://autoblogged.com';
            }
            echo '<p align="center"><a href="'.$url.'" title="'.$links[array_rand($links)].'">'.$links[array_rand($links)].'</a></p>';
         }
      }
   }
}

if (!function_exists('ab_FeedsPage')) {

   /**
    * Main feeds page
    *
    * @return unknown Return
    */
   function ab_FeedsPage() {
      switch ($_GET['action']) {
         case 'edit':
            require_once (dirname(__FILE__).'/ab-admin.php');
            ab_showEditFeedPage();
            break;
         default:
            ab_showFeedsPage();
            break;
      }
      return;
   }
}

if (!function_exists('ab_TagOptionsPage')) {

   /**
    * Tag options admin page
    *
    * @return void
    */
   function ab_TagOptionsPage() {
      ab_showTagOptionsPage();
   }
}


if (!function_exists('ab_SettingsPage')) {

   /**
    * Settings admin page
    *
    * @return void
    */
   function ab_SettingsPage() {
      ab_showSettingsPage();
   }
}

if (!function_exists('ab_FilteringPage')) {

   /**
    * Filtering admin page
    *
    * @return void
    */
   function ab_FilteringPage() {
      ab_showFilteringPage();
   }
}


if (!function_exists('ab_SupportPage')) {

   /**
    * Support admin page
    *
    * @return void
    */
   function ab_SupportPage() {
      ab_showSupportPage();
   }
}

autoblogged::get_instance();
