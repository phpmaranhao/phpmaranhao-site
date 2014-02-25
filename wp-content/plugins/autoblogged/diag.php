<?php

$php_ok = (function_exists('version_compare') && version_compare(phpversion(), '5.0.0', '>='));
$xml_ok = extension_loaded('xml');
$pcre_ok = extension_loaded('pcre');
$curl_ok = function_exists('curl_exec');
$zlib_ok = extension_loaded('zlib');
$mbstring_ok = extension_loaded('mbstring');
$iconv_ok = extension_loaded('iconv');

require_once(dirname(__FILE__).'/../../../wp-load.php');
check_admin_referer('autoblogged-nav')

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-US" lang="en-US">
<head>
   <title>AutoBlogged Diagnostic Test</title>
   <meta http-equiv="content-type" content="text/html; charset=UTF-8"/>
   <meta name="ROBOTS" content="NOINDEX,NOFOLLOW">
   <style type="text/css">


      table#chart {
         border-collapse: collapse;
      }

      table#chart th {
         text-align: left;
         background-color: #eee;
         padding: 2px 3px;
         border: 1px solid #fff;
      }

      table#chart td {
         text-align: left;
         padding: 2px 3px;
         border: 1px solid #eee;
      }

      table#chart tr.enabled td {
         background-color: #EAFFEA;

      }

      table#chart tr.disabled td,
      table#chart tr.disabled td a {
         background-color: #fcc;
         color: #c00;
      }

      table#chart tr.disabled td a {
         text-decoration: underline;
      }

      div.chunk {
         margin: 20px 0 0 0;
         padding: 0 0 10px 0;
         border-bottom: 1px solid #ccc;
      }

      .warning {
         padding: 3px;
         background-color: #FFEFED;
         color: #c00;
      }

      .footnote,
      .footnote a {
         font: 10px/12px verdana, sans-serif;
         color: #aaa;
      }

      .footnote em {
         background-color: transparent;
         font-style: italic;
      }
   </style>
</head>
<body>

<?PHP

