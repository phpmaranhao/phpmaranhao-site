<?PHP

/**
 * AutoBlogged feed processing module
 *
 *
 * Parameters:
 *
 *   fid       ID of the feed to process if processing a single feed
 *
 *   show      Controls what output response is returned: v = verbose; d = debug; b (default) = returns "true" or "false"
 *             to indicate if feed ran successfully
 *
 *   min-time  Do not process feeds unless specified number of seconds have elapsed since last feed run
 *
 *   stats     Set to "on" to show a summary at the end of the feed operation.
 *
 *   action    Set to "preload-cache" to populate the feeds cache without processing any posts
 *             Set to "preview" to preview feed processing without saving posts
 *             internally might also be set to "cron", "xmlrpc", "manual", or "scheduled"
 *
 * Note: for added security, use .htaccess to set IP and other restrictions or password-protect this file
 *
 * PHP version 5
 *
 *
 * @package   AutoBlogged v2
 * @author    AutoBlogged <support@autoblogged.com>
 * @category  plugins
 * @copyright Copyright (c)2011 AutoBlogged, ALL RIGHTS RESERVED
 * @version   SVN Build: $Id: run.php 32 2011-02-21 21:16:38Z dev $
 */


// These are to avoid problems with other plugins such as WP eCommerce
ob_start();
session_cache_limiter('');


$ret = null;

// Load WordPress
require_once (dirname(__FILE__).'/../../../wp-load.php');

$key = ab_authKey();
if ($key <> $_GET['auth']) {
   die('Access Denied.');
} else {
   if (!empty($_GET['fid'])) {
      if (is_numeric($_GET['fid'])) {
         $fid = $_GET['fid'];
      } else {
         die('Invalid feed ID');
      }
   }


   // Get show_output and debug_mode parameters
   $show = strtolower(substr($_GET['show'], 0, 1));
   if ($show == 'v' || $show == 'd') $verbose = true;
   if ($show == 'd') $debug = true;
   $action = strtolower($_GET['action']);
   if ($action == 'preview') $type = 'preview';
   if ($action == 'preload-cache') $type = 'preload-cache';
   if (empty($action)) {
      $type = 'cron';
   } else {
      $type = $action;
   }

   // Process feeds now
   $autoblogged = autoblogged::get_instance();
   $ret = $autoblogged->ab_processFeeds($fid, $type, $verbose, $debug);

   if ($_GET['show'] == 'b' || empty($_GET['show'])) {
      if ($ret) {
         echo 'Success';
      } else {
         echo 'Failure';
      }
   }
}
?>

</body>
</html>