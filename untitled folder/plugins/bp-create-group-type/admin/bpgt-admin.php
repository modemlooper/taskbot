<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

//Add admin page for price quote settings
if( !class_exists( 'BPGTAdmin' ) ) {
    class BPGTAdmin{

        //Constructor
        function __construct() {
            add_action( 'admin_menu', array( $this, 'bpgt_add_submenu_page' ) );
        }

        //Actions performed on loading admin_menu
        function bpgt_add_submenu_page() {
            $icon_url = BPGT_PLUGIN_URL.'admin/assets/images/add-type.png';
            add_submenu_page('bp-groups', __('BP Add Group Types Settings', 'bp-grp-types'), __('Group Types', 'bp-grp-types'), 'manage_options', 'bp-grp-types-options', array($this, 'bpgt_admin_options_page'), $icon_url, 56);
        }

        function bpgt_admin_options_page() {
            include 'bpgt-admin-options-page.php';
        }
    }
    new BPGTAdmin();
}