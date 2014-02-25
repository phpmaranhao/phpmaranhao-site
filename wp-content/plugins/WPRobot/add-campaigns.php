<div class="wrap">

<style type="text/css">
.addt {padding:5px;margin-bottom:10px;background:#FFF;border:1px solid #E5E5E5;box-shadow: 0 1px 1px 0 rgba(0, 0, 0, 0.1);}
.addt:hover {border:1px solid #CCC;}
div.expld {
	margin-top: 20px;
	padding:10px;
    background-color: #FFFFFF;
    border-left: 4px solid #AAA;
    box-shadow: 0 1px 1px 0 rgba(0, 0, 0, 0.1);
}
div.expld:hover {
    border-left: 4px solid #35A0CE;
}
<?php if($options['wpr_help'] == "Yes") {?>
a.tooltip {background:#F9F9F9;font-weight:bold;text-decoration:none;padding:2px 6px;}
a.tooltip:hover {background:#F9F9F9; text-decoration:none;} /*BG color is a must for IE6*/
a.tooltip span {display:none;font-weight:normal; padding:2px 3px; margin-left:8px; width:245px;-webkit-box-shadow: 5px 5px 6px 0px rgba(0,0,0,0.4);-moz-box-shadow: 5px 5px 6px 0px rgba(0,0,0,0.4);box-shadow: 5px 5px 6px 0px rgba(0,0,0,0.4);}
a.tooltip:hover span{display:inline; position:absolute; background:#ffffff; border:1px solid #cccccc; color:#6c6c6c;}
<?php } else {?>
a.tooltip {display:none;}
<?php } ?>
</style>
<script type="text/javascript">
function toggle_visibility(id) {
	var e = document.getElementById(id);
	if(e.style.display == 'block')
		e.style.display = 'none';
	else
		e.style.display = 'block';
}

</script>
<div id="wprobot" class="icon32"></div>
<h2><?php if( $_GET['edit'] ) {_e("Edit Campaign","wprobot");} else { _e("Add New Campaign","wprobot");} ?> <a style="font-size:60%;" href="#" onclick="toggle_visibility('stepbystep');"><?php _e("&rarr; Show step-by-step Instructions","wprobot") ?></a></h2>

<form method="post" id="wpr_new">

	<?php if( $_GET['edit'] ) { ?>
		<input name="edit" type="hidden" value="<?php echo $_GET['edit']; ?>"/>
	<?php } ?>

	<div style="width:25%;float:right;";>
		<!--<?php if($options['wpr_simple']!='Yes' ) { ?>
		<div class="expld">
			<strong><?php _e("Quick Template Setup","wprobot") ?></strong><br/>
			<?php _e("Add all Post template presets as templates:","wprobot") ?><br/>
			<input class="button" type="submit" name="quick" value="<?php _e("Quick Template Setup","wprobot") ?>" /><br/>
			<?php _e("Add 3-6 random Post template presets:","wprobot") ?><br/>
			<input class="button" type="submit" name="quickrand" value="<?php _e("Random Template Setup","wprobot") ?>" /><br/>
		</div>
		<?php } ?>
		
		<div class="expld">
			<strong><?php _e("Exact Match","wprobot") ?></strong><br/>
			<?php _e('Use "keyword" in the Keyword field to enable exact match for a keyword.',"wprobot") ?>
			<input class="button" type="submit" name="exact" value="<?php _e("Convert all keywords to exact","wprobot") ?>" />
		</div>	-->
		
		<?php if(get_option('ma_poststatus') != "") {?>
		<div class="expld">
			<strong><?php _e("Import","wprobot") ?></strong><br/>
			<input class="button" type="submit" name="importwpr2" value="<?php _e("Import WP Robot 2 Keywords","wprobot") ?>" />
		</div>			
		<?php } ?>	
		
		<div class="expld">	
			<strong><?php if($options['wpr_simple']!='Yes' ) { _e("Content Template Tags","wprobot"); } else { _e("Mixed Template Tags","wprobot"); } ?></strong><br/>	
			<p><?php _e("To combine different modules in a post you can add any of the following template tags to the 'Post Content' field on the left.","wprobot") ?></p>
			<?php foreach($wpr_loadedmodules as $loadedmodule) {if($loadedmodule != "translation" && !in_array($loadedmodule, $wpr_disabledmodules)){echo "{".$loadedmodule."}<br/>";}} ?>
			<br/>{keyword}<br/>{catlink}<br/>{amazonlist:x}<br/><a href="http://wprobot.net/documentation/#631"><?php _e("Random Tags","wprobot") ?></a><br/><a href="http://wprobot.net/documentation/#63"><b><?php _e("See Documentation","wprobot") ?></b></a>
		</div>		
		
		<?php if($options['wpr_simple']!='Yes' ) { ?>
		<div class="expld">	
			<strong><?php _e("Title Template Tags","wprobot") ?></strong><br/>{title}<br/>	
			<?php foreach($wpr_loadedmodules as $loadedmodule) {if($loadedmodule != "translation" && !in_array($loadedmodule, $wpr_disabledmodules)){echo "{".$loadedmodule."title}<br/>";}} ?>
			<br/>{keyword}<br/><a href="http://wprobot.net/documentation/#631"><?php _e("Random Tags","wprobot") ?></a><br/><a href="http://wprobot.net/documentation/#63"><b><?php _e("See Documentation","wprobot") ?></b></a>
		</div>		
		<?php } ?>
		
	</div>
	<div style="width:70%;position:relative;">		
	
	<div id="stepbystep" class="expld" style="display:none;">
		<?php _e('<p>There are several <a href="http://wprobot.net/videos/"><strong>campaign creation video tutorials</strong></a> available. Be sure to watch them if you have problems!</p>
		<p>The following steps are required to create a simple keyword campaign in WP Robot and start posting immediatelly:<br/><br/>
		(optional) <strong>1.</strong> Choose a custom <strong>Name</strong> for your campaign.<br/><br/>
		<strong>2.</strong> Enter the <strong>Keywords</strong> you want to create automatic posts for on your blog. You can enter any number of words into the Keywords field with each word being on a separate line.<br/><br/>
		<strong>3.</strong> Enter the Wordpress <strong>Categories</strong> you want to have the automatic posts added to. You can either enter a single category or one category for each keyword you have entered - in this case the category that is on the same line as the keyword will be used. By default WP Robot will create all categories that do not yet exist on your weblog.<br/><br/>
		(optional) <strong>4.</strong> Change the post interval in the <strong>Post every</strong> field to your liking.<br/><br/>
		<strong>5.</strong> Choose the <strong>Templates</strong> you want to use in your campaign. The Templates decide what kind of content will be posted. You can have any number of templates in a single campaign in which case a random one will be selected for each post.<br/>
		- To only post content from one specific module in your campaign (i.e. only Amazon products) find the <strong>Load Template Preset</strong> text and load the default template of your desired module with the dropdown menu and the "Load" button.<br/>
		- To post content from all your modules use the <strong>Quick Template Setup</strong> button in the top right corner - this will instantly add all post template presets to your campaign.<br/><br/>
		(optional) <strong>6.</strong> Edit any options in the <strong>Optional Settings</strong> section if you want to. All the settings you can find there are explained when hovering over the "?" button next to its name!<br/><br/>
		<strong>7.</strong> Click <strong>Create Campaign</strong> to finish!<br/><br/>
		Please Note: If campaign creation seems to complex for you in the beginning you can enable the <strong>Simple Mode</strong> on the WP Robot "Options" screen which simplifies the process.
		</p>',"wprobot") ?>
		<a href="#" onclick="toggle_visibility('stepbystep');"><?php _e("&rarr; Hide","wprobot") ?></a>
	</div>
	
		<p class="submit" style="float:right;margin:0;padding: 10px 0;"><input class="button-primary" type="submit" name="wpr_add" value="<?php if(!$_GET["edit"]) {_e("Create Campaign","wprobot");} else {_e("Update Campaign","wprobot");} ?>" /></p>
	
	<input type="hidden" name="type" value="<?php echo $_POST['type']; ?>" />
	<?php if(function_exists("wpr_rsspost") || function_exists("wpr_amazonpost")) { ?>
	<?php _e("Campaign Type:","wprobot") ?>
	<input class="<?php if($_POST['type'] == "keyword") {echo "button-primary";} else {echo "button";} ?>" type="submit" name="type1" value="<?php _e("Keyword Campaign","wprobot") ?>" />
	<?php if(function_exists("wpr_rsspost")) { ?><input class="<?php if($_POST['type'] == "rss") {echo "button-primary";} else {echo "button";} ?>" type="submit" name="type2" value="<?php _e("RSS Campaign","wprobot") ?>" /><?php } ?>
	<?php if(function_exists("wpr_amazonpost")) { ?><input class="<?php if($_POST['type'] == "nodes") {echo "button-primary";} else {echo "button";} ?>" type="submit" name="type3" value="<?php _e("BrowseNode Campaign","wprobot") ?>" /><?php } ?>
	<!--Tooltip--><a class="tooltip" href="#">?<span><?php _e('A <b>Keyword Campaign</b> searches for and posts content related to the keywords you enter.<br/><br/>A <b>RSS Campaign</b> posts content from RSS feeds. Keywords can be used optionally to use other modules and filter the RSS feeds.<br/><br/>A <b>BrowseNode Campaign</b> posts content found in the Amazon BrowseNodes you specify. Keywords can be used optionally for other modules.<br/><br/>The blue button marks the campaign type currently selected.',"wprobot") ?></span></a>
	<?php } ?>	
		
	<p><?php _e("Name your campaign:","wprobot") ?> <input name="name" type="text" value="<?php echo $_POST['name'];?>"/><!--Tooltip--><a class="tooltip" href="#">?<span><?php _e('The name is only used to organize your campaigns. It does not affect posting.',"wprobot") ?></span></a></p>
		
	<h3 style="text-transform:uppercase;border-bottom: 1px solid #ccc;"><?php _e("Main Settings","wprobot") ?></h3>

	<table class="addt" width="100%">
		<?php if($_POST['type'] == "rss") { ?>
		<tr>
			<td colspan="2">
				<b><?php _e("RSS Feeds","wprobot") ?></b> <?php _e("(one per line)","wprobot") ?>
				<!--Tooltip--><a class="tooltip" href="#">?<span><?php _e('Enter one RSS Feed URL per line. The feeds correspond to the keyword and category on the same line, i.e. the RSS Feed on line 2 belongs to the keyword and category on line 2 in the respective fields.<br/><br/><b>Keywords are optional!</b> They can be used to also post content from other modules in this RSS Campaign and/or filter the content of the RSS feeds.',"wprobot") ?></span></a><br/>
				<textarea name="feeds" rows="5" cols="75"><?php echo $_POST['feeds'];?></textarea>				
			</td>
		</tr>
		<?php } elseif($_POST['type'] == "nodes") { ?>
		<tr>
			<td width="50%">
				<b><?php _e("Amazon BrowseNodes","wprobot") ?></b> <?php _e("(one per line)","wprobot") ?>
				<!--Tooltip--><a class="tooltip" href="#">?<span><?php _e('Enter one Amazon BrowseNode per line. The BrowseNode corresponds to the keyword and category on the same line, i.e. the BrowseNode on line 2 belongs to the keyword and category on line 2 in the respective fields.<br/><br/><b>Keywords are optional!</b> They can be used to also post content from other modules in this BrowseNode Campaign.',"wprobot") ?></span></a><br/>
				<textarea name="nodes" rows="5" cols="33"><?php echo $_POST['nodes'];?></textarea>				
			</td>
			<td>
			<?php _e('- BrowseNodes are used by Amazon to categorize all their products.<br/>
			- <strong>Important:</strong> You have to specify the correct Amazon Department the BrowseNodes belong to!<br/>
			- Find BrowseNodes on <a target="_blank" href="http://browsenodes.com">browsenodes.com</a>.',"wprobot") ?>
			</td>
		</tr>		
		<?php } ?>		
		<tr>
			<td width="50%">
				<b><?php _e("Keywords","wprobot") ?></b> <?php _e("(one per line)","wprobot") ?>
				<!--Tooltip--><a class="tooltip" href="#">?<span><?php _e('Enter one keyword or phrase per line. For each post one of the keywords will be randomly selected.<br/><br/>You can enclose keywords in double quotes to enable exact search for them.<br/><br/><b>Example</b><br/>Banana<br/>"Wordpress Plugins"<br/>Free Apple Ipods',"wprobot") ?></span></a>
				<br/>
				<textarea name="keywords" rows="5" cols="33"><?php echo stripslashes($_POST['keywords']);?></textarea>
			</td>
			<td valign="top">
				<input size="5" name="multisingle" type="hidden" value="<?php echo $_POST['multisingle']; ?>"/>
				<?php if($_POST['multisingle'] == "multi") {$txt = __("Single","wprobot");} else {$txt = __("Multi","wprobot");} ?>
				<b><?php _e("Categories","wprobot") ?></b> <?php _e("(one per line)","wprobot") ?> <input class="button" type="submit" name="catbut" value="<?php echo $txt; ?>" />
				<!--Tooltip--><a class="tooltip" href="#">?<span><?php _e('Enter one category per line. Each category will corespond to the keyword on the same line, i.e. the keyword on line 2 will be posted to the category on line 2.<br/><br/>You can also enter a single category instead. Then all posts will be made in this category.<br/><br/><b>Example</b><br/>Banana Category<br/>Wordpress Plugins<br/>Free Apple Ipods',"wprobot") ?> <?php _e("<br/><br/>New: Since WP Robot v3.4 you can also enter multiple comma-separated categories for a single keyword, e.g. enter 'Dogs,Photos' to have posts for the keyword on the same line added to both the 'Dogs' and 'Photos' categories.","wprobot") ?></span></a>
				<br/>
				<?php if($_POST['multisingle'] == "multi") {?>
				<textarea name="categories" rows="5" cols="33"><?php echo stripslashes($_POST['categories']);?></textarea>
				<?php } else {
					echo '<select name="categories">';		
				   	$categories = get_categories('type=post&hide_empty=0');
				   	foreach($categories as $category) {
						if(!empty($category->parent)) {$par = "";} else {$par = "-";}
						if($category->cat_ID == $_POST['categories']) {$sel = " selected";} else {$sel = "";}
				   		echo '<option value="'.$category->cat_ID.'"'.$sel.'>'.$par.$category->cat_name.'</option>';
				   	}
					echo '</select>';		
					}	
				?>
			</td>
		</tr>
		<tr>
			<td>
				<input name="autopost" type="checkbox" value="yes" <?php if ($_POST['autopost'] =='yes') {echo "checked";} ?>/> <?php _e("Post every","wprobot") ?> <input size="5" name="interval" type="text" value="<?php echo $_POST['interval'];?>"/>
					<select name="period">
						<option value="hours" <?php if ($_POST['period'] =='hours') {echo "selected";} ?>><?php _e("Hours","wprobot") ?></option>
						<option value="days" <?php if ($_POST['period'] =='days') {echo "selected";} ?>><?php _e("Days","wprobot") ?></option>
					</select>	
				<!--Tooltip--><a class="tooltip" href="#">?<span><?php _e('If enabled Wordpress internal cron functions will be used to run this campaign in the interval you specify.<br/><br/>Alternatively you can disable the option and then set up a unix cron-job to autopost this campaign after creating it.',"wprobot") ?></span></a>		
			</td>
			<td>
				<input name="createcats" type="checkbox" value="yes" <?php if ($_POST['createcats'] =='yes') {echo "checked";} ?>/> <?php _e("Create categories if not existing","wprobot") ?>
			</td>
		</tr>		
	</table>
	
	<?php if($options['wpr_simple']=='Yes' && $_POST['type'] == "keyword") { ?>
	<h3 style="text-transform:uppercase;border-bottom: 1px solid #ccc;"><?php _e("Modules","wprobot") ?></h3>	
	<table class="addt" width="100%">	
	
		<?php $ev = 0;foreach($wpr_loadedmodules as $lmodule) { if($lmodule != "translation" && $lmodule != "rss") { ?>
			<?php if (!$ev % 2 && $ev > 0) { ?><tr valign="top"><?php } ?>
				<td>
					<input size="3" name="<?php echo $lmodule; ?>chance" type="text" value="<?php echo $_POST[$lmodule."chance"];?>"/>% <strong><?php echo ucwords($lmodule)."</strong>";_e(" Posts","wprobot") ?>
					<!--Tooltip--><a class="tooltip" href="#">?<span><?php _e('The <strong>chance the '.ucwords($lmodule).' Module is used for a post by this campaign</strong>. Chances for all modules have to sum up to 100 in total.<br/><br/>How a '.ucwords($lmodule).' post will look is decided by its module template which you can edit on the WP Robot "Templates" screen.',"wprobot") ?></span></a>		
				</td>
			<?php if ($ev % 2 && $ev > 0) { ?></tr>	<?php } ?>
		<?php $ev++; } } ?>
		<tr valign="top">	
			<td><br/>
				<input size="3" name="mixchance" type="text" value="<?php echo $_POST["mixchance"];?>"/>% <?php _e("<strong>Mixed</strong> Posts","wprobot") ?>
				<!--Tooltip--><a class="tooltip" href="#">?<span><?php _e('The chance a mixed post is created for this campaign.<br/><br/>Here you can mix any of your modules together into single posts by combining several of the <strong>Mixed Template Tags</strong> you can find in the sidebar to the right in the field below. The title of the first module will be used as post title.',"wprobot") ?></span></a>	
				<br/>
				<textarea name="mixcontent" rows="3" cols="33"><?php echo stripslashes($_POST["mixcontent"]);?></textarea>
			</td>
		</tr>		
	</table>
	
	<?php } else { ?>
	<h3 style="text-transform:uppercase;border-bottom: 1px solid #ccc;"><?php _e("Templates","wprobot") ?></h3>	

	<p><?php _e("What kind of content do you want to post with this campaign?","wprobot") ?></p>
	
		<?php //if(!$_POST['tnum']) {$_POST['tnum'] = 1;} ?>
		<input size="5" name="tnum" type="hidden" value="<?php echo $_POST['tnum']; ?>"/>
		
	<div class="addt">
	<p class="submit" style="float:right;margin:0;padding: 10px 0;">
		<!--<input class="button" type="submit" name="evenchance" value="<?php _e("Even Chances","wprobot") ?>" />-->
		<input class="button" type="submit" name="quick" value="<?php _e("Quick Template Setup","wprobot") ?>" />
		<a class="tooltip" href="#">?<span><?php _e('Add all available post templates to this campaign in order to use all your modules / content sources.',"wprobot") ?></span></a>
	</p>
	<p class="submit" style="margin:0;padding: 10px 0;">
	<input class="button" type="submit" name="wpr_add_template" value="<?php _e("Add Post Template","wprobot") ?>" />
	<?php _e("with content preset:","wprobot") ?>
				<select name="wpr_add_template_preset">
					<option><?php _e('Random',"wprobot") ?></option>
					<?php 
						foreach($presets as $num => $preset) {
							echo '<option>'.$num.'</option>';
						}						
					?>
				</select>	
	<!--Tooltip--><a class="tooltip" href="#">?<span><?php _e('Adds an additional template that will be used for this campaign. Templates decide what content gets posted. For each post one of all the templates will be randomly selected.<br/><br/>You can also use the quick setup buttons on the right to add a large amount of random templates fast.',"wprobot") ?></span></a>
	</p></div>	
	
	<?php if($_POST['tnum'] == 0 && $skippedx > 0) { ?>
		
		<div class="addt"><p><?php _e("<strong>Please note:</strong> ","wprobot"); echo $skippedx; _e(" of your modules were hidden. You need to configure them on the Options page first.","wprobot"); ?></p></div>		
		
	<?php } ?>		
	
	<?php if($_POST['tnum'] == 0 || empty($_POST['tnum'])) { ?>
		
		<div class="addt"><p><?php _e('<strong>No templates added.</strong> You need to add at least one template with the "Add Template" or "Quick Setup" buttons above.',"wprobot"); ?></p></div>		
		
	<?php } ?>	
		
		<?php for ($i = 1; $i <= $_POST['tnum']; $i++) { ?>
	<table class="addt" id="pt-<?php echo $_POST['tnum']; ?>" width="100%">		
		<tr valign="top">
			<td width="50%">
				<strong style="font-size:120%;border-bottom:1px dotted #ccc;"><?php _e("Post Template","wprobot") ?> <?php echo $i; ?> <?php if(!empty($_POST["name$i"])) { ?><input name="name<?php echo $i; ?>" type="hidden" value="<?php echo $_POST["name$i"]; ?>"/>( <?php echo $_POST["name$i"]; ?> )<?php } ?></strong><br/>
				<?php _e("Chance of being used:","wprobot") ?> <input size="5" name="chance<?php echo $i; ?>" type="text" value="<?php echo $_POST["chance$i"];?>"/> %	<!--Tooltip--><a class="tooltip" href="#">?<span><?php _e('For each post one of the templates you have added to this campaign will be selected and used randomly. This setting decides how likely it is that this particular template gets selected. <b>The chance fields for all templates have to sum up to 100% in total!</b>',"wprobot") ?></span></a>				
				<br/><input class="button" type="submit" name="delete<?php echo $i; ?>" value="<?php _e("Remove Template","wprobot") ?>" />			
			</td>
			<td>
				<strong><?php _e("Post Title","wprobot") ?></strong>
				<!--Tooltip--><a class="tooltip" href="#">?<span><?php _e('This is the template field for the title of posts.<br/><br/>- Template tags in curly braces, i.e. {amazontitle}, will be replaced with the title returned by the respective module. See the list to the right for all available template tags.<br/><br/>- You can use template tags several times but beware that Wordpress has a length limit of 255 characters on titles. Longer titles will get cut.<br/><br/>- You can also enter any other text you want to display in your titles.',"wprobot") ?></span></a><br/>
				<textarea name="title<?php echo $i; ?>" rows="2" cols="33"><?php echo stripslashes($_POST["title$i"]);?></textarea>
			</td>
		</tr>	
		<tr>
			<td>
			</td>
			<td rowspan="3">
				<strong><?php _e("Post Content","wprobot") ?></strong>
				<!--Tooltip--><a class="tooltip" href="#">?<span><?php _e('This is the template field for the body of posts.<br/><br/>- Template tags in curly braces, i.e. {amazon}, will be replaced with the content returned by the respective module. See the list to the right for all available template tags.<br/><br/>- You can use template tags several times to add more items to the posts, i.e. 2 Amazon products.<br/><br/>- You can also enter any other text or HTML code you want to display in your posts.',"wprobot") ?></span></a><br/>
				<textarea name="content<?php echo $i; ?>" rows="5" cols="33"><?php echo stripslashes($_POST["content$i"]);?></textarea>
			</td>
		</tr>
		<tr>
			<td>
	
			</td>
		</tr>
		<tr>
			<td>

			</td>
		</tr>	
		<?php 

		$displ_comments = 0;
		if(strpos($_POST["content$i"], '{amazon}') !== false) {$amazon_comments = 1;$displ_comments = 1;} else {$amazon_comments = 0;}
		if(strpos($_POST["content$i"], '{youtube}') !== false) {$youtube_comments = 1;$displ_comments = 1;} else {$youtube_comments = 0;}
		if(strpos($_POST["content$i"], '{flickr}') !== false) {$flickr_comments = 1;$displ_comments = 1;} else {$flickr_comments = 0;}
		if(strpos($_POST["content$i"], '{yahooanswers}') !== false) {$yahoo_comments = 1;$displ_comments = 1;} else {$yahoo_comments = 0;}

		if($displ_comments == 1) { ?>
		<tr>	
			<td valign="top">
				<!--<div style="padding:5px;margin-right: 10px;border:1px dotted #ccc;">
					<?php _e("Current Preset:","wprobot") ?> <?php echo $_POST["p$i"]; ?><br/>
					<?php _e("Load Template Preset:","wprobot") ?>
					<a class="tooltip" href="#">?<span><?php _e('Loads a different Page Template Preset for this template. You can set up and change your presets on the "Templates" page of WP Robot.<br/><br/>Use the "Remove Template" button to remove this template from this campaign.',"wprobot") ?></span></a><br/>
					<select name="p<?php echo $i; ?>">
						<?php 
							foreach($presets as $num => $preset) {
								if($num == $_POST["p$i"]) {$selected = " selected";} else {$selected = "";}
								echo '<option'.$selected.'>'.$num.'</option>';
							}						
						?>
					</select>
					<input class="button" type="submit" name="load<?php echo $i; ?>" value="<?php _e("Load","wprobot") ?>" />
					
				</div>	-->	
			</td>
			
			<td>
			<strong><?php _e("Post Comments","wprobot") ?></strong>
			<a class="tooltip" href="#">?<span><?php _e('Choose the content you want to add to posts made with this template as comments.<br/><br/><b>Important:</b>You can only add comments for modules that are also present in your templates content, i.e. Amazon reviews can only be added if at least one {amazon} tag exists in the "Post Content" field of this template.',"wprobot") ?></span></a><br/>
			<?php if($amazon_comments == 1) { ?>
			<input type="checkbox" name="comments_amazon<?php echo $i; ?>" value="1" <?php if(!function_exists("wpr_amazonpost")) {echo "disabled";} elseif($_POST["comments_amazon$i"]) {echo "checked";} ?>/> <?php _e("Amazon reviews","wprobot") ?><br/>
			<?php } if($flickr_comments == 1) { ?>
			<input type="checkbox" name="comments_flickr<?php echo $i; ?>" value="1" <?php if(!function_exists("wpr_flickrpost")) {echo "disabled";} elseif($_POST["comments_flickr$i"]) {echo "checked";} ?>/> <?php _e("Flickr comments","wprobot") ?><br/>
			<?php } if($yahoo_comments == 1) { ?>
			<input type="checkbox" name="comments_yahoo<?php echo $i; ?>" value="1" <?php if(!function_exists("wpr_yahooanswerspost")) {echo "disabled";} elseif($_POST["comments_yahoo$i"]) {echo "checked";} ?>/> <?php _e("Yahoo Answers answers","wprobot") ?><br/>
			<?php } if($youtube_comments == 1) { ?>
			<input type="checkbox" name="comments_youtube<?php echo $i; ?>" value="1" <?php if(!function_exists("wpr_youtubepost")) {echo "disabled";} elseif($_POST["comments_youtube$i"]) {echo "checked";} ?>/> <?php _e("Youtube comments","wprobot") ?>
			<?php } ?>
			</td>
				
		</tr>
		<?php } ?>
	</table>			
		<?php } ?>
	<?php } ?>
	
	<h3 style="text-transform:uppercase;border-bottom: 1px solid #ccc;"><?php _e("Optional Settings","wprobot") ?></h3>	
	
	<table class="addt" width="100%">
		<tr>
			<td width="50%">
				<strong><?php _e("Replace Keywords","wprobot") ?></strong> <?php _e("(one per line)","wprobot") ?>
				<!--Tooltip--><a class="tooltip" href="#">?<span><?php _e('Use this option to replace certain keywords in this campaigns posts with other keywords or links.<br/><br/><b>Syntax</b><br/>Keyword|Replace With|Chance<br/><br/><b>Example</b><br/>Wordpress|Joomla|50<br/>Free Apple Ipods|Expensive Apple Ipods|100<br/>Wordpress Plugins|< a href=link>Link text< /a>|25',"wprobot") ?></span></a><br/>
				<textarea name="replace" rows="3" cols="33"><?php echo stripslashes($_POST['replace']);?></textarea>
			</td>	
			<td>
				<strong><?php _e("Exclude Keywords","wprobot") ?></strong> <?php _e("(one per line)","wprobot") ?>
				<!--Tooltip--><a class="tooltip" href="#">?<span><?php _e('If any one of the keywords you enter here is found in a post it will be skipped and not created. Because of that make sure not to enter common words or phrases here.<br/><br/><b>Example</b><br/>exclude keyword 1<br/>keyword 2',"wprobot") ?></span></a><br/>
				<textarea name="exclude" rows="3" cols="33"><?php echo stripslashes($_POST['exclude']);?></textarea>
			</td>			
		</tr>
		<?php if(function_exists("wpr_amazonpost") || function_exists("wpr_ebaypost")) { ?>
		<tr>
			<td width="50%">
				<?php if(function_exists("wpr_amazonpost")) { ?>
				<strong><?php _e("Amazon Department","wprobot") ?></strong><br/>
				<?php $ll = $options["wpr_aa_site"];?>
				<select name="amazon_department">
					<option value="All" <?php if($_POST['amazon_department'] == "All") {echo "selected";}?>><?php _e("All","wprobot") ?></option>
					<?php if($ll!="fr" || $ll!="ca") {?><option value="Apparel" <?php if($_POST['amazon_department'] == "Apparel") {echo "selected";}?>><?php _e("Apparel","wprobot") ?></option><?php } ?>
					<?php if($ll=="com" || $ll=="de") {?><option value="Automotive" <?php if($_POST['amazon_department'] == "Automotive") {echo "selected";}?>><?php _e("Automotive","wprobot") ?></option><?php } ?>
					<?php if($ll!="ca") {?><option value="Baby" <?php if($_POST['amazon_department'] == "Baby") {echo "selected";}?>><?php _e("Baby","wprobot") ?></option><?php } ?>
					<?php if($ll!="uk" || $ll!="de") {?><option value="Beauty" <?php if($_POST['amazon_department'] == "Beauty") {echo "selected";}?>><?php _e("Beauty","wprobot") ?></option><?php } ?>
					<option value="Books" <?php if($_POST['amazon_department'] == "Books") {echo "selected";}?>><?php _e("Books","wprobot") ?></option>
					<option value="Classical" <?php if($_POST['amazon_department'] == "Classical") {echo "selected";}?>><?php _e("Classical","wprobot") ?></option>
					<?php if($ll=="com") {?><option value="DigitalMusic" <?php if($_POST['amazon_department'] == "DigitalMusic") {echo "selected";}?>><?php _e("Digital Music","wprobot") ?></option><?php } ?>
					<?php if($ll!="jp" || $ll!="ca") {?><option value="MP3Downloads" <?php if($_POST['amazon_department'] == "MP3Downloads") {echo "selected";}?>><?php _e("MP3 Downloads","wprobot") ?></option><?php } ?>
					<option value="DVD" <?php if($_POST['amazon_department'] == "DVD") {echo "selected";}?>><?php _e("DVD","wprobot") ?></option>
					<option value="Electronics" <?php if($_POST['amazon_department'] == "Electronics") {echo "selected";}?>><?php _e("Electronics","wprobot") ?></option>
					<?php if($ll!="com" || $ll!="uk") {?><option value="ForeignBooks" <?php if($_POST['amazon_department'] == "ForeignBooks") {echo "selected";}?>><?php _e("Foreign Books","wprobot") ?></option><?php } ?>
					<?php if($ll=="com") {?><option value="GourmetFood" <?php if($_POST['amazon_department'] == "GourmetFood") {echo "selected";}?>><?php _e("Gourmet Food","wprobot") ?></option><?php } ?>
					<?php if($ll=="com" || $ll=="de" || $ll!="uk") {?><option value="Grocery" <?php if($_POST['amazon_department'] == "Grocery") {echo "selected";}?>><?php _e("Grocery","wprobot") ?></option><?php } ?>
					<?php if($ll!="ca") {?><option value="HealthPersonalCare" <?php if($_POST['amazon_department'] == "HealthPersonalCare") {echo "selected";}?>><?php _e("Health &amp; Personal Care","wprobot") ?></option><?php } ?>
					<?php if($ll!="fr" || $ll!="ca") {?><option value="HomeGarden" <?php if($_POST['amazon_department'] == "HomeGarden") {echo "selected";}?>><?php _e("Home &amp; Garden","wprobot") ?></option><?php } ?>
					<?php if($ll=="com") {?><option value="Industrial" <?php if($_POST['amazon_department'] == "Industrial") {echo "selected";}?>><?php _e("Industrial","wprobot") ?></option><?php } ?>
					<?php if($ll!="ca") {?><option value="Jewelry" <?php if($_POST['amazon_department'] == "Jewelry") {echo "selected";}?>><?php _e("Jewelry","wprobot") ?></option><?php } ?>
					<option value="KindleStore" <?php if($_POST['amazon_department'] == "KindleStore") {echo "selected";}?>><?php _e("Kindle Store","wprobot") ?></option>
					<?php if($ll!="ca") {?><option value="Kitchen" <?php if($_POST['amazon_department'] == "Kitchen") {echo "selected";}?>><?php _e("Kitchen","wprobot") ?></option><?php } ?>
					<?php if($ll=="com" || $ll=="de") {?><option value="Magazines" <?php if($_POST['amazon_department'] == "Magazines") {echo "selected";}?>><?php _e("Magazines","wprobot") ?></option><?php } ?>
					<?php if($ll=="com") {?><option value="Merchants" <?php if($_POST['amazon_department'] == "Merchants") {echo "selected";}?>><?php _e("Merchants","wprobot") ?></option><?php } ?>
					<?php if($ll=="com") {?><option value="Miscellaneous" <?php if($_POST['amazon_department'] == "Miscellaneous") {echo "selected";}?>><?php _e("Miscellaneous","wprobot") ?></option><?php } ?>
					<option value="Music" <?php if($_POST['amazon_department'] == "Music") {echo "selected";}?>><?php _e("Music","wprobot") ?></option>
					<?php if($ll=="com" || $ll!="uk" || $ll!="de" || $ll!="fr") {?><option value="MusicalInstruments" <?php if($_POST['amazon_department'] == "MusicalInstruments") {echo "selected";}?>><?php _e("Musical Instruments","wprobot") ?></option><?php } ?>
					<?php if($ll!="ca") {?><option value="MusicTracks" <?php if($_POST['amazon_department'] == "MusicTracks") {echo "selected";}?>><?php _e("Music Tracks","wprobot") ?></option><?php } ?>
					<?php if($ll!="jp" || $ll!="ca") {?><option value="OfficeProducts" <?php if($_POST['amazon_department'] == "OfficeProducts") {echo "selected";}?>><?php _e("Office Products","wprobot") ?></option><?php } ?>
					<?php if($ll!="fr" || $ll!="ca") {?><option value="OutdoorLiving" <?php if($_POST['amazon_department'] == "OutdoorLiving") {echo "selected";}?>><?php _e("Outdoor &amp; Living","wprobot") ?></option><?php } ?>
					<?php if($ll=="com" || $ll=="de") {?><option value="PCHardware" <?php if($_POST['amazon_department'] == "PCHardware") {echo "selected";}?>><?php _e("PC Hardware","wprobot") ?></option><?php } ?>
					<?php if($ll=="com") {?><option value="PetSupplies" <?php if($_POST['amazon_department'] == "PetSupplies") {echo "selected";}?>><?php _e("Pet Supplies","wprobot") ?></option><?php } ?>
					<?php if($ll=="com" || $ll=="de") {?><option value="Photo" <?php if($_POST['amazon_department'] == "Photo") {echo "selected";}?>><?php _e("Photo","wprobot") ?></option><?php } ?>
					<?php if($ll=="com" || $ll=="de") {?><option value="Shoes" <?php if($_POST['amazon_department'] == "Shoes") {echo "selected";}?>><?php _e("Shoes","wprobot") ?></option><?php } ?>
					<option value="Software" <?php if($_POST['amazon_department'] == "Software") {echo "selected";}?>><?php _e("Software","wprobot") ?></option>
					<?php if($ll!="fr" || $ll!="ca") {?><option value="SportingGoods" <?php if($_POST['amazon_department'] == "SportingGoods") {echo "selected";}?>><?php _e("Sporting Goods","wprobot") ?></option><?php } ?>
					<?php if($ll!="fr" || $ll!="ca") {?><option value="Tools" <?php if($_POST['amazon_department'] == "Tools") {echo "selected";}?>><?php _e("Tools","wprobot") ?></option><?php } ?>
					<?php if($ll!="ca") {?><option value="Toys" <?php if($_POST['amazon_department'] == "Toys") {echo "selected";}?>><?php _e("Toys","wprobot") ?></option><?php } ?>
					<option value="UnboxVideo" <?php if($_POST['amazon_department'] == "UnboxVideo") {echo "selected";}?>><?php _e("Unbox Video","wprobot") ?></option>
					<option value="VHS" <?php if($_POST['amazon_department'] == "VHS") {echo "selected";}?>><?php _e("VHS","wprobot") ?></option>
					<option value="Video" <?php if($_POST['amazon_department'] == "Video") {echo "selected";}?>><?php _e("Video","wprobot") ?></option>
					<option value="VideoGames" <?php if($_POST['amazon_department'] == "VideoGames") {echo "selected";}?>><?php _e("Video Games","wprobot") ?></option>
					<?php if($ll!="jp" || $ll!="ca") {?><option value="Watches" <?php if($_POST['amazon_department'] == "Watches") {echo "selected";}?>><?php _e("Watches","wprobot") ?></option><?php } ?>
					<?php if($ll=="com") {?><option value="Wireless" <?php if($_POST['amazon_department'] == "Wireless") {echo "selected";}?>><?php _e("Wireless","wprobot") ?></option><?php } ?>
					<?php if($ll=="com") {?><option value="WirelessAccessories" <?php if($_POST['amazon_department'] == "WirelessAccessories") {echo "selected";}?>><?php _e("Wireless Accessories","wprobot") ?></option><?php } ?>         			
				</select>
				<?php } ?>
			</td>		
			<td>
				<?php if(function_exists("wpr_ebaypost")) { ?>
				<strong><?php _e("eBay Category","wprobot") ?></strong><br/>
				<select name="ebay_category">
					<option <?php if($_POST['ebay_category'] == "all") {echo "selected";}?> value="all"><?php _e("All Categories","wprobot") ?></option>
					<option <?php if($_POST['ebay_category'] == "20081") {echo "selected";}?> value="20081"><?php _e("Antiques","wprobot") ?></option>
					<option <?php if($_POST['ebay_category'] == "550") {echo "selected";}?> value="550" ><?php _e("Art","wprobot") ?></option>
					<option <?php if($_POST['ebay_category'] == "2984") {echo "selected";}?> value="2984"><?php _e("Baby","wprobot") ?></option>
					<option <?php if($_POST['ebay_category'] == "267") {echo "selected";}?> value="267" ><?php _e("Books","wprobot") ?></option>
					<option <?php if($_POST['ebay_category'] == "12576") {echo "selected";}?> value="12576"><?php _e("Business &amp; Industrial","wprobot") ?></option>
					<option <?php if($_POST['ebay_category'] == "625") {echo "selected";}?> value="625" ><?php _e("Cameras &amp; Photo","wprobot") ?></option>
					<option <?php if($_POST['ebay_category'] == "15032") {echo "selected";}?> value="15032"><?php _e("Cell Phones &amp; PDAs","wprobot") ?></option>
					<option <?php if($_POST['ebay_category'] == "11450") {echo "selected";}?> value="11450"><?php _e("Clothing, Shoes &amp; Accessories","wprobot") ?></option>
					<option <?php if($_POST['ebay_category'] == "11116") {echo "selected";}?> value="11116" ><?php _e("Coins &amp; Paper Money","wprobot") ?></option>
					<option <?php if($_POST['ebay_category'] == "1") {echo "selected";}?> value="1" ><?php _e("Collectibles","wprobot") ?></option>
					<option <?php if($_POST['ebay_category'] == "58058") {echo "selected";}?> value="58058"><?php _e("Computers &amp; Networking","wprobot") ?></option>
					<option <?php if($_POST['ebay_category'] == "14339") {echo "selected";}?> value="14339"><?php _e("Crafts","wprobot") ?></option>
					<option <?php if($_POST['ebay_category'] == "237") {echo "selected";}?> value="237" ><?php _e("Dolls &amp; Bears","wprobot") ?></option>
					<option <?php if($_POST['ebay_category'] == "11232") {echo "selected";}?> value="11232" ><?php _e("DVDs &amp; Movies","wprobot") ?></option>
					<option <?php if($_POST['ebay_category'] == "6000") {echo "selected";}?> value="6000" ><?php _e("eBay Motors","wprobot") ?></option>
					<option <?php if($_POST['ebay_category'] == "293") {echo "selected";}?> value="293" ><?php _e("Electronics","wprobot") ?></option>
					<option <?php if($_POST['ebay_category'] == "45100") {echo "selected";}?> value="45100" ><?php _e("Entertainment Memorabilia","wprobot") ?></option>
					<option <?php if($_POST['ebay_category'] == "31411") {echo "selected";}?> value="31411" ><?php _e("Gift Certificates","wprobot") ?></option>
					<option <?php if($_POST['ebay_category'] == "26395") {echo "selected";}?> value="26395" ><?php _e("Health &amp; Beauty","wprobot") ?></option>
					<option <?php if($_POST['ebay_category'] == "11700") {echo "selected";}?> value="11700"><?php _e("Home &amp; Garden","wprobot") ?></option>
					<option <?php if($_POST['ebay_category'] == "281") {echo "selected";}?> value="281" ><?php _e("Jewelry &amp; Watches","wprobot") ?></option>
					<option <?php if($_POST['ebay_category'] == "11233") {echo "selected";}?> value="11233"><?php _e("Music","wprobot") ?></option>
					<option <?php if($_POST['ebay_category'] == "619") {echo "selected";}?> value="619" ><?php _e("Musical Instruments","wprobot") ?></option>
					<option <?php if($_POST['ebay_category'] == "870") {echo "selected";}?> value="870" ><?php _e("Pottery &amp; Glass","wprobot") ?></option>
					<option <?php if($_POST['ebay_category'] == "10542") {echo "selected";}?> value="10542"><?php _e("Real Estate","wprobot") ?></option>
					<option <?php if($_POST['ebay_category'] == "316") {echo "selected";}?> value="316" ><?php _e("Specialty Services","wprobot") ?></option>
					<option <?php if($_POST['ebay_category'] == "382") {echo "selected";}?> value="382" ><?php _e("Sporting Goods","wprobot") ?></option>
					<option <?php if($_POST['ebay_category'] == "64482") {echo "selected";}?> value="64482"><?php _e("Sports Mem, Cards &amp; Fan Shop","wprobot") ?></option>
					<option <?php if($_POST['ebay_category'] == "260") {echo "selected";}?> value="260" ><?php _e("Stamps","wprobot") ?></option>
					<option <?php if($_POST['ebay_category'] == "1305") {echo "selected";}?> value="1305"><?php _e("Tickets","wprobot") ?></option>
					<option <?php if($_POST['ebay_category'] == "220") {echo "selected";}?> value="220"><?php _e("Toys &amp; Hobbies","wprobot") ?></option>
					<option <?php if($_POST['ebay_category'] == "3252") {echo "selected";}?> value="3252" ><?php _e("Travel","wprobot") ?></option>
					<option <?php if($_POST['ebay_category'] == "1249") {echo "selected";}?> value="1249" ><?php _e("Video Games","wprobot") ?></option>
					<option <?php if($_POST['ebay_category'] == "99") {echo "selected";}?> value="99"><?php _e("Everything Else","wprobot") ?></option>
				</select>
				<?php } ?>
			</td>
		</tr>
		<?php } ?>	
		
		<input type="hidden" name="wpr_postthumbs" value="1" />
		<!--<tr>
			<td width="100%" colspan="2">
			<b><?php _e("Featured Image","wprobot") ?></b>
			<a class="tooltip" target="_blank" href="http://wprobot.net/documentation/#634">?<span><?php _e('<b>Important:</b> For this setting to work "Save Images to Server:" on the Options page has to be activated and your Wordpress "uploads" folder needs to be writeable (chmod 666)!',"wprobot") ?> <?php _e("<br/><br/><b>Warning:</b> Only enable if your theme actually supports WP post thumbnails or you get duplicate posts!","wprobot") ?></span></a><br/>
			<p style="margin:3px 0;">
			<input type="checkbox" name="wpr_postthumbs" value="1" <?php if($_POST["wpr_postthumbs"] == 1) {echo "checked";} ?>/> <?php _e("Attempt to add featured image / post thumbnail to each created post if image is available. (<b>Warning:</b> Only enable if your theme actually supports WP post thumbnails or you get duplicate posts!)","wprobot") ?>
			</p>
			</td>
		</tr>	-->	
		
		<tr>
			<td width="100%" colspan="2">
			<b><?php _e("Custom Fields","wprobot") ?></b>
			<!--Tooltip--><a class="tooltip" target="_blank" href="http://wprobot.net/documentation/#634">?<span><?php _e('This custom field will be added to every post. Premium templates often use custom fields to populate thumbnails but there are many other possible uses as well.<br/><br/><b>Click here to see a list of all available Value Tags</b><br/><br/><strong>Use the "+" button to add an additional custom field.</strong>',"wprobot") ?></span></a><br/>

			<?php if(!$_POST['cfnum']) {$_POST['cfnum'] = 1;} ?>
			<input size="5" name="cfnum" type="hidden" value="<?php echo $_POST['cfnum']; ?>"/>
			
			<?php for ($i = 1; $i <= $_POST['cfnum']; $i++) { ?>

			<strong><?php echo $i; ?>.</strong>
			<?php _e("Name:","wprobot") ?> <input name="cf_name<?php echo $i; ?>" type="text" value="<?php echo $_POST["cf_name$i"];?>"/> 
			<?php _e("Value:","wprobot") ?> <input name="cf_value<?php echo $i; ?>" type="text" value="<?php echo $_POST["cf_value$i"];?>"/>
			<?php if($i == $_POST['cfnum']) {?>
				<input class="button" type="submit" name="wpr_cf_add" value="+" />
				<?php if($_POST['cfnum'] > 1) {?> <input class="button" type="submit" name="wpr_cf_remove" value="-" /><?php } ?>		
			<?php } ?>			 			
			<br/>
			<?php } ?>			
			</td>
		</tr>
		<?php if(function_exists("wpr_translate")) { ?>
		<tr>
			<td width="100%" colspan="2">
			<b><?php _e("Translation","wprobot") ?></b>
			<!--Tooltip--><a class="tooltip" href="#">?<span><?php _e('Translates a certain percentage of this campaigns posts using Google Translate and the Translation Module.<br/><br/><b>Warning:</b> Google Translate is not perfect and using this feature might produce content containing bad grammar or formatting!',"wprobot") ?></span></a><br/>
			<?php _e("Translate","wprobot") ?> <input size="3" name="transchance" type="text" value="<?php echo $_POST['transchance'];?>"/>% <?php _e("of this campaigns posts from","wprobot") ?> 
				<select name="trans1" >
					<option value="no" <?php if ($_POST['trans1']=='no') {echo 'selected';} ?>>---</option>
					<option value="de" <?php if ($_POST['trans1']=='de') {echo 'selected';} ?>><?php _e('German',"wprobot") ?></option>
					<option value="en" <?php if ($_POST['trans1']=='en') {echo 'selected';} ?>><?php _e('English',"wprobot") ?></option>
					<option value="fr" <?php if ($_POST['trans1']=='fr') {echo 'selected';} ?>><?php _e('French',"wprobot") ?></option>
					<option value="separator" disabled="">&mdash;</option>
					<option value="af" <?php if ($_POST['trans1']=='af') {echo 'selected';} ?>><?php _e('Afrikaans',"wprobot") ?></option>
					<option value="sq" <?php if ($_POST['trans1']=='sq') {echo 'selected';} ?>><?php _e('Albanian',"wprobot") ?></option>
					<option value="ar" <?php if ($_POST['trans1']=='ar') {echo 'selected';} ?>><?php _e('Arabic',"wprobot") ?></option>
					<option value="be" <?php if ($_POST['trans1']=='be') {echo 'selected';} ?>><?php _e('Belarusian',"wprobot") ?></option>
					<option value="bg" <?php if ($_POST['trans1']=='bg') {echo 'selected';} ?>><?php _e('Bulgarian',"wprobot") ?></option>
					<option value="ca" <?php if ($_POST['trans1']=='ca') {echo 'selected';} ?>><?php _e('Catalan',"wprobot") ?></option>
					<option value="zh-CN" <?php if ($_POST['trans1']=='zh-CN') {echo 'selected';} ?>><?php _e('Chinese',"wprobot") ?></option>
					<option value="hr" <?php if ($_POST['trans1']=='hr') {echo 'selected';} ?>><?php _e('Croatian',"wprobot") ?></option>
					<option value="cs" <?php if ($_POST['trans1']=='cs') {echo 'selected';} ?>><?php _e('Czech',"wprobot") ?></option>
					<option value="da" <?php if ($_POST['trans1']=='da') {echo 'selected';} ?>><?php _e('Danish',"wprobot") ?></option>
					<option value="nl" <?php if ($_POST['trans1']=='nl') {echo 'selected';} ?>><?php _e('Dutch',"wprobot") ?></option>
					<option value="en" <?php if ($_POST['trans1']=='en') {echo 'selected';} ?>><?php _e('English',"wprobot") ?></option>
					<option value="et" <?php if ($_POST['trans1']=='et') {echo 'selected';} ?>><?php _e('Estonian',"wprobot") ?></option>
					<option value="tl" <?php if ($_POST['trans1']=='tl') {echo 'selected';} ?>><?php _e('Filipino',"wprobot") ?></option>
					<option value="fi" <?php if ($_POST['trans1']=='fi') {echo 'selected';} ?>><?php _e('Finnish',"wprobot") ?></option>
					<option value="fr" <?php if ($_POST['trans1']=='fr') {echo 'selected';} ?>><?php _e('French',"wprobot") ?></option>
					<option value="gl" <?php if ($_POST['trans1']=='gl') {echo 'selected';} ?>><?php _e('Galician',"wprobot") ?></option>
					<option value="de" <?php if ($_POST['trans1']=='de') {echo 'selected';} ?>><?php _e('German',"wprobot") ?></option>
					<option value="el" <?php if ($_POST['trans1']=='el') {echo 'selected';} ?>><?php _e('Greek',"wprobot") ?></option>
					<option value="iw" <?php if ($_POST['trans1']=='iw') {echo 'selected';} ?>><?php _e('Hebrew',"wprobot") ?></option>
					<option value="hi" <?php if ($_POST['trans1']=='hi') {echo 'selected';} ?>><?php _e('Hindi',"wprobot") ?></option>
					<option value="hu" <?php if ($_POST['trans1']=='hu') {echo 'selected';} ?>><?php _e('Hungarian',"wprobot") ?></option>
					<option value="is" <?php if ($_POST['trans1']=='is') {echo 'selected';} ?>><?php _e('Icelandic',"wprobot") ?></option>
					<option value="id" <?php if ($_POST['trans1']=='id') {echo 'selected';} ?>><?php _e('Indonesian',"wprobot") ?></option>
					<option value="ga" <?php if ($_POST['trans1']=='ga') {echo 'selected';} ?>><?php _e('Irish',"wprobot") ?></option>
					<option value="it" <?php if ($_POST['trans1']=='it') {echo 'selected';} ?>><?php _e('Italian',"wprobot") ?></option>
					<option value="ja" <?php if ($_POST['trans1']=='ja') {echo 'selected';} ?>><?php _e('Japanese',"wprobot") ?></option>
					<option value="ko" <?php if ($_POST['trans1']=='ko') {echo 'selected';} ?>><?php _e('Korean',"wprobot") ?></option>
					<option value="lv" <?php if ($_POST['trans1']=='lv') {echo 'selected';} ?>><?php _e('Latvian',"wprobot") ?></option>
					<option value="lt" <?php if ($_POST['trans1']=='lt') {echo 'selected';} ?>><?php _e('Lithuanian',"wprobot") ?></option>
					<option value="mk" <?php if ($_POST['trans1']=='mk') {echo 'selected';} ?>><?php _e('Macedonian',"wprobot") ?></option>
					<option value="ms" <?php if ($_POST['trans1']=='ms') {echo 'selected';} ?>><?php _e('Malay',"wprobot") ?></option>
					<option value="mt" <?php if ($_POST['trans1']=='mt') {echo 'selected';} ?>><?php _e('Maltese',"wprobot") ?></option>
					<option value="nor" <?php if ($_POST['trans1']=='nor') {echo 'selected';} ?>><?php _e('Norwegian',"wprobot") ?></option>
					<option value="fa" <?php if ($_POST['trans1']=='fa') {echo 'selected';} ?>><?php _e('Persian',"wprobot") ?></option>
					<option value="pl" <?php if ($_POST['trans1']=='pl') {echo 'selected';} ?>><?php _e('Polish',"wprobot") ?></option>
					<option value="pt" <?php if ($_POST['trans1']=='pt') {echo 'selected';} ?>><?php _e('Portuguese',"wprobot") ?></option>
					<option value="ro" <?php if ($_POST['trans1']=='ro') {echo 'selected';} ?>><?php _e('Romanian',"wprobot") ?></option>
					<option value="ru" <?php if ($_POST['trans1']=='ru') {echo 'selected';} ?>><?php _e('Russian',"wprobot") ?></option>
					<option value="sr" <?php if ($_POST['trans1']=='sr') {echo 'selected';} ?>><?php _e('Serbian',"wprobot") ?></option>
					<option value="sk" <?php if ($_POST['trans1']=='sk') {echo 'selected';} ?>><?php _e('Slovak',"wprobot") ?></option>
					<option value="sl" <?php if ($_POST['trans1']=='sl') {echo 'selected';} ?>><?php _e('Slovenian',"wprobot") ?></option>
					<option value="es" <?php if ($_POST['trans1']=='es') {echo 'selected';} ?>><?php _e('Spanish',"wprobot") ?></option>
					<option value="sw" <?php if ($_POST['trans1']=='sw') {echo 'selected';} ?>><?php _e('Swahili',"wprobot") ?></option>
					<option value="sv" <?php if ($_POST['trans1']=='sv') {echo 'selected';} ?>><?php _e('Swedish',"wprobot") ?></option>
					<option value="th" <?php if ($_POST['trans1']=='th') {echo 'selected';} ?>><?php _e('Thai',"wprobot") ?></option>
					<option value="tr" <?php if ($_POST['trans1']=='tr') {echo 'selected';} ?>><?php _e('Turkish',"wprobot") ?></option>
					<option value="uk" <?php if ($_POST['trans1']=='uk') {echo 'selected';} ?>><?php _e('Ukrainian',"wprobot") ?></option>
					<option value="vi" <?php if ($_POST['trans1']=='vi') {echo 'selected';} ?>><?php _e('Vietnamese',"wprobot") ?></option>
					<option value="cy" <?php if ($_POST['trans1']=='cy') {echo 'selected';} ?>><?php _e('Welsh',"wprobot") ?></option>
					<option value="yi" <?php if ($_POST['trans1']=='yi') {echo 'selected';} ?>><?php _e('Yiddish',"wprobot") ?></option>
				</select>			
			<?php _e("to","wprobot") ?> 
				<select name="trans2">
					<option value="no" <?php if ($_POST['trans2']=='no') {echo 'selected';} ?>><?php _e('---',"wprobot") ?></option>
					<option value="de" <?php if ($_POST['trans2']=='de') {echo 'selected';} ?>><?php _e('German',"wprobot") ?></option>
					<option value="en" <?php if ($_POST['trans2']=='en') {echo 'selected';} ?>><?php _e('English',"wprobot") ?></option>
					<option value="fr" <?php if ($_POST['trans2']=='fr') {echo 'selected';} ?>><?php _e('French',"wprobot") ?></option>
					<option value="separator" disabled="">&mdash;</option>
					<option value="af" <?php if ($_POST['trans2']=='af') {echo 'selected';} ?>><?php _e('Afrikaans',"wprobot") ?></option>
					<option value="sq" <?php if ($_POST['trans2']=='sq') {echo 'selected';} ?>><?php _e('Albanian',"wprobot") ?></option>
					<option value="ar" <?php if ($_POST['trans2']=='ar') {echo 'selected';} ?>><?php _e('Arabic',"wprobot") ?></option>
					<option value="be" <?php if ($_POST['trans2']=='be') {echo 'selected';} ?>><?php _e('Belarusian',"wprobot") ?></option>
					<option value="bg" <?php if ($_POST['trans2']=='bg') {echo 'selected';} ?>><?php _e('Bulgarian',"wprobot") ?></option>
					<option value="ca" <?php if ($_POST['trans2']=='ca') {echo 'selected';} ?>><?php _e('Catalan',"wprobot") ?></option>
					<option value="zh-CN" <?php if ($_POST['trans2']=='zh-CN') {echo 'selected';} ?>><?php _e('Chinese',"wprobot") ?></option>
					<option value="hr" <?php if ($_POST['trans2']=='hr') {echo 'selected';} ?>><?php _e('Croatian',"wprobot") ?></option>
					<option value="cs" <?php if ($_POST['trans2']=='cs') {echo 'selected';} ?>><?php _e('Czech',"wprobot") ?></option>
					<option value="da" <?php if ($_POST['trans2']=='da') {echo 'selected';} ?>><?php _e('Danish',"wprobot") ?></option>
					<option value="nl" <?php if ($_POST['trans2']=='nl') {echo 'selected';} ?>><?php _e('Dutch',"wprobot") ?></option>
					<option value="en" <?php if ($_POST['trans2']=='en') {echo 'selected';} ?>><?php _e('English',"wprobot") ?></option>
					<option value="et" <?php if ($_POST['trans2']=='et') {echo 'selected';} ?>><?php _e('Estonian',"wprobot") ?></option>
					<option value="tl" <?php if ($_POST['trans2']=='tl') {echo 'selected';} ?>><?php _e('Filipino',"wprobot") ?></option>
					<option value="fi" <?php if ($_POST['trans2']=='fi') {echo 'selected';} ?>><?php _e('Finnish',"wprobot") ?></option>
					<option value="fr" <?php if ($_POST['trans2']=='fr') {echo 'selected';} ?>><?php _e('French',"wprobot") ?></option>
					<option value="gl" <?php if ($_POST['trans2']=='gl') {echo 'selected';} ?>><?php _e('Galician',"wprobot") ?></option>
					<option value="de" <?php if ($_POST['trans2']=='de') {echo 'selected';} ?>><?php _e('German',"wprobot") ?></option>
					<option value="el" <?php if ($_POST['trans2']=='el') {echo 'selected';} ?>><?php _e('Greek',"wprobot") ?></option>
					<option value="iw" <?php if ($_POST['trans2']=='iw') {echo 'selected';} ?>><?php _e('Hebrew',"wprobot") ?></option>
					<option value="hi" <?php if ($_POST['trans2']=='hi') {echo 'selected';} ?>><?php _e('Hindi',"wprobot") ?></option>
					<option value="hu" <?php if ($_POST['trans2']=='hu') {echo 'selected';} ?>><?php _e('Hungarian',"wprobot") ?></option>
					<option value="is" <?php if ($_POST['trans2']=='is') {echo 'selected';} ?>><?php _e('Icelandic',"wprobot") ?></option>
					<option value="id" <?php if ($_POST['trans2']=='id') {echo 'selected';} ?>><?php _e('Indonesian',"wprobot") ?></option>
					<option value="ga" <?php if ($_POST['trans2']=='ga') {echo 'selected';} ?>><?php _e('Irish',"wprobot") ?></option>
					<option value="it" <?php if ($_POST['trans2']=='it') {echo 'selected';} ?>><?php _e('Italian',"wprobot") ?></option>
					<option value="ja" <?php if ($_POST['trans2']=='ja') {echo 'selected';} ?>><?php _e('Japanese',"wprobot") ?></option>
					<option value="ko" <?php if ($_POST['trans2']=='ko') {echo 'selected';} ?>><?php _e('Korean',"wprobot") ?></option>
					<option value="lv" <?php if ($_POST['trans2']=='lv') {echo 'selected';} ?>><?php _e('Latvian',"wprobot") ?></option>
					<option value="lt" <?php if ($_POST['trans2']=='lt') {echo 'selected';} ?>><?php _e('Lithuanian',"wprobot") ?></option>
					<option value="mk" <?php if ($_POST['trans2']=='mk') {echo 'selected';} ?>><?php _e('Macedonian',"wprobot") ?></option>
					<option value="ms" <?php if ($_POST['trans2']=='ms') {echo 'selected';} ?>><?php _e('Malay',"wprobot") ?></option>
					<option value="mt" <?php if ($_POST['trans2']=='mt') {echo 'selected';} ?>><?php _e('Maltese',"wprobot") ?></option>
					<option value="nor" <?php if ($_POST['trans2']=='nor') {echo 'selected';} ?>><?php _e('Norwegian',"wprobot") ?></option>
					<option value="fa" <?php if ($_POST['trans2']=='fa') {echo 'selected';} ?>><?php _e('Persian',"wprobot") ?></option>
					<option value="pl" <?php if ($_POST['trans2']=='pl') {echo 'selected';} ?>><?php _e('Polish',"wprobot") ?></option>
					<option value="pt" <?php if ($_POST['trans2']=='pt') {echo 'selected';} ?>><?php _e('Portuguese',"wprobot") ?></option>
					<option value="ro" <?php if ($_POST['trans2']=='ro') {echo 'selected';} ?>><?php _e('Romanian',"wprobot") ?></option>
					<option value="ru" <?php if ($_POST['trans2']=='ru') {echo 'selected';} ?>><?php _e('Russian',"wprobot") ?></option>
					<option value="sr" <?php if ($_POST['trans2']=='sr') {echo 'selected';} ?>><?php _e('Serbian',"wprobot") ?></option>
					<option value="sk" <?php if ($_POST['trans2']=='sk') {echo 'selected';} ?>><?php _e('Slovak',"wprobot") ?></option>
					<option value="sl" <?php if ($_POST['trans2']=='sl') {echo 'selected';} ?>><?php _e('Slovenian',"wprobot") ?></option>
					<option value="es" <?php if ($_POST['trans2']=='es') {echo 'selected';} ?>><?php _e('Spanish',"wprobot") ?></option>
					<option value="sw" <?php if ($_POST['trans2']=='sw') {echo 'selected';} ?>><?php _e('Swahili',"wprobot") ?></option>
					<option value="sv" <?php if ($_POST['trans2']=='sv') {echo 'selected';} ?>><?php _e('Swedish',"wprobot") ?></option>
					<option value="th" <?php if ($_POST['trans2']=='th') {echo 'selected';} ?>><?php _e('Thai',"wprobot") ?></option>
					<option value="tr" <?php if ($_POST['trans2']=='tr') {echo 'selected';} ?>><?php _e('Turkish',"wprobot") ?></option>
					<option value="uk" <?php if ($_POST['trans2']=='uk') {echo 'selected';} ?>><?php _e('Ukrainian',"wprobot") ?></option>
					<option value="vi" <?php if ($_POST['trans2']=='vi') {echo 'selected';} ?>><?php _e('Vietnamese',"wprobot") ?></option>
					<option value="cy" <?php if ($_POST['trans2']=='cy') {echo 'selected';} ?>><?php _e('Welsh',"wprobot") ?></option>
					<option value="yi" <?php if ($_POST['trans2']=='yi') {echo 'selected';} ?>><?php _e('Yiddish',"wprobot") ?></option>
				</select>			
			<?php _e("to","wprobot") ?> 
				<select name="trans3">
					<option value="no" <?php if ($_POST['trans3']=='no') {echo 'selected';} ?>><?php _e('---',"wprobot") ?></option>
					<option value="de" <?php if ($_POST['trans3']=='de') {echo 'selected';} ?>><?php _e('German',"wprobot") ?></option>
					<option value="en" <?php if ($_POST['trans3']=='en') {echo 'selected';} ?>><?php _e('English',"wprobot") ?></option>
					<option value="fr" <?php if ($_POST['trans3']=='fr') {echo 'selected';} ?>><?php _e('French',"wprobot") ?></option>
					<option value="separator" disabled="">&mdash;</option>
					<option value="af" <?php if ($_POST['trans3']=='af') {echo 'selected';} ?>><?php _e('Afrikaans',"wprobot") ?></option>
					<option value="sq" <?php if ($_POST['trans3']=='sq') {echo 'selected';} ?>><?php _e('Albanian',"wprobot") ?></option>
					<option value="ar" <?php if ($_POST['trans3']=='ar') {echo 'selected';} ?>><?php _e('Arabic',"wprobot") ?></option>
					<option value="be" <?php if ($_POST['trans3']=='be') {echo 'selected';} ?>><?php _e('Belarusian',"wprobot") ?></option>
					<option value="bg" <?php if ($_POST['trans3']=='bg') {echo 'selected';} ?>><?php _e('Bulgarian',"wprobot") ?></option>
					<option value="ca" <?php if ($_POST['trans3']=='ca') {echo 'selected';} ?>><?php _e('Catalan',"wprobot") ?></option>
					<option value="zh-CN" <?php if ($_POST['trans3']=='zh-CN') {echo 'selected';} ?>><?php _e('Chinese',"wprobot") ?></option>
					<option value="hr" <?php if ($_POST['trans3']=='hr') {echo 'selected';} ?>><?php _e('Croatian',"wprobot") ?></option>
					<option value="cs" <?php if ($_POST['trans3']=='cs') {echo 'selected';} ?>><?php _e('Czech',"wprobot") ?></option>
					<option value="da" <?php if ($_POST['trans3']=='da') {echo 'selected';} ?>><?php _e('Danish',"wprobot") ?></option>
					<option value="nl" <?php if ($_POST['trans3']=='nl') {echo 'selected';} ?>><?php _e('Dutch',"wprobot") ?></option>
					<option value="en" <?php if ($_POST['trans3']=='en') {echo 'selected';} ?>><?php _e('English',"wprobot") ?></option>
					<option value="et" <?php if ($_POST['trans3']=='et') {echo 'selected';} ?>><?php _e('Estonian',"wprobot") ?></option>
					<option value="tl" <?php if ($_POST['trans3']=='tl') {echo 'selected';} ?>><?php _e('Filipino',"wprobot") ?></option>
					<option value="fi" <?php if ($_POST['trans3']=='fi') {echo 'selected';} ?>><?php _e('Finnish',"wprobot") ?></option>
					<option value="fr" <?php if ($_POST['trans3']=='fr') {echo 'selected';} ?>><?php _e('French',"wprobot") ?></option>
					<option value="gl" <?php if ($_POST['trans3']=='gl') {echo 'selected';} ?>><?php _e('Galician',"wprobot") ?></option>
					<option value="de" <?php if ($_POST['trans3']=='de') {echo 'selected';} ?>><?php _e('German',"wprobot") ?></option>
					<option value="el" <?php if ($_POST['trans3']=='el') {echo 'selected';} ?>><?php _e('Greek',"wprobot") ?></option>
					<option value="iw" <?php if ($_POST['trans3']=='iw') {echo 'selected';} ?>><?php _e('Hebrew',"wprobot") ?></option>
					<option value="hi" <?php if ($_POST['trans3']=='hi') {echo 'selected';} ?>><?php _e('Hindi',"wprobot") ?></option>
					<option value="hu" <?php if ($_POST['trans3']=='hu') {echo 'selected';} ?>><?php _e('Hungarian',"wprobot") ?></option>
					<option value="is" <?php if ($_POST['trans3']=='is') {echo 'selected';} ?>><?php _e('Icelandic',"wprobot") ?></option>
					<option value="id" <?php if ($_POST['trans3']=='id') {echo 'selected';} ?>><?php _e('Indonesian',"wprobot") ?></option>
					<option value="ga" <?php if ($_POST['trans3']=='ga') {echo 'selected';} ?>><?php _e('Irish',"wprobot") ?></option>
					<option value="it" <?php if ($_POST['trans3']=='it') {echo 'selected';} ?>><?php _e('Italian',"wprobot") ?></option>
					<option value="ja" <?php if ($_POST['trans3']=='ja') {echo 'selected';} ?>><?php _e('Japanese',"wprobot") ?></option>
					<option value="ko" <?php if ($_POST['trans3']=='ko') {echo 'selected';} ?>><?php _e('Korean',"wprobot") ?></option>
					<option value="lv" <?php if ($_POST['trans3']=='lv') {echo 'selected';} ?>><?php _e('Latvian',"wprobot") ?></option>
					<option value="lt" <?php if ($_POST['trans3']=='lt') {echo 'selected';} ?>><?php _e('Lithuanian',"wprobot") ?></option>
					<option value="mk" <?php if ($_POST['trans3']=='mk') {echo 'selected';} ?>><?php _e('Macedonian',"wprobot") ?></option>
					<option value="ms" <?php if ($_POST['trans3']=='ms') {echo 'selected';} ?>><?php _e('Malay',"wprobot") ?></option>
					<option value="mt" <?php if ($_POST['trans3']=='mt') {echo 'selected';} ?>><?php _e('Maltese',"wprobot") ?></option>
					<option value="nor" <?php if ($_POST['trans3']=='nor') {echo 'selected';} ?>><?php _e('Norwegian',"wprobot") ?></option>
					<option value="fa" <?php if ($_POST['trans3']=='fa') {echo 'selected';} ?>><?php _e('Persian',"wprobot") ?></option>
					<option value="pl" <?php if ($_POST['trans3']=='pl') {echo 'selected';} ?>><?php _e('Polish',"wprobot") ?></option>
					<option value="pt" <?php if ($_POST['trans3']=='pt') {echo 'selected';} ?>><?php _e('Portuguese',"wprobot") ?></option>
					<option value="ro" <?php if ($_POST['trans3']=='ro') {echo 'selected';} ?>><?php _e('Romanian',"wprobot") ?></option>
					<option value="ru" <?php if ($_POST['trans3']=='ru') {echo 'selected';} ?>><?php _e('Russian',"wprobot") ?></option>
					<option value="sr" <?php if ($_POST['trans3']=='sr') {echo 'selected';} ?>><?php _e('Serbian',"wprobot") ?></option>
					<option value="sk" <?php if ($_POST['trans3']=='sk') {echo 'selected';} ?>><?php _e('Slovak',"wprobot") ?></option>
					<option value="sl" <?php if ($_POST['trans3']=='sl') {echo 'selected';} ?>><?php _e('Slovenian',"wprobot") ?></option>
					<option value="es" <?php if ($_POST['trans3']=='es') {echo 'selected';} ?>><?php _e('Spanish',"wprobot") ?></option>
					<option value="sw" <?php if ($_POST['trans3']=='sw') {echo 'selected';} ?>><?php _e('Swahili',"wprobot") ?></option>
					<option value="sv" <?php if ($_POST['trans3']=='sv') {echo 'selected';} ?>><?php _e('Swedish',"wprobot") ?></option>
					<option value="th" <?php if ($_POST['trans3']=='th') {echo 'selected';} ?>><?php _e('Thai',"wprobot") ?></option>
					<option value="tr" <?php if ($_POST['trans3']=='tr') {echo 'selected';} ?>><?php _e('Turkish',"wprobot") ?></option>
					<option value="uk" <?php if ($_POST['trans3']=='uk') {echo 'selected';} ?>><?php _e('Ukrainian',"wprobot") ?></option>
					<option value="vi" <?php if ($_POST['trans3']=='vi') {echo 'selected';} ?>><?php _e('Vietnamese',"wprobot") ?></option>
					<option value="cy" <?php if ($_POST['trans3']=='cy') {echo 'selected';} ?>><?php _e('Welsh',"wprobot") ?></option>
					<option value="yi" <?php if ($_POST['trans3']=='yi') {echo 'selected';} ?>><?php _e('Yiddish',"wprobot") ?></option>
				</select>
			<?php _e("to","wprobot") ?> 
				<select name="trans4">
					<option value="no" <?php if ($_POST['trans4']=='no') {echo 'selected';} ?>><?php _e('---',"wprobot") ?></option>
					<option value="de" <?php if ($_POST['trans4']=='de') {echo 'selected';} ?>><?php _e('German',"wprobot") ?></option>
					<option value="en" <?php if ($_POST['trans4']=='en') {echo 'selected';} ?>><?php _e('English',"wprobot") ?></option>
					<option value="fr" <?php if ($_POST['trans4']=='fr') {echo 'selected';} ?>><?php _e('French',"wprobot") ?></option>
					<option value="separator" disabled="">&mdash;</option>
					<option value="af" <?php if ($_POST['trans4']=='af') {echo 'selected';} ?>><?php _e('Afrikaans',"wprobot") ?></option>
					<option value="sq" <?php if ($_POST['trans4']=='sq') {echo 'selected';} ?>><?php _e('Albanian',"wprobot") ?></option>
					<option value="ar" <?php if ($_POST['trans4']=='ar') {echo 'selected';} ?>><?php _e('Arabic',"wprobot") ?></option>
					<option value="be" <?php if ($_POST['trans4']=='be') {echo 'selected';} ?>><?php _e('Belarusian',"wprobot") ?></option>
					<option value="bg" <?php if ($_POST['trans4']=='bg') {echo 'selected';} ?>><?php _e('Bulgarian',"wprobot") ?></option>
					<option value="ca" <?php if ($_POST['trans4']=='ca') {echo 'selected';} ?>><?php _e('Catalan',"wprobot") ?></option>
					<option value="zh-CN" <?php if ($_POST['trans4']=='zh-CN') {echo 'selected';} ?>><?php _e('Chinese',"wprobot") ?></option>
					<option value="hr" <?php if ($_POST['trans4']=='hr') {echo 'selected';} ?>><?php _e('Croatian',"wprobot") ?></option>
					<option value="cs" <?php if ($_POST['trans4']=='cs') {echo 'selected';} ?>><?php _e('Czech',"wprobot") ?></option>
					<option value="da" <?php if ($_POST['trans4']=='da') {echo 'selected';} ?>><?php _e('Danish',"wprobot") ?></option>
					<option value="nl" <?php if ($_POST['trans4']=='nl') {echo 'selected';} ?>><?php _e('Dutch',"wprobot") ?></option>
					<option value="en" <?php if ($_POST['trans4']=='en') {echo 'selected';} ?>><?php _e('English',"wprobot") ?></option>
					<option value="et" <?php if ($_POST['trans4']=='et') {echo 'selected';} ?>><?php _e('Estonian',"wprobot") ?></option>
					<option value="tl" <?php if ($_POST['trans4']=='tl') {echo 'selected';} ?>><?php _e('Filipino',"wprobot") ?></option>
					<option value="fi" <?php if ($_POST['trans4']=='fi') {echo 'selected';} ?>><?php _e('Finnish',"wprobot") ?></option>
					<option value="fr" <?php if ($_POST['trans4']=='fr') {echo 'selected';} ?>><?php _e('French',"wprobot") ?></option>
					<option value="gl" <?php if ($_POST['trans4']=='gl') {echo 'selected';} ?>><?php _e('Galician',"wprobot") ?></option>
					<option value="de" <?php if ($_POST['trans4']=='de') {echo 'selected';} ?>><?php _e('German',"wprobot") ?></option>
					<option value="el" <?php if ($_POST['trans4']=='el') {echo 'selected';} ?>><?php _e('Greek',"wprobot") ?></option>
					<option value="iw" <?php if ($_POST['trans4']=='iw') {echo 'selected';} ?>><?php _e('Hebrew',"wprobot") ?></option>
					<option value="hi" <?php if ($_POST['trans4']=='hi') {echo 'selected';} ?>><?php _e('Hindi',"wprobot") ?></option>
					<option value="hu" <?php if ($_POST['trans4']=='hu') {echo 'selected';} ?>><?php _e('Hungarian',"wprobot") ?></option>
					<option value="is" <?php if ($_POST['trans4']=='is') {echo 'selected';} ?>><?php _e('Icelandic',"wprobot") ?></option>
					<option value="id" <?php if ($_POST['trans4']=='id') {echo 'selected';} ?>><?php _e('Indonesian',"wprobot") ?></option>
					<option value="ga" <?php if ($_POST['trans4']=='ga') {echo 'selected';} ?>><?php _e('Irish',"wprobot") ?></option>
					<option value="it" <?php if ($_POST['trans4']=='it') {echo 'selected';} ?>><?php _e('Italian',"wprobot") ?></option>
					<option value="ja" <?php if ($_POST['trans4']=='ja') {echo 'selected';} ?>><?php _e('Japanese',"wprobot") ?></option>
					<option value="ko" <?php if ($_POST['trans4']=='ko') {echo 'selected';} ?>><?php _e('Korean',"wprobot") ?></option>
					<option value="lv" <?php if ($_POST['trans4']=='lv') {echo 'selected';} ?>><?php _e('Latvian',"wprobot") ?></option>
					<option value="lt" <?php if ($_POST['trans4']=='lt') {echo 'selected';} ?>><?php _e('Lithuanian',"wprobot") ?></option>
					<option value="mk" <?php if ($_POST['trans4']=='mk') {echo 'selected';} ?>><?php _e('Macedonian',"wprobot") ?></option>
					<option value="ms" <?php if ($_POST['trans4']=='ms') {echo 'selected';} ?>><?php _e('Malay',"wprobot") ?></option>
					<option value="mt" <?php if ($_POST['trans4']=='mt') {echo 'selected';} ?>><?php _e('Maltese',"wprobot") ?></option>
					<option value="nor" <?php if ($_POST['trans4']=='nor') {echo 'selected';} ?>><?php _e('Norwegian',"wprobot") ?></option>
					<option value="fa" <?php if ($_POST['trans4']=='fa') {echo 'selected';} ?>><?php _e('Persian',"wprobot") ?></option>
					<option value="pl" <?php if ($_POST['trans4']=='pl') {echo 'selected';} ?>><?php _e('Polish',"wprobot") ?></option>
					<option value="pt" <?php if ($_POST['trans4']=='pt') {echo 'selected';} ?>><?php _e('Portuguese',"wprobot") ?></option>
					<option value="ro" <?php if ($_POST['trans4']=='ro') {echo 'selected';} ?>><?php _e('Romanian',"wprobot") ?></option>
					<option value="ru" <?php if ($_POST['trans4']=='ru') {echo 'selected';} ?>><?php _e('Russian',"wprobot") ?></option>
					<option value="sr" <?php if ($_POST['trans4']=='sr') {echo 'selected';} ?>><?php _e('Serbian',"wprobot") ?></option>
					<option value="sk" <?php if ($_POST['trans4']=='sk') {echo 'selected';} ?>><?php _e('Slovak',"wprobot") ?></option>
					<option value="sl" <?php if ($_POST['trans4']=='sl') {echo 'selected';} ?>><?php _e('Slovenian',"wprobot") ?></option>
					<option value="es" <?php if ($_POST['trans4']=='es') {echo 'selected';} ?>><?php _e('Spanish',"wprobot") ?></option>
					<option value="sw" <?php if ($_POST['trans4']=='sw') {echo 'selected';} ?>><?php _e('Swahili',"wprobot") ?></option>
					<option value="sv" <?php if ($_POST['trans4']=='sv') {echo 'selected';} ?>><?php _e('Swedish',"wprobot") ?></option>
					<option value="th" <?php if ($_POST['trans4']=='th') {echo 'selected';} ?>><?php _e('Thai',"wprobot") ?></option>
					<option value="tr" <?php if ($_POST['trans4']=='tr') {echo 'selected';} ?>><?php _e('Turkish',"wprobot") ?></option>
					<option value="uk" <?php if ($_POST['trans4']=='uk') {echo 'selected';} ?>><?php _e('Ukrainian',"wprobot") ?></option>
					<option value="vi" <?php if ($_POST['trans4']=='vi') {echo 'selected';} ?>><?php _e('Vietnamese',"wprobot") ?></option>
					<option value="cy" <?php if ($_POST['trans4']=='cy') {echo 'selected';} ?>><?php _e('Welsh',"wprobot") ?></option>
					<option value="yi" <?php if ($_POST['trans4']=='yi') {echo 'selected';} ?>><?php _e('Yiddish',"wprobot") ?></option>
				</select>							
			<?php _e(" (translate comments?","wprobot") ?> <input type="checkbox" name="trans_comments" value="1" <?php if($_POST["trans_comments"] == 1) {echo "checked";} ?>/> <?php _e("Yes","wprobot") ?> <!--Tooltip--><a class="tooltip" href="#">?<span><?php _e('If selected all comments added to a post that is translated will be translated as well.<br/><br/><b>Warning:</b> Only use this if you have entered proxys for translation or else you might get banned from Google Translate for sending too many requests.',"wprobot") ?></span></a> )
			</td>
		</tr>	
		<?php } ?>	
		
	
		<tr>
			<td width="50%">
			<b><?php _e("Rewriter","wprobot") ?></b>
			<!--Tooltip--><a class="tooltip" href="#">?<span><?php _e('Important: You also need to activate the rewriting service of your choice on the WP Robot Options page.',"wprobot") ?></span></a><br/>			
			<input type="checkbox" name="wpr_rewriter" value="1" <?php if($_POST["wpr_rewriter"] == 1) {echo "checked";} ?>/> <?php _e("Activate rewriting for this campaign.","wprobot") ?>			
			</td>
			
			<td width="50%">
			<b><?php _e("Author ID or Name","wprobot") ?></b>
			<!--Tooltip--><a class="tooltip" href="#">?<span><?php _e('Enter the <b>ID of the author</b> you want to use for posts by this campaign. If left empty the default settings from the WP Robot Options screen will be used.<br/><br/>You can also enter any <b>author name</b> directly - if no user with the chosen name exists in your blog WP Robot will create one.',"wprobot") ?></span></a><br/>
			<input name="wpr_author" type="text" value="<?php echo $_POST['wpr_author'];?>"/>
			</td>			
		</tr>		

		
		<tr>
			<td width="50%">
			<b><?php _e("Start","wprobot") ?></b>
			<!--Tooltip--><a class="tooltip" href="#">?<span><?php _e('Delays the first post of this campaign by the amount you enter. Zero means the first post will be created immediatelly after adding the campaign.',"wprobot") ?></span></a><br/>
			<?php _e("Create first post in","wprobot") ?> <input size="3" name="delaystart" type="text" value="<?php echo $_POST['delaystart'];?>"/> <?php _e("hours.","wprobot") ?>
			</td>
			
			<td>
			<b><?php _e("New Post Status","wprobot") ?></b>
			<!--Tooltip--><a class="tooltip" href="#">?<span><?php _e('New posts by this campaign can either be published immediatelly or be added as drafts, so you can review them before going live. This overrides the global post status setting on the options page!',"wprobot") ?></span></a><br/>
				<select name="wpr_poststatus" id="wpr_poststatus">
					<option value="default" <?php if ($_POST['wpr_poststatus']=='default') {echo 'selected';} ?>><?php _e("default","wprobot") ?></option>				
					<option value="published" <?php if ($_POST['wpr_poststatus']=='published') {echo 'selected';} ?>><?php _e("published","wprobot") ?></option>
					<option value="draft" <?php if ($_POST['wpr_poststatus']=='draft') {echo 'selected';} ?>><?php _e("draft","wprobot") ?></option>
				</select>
			</td>		
		</tr>	

		<tr>
			<td width="50%">
			<b><?php _e("Custom Post Type","wprobot") ?></b>
			<!--Tooltip--><a class="tooltip" href="#">?<span><?php _e('Custom post types are a new Wordpress feature used by some themes to categorize the content into different types. <b>Warning:</b> Only enter something here if you are sure your theme supports a custom post type of this name or you wont see the content generated by this campaign!',"wprobot") ?></span></a><br/>
			<input size="30" name="wpr_posttype" type="text" value="<?php echo $_POST['wpr_posttype'];?>"/>
			</td>
			
			<td>
			</td>		
		</tr>			
	</table>	
			
	<p class="submit" style="margin:0;padding: 10px 0;"><input class="button-primary" type="submit" name="wpr_add" value="<?php if(!$_GET["edit"]) {_e("Create Campaign","wprobot");} else {_e("Update Campaign","wprobot");} ?>" /></p>
	<br/><br/><br/><br/><br/><br/><br/><br/><br/><br/>
	</div>
	
</form>