<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly.

class Disciple_Tools_People_Groups_Base {
    public $post_type = 'peoplegroups';
    public $single_name = 'People Group';
    public $plural_name = 'People Groups';

    public function __construct() {

        //setup post type
        add_action( 'after_setup_theme', [ $this, 'after_setup_theme' ], 100 );
        add_filter( 'dt_set_roles_and_permissions', [ $this, 'dt_set_roles_and_permissions' ], 20, 1 ); //after contacts
        add_filter( 'dt_get_post_type_settings', [ $this, 'dt_get_post_type_settings' ], 20, 2 );

        //setup tiles and fields
        add_action( 'p2p_init', [ $this, 'p2p_init' ] );
        add_filter( 'dt_custom_fields_settings', [ $this, 'dt_custom_fields_settings' ], 10, 2 );
        add_filter( 'dt_details_additional_tiles', [ $this, 'dt_details_additional_tiles' ], 10, 2 );
        add_action( 'dt_details_additional_section', [ $this, 'dt_details_additional_section' ], 20, 2 );

        // hooks
        add_action( 'dt_post_created', [ $this, 'dt_post_created' ], 10, 3 );
        add_filter( 'dt_post_update_fields', [ $this, 'dt_post_update_fields' ], 10, 3 );
        add_filter( 'dt_post_create_fields', [ $this, 'dt_post_create_fields' ], 10, 2 );

        //list
        add_filter( 'dt_user_list_filters', [ $this, 'dt_user_list_filters' ], 10, 2 );
        add_filter( 'dt_filter_access_permissions', [ $this, 'dt_filter_access_permissions' ], 20, 2 );

    }

    public function after_setup_theme() {
        $this->single_name = __( 'People Group', 'disciple_tools' );
        $this->plural_name = __( 'People Groups', 'disciple_tools' );

        if ( class_exists( 'Disciple_Tools_Post_Type_Template' ) ) {
            new Disciple_Tools_Post_Type_Template( $this->post_type, $this->single_name, $this->plural_name );
        }
    }

    public function dt_get_post_type_settings( $settings, $post_type ) {
        if ( $post_type === $this->post_type ) {
            $settings['label_singular'] = $this->single_name;
            $settings['label_plural']   = $this->plural_name;
        }

        return $settings;
    }

    public function dt_set_roles_and_permissions( $expected_roles ) {

        if ( ! isset( $expected_roles['multiplier'] ) ) {
            $expected_roles['multiplier'] = [

                'label'       => __( 'Multiplier', 'disciple-tools-plugin-starter-template' ),
                'description' => 'Interacts with Contacts and Groups',
                'permissions' => []
            ];
        }

        // if the user can access contact they also can access this post type
        foreach ( $expected_roles as $role => $role_value ) {
            if ( isset( $role_value['permissions']['access_contacts'] ) && $role_value['permissions']['access_contacts'] ) {
                $expected_roles[ $role ]['permissions'][ 'access_' . $this->post_type ] = true;
//                $expected_roles[$role]['permissions']['create_' . $this->post_type] = true;
//                $expected_roles[$role]['permissions']['update_' . $this->post_type] = true;
            }
        }

        if ( isset( $expected_roles['administrator'] ) ) {
            $expected_roles['administrator']['permissions'][ 'view_any_' . $this->post_type ]   = true;
            $expected_roles['administrator']['permissions'][ 'update_any_' . $this->post_type ] = true;
            $expected_roles['administrator']['permissions']['edit_peoplegroups']                = true;
        }
        if ( isset( $expected_roles['dt_admin'] ) ) {
            $expected_roles['dt_admin']['permissions'][ 'view_any_' . $this->post_type ]   = true;
            $expected_roles['dt_admin']['permissions'][ 'update_any_' . $this->post_type ] = true;
            $expected_roles['dt_admin']['permissions']['edit_peoplegroups']                = true;
        }

        return $expected_roles;
    }

