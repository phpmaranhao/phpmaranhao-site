(function() {
	// Load plugin specific language pack
	tinymce.PluginManager.requireLangPack('wprobot');
	
	tinymce.create('tinymce.plugins.wprobot', {
		/**
		 * Initializes the plugin, this will be executed after the plugin has been created.
		 * This call is done before the editor instance has finished it's initialization so use the onInit event
		 * of the editor instance to intercept that event.
		 *
		 * @param {tinymce.Editor} ed Editor instance that the plugin is initialized in.
		 * @param {string} url Absolute URL to where the plugin is located.
		 */
		init : function(ed, url) {
			// Register the command so that it can be invoked by using tinyMCE.activeEditor.execCommand('mceExample');

			ed.addCommand('mcewprobot', function() {
				ed.windowManager.open({
					file : url + '/window.php',
					width : 360 + ed.getLang('wprobot.delta_width', 0),
					height : 245 + ed.getLang('wprobot.delta_height', 0),
					inline : 1
				}, {
					plugin_url : url // Plugin absolute URL
				});
			});

			// Register example button
			ed.addButton('wprobot', {
				title : 'wprobot.desc',
				cmd : 'mcewprobot',
				image : url + '/wpr.gif'
			});
		},

		/**
		 * Returns information about the plugin as a name/value array.
		 * The current keys are longname, author, authorurl, infourl and version.
		 *
		 * @return {Object} Name/value array containing information about the plugin.
		 */
		getInfo : function() {
			return {
					longname  : 'wprobot',
					author 	  : 'Thomas Hoefter',
					authorurl : 'http://wprobot.net/',
					infourl   : 'http://wprobot.net/',
					version   : "3.0"
			};
		}
	});

	// Register plugin
	tinymce.PluginManager.add('wprobot', tinymce.plugins.wprobot);
})();
