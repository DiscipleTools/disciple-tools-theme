<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly.

class DT_Contacts_Base {
    private static $_instance = null;
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    } // End instance()

    public $post_type = "contacts";

    public function __construct() {
        //setup post type
        add_action( 'after_setup_theme', [ $this, 'after_setup_theme' ], 100 );
        add_filter( 'dt_set_roles_and_permissions', [ $this, 'dt_set_roles_and_permissions' ], 10, 1 );

        //setup tiles and fields
        add_action( 'wp_enqueue_scripts', [ $this, 'scripts' ], 99 );
        add_action( 'p2p_init', [ $this, 'p2p_init' ] );
        add_filter( 'dt_custom_fields_settings', [ $this, 'dt_custom_fields_settings' ], 5, 2 );
        add_filter( 'dt_custom_fields_settings_after_combine', [ $this, 'dt_custom_fields_settings_after_combine' ], 10, 2 );
        add_filter( 'dt_details_additional_tiles', [ $this, 'dt_details_additional_tiles' ], 10, 2 );
        add_filter( 'dt_details_additional_tiles', [ $this, 'dt_details_additional_tiles_after' ], 100, 2 );
        add_action( 'dt_record_admin_actions', [ $this, "dt_record_admin_actions" ], 10, 2 );
        add_action( 'dt_record_footer', [ $this, "dt_record_footer" ], 10, 2 );
        add_action( 'dt_record_notifications_section', [ $this, "dt_record_notifications_section" ], 10, 2 );
        add_filter( 'dt_record_icon', [ $this, 'dt_record_icon' ], 10, 3 );

        // hooks
        add_action( 'rest_api_init', [ $this, 'add_api_routes' ] );
        add_action( "post_connection_removed", [ $this, "post_connection_removed" ], 10, 4 );
        add_action( "post_connection_added", [ $this, "post_connection_added" ], 10, 4 );
        add_filter( "dt_post_update_fields", [ $this, "update_post_field_hook" ], 10, 3 );
        add_filter( "dt_post_create_fields", [ $this, "dt_post_create_fields" ], 20, 2 );
        add_filter( "dt_comments_additional_sections", [ $this, "add_comm_channel_comment_section" ], 10, 2 );


        //list
        add_filter( "dt_user_list_filters", [ $this, "dt_user_list_filters" ], 10, 2 );

    }


    public function after_setup_theme(){
        if ( class_exists( 'Disciple_Tools_Post_Type_Template' )) {
            new Disciple_Tools_Post_Type_Template( "contacts", __( 'Contact', 'disciple_tools' ), __( 'Contacts', 'disciple_tools' ) );
        }
    }

    public function dt_set_roles_and_permissions( $expected_roles ){
        $expected_roles["multiplier"] = [
            "label" => __( 'Multiplier', 'disciple_tools' ),
            "description" => "Interacts with Contacts and Groups",
            "permissions" => []
        ];
        $expected_roles["strategist"] = [
            "label" => __( 'Strategist', 'disciple_tools' ),
            "description" => "View project metrics",
            "permissions" => []
        ];
        $expected_roles["user_manager"] = [
            "label" => __( 'User Manager', 'disciple_tools' ),
            "description" => "List, invite, promote and demote users",
            "permissions" => []
        ];
        $expected_roles["dt_admin"] = [
            "label" => __( 'Disciple.Tools Admin', 'disciple_tools' ),
            "description" => "All D.T permissions",
            "permissions" => []
        ];
        $expected_roles["administrator"] = [
            "label" => __( 'Administrator', 'disciple_tools' ),
            "description" => "All D.T permissions plus the ability to manage plugins.",
            "permissions" => []
        ];

        $multiplier_permissions = Disciple_Tools_Roles::default_multiplier_caps();

        $user_management_permissions = Disciple_Tools_Roles::default_user_management_caps();

        // Multiplier
        $expected_roles["multiplier"]["permissions"] = array_merge( $expected_roles["multiplier"]["permissions"], $multiplier_permissions );

        // User Manager
        $expected_roles["user_manager"]["permissions"] = array_merge( $expected_roles["user_manager"]["permissions"], $multiplier_permissions );
        $expected_roles["user_manager"]["permissions"] = array_merge( $expected_roles["user_manager"]["permissions"], $user_management_permissions );

        // D.T Admin
        $expected_roles["dt_admin"]["permissions"] = array_merge( $expected_roles["dt_admin"]["permissions"], $multiplier_permissions );
        $expected_roles["dt_admin"]["permissions"] = array_merge( $expected_roles["dt_admin"]["permissions"], $user_management_permissions );
        $expected_roles["dt_admin"]["permissions"]['manage_dt'] = true;
        $expected_roles["dt_admin"]["permissions"]['view_project_metrics'] = true;
        $expected_roles["dt_admin"]["permissions"]['edit_page'] = true; //site links
        $expected_roles["dt_admin"]["permissions"]['edit_posts'] = true; //site links

        //strategist
        $expected_roles["strategist"]["permissions"]['view_project_metrics'] = true;

        $expected_roles["administrator"]["permissions"] = array_merge( $expected_roles["dt_admin"]["permissions"], $multiplier_permissions );
        $expected_roles["administrator"]["permissions"] = array_merge( $expected_roles["dt_admin"]["permissions"], $user_management_permissions );
        $expected_roles["administrator"]["permissions"]["dt_all_admin_contacts"] = true;

        return $expected_roles;
    }

    public function dt_custom_fields_settings( $fields, $post_type ){
        if ( $post_type === 'contacts' ){
            $fields["nickname"] = [
                'name' => __( "Nickname", 'disciple_tools' ),
                'type' => 'text',
                'tile' => 'details',
                'icon' => get_template_directory_uri() . "/dt-assets/images/nametag.svg",
            ];
            $fields["type"] = [
                'name'        => __( 'Contact Type', 'disciple_tools' ),
                'type'        => 'key_select',
                'default'     => [
                    'user' => [
                        "label" => __( 'User', 'disciple_tools' ),
                        "description" => __( "Representing a User in the system", 'disciple_tools' ),
                        "color" => "#3F729B",
                        "hidden" => true
                    ],
                    'personal' => [
                        "label" => __( 'Personal', 'disciple_tools' ),
                        "color" => "#9b379b",
                        "description" => __( "A friend, family member or acquaintance", 'disciple_tools' ),
                        "visibility" => __( "Only me", 'disciple_tools' ),
                        "icon" => get_template_directory_uri() . "/dt-assets/images/locked.svg",
                        "order" => 50
                    ],
                ],
                "icon" => get_template_directory_uri() . '/dt-assets/images/circle-square-triangle.svg',
                'customizable' => false
            ];
            $fields["duplicate_data"] = [
                "name" => 'Duplicates', //system string does not need translation
                'type' => 'array',
                'default' => [],
                "hidden" => true
            ];
            $fields["duplicate_of"] = [
                "name" => "Duplicate of", //system string does not need translation
                "type" => "text",
                "hidden" => true
            ];


            $fields['tags'] = [
                'name'        => __( 'Tags', 'disciple_tools' ),
                'description' => _x( 'A useful way to group related items and can help group contacts associated with noteworthy characteristics. e.g. business owner, sports lover. The contacts can also be filtered using these tags.', 'Optional Documentation', 'disciple_tools' ),
                'type'        => 'tags',
                'default'     => [],
                'tile'        => 'other',
                'icon' => get_template_directory_uri() . "/dt-assets/images/tag.svg",
            ];
            $fields["follow"] = [
                'name'        => __( 'Follow', 'disciple_tools' ),
                'type'        => 'multi_select',
                'default'     => [],
                'hidden'      => true
            ];
            $fields["unfollow"] = [
                'name'        => __( 'Un-Follow', 'disciple_tools' ),
                'type'        => 'multi_select',
                'default'     => [],
                'hidden'      => true
            ];
            $fields['tasks'] = [
                'name' => __( 'Tasks', 'disciple_tools' ),
                'type' => 'task',
                'private' => true
            ];
            $fields["languages"] = [
                'name' => __( 'Languages', 'disciple_tools' ),
                'type' => 'multi_select',
                'default' => dt_get_option( "dt_working_languages" ) ?: [],
                'icon' => get_template_directory_uri() . "/dt-assets/images/languages.svg",
                "tile" => "no_tile"
            ];

            //add communication channels
            $fields["contact_phone"] = [
                "name" => __( 'Phone', 'disciple_tools' ),
                "icon" => get_template_directory_uri() . "/dt-assets/images/phone.svg",
                "type" => "communication_channel",
                "tile" => "details",
                "customizable" => false,
                "in_create_form" => true,
            ];
            $fields["contact_email"] = [
                "name" => __( 'Email', 'disciple_tools' ),
                "icon" => get_template_directory_uri() . "/dt-assets/images/email.svg",
                "type" => "communication_channel",
                "tile" => "details",
                "customizable" => false
            ];

            // add location fields
            $fields['location_grid'] = [
                'name'        => __( 'Locations', 'disciple_tools' ),
                'description' => _x( 'The general location where this contact is located.', 'Optional Documentation', 'disciple_tools' ),
                'type'        => 'location',
                'mapbox'    => false,
                "in_create_form" => true,
                "tile" => "details",
                "icon" => get_template_directory_uri() . "/dt-assets/images/location.svg",
            ];
            $fields['location_grid_meta'] = [
                'name'        => __( 'Locations or Address', 'disciple_tools' ),
                'type'        => 'location_meta',
                "tile"      => "details",
                'mapbox'    => false,
                'hidden' => true
            ];
            $fields["contact_address"] = [
                "name" => __( 'Address', 'disciple_tools' ),
                "icon" => get_template_directory_uri() . "/dt-assets/images/house.svg",
                "type" => "communication_channel",
                "tile" => "details",
                'mapbox'    => false,
                "customizable" => false
            ];
            if ( DT_Mapbox_API::get_key() ){
                $fields["contact_address"]["custom_display"] = true;
                $fields["contact_address"]["mapbox"] = true;
                $fields["contact_address"]["hidden"] = true;
                unset( $fields["contact_address"]["tile"] );
                $fields["location_grid"]["mapbox"] = true;
                $fields["location_grid"]["hidden"] = true;
                $fields["location_grid_meta"]["mapbox"] = true;
                $fields["location_grid_meta"]["hidden"] = false;
            }

            // add social media
            $fields["contact_facebook"] = [
                "name" => __( 'Facebook', 'disciple_tools' ),
                "icon" => get_template_directory_uri() . "/dt-assets/images/facebook.svg",
                "hide_domain" => true,
                "type" => "communication_channel",
                "tile" => "details",
                "customizable" => false
            ];
            $fields["contact_twitter"] = [
                "name" => __( 'Twitter', 'disciple_tools' ),
                "icon" => get_template_directory_uri() . "/dt-assets/images/twitter.svg",
                "hide_domain" => true,
                "type" => "communication_channel",
                "tile" => "details",
                "customizable" => false
            ];
            $fields["contact_other"] = [
                "name" => __( 'Other Social Links', 'disciple_tools' ),
                "icon" => get_template_directory_uri() . "/dt-assets/images/socialmedia.svg",
                "hide_domain" => false,
                "type" => "communication_channel",
                "tile" => "details",
                "customizable" => false
            ];

            $fields["relation"] = [
                "name" => sprintf( _x( "Connections to other %s", 'connections to other records', 'disciple_tools' ), __( "Contacts", 'disciple_tools' ) ),
                "description" => _x( "Relationship this contact has with another contact in the system.", 'Optional Documentation', 'disciple_tools' ),
                "type" => "connection",
                "post_type" => "contacts",
                "p2p_direction" => "any",
                "p2p_key" => "contacts_to_relation",
                "tile" => "other",
                "in_create_form" => [ "placeholder" ],
                'icon' => get_template_directory_uri() . "/dt-assets/images/connection-people.svg",
            ];

            $fields['gender'] = [
                'name'        => __( 'Gender', 'disciple_tools' ),
                'type'        => 'key_select',
                'default'     => [
                    'male'    => [ "label" => __( 'Male', 'disciple_tools' ) ],
                    'female'  => [ "label" => __( 'Female', 'disciple_tools' ) ],
                ],
                'tile'     => 'details',
                "icon" => get_template_directory_uri() . "/dt-assets/images/gender.svg",
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
                'icon' => get_template_directory_uri() . "/dt-assets/images/contact-age.svg",
            ];

            $fields['requires_update'] = [
                'name'        => __( 'Requires Update', 'disciple_tools' ),
                'type'        => 'boolean',
                'default'     => false,
            ];

            $fields["overall_status"] = [
                "name" => __( "Contact Status", 'disciple_tools' ),
                'description' => _x( 'The Contact Status describes the progress in communicating with the contact.', "Contact Status field description", 'disciple_tools' ),
                "type" => "key_select",
                "default" => [
                    'active'       => [
                        "label" => __( 'Active', 'disciple_tools' ),
                        "description" => _x( "The contact is progressing and/or continually being updated.", "Contact Status field description", 'disciple_tools' ),
                        "color" => "#4CAF50",
                    ],
                    "closed" => [
                        "label" => __( "Archived", 'disciple_tools' ),
                        "color" => "#808080",
                        "description" => _x( "This contact has made it known that they no longer want to continue or you have decided not to continue with him/her.", "Contact Status field description", 'disciple_tools' ),
                    ]
                ]
            ];
        }
        return $fields;
    }

    /**
     * Filter that runs after the default fields and custom settings have been combined
     * @param $fields
     * @param $post_type
     * @return mixed
     */
    public function dt_custom_fields_settings_after_combine( $fields, $post_type ){
        if ( $post_type === "contacts" ){
            //make sure disabled communication channels also have the hidden field set
            foreach ( $fields as $field_key => $field_value ){
                if ( isset( $field_value["type"] ) && $field_value["type"] === "communication_channel" ){
                    if ( isset( $field_value["enabled"] ) && $field_value["enabled"] === false ){
                        $fields[$field_key]["hidden"] = true;
                    }
                }
            }
        }
        return $fields;
    }

    public static function dt_record_admin_actions( $post_type, $post_id ){
        if ( $post_type === "contacts" ){
            $post = DT_Posts::get_post( $post_type, $post_id );
            if ( empty( $post["archive"] ) && ( $post["type"]["key"] === "personal" || $post["type"]["key"] === "placeholder" ) ) :?>
                <li>
                    <a data-open="archive-record-modal">
                        <img class="dt-icon" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/archive.svg' ) ?>"/>
                        <?php echo esc_html( sprintf( _x( "Archive %s", "Archive Contact", 'disciple_tools' ), DT_Posts::get_post_settings( $post_type )["label_singular"] ) ) ?></a>
                </li>
            <?php endif; ?>

            <li>
                <a data-open="contact-type-modal">
                    <img class="dt-icon" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/circle-square-triangle.svg' ) ?>"/>
                    <?php echo esc_html( sprintf( _x( "Change %s Type", "Change Record Type", 'disciple_tools' ), DT_Posts::get_post_settings( $post_type )["label_singular"] ) ) ?></a>
            </li>
            <li><a data-open="merge-dupe-edit-modal">
                    <img class="dt-icon" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/duplicate.svg' ) ?>"/>

                    <?php esc_html_e( "See duplicates", 'disciple_tools' ) ?></a></li>
            <li><a id="open_merge_with_contact">
                    <img class="dt-icon" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/merge.svg' ) ?>"/>
                    <?php esc_html_e( "Merge with another contact", 'disciple_tools' ) ?></a></li>
            <?php get_template_part( 'dt-assets/parts/merge', 'details' ); ?>
            <?php
        }
    }


    public function dt_record_footer( $post_type, $post_id ){
        if ( $post_type === "contacts" ) :
            $contact_fields = DT_Posts::get_post_field_settings( $post_type );
            $post = DT_Posts::get_post( $post_type, $post_id ); ?>
            <div class="reveal" id="archive-record-modal" data-reveal data-reset-on-close>
                <h3><?php echo esc_html( sprintf( _x( "Archive %s", "Archive Contact", 'disciple_tools' ), DT_Posts::get_post_settings( $post_type )["label_singular"] ) ) ?></h3>
                <p><?php echo esc_html( sprintf( _x( "Are you sure you want to archive %s?", "Are you sure you want to archive name?", 'disciple_tools' ), $post["name"] ) ) ?></p>

                <div class="grid-x">
                    <button class="button button-cancel clear" data-close aria-label="Close reveal" type="button">
                        <?php echo esc_html__( 'Cancel', 'disciple_tools' )?>
                    </button>
                    <button class="button alert loader" type="button" id="archive-record">
                        <?php esc_html_e( 'Archive', 'disciple_tools' ); ?>
                    </button>
                    <button class="close-button" data-close aria-label="Close modal" type="button">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            </div>

            <div class="reveal" id="contact-type-modal" data-reveal>
                <h3><?php echo esc_html( $contact_fields["type"]["name"] ?? '' )?></h3>
                <p><?php echo esc_html( $contact_fields["type"]["description"] ?? '' )?></p>
                <p><?php esc_html_e( 'Choose an option:', 'disciple_tools' )?></p>

                <select id="type-options">
                    <?php
                    foreach ( $contact_fields["type"]["default"] as $option_key => $option ) {
                        if ( !empty( $option["label"] ) && ( !isset( $option["hidden"] ) || $option["hidden"] !== true ) ) {
                            $selected = ( $option_key === ( $post["type"]["key"] ?? "" ) ) ? "selected" : "";
                            ?>
                            <option value="<?php echo esc_attr( $option_key ) ?>" <?php echo esc_html( $selected ) ?>>
                                <?php echo esc_html( $option["label"] ?? "" ) ?>
                                <?php if ( !empty( $option["description"] ) ){
                                    echo esc_html( ' - ' . $option["description"] ?? "" );
                                } ?>
                            </option>
                            <?php
                        }
                    }
                    ?>
                </select>

                <button class="button button-cancel clear" data-close aria-label="Close reveal" type="button">
                    <?php echo esc_html__( 'Cancel', 'disciple_tools' )?>
                </button>
                <button class="button loader" type="button" id="confirm-type-close" data-field="closed">
                    <?php echo esc_html__( 'Confirm', 'disciple_tools' )?>
                </button>
                <button class="close-button" data-close aria-label="Close modal" type="button">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <script type="text/javascript">
                jQuery('#confirm-type-close').on('click', function(){
                    $(this).toggleClass('loading')
                    API.update_post('contacts', <?php echo esc_html( GET_THE_ID() ); ?>, {type:$('#type-options').val()}).then(contactData=>{
                        window.location.reload()
                    }).catch(err => { console.error(err) })
                })
            </script>
        <?php endif;
    }


    public function p2p_init(){
        /**
         * Contact Connection or Relation
         */
        p2p_register_connection_type(
            [
                'name'        => 'contacts_to_relation',
                'from'        => 'contacts',
                'to'          => 'contacts'
            ]
        );

    }

    public function dt_details_additional_tiles( $tiles, $post_type = "" ){
        return $tiles;
    }

    public function dt_details_additional_tiles_after( $tiles, $post_type = "" ){
        if ( $post_type === "contacts" ){
            $tiles["other"] = [ "label" => __( "Other", 'disciple_tools' ) ];
        }
        return $tiles;
    }


    public function post_connection_added( $post_type, $post_id, $post_key, $value ){
    }
    public function post_connection_removed( $post_type, $post_id, $post_key, $value ){
    }

    public function update_post_field_hook( $fields, $post_type, $post_id ){
        return $fields;
    }

    //Add, remove or modify fields before the fields are processed in post create
    public function dt_post_create_fields( $fields, $post_type ){
        if ( $post_type === "contacts" ){
            if ( !isset( $fields["type"] ) ){
                $fields["type"] = "personal";
            }
        }
        return $fields;
    }

    //list page filters function
    public static function dt_user_list_filters( $filters, $post_type ){
        if ( $post_type === 'contacts' ){
            $shared_by_type_counts = DT_Posts_Metrics::get_shared_with_meta_field_counts( "contacts", 'type' );
            $post_label_plural = DT_Posts::get_post_settings( $post_type )['label_plural'];

            $filters["tabs"][] = [
                "key" => "default",
                "label" => __( "Default Filters", 'disciple_tools' ),
                "order" => 7
            ];
            $filters["filters"][] = [
                'ID' => 'all_my_contacts',
                'tab' => 'default',
                'name' => sprintf( _x( "All %s", 'All records', 'disciple_tools' ), $post_label_plural ),
                'labels' =>[
                    [
                        'id' => 'all',
                        'name' => sprintf( _x( "All %s I can view", 'All records I can view', 'disciple_tools' ), $post_label_plural ),
                    ]
                ],
                'query' => [
                    'sort' => '-post_date',
                ],
            ];
            $filters["filters"][] = [
                'ID' => 'recent',
                'tab' => 'default',
                'name' => __( "My Recently Viewed", 'disciple_tools' ),
                'query' => [
                    'dt_recent' => true
                ],
                'labels' => [
                    [ "id" => 'recent', 'name' => __( "Last 30 viewed", 'disciple_tools' ) ]
                ]
            ];
            $filters["filters"][] = [
                'ID' => 'favorite',
                'tab' => 'default',
                'name' => sprintf( _x( "Favorite %s", 'Favorite Contacts', 'disciple_tools' ), $post_label_plural ),
                'query' => [
                    "fields" => [ "favorite" => [ "1" ] ],
                    'sort' => "name"
                ],
                'labels' => [
                    [ "id" => "1", "name" => __( "Favorite", "disciple_tools" ) ]
                ]
            ];
            $filters["filters"][] = [
                'ID' => 'personal',
                'tab' => 'default',
                'name' => __( "Personal", 'disciple_tools' ),
                'query' => [
                    'type' => [ 'personal' ],
                    'sort' => 'name',
                    "overall_status" => [ "-closed" ],
                ],
                "count" => $shared_by_type_counts['keys']['personal'] ?? 0,
            ];
            $filters["filters"] = self::add_default_custom_list_filters( $filters["filters"] );
        }
        return $filters;
    }

    //list page filters function
    private static function add_default_custom_list_filters( $filters ){
        if ( empty( $filters )){
            $filters = [];
        }
        $default_filters = [
            [
                'ID' => 'my_shared',
                'visible' => "1",
                'type' => 'default',
                'tab' => 'custom',
                'name' => __( 'Shared with me', 'disciple_tools' ),
                'query' => [
                    'shared_with' => [ 'me' ],
                    'sort' => 'name',
                ],
                'labels' => [
                    [
                        'id' => 'my_shared',
                        'name' => __( 'Shared with me', 'disciple_tools' ),
                    ],
                ],
            ]
        ];
        //prepend filter if it is not already created.
        $contact_filter_ids = array_map( function ( $a ){
            return $a["ID"];
        }, $filters );
        foreach ( $default_filters as $filter ) {
            if ( !in_array( $filter["ID"], $contact_filter_ids ) ){
                array_unshift( $filters, $filter );
            }
        }
        //translation for default fields
        foreach ( $filters as $index => $filter ) {
            if ( $filter["name"] === 'Shared with me' ) {
                $filters[$index]["name"] = __( 'Shared with me', 'disciple_tools' );
                $filters[$index]['labels'][0]['name'] = __( 'Shared with me', 'disciple_tools' );
            }
        }
        return $filters;
    }


    public function scripts(){
        if ( is_singular( "contacts" ) && get_the_ID() && DT_Posts::can_view( $this->post_type, get_the_ID() ) ){
            wp_enqueue_script( 'dt_contacts', get_template_directory_uri() . '/dt-contacts/contacts.js', [
                'jquery',
            ], filemtime( get_theme_file_path() . '/dt-contacts/contacts.js' ), true );
        }
    }

    public function add_api_routes() {
        $namespace = "dt-posts/v2";

    }



    public function add_comm_channel_comment_section( $sections, $post_type ){
        if ( $post_type === "contacts" ){
            $channels = DT_Posts::get_post_field_settings( $post_type );
            foreach ( $channels as $channel_key => $channel_option ) {
                if ( $channel_option["type"] !== "communication_channel" ){
                    continue;
                }
                $enabled = !isset( $channel_option['enabled'] ) || $channel_option['enabled'] !== false;
                if ( $channel_key == 'contact_phone' || $channel_key == 'contact_email' || $channel_key == 'contact_address' || !$enabled ){
                    continue;
                }
                $sections[] = [
                    "key" => $channel_key,
                    "label" => esc_html( $channel_option["name"] ?? $channel_key )
                ];
            }
        }
        return $sections;
    }

    public function dt_record_notifications_section( $post_type, $dt_post ){
        if ( $post_type === "contacts" ):
            $post_settings = DT_Posts::get_post_settings( $post_type );
            ?>
            <!-- archived -->
            <section class="cell small-12 archived-notification"
                     style="display: <?php echo esc_html( ( isset( $dt_post['overall_status']["key"] ) && $dt_post['overall_status']["key"] === "closed" ) ? "block" : "none" ) ?> ">
                <div class="bordered-box detail-notification-box" style="background-color:#333">
                    <h4>
                        <img src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/alert-circle-exc.svg' ) ?>"/>
                        <?php echo esc_html( sprintf( __( 'This %s is archived', 'disciple_tools' ), strtolower( $post_settings["label_singular"] ) ) ) ?>
                    </h4>
                    <button class="button" id="unarchive-record"><?php esc_html_e( 'Restore', 'disciple_tools' )?></button>
                </div>
            </section>
        <?php endif;

    }
    public function dt_record_icon( $icon, $post_type, $dt_post ){
        if ($post_type == 'contacts') {
            $gender = isset( $dt_post["gender"] ) ? $dt_post["gender"]["key"] : "male";
            $icon = 'fi-torso';
            if ($gender == 'female') {
                $icon = $icon.'-'.$gender;
            }
        }
        return $icon;
    }
}
