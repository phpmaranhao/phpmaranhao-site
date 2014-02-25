<?php

require_once(dirname(__FILE__).'/../../../wp-load.php');
check_admin_referer('autoblogged-nav')

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-US" lang="en-US">
<head>
   <title>AutoBlogged Help</title>
   <meta http-equiv="content-type" content="text/html; charset=UTF-8"/>
   <meta name="ROBOTS" content="NOINDEX,NOFOLLOW">
   <link rel="stylesheet" type="text/css" href="admin.css"/>
   <style type="text/css">

      h1 {
         font-family: Arial, Helvetica, sans-serif;
         font-size: 14pt;
      }

      a {
         font-family: Arial, Helvetica, sans-serif;
         font-size: 9pt;
      }

      table {
         border-collapse: collapse;
         font-family: Arial, Helvetica, sans-serif;
         font-size: 8pt;
      }

      table th {
         text-align: left;
         background-color: #eee;
         padding: 2px 3px;
         border: 1px solid #fff;
         font-size: 8pt;
      }

      table td {
         text-align: left;
         padding: 2px 3px;
         border: 1px solid #eee;
         font-size: 8pt;
         vertical-align: top;
      }

      table tr.enabled td {
         background-color: #EAFFEA;
         font-size: 8pt;
      }

      table tr.disabled td,
      tabletr.disabled td a {
         background-color: #fcc;
         color: #c00;
      }

      table tr.disabled td a {
         text-decoration: underline;
      }


   </style>
   <style type="text/css">.divHide {
      display: none;
   }

   .divShow {
      display: block;
   }</style>
   <script type="text/javascript" language="javascript">function showhide(obj) {
      var el = document.getElementById(obj);
      if (el.className == "divHide") {
         el.className = "divShow";
      } else {
         el.className = "divHide";
      }
   }
   </script>
</head>
<body>


<?php
flush();
if ($_GET['t'] == 'cron') {
   $url = ab_pluginURL().'/run.php?auth='.ab_authKey();
   $url_feed = $url.'&fid='.$_GET['fid'];
   ?>


<h1>Using External Cron Scheduling</h1>

<p>The external cron scheduling feature allows you to trigger AutoBlogged feed updates using an externally scheduled
   cron job. This method is much more reliable than the internal scheduler and reduces the load on the server. To use
   this feature, you need to load a special site-specific URL. </p>
<p>The URL to process all feeds on your site is:<br/>
   <?php echo '<code>'.$url.'</code>' ?>
<p>The URL to process just this feed is:<br/>
   <?php echo '<code>'.$url_feed.'</code>'?>

<p>To process these URLs using a <a href="http://en.wikipedia.org/wiki/Cron" target="_blank">unix-style cron</a>, you
   need to load the URL via HTTP using a web client such as <a href="http://en.wikipedia.org/wiki/Wget" target="_blank">Wget</a>
   or <a href="http://en.wikipedia.org/wiki/CURL" target="_blank">cURL</a>, or a web browser. Do not run the PHP script
   directly via the cron job as it needs to load WordPress via the web server. You can also use one of a number of <a
      href="http://www.google.com/search?q=free+cron+service" target="_blank">free web-based cron job services</a> to
   trigger these jobs.</p>


<p><a href="#debug1" onclick="showhide('debugdiv')"><img style="vertical-align: middle;"
                                                         src="<?php echo ab_pluginURL().'/img/plus.gif'; ?>"/>&nbsp;<b>Advanced
   Settings</b></a></p>
<div id="debugdiv" class="divHide">
   <p><b>Parameters</b></p>

   <p>The feed processing URL has parameters that give you more control over the feed processing and output. These
      parameters are as follows:<br/>

   <ul>
      <li><b><code>fid</code></b> - This is the ID of the feed as stored in the <?PHP echo ab_tableName(); ?> table in
         the database. Specifying this parameter will only process the feed with the associated ID.
      </li>
      <li><b><code>show</code></b> - Controls what output response is returned: v = verbose; d = debug; b (default) =
         returns "Success" or "Failure"
      </li>
      <li><b><code>min-time</code></b> - Do not process feeds unless specified number of seconds have elapsed since last
         feed run
      </li>
      <li><b><code>stats</code></b> - Set to <code>on</code> to show a summary of the feed operation.</li>
      <li><b><code>preview</code></b> - Set to <code>on</code> to simulate feed processing without adding any posts</li>
      <li><b><code>action</code></b> - Set to <code>preload-cache</code> to populate the feeds cache without processing
         any posts; set to <code>preview</code> to simulate feed processing without adding any posts
      </li>
   </ul>
   </p>
   <p><b>Example</b></p>

   <p>Run just this feed, show verbose output and stats:</p>

   <p><code><?php echo $url_feed ?>&show=v&stats=on</code></p>

   <p><b>Timing</b></p>
   To prevent excessive load on the server and prevent abuse, AutoBlogged will not process feeds unless at least 60
   seconds have elapsed since the last feed was processed (this can be modified by changing the value of <code>MIN_UPDATE_SECONDS</code>
   in <code>autoblogged.php</code>). You can override this value to something longer, but not shorter, by using the
   <code>min-time</code> parameter in the request URL.

   <p><b>Authentication Key</b></p>

   <p>The authentication key is derived from the <code>SECURE_AUTH_KEY</code> setting in your <code>wp-config.php</code>
      file, using the site URL as a salt to make it unique. This key is a semi-secret authentication token and should
      not be shared with others. However, if someone were to obtain this key, the greatest risk is that they could
      repeatedly load the URL and trigger an AutoBlogged update (but not faster than every 60 seconds as explained
      above), putting an extra resource load on the site.</p>

   <p>If you need to change the authentication key given above, you can do so by modifying the
      <code>SECURE_AUTH_KEY</code> value in <code>wp-config.php</code>.</p>

   <p><b>Additional Security Restrictions</b></p>
   To limit access to the cron job URL to specific IP addresses or add password authentication, we recommend using an <a
   href="http://www.askapache.com/htaccess/apache-authentication-in-htaccess.html" target="_blank">.htacess
   file</a>.</p>

   <p><b>Cache Preloading</b></p>

   <p>When working with a large number of feeds, you can improve performance and reduce memory overhead by preloading
      the RSS feed cache. To do this, you should schedule a cron job with the <code>action=preload-cache</code>
      parameter a few minutes before running your normal cron job.</p>
</div>
<p>&nbsp;</p>
<p>&nbsp;</p>

   <?PHP
}

