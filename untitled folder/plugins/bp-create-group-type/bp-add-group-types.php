<?php
/*
Plugin Name: BP Add Group Types
Plugin URI: https://wbcomdesigns.com/contact
Description: This plugin adds a new feature to buddypress, "group types" that allows site admin to add group types.
Version: 999999.0.1
Author: Wbcom Designs
Author URI: http://wbcomdesigns.com
License: GPLv2+
Text Domain: bp-grp-types
Domain Path: /languages
*/
if (!defined('ABSPATH')) exit; // Exit if accessed directly

//Load plugin textdomain.
add_action('init', 'bpgt_load_textdomain');
function bpgt_load_textdomain() {
    $domain = "bp-grp-types";
    $locale = apply_filters('plugin_locale', get_locale(), $domain);
    load_textdomain($domain, 'languages/'.$domain.'-'.$locale.'.mo');
    $var = load_plugin_textdomain($domain, false, plugin_basename(dirname(__FILE__)).'/languages');
}

//Constants used in the plugin
define('BPGT_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('BPGT_PLUGIN_URL', plugin_dir_url(__FILE__));

//Include needed files on init
add_action('init', 'bpgt_include_files');
add_action('admin_init', 'bpgt_include_files');
function bpgt_include_files() {
    $include_files = array(
        'includes/bpgt-scripts.php',
        'includes/bpgt-ajax.php',
        'includes/bpgt-functions.php',
        'admin/bpgt-admin.php',
    );
    foreach( $include_files  as $include_file ) {
        include $include_file;
    }
    }

//Plugin Activation
register_activation_hook( __FILE__, 'bpgt_plugin_activation' );
function bpgt_plugin_activation() {
    //Check if "Buddypress" plugin is active or not
    if (!in_array('buddypress/bp-loader.php', apply_filters('active_plugins', get_option('active_plugins')))) {
        //Buddypress Plugin is inactive, hence deactivate this plugin
        deactivate_plugins( plugin_basename( __FILE__ ) );
        wp_die( __( 'The <b>BP Add Group Types</b> plugin requires <b>Buddypress</b> plugin to be installed and active. Return to <a href="'.admin_url('plugins.php').'">Plugins</a>', 'bp-grp-types' ) );
    }
}

//Settings link for this plugin
add_filter( 'plugin_action_links_'.plugin_basename(__FILE__), 'bpgt_admin_page_link' );
function bpgt_admin_page_link( $links ) {
    $page_link = array('<a href="'.admin_url('admin.php?page=bp-grp-types-options').'">Group Types</a>');
    return array_merge( $links, $page_link );
}
