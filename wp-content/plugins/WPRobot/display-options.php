<div class="wrap">
<form method="post" id="wpr_options">	

<div id="wprobot" class="icon32"></div>
<h2 class="nav-tab-wrapper">WP Robot&nbsp;&nbsp;&nbsp; 
<a class="nav-tab <?php if($_GET["which"] != "del" && $_GET["which"] != "dis") {echo "nav-tab-active";} ?>" href="?page=wpr-options"><?php _e("Options","wprobot");?></a>
<a class="nav-tab <?php if($_GET["which"] == "dis") {echo "nav-tab-active";} ?>" href="?page=wpr-options&which=dis"><?php _e("Disable Modules","wprobot");?></a>
<a class="nav-tab <?php if($_GET["which"] == "del") {echo "nav-tab-active";} ?>" href="?page=wpr-options&which=del"><?php _e("Resetting and Uninstalling","wprobot");?></a>
</h2>

<style type="text/css">
table.addt {padding:5px;margin-bottom:10px;background:#FFF;border:1px solid #E5E5E5;box-shadow: 0 1px 1px 0 rgba(0, 0, 0, 0.1);}
table.addt:hover {border:1px solid #CCC;}
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
<?php if($options['wpr_help'] == "Yes") { // FFFFE0 // E6DB55 ?>
a.tooltip {background:#F9F9F9;font-weight:bold;text-decoration:none;padding:2px 6px;}
a.tooltip:hover {background:#F9F9F9; text-decoration:none;} /*BG color is a must for IE6*/
a.tooltip span {display:none;font-weight:normal; padding:2px 3px; margin-left:8px; width:245px;-webkit-box-shadow: 5px 5px 6px 0px rgba(0,0,0,0.4);-moz-box-shadow: 5px 5px 6px 0px rgba(0,0,0,0.4);box-shadow: 5px 5px 6px 0px rgba(0,0,0,0.4);}
a.tooltip:hover span{display:inline; position:absolute; background:#ffffff; border:1px solid #cccccc; color:#6c6c6c;}
<?php } else {?>
a.tooltip {display:none;}
<?php } ?>
</style>

<?php if($_GET["which"] == "dis") { ?>

	<p><?php _e("You can <b>disable modules you do not intend to use</b> on this blog. Disabling a module hides its settings from the Options page, its templates from the Templates page and prevents it from being used in campaigns. Existing campaigns are not affected. You can always reenable a module later.","wprobot") ?></p>
<?php
	foreach($wpr_modules as $module) {
		if(!in_array($module, $wpr_loadedmodules)) { // not installed
			?>
			<p><input name="disable_<?php echo $module ?>" type="checkbox" id="disable_<?php echo $module ?>" value="1" disabled /> <strong><label for="disable_<?php echo $module ?>"><?php echo ucwords($module) ?> Module</label></strong> - <?php _e("not installed","wprobot") ?>, <a target="_blank" href="http://wprobot.net/order/ordercustom.php"><?php _e("order now!","wprobot") ?></a></p>
			<?php
		} else {
		
			if(in_array($module, $wpr_disabledmodules)) { // disabled
				?>
				<p><input name="disable_<?php echo $module ?>" type="checkbox" id="disable_<?php echo $module ?>" value="1" /> <strong><label for="disable_<?php echo $module ?>"><?php echo ucwords($module) ?> Module</label></strong></p>
				<?php			
			} else {
				?>
				<p><input name="disable_<?php echo $module ?>" type="checkbox" id="disable_<?php echo $module ?>" value="1" checked /> <strong><label for="disable_<?php echo $module ?>"><?php echo ucwords($module) ?> Module</label></strong></p>
				<?php
			}
		
		}
	}

?>

	<p><?php _e("To disable modules deselect the checkbox next to them and then press 'Save Selection'.","wprobot") ?></p>

	<p class="submit"><input class="button-primary" type="submit" name="wpr_disable_save" value="<?php _e("Save Selection","wprobot") ?>" /></p>

<?php } elseif($_GET["which"] == "del") { ?>

	<h3 style="text-transform:uppercase;border-bottom: 1px solid #ccc;"><?php _e("Upgrade","wprobot") ?></h3>

	<p><?php _e("Use the button below after upgrading from the <b>Basic Core</b> to the <b>Advanced or Elite Core</b> version.","wprobot") ?></p>
	
	<input onclick="return confirm('<?php _e("This will attempt to update your WP Robot Core. Only continue if you have upgraded to a better Core Version after installing WP Robot on this blog.","wprobot") ?>')" class="button" type="submit" name="wpr_update_core" value="<?php _e("Update Core","wprobot") ?>" /> 	

	<h3 style="text-transform:uppercase;border-bottom: 1px solid #ccc;"><?php _e("Reset Your Settings","wprobot") ?></h3>


	<input onclick="return confirm('<?php _e("This will reset all templates to their default values and delete any changes or additions you have made. Continue?","wprobot") ?>')" class="button" type="submit" name="wpr_templates_default" value="<?php _e("Reset Templates","wprobot") ?>" /> <br/>
	<input onclick="return confirm('<?php _e("This will reset all options to their default values. Continue?","wprobot") ?>')" class="button" type="submit" name="wpr_options_default" value="<?php _e("Reset Options to Defaults","wprobot") ?>" /> 
	
	<h3 style="text-transform:uppercase;border-bottom: 1px solid #ccc;"><?php _e("Clear WP Robot Database","wprobot") ?></h3>
	
	
	<input onclick="return confirm('<?php _e("This will clear the WP Robot log of all messages and errors. Continue?","wprobot") ?>')" class="button" type="submit" name="wpr_clear_log" value="<?php _e("Clear Log","wprobot") ?>" /> <br/>
	<input onclick="return confirm('<?php _e("This will clear the WP Robot post history and thus all posts in the history could get posted again. Continue?","wprobot") ?>')" class="button" type="submit" name="wpr_clear_posts" value="<?php _e("Clear Post History","wprobot") ?>" /> 
	
	<h3 style="text-transform:uppercase;border-bottom: 1px solid #ccc;"><?php _e("Uninstall WP Robot","wprobot") ?></h3>
	
	<p><?php _e("The button below will <b>uninstall WP Robot completely</b> including the <b>deletion of all database tables and all your settings and campaigns</b>. All the <b>posts created by WP Robot are not deleted</b>.","wprobot") ?></p>
		
	<input onclick="return confirm('<?php _e("Warning: This will uninstall WP Robot and delete all settings. Continue?","wprobot") ?>')" class="button" type="submit" name="wpr_uninstall" value="<?php _e("Uninstall WP Robot","wprobot") ?>" />

	
	
	<div class="updated" style="margin-top: 20px;">	
		<h3><?php _e("Unix Cron Job","wprobot") ?></h3>
		<p><?php _e('You can set up an Unix cron job in your servers control panel as an alternative to the Wordpress pseodo-cron jobs. See the <a href="http://wprobot.net/documentation/#94a">documentation</a> for setup instructions and advanced options.',"wprobot") ?>
		<br/><br/>
		<strong><?php _e("Cron Url for this Weblog:","wprobot") ?></strong><br/><i><?php echo WPR_URLPATH ."cron.php?code=".get_option("wpr_cron"); ?></i>
		<br/><br/>
		<strong><?php _e("Sample Cron Command:","wprobot") ?></strong><br/><i>wget -O /dev/null <?php echo WPR_URLPATH ."cron.php?code=".get_option("wpr_cron"); ?></i>
		</p>
	</div>	

<?php } else { ?>
	<div style="width:27%;float:right;";>
	
		<div class="expld">
			<strong><?php _e("Documentation","wprobot") ?></strong><br/>
			<?php _e('Have <a href="http://wprobot.net/documentation/#8">a look at the <b>documentation</b></a> to view an explanation of all available settings.',"wprobot") ?>
		</div>			
	
		<div class="expld">
			<?php _e('<strong>Affiliate and API keys</strong> highlighted in red have to be entered for the plugin to function correctly!',"wprobot") ?>
		</div>	
	
		<div class="expld">
			<strong><?php _e("Quick Links","wprobot") ?></strong><br/>
			<?php _e('- <a target="_blank" href="http://wprobot.net/">WP Robot</a><br/>- <a target="_blank" href="http://wprobot.net/videos">Video Tutorials</a><br/>- <a target="_blank" href="http://wprobot.net/documentation">Online Documentation</a><br/>- <a target="_blank" href="http://wprobot.net/forum">Support Forum</a><br/>- <a target="_blank" href="http://wprobot.net/robotpal/sendnew.php">New Download Link</a>',"wprobot") ?>
		</div>			

		<div class="expld">
			<strong><?php _e("Supported Spinning Software","wprobot") ?></strong><br/>

			<?php _e('Go to the <a href="?page=ar-settings"><b>All Rewriters</b></a> page to set up content spinning in WP Robot.',"wprobot") ?>
			<br/>
			<ul style="list-style-type: disc; padding-left: 30px;">
			<li><a rel="nofollow" target="_blank" href="http://wprobot.net/go/thebestspinner">TheBestSpinner</a></li>
			<li><a rel="nofollow" target="_blank" href="http://wprobot.net/go/spinnerchief">Spinnerchief</a></li>
			<li><a rel="nofollow" target="_blank" href="http://wprobot.net/go/chimprewriter">ChimpRewriter</a></li>
			<li><a rel="nofollow" target="_blank" href="http://wprobot.net/go/spinrewriter">SpinRewriter</a></li>
			<li><a rel="nofollow" target="_blank" href="http://wprobot.net/go/wordai">WordAI</a></li>
			<li><a rel="nofollow" target="_blank" href="http://wprobot.net/go/contentprofessor">ContentProfessor</a></li>
			</ul>
			
		</div>	
		
		<div class="expld">
			<strong><?php _e("Recommended Software","wprobot") ?></strong><br/>


			<ul style="list-style-type: disc; padding-left: 30px;">
			<li><a rel="nofollow" target="_blank" href="http://wprobot.net/go/articlebuilder">Article Builder</a></li>
			<li><a rel="nofollow" target="_blank" href="http://wprobot.net/go/bigcontentsearch">Big Content Search</a></li>
			</ul>
			
		</div>			

		<div class="expld">
			<strong style="font-size: 125%;"><?php _e("Bulk Manage WP Robot","wprobot") ?></strong><br/>

			<?php _e('<a href="http://cmscommander.com/"><b>CMS Commander</b></a> allows you to manage and update WP Robot on all your weblogs at the same time. <a href="http://cmscommander.com/"><b>Try it for free now!</b></a>',"wprobot") ?>

		</div>			
		
	</div>
	<div style="width:70%;">		

	<p class="submit"><input class="button-primary" type="submit" name="wpr_options_save" value="<?php _e("Save Options","wprobot") ?>" /></p>
		
	<h3 style="text-transform:uppercase;border-bottom: 1px solid #ccc;"><?php _e("License Options","wprobot") ?></h3>	
		<table class="addt" width="100%" cellspacing="2" cellpadding="5" class="editform"> 	
			<tr valign="top"> 
				<td width="40%" scope="row"><?php _e("Your Paypal Email:","wprobot") ?></td> 
				<?php if($options['wpr_email'] != "demo") {$wemail = substr_replace($options['wpr_email'], '***', 0, 3);} else {$wemail = $options['wpr_email'];} ?>
				<td><input id="wpr_email" size="40" type="text" value="<?php echo $wemail; ?>" name="wpr_email" <?php if($options['wpr_core']!='developer' && $options['wpr_email']!="demo") {echo "disabled";} ?> />
				 <?php if($options['wpr_core']=='developer' || $options['wpr_email']=="demo") { ?><input class="button" type="submit" name="wpr_update_email" value="<?php _e("Update","wprobot") ?>" /><?php } ?>
				<!--Tooltip--><a class="tooltip" href="#">?<span><?php _e('If you own the Developer License of WP Robot you can edit the Paypal email in order to insert a sublicense for your clients.',"wprobot") ?></span></a>
				</td> 
			</tr>		
		</table>		

	<h3 style="text-transform:uppercase;border-bottom: 1px solid #ccc;"><?php _e("General Options","wprobot") ?></h3>	
		<table class="addt" width="100%" cellspacing="2" cellpadding="5" class="editform"> 		
			<tr valign="top"> 
				<td width="40%" scope="row"><?php _e("Enable Simple Mode:","wprobot") ?></td> 
				<td>
				<input name="wpr_simple" type="checkbox" id="wpr_simple" value="Yes" <?php if ($options['wpr_simple']=='Yes') {echo "checked";} ?>/> <label for="wpr_simple"><?php _e("Yes","wprobot") ?>	</label>	
				<!--Tooltip--><a class="tooltip" href="#">?<span><?php _e('In Simple Mode certain steps of the campaign creation in WP Robot have been simplified but in exchange campaigns are less powerful and flexible.<br/><br/>Specifically the "Post Templates" are hidden from the campaigns and instead you can only enable or disable single modules for your campaign (mostly similar to how WP Robot 2 worked).<br/><br/>You can switch between Simple and Advanced Mode at any time without affecting your campaigns.',"wprobot") ?></span></a>
				</td> 
			</tr>		
			<tr valign="top"> 
				<td width="40%" scope="row"><?php _e("New Post Status:","wprobot") ?></td>
				<td>
				<select name="wpr_poststatus" id="wpr_poststatus">
					<option value="published" <?php if ($options['wpr_poststatus']=='published') {echo 'selected';} ?>><?php _e("published","wprobot") ?></option>
					<option value="draft" <?php if ($options['wpr_poststatus']=='draft') {echo 'selected';} ?>><?php _e("draft","wprobot") ?></option>
				</select>
				</td> 
			</tr>	
			<tr valign="top"> 
				<td width="40%" scope="row"><?php _e("Reset Post Counter:","wprobot") ?></td>
				<td>
				<select name="wpr_resetcount" id="wpr_resetcount">
					<option value="no" <?php if ($options['wpr_resetcount']=='no') {echo 'selected';} ?>><?php _e("no","wprobot") ?></option>
					<option <?php if ($options['wpr_resetcount']=='50') {echo 'selected';} ?>>50</option>					
					<option <?php if ($options['wpr_resetcount']=='75') {echo 'selected';} ?>>75</option>					
					<option <?php if ($options['wpr_resetcount']=='100') {echo 'selected';} ?>>100</option>
					<option <?php if ($options['wpr_resetcount']=='150') {echo 'selected';} ?>>150</option>
					<option <?php if ($options['wpr_resetcount']=='200') {echo 'selected';} ?>>200</option>					
				</select>
				<!--Tooltip--><a class="tooltip" href="#">?<span><?php _e('Will reset the internal search counter for a keyword after a certain amount of created posts. The effect is that search and posting will start from the beginning of the search results again and thus add new and relevant posts.',"wprobot") ?></span></a>
				</td> 
			</tr>	
			<tr valign="top"> 
				<td width="40%" scope="row"><?php _e("Enable Help Tooltips:","wprobot") ?></td> 
				<td>
				<input name="wpr_help" type="checkbox" id="wpr_help" value="Yes" <?php if ($options['wpr_help']=='Yes') {echo "checked";} ?>/> <label for="wpr_help"><?php _e("Yes","wprobot") ?></label>		
				<!--Tooltip--><a class="tooltip" href="#">?<span><?php _e('If disabled all help tooltips like the one you are reading right now will disappear.',"wprobot") ?></span></a>
				</td> 
			</tr>	
			<!--<tr valign="top"> 
				<td width="40%" scope="row"><?php _e("Enable Old Duplicate Check:<br/><small>(for WP Robot 2 backwards compatibility)</small>","wprobot") ?></td> 
				<td>
				<input name="wpr_check_unique_old" type="checkbox" id="wpr_check_unique_old" value="Yes" <?php if ($options['wpr_check_unique_old']=='Yes') {echo "checked";} ?>/> <?php _e("Yes","wprobot") ?>		
				<a class="tooltip" href="#">?<span><?php _e('If disabled the old duplicate check from WP Robot 2 (comparing post titles) will be used besides the new method in WP Robot 4.<br/><br/><b>This should only be enabled if you were using WP Robot 2 or previous versions on this blog. If you were and do not disable this option you might get duplicate posts.</b>',"wprobot") ?></span></a>
				</td> 
			</tr>		-->
			<tr valign="top"> 
				<td width="40%" scope="row"><?php _e("Randomize Post Times:","wprobot") ?></td> 
				<td>
				<input name="wpr_randomize" type="checkbox" id="wpr_randomize" value="Yes" <?php if ($options['wpr_randomize']=='Yes') {echo "checked";} ?>/> <label for="wpr_randomize"><?php _e("Yes","wprobot") ?></label>		
				</td> 
			</tr>
			<tr valign="top"> 
				<td width="40%" scope="row"><?php _e("Randomize Number of Comments:","wprobot") ?></td> 
				<td>
				<input name="wpr_randomize_comments" type="checkbox" id="wpr_randomize_comments" value="Yes" <?php if ($options['wpr_randomize_comments']=='Yes') {echo "checked";} ?>/> <label for="wpr_randomize_comments"><?php _e("Yes","wprobot") ?>		</label>
				<!--Tooltip--><a class="tooltip" href="#">?<span><?php _e('If enabled only 50-100% of available comments for a post will be randomly added to provide more variation.',"wprobot"); ?></span></a>				
				</td> 
			</tr>				
			<tr valign="top"> 
				<td width="40%" scope="row"><?php _e("Cloak Affiliate Links:","wprobot") ?></td> 
				<td>
				<input name="wpr_cloak" type="checkbox" id="wpr_cloak" value="Yes" <?php if (get_option('wpr_cloak')=='Yes') {echo "checked";} ?>/> <label for="wpr_cloak"><?php _e("Yes","wprobot") ?>	</label>
				<!--Tooltip--><a class="tooltip" target="_blank" href="http://wprobot.net/blog/how-to-set-up-wp-robot-link-cloaking/">?<span><?php _e('<b>Important:</b> Additional steps are required to enable link cloaking. If you only check this box and don\'t finish the setup your links will not work!<br/><br/><b>Click to view setup instructions.</b>',"wprobot") ?></span></a>	
				</td> 
			</tr>				
			<tr valign="top"> 
				<td width="40%" scope="row"><?php _e("Automatically create Tags:","wprobot") ?></td>
				<td>
				<input name="wpr_autotag" type="checkbox" id="wpr_autotag" value="Yes" <?php if ($options['wpr_autotag']=='Yes') {echo "checked";} ?>/> <label for="wpr_autotag"><?php _e("Yes","wprobot") ?></label>
				</td> 
			</tr>
			<tr valign="top"> 
				<td width="40%" scope="row"><?php _e("Save Images to Server:","wprobot") ?></td> 
				<td>
				<input name="wpr_save_images" type="checkbox" id="wpr_save_images" value="Yes" <?php if ($options['wpr_save_images']=='Yes') {echo "checked";} ?>/> <label for="wpr_save_images"><?php _e("Yes","wprobot") ?></label>		
				<!--Tooltip--><a class="tooltip" href="#">?<span><?php _e('If enabled all images from WP Robot posts will be saved to your server instead of hotlinked. <b>Important:</b> For this option to work you need to make the "/wp-content/uploads" directory of your Wordpress installation writeable by the server (chmod 666).',"wprobot"); ?></span></a>				
				</td> 
			</tr>				
			<tr valign="top"> 
				<td width="40%" scope="row"><?php _e("Exclude from Tags:<br/><small>Words with 3 characters and less are automatically excluded</small>","wprobot") ?></td> 
				<td>			
					<textarea name="wpr_badwords" rows="2" cols="30"><?php echo $options['wpr_badwords'];?></textarea>	
				</td> 
			</tr>	
			<tr valign="top"> 
				<td width="40%" scope="row"><?php _e("Global Exclude Keywords:","wprobot") ?></td> 
				<td>			
					<textarea name="wpr_global_exclude" rows="2" cols="30"><?php echo $options['wpr_global_exclude'];?></textarea>	
					<!--Tooltip--><a class="tooltip" href="#">?<span><?php _e('Enter one keyword or phrase per line.<br/><br/>The keywords will be excluded from <b>all</b> campaigns and posts. If any one is found the post is skipped.<br/><br/>This is in addition to the exclude keywords you can set up for each campaign individually.',"wprobot") ?></span></a>
				</td> 
			</tr>							
			<tr valign="top"> 
				<td width="40%" scope="row"><?php _e("Author:","wprobot") ?></td> 
				<td><input id="wpr_authorid" size="14" class="text" type="text" value="<?php echo $options['wpr_authorid']; ?>" name="wpr_authorid"/>
				<?php _e("Enter User ID or Name","wprobot") ?> 
				<!--Tooltip--><a class="tooltip" href="#">?<span><?php _e('Enter the user ID or user name you want to use for all WP Robot posts.<br/><br/>You can enter <strong>several user IDs or names</strong> separated by ";" (without quotes) to select a random user from the list for each post. If you enter a name and no user with the chosen name exists in your blog WP Robot will create one.',"wprobot") ?></span></a>
				</td> 
			</tr>	
			<tr valign="top"> 
				<td width="40%" scope="row"><?php _e("Open Links:","wprobot") ?></td>
				<td>
				<select name="wpr_openlinks" id="wpr_openlinks">
					<option value="yes" <?php if ($options['wpr_openlinks']=='yes') {echo 'selected';} ?>><?php _e("In a new window","wprobot") ?></option>
					<option value="no" <?php if ($options['wpr_openlinks']=='no') {echo 'selected';} ?>><?php _e("In the same window","wprobot") ?></option>								
				</select>
				</td> 
			</tr>	

			<tr valign="top"> 
				<td width="40%" scope="row"><?php _e("Use Proxies","wprobot") ?></td> 
				<td>
				<input name="wpr_trans_use_proxies" type="checkbox" id="wpr_trans_use_proxies" value="yes" <?php if ($options['wpr_trans_use_proxies']=='yes') {echo "checked";} ?>/> <label for="wpr_trans_use_proxies"><?php _e("Yes, randomly select one of the following proxies:","wprobot") ?></label><br/>
				<textarea name="wpr_trans_proxies" rows="4" cols="32"><?php echo $options['wpr_trans_proxies'];?></textarea>	
				<!--Tooltip--><a class="tooltip" href="#">?<span><?php _e('Enter one proxy IP address per line. A random one will be selected for each post. Example:<br/><br/>222.77.14.55:80<br/>221.130.7.74:80<br/><br/>If you have socks5 proxies or private proxies you can use the following format to enter them:<br/><br/>IP|Type|User:Password<br/><br/>Example:<br/>221.130.7.74:80|socks|user:pass<br/>221.130.7.74:80|http|user:pass',"wprobot") ?></span></a>
				<!--<input name="wpr_trans_delete_proxies" type="checkbox" id="wpr_trans_delete_proxies" value="yes" <?php if ($options['wpr_trans_delete_proxies']=='yes') {echo "checked";} ?>/> <?php _e("Delete dead proxies automatically","wprobot") ?>-->
				</td> 
			</tr>			
			<tr valign="top"> 
				<td width="40%" scope="row"><?php _e("Intelligent Keyword Replace:","wprobot") ?></td> 
				<td>
				<input name="wpr_replace_keyword" type="checkbox" id="wpr_replace_keyword" value="1" <?php if ($options['wpr_replace_keyword']=='1') {echo "checked";} ?>/> <label for="wpr_replace_keyword"><?php _e("Yes, replace keyword with the title of first module in post template.","wprobot") ?></label>		
				<!--Tooltip--><a class="tooltip" href="#">?<span><?php _e('When enabled WP Robot will replace the keyword with the title of the first module that is used in the post template. That means the title is used as keyword for the following modules, resulting in more closely related content.<br/><br/><b>Example:</b> In a post template containing {rss} {youtube} {amazon} in that order the title of the returned RSS feed item will be used as keyword to search a related Youtube video as well as a related Amazon product (instead of the keyword you actually entered in your campaign).',"wprobot"); ?></span></a>				
				</td> 
			</tr>				
		</table>

	<?php
		foreach($wpr_modules as $module) {
			$function = "wpr_".$module."_options";
			if(function_exists($function) && !in_array($module, $wpr_disabledmodules)) {
				$function($options);
			}
		}
	?>		
				
	<h3 style="text-transform:uppercase;border-bottom: 1px solid #ccc;"><?php _e("Error Handling","wprobot") ?></h3>
	<p><i><?php _e("Important: Only edit the options below if you know what you are doing!","wprobot") ?></i></p>
		<table class="addt" width="100%" cellspacing="2" cellpadding="5" class="editform"> 		
			<tr valign="top"> 
				<td width="40%" scope="row"><?php _e("Maximum Retries","wprobot") ?></td> 
				<td>
					<input size="4" name="wpr_err_retries" type="text" id="wpr_err_retries" value="<?php echo $options['wpr_err_retries'] ;?>"/>
					<!--Tooltip--><a class="tooltip" href="#">?<span><?php _e('This option defines how many times the creation of a post is retried if a previous attempt failed.',"wprobot") ?></span></a>
				</td> 
			</tr>	
			<!--<tr valign="top"> 
				<td width="40%" scope="row"><?php _e("Maximum Errors","wprobot") ?></td> 
				<td>
					<input size="4" name="wpr_err_maxerr" type="text" id="wpr_err_maxerr" value="<?php echo $options['wpr_err_maxerr'] ;?>"/>
					<a class="tooltip" href="#">?<span><?php _e('The maximum amount of errors that can be encountered for modules in a template before skipping a post.<br/><br/><b>Example</b><br/>If set to 0 a post will be skipped as soon as one of the modules in the template returned an error, no matter how many other modules were there that worked.',"wprobot") ?></span></a>
				</td> 
			</tr>
			<tr valign="top"> 
				<td width="40%" scope="row"><?php _e("Minimum Modules","wprobot") ?></td> 
				<td>
					<input size="4" name="wpr_err_minmod" type="text" id="wpr_err_minmod" value="<?php echo $options['wpr_err_minmod'] ;?>"/>
					<a class="tooltip" href="#">?<span><?php _e('The minimum number of modules necessary for a post to get created.<br/><br/><b>Example</b><br/>If set to 2 and the template contains three modules (i.e. {amazon}{article}{ebay}) at least two modules have to work for a post to get created.<br/><br/><b>Warning:</b> Do not set this to zero as otherwise you could get empty posts. If you set this option to a higher value than the number of modules in your template you will never get any posts.',"wprobot") ?></span></a>
				</td> 
			</tr>-->
				<input size="4" name="wpr_err_maxerr" type="hidden" id="wpr_err_maxerr" value="<?php echo $options['wpr_err_maxerr'] ;?>"/>
				<input size="4" name="wpr_err_minmod" type="hidden" id="wpr_err_minmod" value="<?php echo $options['wpr_err_minmod'] ;?>"/>
			<tr valign="top"> 
				<td width="40%" scope="row"><?php _e("Disable Keywords","wprobot") ?></td> 
				<td>
					<?php _e("...after creating a post for it failed","wprobot") ?> <input size="4" name="wpr_err_disable" type="text" id="wpr_err_disable" value="<?php echo $options['wpr_err_disable'] ;?>"/> <?php _e("times.","wprobot") ?>
					<!--Tooltip--><a class="tooltip" href="#">?<span><?php _e('Select how many times a keyword may return an error until it is disabled. Disabled keywords will not get selected anymore in the campaign they are in.',"wprobot") ?></span></a>
				</td> 
			</tr>				
		</table>
		

	<h3 style="text-transform:uppercase;border-bottom: 1px solid #ccc;"><?php _e("Rewriting","wprobot") ?></h3>
	
		<!-- CHECK FOR ALL REWRITERS: If installed display active rewriters, if not link to install -->
	
		<?php
		if(empty($rw_options)) { ?>
			<p>Please <a href="plugin-install.php?tab=search&type=term&s=AllRewriters&plugin-search-input=Search+Plugins"><strong>install</strong></a> and activate our free <strong>All Rewriters</strong> plugin to automatically rewrite WP Robot posts.</p>
		<?php } else {
		?>		
			<p><?php _e('Individual spinning software setup has been moved to the <strong><a href="http://xdemo.wprobot.net/wp-admin/admin.php?page=ar-settings">All Rewriters</a></strong> settings page.',"wprobot") ?></p>
			<table class="addt" width="100%" cellspacing="2" cellpadding="5" class="editform"> 		
				<tr valign="top"> 
					<td width="40%" scope="row"><?php _e("Use Rewriter:","wprobot") ?></td> 
					<td>	
					<select name="wpr_rewrite_use" id="wpr_rewrite_use">
						<option value="rand" <?php if ($options['wpr_rewrite_use']=='rand') {echo 'selected';} ?>><?php _e("Random","wprobot") ?></option>
						<option value="chain" <?php if ($options['wpr_rewrite_use']=='chain') {echo 'selected';} ?>><?php _e("Chain Rewriting","wprobot") ?></option>								
						<?php foreach($rw_options as $module => $moduledata) {if(1 == $moduledata["enabled"]) { ?>
							<option value="<?php echo $module; ?>" <?php if ($options['wpr_rewrite_use']==$module) {echo 'selected';} ?>><?php echo $moduledata["name"]; ?></option>
						<?php }} ?>
					</select>
					</td> 
				</tr>		
				<tr valign="top"> 
					<td width="40%" scope="row"><?php _e("Rewrite Titles:","wprobot") ?></td> 
					<td>
					<input name="wpr_tbs_rewrite_title" type="checkbox" id="wpr_tbs_rewrite_title" value="Yes" <?php if ($options['wpr_tbs_rewrite_title']=='Yes') {echo "checked";} ?>/> <?php _e("Yes","wprobot") ?>		
					<!--Tooltip--><a class="tooltip" href="#">?<span><?php _e('If checked WP Robot will rewrite post titles as well as the content.',"wprobot") ?></span></a>
					</td> 
				</tr>					
			</table>		
		<?php } ?>
		
		
		<p class="submit"><input class="button-primary" type="submit" name="wpr_options_save" value="<?php _e("Save Options","wprobot") ?>" /></p>
	</div>

<?php } ?>
	</form>	
</div>
