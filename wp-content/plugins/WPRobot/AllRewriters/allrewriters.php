<?php
@require_once("rewriter-requests.php");

function ar_activate($options) {
	include("rewriter-options.php");
	//global $ar_optionsarray;
	$allrewriters_settings = $ar_optionsarray;
	
	if(empty($options)) {$options = unserialize(get_option("wpr_options"));	}
	
	if(!empty($options)) {
	
		if(!empty($options['wpr_rewrite_protected'])) {	
			$allrewriters_settings["general"]["options"]["protected_words"]["value"] = $options['wpr_rewrite_protected'];
		}
	
		if(!empty($options['wpr_tbs_rewrite_email'])) {
			$allrewriters_settings["thebestspinner"]["enabled"] = 1;
			$allrewriters_settings["thebestspinner"]["options"]["tbs_email"]["value"] = $options['wpr_tbs_rewrite_email'];
			$allrewriters_settings["thebestspinner"]["options"]["tbs_pw"]["value"] = $options['wpr_tbs_rewrite_pw'];
			$allrewriters_settings["thebestspinner"]["options"]["tbs_quality"]["value"] = $options['wpr_tbs_quality'];
		}

		if(!empty($options['wpr_sc_rewrite_email'])) {
			$allrewriters_settings["spinnerchief"]["enabled"] = 1;
			$allrewriters_settings["spinnerchief"]["options"]["sc_email"]["value"] = $options['wpr_sc_rewrite_email'];
			$allrewriters_settings["spinnerchief"]["options"]["sc_pw"]["value"] = $options['wpr_sc_rewrite_pw'];
			$allrewriters_settings["spinnerchief"]["options"]["sc_quality"]["value"] = $options['wpr_sc_quality'];
			$allrewriters_settings["spinnerchief"]["options"]["sc_port"]["value"] = $options['wpr_sc_port'];
			$allrewriters_settings["spinnerchief"]["options"]["sc_thesaurus"]["value"] = $options['wpr_sc_thesaurus'];
		}	

		if(!empty($options['wpr_schimp_rewrite_email'])) {
			$allrewriters_settings["spinchimp"]["enabled"] = 1;
			$allrewriters_settings["spinchimp"]["options"]["schimp_email"]["value"] = $options['wpr_schimp_rewrite_email'];
			$allrewriters_settings["spinchimp"]["options"]["appid"]["value"] = $options['wpr_schimp_rewrite_pw'];
			$allrewriters_settings["spinchimp"]["options"]["schimp_quality"]["value"] = $options['wpr_schimp_quality'];
		}	

		if(!empty($options['wpr_sr_rewrite_email'])) {
			$allrewriters_settings["spinrewriter"]["enabled"] = 1;
			$allrewriters_settings["spinrewriter"]["options"]["sr_email"]["value"] = $options['wpr_sr_rewrite_email'];
			$allrewriters_settings["spinrewriter"]["options"]["appid"]["value"] = $options['wpr_sr_rewrite_pw'];
			$allrewriters_settings["spinrewriter"]["options"]["sr_quality"]["value"] = $options['wpr_sr_quality'];
		}		

		if(!empty($options['wpr_wai_rewrite_email'])) {
			$allrewriters_settings["wordai"]["enabled"] = 1;
			$allrewriters_settings["wordai"]["options"]["wai_rewrite_email"]["value"] = $options['wpr_wai_rewrite_email'];
			$allrewriters_settings["wordai"]["options"]["wai_rewrite_pw"]["value"] = $options['wpr_wai_rewrite_pw'];
			$allrewriters_settings["wordai"]["options"]["wai_quality"]["value"] = $options['wpr_wai_quality'];
			if($options['wpr_wai_sentence'] == "on") {$allrewriters_settings["wordai"]["options"]["wai_sentence"]["value"] = 1;}
			if($options['wpr_wai_paragraph'] == "on") {$allrewriters_settings["wordai"]["options"]["wai_paragraph"]["value"] = 1;}
			if($options['wpr_wai_nooriginal'] == "on") {$allrewriters_settings["wordai"]["options"]["wai_nooriginal"]["value"] = 1;}
		}			
	}
	
	update_option('allrewriters_settings',$allrewriters_settings);		
	return $allrewriters_settings;
}
//register_activation_hook(__FILE__, 'ar_activate');

