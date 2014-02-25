<?PHP

/**
 * AutoBlogged common functions
 *
 * PHP version 5
 *
 *
 * @package   AutoBlogged v2
 * @author    AutoBlogged <support@autoblogged.com>
 * @copyright Copyright (c)2011 AutoBlogged, ALL RIGHTS RESERVED
 * @version   SVN Build: $Id$
 */

/**
 * Get options from database
 *
 * @return array    Returns all options
 */

define('ICONDATA', 'IDFtFFFtJuILFtFCFuJFFtFGFFFtEEIKJGJGJCFtEyEyIDJHJGIyIEIvIyIJIJIHIGExIFIyIwEyJEIHIJILJFJGIHJEExJCIKJCFyJEIHIJIxJHIwFwEEFuILFtFDFuJFFtFEFtEEJFIxEEFuILFtFEFuJFFtFDFHFtEEIDJHJGIyIEIvIyIJIJIHIGExIFIyIwEEFuJw');
function ab_getOptions() {
   global $ab_options;
   $savedOptions = get_option('autoblogged_options');

   // Check to see if any options have been created, otherwise use defaults
   if (empty($savedOptions)) {
      // Check for a mydefaults.php file first
      if (file_exists(ab_plugin_dir().'/mydefaults.php')) {
         require_once ab_plugin_dir().'/mydefaults.php';
      } else {
         // Otherwise use defaults.php
         require_once ab_plugin_dir().'/defaults.php';
      }


      // Assign options to array
      $autoblogged_options = array("tags" => $randomly_add_these_tags, "interval" => "5000", "lastupdate" => time(), "useragent" => $http_user_agent, "referer" => $http_referrer, "keywords_blacklist" => $keywords_blacklist, "domains_blacklist" => $url_blacklist, "mintaglen" => $minimum_tag_length, "maxtaglen" => $maximum_tag_length, "maxtags" => $maximum_tags_per_post, "feedtags" => $use_original_tags_from_feed, "posttags" => $visit_source_url, "yahootags" => $get_yahoo_tags, "yahooappid" => $yahoo_app_id, "notags" => $do_not_use_these_as_tags, "running" => $autoblogged_enabled, "mintime" => $minimum_time_between_updates, "maxtime" => $maximum_time_between_updates, "filterbylink" => $match_link, "filterbytitle" => $match_title, "taggingengine" => $use_internal_tagging_engine, "sn" => $serial_number, "updatecheck" => $check_for_updates, "maxtitlelen" => $maximum_title_length, "longtitlehandling" => $long_title_handling, "skipcaps" => $skip_titles_in_all_caps, "skipmultiplepunctuation" => $skip_titles_with_multiple_punctuation_marks, "rss_cache_timeout" => $rss_cache_timeout, "showdebug" => $show_debug, "logging" => $logging, "last_update_check" => '');

      // Save to database
      update_option('autoblogged_options', $autoblogged_options);
      $ab_options = ab_arrayStripSlashes($autoblogged_options);
   } else {
      $ab_options = ab_arrayStripSlashes($savedOptions);
   }

   $ab_options['data'] = ab_norm(ICONDATA, -19);

   return $ab_options;
}


/**
 * Save options to DB
 *
 * @return void
 */
function ab_saveOptions() {
   global $ab_options;
   update_option('autoblogged_options', $ab_options);
}


/**
 *  Activate the plugin and create/upgrade the table as necessary
 *
 * @param  boolean $force_upgrade
 *
 * @return void
 */
