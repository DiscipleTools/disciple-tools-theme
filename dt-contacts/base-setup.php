<?php

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
        add_filter( 'dt_custom_fields_settings', [ $this, 'dt_custom_fields_settings' ], 10, 2 );
        add_filter( 'dt_details_additional_tiles', [ $this, 'dt_details_additional_tiles' ], 10, 2 );
        add_filter( 'dt_details_additional_tiles', [ $this, 'dt_details_additional_tiles_after' ], 100, 2 );
        add_action( 'dt_details_additional_section', [ $this, 'dt_details_additional_section' ], 20, 2 );
        add_action( 'dt_record_admin_actions', [ $this, "dt_record_admin_actions" ], 10, 2 );


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
            new Disciple_Tools_Post_Type_Template( "contacts", 'Contact', 'Contacts' );
        }
    }

    public function dt_set_roles_and_permissions( $expected_roles ){
        $roles = [
            "manage_users" => __( 'User Manager', 'disciple_tools' ),
            "dt_admin" => __( 'Disciple.Tools Admin', 'disciple_tools' ),
            "strategist" => __( 'Strategist', 'disciple_tools' ),
        ];
        $roles["multiplier"] = [
            "label" => __( 'Multiplier', 'disciple_tools' ),
            "description" => "Interacts with Contacts and Groups",
            "permissions" => []
        ];
        foreach ( $roles as $role => $label ){
            if ( !isset( $expected_roles[$role] ) ){
                $expected_roles[$role] = [
                    "label" => $label,
                    "permissions" => []
                ];
            }
        }
        // Multiplier
        $expected_roles["multiplier"]["permissions"]['read'] = true; //allow access to wp-admin to set 2nd factor auth settings per user.
        $expected_roles["multiplier"]["permissions"]['access_' . $this->post_type] = true;
        $expected_roles["multiplier"]["permissions"]['create_' . $this->post_type] = true;
        $expected_roles["multiplier"]["permissions"]['read_location'] = true;
        $expected_roles["multiplier"]["permissions"]['access_peoplegroups'] = true;
        $expected_roles["multiplier"]["permissions"]['list_peoplegroups'] = true;


        // User Manager
        $expected_roles["manage_users"]["permissions"]['read'] = true; //allow access to wp-admin to set 2nd factor auth settings per user.
        $expected_roles["manage_users"]["permissions"]['promote_users'] = true;
        $expected_roles["manage_users"]["permissions"]['edit_users'] = true;
        $expected_roles["manage_users"]["permissions"]['create_users'] = true;
        $expected_roles["manage_users"]["permissions"]['delete_users'] = true;
        $expected_roles["manage_users"]["permissions"]['list_users'] = true;
        $expected_roles["manage_users"]["permissions"]['dt_list_users'] = true;

        // D.T Admin
        $expected_roles["dt_admin"]["permissions"]['read'] = true; //allow access to wp-admin to set 2nd factor auth settings per user.
        $expected_roles["dt_admin"]["permissions"]['manage_dt'] = true;
        $expected_roles["dt_admin"]["permissions"]['view_project_metrics'] = true;
        $expected_roles["dt_admin"]["permissions"]['promote_users'] = true;
        $expected_roles["dt_admin"]["permissions"]['edit_users'] = true;
        $expected_roles["dt_admin"]["permissions"]['create_users'] = true;
        $expected_roles["dt_admin"]["permissions"]['delete_users'] = true;
        $expected_roles["dt_admin"]["permissions"]['list_users'] = true;
        $expected_roles["dt_admin"]["permissions"]['dt_list_users'] = true;
        $expected_roles["dt_admin"]["permissions"]['read_location'] = true;
        $expected_roles["dt_admin"]["permissions"]['list_peoplegroups'] = true;
        $expected_roles["dt_admin"]["permissions"]['edit_page'] = true; //site links
        $expected_roles["dt_admin"]["permissions"]['edit_posts'] = true; //site links

        $expected_roles["strategist"]["permissions"]['view_project_metrics'] = true;

        $expected_roles["administrator"]["permissions"]['read'] = true; //allow access to wp-admin to set 2nd factor auth settings per user.
        $expected_roles["administrator"]["permissions"]['promote_users'] = true;
        $expected_roles["administrator"]["permissions"]['edit_users'] = true;
        $expected_roles["administrator"]["permissions"]['create_users'] = true;
        $expected_roles["administrator"]["permissions"]['delete_users'] = true;
        $expected_roles["administrator"]["permissions"]['list_users'] = true;
        $expected_roles["administrator"]["permissions"]['dt_list_users'] = true;


        return $expected_roles;
    }

    public function dt_custom_fields_settings( $fields, $post_type ){
        if ( $post_type === 'contacts' ){
            $fields["nickname"] = [
                'name' => __( "Nickname", 'disciple_tools' ),
                'type' => 'text',
                'tile' => 'details',
                'icon' => get_template_directory_uri() . "/dt-assets/images/name.svg",
            ];
            $fields["type"] = [
                'name'        => __( 'Contact Type', 'disciple_tools' ),
                'type'        => 'key_select',
                'default'     => [
                    'user' => [
                        "label" => __( 'User', 'disciple_tools' ),
                        "color" => "#3F729B",
                        "hidden" => true,
                    ],
                    'personal' => [
                        "label" => __( 'Personal', 'disciple_tools' ),
                        "color" => "#9b379b",
                        "description" => "Only I can see this contact.",
                        "icon" => get_template_directory_uri() . "/dt-assets/images/locked.svg",
                    ],
                ],
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
                "name" => sprintf( _x( "Connections to other %s", 'connections to other records', 'disciple_tools' ), __( "Contacts", 'disciple_tools' ) ),
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
                "customizable" => false
            ];
            $fields["contact_email"] = [
                "name" => __( 'Email', 'disciple_tools' ),
                "icon" => get_template_directory_uri() . "/dt-assets/images/email.svg",
                "type" => "communication_channel",
                "tile" => "details",
                "customizable" => false
            ];
            $fields["contact_address"] = [
                "name" => __( 'Address', 'disciple_tools' ),
                "icon" => get_template_directory_uri() . "/dt-assets/images/house.svg",
                "type" => "communication_channel",
                "tile" => "details",
                "customizable" => false
            ];
            $fields["facebook"] = [
                "name" => __( 'Facebook', 'disciple_tools' ),
                "icon" => get_template_directory_uri() . "/dt-assets/images/facebook.svg",
                "hide_domain" => true,
                "type" => "communication_channel",
                "tile" => "details",
                "customizable" => false
            ];
            $fields["twitter"] = [
                "name" => __( 'Twitter', 'disciple_tools' ),
                "icon" => get_template_directory_uri() . "/dt-assets/images/twitter.svg",
                "hide_domain" => true,
                "type" => "communication_channel",
                "tile" => "details",
                "customizable" => false
            ];

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
                    'male'    => [ "label" => __( 'Male', 'disciple_tools' ) ],
                    'female'  => [ "label" => __( 'Female', 'disciple_tools' ) ],
                ],
                'tile'     => 'details',
                "icon" => get_template_directory_uri() . "/dt-assets/images/gender.svg",
            ];


        }
        return $fields;
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

        if ( $post_type === "contacts" && $section === "status" ){
            $contact_fields = DT_Posts::get_post_field_settings( $post_type );
            $post = DT_Posts::get_post( $post_type, GET_THE_ID() );
            ?>
            <div class="reveal" id="contact-type-modal" data-reveal>
                <h3><?php echo esc_html( $contact_fields["type"]["name"] ?? '' )?></h3>
                <p><?php echo esc_html( $contact_fields["type"]["description"] ?? '' )?></p>
                <p><?php esc_html_e( 'Choose an option:', 'disciple_tools' )?></p>

<!--                @todo choose user option permissions-->
                <select id="type-options">
                    <?php
                    foreach ( $contact_fields["type"]["default"] as $option_key => $option ) {
                        if ( !empty( $option["label"] ) ) {
                            $selected = ( $option_key === ( $post["type"]["key"] ?? "" ) ) ? "selected" : "";
                            ?>
                            <option value="<?php echo esc_attr( $option_key ) ?>" <?php echo esc_html( $selected ) ?>> <?php echo esc_html( $option["label"] ?? "" ) ?></option>
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
                //@todo?
                jQuery('#confirm-type-close').on('click', function(){
                    $(this).toggleClass('loading')
                    API.update_post('contacts', <?php echo esc_html( GET_THE_ID() ); ?>, {type:$('#type-options').val()}).then(contactData=>{
                      window.location.reload()
                    }).catch(err => { console.error(err) })
                })
            </script>
        <?php }
    }

    public static function dt_record_admin_actions( $post_type, $post_id ){
        if ( $post_type === "contacts" ){
            $contact = DT_Posts::get_post( $post_type, $post_id );
            ?>
            <li>
                <a data-open="contact-type-modal"><?php echo esc_html( sprintf( _x( "Change %s Type", "Change Record Type", 'disciple_tools' ), DT_Posts::get_post_settings( $post_type )["label_singular"] ) ) ?></a>
            </li>
            <?php
        }
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
                "key" => "all",
                "label" => sprintf( _x( "My %s", 'My records', 'disciple_tools' ), $post_label_plural ),
                "count" => $shared_by_type_counts['total'],
                "order" => 5
            ];
            $filters["filters"][] = [
                'ID' => 'all_contacts',
                'tab' => 'all',
                'name' => _x( "All", 'List Filters', 'disciple_tools' ),
                'query' => [
                    'shared_with' => [ 'me' ],
                    'sort' => 'name',
                ],
                'labels' => [
                    [
                        'id' => 'my_shared',
                        'name' => 'Shared with me'
                    ]
                ],
                "count" => $shared_by_type_counts['total'],
            ];
            $filters["filters"][] = [
                'ID' => 'personal',
                'tab' => 'all',
                'name' => sprintf( _x( "Personal %s", 'Personal records', 'disciple_tools' ), $post_label_plural ),
                'query' => [
                    'type' => [ 'personal' ],
                    'sort' => 'name'
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
                'name' => 'Shared with me',
                'query' => [
                    'shared_with' => [ 'me' ],
                    'sort' => 'name',
                ],
                'labels' => [
                    [
                        'id' => 'my_shared',
                        'name' => 'Shared with me',
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
        if ( is_singular( "contacts" ) ){
            wp_enqueue_script( 'dt_contacts', get_template_directory_uri() . '/dt-contacts/contacts.js', [
                'jquery',
            ], filemtime( get_theme_file_path() . '/dt-contacts/contacts.js' ), true );
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
}