function ar_deactivate() {
	delete_option('allrewriters_settings');		
}
//register_deactivation_hook( __FILE__, 'ar_deactivate' );

function ar_settings_page_scripts() {
	wp_enqueue_script('jquery');	
}

if(isset($_GET['page']) && $_GET['page'] == 'ar-settings' ) {
	add_action('admin_head', 'ar_settings_page_head');		
}
function ar_settings_page_head() {
	?>
    <script type="text/javascript">	
	jQuery(document).ready(function($) {
		var index;
		var modules = ["spinnerchief", "thebestspinner", "spinchimp", "spinrewriter", "wordai", "contentprofessor"];
		for (index = 0; index < modules.length; ++index) {
			toggle("#" + modules[index], "#" + modules[index] + "_enabled");
		}
	});
	
	function toggle(className, obj) {
		var jQueryinput = jQuery(obj);
		if (jQueryinput.prop('checked')) jQuery(className).show();
		else jQuery(className).hide();
	}
	</script>		
	<?php
}

function ar_settings_page() {
	$options = get_option("allrewriters_settings");  //print_r($options );
	if(empty($options)) {$options = ar_activate();}
	
	if($_GET["c"] == 1) {
		echo allrewriters_sc_rewrite('<p>Libertarian-leaning Republicans in Congress, including <a target="new" href="https://twitter.com/RepThomasMassie/status/372358495439294464">Reps. Thomas Massie, R-Ky.</a> and <a target="new" href="https://twitter.com/repjustinamash/status/372352492061077504">Justin Amash, R-Mich.</a>, said via Twitter on Tuesday that any action without congressional approval would clearly be unconstitutional.</p>');
	}
	
	if($_POST["save_options"]) {	
		foreach($options as $module => $moduledata) {

			if(2 != $moduledata["enabled"]) {$options[$module]["enabled"] = $_POST[$module."_enabled"];}
		
			foreach($moduledata["options"] as $option => $data) {
				if($option != "title" && $option != "unique" && $option != "error" && $option != "unique_direct" && $option != "title_direct") {			
					$options[$module]["options"][$option]["value"] = $_POST[$module."_".$option];
				}
			}			
		}
		
		$options["general"]["options"]["chain"][1] = $_POST["allrewriters_chain_1"];
		$options["general"]["options"]["chain"][2] = $_POST["allrewriters_chain_2"];
		$options["general"]["options"]["chain"][3] = $_POST["allrewriters_chain_3"];

		$result = update_option("allrewriters_settings", $options);
		if($result) {
			echo '<div class="updated below-h2"><p>Options have been updated.</p></div>';	
		} else {
			echo '<div class="updated below-h2"><p>Error: Options could not be updated.</p></div>';	
		}			
	}

	$rwactive = 0;
	foreach($options as $module => $moduledata) {
		if(1 == $moduledata["enabled"]) {
			$rwactive = 1;
		}	
	}	
	
	if($rwactive == 0) {	
		$llist = array();
		foreach($options as $module => $moduledata) {if(2 != $moduledata["enabled"]) {$llist[] = '<a href="'.$moduledata["link"].'" target="_blank">'.$moduledata["name"].'</a>';}}

		$last = array_pop($llist);
		$string = count($llist) ? implode(", ", $llist) . " and " . $last : $last;
	}
	
?>
<div class="wrap">
	<h2><?php _e("All Rewriters Settings","allrewriters") ?></h2>
	
	<?php if($rwactive == 0) {	?>
		<p><?php _e("<strong>No rewriter activated.</strong> Check the rewriters you want to use and then enter your user details for authorization. Once set up rewriting will show up on the Wordpress 'Add New' screens.","wprobot") ?></p>
		
		<p><?php _e("All Rewriters supports","wprobot") ?> <?php echo $string; ?>. <?php _e("<strong>Tip</strong>: Use several rewriters together for the best results!","wprobot") ?></p>
	<?php } ?>

	<form method="post" name="allrewriter_options">	
	
				<?php $num = 0; foreach($options as $module => $moduledata) { $num++; ?>

					<?php if(2 == $moduledata["enabled"]) { ?>
						<h3><?php echo $moduledata["name"]; ?></h3>
					<?php } else { ?>
						<h3><input style="margin-right: 5px; margin-top: 2px;" onclick="toggle('#<?php echo $module; ?>', this)" class="button" type="checkbox" id="<?php echo $module."_enabled"; ?>" name="<?php echo $module."_enabled"; ?>" value="1" <?php if(1 == $moduledata["enabled"]) {echo "checked";} ?>/><label for="<?php echo $module."_enabled"; ?>"><?php echo $moduledata["name"]; ?></label> <?php if(!empty($moduledata["link"])) { ?><a target="_blank" href="<?php echo $moduledata["link"]; ?>">Sign Up &rarr;</a><?php } ?></h3>
					<?php } ?>
					
					<div id="<?php echo $module; ?>">					
					<table class="form-table">
						<tbody>				
					
							<?php foreach($moduledata["options"] as $option => $data) {
								if($option != "title" && $option != "unique" && $option != "error" && $option != "unique_direct" && $option != "title_direct") {
									if($data["type"] == "text") { // Text Option ?> 
										<tr>
											<th scope="row"><label for="<?php echo $module."_".$option;?>"><?php echo $data["name"];?></label></th>
											<td><input class="regular-text" type="text" name="<?php echo $module."_".$option;?>" value="<?php echo $data["value"]; ?>" />

												<!-- EXPLANATION DISPLAY -->	
												<?php if(!empty($optionsexpl[$module]["options"][$option]["explanation"])) { ?>
													<span style="font-size: 90%;color: #666;"><?php 
													if(!empty($optionsexpl[$module]["options"][$option]["link"])) {echo '<a target="_blank" href="'.$optionsexpl[$module]["options"][$option]["link"].'">';} 
													echo $optionsexpl[$module]["options"][$option]["explanation"]; 
													if(!empty($optionsexpl[$module]["options"][$option]["link"])) {echo '</a>';} 
													?></span>
												<?php } ?>
											</td>	
										</tr>
									<?php } elseif($data["type"] == "select") { // Select Option ?>
										<tr>	
											<th scope="row"><label for="<?php echo $module."_".$option;?>"><?php echo $data["name"];?></label></th>
											<td><select name="<?php echo $module."_".$option;?>">
												<?php foreach($data["values"] as $val => $name) { ?>
												<option value="<?php echo $val;?>" <?php if($val == $data["value"]) {echo "selected";} ?>><?php echo $name; ?></option>
												<?php } ?>		
											</select></td>	
										</tr>
									<?php } elseif($data["type"] == "checkbox") { // checkbox Option ?>		
										<tr>	
											<th scope="row"><label for="<?php echo $module."_".$option;?>"><?php echo $data["name"];?></label></th>
											<td><input class="button" type="checkbox" name="<?php echo $module."_".$option; ?>" value="1" <?php if(1 == $data["value"]) {echo "checked";} ?>/>
											
													<!-- EXPLANATION DISPLAY -->	
												<?php if(!empty($optionsexpl[$module]["options"][$option]["explanation"])) { ?>
													<span style="font-size: 90%;color: #666;"><?php 
													if(!empty($optionsexpl[$module]["options"][$option]["link"])) {echo '<a target="_blank" href="'.$optionsexpl[$module]["options"][$option]["link"].'">';} 
													echo $optionsexpl[$module]["options"][$option]["explanation"]; 
													if(!empty($optionsexpl[$module]["options"][$option]["link"])) {echo '</a>';} 
													?></span>
												<?php } ?>										
											
											</td>	
										</tr>									
									<?php } elseif($data["type"] == "textarea") { // textarea Option ?>		
										<tr>	
											<th scope="row"><label for="<?php echo $module."_".$option;?>"><?php echo $data["name"];?></label></th>
											
											<td>
													<!-- EXPLANATION DISPLAY -->	
												<?php if(!empty($optionsexpl[$module]["options"][$option]["explanation"])) { ?>
													<span style="font-size: 90%;color: #666;"><?php 
													if(!empty($optionsexpl[$module]["options"][$option]["link"])) {echo '<a target="_blank" href="'.$optionsexpl[$module]["options"][$option]["link"].'">';} 
													echo $optionsexpl[$module]["options"][$option]["explanation"]; 
													if(!empty($optionsexpl[$module]["options"][$option]["link"])) {echo '</a>';} 
													?></span><br/>
												<?php } ?>												
											
											<textarea cols="80" rows="3" name="<?php echo $module."_".$option; ?>"><?php echo $data["value"]; ?></textarea>
											</td>	
										</tr>									
									<?php } ?>	
									
								<?php } ?>
							<?php } ?>
					
						</tbody>
					</table>								
					
					</div>	
				<?php } ?>
				
				
				<h3><?php _e("Rewriting Chain","wprobot") ?></h3>
				<div>
					<table class="form-table">
						<tbody>					
							<tr class="odd">	
								<th scope="row"><label for=""><?php _e("Rewriting Chain","wprobot") ?></label></th>
								<td>
								Rewriter 1: <select name="allrewriters_chain_1">
									<option <?php if(empty($options["general"]["options"]["chain"][1])) {echo "selected";} ?> value="">---</option>									
									<?php foreach($options as $module => $moduledata) { if($module != "general") { ?>
									<option <?php if($options["general"]["options"]["chain"][1] == $moduledata["function"]) {echo "selected";} ?> value="<?php echo $moduledata["function"]; ?>"><?php echo $moduledata["name"]; ?></option>									
									<?php } } ?>
								</select>
								Rewriter 2: <select name="allrewriters_chain_2">
									<option <?php if(empty($options["general"]["options"]["chain"][2])) {echo "selected";} ?> value="">---</option>									
									<?php foreach($options as $module => $moduledata) { if($module != "general") { ?>
									<option <?php if($options["general"]["options"]["chain"][2] == $moduledata["function"]) {echo "selected";} ?> value="<?php echo $moduledata["function"]; ?>"><?php echo $moduledata["name"]; ?></option>									
									<?php } } ?>
								</select>
								Rewriter 3: <select name="allrewriters_chain_3">
									<option <?php if(empty($options["general"]["options"]["chain"][3])) {echo "selected";} ?> value="">---</option>									
									<?php foreach($options as $module => $moduledata) { if($module != "general") { ?>
									<option <?php if($options["general"]["options"]["chain"][3] == $moduledata["function"]) {echo "selected";} ?> value="<?php echo $moduledata["function"]; ?>"><?php echo $moduledata["name"]; ?></option>									
									<?php } } ?>
								</select>								
								</td>	
							</tr>				
						</tbody>
					</table>					
				</div>	
				<p class="submit"><input class="button-primary" type="submit" name="save_options" value="<?php _e("Save All Settings","wprobot") ?>" /></p>	
	
<?php
}

