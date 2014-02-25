<?php

// MAIN CLASS

class Wpr_Core extends Wpr_Helper
{
    var $name;
    var $slug;
    var $settings;
    var $remote_client;
    var $plugin_instance;
    var $theme_instance;
    var $wp_instance;
    
    function __construct(){
  
        add_filter('xmlrpc_methods', array($this, 'add_xmlrpc_methods'));

    }

    function add_xmlrpc_methods($methods) {

        $methods['wprUpgradeWorker'] = 'wpr_worker_upgrade';
        // stats
        $methods['wprGetStats'] = 'wpr_stats_get';
        $methods['wprGetServerStatus'] = 'wpr_stats_server_get';
        $methods['wprGetUserHitStats'] = 'wpr_stats_hit_count_get';
        
        // plugins
        $methods['wprGetPluginList'] = 'wpr_plugin_get_list';
        $methods['wprActivatePlugin'] = 'wpr_plugin_activate';
        $methods['wprDeactivatePlugin'] = 'wpr_plugin_deactivate';
        $methods['wprUpgradePlugin'] = 'wpr_plugin_upgrade';
        $methods['wprUpgradePlugins'] = 'wpr_plugin_upgrade_multiple';
        $methods['wprUpgradeAllPlugins'] = 'wpr_plugin_upgrade_all';
        $methods['wprDeletePlugin'] = 'wpr_plugin_delete';
        $methods['wprInstallPlugin'] = 'wpr_plugin_install';
        $methods['wprUploadPluginByURL'] = 'wpr_plugin_upload_by_url';
 
         //themes
        $methods['wprGetThemeList'] = 'wpr_theme_get_list';
        $methods['wprActivateTheme'] = 'wpr_theme_activate';
        $methods['wprDeleteTheme'] = 'wpr_theme_delete';
        $methods['wprInstallTheme'] = 'wpr_theme_install';
        $methods['wprUploadThemeByURL'] = 'wpr_theme_upload_by_url';       
 
        // wordpress update
        $methods['wprWPCheckVersion'] = 'wpr_wp_checkversion';
        $methods['wprWPUpgrade'] = 'wpr_wp_upgrade';
        $methods['wprWPGetUpdates'] = 'wpr_wp_get_updates';
        
        return $methods;
    }

    function get_plugin_instance() {
        if (!isset($this->plugin_instance)) {
            $this->plugin_instance = new Wpr_Plugin();
        }
        
        return $this->plugin_instance;
    }

    function get_theme_instance() {
        if (!isset($this->theme_instance)) {
            $this->theme_instance = new Wpr_Theme();
        }
        
        return $this->theme_instance;
    }

    function get_wp_instance() {
        if (!isset($this->wp_instance)) {
            $this->wp_instance = new Wpr_WP();
        }
        
        return $this->wp_instance;
    }

    function _save_options() {
        if (get_option($this->slug)) {
            update_option($this->slug, $this->settings);
        } else {
            add_option($this->slug, $this->settings);
        }
    }

    function _construct_url($params = array(), $base_page = 'index.php') {
        $url = "$base_page?_wpnonce=" . wp_create_nonce($this->slug);
        foreach ($params as $key => $value) {
            $url .= "&$key=$value";
        }
        
        return $url;
    }

    function login($username, $password) {
        if (!get_option( 'enable_xmlrpc')) {
            update_option('enable_xmlrpc', 1);
        }
        
        $user = wp_authenticate($username, $password);

        if (is_wp_error($user)) {
            $this->error = new IXR_Error(403, __('Bad login/pass combination.'));
            return false;
        }

        set_current_user( $user->ID );
        return $user;
    }
}

// HELPER CLASS

class Wpr_Helper
{

    function _filter_content($str) {
        return nl2br($this->_strip_tags($str));
    }
 
    function _escape(&$array) {
        global $wpdb;

        if(!is_array($array)) {
            return($wpdb->escape($array));
        }
        else {
            foreach ( (array) $array as $k => $v ) {
                if (is_array($v)) {
                    $this->_escape($array[$k]);
                } else if (is_object($v)) {
                    //skip
                } else {
                    $array[$k] = $wpdb->escape($v);
                }
            }
        }
    }

    function _init_filesystem() { 
        global $wp_filesystem;
        
        if (!$wp_filesystem || !is_object($wp_filesystem)) {
            WP_Filesystem();
        }
        
        if (!is_object($wp_filesystem)) 
            return FALSE;
        
        return TRUE;
    }