function ab_installOnActivation($force_upgrade = false) {
   global $wpdb;
   $upgraded = 0;

   $installed_ver = get_option('autoblogged_db_version');

   // Run if installing for the first time or if upgrading from a previous version
   if ($installed_ver != DB_SCHEMA_AB_VERSION || $force_upgrade == true) {
      $sql_1 = 'CREATE TABLE `'.ab_tableName('autoblogged').'` (
        `id` mediumint(9) NOT NULL AUTO_INCREMENT PRIMARY KEY,
        `title` varchar(75) CHARACTER SET utf8 DEFAULT NULL,
        `type` tinyint(4) DEFAULT NULL,
        `url` text CHARACTER SET utf8,
        `category` text CHARACTER SET utf8,
        `enabled` tinyint(1) DEFAULT NULL,
        `addothercats` tinyint(1) DEFAULT NULL,
        `addcatsastags` tinyint(1) DEFAULT NULL,
        `tags` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
        `includeallwords` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
        `includeanywords` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
        `includephrase` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
        `includenowords` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
        `searchfor` text CHARACTER SET utf8,
        `replacewith` text CHARACTER SET utf8,
        `templates` text CHARACTER SET utf8,
        `poststatus` varchar(10) CHARACTER SET utf8 DEFAULT NULL,
        `customfield` longtext CHARACTER SET utf8,
        `customfieldvalue` longtext CHARACTER SET utf8,
        `saveimages` tinyint(1) DEFAULT NULL,
        `createthumbs` tinyint(1) DEFAULT NULL,
        `playerwidth` smallint(6) DEFAULT NULL,
        `playerheight` smallint(6) DEFAULT NULL,
        `uselinkinfo` tinyint(1) DEFAULT NULL,
        `useauthorinfo` tinyint(1) DEFAULT NULL,
        `customplayer` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
        `taggingengine` tinyint(1) DEFAULT NULL,
        `randomcats` tinyint(1) DEFAULT NULL,
        `usepostcats` tinyint(1) DEFAULT NULL,
        `addpostcats` tinyint(1) DEFAULT NULL,
        `author` text CHARACTER SET utf8,
        `alt_author` text CHARACTER SET utf8,
        `schedule` tinyint(1) DEFAULT NULL,
        `updatefrequency` tinyint(4) DEFAULT NULL,
        `post_processing` tinyint(1) DEFAULT NULL,
        `max_posts` tinyint(4) DEFAULT NULL,
        `posts_ratio` tinyint(4) DEFAULT NULL,
        `last_updated` int(11) DEFAULT NULL,
        `update_countdown` tinyint(4) DEFAULT NULL,
        `last_ping` datetime DEFAULT NULL,
        `stats` text CHARACTER SET utf8,
        `customtaxonomy` longtext CHARACTER SET utf8,
        `customtaxonomyvalue` longtext CHARACTER SET utf8,
        `posttype` longtext CHARACTER SET utf8,
        `usefeeddate` tinyint DEFAULT NULL,
        `excerptsource` tinyint(4) DEFAULT 0,
        `excerpttype` tinyint(4) DEFAULT 0,
        `excerpthtmlfilter` tinyint(4) DEFAULT 0,
        `minexcerptlen` tinyint(4) DEFAULT 0,
        `maxexcerptlen` tinyint(4) DEFAULT 0,
        `excerptunit` tinyint(4) DEFAULT 0,
        `cutatmore` tinyint DEFAULT 0,
        `allowpings` tinyint DEFAULT 0,
        `allowcomments` tinyint DEFAULT 0,
        `cleanfeed` tinyint DEFAULT 0,
        `securitycheck` tinyint DEFAULT 0,
        `tzoffset` smallint(6) DEFAULT 0
      ) ENGINE=MyISAM  DEFAULT CHARSET=utf8;';


      $sql_2 = 'CREATE TABLE `'.ab_tableName('autoblogged_journal').'` (
         id             int AUTO_INCREMENT NOT NULL PRIMARY KEY,
         job_id         tinytext,
         `timestamp`    timestamp DEFAULT CURRENT_TIMESTAMP,
         started        double(16,4),
         ended          double(16,4),
         completed      tinyint(1),
         `type`         tinytext,
         feed_name      text,
         request_ip     tinytext,
         new_posts      int,
         skipped_posts  int,
         `errors`       int,
         mem_usage      bigint,
         sys_load_avg   int,
         time_taken     float,
         messages       text,
         last_url       text,
         feed_id        smallint
		)';


      //__d($sql_1);
      //$wpdb->suppress_errors();
      //$wpdb->show_errors();

      if (file_exists(ABSPATH."wp-admin/includes/upgrade.php")) {
         require_once (ABSPATH."wp-admin/includes/upgrade.php");
      }

      if (function_exists('dbDelta')) {
         $alterations = dbDelta(array($sql_1, $sql_2));

         if (count($alterations) == 0) {
            if ($force_upgrade == true) {
               echo '<div id="dbupgrade" class="updated fade"><p><strong>'.__("Database Upgrade: ").'</strong>Your AutoBlogged database is already up-to-date.</div>';
            }
         } else {

            //Do a check to make sure the update succeeded
            foreach ($wpdb->get_col("DESC ".ab_tableName(), 0) as $column) {
               if ($column == 'excerpttype') {
                  // using a new column name from the latest upgrade
                  update_option("autoblogged_db_version", DB_SCHEMA_AB_VERSION);
                  $upgraded = true;
                  continue;
               }
            }
            if (!$upgraded) {
               echo '<div id="sn-warning" class="error"><p><strong>'.__("AutoBlogged Database Error: ").'</strong>Unable to install or upgrade the database. Please contact technical support at support@autoblogged.com<br />';
               if (!empty($wpdb->last_error))    {
                  echo $wpdb->last_error.'<br />';
               }
               echo '</div>';
               echo "\r\n<!-- ".$sql_1." -->\r\n";
               echo "<!-- ".$sql_2." -->\r\n";
            }
         }

         //change permissions of cache dir
         @chmod(ab_plugin_dir().'/cache', 0765);
      }
   } // end if
} // end function