    public function dt_custom_fields_settings( $fields, $post_type ) {
        if ( $post_type === $this->post_type ) {
            /**
             * Basic Framework Fields
             *
             */
            $fields['tags']               = [
                'name'           => __( 'Tags', 'disciple_tools' ),
                'description'    => _x( 'A useful way to group related items.', 'Optional Documentation', 'disciple_tools' ),
                'type'           => 'tags',
                'default'        => [],
                'tile'           => 'other',
                'custom_display' => true,
                'icon'           => get_template_directory_uri() . "/dt-assets/images/tag.svg",
            ];
            $fields["follow"]             = [
                'name'    => __( 'Follow', 'disciple_tools' ),
                'type'    => 'multi_select',
                'default' => [],
                'section' => 'misc',
                'hidden'  => true
            ];
            $fields["unfollow"]           = [
                'name'    => __( 'Un-Follow', 'disciple_tools' ),
                'type'    => 'multi_select',
                'default' => [],
                'hidden'  => true
            ];
            $fields['tasks']              = [
                'name' => __( 'Tasks', 'disciple_tools' ),
                'type' => 'post_user_meta',
            ];
            $fields["duplicate_data"]     = [
                "name"    => 'Duplicates', //system string does not need translation
                'type'    => 'array',
                'default' => [],
            ];
            $fields['assigned_to']        = [
                'name'           => __( 'Assigned To', 'disciple_tools' ),
                'description'    => __( "Select the main person who is responsible for reporting on this record.", 'disciple_tools' ),
                'type'           => 'user_select',
                'default'        => '',
                'tile'           => 'status',
                'icon'           => get_template_directory_uri() . '/dt-assets/images/assigned-to.svg',
                "show_in_table"  => 16,
                'custom_display' => true,
            ];
            $fields["requires_update"]    = [
                'name'        => __( 'Requires Update', 'disciple_tools' ),
                'description' => '',
                'type'        => 'boolean',
                'default'     => false,
            ];
            $fields['status']             = [
                'name'          => __( 'Status', 'disciple_tools' ),
                'description'   => _x( 'Set the current status.', 'field description', 'disciple_tools' ),
                'type'          => 'key_select',
                'default'       => [
                    'inactive' => [
                        'label'       => __( 'Inactive', 'disciple_tools' ),
                        'description' => _x( 'No longer active.', 'field description', 'disciple_tools' ),
                        'color'       => "#F43636"
                    ],
                    'active'   => [
                        'label'       => __( 'Active', 'disciple_tools' ),
                        'description' => _x( 'Is active.', 'field description', 'disciple_tools' ),
                        'color'       => "#4CAF50"
                    ],
                ],
                'tile'          => '',
                'icon'          => get_template_directory_uri() . '/dt-assets/images/status.svg',
                "default_color" => "#366184",
                "show_in_table" => 10,
            ];
            $fields['contact_count']      = [
                'name'          => __( "Contacts Total", 'disciple_tools' ),
                'type'          => 'number',
                'default'       => '0',
                'show_in_table' => true
            ];
            $fields['contacts']           = [
                'name'           => __( "Contacts", 'disciple_tools' ),
                'type'           => 'connection',
                "post_type"      => 'contacts',
                'tile'           => 'connections',
                "p2p_direction"  => "from",
                "p2p_key"        => $this->post_type . "_to_contacts",
                'icon'           => get_template_directory_uri() . "/dt-assets/images/contact-generation.svg",
                'create-icon'    => get_template_directory_uri() . '/dt-assets/images/add-contact.svg',
                "in_create_form" => true,
            ];
            $fields['group_total']        = [
                'name'          => __( "Groups Total", 'disciple_tools' ),
                'type'          => 'number',
                'default'       => '0',
                'show_in_table' => true
            ];
            $fields['groups']             = [
                'name'           => __( "Groups", 'disciple_tools' ),
                'type'           => 'connection',
                "post_type"      => 'groups',
                "p2p_direction"  => "from",
                "p2p_key"        => $this->post_type . "_to_groups",
                "tile"           => "connections",
                'icon'           => get_template_directory_uri() . "/dt-assets/images/groups.svg",
                'create-icon'    => get_template_directory_uri() . '/dt-assets/images/add-group.svg',
                "in_create_form" => true,
            ];
            $fields['location_grid']      = [
                'name'           => __( 'Locations', 'disciple_tools' ),
                'description'    => _x( 'The general location where this contact is located.', 'Optional Documentation', 'disciple_tools' ),
                'type'           => 'location',
                'mapbox'         => false,
                "in_create_form" => true,
                "tile"           => "details",
                "icon"           => get_template_directory_uri() . "/dt-assets/images/location.svg",
            ];
            $fields['location_grid_meta'] = [
                'name'        => __( 'Locations', 'disciple_tools' ),
                //system string does not need translation
                'description' => _x( 'The general location where this contact is located.', 'Optional Documentation', 'disciple_tools' ),
                'type'        => 'location_meta',
                "tile"        => "details",
                'mapbox'      => false,
                'hidden'      => true,
                "icon"        => get_template_directory_uri() . "/dt-assets/images/location.svg?v=2",
            ];
            $fields["contact_address"]    = [
                "name"         => __( 'Address', 'disciple_tools' ),
                "icon"         => get_template_directory_uri() . "/dt-assets/images/house.svg",
                "type"         => "communication_channel",
                "tile"         => "details",
                'mapbox'       => false,
                "customizable" => false
            ];

            if ( DT_Mapbox_API::get_key() ) {
                $fields["contact_address"]["custom_display"] = true;
                $fields["contact_address"]["mapbox"]         = true;
                unset( $fields["contact_address"]["tile"] );
                $fields["location_grid"]["mapbox"]      = true;
                $fields["location_grid_meta"]["mapbox"] = true;
                $fields["location_grid"]["hidden"]      = true;
                $fields["location_grid_meta"]["hidden"] = false;
            }

            /**
             * Generation and peer connection fields
             */
            $fields["parents"]  = [
                "name"          => __( 'Parents', 'disciple_tools' ),
                'description'   => '',
                "type"          => "connection",
                "post_type"     => $this->post_type,
                "p2p_direction" => "from",
                "p2p_key"       => $this->post_type . "_to_" . $this->post_type,
                'tile'          => 'connections',
                'icon'          => get_template_directory_uri() . '/dt-assets/images/group-parent.svg',
                'create-icon'   => get_template_directory_uri() . '/dt-assets/images/add-group.svg',
            ];
            $fields["peers"]    = [
                "name"          => __( 'Peers', 'disciple_tools' ),
                'description'   => '',
                "type"          => "connection",
                "post_type"     => $this->post_type,
                "p2p_direction" => "any",
                "p2p_key"       => $this->post_type . "_to_peers",
                'tile'          => 'connections',
                'icon'          => get_template_directory_uri() . '/dt-assets/images/group-peer.svg',
                'create-icon'   => get_template_directory_uri() . '/dt-assets/images/add-group.svg',
            ];
            $fields["children"] = [
                "name"          => __( 'Children', 'disciple_tools' ),
                'description'   => '',
                "type"          => "connection",
                "post_type"     => $this->post_type,
                "p2p_direction" => "to",
                "p2p_key"       => $this->post_type . "_to_" . $this->post_type,
                'tile'          => 'connections',
                'icon'          => get_template_directory_uri() . '/dt-assets/images/group-child.svg',
                'create-icon'   => get_template_directory_uri() . '/dt-assets/images/add-group.svg',
            ];
        }

        /**
         * Modify fields for connected post types
         */
        if ( $post_type === "contacts" ) {
            $fields[ $this->post_type ] = [
                "name"          => $this->plural_name,
                "description"   => '',
                "type"          => "connection",
                "post_type"     => $this->post_type,
                "p2p_direction" => "from",
                "p2p_key"       => $this->post_type . "_to_contacts",
                "tile"          => "other",
                'icon'          => get_template_directory_uri() . "/dt-assets/images/group-type.svg",
                'create-icon'   => get_template_directory_uri() . "/dt-assets/images/add-group.svg",
                "show_in_table" => 35
            ];
        }

        return $fields;
    }