add_action( 'add_meta_boxes', 'allrewriters_metabox' );
function allrewriters_metabox() {
	$screens = array('post', 'page');
	foreach ($screens as $screen) {
		add_meta_box('allrewriters_section',__( 'All Rewriters', 'allrewriters' ), 'allrewriters_metabox_content', $screen);
	}
}

function allrewriters_metabox_content($post) {
	$options = get_option("allrewriters_settings");
	
	$rwactive = 0;$rwcontent = "";
	foreach($options as $module => $moduledata) {

		if(1 == $moduledata["enabled"]) {
			$rwactive = 1;
			$rwcontent .= '<span id="'.$moduledata["function"] .'-load" style="display: none;">
			<img src="'.WPR_URLPATH . basename(dirname(__FILE__)).'/images/ajax-loader.gif" style="width: 16px; height: 16px;margin-bottom: -2px;" /></span>
			<input style="margin-right: 10px;" type="button" class="button ar-rewrite" id="'.$moduledata["function"] .'" value="'.$moduledata["name"] . '">';
		}	
	}
	
	if(!empty($options["general"]["options"]["chain"][1]) && !empty($options["general"]["options"]["chain"][2])) {
		$rwcontent .= '<span id="chain-load" style="display: none;">
		<img src="'.WPR_URLPATH . basename(dirname(__FILE__)).'/images/ajax-loader.gif" style="width: 16px; height: 16px;margin-bottom: -2px;" /></span>
		<input style="margin-right: 10px;" type="button" class="button ar-rewrite" id="chain" value="Chain">';	
	}

	if($rwactive == 0) {
		$llist = array();
		foreach($options as $module => $moduledata) {if(2 != $moduledata["enabled"]) {$llist[] = '<a href="'.$moduledata["link"].'" target="_blank">'.$moduledata["name"].'</a>';}}

		$last = array_pop($llist);
		$string = count($llist) ? implode(", ", $llist) . " and " . $last : $last;

		echo '<p>No rewriter activated. Please go to the <a href="admin.php?page=ar-settings">Options page</a> to do so. All Rewriters supports '.$string.'. <strong>Tip</strong>: Use several rewriters together for the best results!</p>';
	} else {
	
	?>
	
	<div id="rewriteall_rws">
		<span>Rewrite editor content with:</span>
		<?php echo $rwcontent; ?>
	</div>
	
	<div id="ar_error_box" style="display: none;background-color: #EDB1B1;padding: 5px;margin-top: 10px;font-weight: bold;">	
	</div>
	
	<div id="first_results" style="display: none;background-color: #fff;padding: 5px;margin-top: 10px;border: 1px dotted #CCCCCC;">	
	
		<div class="ar-controls" style="margin-bottom: 5px; padding-bottom: 5px; border-bottom: 1px dotted #969696;">
			<strong class="ar-result-title"style="margin-right: 10px;" ></strong>
			
			<span class="ar-check" style="display: none;">
			<img src="<?php echo WPR_URLPATH . basename(dirname(__FILE__)).'/images/check.png'; ?>" style="width: 16px; height: 16px;margin-bottom: -2px;" />
			</span>

			<input type="button" class="ar-use-button button-primary" value="Copy To Editor">
		</div>	
	
		<div class="ar-results">
		</div>
		
		<div class="ar-controls-bottom" style="margin-top: 5px; padding-top: 5px; border-top: 1px dotted #969696;">
			<strong>Rewrite again with:</strong>
			<?php echo str_replace('id="', 'id="2-', $rwcontent); ?>	
		</div>
	</div>
	
	<?php
	}
}