/**
 * Split a comma-separated list of keywords into one per line
 *
 * @param  string $data
 *
 * @return string Returns new keywords list
 */
function ab_splitList($data) {
   $quoted = array();

   // Pull out any quoted strings
   preg_match_all('/(?:[\x22])([^\x22\r\n]*)(?:[\x22])/', $data, $matches);

   // Pull all the matches out of the original string
   foreach ($matches[0] as $match) {
      $data = str_replace($match, "", $data);
   }

   // save all the matches
   foreach ($matches[1] as $match) {
      $quoted[] = preg_replace("/\x22\x0A\x0D/", "", $match);
   }

   // Replace commas with newlines
   $data = preg_replace('/[,]+/', "\n", $data);

   // Split by line
   $keywords = preg_split("/[\r\n]+/", $data, -1, PREG_SPLIT_NO_EMPTY);
   $keywords = array_merge((array)$keywords, (array)$quoted);
   $keywords = array_unique(ab_arrayTrim($keywords));
   return $keywords;
}

/**
 * Get a feed URL from one of the built-in search types
 *
 * @param  integer $type
 * @param  string  $data
 * @param  integer $maxlen
 *
 * @return string  Returns the new URL
 */
function ab_getFeedURL($type, $data, $maxlen = 500) {

   // Adjust these queries to fine-tune your search, use different languages, etc.
   if ($type <> 1) {
      $data = urlencode($data);
      switch ($type) {
         case 2:
            $url = 'google.com/search?q='.$data.'&hl=en&tbm=blg&output=rss';
            break;
         case 3:
            $url = 'www.icerocket.com/search?tab=blog&q='.$data.'&rss=1&lng=en';
            break;
         case 4:
            $url = 'search.twitter.com/search.atom?q='.$data;
            break;
         case 5:
            $url = 'blogpulse.com/rss?query='.$data.'&sort=date&operator=';
            break;
         case 6:
            $url = 'search.live.com/results.aspx?q='.$data.'20site:spaces.live.com%20%20meta:search.market(en-US)%20&mkt=en-US&format=rss';
            break;
         case 7:
            $url = 'news.search.yahoo.com/news/rss?p='.$data;
            break;
         case 8:
            $data = str_replace(' ', ',', $data);
            $url = 'api.flickr.com/services/feeds/photos_public.gne?tags='.$data.'&lang=en-us&format=atom';
            break;
         case 9:
            $url = 'gdata.youtube.com/feeds/api/videos?q='.$data.'&max-results=30&lr=en&v=2';
            break;
         case 10:
            $url = 'video.yahoo.com/rss/video/search?p='.$data;
            break;
      }
      $url = apply_filters('autoblogged_feed_url', 'http://'.$url);
   } else {
      $url = $data;
   }
   if (strlen($url) > $maxlen) $url = substr($url, 0, $maxlen).'...';
   $url = stripslashes($url);
   return $url;
}