    public function p2p_init() {
        /**
         * Group members field
         */
        p2p_register_connection_type(
            [
                'name'      => $this->post_type . "_to_contacts",
                'from'      => 'contacts',
                'to'        => $this->post_type,
                'admin_box' => [
                    'show' => false,
                ],
                'title'     => [
                    'from' => __( 'Contacts', 'disciple_tools' ),
                    'to'   => $this->plural_name,
                ]
            ]
        );
        /**
         * Parent and child connection
         */
        p2p_register_connection_type(
            [
                'name'  => $this->post_type . "_to_" . $this->post_type,
                'from'  => $this->post_type,
                'to'    => $this->post_type,
                'title' => [
                    'from' => $this->plural_name . ' by',
                    'to'   => $this->plural_name,
                ],
            ]
        );
        /**
         * Peer connections
         */
        p2p_register_connection_type( [
            'name' => $this->post_type . "_to_peers",
            'from' => $this->post_type,
            'to'   => $this->post_type,
        ] );
        /**
         * Group People Groups field
         */
//        p2p_register_connection_type(
//            [
//                'name'        => $this->post_type."_to_peoplegroups",
//                'from'        => $this->post_type,
//                'to'          => 'peoplegroups',
//                'title'       => [
//                    'from' => __( 'People Groups', 'disciple_tools' ),
//                    'to'   => $this->plural_name,
//                ]
//            ]
//        );
    }

    public function dt_details_additional_tiles( $tiles, $post_type = "" ) {
        if ( $post_type === $this->post_type ) {
            $tiles["connections"] = [ "label" => __( "Connections", 'disciple_tools' ) ];
            $tiles["other"]       = [ "label" => __( "Other", 'disciple_tools' ) ];
        }

        return $tiles;
    }

