<div class="wrap">
<style type="text/css">
a.tooltip {background:#F9F9F9;font-weight:bold;text-decoration:none;padding:2px 6px;}
a.tooltip:hover {background:#F9F9F9; text-decoration:none;} /*BG color is a must for IE6*/
a.tooltip span {display:none;font-weight:normal; padding:2px 3px; margin-left:8px; width:245px;-webkit-box-shadow: 5px 5px 6px 0px rgba(0,0,0,0.4);-moz-box-shadow: 5px 5px 6px 0px rgba(0,0,0,0.4);box-shadow: 5px 5px 6px 0px rgba(0,0,0,0.4);}
a.tooltip:hover span{display:inline; position:absolute; background:#ffffff; border:1px solid #cccccc; color:#6c6c6c;}
h3 a,h2 a {font-size:80%;text-decoration:none;margin-left:10px;}
</style>
<div id="wprobot" class="icon32"></div>
<h2><?php echo $result->name; ?>  <a href="?page=wpr-campaigns"><?php _e("&rarr; All Campaigns","wprobot") ?></a></h2>

<h3><?php _e("Controls","wprobot") ?></h3>
	<form id="campaigns" method="post">	
	<input type="hidden" value="<?php echo $result->id; ?>" name="delete[]"/>
	
	<div>
		<div style="height:80px;padding:8px;float:left;margin-right: 2%;width:64%;border:1px solid #CCCCCC;">
			<div style="float:left;margin-right: 50px;">
			<b><?php _e("Bulk Post","wprobot") ?></b><br/>
			<?php _e("Number of Posts:","wprobot") ?> <input size="3" style="background:#fff;" name="wpr_bulk" type="text" value="1"/><br/>	
			<input style="margin: 2px;" class="button-primary" type="submit" name="wpr_runnow" value="<?php _e("Post Now","wprobot") ?>"/>	
			</div>		
			<div style="float:left;margin-right: 10px;">			
			<input name="backdate" type="checkbox" id="backdate" value="yes" /> <b><?php _e("Backdate?","wprobot") ?></b><br/>
			<?php _e("Start Date:","wprobot") ?> <input size="11" style="background:#fff;" name="time" type="text" id="time" value="<?php echo date('Y-m-d'); ?>"/><br/>
			<?php _e("Between Posts:","wprobot") ?> <input size="3" style="background:#fff;" name="timespace" type="text" id="time" value="1"/> <?php _e("to","wprobot") ?> <input size="3" style="background:#fff;" name="timespace2" type="text" id="time" value="2"/> <?php _e("day(s)","wprobot") ?>
			</div>			
		</div>
		<div style="height:80px;padding:8px;float:left;width:30%;border:1px solid #CCCCCC;">
			<ul>
				<li>
					<?php if($result->pause == 0) { ?>
					<a href="?page=wpr-single&id=<?php echo $result->id; ?>&pause=<?php echo $result->id; ?>"><?php _e("Pause Campaign","wprobot") ?></a>
					<?php } else { ?>
					<a href="?page=wpr-single&id=<?php echo $result->id; ?>&unpause=<?php echo $result->id; ?>"><?php _e("Continue Campaign","wprobot") ?></a>
					<?php } ?>
				</li>
				<li><a href="?page=wpr-add&ccopy=<?php echo $result->id; ?>"><?php _e("Copy Campaign","wprobot") ?></a></li>
				<li><a href="?page=wpr-add&edit=<?php echo $result->id; ?>"><?php _e("Edit Campaign","wprobot") ?></a></li>
			</ul>
		</div>	
	</div>
	<div style="clear:both;"></div>

<h3><?php _e("Automatic Posts","wprobot") ?></h3>
<?php if ($result->pause != 1) { ?>
<p><?php _e("Next autopost will be created on","wprobot") ?> <?php echo date('m/j/Y H:i:s',wp_next_scheduled("wprobothook",array($result->id)));?><i> (<?php _e("every","wprobot") ?> <?php echo $result->cinterval." ".$result->period; ?>).</i></p>
<?php } else { ?>
<p><?php _e("Campaign has been <b>paused</b> and no autoposts will be created. You can unpause the campaign or use the following URL to set up a unix cron job in your hosts control panel:","wprobot") ?><br/>
<i><?php echo WPR_URLPATH ."cron.php?code=".get_option("wpr_cron")."&id=".$result->id; ?></i></p>

<?php } ?>
<h3><?php _e("Campaign Log","wprobot") ?> <a href="?page=wpr-log&id=<?php echo $result->id; ?>"><?php _e("&rarr; Full Log","wprobot") ?></a></h3>
<?php if ($errors) { ?>
<table width="60%" class="widefat post fixed" cellspacing="0">	
	<thead>
		<tr>
			<th width="15%"><?php _e("Time","wprobot") ?></th>		
			<th width="20%"><?php _e("Keyword","wprobot") ?></th>
			<th width="10%"><?php _e("Module","wprobot") ?></th>
			<th width="55%"><?php _e("Message","wprobot") ?></th>
		</tr>
	</thead>
	<tbody>	
	<?php foreach($errors as $error) {?>
		<tr <?php if($error->module == "") {if($error->reason == "Post created") {echo 'style="background:#CEF6CE;"';} elseif($error->reason == "Exclude" || $error->reason == "Duplicate Content") {echo 'style="background:#F2F5A9;"';} else {echo 'style="background:#F6CECE;"';}} ?>>
			<td><?php if($error->module == "") {echo "<strong>".$error->time."</strong>";} else {?> <img src="<?php echo WPR_URLPATH; ?>images/arrow.png" /> <?php } ?></td>		
			<td><a title="<?php _e("View full log for this keyword","wprobot") ?>" href="?page=wpr-log&id=<?php echo $error->campaign; ?>&keyword=<?php echo $error->keyword; ?>"><?php echo $error->keyword; ?></a></td>
			<td><?php echo $error->module; ?></td>
			<td><?php echo $error->message; ?></td>
		</tr>
	<?php }?>
	</tbody>	
</table>
<?php } else { ?>
<p><?php _e("Campaign log is empty.","wprobot") ?></p>
<?php } ?>

<h3><?php _e("Keywords Overview","wprobot") ?></h3>
	
	<form id="campaigns" method="post">	
<p class="input">
	<input onclick="return confirm('<?php _e("Are you sure you want to delete all selected keywords?","wprobot") ?>')" class="button-secondary" type="submit" name="deletekws" value="<?php _e("Delete Selected Keywords","wprobot") ?>"/>	
	<input class="button-secondary" type="submit" name="resetkws" value="<?php _e("Enable Disabled Keywords","wprobot") ?>"/>
	<!--Tooltip--><a class="tooltip" href="#">?<span><?php _e('If posting for the same keyword fails several times (according to the setting for your "Disable Keywords" option) it will be disabled and excluded from the campaign. Such keywords are displayed in red above. By using this button you can reset those keywords to be included again.',"wprobot") ?></span></a>
</p>	
<table width="60%" class="widefat post fixed" cellspacing="0">	
	<thead>
		<tr>
			<th class="manage-column column-cb check-column" style="" scope="col"><input type="checkbox"/>
			</th>	
			<?php if($result->ctype == "rss") { ?>
			<th id="title" class="manage-column column-title" style="" scope="col"><?php _e("Feeds","wprobot") ?></th>
			<?php  } elseif($result->ctype == "nodes") { ?>
			<th id="title" class="manage-column column-title" style="" scope="col"><?php _e("BrowseNodes","wprobot") ?></th>
			<?php  } ?>		
			<th id="title" class="manage-column column-title" style="" scope="col"><?php _e("Keywords","wprobot") ?></th>
			<th id="categories" class="manage-column column-categories" style="" scope="col"><?php _e("Categories","wprobot") ?></th>
			<th id="author" class="manage-column column-author" style="" scope="col"><?php _e("Posts Created","wprobot") ?></th>			
		</tr>
	</thead>
	<tfoot>
		<tr>
			<th class="manage-column column-cb check-column" style="" scope="col"><input type="checkbox"/>
			</th>		
			<?php if($result->ctype == "rss") { ?>
			<th id="title" class="manage-column column-title" style="" scope="col"><?php _e("Feeds","wprobot") ?></th>
			<?php  } elseif($result->ctype == "nodes") { ?>
			<th id="title" class="manage-column column-title" style="" scope="col"><?php _e("BrowseNodes","wprobot") ?></th>
			<?php  } ?>				
			<th class="manage-column column-title" style="" scope="col"><?php _e("Keywords","wprobot") ?></th>
			<th class="manage-column column-categories" style="" scope="col"><?php _e("Categories","wprobot") ?></th>
			<th class="manage-column column-author" style="" scope="col"><?php _e("Posts Created","wprobot") ?></th>			
		</tr>
	</tfoot>	
	<tbody>	
	
		<tr id="post-1575" class="alternate author-self status-publish iedit" valign="top">
			<td></td>
			<?php if($result->ctype == "keyword") { ?>
			<td class="keywords column-categories"><strong><?php echo count($keywords);?> <?php _e("keywords","wprobot") ?></strong></td>	
			<?php } elseif($result->ctype == "rss") { ?>
			<td class="keywords column-categories"><strong><?php echo count($keywords);?> <?php _e("RSS feeds","wprobot") ?></strong></td>			
			<td></td>
			<?php  } elseif($result->ctype == "nodes") { ?>
			<td class="keywords column-categories"><strong><?php echo count($keywords);?> <?php _e("BrowseNodes","wprobot") ?></strong></td>			
			<td></td>
			<?php  } ?>			
			<td class="categories column-categories"><strong><?php echo count($categories); ?> <?php _e("categories","wprobot") ?></strong></td>
			<td class="author column-author">
				<?php echo '<strong>'.$result->posts_created.'</strong>'; ?>		
			</td>

		</tr>		
      <?php foreach ($keywords as $num => $keyword) { ?>	
	
		<tr id="post-1575" class="alternate author-self status-publish iedit" valign="top">
			<th class="check-column" scope="row">
				<input type="checkbox" value="<?php if($result->ctype == "keyword") {echo $keyword[0];} elseif($result->ctype == "rss") {echo $keyword["feed"];} elseif($result->ctype == "nodes") {echo $keyword["node"];} ?>" name="kws[]"/>
			</th>
			<td class="keywords column-categories">
			<?php 
			
			if($result->ctype == "keyword") {
				$ec1 = $keyword[0];
			} elseif($result->ctype == "rss") {
				$ec1 = $keyword["feed"];
			} elseif($result->ctype == "nodes") {
				$ec1 = $keyword["bnn"] . " (".$keyword["node"].")";
			}	
			
			if($keyword["skipped"] == 0) {
				echo '<span>'.$ec1.'</span>';
			} elseif($keyword["skipped"] == 1) {
				echo '<span style="color:#FFCC33;">'.$ec1.'</span>';
			} elseif($keyword["skipped"] == 2) {
				echo '<span style="color:#FFCC00;">'.$ec1.'</span>';
			} elseif($keyword["skipped"] > 3 && $keyword["skipped"] < $options['wpr_err_disable']) {
				echo '<span style="color:#FF9900;">'.$ec1.'</span>';
			} elseif($keyword["skipped"] == $options['wpr_err_disable']) {
				echo '<span style="color:#FF0000;">'.$ec1.'</span>';
			} else {	
				echo '<span style="color:#FF0000;">'.$ec1.'</span>';
			}
	
			if($result->ctype == "keyword") {
			?>
				<div class="row-actions">
					<span class="view">
					<a rel="permalink" title="<?php _e("Create Post","wprobot") ?>" href="admin.php?page=wpr-single&id=<?php echo $result->id; ?>&wpr_runnow=<?php echo $result->id; ?>&keyword=<?php echo $keyword[0]; ?>"><?php _e("Post Now","wprobot") ?></a>
					|					
					</span>
					<span class="view">
					<a rel="permalink" title="<?php _e("View Log","wprobot") ?>" href="?page=wpr-log&id=<?php echo $result->id; ?>&keyword=<?php echo $keyword[0]; ?>"><?php _e("View Log","wprobot") ?></a>
					|					
					</span>	
					<span class="delete">
					<a class="submitdelete" onclick="return confirm('<?php _e("Are you sure you want to delete this keyword?","wprobot") ?>')" href="admin.php?page=wpr-single&kwdelete=1&id=<?php echo $result->id; ?>&keyword=<?php echo $keyword[0]; ?>" title="<?php _e("Delete this keyword","wprobot") ?>"><?php _e("Delete","wprobot") ?></a>
					</span>					
				</div>	
			</td>	
			
			<?php
			}
			if($result->ctype == "rss" || $result->ctype == "nodes") { ?>
			<td><?php echo $keyword[0]; ?></td>
			<?php  } ?>				
			
			<td class="categories column-categories"><?php $r = 0;if(is_array($categories[$num])) {foreach($categories[$num] as $cat) {echo $cat["name"];if(!empty($categories[$num][$r+1]["id"])) {echo ", ";}$r++;} }	?></td>
			<td class="author column-author">
				<?php echo $keyword[1]["total"]; ?>	
				<!--Tooltip--><a class="tooltip" href="#">?<span><?php _e('<b>Module Post Numbers</b><br/>',"wprobot") ?>
				<?php foreach($keyword[1] as $module => $posted) {echo $module.": ".$posted."<br/>";} ?>
				<?php _e('<br/><b>Important:</b> Those numbers are approximations only because they also include skipped posts.',"wprobot") ?>
				</span></a>
			</td>

		</tr>	
		<?php } ?>
	</tbody>
</table>	

</form>
<h3><?php _e("Templates","wprobot") ?> <a href="?page=wpr-add&edit=<?php echo $result->id; ?>"><?php _e("&rarr; Edit","wprobot") ?></a></h3>
<?php $i=1;foreach ($templates as $num => $template) { ?>
	<div style="float:left;width:280px;height:250px;border:1px solid #ccc;margin:0 10px 10px 0;padding:5px;background:#fff">
	<b><?php _e("Post Template","wprobot") ?> <?php echo $i; ?></b><br/>
	<?php _e("Chance:","wprobot") ?> <?php echo $template["chance"]; ?>%<br/>
	<textarea disabled name="title" rows="2" cols="30"><?php echo $template["title"]; ?></textarea><br/>
	<textarea disabled name="keywords" rows="5" cols="30"><?php echo $template["content"]; ?></textarea>
	</div>
<?php $i++;} ?>	 
</div>