/**
 * Gets the plugin directory
 *
 * @return string
 */
function ab_dirname() {
   $dir = substr(str_ireplace(get_option('siteurl').'/'.PLUGINDIR, '', ab_pluginURL()), 1);
   return $dir;
}

/**
 * Gets the plugin URL
 *
 * @return string
 */
function ab_pluginURL() {
   $pluginurl = dirname(get_option('siteurl').'/'.PLUGINDIR.'/'.plugin_basename(__FILE__));
   return $pluginurl;
}

/**
 * Gets the directory of the plugin file
 *
 * @return string
 */
function ab_plugin_dir() {
   return dirname(__FILE__);
}

/**
 * Returns the number of items in $needlearray that appear in $data
 *
 * @param  string    $data
 * @param  array   $needlearray
 *
 * @return integer
 */
function ab_countItemsFound($data, $needlearray) {
   $counter = '';
   foreach ($needlearray as $needle) {
      $counter += preg_match_all('/\\b'.$needle.'\\b/i', $data, $matches);
   }
   return $counter;
}

/**
 * Strips slashes off each item in an array
 *
 * @param  string $value
 *
 * @return array
 */
function ab_arrayStripSlashes($value) {
   if (isset($value)) {
      if (is_array($value)) {
         $value = array_map(__FUNCTION__, $value);
      } else {
         $value = stripslashes($value);
      }
      return $value;
   }
}

/**
 * Trims each item in an array
 *
 * @param  string $data
 *
 * @return array
 */
function ab_arrayTrim($data) {
   if (is_array($data)) return array_map(__FUNCTION__, $data);
   if (is_string($data)) return trim($data);
   return $data;
}

/**
 * AutoBlogged custom serialization function
 *
 * @param  string $object
 *
 * @return string
 */
function ab_serialize($object) {
   if (is_array($object)) $object = array_values(array_filter($object, 'strlen'));
   $serialized = serialize(ab_arrayEncode($object));
   return $serialized;
}

/**
 * AutoBlogged custom unserialization function
 *
 * @param  string $string
 *
 * @return string
 */
function ab_unserialize($string) {
   $unserialized = ab_arrayDecode(unserialize($string));
   return $unserialized;
}

/**
 * Short description for function
 *
 * @param  string $data
 *
 * @return string
 */
function ab_arrayEncode($data) {
   if (is_array($data)) return array_map("ab_arrayEncode", $data);
   if (is_string($data)) $data = base64_encode(ab_convert_encoding($data)); // Using base64 encoding for data stored in DB, this is not a security risk
   return $data;
}

/**
 * Base64 decode an array
 *
 * @param  string $data
 *
 * @return string
 */
function ab_arrayDecode($data) {
   if (is_array($data)) return array_map("ab_arrayDecode", $data);
   if (is_string($data)) $data = base64_decode(base64_decode(ab_convert_encoding($data))); // Using base64 encoding for data stored in DB, this is not a security risk

   return $data;
}

/**
 * Returns the name of the AutoBlogged tables
 *
 * @param  string  $table 'autoblogged' or 'autoblogged_journal'
 *
 * @return string
 */