//add_action( 'save_post', 'allrewriters_save_metabox' );
//function allrewriters_save_metabox( $post_id ) {
//}

// Header
function allrewriters_head() {
?>
    <script type="text/javascript">	
	jQuery(document).ready(function($) {
		jQuery('.ar-rewrite').click(function(e) {

			e.preventDefault();
			if(jQuery(this).parent().hasClass("ar-controls-bottom") == true) {
				var secondary = true;
			}
			
			var rewriter = jQuery(this).attr('id').replace('2-','');	
			var rewritername = jQuery(this).val();			
			jQuery(this).attr("disabled", "disabled");

			if(secondary == true) {
				var jthis = jQuery(this);
				var secparent = jQuery(this).parent().attr('id').replace('-ar-controls-bottom','');
				var rewrite_text = jQuery('#' + secparent + "-ar-results").html();
				jQuery(this).parent().find('#2-' + rewriter + '-load').show();
			} else {		

				if(jQuery("#content").is(":visible")) {
					var rewrite_text = jQuery('#content').val();		
				} else {
					var rewrite_text = tinyMCE.get('content').getContent();		
				}		
			
				jQuery('#' + rewriter + '-load').show();				
			}
			
	
			var ar_meta_box_nonce = {
				security: '<?php echo wp_create_nonce('ar_nonce');?>'
			}			
					
			var data = {
				action: 'ar_rewrite',
				wpnonce: ar_meta_box_nonce.security,
				text: rewrite_text,
				rewriter: rewriter,
				ajax: 1,
				};
				
			jQuery.ajax ({
				type: 'POST',
				url: ajaxurl,
				data: data,
				dataType: 'json',
				success: function(response) {
					if(response.error != undefined && response.error != "") {	

						var clone = jQuery("#ar_error_box").clone();
						clone.attr("id", rewriter + "-error");
						clone.html(rewritername + " Error: " + response.error);
						clone.insertAfter("#rewriteall_rws");	
						
						jQuery('#' + rewriter + '-error').show();
						jQuery('#' + rewriter + '-error').fadeOut(10000, function() { jQuery('#' + rewriter + '-error').remove(); });
						
						if(secondary == true) {
							jQuery('#2-' + rewriter).removeAttr("disabled"); 
							jQuery('#2-' + rewriter + '-load').hide();						
						} else {
							jQuery("#" + rewriter).removeAttr("disabled"); 
							jQuery('#' + rewriter + '-load').hide();
						}
					} else {	
						if(secondary == true) {

							jQuery('#' + secparent + '-ar-results').html(response.result);
							
							jthis.parent().parent().find(".ar-use-button").removeAttr("disabled"); 
							jthis.parent().parent().find(".ar-check").hide(); 
							
							jthis.parent().find('#2-' + rewriter + '-load').hide();
							jthis.parent().find('#2-' + rewriter).remove();				
						} else {
							var clone = jQuery("#first_results").clone(true, true);
							clone.attr("id", rewriter + "-results");
							clone.find(".ar-results").attr("id", rewriter + "-ar-results");
							clone.find(".ar-controls").attr("id", rewriter + "-ar-controls");
							clone.find(".ar-controls-bottom").attr("id", rewriter + "-ar-controls-bottom");
							clone.find(".ar-use-button").attr("id", rewriter + "-use");
							clone.find(".ar-result-title").html(rewritername + " Results");
							clone.find(".ar-results").html(response.result);
							clone.insertAfter("#rewriteall_rws");						
							jQuery('#' + rewriter + '-results').show();
							
							jQuery("#" + rewriter).remove();
							jQuery('#' + rewriter + '-load').hide();
						}
					}
				}
			});			

			return false;
		});	
		
		jQuery('.ar-use-button').click(function(e) {

			e.preventDefault();
			var rewriter = jQuery(this).attr('id').replace('-use','');
			var rwcontent = jQuery("#" + rewriter + "-ar-results").html();

			if(jQuery("#content").is(":visible")) {
				jQuery('#content').val(rwcontent);
			} else {
				tinyMCE.execCommand('mceSetContent',false,rwcontent);	// mceReplaceContent command - to replace selection with rewritten content?		
			}			

			jQuery(".ar-check").hide();
			jQuery(this).parent().find(".ar-check").show();
			
			jQuery(".ar-use-button").removeAttr("disabled"); 
			jQuery(this).attr("disabled", "disabled");
		});			

	});
	</script>
<?php
}