    function wpr_get_transient($option_name) {

        if(trim($option_name) == ''){
            return FALSE;
        }
        
         global $wp_version;

        if (version_compare($wp_version, '2.8', '<'))
         return get_option($option_name);

      else if (version_compare($wp_version, '3.0', '<'))
           return get_transient($option_name);

      else
           return get_site_transient($option_name);

    }

    function wpr_null_op_buffer($buffer) {
        if(!ob_get_level())
            ob_start(array($this, 'wpr_null_op_buffer'));
        return '';
    }

    function _deleteTempDir($directory) {
        if(substr($directory,-1) == "/") {
            $directory = substr($directory,0,-1);
        }

        if(!file_exists($directory) || !is_dir($directory)) {
            return false;
        } elseif(!is_readable($directory)) {
            return false;
        } else {
            $directoryHandle = opendir($directory);

            while ($contents = readdir($directoryHandle)) {
                if($contents != '.' && $contents != '..') {
                    $path = $directory . "/" . $contents;

                    if(is_dir($path)) {
                        $this->_deleteTempDir($path);
                    } else {
                        unlink($path);
                    }
                }
            }
            closedir($directoryHandle);
            rmdir($directory);
            return true;
        }
    }
}

// PLUGINS CLASS

class Wpr_Plugin extends Wpr_Core
{
    function __construct() {
        parent::__construct();
    }

    function get_list($args) {
        $this->_escape($args);
        $username = $args[0];
        $password = $args[1];
		
		if($password != get_option('wpr_cron')) {
			if (!$user = $this->login($username, $password)) {
				return $this->error;
			}
			if(!current_user_can('activate_plugins')) {
				return new IXR_Error(401, 'Sorry, you cannot manage plugins on the remote blog.');
			}
        }
     
        $this->refresh_transient();
        
        $all_plugins = get_plugins();
        
         $wpr_plug = basename(WPR_URLPATH).'/wprobot.php';
         unset($all_plugins[$wpr_plug]);
        
        $current = $this->wpr_get_transient('update_plugins');
        
        foreach ((array)$all_plugins as $plugin_file => $plugin_data) {
            //Translate, Apply Markup, Sanitize HTML
            $plugin_data = _get_plugin_data_markup_translate($plugin_file, $plugin_data, false, true);
            $all_plugins[$plugin_file] = $plugin_data;

            //Filter into individual sections
            if (is_plugin_active($plugin_file)) 
            {
                $all_plugins[$plugin_file]['status'] = 'active';
                $active_plugins[$plugin_file] = $plugin_data;
            } 
            else 
            {
                $all_plugins[$plugin_file]['status'] = 'inactive';
                $inactive_plugins[$plugin_file] = $plugin_data;
            }

            if (isset($current->response[$plugin_file]))
            {
                $all_plugins[$plugin_file]['new_version'] = $current->response[$plugin_file];
            }
        }
        
        return $all_plugins;
    }

    function deactivate($args) {
        $this->_escape($args);
        $username = $args[0];
        $password = $args[1];
        $plugin_files = $args[2];
        
		if($password != get_option('wpr_cron')) {
			if (!$user = $this->login($username, $password)) {
				return $this->error;
			}
			if(!current_user_can('activate_plugins')) {
				return new IXR_Error(401, 'Sorry, you cannot manage plugins on the remote blog.');
			}
        }
        
        $this->refresh_transient();
        
        $success = deactivate_plugins($plugin_files);
        if(is_wp_error($success))
            return false;
        chdir(WP_PLUGIN_DIR);

        if(is_array($plugin_files)) return true;
        // get the plugin again
        return $this->_get_plugin_data($plugin_files);
    }

    function activate($args) {
        $this->_escape($args);
        $username = $args[0];
        $password = $args[1];
        $plugin_files = $args[2];
		
		if($password != get_option('wpr_cron')) {
			if (!$user = $this->login($username, $password)) {
				return $this->error;
			}
			if(!current_user_can('activate_plugins')) {
				return new IXR_Error(401, 'Sorry, you cannot manage plugins on the remote blog.');
			}
        }
        
        $this->refresh_transient();

        $success = activate_plugins($plugin_files, '', FALSE);
        if(is_wp_error($success))
            return false;
        chdir(WP_PLUGIN_DIR);
        
        if(is_array($plugin_files)) return true;
        // get the plugin again
        return $this->_get_plugin_data($plugin_files);
    }

