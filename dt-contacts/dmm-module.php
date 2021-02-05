<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly.

class DT_Contacts_DMM  extends DT_Module_Base {
    public $post_type = "contacts";
    public $module = "dmm_module";

    private static $_instance = null;
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    } // End instance()

    public function __construct(){
        parent::__construct();
        if ( !self::check_enabled_and_prerequisites() ){
            return;
        }
        //setup fields
        add_action( 'p2p_init', [ $this, 'p2p_init' ] );
        add_filter( 'dt_custom_fields_settings', [ $this, 'dt_custom_fields_settings' ], 10, 2 );
        add_action( 'rest_api_init', [ $this, 'add_api_routes' ] );

         //display tiles and fields
        add_filter( 'dt_details_additional_tiles', [ $this, 'dt_details_additional_tiles' ], 10, 2 );
        add_action( 'dt_details_additional_section', [ $this, 'dt_details_additional_section' ], 20, 2 );
        add_action( 'dt_record_footer', [ $this, 'dt_record_footer' ], 10, 2 );

        //hooks
        add_action( "post_connection_removed", [ $this, "post_connection_removed" ], 10, 4 );
        add_action( "post_connection_added", [ $this, "post_connection_added" ], 10, 4 );
        add_filter( "dt_post_create_fields", [ $this, "dt_post_create_fields" ], 10, 2 );

        //list
        add_filter( "dt_user_list_filters", [ $this, "dt_user_list_filters" ], 10, 2 );
        add_filter( "dt_search_viewable_posts_query", [ $this, "dt_search_viewable_posts_query" ], 10, 1 );
        add_action( "dt_comment_action_quick_action", [ $this, "dt_comment_action_quick_action" ], 10, 1 );

        add_action( 'wp_enqueue_scripts', [ $this, 'scripts' ], 99 );

    }

    public function dt_custom_fields_settings( $fields, $post_type ){
        $declared_fields = $fields;
        if ( $post_type === 'contacts' ){
            $fields["type"]["default"]["placeholder"] = [
                "label" => __( 'Connection', 'disciple_tools' ),
                "color" => "#FF9800",
                "description" => __( 'Connected to a contact, or generational fruit', 'disciple_tools' ),
                "icon" => get_template_directory_uri() . "/dt-assets/images/network.svg",
                "order" => 40,
                "visibility" => __( "Only me", 'disciple_tools' ),
            ];
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
                "show_in_table" => 20,
                "icon" => get_template_directory_uri() . "/dt-assets/images/bible.svg",
            ];
            $fields["faith_status"] =[
                "name" => __( 'Faith Status', 'disciple_tools' ),
                'type' => "key_select",
                "default" => [
                    "seeker"     => [
                        "label" => __( 'Seeker', 'disciple_tools' ),
                    ],
                    "believer"     => [
                        "label" => __( 'Believer', 'disciple_tools' ),
                    ],
                    "leader"     => [
                        "label" => __( 'Leader', 'disciple_tools' ),
                    ],
                ],
                'tile' => "status",
                'icon' => get_template_directory_uri() . "/dt-assets/images/cross.svg",
                'in_create_form' => true
            ];
            $fields["subassigned"] = [
                "name" => __( "Sub-assigned to", 'disciple_tools' ),
                "description" => __( "Contact or User assisting the Assigned To user to follow up with the contact.", 'disciple_tools' ),
                "type" => "connection",
                "post_type" => "contacts",
                "p2p_direction" => "to",
                "p2p_key" => "contacts_to_subassigned",
                "tile" => "status",
                "custom_display" => false,
                'icon' => get_template_directory_uri() . "/dt-assets/images/subassigned.svg",
            ];


            $fields["coaching"] = [
                "name" => __( "Is Coaching", 'disciple_tools' ),
                "description" => _x( "Who is this contact coaching", 'Optional Documentation', 'disciple_tools' ),
                "type" => "connection",
                "post_type" => "contacts",
                "p2p_direction" => "to",
                "p2p_key" => "contacts_to_contacts",
                "tile" => "other",
                "icon" => get_template_directory_uri() . '/dt-assets/images/coaching.svg',
            ];
            $fields['baptism_date'] = [
                'name' => __( 'Baptism Date', 'disciple_tools' ),
                'type' => 'date',
                'icon' => get_template_directory_uri() . '/dt-assets/images/calendar.svg',
                'tile' => 'details',
            ];

            $fields['baptism_generation'] = [
                'name'        => __( 'Baptism Generation', 'disciple_tools' ),
                'type'        => 'number',
                'default'     => '',
            ];
            $fields["coached_by"] = [
                "name" => __( "Coached by", 'disciple_tools' ),
                "description" => _x( "Who is coaching this contact", 'Optional Documentation', 'disciple_tools' ),
                "type" => "connection",
                "post_type" => "contacts",
                "p2p_direction" => "from",
                "p2p_key" => "contacts_to_contacts",
                "tile" => "status",
                "icon" => get_template_directory_uri() . '/dt-assets/images/coach.svg',
            ];
            $fields["baptized_by"] = [
                "name" => __( "Baptized by", 'disciple_tools' ),
                "description" => _x( "Who baptized this contact", 'Optional Documentation', 'disciple_tools' ),
                "type" => "connection",
                "post_type" => "contacts",
                "p2p_direction" => "from",
                "p2p_key" => "baptizer_to_baptized",
                'tile'     => 'faith',
                "icon" => get_template_directory_uri() . '/dt-assets/images/baptism.svg',
            ];
            $fields["baptized"] = [
                "name" => __( "Baptized", 'disciple_tools' ),
                "description" => _x( "Who this contact has baptized", 'Optional Documentation', 'disciple_tools' ),
                "type" => "connection",
                "post_type" => "contacts",
                "p2p_direction" => "to",
                "p2p_key" => "baptizer_to_baptized",
                'tile'     => 'faith',
                "icon" => get_template_directory_uri() . '/dt-assets/images/child.svg',
            ];
            $fields["people_groups"] = [
                "name" => __( 'People Groups', 'disciple_tools' ),
                'description' => _x( 'The people groups represented by this contact.', 'Optional Documentation', 'disciple_tools' ),
                "type" => "connection",
                "post_type" => "peoplegroups",
                "p2p_direction" => "from",
                "p2p_key" => "contacts_to_peoplegroups",
                'tile'     => 'details',
                'icon' => get_template_directory_uri() . "/dt-assets/images/people-group.svg",
            ];

            $fields['quick_button_no_answer'] = [
                'name'        => __( 'No Answer', 'disciple_tools' ),
                'type'        => 'number',
                'default'     => 0,
                'section'     => 'quick_buttons',
                'icon'        => get_template_directory_uri() . "/dt-assets/images/no-answer.svg",
                "customizable" => false
            ];
            $fields['quick_button_contact_established'] = [
                'name'        => __( 'Contact Established', 'disciple_tools' ),
                'description' => '',
                'type'        => 'number',
                'default'     => 0,
                'section'     => 'quick_buttons',
                'icon'        => get_template_directory_uri() . "/dt-assets/images/successful-conversation.svg",
                "customizable" => false
            ];
            $fields['quick_button_meeting_scheduled'] = [
                'name'        => __( 'Meeting Scheduled', 'disciple_tools' ),
                'description' => '',
                'type'        => 'number',
                'default'     => 0,
                'section'     => 'quick_buttons',
                'icon'        => get_template_directory_uri() . "/dt-assets/images/meeting-scheduled.svg",
                "customizable" => false
            ];
            $fields['quick_button_meeting_complete'] = [
                'name'        => __( 'Meeting Complete', 'disciple_tools' ),
                'description' => '',
                'type'        => 'number',
                'default'     => 0,
                'section'     => 'quick_buttons',
                'icon'        => get_template_directory_uri() . "/dt-assets/images/meeting-complete.svg",
                "customizable" => false
            ];
            $fields['quick_button_no_show'] = [
                'name'        => __( 'Meeting No-show', 'disciple_tools' ),
                'description' => '',
                'type'        => 'number',
                'default'     => 0,
                'section'     => 'quick_buttons',
                'icon'        => get_template_directory_uri() . "/dt-assets/images/no-show.svg",
                "customizable" => false
            ];



        }
        return dt_array_merge_recursive_distinct( $declared_fields, $fields );
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
            if ( isset( $tiles["status"] ) && !isset( $tiles["status"]["order"] ) ){
                $tiles["status"]["order"] = [ "subassigned", "faith_status", "coached_by" ];
            }
        }
        return $tiles;
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
            if ( $post_key === "coached_by" ){
                $user_id = get_post_meta( $value, "corresponds_to_user", true );
                if ( $user_id ){
                    DT_Posts::add_shared( $post_type, $post_id, $user_id, null, false, false, true );
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

    //Add, remove or modify fields before the fields are processed in post create
    public function dt_post_create_fields( $fields, $post_type ){
        if ( $post_type === "contacts" ){
            if ( !isset( $fields["type"] ) ){
                $fields["type"] = "placeholder";
            }
            //mark a new user contact as being coached be the user who added the new user.
            if ( $fields["type"] === "user" ){
                $current_user_contact = Disciple_Tools_Users::get_contact_for_user( get_current_user_id() );
                if ( $current_user_contact && !is_wp_error( $current_user_contact ) ){
                    $fields["coached_by"] = [ "values" => [ [ "value" => $current_user_contact ] ] ];
                }
            }
        }
        return $fields;
    }


    public static function dt_user_list_filters( $filters, $post_type ) {
        if ( $post_type === 'contacts' ) {

            global $wpdb;
            $user_post_id = Disciple_Tools_Users::get_contact_for_user( get_current_user_id() ) ?? 0;
            $coached_by_me = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(p2p_to) FROM $wpdb->p2p WHERE p2p_to = %s AND p2p_type = 'contacts_to_contacts'", esc_sql( $user_post_id ) ) );

            $post_label_plural = DT_Posts::get_post_settings( $post_type )['label_plural'];
            $shared_by_type_counts = DT_Posts_Metrics::get_shared_with_meta_field_counts( "contacts", 'type' );
            $filters["filters"][] = [
                'ID' => 'placeholder',
                'tab' => 'default',
                'name' => sprintf( _x( "Connected %s", 'Personal records', 'disciple_tools' ), $post_label_plural ),
                'query' => [
                    'type' => [ 'placeholder' ],
                    "overall_status" => [ "-closed" ],
                    'sort' => 'name'
                ],
                "count" => $shared_by_type_counts['keys']['placeholder'] ?? 0,
            ];
            $filters["filters"][] = [
                'ID' => 'my_coached',
                'visible' => "1",
                'type' => 'default',
                'tab' => 'default',
                'name' => __( 'Coached by me', 'disciple_tools' ),
                'count' => $coached_by_me,
                'query' => [
                    'coached_by' => [ 'me' ],
                    "overall_status" => [ "-closed" ],
                    'sort' => 'seeker_path',
                ],
                'labels' => [
                    [
                        'id' => 'my_coached',
                        'name' => __( 'Coached by me', 'disciple_tools' ),
                        'field' => 'coached_by',
                    ],
                ],
            ];
        }

        //translation for default fields
        foreach ( $filters["filters"] as $index => $filter ) {
            if ( $filter["name"] === 'Coached by me' ) {
                $filters["filters"][$index]["name"] = __( 'Coached by me', 'disciple_tools' );
                $filters["filters"][$index]['labels'][0]['name'] = __( 'Coached by me', 'disciple_tools' );
            }
        }
        return $filters;
    }

    public function dt_details_additional_section( $section, $post_type ) {}

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
                    <ul class="menu is-dropdown-submenu" style="width: max-content">
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


    public function dt_record_footer( $post_type, $post_id ){
        if ( $post_type !== "contacts" ){
            return;
        }
        get_template_part( 'dt-assets/parts/modals/modal', 'revert' );
        $field_settings = DT_Posts::get_post_field_settings( $post_type );
        $post = DT_Posts::get_post( "contacts", $post_id );
        ?>
        <div class="reveal" id="baptism-modal" data-reveal data-close-on-click="false">

            <h3><?php echo esc_html( $field_settings["baptized"]["name"] )?></h3>
            <p><?php esc_html_e( "Who was this contact baptized by and when?", 'disciple_tools' )?></p>

            <div>
                <div class="section-subheader">
                    <?php echo esc_html( $field_settings["baptized_by"]["name"] )?>
                </div>
                <div class="modal_baptized_by details">
                    <var id="modal_baptized_by-result-container" class="result-container modal_baptized_by-result-container"></var>
                    <div id="modal_baptized_by_t" name="form-modal_baptized_by" class="scrollable-typeahead typeahead-margin-when-active">
                        <div class="typeahead__container">
                            <div class="typeahead__field">
                                <span class="typeahead__query">
                                    <input class="js-typeahead-modal_baptized_by input-height"
                                           name="modal_baptized_by[query]"
                                           placeholder="<?php echo esc_html_x( "Search multipliers and contacts", 'input field placeholder', 'disciple_tools' ) ?>"
                                           autocomplete="off">
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <span class="section-subheader"><?php echo esc_html( $field_settings["baptism_date"]["name"] )?></span>
                <input type="text" data-date-format='yy-mm-dd' value="<?php echo esc_html( $post["baptism_date"]["timestamp"] ?? '' );?>" id="modal-baptism-date-picker" autocomplete="off">

            </div>


            <div class="grid-x">
                <button class="button" data-close type="button" id="close-baptism-modal">
                    <?php echo esc_html__( 'Close', 'disciple_tools' )?>
                </button>
                <button class="close-button" data-close aria-label="Close modal" type="button">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        </div>
        <?php
    }

    public function dt_search_viewable_posts_query( $query ){
        if ( isset( $query["combine"] ) && in_array( "subassigned", $query["combine"] ) && isset( $query["assigned_to"], $query["subassigned"] ) ){
            $a = $query["assigned_to"];
            $s = $query["subassigned"];
            unset( $query["assigned_to"] );
            unset( $query["subassigned"] );
            $query[] = [ "assigned_to" => $a, "subassigned" => $s ];
        }
        return $query;
    }

}
