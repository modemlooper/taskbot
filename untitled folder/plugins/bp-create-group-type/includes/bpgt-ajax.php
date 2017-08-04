<?php
// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

//Class to serve AJAX Calls
if( !class_exists( 'BPGTAjax' ) ) {
    class BPGTAjax{

        //Constructor
        function __construct() {
            //Add BP Group Types
            add_action( 'wp_ajax_bpgt_add_group_type', array( $this, 'bpgt_add_group_type' ) );
            add_action( 'wp_ajax_nopriv_bpgt_add_group_type', array( $this, 'bpgt_add_group_type' ) );

            //Delete BP Group Types
            add_action( 'wp_ajax_bpgt_delete_group_type', array( $this, 'bpgt_delete_group_type' ) );
            add_action( 'wp_ajax_nopriv_bpgt_delete_group_type', array( $this, 'bpgt_delete_group_type' ) );

            //Search BP Group Types
            add_action( 'wp_ajax_bpgt_search_group_type', array( $this, 'bpgt_search_group_type' ) );
            add_action( 'wp_ajax_nopriv_bpgt_search_group_type', array( $this, 'bpgt_search_group_type' ) );

            //Update BP Group Types
            add_action( 'wp_ajax_bpgt_update_group_type', array( $this, 'bpgt_update_group_type' ) );
            add_action( 'wp_ajax_nopriv_bpgt_update_group_type', array( $this, 'bpgt_update_group_type' ) );
        }

        //Actions performed to add group types
        function bpgt_add_group_type() {
            if( isset( $_POST['action'] ) && $_POST['action'] === 'bpgt_add_group_type' ) {
                $name = $_POST['name'];

                $group_type = array(
                    'name' => $name,
                    'slug' => $_POST['slug'] == '' ? str_replace( '', '-', strtolower( $name ) ) : $_POST['slug'],
                    'desc' => $_POST['desc']
                );

                $group_types = get_option( 'bpgt_group_types' );
                if( $group_types == '' ) {
                    $group_types[] = $group_type;
                    update_option( 'bpgt_group_types', serialize( $group_types ) );
                } else {
                    $group_types = unserialize( $group_types );
                    $group_types[] = $group_type;
                    update_option( 'bpgt_group_types', serialize( $group_types ) );
                }
                
                $all_bp_group_types = $_POST['all_bp_group_types'];
                if( $all_bp_group_types != '' ){
                	$all_bp_group_types = str_replace("\\", "", $all_bp_group_types);
                	$all_bp_group_types = json_decode( $all_bp_group_types, true );
                	$all_bp_group_types[] = $_POST['slug'];
                } else {
                	$all_bp_group_types[] = $_POST['slug'];
                }
                $arr = array(
                	'msg' => 'group-type-added',
                	'all_bp_group_types' => json_encode( $all_bp_group_types ),
                );
                echo json_encode( $arr );
                die;
            }
        }

        //Actions performed to delete group types
        function bpgt_delete_group_type() {
            if (isset($_POST['action']) && $_POST['action'] === 'bpgt_delete_group_type') {
                $slug = $_POST['slug'];
                $group_types = unserialize(get_option('bpgt_group_types'));
				
                foreach ($group_types as $key => $group_type) {
                    if ($slug == $group_type['slug']) {
                        $key_to_unset = $key;
                        break;
                    }
                }

                unset($group_types[$key_to_unset]);
				
                if (empty($group_types)) {
                    delete_option('bpgt_group_types');
                } else {
                    update_option('bpgt_group_types', serialize($group_types));
                }
                echo 'group-type-deleted';
                die;
            }
        }

        //Actions performed to search group types
        function bpgt_search_group_type() {
            if (isset($_POST['action']) && $_POST['action'] === 'bpgt_search_group_type') {
                $search_txt = $_POST['search_txt'];
                $result = array();
                $group_types = get_option('bpgt_group_types');
                if ($group_types != '') {
                    $group_types = unserialize($group_types);
                    foreach ($group_types as $key => $group_type) {
                        $name_pos = $slug_pos = $desc_pos = false;
                        $name_pos = strpos($group_type['name'], $search_txt);
                        $slug_pos = strpos($group_type['slug'], $search_txt);
                        $desc_pos = strpos($group_type['desc'], $search_txt);
                        if ($name_pos !== false || $slug_pos !== false || $desc_pos !== false) {
                            $result['group_types'][] = $group_type;
                        }
                    }
                    $result['found'] = 'yes';
                    $result['msg'] = 'Group Types Found!';
                    //If no grp types matched the search criteria
                    if (empty($result['group_types'])) {
                        $result = array(
                            'found' => 'no',
                            'msg' => __('No Group Types Found', ''),
                        );
                    }
                } else {
                    $result = array(
                        'found' => 'no',
                        'msg' => __('No Group Types Found', ''),
                    );
                }

                echo json_encode($result);
                die;
            }
        }

        //Actions performed to update group types
        function bpgt_update_group_type() {
            if (isset($_POST['action']) && $_POST['action'] === 'bpgt_update_group_type') {
                $new_name = $_POST['new_name'];
                $old_slug = $_POST['old_slug'];
				
                $group_types = unserialize(get_option('bpgt_group_types'));
                foreach ($group_types as $key => $group_type) {
                    if ($old_slug == $group_type['slug']) {
                        $key_to_update = $key;
                        break;
                    }
                }

                $new_group_type = array(
                    'name' => $new_name,
                    'slug' => $_POST['new_slug'],
                    'desc' => $_POST['new_desc']
                );

                $group_types[$key_to_update] = $new_group_type;
				
                delete_option('bpgt_group_types');
                update_option('bpgt_group_types', serialize($group_types));
                echo 'group-type-updated';
                die;
            }
        }
    }
    new BPGTAjax();
}