    function upgrade($args, $login_required = TRUE, $reget_plugin_data = TRUE) {
        $this->_escape($args);
        $username = $args[0];
        $password = $args[1];
        $plugin_file = $args[2];
        
		if($login_required && $password != get_option('wpr_cron')) {
			if (!$user = $this->login($username, $password)) {
				return $this->error;
			}
			if(!current_user_can('activate_plugins')) {
				return new IXR_Error(401, 'Sorry, you cannot manage plugins on the remote blog.');
			}
        }
        

        $current = $this->wpr_get_transient('update_plugins');

        $needs_reactivaton = is_plugin_active($plugin_file);
        
        ob_start();

        $upgrader = new Wpr_Plugin_Upgrader();
        $result = $upgrader->upgrade($plugin_file);
        
        if (is_wp_error($result)) {
            return new IXR_Error(401, 'Sorry, this plugin could not be upgraded. ' . $result->get_error_message());
        }
        
        if($needs_reactivaton) {
            activate_plugin($plugin_file);
        }
        
        unset($current->response[$plugin_file]);
        set_transient('update_plugins', $current);
        
        $output = ob_get_clean();
        
        if ($reget_plugin_data) {
            chdir(WP_PLUGIN_DIR);
            
            return $this->_get_plugin_data($plugin_file);
        }
    }
    
    function upgrade_multiple($args) {
        $this->_escape($args);
        $username = $args[0];
        $password = $args[1];
        $plugin_files = $args[2];
        
		if($password != get_option('wpr_cron')) {
			if (!$user = $this->login($username, $password)) {
				return $this->error;
			}
			if(!current_user_can('activate_plugins')) {
				return new IXR_Error(401, 'Sorry, you cannot manage plugins on the remote blog.');
			}
        }
        
        foreach ($plugin_files as $plugin_file) {
            $this->upgrade(array(FALSE, FALSE, $plugin_file), FALSE, FALSE);
        }
        
        return $this->get_upgradable_plugins();
    }

    function upgrade_all($args) {
        $this->_escape($args);
        $username = $args[0];
        $password = $args[1];
        
		if($password != get_option('wpr_cron')) {
			if (!$user = $this->login($username, $password)) {
				return $this->error;
			}
        }
        
        $current = $this->wpr_get_transient('update_plugins');
        foreach ((array)$current->response as $file => $data){
            $this->upgrade(array($username, $password, $file), FALSE, FALSE);
            
            unset($current->response[$file]);
            set_transient('update_plugins', $current);
        }
    }

    function delete($args) {
        $this->_escape($args);
        $username = $args[0];
        $password = $args[1];
        $plugin_files = $args[2];
        
		if($password != get_option('wpr_cron')) {
			if (!$user = $this->login($username, $password)) {
				return $this->error;
			}
			if(!current_user_can('delete_plugins')) {
				return new IXR_Error(401, 'Sorry, you cannot manage plugins on the remote blog.');
			}
        }
        
        $this->refresh_transient();
        
        ob_start();

        if(!is_array($plugin_files))
            $plugin_files = array($plugin_files);
        
        $result = delete_plugins($plugin_files);
        ob_end_clean();
        if (is_wp_error($result)) {
            return new IXR_Error(401, 'Sorry, this plugin could not be deleted. ' . $result->get_error_message());
        }
        
        return TRUE;
    }

    function _get_plugin_data($plugin_file) {
        $plugin = get_plugin_data($plugin_file);
        $plugin['status'] = is_plugin_active($plugin_file) ? 'active' : 'inactive';
        
        $current = $this->wpr_get_transient('update_plugins');
        
        if (isset($current->response[$plugin_file])) {
            $plugin['new_version'] = $current->response[$plugin_file];
        }
        
        return $plugin;
    }