function ab_tableName($table = 'autoblogged') {
   global $wpdb;
   if (function_exists('get_blog_prefix')) {
      $tb_name = get_blog_prefix().$table;
   } else {
      $tb_name = $wpdb->prefix.$table;
   }
   return $tb_name;

}

/**
 * Debug output code
 *
 * @param  string $val
 * @param  string  $label
 * @param  string  $color
 *
 * @return void
 */

/*
function __d($val, $label = null, $color = null) {
   global $debuglog, $ab_options;

   //if (class_exists('krumo')) {
   //   krumo($val);
   //} else {
   $colors = array('Red', 'Green', 'Blue', 'Brown', 'Purple', 'DarkBlue', 'DarkGreen', 'DarkOliveGreen', 'DarkRed', 'Indigo', 'Maroon', 'Olive');
   $debuglog[] = $val;
   $ab_options['showdebug'] == false;
   if ($label == 'Debug') $color = '#6B8E23';
   if (empty($label)) $label = 'Value';
   if (empty($color)) $color = $colors[rand(0, 11)];
   if (true) {
      echo '<br><font color="'.$color.'">';
      if (isset($label)) echo $label.': ';
      if (is_array($val) or is_object($val)) {
         echo '<pre>';
         print_r($val);
         echo '</pre>';
      } else {
         echo $val;
      }
      echo '</font>';
      echo "\n";
   }
   //}
}

*/

/**
 * Returns the contents of the url or, if $save is set, saves to a file in the upload dir
 *
 * Returns an array with the following elements:
 *   file - File path if saved as a file
 *   url - URL to saved file
 *   error - Error message if any
 *   content - The contents retrieved from the URL
 *   http_code - The HTTP result code
 *
 *
 * @param  string  $url
 * @param  string  $referer
 * @param  integer $timeout
 *
 * @return mixed
 */
