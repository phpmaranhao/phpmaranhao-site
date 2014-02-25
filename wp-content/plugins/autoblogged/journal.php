<?php

require_once(dirname(__FILE__).'/../../../wp-load.php');
check_admin_referer('autoblogged-nav')

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-US" lang="en-US">
<head>
   <title>AutoBlogged Journal Viewer</title>
   <meta http-equiv="content-type" content="text/html; charset=UTF-8"/>
   <meta name="ROBOTS" content="NOINDEX,NOFOLLOW">
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
</head>
<body>

<?php
if ($_GET['a'] == 'p') {
   $sql = 'TRUNCATE '.ab_tableName('autoblogged_journal');
   $ret = $wpdb->query($sql);
}
?>


<h1>AutoBlogged Schedule History</h1>

<p><a href="<?PHP echo wp_nonce_url(ab_pluginURL().'/journal.php?a=p&height=550&width=850', 'autoblogged-nav') ?>">Purge
   Log</a></p>
<table cellpadding="0" cellspacing="0" border="0" width="100%">
   <tr>
      <th>Feed</th>
      <th>Type</th>
      <th style="text-align:center">Time Started</th>
      <th style="text-align:center">Time Taken<br/>(Seconds)</th>
      <th style="text-align:center">Completed</th>
      <th style="text-align:center">Posts<br/>Added</th>
      <th style="text-align:center">Posts<br/>Skipped</th>
      <th style="text-align:center">Mem Usage<br/>(Bytes)</th>
      <th style="text-align:center">Avg Sys Load</th>
      <th style="text-align:center">Errors</th>
      <th style="text-align:center">Last URL/Keyword Completed</th>
   </tr>


   <?php

   $sql = 'SELECT * FROM '.ab_tableName('autoblogged_journal').' ORDER BY `timestamp` DESC';
   $results = $wpdb->get_results($sql, ARRAY_A);

   foreach ($results as $result) {
      echo '<tr>';
      echo '<td>'.$result['feed_name'].'</td>';
      echo '<td width="7%">'.ucfirst($result['type']).'</td>';
      echo '<td width="15%"style="text-align:center">'.$result['timestamp'].'</td>';
      echo '<td width="7%"style="text-align:center">'.$result['time_taken'].'</td>';
      echo '<td width="10%"style="text-align:center">'.($result['completed'] == true ? 'Yes' : 'No').'</td>';
      echo '<td width="5%" style="text-align:center">'.$result['new_posts'].'</td>';
      echo '<td width="5%" style="text-align:center">'.$result['skipped_posts'].'</td>';
      echo '<td width="5%" style="text-align:center">'.number_format($result['mem_usage']).'</td>';
      echo '<td width="5%" style="text-align:center">'.$result['sys_load_avg'].'</td>';
      echo '<td width="20%" style="text-align:left">'.$result['messages'].'</td>';
      echo '<td width="20%" style="text-align:left">'.$result['last_url'].'</td>';
      echo '</tr>';
   }
   ?>
</table>

</body>
</html>
