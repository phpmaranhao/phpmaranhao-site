<?php
/* Finding the path to the wp-admin folder */
$iswin = preg_match('/:\\\/', dirname(__file__));
$slash = ($iswin) ? "\\" : "/";

$wp_path = preg_split('/(?=((\\\|\/)wp-content)).*/', dirname(__file__));
$wp_path = (isset($wp_path[0]) && $wp_path[0] != "") ? $wp_path[0] : $_SERVER["DOCUMENT_ROOT"];

/** Load WordPress Administration Bootstrap */
require_once($wp_path . $slash . 'wp-load.php');
require_once($wp_path . $slash . 'wp-admin' . $slash . 'admin.php');

// check for rights
if ( !is_user_logged_in() || !current_user_can('edit_posts') ) 
	wp_die(__( "You are not allowed to be here", 'post-snippets' ));

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>WP Robot</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<script language="javascript" type="text/javascript" src="<?php echo get_option('siteurl') ?>/wp-includes/js/tinymce/tiny_mce_popup.js"></script>
	<script language="javascript" type="text/javascript" src="<?php echo get_option('siteurl') ?>/wp-includes/js/tinymce/utils/mctabs.js"></script>
	<script language="javascript" type="text/javascript" src="<?php echo get_option('siteurl') ?>/wp-includes/js/tinymce/utils/form_utils.js"></script>
	<script language="javascript" type="text/javascript">
	function init() {
		tinyMCEPopup.resizeToInnerSize();
	}

	function insertWPRCode() {

		var insertString;

		var start = document.getElementById('start').value;
		var module = document.getElementById('module').value;
		var keyword = document.getElementById('keyword').value;
		var num = document.getElementById('num').value;
		
		insertString = '{wprobot module="' + module + '" keyword="' + keyword + '" start="' + start + '" num="' + num + '"}';

		if(window.tinyMCE) {
			//window.tinyMCE.execInstanceCommand('content', 'mceInsertContent', false, insertString);
			tinyMCEPopup.execCommand("mceBeginUndoLevel");
			tinyMCEPopup.execCommand('mceInsertContent', false, insertString);
			tinyMCEPopup.execCommand("mceEndUndoLevel");
			//Peforms a clean up of the current editor HTML. 
			//tinyMCEPopup.editor.execCommand('mceCleanup');
			//Repaints the editor. Sometimes the browser has graphic glitches. 
			tinyMCEPopup.editor.execCommand('mceRepaint');
			tinyMCEPopup.close();
		}
		return;
	}
	</script>
	<base target="_self" />
</head>
<body id="link" onload="tinyMCEPopup.executeOnLoad('init();');document.body.style.display='';" style="display: none">

	<form name="post_snippets" action="#">

	<div class="tabs">
		<ul>
			<li id="ps_tab1" class="current"><span>Insert Content</span></li>
		</ul>
	</div>
	
	<div class="panel_wrapper" style="overflow:auto;height:165px;">
	
        <div id="ps_panel1" class="panel current" style="height:165px;">
        <br />
        <table border="0" cellpadding="4" cellspacing="0">

			 <tr>
				<td nowrap="nowrap"><label for="keyword">Keyword:</label></td>
				<td><input type="text" id="keyword" name="keyword" style="width: 190px" />
				</td>
			  </tr>
			 <tr>
				<td nowrap="nowrap"><label for="module">Module:</label></td>
				<td>
					<select id="module" name="module">
					<?php foreach($wpr_loadedmodules as $loadedmodule) {if($loadedmodule != "translation"){echo "<option>".ucwords($loadedmodule)."</option>";}} ?>			
					</select>
				</td>
			  </tr>	
			 <tr>
				<td nowrap="nowrap"><label for="start">Num:</label></td>
				<td><input type="text" id="num" name="num" style="width: 190px" value="1" />
				</td>
			  </tr>					  
			 <tr>
				<td nowrap="nowrap"><label for="start">Start:</label></td>
				<td><input type="text" id="start" name="start" style="width: 190px" value="1" />
				</td>
			  </tr>			  
        </table>
		<p>"Start" marks the number of the returned results that will be used, "Num" the number of results inserted. See the <a href="http://wprobot.net/documentation/#64" target="_blank">documentation</a> for details.</p>
        </div>	

	</div>

	<div class="mceActionPanel">
		<div style="float: left">
			<input type="button" id="cancel" name="cancel" value="<?php _e( 'Cancel', 'post-snippets' ); ?>" onclick="tinyMCEPopup.close();" />
		</div>

		<div style="float: right">
			<input type="submit" id="insert" name="insert" value="<?php _e( 'Insert', 'post-snippets' ); ?>" onclick="insertWPRCode();" />
		</div>
	</div>
</form>
</body>
</html>