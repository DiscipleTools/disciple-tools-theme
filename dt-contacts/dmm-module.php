<?php

class DT_Contacts_DMM {
    private static $_instance = null;
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    } // End instance()

    public function __construct() {
        //setup fields
        add_action( 'p2p_init', [ $this, 'p2p_init' ] );
        add_filter( 'dt_custom_fields_settings', [ $this, 'dt_custom_fields_settings' ], 10, 2 );

         //display tiles and fields
        add_filter( 'dt_details_additional_tiles', [ $this, 'dt_details_additional_tiles' ], 10, 2 );

        //hooks
        add_action( "post_connection_removed", [ $this, "post_connection_removed" ], 10, 4 );
        add_action( "post_connection_added", [ $this, "post_connection_added" ], 10, 4 );

        //list
        add_filter( "dt_user_list_filters", [ $this, "dt_user_list_filters" ], 10, 2 );

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



            $fields["coaching"] = [
                "name" => __( "Coached", 'disciple_tools' ),
                "description" => _x( "Who is this contact coaching", 'Optional Documentation', 'disciple_tools' ),
                "type" => "connection",
                "post_type" => "contacts",
                "p2p_direction" => "to",
                "p2p_key" => "contacts_to_contacts",
                "tile" => "other"
            ];
            $fields['baptism_date'] = [
                'name'        => __( 'Baptism Date', 'disciple_tools' ),
                'description' => '',
                'type'        => 'date',
                'default'     => '',
                'tile'     => 'faith',
            ];

            $fields['baptism_generation'] = [
                'name'        => __( 'Baptism Generation', 'disciple_tools' ),
                'type'        => 'text',
                'default'     => '',
                'section'     => 'misc',
            ];
            $fields["coached_by"] = [
                "name" => __( "Coached by", 'disciple_tools' ),
                "description" => _x( "Who is coaching this contact", 'Optional Documentation', 'disciple_tools' ),
                "type" => "connection",
                "post_type" => "contacts",
                "p2p_direction" => "from",
                "p2p_key" => "contacts_to_contacts",
                "tile" => "other"
            ];
            $fields["coaching"] = [
                "name" => __( "Coached", 'disciple_tools' ),
                "description" => _x( "Who is this contact coaching", 'Optional Documentation', 'disciple_tools' ),
                "type" => "connection",
                "post_type" => "contacts",
                "p2p_direction" => "to",
                "p2p_key" => "contacts_to_contacts",
                "tile" => "other"
            ];
            $fields["baptized_by"] = [
                "name" => __( "Baptized by", 'disciple_tools' ),
                "description" => _x( "Who baptized this contact", 'Optional Documentation', 'disciple_tools' ),
                "type" => "connection",
                "post_type" => "contacts",
                "p2p_direction" => "from",
                "p2p_key" => "baptizer_to_baptized",
                'tile'     => 'faith'
            ];
            $fields["baptized"] = [
                "name" => __( "Baptized", 'disciple_tools' ),
                "description" => _x( "Who this contact has baptized", 'Optional Documentation', 'disciple_tools' ),
                "type" => "connection",
                "post_type" => "contacts",
                "p2p_direction" => "to",
                "p2p_key" => "baptizer_to_baptized",
                'tile'     => 'faith'
            ];
            $fields["people_groups"] = [
                "name" => __( 'People Groups', 'disciple_tools' ),
                'description' => _x( 'The people groups represented by this group.', 'Optional Documentation', 'disciple_tools' ),
                "type" => "connection",
                "post_type" => "peoplegroups",
                "p2p_direction" => "from",
                "p2p_key" => "contacts_to_peoplegroups",
                'tile'     => 'other',
                'icon' => get_template_directory_uri() . "/dt-assets/images/people-group.svg",
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
            ]
        );
        /**
         * Contact Baptism Field
         */
        p2p_register_connection_type(
            [
                'name'        => 'baptizer_to_baptized',
                'from'        => 'contacts',
                'to'          => 'contacts',
                'admin_box' => [
                    'show' => false,
                ],
                'title'       => [
                    'from' => __( 'Baptized by', 'disciple_tools' ),
                    'to'   => __( 'Baptized', 'disciple_tools' ),
                ]
            ]
        );
    }

    public function dt_details_additional_tiles( $tiles, $post_type = "" ){
        if ( $post_type === "contacts" ){
            $tiles["faith"] = [
                "label" => __( "Faith", 'disciple_tools' )
            ];
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