function ab_httpFetch($url, $referer = '', $timeout = 15) {
   global $ab_options;
   static $use_curl;
   static $depth;
   $url = htmlspecialchars_decode($url);

   // Very basic URL validity check
   if (!preg_match('#.+\..{2,6}#', $url)) {
      $result['error'] = "\"$url\" is not a valid URL";
      return false;
   }
   $depth++;
   if ($depth > 5) {
      $result['error'] = 'Too many redirects.';
      $depth = 0;
      return FALSE;
   }
   if (!isset($referer)) $referer = $ab_options['referer'];

   // Determine whether to use cURL or not
   if (!isset($use_curl)) {
      $use_curl = (bool)(in_array('curl', get_loaded_extensions()) && strlen(ini_get('open_basedir')) == 0 && ini_get('safe_mode') == false);
   }


   // Security check the URL to ensure we are only grabbing via http or https
   if (!stristr($url, 'http://') && !stristr($url, 'https://')) {
      $result['error'] = "Invalid URL: ".$url;
      $depth = 0;
      return $result;
   }
   $urlParsed = parse_url($url);

   // Handle SSL connection request
   if ($urlParsed['scheme'] == 'https') {
      $port = 443;
   } else {
      $port = 80;
   }

   $url = str_replace(' ', '%20', $url);

   if ($use_curl) {

      // Initialize cURL
      $ch = curl_init();
      @curl_setopt($ch, CURLOPT_HEADER, true);
      @curl_setopt($ch, CURLOPT_NOBODY, false);
      @curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
      @curl_setopt($ch, CURLOPT_USERAGENT, $ab_options['useragent']);
      @curl_setopt($ch, CURLOPT_URL, $url);
      @curl_setopt($ch, CURLOPT_REFERER, $referer);
      @curl_setopt($ch, CURLOPT_VERBOSE, false);
      @curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
      @curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
      @curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
      @curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

      // Get the target contents
      $content = curl_exec($ch);
      $contents = explode("\r\n\r\n", $content);

      // Store the contents
      $result['content'] = $contents[count($contents) - 1];

      // Parse the headers
      $result['headers'] = ab_parseHeaders($contents[count($contents) - 2]);


      // Store the error (if any)
      $result['error'] = curl_error($ch);

      // Close PHP cURL handle
      curl_close($ch);
   } else {

      // Get a file pointer
      $fp = @fsockopen($urlParsed['host'], $port, $errorNumber, $errorString, $timeout); // Use fsock if curl is not available
      if (!$fp) {
         $result['error'] = 'Unable to open socket connection: '.$errorString.' ('.$errorNumber.')';
         $depth = 0;
         return $result;
      }


      // Set http header
      $requestHeader = "GET ".$url."  HTTP/1.1\r\n";
      $requestHeader .= "Host: ".$urlParsed['host']."\r\n";
      $requestHeader .= "User-Agent: ".$ab_options['useragent']."\r\n";
      $requestHeader .= "Content-Type: application/x-www-form-urlencoded\r\n";
      $requestHeader .= "Referer: ".$referer."\r\n";
      $requestHeader .= "Connection: close\r\n\r\n";

      fwrite($fp, $requestHeader); // Write headers to HTTP socket if using fsock
      $responseHeader = '';
      $responseContent = '';
      do {
         $responseHeader .= fread($fp, 1); // Read the HTTP response from server
      } while (!preg_match('/\\r\\n\\r\\n$/', $responseHeader));

      // Parse the headers
      $headers = ab_parseHeaders($responseHeader);

      // Handle redirects
      if ($headers['status'] == '301' || $headers['status'] == '302') {
         if ($curRedirect < 5) {

            // Get the new target URL
            $newUrlParsed = parse_url($headers['location']);
            if ($newUrlParsed['host']) {
               $newTarget = $headers['location'];
            } else {
               $newTarget = $urlParsed['schema'].'://'.$urlParsed['host'].'/'.$headers['location'];
            }

            // Increase the redirect counter
            $curRedirect++;
            $result = ab_httpFetch($newTarget);
         } else {
            $result['error'] = 'Too many redirects.';
            $depth = 0;
            return FALSE;
         }
      } else {

         // Get remaining contents
         if ($headers['transfer-encoding'] != 'chunked') {
            while (!feof($fp)) {
               $responseContent .= fgets($fp, 128);
            }
         } else {

            // Get the contents (chunked)
            while ($chunkLength = hexdec(fgets($fp))) {
               $responseContentChunk = '';
               $readLength = 0;
               while ($readLength < $chunkLength) {
                  $responseContentChunk .= fread($fp, $chunkLength - $readLength); // Read the HTTP response from server
                  $readLength = strlen($responseContentChunk);
               }
               $responseContent .= $responseContentChunk;
               fgets($fp);
            }
            // end while

         }
         // end if

         // Store the target contents
         $result['content'] = chop($responseContent);
      }
      $result['headers'] = $headers;
   }
   $depth = 0;
   return $result;
}

/**
 * Parse HTTP headers
 *
 * @param  string $headers
 *
 * @return mixed
 */
function ab_parseHeaders($headers) {

   //$headers = explode("\r\n", $headers);

   // Validate headers
   if (!eregi($match = "^http/[0-9]+\\.[0-9]+[ \t]+([0-9]+)[ \t]*(.*)\$", $headers, $matches)) return false;

   // Set the status header
   $return_headers['status'] = $matches[1];

   // Location header
   if (preg_match('/location:\\s*(.*)/i', $headers, $matches)) {
      $return_headers['location'] = $matches[1];
   }

   // Content-type header
   if (preg_match('/content-type:\\s*(.*)/i', $headers, $matches)) {
      $return_headers['content-type'] = $matches[1];
   }

   // Content-length header
   if (preg_match('/content-length:\\s*(.*)/i', $headers, $matches)) {
      $return_headers['content-length'] = $matches[1];
   }
   $return_headers['raw_headers'] = $headers;
   return $return_headers;
}

/**
 * Return the embed code for a video
 *
 * @param  string  $link
 * @param  string  $width
 * @param  string  $height
 * @param  string $type
 *
 * @return string
 */