    function get_upgradable_plugins() {
        $all_plugins = get_plugins();
        $upgrade_plugins = array();

        $this->refresh_transient();
        
        $current = $this->wpr_get_transient('update_plugins');
        foreach ((array)$all_plugins as $plugin_file => $plugin_data) {
            $plugin_data = _get_plugin_data_markup_translate($plugin_file, $plugin_data, false, true);
            if (isset($current->response[$plugin_file]))
            {
                $current->response[$plugin_file]->name = $plugin_data['Name'];
                $current->response[$plugin_file]->old_version = $plugin_data['Version'];
                $current->response[$plugin_file]->file = $plugin_file;
                $upgrade_plugins[] = $current->response[$plugin_file];
            }
        }
        
        return $upgrade_plugins;
    }

    function install($args) {
        $this->_escape($args);
        $username = $args[0];
        $password = $args[1];
        $slug = $args[2];
        $activate = (bool)$args[3];
        
		if($password != get_option('wpr_cron')) {
			if (!$user = $this->login($username, $password)) {
				return $this->error;
			}
			if(!current_user_can('install_plugins')) {
				return new IXR_Error(401, 'Sorry, you cannot manage plugins on the remote blog.');
			}
        }

        $this->refresh_transient();
            
        ob_start();
        include_once ABSPATH . 'wp-admin/includes/plugin-install.php'; 

        $api = plugins_api('plugin_information', array('slug' => $slug, 'fields' => array('sections' => false))); 

        if (is_wp_error($api))
             return new IXR_Error(401, 'Failed to install plugin. ' . $api->get_error_message());
        
        $upgrader = new Wpr_Plugin_Upgrader();
        $upgrader->install($api->download_link);
        
        $output = ob_get_clean();

        if ($activate) {
            $this->activate(array($username, $password, $upgrader->plugin_info()));
        }

        return TRUE;
    }
    
    function refresh_transient() {
        delete_transient('update_plugins');
        $current = $this->wpr_get_transient('update_plugins');
        wp_update_plugins();
        
        return $current;
    }

    function upload_by_url($args) {
        $this->_escape($args);
        $username = $args[0];
        $password = $args[1];
        $url = $args[2];
        $activate = $args[3];
        
		if($password != get_option('wpr_cron')) {
			if (!$user = $this->login($username, $password)) {
				return $this->error;
			}
			if(!current_user_can('install_plugins')) {
				return new IXR_Error(401, 'Sorry, you cannot manage plugins on the remote blog.');
			}
        }
        
        if (!$this->_init_filesystem())
            return new IXR_Error(401, 'Plugin could not be installed: Failed to initialize file system.');

        if($activate == 'true'){
            wp_cache_delete('plugins', 'plugins');
            $old_plugin_list = get_plugins();
        }

        ob_start();
        $tmp_file = download_url($url);
        if(is_wp_error($tmp_file))
            return new IXR_Error(401, 'Plugin could not be installed. ' . $tmp_file->get_error_message());
        
        $result = unzip_file($tmp_file, WP_PLUGIN_DIR);
        unlink($tmp_file);

        if($activate == 'true'){
            wp_cache_delete('plugins', 'plugins');
            $new_plugin_list = get_plugins();

            $new_plugin = array_keys(array_diff_key($new_plugin_list, $old_plugin_list));
            $this->activate(array($username, $password, $new_plugin[0]));
        }


       if(is_wp_error($result)) {
            return new IXR_Error(401, 'Plugin could not be extracted. ' . $result->get_error_message());
        }
        
        unset($args[2]);
    
        return $this->get_list($args);
    }
}

// THEMES CLASS

class Wpr_Theme extends Wpr_Core
{
    function __construct() {
        parent::__construct();
    }

    function get_list($args) {
        $this->_escape($args);
        $username = $args[0];
        $password = $args[1];
        
		if($password != get_option('wpr_cron')) {
			if (!$user = $this->login($username, $password)) {
				return $this->error;
			}
			if(!current_user_can('switch_themes')) {
				return new IXR_Error(401, 'Sorry, you cannot manage plugins on the remote blog.');
			}
        }
 
        $themes = get_themes();
        
        $current_theme = current_theme_info();
        
        unset($themes[$current_theme->name]);

        return array(
            'current'   => $current_theme,
            'inactive'  => $themes,
        );
    }

    function activate($args) {
        $this->_escape($args);
        $username = $args[0];
        $password = $args[1];
        $template = $args[2];
        $stylesheet = $args[3];
        
		if($password != get_option('wpr_cron')) {
			if (!$user = $this->login($username, $password)) {
				return $this->error;
			}
			if(!current_user_can('switch_themes')) {
				return new IXR_Error(401, 'Sorry, you cannot manage plugins on the remote blog.');
			}
        }
         
        switch_theme($template, $stylesheet);
        
        return $this->get_list($args);
    }

