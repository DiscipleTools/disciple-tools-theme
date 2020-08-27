<?php

class DT_Contacts_Base {
    private static $_instance = null;
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    } // End instance()

    public function __construct() {
        add_action( 'after_setup_theme', [ $this, 'after_setup_theme' ], 100 );
        add_action( 'p2p_init', [ $this, 'p2p_init' ] );
        add_filter( 'dt_details_additional_tiles', [ $this, 'dt_details_additional_tiles' ], 10, 2 );

        add_filter( 'dt_custom_fields_settings', [ $this, 'dt_custom_fields_settings' ], 10, 2 );
        add_action( "post_connection_removed", [ $this, "post_connection_removed" ], 10, 4 );
        add_action( "post_connection_added", [ $this, "post_connection_added" ], 10, 4 );
        add_filter( "dt_user_list_filters", [ $this, "dt_user_list_filters" ], 10, 2 );

    }


    public function after_setup_theme(){
        if ( class_exists( 'Disciple_Tools_Post_Type_Template' )) {
            new Disciple_Tools_Post_Type_Template( "contacts", 'Contact', 'Contacts' );
        }
    }

    public function dt_custom_fields_settings( $fields, $post_type ){
        if ( $post_type === 'contacts' ){
            $fields["milestones"] = [
                "name"    => __( 'Faith Milestones', 'disciple_tools' ),
                "description" => _x( 'Assign which milestones the contact has reached in their faith journey. These are points in a contact’s spiritual journey worth celebrating but can happen in any order.', 'Optional Documentation', 'disciple_tools' ),
                "type"    => "multi_select",
                "default" => [
                    "milestone_has_bible"     => [
                        "label" => __( 'Has Bible', 'disciple_tools' ),
                        "description" => ''
                    ],
                    "milestone_reading_bible" => [
                        "label" => __( 'Reading Bible', 'disciple_tools' ),
                        "description" => ''
                    ],
                    "milestone_belief"        => [
                        "label" => __( 'States Belief', 'disciple_tools' ),
                        "description" => ''
                    ],
                    "milestone_can_share"     => [
                        "label" => __( 'Can Share Gospel/Testimony', 'disciple_tools' ),
                        "description" => ''
                    ],
                    "milestone_sharing"       => [
                        "label" => __( 'Sharing Gospel/Testimony', 'disciple_tools' ),
                        "description" => ''
                    ],
                    "milestone_baptized"      => [
                        "label" => __( 'Baptized', 'disciple_tools' ),
                        "description" => ''
                    ],
                    "milestone_baptizing"     => [
                        "label" => __( 'Baptizing', 'disciple_tools' ),
                        "description" => ''
                    ],
                    "milestone_in_group"      => [
                        "label" => __( 'In Church/Group', 'disciple_tools' ),
                        "description" => ''
                    ],
                    "milestone_planting"      => [
                        "label" => __( 'Starting Churches', 'disciple_tools' ),
                        "description" => ''
                    ],
                ],
                "customizable" => "add_only",
                "tile" => "faith",
                "show_in_table" => true
            ];
            $fields['gender'] = [
                'name'        => __( 'Gender', 'disciple_tools' ),
                'type'        => 'key_select',
                'default'     => [
                    'not-set' => [ "label" => '' ],
                    'male'    => [ "label" => __( 'Male', 'disciple_tools' ) ],
                    'female'  => [ "label" => __( 'Female', 'disciple_tools' ) ],
                ],
                'tile'     => 'details',
                "in_create_form" => true,
            ];
            $fields['age'] = [
                'name'        => __( 'Age', 'disciple_tools' ),
                'type'        => 'key_select',
                'default'     => [
                    'not-set' => [ "label" => '' ],
                    '<19'     => [ "label" => __( 'Under 18 years old', 'disciple_tools' ) ],
                    '<26'     => [ "label" => __( '18-25 years old', 'disciple_tools' ) ],
                    '<41'     => [ "label" => __( '26-40 years old', 'disciple_tools' ) ],
                    '>41'     => [ "label" => __( 'Over 40 years old', 'disciple_tools' ) ],
                ],
                'tile'     => 'details',
                "in_create_form" => true,
            ];
            $fields["contact_phone"] = [
                "name" => __( 'Phone', 'disciple_tools' ),
                "icon" => get_template_directory_uri() . "/dt-assets/images/phone.svg",
                "type" => "communication_channel",
                "tile" => "details",
                "in_create_form" => true,
            ];
            $fields["text"] = [
                "name" => __( 'Text', 'disciple_tools' ),
                "type" => "text",
                "tile" => "other",
                "in_create_form" => true,
            ];
            $fields["number"] = [
                "name" => __( 'Number', 'disciple_tools' ),
                "type" => "number",
                "tile" => "other",
                "in_create_form" => true,
            ];
            $fields['location_grid'] = [
                'name'        => __( 'Locations', 'disciple_tools' ),
                'description' => _x( 'The general location where this contact is located.', 'Optional Documentation', 'disciple_tools' ),
                'type'        => 'location',
                'default'     => [],
                "in_create_form" => true,
            ];
            $fields['location_grid_meta'] = [
                'name'        => 'Location Grid Meta', //system string does not need translation
                'type'        => 'location_meta',
                'default'     => [],
                'hidden' => true
            ];
            $fields["coaching"] = [
                "name" => __( "Coached", 'disciple_tools' ),
                "description" => _x( "Who is this contact coaching", 'Optional Documentation', 'disciple_tools' ),
                "type" => "connection",
                "post_type" => "contacts",
                "p2p_direction" => "to",
                "p2p_key" => "contacts_to_contacts",
                "in_create_form" => true,
            ];

        }

        return $fields;
    }

    public function p2p_init(){
        /**
         * Contact Coaching field
         */
        p2p_register_connection_type(
            [
                'name'        => 'contacts_to_contacts',
                'from'        => 'contacts',
                'to'          => 'contacts',
                'admin_box' => [
                    'show' => false,
                ]
            ]
        );
    }

    public function dt_details_additional_tiles( $tiles, $post_type = "" ){
        if ( $post_type === "contacts" ){
            $tiles["faith"] = [
                "label" => __( "Faith", 'disciple_tools' )
            ];
            $tiles["other"] = [ "label" => __( "Faith", 'disciple_tools' ) ];
        }
        return $tiles;
    }


    private function update_contact_counts( $contact_id, $action = "added", $type = 'contacts' ){

    }
    public function post_connection_added( $post_type, $post_id, $post_key, $value ){
    }
    public function post_connection_removed( $post_type, $post_id, $post_key, $value ){
    }

    public static function dt_user_list_filters( $filters, $post_type ) {
        if ( $post_type === 'contacts' ) {
            $filters["tabs"][] = [
                "key" => "all_contacts",
                "label" => _x( "All", 'List Filters', 'disciple_tools' ),
                "order" => 10
            ];
            // add assigned to me filters
            $filters["filters"][] = [
                'ID' => 'all_contacts',
                'tab' => 'all_contacts',
                'name' => _x( "All", 'List Filters', 'disciple_tools' ),
                'query' => [],
            ];
        }
        return $filters;
    }
}