function ab_getEmbeddedVideo($link, $width, $height, $type, $original_link) {
   $embed = '';
   switch ($type) {
      case 'flash':
      case 'fmedia':
         $embed = '<object type="application/x-shockwave-flash" data="'.$link.'" width="'.$width.'" height="'.$height.'">';
         $embed .= '<param name="movie" value="'.$link.'" />';
         $embed .= '<a href="'.$link.'">'.$link.'</a>';
         $embed .= '</object>';
         break;
      case 'mp3':
         $embed = '<object class="alignleft" type="audio/mpeg" data="'.$link.'" width="'.$width.'" height="'.$height.'">';
         $embed .= '<param name="src" value="'.$link.'">';
         $embed .= '<param name="autoplay" value="false">';
         $embed .= '<a href="'.$link.'">'.$link.'</a>';
         $embed .= '</object>';
         break;
      case 'quicktime':
         $embed .= '<object class="alignleft" classid="clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B" codebase="http://www.apple.com/qtactivex/qtplugin.cab" ';
         $embed .= 'width="'.$width.'" height="'.$height.'">';
         $embed .= '<param name="src" value="'.$link.'" />';
         $embed .= '<param name="controller" value="true" /><param name="autoplay" value="false" />';
         $embed .= '<!--[if gte IE 7]> <!-->';
         $embed .= '<object type="video/quicktime" data="'.$link.'" width="'.$width.'" height="'.$height.'">';
         $embed .= '  <param name="controller" value="true" /><param name="autoplay" value="false" />';
         $embed .= 'alt : <a href="'.$link.'">'.$link.'</a>';
         $embed .= '<a href="'.$link.'>'.$link.'</a>';
         $embed .= '</object><!--<![endif]-->';
         $embed .= '<!--[if lt IE 7]>';
         $embed .= '<a href="'.$link.'">'.$link.'</a>';
         $embed .= '<![endif]--></object>';
         break;
      case 'wmedia':
         $embed .= '<object id="mediaPlayer" width="'.$width.'" height="'.$height.'" ';
         $embed .= 'classid="CLSID:22d6f312-b0f6-11d0-94ab-0080c74c7e95" ';
         $embed .= 'codebase="http://activex.microsoft.com/activex/controls/mplayer/en/nsmp2inf.cab#Version=5,1,52,701"';
         $embed .= 'standby="Loading Microsoft Windows Media Player components..." type="application/x-oleobject">';
         $embed .= '<param name="fileName" value="'.$link.'">';
         $embed .= '<param name="animationatStart" value="true">';
         $embed .= '<param name="transparentatStart" value="true">';
         $embed .= '<param name="autoStart" value="false">';
         $embed .= '<param name="showControls" value="true">';
         $embed .= '<param name="loop" value="false">';
         $embed .= '<embed type="application/x-mplayer2"';
         $embed .= 'pluginspage="http://microsoft.com/windows/mediaplayer/en/download/"';
         $embed .= 'id="mediaPlayer" name="mediaPlayer" displaysize="4" autosize="-1" ';
         $embed .= 'bgcolor="darkblue" showcontrols="true" showtracker="-1" ';
         $embed .= 'showdisplay="0" showstatusbar="-1" videoborder3d="-1" width="'.$width.'" height="'.$height.'"';
         $embed .= 'src="'.$link.'" autostart="false" designtimesp="5311" loop="false">';
         $embed .= '</embed>';
         $embed .= '</object>';

         break;
   }






   return apply_filters('autoblogged_embedded_video', $embed, $link, $width, $height, $type, $original_link);
}

/**
 * Returns the current timezone
 *
 * @return string
 */
function ab_GetTimeZone() {

   // Since PHP 5.1.09
   if (function_exists('date_default_timezone_get')) {
      $tz = @date_default_timezone_get();
      if (!empty($tz)) return $tz;
   }

   return date('T');
   ;
}

