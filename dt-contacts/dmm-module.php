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
        add_action( 'rest_api_init', [ $this, 'add_api_routes' ] );

         //display tiles and fields
        add_filter( 'dt_details_additional_tiles', [ $this, 'dt_details_additional_tiles' ], 10, 2 );
        add_action( 'dt_details_additional_section', [ $this, 'dt_details_additional_section' ], 20, 2 );

        //hooks
        add_action( "post_connection_removed", [ $this, "post_connection_removed" ], 10, 4 );
        add_action( "post_connection_added", [ $this, "post_connection_added" ], 10, 4 );

        //list
        add_filter( "dt_user_list_filters", [ $this, "dt_user_list_filters" ], 10, 2 );

        add_action( "dt_comment_action_quick_action", [ $this, "dt_comment_action_quick_action" ], 10, 1 );

        add_action( 'wp_enqueue_scripts', [ $this, 'scripts' ], 99 );

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
                "show_in_table" => 20
            ];

            $fields["subassigned"] = [
                "name" => __( "Sub-assigned to", 'disciple_tools' ),
                "description" => __( "Contact or User assisting the Assigned To user to follow up with the contact.", 'disciple_tools' ),
                "type" => "connection",
                "post_type" => "contacts",
                "p2p_direction" => "to",
                "p2p_key" => "contacts_to_subassigned",
//                "tile" => "status",
                'icon' => get_template_directory_uri() . "/dt-assets/images/subassigned.svg",
            ];

