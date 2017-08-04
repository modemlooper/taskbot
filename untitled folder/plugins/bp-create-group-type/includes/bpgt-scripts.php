<?php
// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

//Class to add custom scripts and styles
if( !class_exists( 'BPGTScriptsStyles' ) ) {
    class BPGTScriptsStyles{

        //Constructor
        function __construct() {
            $curr_url = $_SERVER['REQUEST_URI'];
            if( strpos($curr_url, 'bp-grp-types-options') !== false ) {
                add_action( 'admin_enqueue_scripts', array( $this, 'bpgt_admin_variables' ) );
            }
        }

        //Actions performed for enqueuing scripts and styles for admin panel
        function bpgt_admin_variables() {
            wp_enqueue_script('bpgt-js-admin', BPGT_PLUGIN_URL.'admin/assets/js/bpgt-admin.js', array('jquery'));
            wp_enqueue_style('bpgt-admin-css', BPGT_PLUGIN_URL.'admin/assets/css/bpgt-admin.css');

            //Font Awesome
            wp_enqueue_style('bpgt-fa-css', '//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css');
        }
    }
    new BPGTScriptsStyles();
}