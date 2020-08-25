<?php

class DT_Contacts_Access {
    private static $_instance = null;
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    } // End instance()

    public function __construct() {
//        add_action( 'after_setup_theme', [ $this, 'after_setup_theme' ], 100 );
//        add_action( 'p2p_init', [ $this, 'p2p_init' ] );

        //setup fields
        add_filter( 'dt_custom_fields_settings', [ $this, 'dt_custom_fields_settings' ], 20, 2 );

        //display tiles and fields
        add_action( 'dt_details_additional_section', [ $this, 'dt_details_additional_section' ], 20, 2 );
        add_filter( 'dt_details_additional_tiles', [ $this, 'dt_details_additional_tiles' ], 20, 2 );

        //list
        add_filter( "dt_user_list_filters", [ $this, "dt_user_list_filters" ], 20, 2 );

        //api
        add_action( "post_connection_removed", [ $this, "post_connection_removed" ], 20, 4 );
        add_action( "post_connection_added", [ $this, "post_connection_added" ], 20, 4 );

    }


    public function after_setup_theme(){}
    public function p2p_init(){}

    public function dt_custom_fields_settings( $fields, $post_type ){
        if ( $post_type === 'contacts' ){
            $fields['seeker_path'] = [
                'name'        => __( 'Seeker Path', 'disciple_tools' ),
                'description' => _x( "Set the status of your progression with the contact. These are the steps that happen in a specific order to help a contact move forward.", 'Seeker Path field description', 'disciple_tools' ),
                'type'        => 'key_select',
                'default'     => [
                    'none'        => [
                      "label" => __( 'Contact Attempt Needed', 'disciple_tools' ),
                      "description" => ''
                    ],
                    'attempted'   => [
                      "label" => __( 'Contact Attempted', 'disciple_tools' ),
                      "description" => ''
                    ],
                    'established' => [
                      "label" => __( 'Contact Established', 'disciple_tools' ),
                      "description" => ''
                    ],
                    'scheduled'   => [
                      "label" => __( 'First Meeting Scheduled', 'disciple_tools' ),
                      "description" => ''
                    ],
                    'met'         => [
                      "label" => __( 'First Meeting Complete', 'disciple_tools' ),
                      "description" => ''
                    ],
                    'ongoing'     => [
                      "label" => __( 'Ongoing Meetings', 'disciple_tools' ),
                      "description" => ''
                    ],
                    'coaching'    => [
                      "label" => __( 'Being Coached', 'disciple_tools' ),
                      "description" => ''
                    ],
                ],
                'section'     => 'status',
                'customizable' => 'add_only',
                'tile' => 'followup'
            ];
        }

        return $fields;
    }



    public function dt_details_additional_tiles( $sections, $post_type = "" ){
        if ( $post_type === "contacts"){
            $sections['followup'] =[
                "label" => "Follow Up"
            ];
        }
        return $sections;
    }

    public function dt_details_additional_section( $section, $post_type ){
    }

    private function update_contact_counts( $contact_id, $action = "added", $type = 'contacts' ){

    }
    public function post_connection_added( $post_type, $post_id, $post_key, $value ){
    }
    public function post_connection_removed( $post_type, $post_id, $post_key, $value ){
    }

    public static function dt_user_list_filters( $filters, $post_type ) {
        return $filters;
    }
}