//            @todo move to group declaration
            $fields["groups"] = [
                "name" => __( "Groups", 'disciple_tools' ),
                "description" => _x( "Groups this contact is a member of.", 'Optional Documentation', 'disciple_tools' ),
                "type" => "connection",
                "post_type" => "groups",
                "p2p_direction" => "from",
                "p2p_key" => "contacts_to_groups",
                "tile" => "other",
                'icon' => get_template_directory_uri() . "/dt-assets/images/group-type.svg",
                'create-icon' => get_template_directory_uri() . "/dt-assets/images/add-group.svg",
                "show_in_table" => 35
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
                'tile'     => 'details',
                'icon' => get_template_directory_uri() . "/dt-assets/images/people-group.svg",
            ];

            $fields['quick_button_no_answer'] = [
                'name'        => __( 'No Answer', 'disciple_tools' ),
                'description' => '',
                'type'        => 'number',
                'default'     => 0,
                'section'     => 'quick_buttons',
                'icon'        => get_template_directory_uri() . "/dt-assets/images/no-answer.svg",
            ];
            $fields['quick_button_contact_established'] = [
                'name'        => __( 'Contact Established', 'disciple_tools' ),
                'description' => '',
                'type'        => 'number',
                'default'     => 0,
                'section'     => 'quick_buttons',
                'icon'        => get_template_directory_uri() . "/dt-assets/images/successful-conversation.svg",
            ];
            $fields['quick_button_meeting_scheduled'] = [
                'name'        => __( 'Meeting Scheduled', 'disciple_tools' ),
                'description' => '',
                'type'        => 'number',
                'default'     => 0,
                'section'     => 'quick_buttons',
                'icon'        => get_template_directory_uri() . "/dt-assets/images/meeting-scheduled.svg",
            ];
            $fields['quick_button_meeting_complete'] = [
                'name'        => __( 'Meeting Complete', 'disciple_tools' ),
                'description' => '',
                'type'        => 'number',
                'default'     => 0,
                'section'     => 'quick_buttons',
                'icon'        => get_template_directory_uri() . "/dt-assets/images/meeting-complete.svg",
            ];
            $fields['quick_button_no_show'] = [
                'name'        => __( 'Meeting No-show', 'disciple_tools' ),
                'description' => '',
                'type'        => 'number',
                'default'     => 0,
                'section'     => 'quick_buttons',
                'icon'        => get_template_directory_uri() . "/dt-assets/images/no-show.svg",
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
        /**
         * Contact Sub-assigned to
         */
        p2p_register_connection_type(
            [
                'name'        => 'contacts_to_subassigned',
                'from'        => 'contacts',
                'to'          => 'contacts',
                'admin_box' => [
                    'show' => false,
                ],
                'title'       => [
                    'from' => __( 'Sub-assigned by', 'disciple_tools' ),
                    'to'   => __( 'Sub-assigned', 'disciple_tools' ),
                ]
            ]
        );
        /**
         * Contact People Groups
         */
        p2p_register_connection_type(
            [
                'name'        => 'contacts_to_peoplegroups',
                'from'        => 'contacts',
                'to'          => 'peoplegroups',
                'title'       => [
                    'from' => __( 'People Groups', 'disciple_tools' ),
                    'to'   => __( 'Contacts', 'disciple_tools' ),
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
        if ( $post_type === "contacts" ){
            if ( $post_key === "subassigned" ){
                $user_id = get_post_meta( $value, "corresponds_to_user", true );
                if ( $user_id ){
                    DT_Posts::add_shared( $post_type, $post_id, $user_id, null, false, false, false );
                    Disciple_Tools_Notifications::insert_notification_for_subassigned( $user_id, $post_id );
                }
            }
            if ( $post_key === 'baptized' ){
                Disciple_Tools_Counter_Baptism::reset_baptism_generations_on_contact_tree( $value );
                $milestones = get_post_meta( $post_id, 'milestones' );
                if ( empty( $milestones ) || !in_array( "milestone_baptizing", $milestones ) ){
                    add_post_meta( $post_id, "milestones", "milestone_baptizing" );
                }
                Disciple_Tools_Counter_Baptism::reset_baptism_generations_on_contact_tree( $post_id );
            }
            if ( $post_key === 'baptized_by' ){
                $milestones = get_post_meta( $post_id, 'milestones' );
                if ( empty( $milestones ) || !in_array( "milestone_baptized", $milestones ) ){
                    add_post_meta( $post_id, "milestones", "milestone_baptized" );
                }
                Disciple_Tools_Counter_Baptism::reset_baptism_generations_on_contact_tree( $post_id );
            }
        }
    }
    public function post_connection_removed( $post_type, $post_id, $post_key, $value ){
        if ( $post_type === "contacts" ){
            if ( $post_key === "baptized_by" ){
                Disciple_Tools_Counter_Baptism::reset_baptism_generations_on_contact_tree( $post_id );
            }
            if ( $post_key === "baptized" ){
                Disciple_Tools_Counter_Baptism::reset_baptism_generations_on_contact_tree( $value );
            }
        }
    }

    public static function dt_user_list_filters( $filters, $post_type ) {
//        if ( $post_type === 'contacts' ) {
//
//        }
        return $filters;
    }

    public function dt_details_additional_section( $section, $post_type ) {
        if ( $post_type === "contacts" ) {
            get_template_part( 'dt-assets/parts/modals/modal', 'revert' );
        }
    }
    public function dt_comment_action_quick_action( $post_type ){
        if ( $post_type === "contacts" ){
            $contact = DT_Posts::get_post( "contacts", get_the_ID() );
            $contact_fields = DT_Posts::get_post_field_settings( $post_type );
            ?>

            <ul class="dropdown menu" data-dropdown-menu style="display: inline-block">
                <li style="border-radius: 5px">
                    <a class="button menu-white-dropdown-arrow"
                       style="background-color: #00897B; color: white;">
                        <?php esc_html_e( "Quick Actions", 'disciple_tools' ) ?></a>
                    <ul class="menu" style="width: max-content">
                        <?php
                        foreach ( $contact_fields as $field => $val ) {
                            if ( strpos( $field, "quick_button" ) === 0 ) {
                                $current_value = 0;
                                if ( isset( $contact[$field] ) ) {
                                    $current_value = $contact[$field];
                                } ?>
                                <li class="quick-action-menu" data-id="<?php echo esc_attr( $field ) ?>">
                                    <a>
                                        <img src="<?php echo esc_url( $val['icon'] ); ?>">
                                        <?php echo esc_html( $val["name"] ); ?>
                                        (<span class="<?php echo esc_attr( $field ) ?>"><?php echo esc_html( $current_value ); ?></span>)
                                    </a>
                                </li>
                                <?php
                            }
                        }
                        ?>
                    </ul>
                </li>
            </ul>
            <button class="help-button" data-section="quick-action-help-text">
                <img class="help-icon"
                     src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/help.svg' ) ?>"/>
            </button>
            <?php
        }
    }

    public function scripts(){
        if ( is_singular( "contacts" ) ){
            wp_enqueue_script( 'dt_contacts_dmm', get_template_directory_uri() . '/dt-contacts/contacts_dmm.js', [
                'jquery',
            ], filemtime( get_theme_file_path() . '/dt-contacts/contacts_dmm.js' ), true );
        }
    }
    public function add_api_routes() {
        $namespace = "dt-posts/v2";
        register_rest_route(
            $namespace, '/contacts/(?P<id>\d+)/revert/(?P<activity_id>\d+)', [
                "methods"  => "GET",
                "callback" => [ $this, 'revert_activity' ],
            ]
        );
    }
    public function revert_activity( WP_REST_Request $request ) {
        $params = $request->get_params();
        if ( isset( $params['id'] ) && isset( $params["activity_id"] ) ) {
            $contact_id = $params['id'];
            $activity_id = $params["activity_id"];
            if ( !DT_Posts::can_update( 'contacts', $contact_id ) ) {
                return new WP_Error( __FUNCTION__, "You do not have permission for this", [ 'status' => 403 ] );
            }
            $activity = DT_Posts::get_post_single_activity( 'contacts', $contact_id, $activity_id );
            if ( empty( $activity->old_value ) ){
                if ( strpos( $activity->meta_key, "quick_button_" ) !== false ){
                    $activity->old_value = 0;
                }
            }
            update_post_meta( $contact_id, $activity->meta_key, $activity->old_value ?? "" );
            return DT_Posts::get_post( "contacts", $contact_id );
        } else {
            return new WP_Error( "get_activity", "Missing a valid contact id or activity id", [ 'status' => 400 ] );
        }
    }

}