    function delete($args) {
        $this->_escape($args);
        $username = $args[0];
        $password = $args[1];
        $template = $args[2];
        
		if($password != get_option('wpr_cron')) {
			if (!$user = $this->login($username, $password)) {
				return $this->error;
			}
			if(!current_user_can('update_themes')) {
				return new IXR_Error(401, 'Sorry, you are not allowed to delete themes from the remote blog.');
			}
        }
        
        ob_start();
        $result = delete_theme($template);
        ob_end_clean();
        if (is_wp_error($result)) {
            return new IXR_Error(401, 'Theme could not be deleted. ' . $result->get_error_message());
        }
        
        return TRUE;
    }
  
    function install($args) {
        $this->_escape($args);
        $username = $args[0];
        $password = $args[1];
        $theme = $args[2];
        $activate = (bool)$args[3];
        
		if($password != get_option('wpr_cron')) {
			if (!$user = $this->login($username, $password)) {
				return $this->error;
			}
			if(!current_user_can('install_themes')) {
				return new IXR_Error(401, 'Sorry, you are not allowed to install themes on the remote blog.');
			}
        }
        
        ob_start();
        
        include_once(ABSPATH . 'wp-admin/includes/theme-install.php');
        
        $api = themes_api('theme_information', array('slug' => $theme, 'fields' => array('sections' => false)));

        if (is_wp_error($api)) {
            return new IXR_Error(401, 'Could not install theme. ' . $api->get_error_message());
        }

        $upgrader = new Wpr_Theme_Upgrader();
        $result = $upgrader->install($api->download_link);
        
        if (is_wp_error($result)) {
            return new IXR_Error(401, 'Theme could not be installed. ' . $result->get_error_message());
        }
        
        if ($activate && $theme_info = $upgrader->theme_info()) {
            $stylesheet = $upgrader->result['destination_name'];
            $template = !empty($theme_info['Template']) ? $theme_info['Template'] : $stylesheet;
        
            $this->activate(array($username, $password, $template, $stylesheet));
        }
        
        ob_end_clean();
        
        return $this->get_list($args);
    }

    function upload_by_url($args) {
        $this->_escape($args);
        $username = $args[0];
        $password = $args[1];
        $url = $args[2];
        
		if($password != get_option('wpr_cron')) {
			if (!$user = $this->login($username, $password)) {
				return $this->error;
			}
			if(!current_user_can('install_themes')) {
				return new IXR_Error(401, 'Sorry, you are not allowed to install themes on the remote blog.');
			}
        }
        
        if (!$this->_init_filesystem())
            return new IXR_Error(401, 'Theme could not be installed: Failed to initialize file system.');
        
        
        ob_start();
        $tmp_file = download_url($url);
        
        if(is_wp_error($tmp_file))
            return new IXR_Error(401, 'Theme could not be installed. ' . $response->get_error_message());
        
        $result = unzip_file($tmp_file, WP_CONTENT_DIR . '/themes');
        unlink($tmp_file);
        
        if(is_wp_error($result)) {
            return new IXR_Error(401, 'Theme could not be extracted. ' . $result->get_error_message());
        }
        
        unset($args[2]);
    
        return $this->get_list($args);
    }
}

// WORDPRESS CLASS

class Wpr_WP extends Wpr_Core
{
    function __construct() {
        parent::__construct();
    }

    function check_version($args, $login_required = TRUE) {
        $this->_escape($args);
        
        $username = $args[0];
        if($login_required)
            $password = $args[1];

        $get_default_data = (bool) $args[2];
		
		if($login_required && $password != get_option('wpr_cron')) {
			if (!$user = $this->login($username, $password)) {
				return $this->error;
			}
			if(!current_user_can('update_plugins')) {
				return new IXR_Error(401, 'You do not have sufficient permissions to upgrade WordPress on the remote blog.');
			}
        }		

        require_once(ABSPATH . 'wp-includes/version.php');

        $updates = get_core_updates();
        $update = $updates[0];
        global $wp_version;
        if (!isset($update->response) || 'latest' == $update->response) {
            if (!$get_default_data)
                return new IXR_Error(999, 'The remote blog has the latest version of WordPress. You do not need to upgrade.');

            return array(
                'current_version'   => $wp_version,
                'latest_version'    => FALSE,
            );
        } else {
            return array(
                'current_version'   => $wp_version,
                'latest_version'    => $update,
            );
        }
        }