function allrewriters_ajax_action() {

	$rewriterfunction = $_POST["rewriter"];
	
	if(get_magic_quotes_gpc()) {
		$text = stripslashes($_POST['text']);
	} else {
		$text = $_POST["text"];
	}

	$nonce = $_POST["wpnonce"];
	
	if (!wp_verify_nonce($nonce, 'ar_nonce')) {
		echo json_encode(array("error" => "Invalid request."));
		exit;
	}	
	
	if($rewriterfunction == "chain") {
		$options = get_option("allrewriters_settings");  //print_r($options );
	
		foreach($options["general"]["options"]["chain"] as $ch => $chfunc) {
			if(!empty($chfunc)) {
				$result = $chfunc($text);
				if(is_array($result) && !empty($result["error"])) {
					echo json_encode(array("error" => $result["error"]));
					exit;		
				} else {
					$text = $result;
				}				
			}
		}
		echo json_encode(array("result" => $text));
		exit;			
	}

	if(empty($rewriterfunction) || !function_exists($rewriterfunction)) {
		echo json_encode(array("error" => "Rewriter function not found."));
		exit;	
	}
	
	if(empty($text)) {
		echo json_encode(array("error" => "Content is empty."));
		exit;	
	}	

	$result = $rewriterfunction($text);

	if(is_array($result) && !empty($result["error"])) {
		echo json_encode(array("error" => $result["error"]));
		exit;		
	} else {
		echo json_encode(array("result" => $result));
		exit;	
	}
}

if(is_admin()){
    if(in_array($GLOBALS['pagenow'], array('post.php', 'post-new.php'))){
		add_action('admin_head', 'allrewriters_head');		
    }

    add_action('wp_ajax_ar_rewrite', 'allrewriters_ajax_action');
}

?>