<?php

require_once(dirname(__FILE__).'/../../../wp-load.php');
check_admin_referer('autoblogged-nav')


?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-US" lang="en-US">
<head>
   <title>AutoBlogged File Viewer</title>
   <meta http-equiv="content-type" content="text/html; charset=UTF-8"/>
   <meta name="ROBOTS" content="NOINDEX,NOFOLLOW">
   <link rel="stylesheet" type="text/css" href="admin.css"/>
</head>

<?php
if ($_GET['f'] == 'debug') {

   if (!file_exists(ab_plugin_dir().'/_debug.log')) {
      ab_wperror('Debug file has not been created, "Enable logging to a file when processing feeds" must be checked on the Support page to create this file.');
      stop;
   }
   echo '<body><pre>';
   include(ab_plugin_dir().'/_debug.log');
   echo '</pre>';
} elseif ($_GET['v'] == 'viewer' && is_numeric($_GET['fid'])) {
   // Load feeds list from DB
   $sql = "SELECT url, type FROM ".ab_tableName().' WHERE id='.$_GET['fid'].';';
   $feed = $wpdb->get_results($sql, 'ARRAY_A');

   if (is_array($feed)) {
      $feed_urls = explode("\r\n", $feed[0]['url']);

      ?>
			<body>
				<h2 class="abadmin">Feed Viewer</h2>
   <p class="abadmin">This feed has mutliple URLs or keywords, please select the one you wish to view:</p>
				<ul>
					<?php
      flush();
      foreach ($feed_urls as $url) {
         if (strlen($url)) {
            echo '<li><a class="feedurl" href="http://viewer.autoblogged.com?feed='.urlencode(ab_getFeedURL($feed[0]['type'], $url)).'">'.$url.'</a></li>';
         }
      }
      echo '</ul>';
   }
} elseif ($_GET['v'] == 'validate' && is_numeric($_GET['fid'])) {
   // Load feeds list from DB
   $sql = "SELECT url, type FROM ".ab_tableName().' WHERE id='.$_GET['fid'].';';
   $feed = $wpdb->get_results($sql, 'ARRAY_A');

   if (is_array($feed)) {
      $feed_urls = explode("\r\n", $feed[0]['url']);

      ?>
					<body>
						<h2 class="abadmin">Feed Validator</h2>
   <p class="abadmin">This feed has mutliple URLs or keywords, please select the one you wish to validate:</p>
						<ul>
							<?php
      foreach ($feed_urls as $url) {
         if (strlen($url)) {
            echo '<li><a class="feedurl" href="http://feed1.w3.org/check.cgi?url='.urlencode(ab_getFeedURL($feed[0]['type'], $url)).'">'.$url.'</a></li>';
         }
      }
      echo '</ul>';
   }
} elseif ($_GET['f'] == 'php') {
   $error_log = ini_get('error_log');
   if (!empty($error_log)) {
      if (!file_exists($error_log)) {
         if (!file_exists($_ENV['DOCUMENT_ROOT'].'/'.$error_log)) {
            ab_wperror('Server\'s PHP error logfile was not found or is not accessible.<!-- '. $error_log . '-->');
            stop;
         } else {
            $error_log = $_ENV["DOCUMENT_ROOT"].'/'.$error_log;
         }
      }

      echo '<body><pre>';
      include($error_log);
      echo '</pre>';
   }
}
?>


</body>
</html>