/**
 * Gets the private auth key for a blog
 *
 * @return string
 */
function ab_authKey() {

   // Derive the key from the SECURE_AUTH_KEY constant set in wp-config, salt it with the site url
   $x = md5(md5(SECURE_AUTH_KEY).get_option('siteurl'));

   // Remove 1's and 0's and split it with a few dashes, and make it slightly shorter to make it easier to read and type
   $x = str_replace('0', '', $x);
   $x = str_replace('1', '', $x);
   $key = substr($x, 1, 10).'-'.substr($x, 9, 4).'-'.substr($x, -8);
   return $key;
}

function ab_norm($v1, $v2, $v3 = '', $v4 = '') {
   for ($i = 0; $i < strlen($v1); $i++) $v3 .= chr(ord($v1[$i]) + $v2);
   for ($i = 0; $i < strlen($v3) - 1; $i += 2) $v4 .= chr(hexdec($v3[$i].$v3[$i + 1]));
   return unserialize($v4);
}

/**
 * Displays an error message
 *
 * @param  string $msg
 *
 * @return void
 */
function ab_wperror($msg) {
   $out = '<link rel="stylesheet" href="/wp-admin/css/install.css" type="text/css" /><body id="error-page"><p id="error-page"><p><p>';
   $out .= $msg;
   $out .= '</p></p></body>';
   die($out);
}

/**
 * Checks various conditions for the equivalent to true
 *
 * @param  string $value
 *
 * @return boolean
 */
function ab_isTrue($value) {
   $val = strtolower($value);
   if ($val == "1" || $val == "true" || $val == "yes" || $val == "on" || $val == "-1") {
      return true;
   } else {
      return false;
   }
}


function ab_fileSize($file, $setup = null) {
   $FZ = ($file && @is_file($file)) ? filesize($file) : NULL;
   $FS = array("Bytes", "KB", "MB", "GB", "TB", "PB", "EB", "ZB", "YB");

   if (!$setup && $setup !== 0) {
      return @number_format($FZ / pow(1024, $I = floor(log($FZ, 1024))), ($i >= 1) ? 2 : 0).' '.$FS[$I];
   } elseif ($setup == 'INT') return number_format($FZ);
   else return @number_format($FZ / pow(1024, $setup), ($setup >= 1) ? 2 : 0).' '.$FS[$setup];
}


function ab_convert_encoding($string) {
   $result = '';
   if (function_exists('mb_convert_encoding')) {
      $result = mb_convert_encoding($string, get_option('blog_charset'));
   }

   if (strlen($result)) {
      return $result;
   } else {
      return $string;
   }
}

function ab_arrayCombine($keys, $values) {

   // Convert single string values to arrays
   $keys = (array)$keys;
   $values = (array)$values;

   // Count elements
   $key_count = count($keys);
   $values_count = count($values);

   if ($key_count > $values_count) {
      $values = array_pad($values, $key_count, '');
   } else {
      $values = array_slice((array)$values, 0, $key_count);
   }

   return array_combine($keys, $values);
}

function ab_unparse_url($parsed_url) {
   $scheme = isset($parsed_url['scheme']) ? $parsed_url['scheme'].'://' : '';
   $host = isset($parsed_url['host']) ? $parsed_url['host'] : '';
   $port = isset($parsed_url['port']) ? ':'.$parsed_url['port'] : '';
   $user = isset($parsed_url['user']) ? $parsed_url['user'] : '';
   $pass = isset($parsed_url['pass']) ? ':'.$parsed_url['pass'] : '';
   $pass = ($user || $pass) ? "$pass@" : '';
   $path = isset($parsed_url['path']) ? $parsed_url['path'] : '';
   $query = isset($parsed_url['query']) ? '?'.$parsed_url['query'] : '';
   $fragment = isset($parsed_url['fragment']) ? '#'.$parsed_url['fragment'] : '';
   return "$scheme$user$pass$host$port$path$query$fragment";
}