    public function dt_details_additional_section( $section, $post_type ) {
        if ( $post_type === $this->post_type && $section === "status" ) {
            $record        = DT_Posts::get_post( $post_type, get_the_ID() );
            $record_fields = DT_Posts::get_post_field_settings( $post_type );
            ?>

            <div class="cell small-12 medium-4">
                <?php render_field_for_display( "status", $record_fields, $record, true ); ?>
            </div>
            <div class="cell small-12 medium-4">
                <div class="section-subheader">
                    <img
                        src="<?php echo esc_url( get_template_directory_uri() ) . '/dt-assets/images/assigned-to.svg' ?>">
                    <?php echo esc_html( $record_fields["assigned_to"]["name"] ) ?>
                    <button class="help-button" data-section="assigned-to-help-text">
                        <img class="help-icon"
                             src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/help.svg' ) ?>"/>
                    </button>
                </div>

                <div class="assigned_to details">
                    <var id="assigned_to-result-container" class="result-container assigned_to-result-container"></var>
                    <div id="assigned_to_t" name="form-assigned_to" class="scrollable-typeahead">
                        <div class="typeahead__container">
                            <div class="typeahead__field">
                                    <span class="typeahead__query">
                                        <input class="js-typeahead-assigned_to input-height"
                                               name="assigned_to[query]"
                                               placeholder="<?php echo esc_html_x( "Search Users", 'input field placeholder', 'disciple_tools' ) ?>"
                                               autocomplete="off">
                                    </span>
                                <span class="typeahead__button">
                                        <button type="button"
                                                class="search_assigned_to typeahead__image_button input-height"
                                                data-id="assigned_to_t">
                                            <img
                                                src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/chevron_down.svg' ) ?>"/>
                                        </button>
                                    </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="cell small-12 medium-4">
                <?php render_field_for_display( "coaches", $record_fields, $record, true ); ?>
            </div>
        <?php }

        if ( $post_type === $this->post_type && $section === "other" ) :
            $fields = DT_Posts::get_post_field_settings( $post_type );
            ?>
            <div class="section-subheader">
                <?php echo esc_html( $fields["tags"]["name"] ) ?>
            </div>
            <div class="tags">
                <var id="tags-result-container" class="result-container"></var>
                <div id="tags_t" name="form-tags" class="scrollable-typeahead typeahead-margin-when-active">
                    <div class="typeahead__container">
                        <div class="typeahead__field">
                            <span class="typeahead__query">
                                <input class="js-typeahead-tags input-height"
                                       name="tags[query]"
                                       placeholder="<?php echo esc_html( sprintf( _x( "Search %s", "Search 'something'", 'disciple_tools' ), $fields["tags"]['name'] ) ) ?>"
                                       autocomplete="off">
                            </span>
                            <span class="typeahead__button">
                                <button type="button" data-open="create-tag-modal"
                                        class="create-new-tag typeahead__image_button input-height">
                                    <img
                                        src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/tag-add.svg' ) ?>"/>
                                </button>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif;

        if ( $post_type === $this->post_type && $section === "relationships" ) {
            $fields = DT_Posts::get_post_field_settings( $post_type );
            $post   = DT_Posts::get_post( $this->post_type, get_the_ID() );
            ?>
            <div class="section-subheader members-header" style="padding-top: 10px;">
                <div style="padding-bottom: 5px; margin-right:10px; display: inline-block">
                    <?php esc_html_e( "Member List", 'disciple_tools' ) ?>
                </div>
                <button type="button" class="create-new-record" data-connection-key="members" style="height: 36px;">
                    <?php echo esc_html__( 'Create', 'disciple_tools' ) ?>
                    <img style="height: 14px; width: 14px"
                         src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/small-add.svg' ) ?>"/>
                </button>
                <button type="button"
                        class="add-new-member">
                    <?php echo esc_html__( 'Select', 'disciple_tools' ) ?>
                    <img style="height: 16px; width: 16px"
                         src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/add-group.svg' ) ?>"/>
                </button>
            </div>
            <div class="members-section" style="margin-bottom:10px">
                <div
                    id="empty-members-list-message"><?php esc_html_e( "To add new members, click on 'Create' or 'Select'.", 'disciple_tools' ) ?></div>
                <div class="member-list">

                </div>
            </div>
            <div class="reveal" id="add-new-group-member-modal" data-reveal style="min-height:500px">
                <h3><?php echo esc_html_x( "Add members from existing contacts", 'Add members modal', 'disciple_tools' ) ?></h3>
                <p><?php echo esc_html_x( "In the 'Member List' field, type the name of an existing contact to add them to this group.", 'Add members modal', 'disciple_tools' ) ?></p>

                <?php render_field_for_display( "members", $fields, $post, false ); ?>

                <div class="grid-x pin-to-bottom">
                    <div class="cell">
                        <hr>
                        <span style="float:right; bottom: 0;">
                    <button class="button" data-close aria-label="Close reveal" type="button">
                        <?php echo esc_html__( 'Close', 'disciple_tools' ) ?>
                    </button>
                </span>
                    </div>
                </div>
                <button class="close-button" data-close aria-label="Close modal" type="button">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php }
    }