switch ($_GET['type']) {

   case 'info':
      phpinfo();
      break;

   case 'time':
      ?>
      <div id="site">
         <div id="content">

            <div class="chunk">
               <h2>Time Settings</h2><br/><br/>
               <?php

               $server_utc = gmdate('D, d M Y H:i (g:i a)', current_time('timestamp', 0));
               $server_tz = date('T');
               $server_tz_offset = date("O") / 100 * 60 * 60 / 3600;
               $wp_local = date('D, d M Y H:i (g:i a)', current_time('timestamp', 0));
               $wp_tz = trim('UTC'.get_option('gmt_offset'));
               $wp_tz_offset = trim(get_option('gmt_offset'));

               $result = http_fetch('http://developer.yahooapis.com/TimeService/V1/getTime?appid=YahooDemo&output=php');

               if (!empty($result['contents'])) {
                  $timestamp = unserialize($result['contents']);
                  $official_utc = date('D, d M Y H:i (g:i a)', intval($timestamp['Result']['Timestamp']));
               }

               if (($server_tz_offset <> $wp_tz_offset) && ($server_utc == $wp_local)) {
                  ?>
                  <p class="warning">Notice that the time zone for your server is <code><?php echo $server_tz ?></code>
                     and the timezone
                     you have set in WordPress is <code><?php echo $wp_tz ?></code>. However, both your server and
                     WordPress are reporting
                     the same times. This may indicate that the server's time is not actually set as UTC.</p>

                  <?php
               }
               ?>



               <table cellpadding="0" cellspacing="0" border="0" width="100%" id="chart">
                  <tbody>

                     <?php
                  echo '<tr><td colspan="2"><b>Primary Time Settings</b></td></tr>';
                  echo '<tr><td>Server Time ('.$server_tz.')</td><td>'.$server_utc.'</td></tr>';
                  echo '<tr><td>WordPress Time ('.$wp_tz.')</td><td>'.$wp_local.'</td></tr>';

                  echo '<tr><td>Official UTC time</td><td>'.$official_utc.'</td></tr>';

                  echo '<tr><td colspan="2"><b>Other Time Settings</b></td></tr>';
                  echo '<tr><td>&nbsp;Apache Time Zone</td><td>'.getenv('TZ').'<td></tr>';


                  $sql = "SELECT @@global.time_zone, @@session.time_zone, IF(@@session.time_zone = 'SYSTEM', @@system_time_zone, @@session.time_zone);;";

                  $var = $wpdb->get_row($sql, ARRAY_N);
                  echo '<tr><td>&nbsp;MySQL Global Time Zone</td><td>'.$var[0].'</td></tr>';
                  echo '<tr><td>&nbsp;MySQL Session Time Zone</td><td>'.$var[1].'</td></tr>';
                  //echo '<tr><td>&nbsp;MySQL Time Zone Name</td><td>'.$var[2].'</td></tr>';
                  ?>
               </table>


            </div>
         </div>
      </div>

         <?php
      break;

   default:
      ?>
      <div id="site">
         <div id="content">

            <div class="chunk">
               <h2>AutoBlogged Diagnostic Tests</h2><br/><br/>
               <table cellpadding="0" cellspacing="0" border="0" width="100%" id="chart">


                  <?PHP
                  global $ab_options;

                  echo '<!-- ';
                  echo "\r\nSerial Number: ".$ab_options['sn']."\r\n";
                  echo 'Administrator Contact: '.get_option('admin_email')."\r\n";
                  echo '-->';
                  ?>

                  <!-- AutoBlogged Version -->
                  <tr class="<?php echo ($php_ok) ? 'enabled' : 'disabled'; ?>">
                     <td>AutoBlogged version</td>
                     <td><?php echo AB_VERSION ?></td>
                  </tr>


                  <!-- PHP Version -->
                  <tr class="<?php echo ($php_ok) ? 'enabled' : 'disabled'; ?>">
                     <td>PHP version</td>
                     <td>v<?php echo phpversion(); ?>
                        : <?php echo ($php_ok) ? ' passed' : 'Requires v5.0.0 or higher.'; ?></td>
                  </tr>


                  <!-- WordPress Version -->
                  <?PHP
                  require (ABSPATH.WPINC.'/version.php');
                  $wp_ok = version_compare($wp_version, '2.7', '>=');
                  ?>

                  <tr class="<?php echo ($wp_ok) ? 'enabled' : 'disabled'; ?>">
                     <td>WordPress version</td>
                     <td>v<?php echo $wp_version; ?>
                        : <?php echo ($wp_ok) ? ' passed' : 'Requires v2.7 or higher.'; ?></td>
                  </tr>


                  <!-- Server Version -->

                  <tr class="<?php echo ($wp_ok) ? 'enabled' : 'disabled'; ?>">
                     <td>Web Server</td>
                     <td><?php echo getenv('SERVER_SOFTWARE'); ?></td>
                  </tr>


                  <!-- MySQL Version -->
                  <?PHP
                  $MySQL = mysql_get_server_info();
                  $mysql_ok = version_compare($MySQL, '4.0.0', '>=');
                  ?>
                  <tr class="<?php echo ($mysql_ok) ? 'enabled' : 'disabled'; ?>">
                     <td>MySQL version</td>
                     <td>v<?php echo $MySQL; ?>
                        : <?php echo ($mysql_ok) ? ' passed' : 'Requires v4.0.0 or higher.'; ?></td>
                  </tr>


                  <!-- WP Theme -->

                  <tr class="<?php echo ($wp_ok) ? 'enabled' : 'disabled'; ?>">
                     <td>WordPress Theme</td>
                     <td><?php echo get_current_theme();  ?> </td>
                  </tr>


                  <tr class="<?php echo ($xml_ok) ? 'enabled' : 'disabled'; ?>">
                     <td><a href="http://php.net/xml">XML</a> extension</td>
                     <td><?php echo ($xml_ok) ? 'Enabled' : 'XML extension is not enabled'; ?></td>
                  </tr>


                  <tr class="<?php echo ($pcre_ok) ? 'enabled' : 'disabled'; ?>">
                     <td><a href="http://php.net/pcre">PCRE</a> extension</td>
                     <td><?php echo ($pcre_ok) ? 'Enabled' : 'PCRE extension is not enabled'; ?></td>
                  </tr>

                  <tr class="<?php echo ($zlib_ok) ? 'enabled' : 'disabled'; ?>">
                     <td><a href="http://php.net/zlib">Zlib</a> extension</td>
                     <td><?php echo ($zlib_ok) ? 'Enabled' : 'Disabled'; ?></td>
                  </tr>
                  <tr class="<?php echo ($mbstring_ok) ? 'enabled' : 'disabled'; ?>">
                     <td><a href="http://php.net/mbstring">mbstring</a> extension</td>
                     <td><?php echo ($mbstring_ok) ? 'Enabled' : 'Disabled'; ?></td>
                  </tr>
                  <tr class="<?php echo ($iconv_ok) ? 'enabled' : 'disabled'; ?>">
                     <td><a href="http://php.net/iconv">iconv</a> extension</td>
                     <td><?php echo ($iconv_ok) ? 'Enabled' : 'Disabled'; ?></td>
                  </tr>


                  <?PHP
                  $dnstest1 = gethostbynamel('autoblogged.com.');
                  echo '<!--';
                  print_r($dnstest1);
                  echo '-->';
                  $dnstest2 = gethostbynamel('google.com.');
                  $dns_ok = (is_array($dnstest1) || is_array($dnstest2))
                  ?>

                  <tr class="<?php echo ($dns_ok) ? 'enabled' : 'disabled'; ?>">
                     <td>DNS test</td>
                     <td><?php echo ($dns_ok) ? 'Passed' : 'DNS lookups failed'; ?></td>
                  </tr>


                  <?PHP
                  $safe_mode_check = ini_get('safe_mode');
                  if ($safe_mode_check) {
                     $safe_mode = "Enabled";
                  } else {
                     $safe_mode = "Disabled";
                  }

                  $openbasedir_check = ini_get('open_basedir');
                  if (strlen($openbasedir_check) == 0) {
                     $openbasedir = "Null";
                  } else {
                     $openbasedir = $openbasedir_check;
                  }
                  ?>
                  <tr class="<?php echo ($curl_ok) ? 'enabled' : 'disabled'; ?>">
                     <td><a href="http://php.net/curl">cURL</a> extension</td>
                     <td>
                        <?php echo (extension_loaded('curl')) ? 'cURL Installed' : 'cURL extension is not enabled' ?>
                        <br/>
                        safe_mode: <?php echo $safe_mode ?><br/>
                        open_basedir: <?php echo $openbasedir ?><br/>
                     </td>
                  </tr>

                  <?PHP
                  $curltest = http_fetch("http://autoblogged.com/curltest.txt");
                  if ($curltest['contents'] <> '-autoblogged-') {
                     $curltest_ok = false;
                     $curl_error = '('.$curltest['http_code'].') '.$curltest['error'];
                  } else {
                     $curltest_ok = true;
                  }
                  ?>

                  <tr class="<?php echo ($curltest_ok) ? 'enabled' : 'disabled'; ?>">
                     <td>HTTP retrieval test</td>
                     <td>
                        <?php echo (extension_loaded('curl')) ? 'Passed' : $curl_error ?><br/>
                     </td>
                  </tr>
                  </tbody>
               </table>
               <?PHP
               echo '<br /><h3>Character Sets</h3>';

               $SQL = "SELECT CHARACTER_SET_NAME FROM information_schema.COLUMNS WHERE `TABLE_SCHEMA` = '".DB_NAME."' AND `TABLE_NAME` = '".ab_tableName('posts')."posts' AND `COLUMN_NAME` = 'post_title'";
               $re = mysql_query($SQL);
               if ($re) {
                  $r = mysql_fetch_row($re);
                  $wppcharset = $r[0];
               }

               if ($wppcharset <> DB_CHARSET) {
                  ECHO '<p style="background-color:#fcc;color:#c00;">Warning: The WordPress character set does not match the MySQL character set. This may cause problems with the certain character sets. </p>';
               }

               echo '<b>WordPress:</b><br /> ';
               echo 'DB_CHARSET: '.DB_CHARSET.'<br />';
               echo 'DB_COLLATE: '.DB_COLLATE.'<br />';
               echo 'wp_posts table: '.$wppcharset;
               echo '<br/><br />';

               echo '<b>MySQL:</b><br /> ';

               $re = mysql_query('SHOW VARIABLES LIKE "%character_set_%";')or die(mysql_error());
               while ($r = mysql_fetch_assoc($re)) {
                  echo $r['Variable_name'].': '.$r['Value'].'<br />';
               }

               echo '<br /><b>Web Server:</b><br />';
               echo 'HTTP_ACCEPT_CHARSET: '.getenv('HTTP_ACCEPT_CHARSET');
               echo '<br />default_charset: '.getenv('default_charset');
               ?>
            </div>
         </div>
      </div>
         <?PHP break;
} ?>
</body>
</html>

<?PHP
function http_fetch($url, $timeout = 15) {
   $result = array();
   // Initialize curl
   $ch = @curl_init();
   if ($ch) {

      //CURLOPT_AUTOREFERER
      @curl_setopt($ch, CURLOPT_URL, $url);
      @curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      @curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
      @curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);

      //$follow_location = (ini_get('open_basedir') == true || ini_get('safe_mode') == true);
      $follow_location = true;
      @curl_setopt($ch, CURLOPT_FOLLOWLOCATION, $follow_location);
      @curl_setopt($ch, CURLOPT_MAXREDIRS, 5);

      // Grab the URL contents
      $result['contents'] = curl_exec($ch);
      $result['http_code'] = curl_getinfo($ch, CURLINFO_HTTP_CODE);
      $result['error'] = curl_error($ch);

      @curl_close($ch);
      return $result;
   }
}

