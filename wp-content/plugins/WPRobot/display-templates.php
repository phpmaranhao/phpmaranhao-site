<?php
$tags["amazon"] = "{title}, {keyword}, {url}, {link}, {description}, {features}, {thumbnail}, {buynow-big}, {buynow-ger}, {buynow}, {listprice}, {price}, {price-updating}, {reviews-iframe}, {reviews-noiframe}, {smallimage}, {mediumimage}, {largeimage}";
$tags["article"] = "{title}, {keyword}, {url}, {article}, {authortext}";
$tags["clickbank"] = "{title}, {keyword}, {url}, {link}, {description}, {thumbnail}, {imageurl}";
$tags["ebay"] = "{title}, {keyword}, {url}, {thumbnail}, {price}, {descriptiontable}, {description}";
$tags["flickr"] = "{title}, {keyword}, {url}, {image}, {date}, {owner}, {description}";
$tags["rss"] = "{title}, {keyword}, {url}, {content}, {source}, {author}, {mediacontent}, {mediathumbnail}, {enclosure}";
$tags["yahooanswers"] = "{title}, {keyword}, {url}, {question}, {user}, {answers}";
$tags["youtube"] = "{title}, {keyword}, {url}, {thumbnail}, {thumbnail_medium}, {thumbnail_large}, {video}, {rating}, {description}";
$tags["yahoonews"] = "{title}, {keyword}, {url}, {thumbnail}, {summary}, {source}";
$tags["twitter"] = "{title}, {keyword}, {url}, {tweet}, {author}, {authorname}, {authorurl}, {date}";
$tags["shopzilla"] = "{title}, {keyword}, {url}, {description}, {thumbnail}, {manufacturer}, {offers}, {minprice}, {maxprice}";
$tags["oodle"] = "{title}, {keyword}, {url}, {content}, {thumbnail}, {city}, {address}, {latitude}, {longitude}, {price}, {condition}, {features}, {seller_type}, {delivery}";
$tags["pressrelease"] = "{title}, {keyword}, {url}, {thumbnail}, {summary}, {pressrelease}";
$tags["commissionjunction"] = "{title}, {keyword}, {url}, {description}, {thumbnail}, {price}, {currency}, {saleprice}, {listprice}, {advertiser}, {instock}, {imageurl}";
$tags["itunes"] = "{thumbnail}, {keyword}, {trackname}, {collectionname}, {artistname}, {artworkUrl30}, {artworkUrl60}, {artworkUrl100}, {collectionPrice}, {trackPrice}, {currency}, {genre}, {artisturl}, {collectionurl}, {trackurl}, {previewurl}";
$tags["eventful"] = "{title}, {keyword}, {url}, {description}, {begin}, {end}, {venuename}, {venueurl}, {venueaddress}, {city}, {country}";
$tags["linkshare"] = "{title}, {keyword}, {url}, {description}, {thumbnail}, {summary}, {category}, {price}, {imageurl}, {merchant}";
$tags["yelp"] = "{title}, {keyword}, {url}, {rating}, {thumbnail}, {address}, {city}, {reviewscount}, {reviews:x}";
$tags["shareasale"] = "{title}, {keyword}, {url}, {description}, {thumbnail}, {bigimage}, {merchant}, {category}, {manufacturer}, {isbn}, {status}, {price}, {listprice}, {custom1}, {custom2}, {custom3}";
$tags["avantlink"] = "{title}, {keyword}, {url}, {description}, {thumbnail}, {largeimage}, {mediumimage}, {merchant}, {price}, {listprice}, {brand}";
$tags["plr"] = "{title}, {keyword}, {article}";

?>