    //action when a post has been created
    public function dt_post_created( $post_type, $post_id, $initial_fields ) {
        if ( $post_type === $this->post_type ) {

            /**
             * Action to hook for additional processing after a new record is created by the post type.
             */
            do_action( "dt_'.$this->post_type.'_created", $post_id, $initial_fields );

            $post_array = DT_Posts::get_post( $this->post_type, $post_id, true, false );
            if ( isset( $post_array["assigned_to"] ) ) {
                if ( $post_array["assigned_to"]["id"] ) {
                    DT_Posts::add_shared( $this->post_type, $post_id, $post_array["assigned_to"]["id"], null, false, false, false );
                }
            }
        }
    }

    //filter at the start of post update
    public function dt_post_update_fields( $fields, $post_type, $post_id ) {
        if ( $post_type === $this->post_type ) {
            /**
             * Look for specific fields and do additional processing
             */

            // process assigned to field
            if ( isset( $fields["assigned_to"] ) ) {
                if ( filter_var( $fields["assigned_to"], FILTER_VALIDATE_EMAIL ) ) {
                    $user = get_user_by( "email", $fields["assigned_to"] );
                    if ( $user ) {
                        $fields["assigned_to"] = $user->ID;
                    } else {
                        return new WP_Error( __FUNCTION__, "Unrecognized user", $fields["assigned_to"] );
                    }
                }
                //make sure the assigned to is in the right format (user-1)
                if ( is_numeric( $fields["assigned_to"] ) ||
                     strpos( $fields["assigned_to"], "user" ) === false ) {
                    $fields["assigned_to"] = "user-" . $fields["assigned_to"];
                }
                $user_id = dt_get_user_id_from_assigned_to( $fields["assigned_to"] );
                if ( $user_id ) {
                    DT_Posts::add_shared( $this->post_type, $post_id, $user_id, null, false, true, false );
                }
            }

            // process end date if post is set to inactive
            $post_array = DT_Posts::get_post( $this->post_type, $post_id, true, false );
            if ( isset( $fields["status"] ) && empty( $fields["end_date"] ) && empty( $post_array["end_date"] ) && $fields["status"] === 'inactive' ) {
                $fields["end_date"] = time();
            }
        }

        return $fields;
    }

    // filter at the start of post creation
    public function dt_post_create_fields( $fields, $post_type ) {
        return $fields;
    }

    //build list page filters
    public function dt_user_list_filters( $filters, $post_type ) {
        if ( $post_type === $this->post_type ) {
            $filters["tabs"][] = [
                "key"   => "assigned_to_me",
                "label" => _x( "Assigned to me", 'List Filters', 'disciple_tools' ),
                "count" => 0,
                "order" => 20
            ];

            // add assigned to me filters
            $filters["filters"][] = [
                'ID'    => 'my_all',
                'tab'   => 'assigned_to_me',
                'name'  => _x( "All", 'List Filters', 'disciple_tools' ),
                'query' => [
                    'assigned_to' => [ 'me' ],
                    'sort'        => 'status'
                ],
                "count" => 0,
            ];

            $filters["tabs"][] = [
                "key"   => "all",
                "label" => _x( "All", 'List Filters', 'disciple_tools' ),
                "count" => 0,
                "order" => 10
            ];

            // add assigned to me filters
            $filters["filters"][] = [
                'ID'    => 'all',
                'tab'   => 'all',
                'name'  => _x( "All", 'List Filters', 'disciple_tools' ),
                'query' => [
                    'sort' => '-post_date'
                ],
                "count" => 0
            ];
        }

        return $filters;
    }

    public function dt_filter_access_permissions( $permissions, $post_type ) {
        if ( $post_type === $this->post_type ) {
            if ( DT_Posts::can_view_all( $post_type ) ) {
                $permissions = [];
            }
        }

        return $permissions;
    }

}

