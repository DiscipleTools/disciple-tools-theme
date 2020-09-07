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
        //setup post type
        add_action( 'after_setup_theme', [ $this, 'after_setup_theme' ], 100 );

        //setup tiles and fields
        add_action( 'p2p_init', [ $this, 'p2p_init' ] );
        add_filter( 'dt_custom_fields_settings', [ $this, 'dt_custom_fields_settings' ], 10, 2 );
        add_filter( 'dt_details_additional_tiles', [ $this, 'dt_details_additional_tiles' ], 10, 2 );
        add_filter( 'dt_details_additional_tiles', [ $this, 'dt_details_additional_tiles_after' ], 100, 2 );
        add_action( 'dt_details_additional_section', [ $this, 'dt_details_additional_section' ], 20, 2 );

        // hooks
        add_action( 'rest_api_init', [ $this, 'add_api_routes' ] );
        add_action( "post_connection_removed", [ $this, "post_connection_removed" ], 10, 4 );
        add_action( "post_connection_added", [ $this, "post_connection_added" ], 10, 4 );
        add_filter( "dt_post_update_fields", [ $this, "update_post_field_hook" ], 10, 3 );
        add_filter( "dt_post_create_fields", [ $this, "dt_post_create_fields" ], 10, 2 );
        add_filter( "dt_comments_additional_sections", [ $this, "add_comm_channel_comment_section" ], 10, 2 );


        //list
        add_filter( "dt_user_list_filters", [ $this, "dt_user_list_filters" ], 10, 2 );

    }


    public function after_setup_theme(){
        if ( class_exists( 'Disciple_Tools_Post_Type_Template' )) {
            new Disciple_Tools_Post_Type_Template( "contacts", 'Contact', 'Contacts' );
        }
    }

    public function dt_custom_fields_settings( $fields, $post_type ){
        if ( $post_type === 'contacts' ){
            $fields["nickname"] = [
                'name' => __( "Nickname", 'disciple_tools' ),
                'type' => 'text',
                'tile' => 'details',
                'in_create_form' => true,
                'icon' => get_template_directory_uri() . "/dt-assets/images/name.svg",
            ];
            $fields["type"] = [
                'name'        => __( 'Contact Type', 'disciple_tools' ),
                'type'        => 'key_select',
                'default'     => [
                    'access'    => [ "label" => __( 'Access', 'disciple_tools' ) ],
                    'user'     => [ "label" => __( 'User', 'disciple_tools' ) ],
                    'personal' => [ "label" => __( 'Personal', 'disciple_tools' ) ],
                    'fruit' => [ "label" => __( 'Fruit', 'disciple_tools' ) ],
                ],
                'tile'     => 'other',
                'in_create_form' => true,
            ];
            $fields["duplicate_data"] = [
                "name" => 'Duplicates', //system string does not need translation
                'type' => 'array',
                'default' => [],
                'section' => 'admin',
                "hidden" => true
            ];
            $fields["duplicate_of"] = [
                "name" => "Duplicate of", //system string does not need translation
                "type" => "text",
                "default" => '',
                "hidden" => true
            ];





            $fields['tags'] = [
                'name'        => __( 'Tags', 'disciple_tools' ),
                'description' => _x( 'A useful way to group related items and can help group contacts associated with noteworthy characteristics. e.g. business owner, sports lover. The contacts can also be filtered using these tags.', 'Optional Documentation', 'disciple_tools' ),
                'type'        => 'multi_select',
                'default'     => [],
                'tile'        => 'other',
                'custom_display' => true,
                'icon' => get_template_directory_uri() . "/dt-assets/images/phone.svg",
            ];
            $fields["follow"] = [
                'name'        => __( 'Follow', 'disciple_tools' ),
                'type'        => 'multi_select',
                'default'     => [],
                'section'     => 'misc',
                'hidden'      => true
            ];
            $fields["unfollow"] = [
                'name'        => __( 'Un-Follow', 'disciple_tools' ),
                'type'        => 'multi_select',
                'default'     => [],
                'section'     => 'misc',
                'hidden'      => true
            ];
            $fields["relation"] = [
                "name" => __( "Relation", 'disciple_tools' ),
                "description" => _x( "Relationship this contact has with another contact in the system.", 'Optional Documentation', 'disciple_tools' ),
                "type" => "connection",
                "post_type" => "contacts",
                "p2p_direction" => "any",
                "p2p_key" => "contacts_to_relation",
                "tile" => "other"
            ];

            $fields['tasks'] = [
                'name' => __( 'Tasks', 'disciple_tools' ),
                'type' => 'post_user_meta',
            ];
            $fields["languages"] = [
                'name' => __( 'Languages', 'disciple_tools' ),
                'type' => 'multi_select',
                'default' => dt_get_option( "dt_working_languages" ) ?: [],
                'icon' => get_template_directory_uri() . "/dt-assets/images/languages.svg",
            ];

            //add communication channels
            $fields["contact_phone"] = [
                "name" => __( 'Phone', 'disciple_tools' ),
                "icon" => get_template_directory_uri() . "/dt-assets/images/phone.svg",
                "type" => "communication_channel",
                "tile" => "details",
                "in_create_form" => true,
            ];
            $fields["contact_email"] = [
                "name" => __( 'Email', 'disciple_tools' ),
                "icon" => get_template_directory_uri() . "/dt-assets/images/email.svg",
                "type" => "communication_channel",
                "tile" => "details",
                "in_create_form" => true,
            ];
            $fields["contact_address"] = [
                "name" => __( 'Address', 'disciple_tools' ),
                "icon" => get_template_directory_uri() . "/dt-assets/images/house.svg",
                "type" => "communication_channel",
                "tile" => "details",
                "in_create_form" => true,
            ];
            $channels = self::get_channels_list();
            foreach ( $channels as $channel_key => $channel_options ){
                if ( !isset( $fields['contact_'.$channel_key] )){
//                    @todo deleted
                    //communication channels start with contact_
                    $field = [
                        "name" => $channel_options["label"],
                        "type" => "communication_channel",
                        "tile" => "details",
                    ];
                    if ( isset( $channel_options['icon'] ) ) {
                        $field['icon'] = $channel_options['icon'];
                    }
                    $fields['contact_' . $channel_key] = $field;
                }
            }
            $fields['location_grid'] = [
                'name'        => __( 'Locations', 'disciple_tools' ),
                'description' => _x( 'The general location where this contact is located.', 'Optional Documentation', 'disciple_tools' ),
                'type'        => 'location',
                'default'     => [],
                "in_create_form" => true,
                "tile" => "details",
                "icon" => get_template_directory_uri() . "/dt-assets/images/location.svg",
            ];
            $fields['location_grid_meta'] = [
                'name'        => 'Location Grid Meta', //system string does not need translation
                'type'        => 'location_meta',
                'default'     => [],
                'hidden' => true
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
                "icon" => get_template_directory_uri() . "/dt-assets/images/gender.svg",
            ];


        }
        return $fields;
    }

    public static function get_channels_list() {
        $channel_list = [
            "phone"     => [
                "label" => __( 'Phone', 'disciple_tools' ),
                "types" => [],
                "description" => '',
                "icon" => get_template_directory_uri() . "/dt-assets/images/phone.svg",
            ],
            "email"     => [
                "label" => __( 'Email', 'disciple_tools' ),
                "types" => [],
                "description" => '',
                "icon" => get_template_directory_uri() . "/dt-assets/images/email.svg",
            ],
            "address" => [
                "label" => __( "Address", 'disciple_tools' ),
                "types" => [],
                "description" => '',
                "icon" => get_template_directory_uri() . "/dt-assets/images/house.svg",
            ],
            "facebook"  => [
                "label" => __( 'Facebook', 'disciple_tools' ),
                "types" => [],
                "icon" => get_template_directory_uri() . "/dt-assets/images/facebook.svg",
                "hide_domain" => true
            ],
            "twitter"   => [
                "label" => __( 'Twitter', 'disciple_tools' ),
                "types" => [],
                "icon" => get_template_directory_uri() . "/dt-assets/images/twitter.svg",
                "hide_domain" => true
            ]
        ];

        $custom_channels = dt_get_option( "dt_custom_channels" );
        foreach ( $custom_channels as $custom_key => $custom_value ){
            $channel_list[$custom_key] = array_merge( $channel_list[$custom_key] ?? [], $custom_value );
        }
        return apply_filters( 'dt_custom_channels', $channel_list );
    }


    public function dt_details_additional_section( $section, $post_type ){
        if ( $post_type === "contacts" && $section === "other" ) :
            $contact_fields = DT_Posts::get_post_field_settings( $post_type );
            ?>
            <div class="section-subheader">
                <?php echo esc_html( $contact_fields["tags"]["name"] ) ?>
            </div>
            <div class="tags">
                <var id="tags-result-container" class="result-container"></var>
                <div id="tags_t" name="form-tags" class="scrollable-typeahead typeahead-margin-when-active">
                    <div class="typeahead__container">
                        <div class="typeahead__field">
                            <span class="typeahead__query">
                                <input class="js-typeahead-tags input-height"
                                       name="tags[query]"
                                       placeholder="<?php echo esc_html( sprintf( _x( "Search %s", "Search 'something'", 'disciple_tools' ), $contact_fields["tags"]['name'] ) )?>"
                                       autocomplete="off">
                            </span>
                            <span class="typeahead__button">
                                <button type="button" data-open="create-tag-modal" class="create-new-tag typeahead__image_button input-height">
                                    <img src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/tag-add.svg' ) ?>"/>
                                </button>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
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
                $fields["type"] = "personal"; //@todo
            }
        }
        return $fields;
    }

    public static function dt_user_list_filters( $filters, $post_type ){
        if ( $post_type === 'contacts' ){
            $counts = self::get_my_contacts_status_seeker_path();
            $fields = DT_Posts::get_post_field_settings( $post_type );

            /**
             * Setup my contacts filters
             */
            $active_counts = [];
            $update_needed = 0;
            $status_counts = [];
            $total_my = 0;
            foreach ( $counts as $count ){
                if ( $count["type"] != "user" ){
                    $total_my += $count["count"];
                    dt_increment( $status_counts[$count["overall_status"]], $count["count"] );
                    if ( $count["overall_status"] === "active" ){
                        if ( isset( $count["update_needed"] ) ) {
                            $update_needed += (int) $count["update_needed"];
                        }
                        dt_increment( $active_counts[$count["seeker_path"]], $count["count"] );
                    }
                }
            }
            if ( !isset( $status_counts["closed"] ) ) {
                $status_counts["closed"] = '';
            }

            $filters["tabs"][] = [
                "key" => "assigned_to_me",
                "label" => sprintf( _x( "My %s", 'My records', 'disciple_tools' ), DT_Posts::get_post_settings( $post_type )['label_plural'] ),
                "count" => $total_my,
                "order" => 20
            ];
            // add assigned to me filters
            $filters["filters"][] = [
                'ID' => 'my_all',
                'tab' => 'assigned_to_me',
                'name' => _x( "All", 'List Filters', 'disciple_tools' ),
                'query' => [
                    'assigned_to' => [ 'me' ],
                    'subassigned' => [ 'me' ],
                    'combine' => [ 'subassigned' ],
                    'overall_status' => [ '-closed' ],
                    'sort' => 'overall_status'
                ],
                "count" => $total_my,
            ];
            foreach ( $fields["overall_status"]["default"] as $status_key => $status_value ) {
                if ( isset( $status_counts[$status_key] ) ) {
                    $filters["filters"][] = [
                        "ID" => 'my_' . $status_key,
                        "tab" => 'assigned_to_me',
                        "name" => $status_value["label"],
                        "query" => [
                            'assigned_to' => [ 'me' ],
                            'subassigned' => [ 'me' ],
                            'combine' => [ 'subassigned' ],
                            'overall_status' => [ $status_key ],
                            'sort' => 'seeker_path'
                        ],
                        "count" => $status_counts[$status_key]
                    ];
                    if ( $status_key === "active" ){
                        if ( $update_needed > 0 ){
                            $filters["filters"][] = [
                                "ID" => 'my_update_needed',
                                "tab" => 'assigned_to_me',
                                "name" => $fields["requires_update"]["name"],
                                "query" => [
                                    'assigned_to' => [ 'me' ],
                                    'subassigned' => [ 'me' ],
                                    'combine' => [ 'subassigned' ],
                                    'overall_status' => [ 'active' ],
                                    'requires_update' => [ true ],
                                    'sort' => 'seeker_path'
                                ],
                                "count" => $update_needed,
                                'subfilter' => true
                            ];
                        }
                        foreach ( $fields["seeker_path"]["default"] as $seeker_path_key => $seeker_path_value ) {
                            if ( isset( $active_counts[$seeker_path_key] ) ) {
                                $filters["filters"][] = [
                                    "ID" => 'my_' . $seeker_path_key,
                                    "tab" => 'assigned_to_me',
                                    "name" => $seeker_path_value["label"],
                                    "query" => [
                                        'assigned_to' => [ 'me' ],
                                        'subassigned' => [ 'me' ],
                                        'combine' => [ 'subassigned' ],
                                        'overall_status' => [ 'active' ],
                                        'seeker_path' => [ $seeker_path_key ],
                                        'sort' => 'name'
                                    ],
                                    "count" => $active_counts[$seeker_path_key],
                                    'subfilter' => true
                                ];
                            }
                        }
                    }
                }
            }

            /**
             * Setup dispatcher filters
             */
            if ( current_user_can( "view_any_contacts" ) || current_user_can( 'access_specific_sources' ) ) {
                $counts = self::get_all_contacts_status_seeker_path();
                $all_active_counts = [];
                $all_update_needed = 0;
                $all_status_counts = [];
                $total_all = 0;
                foreach ( $counts as $count ){
                    if ( $count["type"] !== "user" ){
                        $total_all += $count["count"];
                        dt_increment( $all_status_counts[$count["overall_status"]], $count["count"] );
                        if ( $count["overall_status"] === "active" ){
                            if ( isset( $count["update_needed"] ) ) {
                                $all_update_needed += (int) $count["update_needed"];
                            }
                            dt_increment( $all_active_counts[$count["seeker_path"]], $count["count"] );
                        }
                    }
                }
                if ( !isset( $all_status_counts["closed"] ) ) {
                    $all_status_counts["closed"] = '';
                }
                $filters["tabs"][] = [
                    "key" => "all_dispatch",
                    "label" => sprintf( _x( "All %s", 'All records', 'disciple_tools' ), DT_Posts::get_post_settings( $post_type )['label_plural'] ),
                    "count" => $total_all,
                    "order" => 10
                ];
                // add assigned to me filters
                $filters["filters"][] = [
                    'ID' => 'all_dispatch',
                    'tab' => 'all_dispatch',
                    'name' => _x( "All", 'List Filters', 'disciple_tools' ),
                    'query' => [
                        'overall_status' => [ '-closed' ],
                        'sort' => 'overall_status'
                    ],
                    "count" => $total_all,
                ];

                foreach ( $fields["overall_status"]["default"] as $status_key => $status_value ) {
                    if ( isset( $all_status_counts[$status_key] ) ) {
                        $filters["filters"][] = [
                            "ID" => 'all_' . $status_key,
                            "tab" => 'all_dispatch',
                            "name" => $status_value["label"],
                            "query" => [
                                'overall_status' => [ $status_key ],
                                'sort' => 'seeker_path'
                            ],
                            "count" => $all_status_counts[$status_key]
                        ];
                        if ( $status_key === "active" ){
                            if ( $all_update_needed > 0 ){
                                $filters["filters"][] = [
                                    "ID" => 'all_update_needed',
                                    "tab" => 'all_dispatch',
                                    "name" => $fields["requires_update"]["name"],
                                    "query" => [
                                        'overall_status' => [ 'active' ],
                                        'requires_update' => [ true ],
                                        'sort' => 'seeker_path'
                                    ],
                                    "count" => $all_update_needed,
                                    'subfilter' => true
                                ];
                            }
                            foreach ( $fields["seeker_path"]["default"] as $seeker_path_key => $seeker_path_value ) {
                                if ( isset( $all_active_counts[$seeker_path_key] ) ) {
                                    $filters["filters"][] = [
                                        "ID" => 'all_' . $seeker_path_key,
                                        "tab" => 'all_dispatch',
                                        "name" => $seeker_path_value["label"],
                                        "query" => [
                                            'overall_status' => [ 'active' ],
                                            'seeker_path' => [ $seeker_path_key ],
                                            'sort' => 'name'
                                        ],
                                        "count" => $all_active_counts[$seeker_path_key],
                                        'subfilter' => true
                                    ];
                                }
                            }
                        }
                    }
                }
            }
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
                'ID' => 'my_coached',
                'visible' => "1",
                'type' => 'default',
                'tab' => 'custom',
                'name' => 'Coached by me',
                'query' => [
                    'coached_by' => [ 'me' ],
                    'sort' => 'seeker_path',
                ],
                'labels' => [
                    [
                        'id' => 'my_coached',
                        'name' => 'Coached by be',
                        'field' => 'coached_by',
                    ],
                ],
            ],
            [
                'ID' => 'my_subassigned',
                'visible' => "1",
                'type' => 'default',
                'tab' => 'custom',
                'name' => 'Subassigned to me',
                'query' => [
                    'subassigned' => [ 'me' ],
                    'sort' => 'overall_status',
                ],
                'labels' => [
                    [
                        'id' => 'my_subassigned',
                        'name' => 'Subassigned to me',
                        'field' => 'subassigned',
                    ],
                ],
            ],
            [
                'ID' => 'my_shared',
                'visible' => "1",
                'type' => 'default',
                'tab' => 'custom',
                'name' => 'Shared with me',
                'query' => [
                    'assigned_to' => [ 'shared' ],
                    'sort' => 'overall_status',
                ],
                'labels' => [
                    [
                        'id' => 'my_shared',
                        'name' => 'Shared with me',
                        'field' => 'subassigned',
                    ],
                ],
            ]
        ];
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
            if ( $filter["name"] === 'Subassigned to me' ) {
                $filters[$index]["name"] = __( 'Subassigned only', 'disciple_tools' );
                $filters[$index]['labels'][0]['name'] = __( 'Subassigned only', 'disciple_tools' );
            }
            if ( $filter["name"] === 'Shared with me' ) {
                $filters[$index]["name"] = __( 'Shared with me', 'disciple_tools' );
                $filters[$index]['labels'][0]['name'] = __( 'Shared with me', 'disciple_tools' );
            }
            if ( $filter["name"] === 'Coached by me' ) {
                $filters[$index]["name"] = __( 'Coached by me', 'disciple_tools' );
                $filters[$index]['labels'][0]['name'] = __( 'Coached by me', 'disciple_tools' );
            }
        }
        return $filters;
    }

    //list page filters function
    private static function get_all_contacts_status_seeker_path(){
        global $wpdb;
        $results = [];

        $can_view_all = false;
        if ( current_user_can( 'access_specific_sources' ) ) {
            $sources = get_user_option( 'allowed_sources', get_current_user_id() ) ?? [];
            if ( empty( $sources ) || in_array( 'all', $sources ) ) {
                $can_view_all = true;
            }
        }

        if ( current_user_can( "view_any_contacts" ) || $can_view_all ) {
            $results = $wpdb->get_results("
                SELECT type.meta_value as type, status.meta_value as overall_status, pm.meta_value as seeker_path, count(pm.post_id) as count, count(un.post_id) as update_needed
                FROM $wpdb->postmeta pm
                INNER JOIN $wpdb->postmeta status ON( status.post_id = pm.post_id AND status.meta_key = 'overall_status' AND status.meta_value != 'closed' )
                INNER JOIN $wpdb->posts a ON( a.ID = pm.post_id AND a.post_type = 'contacts' and a.post_status = 'publish' )
                LEFT JOIN $wpdb->postmeta type ON ( type.post_id = pm.post_id AND type.meta_key = 'type' )
                LEFT JOIN $wpdb->postmeta un ON ( un.post_id = pm.post_id AND un.meta_key = 'requires_update' AND un.meta_value = '1' )
                WHERE pm.meta_key = 'seeker_path'
                GROUP BY type.meta_value, status.meta_value, pm.meta_value
            ", ARRAY_A);
        } else if ( current_user_can( 'access_specific_sources' ) ) {
            $sources = get_user_option( 'allowed_sources', get_current_user_id() ) ?? [];
            $sources_sql = dt_array_to_sql( $sources );
            // phpcs:disable
            $results = $wpdb->get_results( $wpdb->prepare( "
                SELECT type.meta_value as type, status.meta_value as overall_status, pm.meta_value as seeker_path, count(pm.post_id) as count, count(un.post_id) as update_needed
                FROM $wpdb->postmeta pm
                INNER JOIN $wpdb->postmeta status ON( status.post_id = pm.post_id AND status.meta_key = 'overall_status' AND status.meta_value != 'closed' )
                INNER JOIN $wpdb->posts a ON( a.ID = pm.post_id AND a.post_type = 'contacts' and a.post_status = 'publish' )
                LEFT JOIN $wpdb->postmeta type ON ( type.post_id = pm.post_id AND type.meta_key = 'type' )
                LEFT JOIN $wpdb->postmeta un ON ( un.post_id = pm.post_id AND un.meta_key = 'requires_update' AND un.meta_value = '1' )
                WHERE pm.meta_key = 'seeker_path'
                AND (
                    pm.post_id IN ( SELECT post_id from $wpdb->postmeta as source where source.meta_value IN ( $sources_sql ) )
                    OR pm.post_id IN ( SELECT post_id FROM $wpdb->dt_share AS shares where shares.user_id = %s )
                )
                GROUP BY type.meta_value, status.meta_value, pm.meta_value
            ", esc_sql( get_current_user_id() ) ) , ARRAY_A );
            // phpcs:enable
        }
        return $results;
    }

    //list page filters function
    private static function get_my_contacts_status_seeker_path(){
        global $wpdb;
        $user_post = Disciple_Tools_Users::get_contact_for_user( get_current_user_id() ) ?? 0;
        $results = $wpdb->get_results( $wpdb->prepare( "
            SELECT type.meta_value as type, status.meta_value as overall_status, pm.meta_value as seeker_path, count(pm.post_id) as count, count(un.post_id) as update_needed
            FROM $wpdb->postmeta pm
            INNER JOIN $wpdb->postmeta status ON( status.post_id = pm.post_id AND status.meta_key = 'overall_status' AND status.meta_value != 'closed')
            INNER JOIN $wpdb->posts a ON( a.ID = pm.post_id AND a.post_type = 'contacts' and a.post_status = 'publish' )
            LEFT JOIN $wpdb->postmeta un ON ( un.post_id = pm.post_id AND un.meta_key = 'requires_update' AND un.meta_value = '1' )
            LEFT JOIN $wpdb->postmeta type ON ( type.post_id = pm.post_id AND type.meta_key = 'type' )
            WHERE pm.meta_key = 'seeker_path'
            AND (
                pm.post_id IN ( SELECT post_id FROM $wpdb->postmeta WHERE meta_key = 'assigned_to' AND meta_value = CONCAT( 'user-', %s ) )
                OR pm.post_id IN ( SELECT p2p_to from $wpdb->p2p WHERE p2p_from = %s AND p2p_type = 'contacts_to_subassigned' )
            )
            GROUP BY type.meta_value, status.meta_value, pm.meta_value
        ", get_current_user_id(), $user_post ), ARRAY_A);
        return $results;
    }

    //add options to the "Admin Actions" dropdown on the record page
    public static function dt_record_admin_actions( $post_type, $post_id ){
        if ( $post_type === "contacts" ){
            $contact = DT_Posts::get_post( $post_type, $post_id );
            if ( current_user_can( "access_contacts" ) ) {
                ?>
                <!--                @todo-->
                <li><a id="open_merge_with_contact"><?php esc_html_e( "Merge with another contact", 'disciple_tools' ) ?></a></li>
                <li><a data-open="merge-dupe-edit-modal"><?php esc_html_e( "See duplicates", 'disciple_tools' ) ?></a></li>

                <div class="reveal" id="merge-with-contact-modal" data-reveal style="min-height:500px">
                    <h3><?php esc_html_e( "Merge Contact", 'disciple_tools' )?></h3>
                    <p><?php esc_html_e( "Merge this contact with another contact.", 'disciple_tools' )?></p>

                    <div class="merge_with details">
                        <var id="merge_with-result-container" class="result-container merge_with-result-container"></var>
                        <div id="merge_with_t" name="form-merge_with">
                            <div class="typeahead__container">
                                <div class="typeahead__field">
                                    <span class="typeahead__query">
                                        <input class="js-typeahead-merge_with input-height"
                                               name="merge_with[query]" placeholder="<?php echo esc_html_x( "Search multipliers and contacts", 'input field placeholder', 'disciple_tools' ) ?>"
                                               autocomplete="off">
                                    </span>
                                    <span class="typeahead__button">
                                        <button type="button" class="search_merge_with typeahead__image_button input-height" data-id="user-select_t">
                                            <img src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/chevron_down.svg' ) ?>"/>
                                        </button>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <br>
                    <div class="confirm-merge-with-contact" style="display: none">
                        <p><span  id="name-of-contact-to-merge"></span> <?php echo esc_html_x( "selected.", 'added to the end of a sentence', 'disciple_tools' ) ?></p>
                        <p><?php esc_html_e( "Click merge to continue.", 'disciple_tools' ) ?></p>
                    </div>

                    <div class="grid-x">
                        <button class="button button-cancel clear" data-close aria-label="Close reveal" type="button">
                            <?php echo esc_html__( 'Cancel', 'disciple_tools' )?>
                        </button>
                        <form action='<?php echo esc_url( site_url() );?>/contacts/mergedetails' method='get'>
                            <input type='hidden' name='currentid' value='<?php echo esc_html( $contact["ID"] );?>'/>
                            <input id="confirm-merge-with-contact-id" type='hidden' name='dupeid' value=''/>
                            <button type='submit' class="button confirm-merge-with-contact" style="display: none">
                                <?php echo esc_html__( 'Merge', 'disciple_tools' )?>
                            </button>
                        </form>
                        <button class="close-button" data-close aria-label="Close modal" type="button">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                </div>
                <?php
                get_template_part( 'dt-assets/parts/merge', 'details' );
            }
        }
    }



    public function add_api_routes() {
        $namespace = "dt-posts/v2";
        register_rest_route(
            $namespace, '/contact/transfer', [
                "methods"  => "POST",
                "callback" => [ $this, 'contact_transfer' ],
            ]
        );
        register_rest_route(
            $namespace, '/contact/receive-transfer', [
                "methods"  => "POST",
                "callback" => [ $this, 'receive_transfer' ],
            ]
        );
    }
    public function contact_transfer( WP_REST_Request $request ){

        if ( ! ( current_user_can( 'view_any_contacts' ) || current_user_can( 'manage_dt' ) ) ) {
            return new WP_Error( __METHOD__, 'Insufficient permissions' );
        }

        $params = $request->get_params();
        if ( ! isset( $params['contact_id'] ) || ! isset( $params['site_post_id'] ) ){
            return new WP_Error( __METHOD__, "Missing required parameters.", [ 'status' => 400 ] );
        }

        return Disciple_Tools_Contacts_Transfer::contact_transfer( $params['contact_id'], $params['site_post_id'] );

    }

    public function receive_transfer( WP_REST_Request $request ){
        $params = $request->get_params();
        if ( ! current_user_can( 'create_contacts' ) ) {
            return new WP_Error( __METHOD__, 'Insufficient permissions' );
        }

        if ( isset( $params['contact_data'] ) ) {
            $result = Disciple_Tools_Contacts_Transfer::receive_transferred_contact( $params );
            if ( is_wp_error( $result ) ) {
                return [
                    'status' => 'FAIL',
                    'error' => $result->get_error_message(),
                ];
            } else {
                return [
                    'status' => 'OK',
                    'error' => $result['errors'],
                    'created_id' => $result['created_id'],
                ];
            }
        } else {
            return [
                'status' => 'FAIL',
                'error' => 'Missing required parameter'
            ];
        }
    }

    public function add_comm_channel_comment_section( $sections, $post_type ){
        $channels = self::get_channels_list();
        if ( $post_type === "contacts" ){
            foreach ( $channels as $channel_key => $channel_option ) {
                $enabled = !isset( $channel_option['enabled'] ) || $channel_option['enabled'] !== false;
                $hide_domain = isset( $channel_option['hide_domain'] ) && $channel_option['hide_domain'] == true;
                if ( $channel_key == 'phone' || $channel_key == 'email' || $channel_key == 'address' ){
                    continue;
                }

                $sections[] = [
                    "key" => $channel_key,
                    "label" => esc_html( $channel_option["label"] ?? $channel_key )
                ];
            }
        }
        return $sections;
    }
}
