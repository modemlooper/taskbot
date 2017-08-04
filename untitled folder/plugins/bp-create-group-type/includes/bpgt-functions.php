<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

//Class to define custom functions needed
if( !class_exists( 'BPGTFunctions' ) ) {
    class BPGTFunctions{

        //Constructor
        function __construct() {
            add_action( 'bp_groups_register_group_types', array( $this, 'bpgt_register_group_types' ) );
        }

        //Actions performed for registering custom group types
        function bpgt_register_group_types() {
            $saved_group_types = get_option( 'bpgt_group_types' );
            $group_types = bp_groups_get_group_types();
            if( !empty( $saved_group_types ) ) {
                $saved_group_types = unserialize( $saved_group_types );
                foreach ($saved_group_types as $key => $saved_group_type) {
                    $slug = $saved_group_type['slug'];
                    $name = $saved_group_type['name'];
                    $desc = $saved_group_type['desc'];
                    if( !in_array( $slug, $group_types ) ) {
                        $temp = array(
                            'labels' => array(
                                'name' => $this->getPluralPrase( $name ),
                                'singular_name' => $name,
                            ),
                            'has_directory' => strtolower( $this->getPluralPrase( $name ) ),
                            'show_in_create_screen' => true,
                            'show_in_list' => true,
                            'description' => $desc,
                            'create_screen_checked' => true
                        );
                        bp_groups_register_group_type( $name, $temp );
                    }
                }
            }
        }

        //Get plural words

        /**
         * @return string
         */
        function getPluralPrase( $phrase, $value = 2 ) {
            $plural = '';
            if( $value > 1 ) {
                for( $i = 0; $i < strlen( $phrase ); $i++ ) {
                    if( $i == strlen( $phrase ) - 1 ) {
                        $plural.=($phrase[$i]=='y')? 'ies':(($phrase[$i]=='s'|| $phrase[$i]=='x' || $phrase[$i]=='z' || $phrase[$i]=='ch' || $phrase[$i]=='sh')? $phrase[$i].'es' :$phrase[$i].'s');
                    } else {
                        $plural .= $phrase[ $i ];
                    }
                }
                return $plural;
            }
            return $phrase;
        }
    }
    new BPGTFunctions();
}