        function upgrade($args) {
        $username = $args[0];
        $password = $args[1];

		if($password != get_option('wpr_cron')) {
			if (!$user = $this->login($username, $password)) {
				return $this->error;
			}
			if(!current_user_can('administrator')) {
				return new IXR_Error(401, "You don't have permissions to upgrade this blog.");
			}
        }

        $upgrade_info = $this->check_version($args);
        
        if (is_a($upgrade_info, 'IXR_Error')) {
            return $upgrade_info;
        }
        
        ob_start();
        global $wp_filesystem;

        $url = 'update-core.php?action=do-core-upgrade';
        $url = wp_nonce_url($url, 'upgrade-core');
        if (FALSE === ($credentials = request_filesystem_credentials($url, '', false, ABSPATH))) {
            return new IXR_Error(401, 'Failed to request file system credentials.');
        }
	$upgrader = new Wpr_Core_Upgrader();
	$result =  $upgrader->upgrade($upgrade_info['latest_version']);

        ob_end_clean();

        if (is_wp_error($result)) {
            return new IXR_Error(401, $result->get_error_message());
        }
        
        return array(
            'current_version'  => $upgrade_info['latest_version']->current,
        );
    }

    function get_updates($args) {
        $this->_escape($args);
        
        $username = $args[0];
        $password = $args[1];
        
		if($password != get_option('wpr_cron')) {
			if (!$user = $this->login($username, $password)) {
				return $this->error;
			}
        }
        
        $args[] = 1;
        
        return array(
            'core'      => $this->check_version($args, FALSE),
            'plugins'   => $this->get_plugin_instance()->get_upgradable_plugins(),
        );
    }
}

// CONTROLS 

$wpr_core = new Wpr_Core();

function wpr_plugin_get_list($args) {
    global $wpr_core;
    return $wpr_core->get_plugin_instance()->get_list($args);
}
        
function wpr_plugin_activate($args) {
    global $wpr_core;
    return $wpr_core->get_plugin_instance()->activate($args);
}
        
function wpr_plugin_deactivate($args) {
    global $wpr_core;
    return $wpr_core->get_plugin_instance()->deactivate($args);
}

function wpr_plugin_upgrade($args) {
    global $wpr_core;
    return $wpr_core->get_plugin_instance()->upgrade($args);
}

function wpr_plugin_upgrade_multiple($args) {
    global $wpr_core;
    return $wpr_core->get_plugin_instance()->upgrade_multiple($args);
}

function wpr_plugin_upgrade_all($args) {
    global $wpr_core;
    return $wpr_core->get_plugin_instance()->upgrade_all($args);
}

function wpr_plugin_delete($args) {
    global $wpr_core;
    return $wpr_core->get_plugin_instance()->delete($args);
}

function wpr_plugin_install($args) {
    global $wpr_core;
    return $wpr_core->get_plugin_instance()->install($args);
}

function wpr_plugin_upload_by_url($args) {
    global $wpr_core;
    return $wpr_core->get_plugin_instance()->upload_by_url($args);
}
       
function wpr_theme_get_list($args) {
    global $wpr_core;
    return $wpr_core->get_theme_instance()->get_list($args);
}

function wpr_theme_activate($args) {
    global $wpr_core;
    return $wpr_core->get_theme_instance()->activate($args);
}

function wpr_theme_delete($args) {
    global $wpr_core;
    return $wpr_core->get_theme_instance()->delete($args);
}

function wpr_theme_install($args) {
    global $wpr_core;
    return $wpr_core->get_theme_instance()->install($args);
}

function wpr_theme_upload_by_url($args) {
    global $wpr_core;
    return $wpr_core->get_theme_instance()->upload_by_url($args);
}
       
function wpr_wp_checkversion($args) {
    global $wpr_core;
    return $wpr_core->get_wp_instance()->check_version($args);
}

function wpr_wp_upgrade($args) {
    global $wpr_core;
    return $wpr_core->get_wp_instance()->upgrade($args);
}

function wpr_wp_get_updates($args) {
    global $wpr_core;
    return $wpr_core->get_wp_instance()->get_updates($args);
}

?>