if ($_GET['t'] == 'affil') {
   ?>

<h1>Affiliate Program</h1>

<p>If you are signed up for our <a href="http://autoblogged.com/about/affiliate-program/" target="_blank">affiliate
   program</a> you can include your affiliate ID in a link to AutoBlogged in the footer of your blog. If someone clicks
   through and purchases AutoBlogged for themeselves, you get 20% of the purchase price!</p>
<p>Once you are signed up to our affiliate program and generate an affiliate code, you will receive a link such as
   this:</p>
					<code>https://www.e-junkie.com/ecom/gb.php?cl=25009&c=ib&aff=<span
      style="background-color:PaleGoldenrod;"><b>987654</b></span>/code>
   <p>Your affiliate ID that you enter in the Settings page is the number after the <code>aff=</code> parameter, so in
      the above example, the affiliate ID would be <code>987654</code>.</p>

   <?PHP
}
if ($_GET['t'] == 'sysinfo') {
   ?>
   <h1>System Info</h1>

   <p>If you have checked "Attach system version info" the following information will be added to the bottom of your
      support ticket:</p>
   <?php
   $message .= "<pre>".'---SYSTEM CONFIGURATION---';
   $message .= "\n".'AutoBlogged Version: '.AB_VERSION;
   $message .= "\n".'PHP Version: '.phpversion();
   $message .= "\n".'WordPress Version: '.$wp_version;
   $message .= "\n".'MySQL Version: '.mysql_get_server_info();
   $message .= "\n".'WordPress Theme: '.get_current_theme();
   $message .= "\n".'Server: '.getenv('SERVER_SOFTWARE');
   $message .= "\n".'Serial Number: '.$ab_options['sn'];

   echo $message;
}


if ($_GET['t'] == 'excerpts') {
   ?>


   <h1>Excerpt Types</h1>
   <p>Excerpt types allow you to decide how you would like to extract the content from the original article. You can
      choose a text-only excerpt, the full HTML article, or the original content of the feed. The content created is
      accessible using the %excerpt% variable in your post templates. </p>
   <p>The options are as follows:</p>
   <p><strong>Text excerpt</strong><br/>
      This a text-only excerpt that removes all formatting and HTML content. This is the safest option since there is no
      active HTML or other content to exploit. This is also the most copyright friendly option since a small excerpt
      from an article is generally considered fair use. One drawback with this method is that paragraph and other
      formatting is also lost which may not work for some types of content. </p>
   <p><strong>Description</strong><br/>
      This is generally a summary of the article provided by the feed publisher. This field may or may not include HTML
      content depending on the feed publisher. On a WordPress blog, this field comes from the post's excerpt. </p>
   <p><strong>HTML Excerpt </strong><br/>
      This is an HTML excerpt taken from the original article. The HTML is sanitized.</p>
   <p><strong>Full Text HTML</strong><br/>
      This option allows you to get the full original article even if the RSS feed only contains a small excerpt. This
      option causes AutoBlogged to visit the original URL and extract the article text from the page. The HTML is passed
      through a filter to remove unsafe HTML elements. This feature is currently experimental and likely will not work
      with all feeds. You should use caution with this option as reproducing content without permission may violate the
      copyright of the content owner. </p>
   <p>Note that using the full text RSS feeds feature may increase the CPU and memory usage and may increase feed
      processing time. With large RSS feeds, this increase could be significant. Note also that this feature greatly
      depends on having PHP Tidy installed on your web server. </p>
   <p><strong>Unmodified Feed content</strong><br/>
      This is the original content in the feed that has not been filtered or modified in any way. This option could
      potentially be a security risk and should be used only with trusted content sources. <br/>
   </p>
   <p>Note that despite which excerpt type you select, you can always refer to any of them in post templates, custom
      field values, or custom taxonomy values. The variables you can use are as follows:</p>
   <p>%text_excerpt% - The text-only excerpt<br/>
      %description% - The feed's description value<br/>
      %html_excerpt% - Santizied HTML excerpt<br/>
      %full_text_html% - The full HTML original article<br/>
      %content% - The feed's original unmodified content</p>


   <?PHP
}
?>
</body>
</html>