<div class="wrap">
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
a.expll {display:block;padding:10px;}
a.aactive {background:#fff;}
a.expll:hover {display:block;padding:10px;background:#fff;}
h3 a,h2 a {font-size:80%;text-decoration:none;margin-left:10px;}
</style>

<div id="wprobot" class="icon32"></div>
<h2 class="nav-tab-wrapper">WP Robot&nbsp;&nbsp;&nbsp; 
<a class="nav-tab <?php if($_GET["which"] != "post") {echo "nav-tab-active";} ?>" href="?page=wpr-templates"><?php _e("Module Templates","wprobot");?></a>
<a class="nav-tab <?php if($_GET["which"] == "post") {echo "nav-tab-active";} ?>" href="?page=wpr-templates&which=post"><?php _e("Post Template Presets","wprobot");?></a>
</h2>
<!--
<h2><?php if($_GET["which"] == "post") {_e("WP Robot Post Templates","wprobot");?> <a href="?page=wpr-templates&which=post&add=post"><?php _e("&rarr; Add New","wprobot") ?></a><?php } else {_e("WP Robot Module Templates","wprobot");} ?></h2>
-->
	<?php 
	if ($records) {
	
		// POST TEMPLATES
		if($_GET["which"] == "post") {
		$type = "";
		$i = 1;
		?>
	<div style="width:27%;float:right;";>
		
		<div class="expld">	
			<?php _e('WP Robot knows <b>two types of templates</b>:<br/><b>Post Templates</b> are the main templates used for posts. They can contain several module template tags which in turn trigger a <b>Module Template</b>. In other words Module templates are used for single module items, for example a single Amazon product, while Post templates are used for the complete post and can contain several modules.',"wprobot") ?>
		</div>			
		
		<div class="expld">	
			<?php _e('The <strong>Post Template Presets</strong> you set up on this page will be available on the "Add Campaign" screen as presets to use in your campaign.',"wprobot") ?>
		</div>			
	
		<div class="expld">	
			<strong><?php _e("Content Template Tags","wprobot") ?></strong><br/>	
			<?php foreach($wpr_loadedmodules as $loadedmodule) {if($loadedmodule != "translation"){echo "{".$loadedmodule."}<br/>";}} ?>
			<br/>{keyword}<br/>{catlink}<br/><a href="http://wprobot.net/documentation/#631"><?php _e("Random Tags","wprobot") ?></a><br/><a href="http://wprobot.net/documentation/#63"><b>See Documentation</b></a>
		</div>		

		<div class="expld">	
			<strong><?php _e("Title Template Tags","wprobot") ?></strong><br/>		
			<?php foreach($wpr_loadedmodules as $loadedmodule) {if($loadedmodule != "translation"){echo "{".$loadedmodule."title}<br/>";}} ?>
			<br/>{keyword}<br/><a href="http://wprobot.net/documentation/#631"><?php _e("Random Tags","wprobot") ?></a><br/><a href="http://wprobot.net/documentation/#63"><b>See Documentation</b></a>
		</div>	
		
	</div>
	<div style="width:70%;">	
		<form method="post" id="wpr_options">	

		<p style="float: right; font-size: 135%;">
		<a href="?page=wpr-templates&which=post&add=post"><?php _e("&rarr; Add New Template","wprobot") ?></a>
		</p>
		
		<p>
		<input class="button-primary" type="submit" name="tsaveall" value="<?php _e("Save All Changes","wprobot") ?>" />
		</p>		
		
		<input size="5" name="tids" type="hidden" value="<?php echo implode(",", $tids); ?>"/>		
		
		<?php
        foreach ($records as $record) { 
		?>
			
			<a name="<?php echo $i;?>"></a><table class="addt" width="100%">	
				<tr>
					<td valign="top" width="50%">
						<strong style="font-size:120%;border-bottom:1px dotted #ccc;"><?php _e("Post Template Preset","wprobot") ?> <?php echo $i;?></strong><br/>
						<?php _e("Name:","wprobot") ?> <input class="input" name="tname<?php echo $record->id; ?>" type="text" value="<?php echo $record->name; ?>"/>						
					</td>
					<td>
						<strong><?php _e("Post Title","wprobot") ?></strong><br/>
						<textarea name="ttitle<?php echo $record->id; ?>" rows="2" cols="30"><?php echo $record->title;?></textarea>
					</td>
				</tr>				
				<tr>
					<td width="50%">
					</td>
					<td rowspan="3">
						<strong><?php _e("Post Content","wprobot") ?></strong><br/>
						<textarea name="tcontent<?php echo $record->id; ?>" rows="5" cols="30"><?php echo $record->content;?></textarea>
					</td>
				</tr>
				<tr>
					<td>
						<input class="button" type="submit" name="tsave<?php echo $record->id; ?>" value="<?php _e("Save Changes","wprobot") ?>" /> 
						<input class="button" type="submit" name="tcopy<?php echo $record->id; ?>" value="<?php _e("Copy","wprobot") ?>" />						
						<input class="button" type="submit" name="tdelete<?php echo $record->id; ?>" value="<?php _e("Delete","wprobot") ?>" />
					</td>
				</tr>
				<tr>
					<td>
					</td>
				</tr>
		<tr>	
			<td>
	
			</td>
			<td>
			<strong><?php _e("Post Comments","wprobot") ?></strong><br/>
			<input type="checkbox" name="comments_amazon<?php echo $record->id; ?>" value="1" <?php if($record->comments_amazon == 1) {echo "checked";} ?>/> <?php _e("Amazon reviews","wprobot") ?><br/>
			<input type="checkbox" name="comments_yahoo<?php echo $record->id; ?>" value="1" <?php if($record->comments_yahoo == 1) {echo "checked";} ?>/> <?php _e("Yahoo Answers answers","wprobot") ?><br/>
			<input type="checkbox" name="comments_flickr<?php echo $record->id; ?>" value="1" <?php if($record->comments_flickr == 1) {echo "checked";} ?>/> <?php _e("Flickr comments","wprobot") ?><br/>
			<input type="checkbox" name="comments_youtube<?php echo $record->id; ?>" value="1" <?php if($record->comments_youtube == 1) {echo "checked";} ?>/> <?php _e("Youtube comments","wprobot") ?>
			</td>			
		</tr>				
			</table>

			<?php if($type != $record->type) { $type = $record->type; } $i++; ?>			
			
		<?php } ?>
	</form>
	
	<?php } else { // MODULE TEMPLATES ?>
	<div style="width:25%;float:right;";>
		
		<div class="expld">	
			<?php _e('WP Robot knows <b>two types of templates</b>.<br/><b>Post Templates</b> are the main templates used for posts. They can contain several module template tags which in turn trigger a <b>Module Template</b>. In other words Module templates are used for single module items, for example a single Amazon product, while Post templates are used for the complete post and can contain several modules.',"wprobot") ?>
		</div>			
		
		<div class="expld">	
			<?php _e('The <strong>Module Templates</strong> you set up on this page will be used to populate module template tags in your post templates with content.<br/><br/>For <b>example</b> the "Amazon Module Template" will be used for all {amazon} tags in your post templates, the "Article Module Template" for all {article} tags and so on.',"wprobot") ?>
		</div>	

		<div class="expld">	
			<?php _e('Each module you own has one Module Template that belongs to it.<br/><br/>The exception to that is the Flickr Module, which has a separate template for thumbnails.',"wprobot") ?>
		</div>		
		
		<div class="expld">	
			<?php _e('<b>See the documentation for a list of <a href="http://wprobot.net/documentation/#633">all available template tags</a>.</b>',"wprobot") ?>
		</div>			
		
	</div>
	<div style="width:70%;">	
	<form method="post" id="wpr_template">	
    <?php $i = 0;  if(!is_array($wpr_disabledmodules)) { $wpr_disabledmodules = array();} foreach ($records as $record) { 
		if(!in_array($record->type, $wpr_disabledmodules)) { 
			if($type != $record->type) { ?>
			<h3 style="text-transform:uppercase;border-bottom: 1px solid #ccc;"><?php echo $record->type;?> <?php _e("Module Template","wprobot"); if($record->name == "thumbnail") {_e(" (Thumbnail)","wprobot");} ?></h3>	
			<?php if($type == "post") { ?><p><a href="?page=wpr-templates&add=<?php echo $record->type;?>"><?php _e("Add new template","wprobot") ?></a></p><?php } ?>	
			<?php } ?>	
			
			
			<input size="5" name="tnum" type="hidden" value="<?php echo $record->id; ?>"/>
			<table class="addt" width="100%">		
				<tr>
					<td valign="top" rowspan="3">
						<input size="5" name="<?php echo $i."id";?>" type="hidden" value="<?php echo $record->id; ?>"/>
						<strong><?php _e("Content","wprobot") ?></strong><br/>
						<textarea id="<?php echo $i."c";?>" name="<?php echo $i."c";?>" rows="5" cols="42"><?php echo $record->content;?></textarea>
					</td>	
					<td valign="top" style="padding-left:10px;">	
						<strong><?php _e("Tags Available","wprobot") ?></strong><br/>
						<?php $tarray = explode(", ", $tags[$record->type]);
						foreach($tarray as $tag) { ?>
						<input type="button" class="button" name="insert" value="<?php echo $tag;?>" onClick="addtxt('<?php echo $i."c";?>','<?php echo $tag;?>')"> 
						<?php }	?>
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
			</table>

	
		<?php $i++; } } ?>	
		<input size="5" name="modnum" type="hidden" value="<?php echo $i; ?>"/>
		<p><input class="button-primary" type="submit" name="tmodsave" value="<?php _e("Save Changes","wprobot") ?>" /> </p>
		</form>		
	<?php }	?>	<?php }	?>	
		</div>	
<!--
	<div class="updated" style="margin-top: 20px;">	
		<h3>Export Templates</h3>
		<form method="post" id="wpr_savetemplate">	
		<p><?php _e("You can save all your edited Post and Module Templates to the wprobot.net server in order to load them when installing WP Robot on new websites of yours to sped up the setup process.","wprobot") ?><br/><br/>
		<input onclick="return confirm('<?php _e("If you have previously saved templates to the server this will overwrite them. Continue?","wprobot") ?>')" class="button" type="submit" name="wpr_export_templates" value="<?php _e("Save All Templates","wprobot") ?>" /> 
		</p>
		</form>		
	</div>	
-->
		
</div>