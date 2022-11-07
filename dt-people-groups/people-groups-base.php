<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly.

class Disciple_Tools_People_Groups_Base {
    public $post_type = 'peoplegroups';
    public $single_name = 'People Group';
    public $plural_name = 'People Groups';
    public $tile_key = 'jp';

    public function __construct() {

        //setup post type
        add_filter( 'dt_set_roles_and_permissions', [ $this, 'dt_set_roles_and_permissions' ], 20, 1 ); //after contacts
        add_filter( 'dt_get_post_type_settings', [ $this, 'dt_get_post_type_settings' ], 20, 2 );
        add_action( 'after_setup_theme', [ $this, 'after_setup_theme' ], 100 );
        add_filter( 'dt_register_post_type_defaults', [ $this, 'dt_register_post_type_defaults' ], 20, 2 );

        //setup tiles and fields
        add_filter( 'dt_custom_fields_settings', [ $this, 'dt_custom_fields_settings' ], 10, 2 );
        add_filter( 'dt_details_additional_tiles', [ $this, 'dt_details_additional_tiles' ], 10, 2 );

        //list
        add_filter( 'dt_user_list_filters', [ $this, 'dt_user_list_filters' ], 10, 2 );
        add_filter( 'dt_filter_access_permissions', [ $this, 'dt_filter_access_permissions' ], 20, 2 );

        //Hooks
        add_filter( 'dt_nav', [ $this, 'dt_nav_filter' ], 10, 1 );
        add_action( 'dt_details_additional_section', [ $this, 'dt_details_additional_section' ], 40, 2 );
    }

    public function dt_register_post_type_defaults( $defaults, $post_type ) {
        if ( $post_type == $this->post_type ) {
            $capabilities = [
                'read_post'              => 'manage_dt',
                'edit_post'              => 'manage_dt',
                'delete_post'            => 'manage_dt',
                'edit_posts'             => 'manage_dt',
                'edit_others_posts'      => 'manage_dt',
                'publish_posts'          => 'manage_dt',
                'read_private_posts'     => 'manage_dt',
                'delete_others_posts'    => 'manage_dt',
                'delete_posts'           => 'manage_dt',
                'delete_published_posts' => 'manage_dt',
            ];

            $defaults['capabilities'] = $capabilities;
            $defaults['show_in_menu'] = true;
            $defaults['menu_icon']    = 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIyNCIgaGVpZ2h0PSIyNCIgdmlld0JveD0iMCAwIDI0IDI0Ij48ZyBjbGFzcz0ibmMtaWNvbi13cmFwcGVyIiBmaWxsPSIjZmZmZmZmIj48cGF0aCBkYXRhLWNvbG9yPSJjb2xvci0yIiBmaWxsPSIjZmZmZmZmIiBkPSJNMTIsMEM5LjU0MiwwLDcsMS44MDIsNyw0LjgxN2MwLDIuNzE2LDMuODY5LDYuNDg2LDQuMzEsNi45MDdMMTIsMTIuMzgybDAuNjktMC42NTkgQzEzLjEzMSwxMS4zMDMsMTcsNy41MzMsMTcsNC44MTdDMTcsMS44MDIsMTQuNDU4LDAsMTIsMHogTTEyLDdjLTEuMTA1LDAtMi0wLjg5Ni0yLTJjMC0xLjEwNSwwLjg5NS0yLDItMnMyLDAuODk1LDIsMiBDMTQsNi4xMDQsMTMuMTA1LDcsMTIsN3oiPjwvcGF0aD4gPHBhdGggZmlsbD0iI2ZmZmZmZiIgZD0iTTkuODg0LDE5LjQ5OUM5LjAyMywxOC44MTUsNy41NjMsMTgsNS41LDE4cy0zLjUyMywwLjgxNS00LjM4MywxLjQ5OEMwLjQwNywyMC4wNjEsMCwyMC45MTMsMCwyMS44MzZWMjRoMTEgdi0yLjE2NEMxMSwyMC45MTMsMTAuNTkzLDIwLjA2MSw5Ljg4NCwxOS40OTl6Ij48L3BhdGg+IDxjaXJjbGUgZmlsbD0iI2ZmZmZmZiIgY3g9IjUuNSIgY3k9IjEzLjUiIHI9IjMuNSI+PC9jaXJjbGU+IDxwYXRoIGZpbGw9IiNmZmZmZmYiIGQ9Ik0yMi44ODQsMTkuNDk5QzIyLjAyMywxOC44MTUsMjAuNTYzLDE4LDE4LjUsMThzLTMuNTIzLDAuODE1LTQuMzgzLDEuNDk4IEMxMy40MDcsMjAuMDYxLDEzLDIwLjkxMywxMywyMS44MzZWMjRoMTF2LTIuMTY0QzI0LDIwLjkxMywyMy41OTMsMjAuMDYxLDIyLjg4NCwxOS40OTl6Ij48L3BhdGg+IDxjaXJjbGUgZmlsbD0iI2ZmZmZmZiIgY3g9IjE4LjUiIGN5PSIxMy41IiByPSIzLjUiPjwvY2lyY2xlPjwvZz48L3N2Zz4=';
        }

        return $defaults;
    }

    public function dt_details_additional_section( $section, $post_type ) {
        if ( $section == $this->tile_key ) {
            $post_id = get_the_ID();
            $post = DT_Posts::get_post( $post_type, $post_id, false );

            if ( ! empty( $post ) && ! is_wp_error( $post ) && ! empty( $post['jp_PeopleID3'] ) ) {
                ?>
                <div class="section-subheader">
                    Joshua Project Link <img class="dt-icon" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/open-link.svg' ) ?>"/>
                </div>
                <a href="https://joshuaproject.net/people_groups/<?php echo esc_html( $post['jp_PeopleID3'] ); ?>"
                   target="_blank">
                   https://joshuaproject.net/people_groups/<?php echo esc_html( $post['jp_PeopleID3'] ); ?>
                </a>
                <?php
            }
        }
    }

    public function dt_nav_filter( $navigation_array ) {
        $is_hidden = ! get_option( Disciple_Tools_People_Groups::$option_key_settings_display_tab );

        if ( isset( $navigation_array['main'], $navigation_array['main'][ $this->post_type ] ) ) {
            $navigation_array['main'][ $this->post_type ]['hidden'] = $is_hidden;
        }

        if ( isset( $navigation_array['admin'], $navigation_array['admin']['add_new'] ) ) {
            $updated_submenus = [];
            foreach ( $navigation_array['admin']['add_new']['submenu'] ?? [] as $submenu ) {
                if ( strpos( $submenu['link'], '/' . $this->post_type . '/' ) > 0 ) {
                    $submenu['hidden'] = $is_hidden;
                }
                $updated_submenus[] = $submenu;
            }

            if ( ! empty( $updated_submenus ) ) {
                $navigation_array['admin']['add_new']['submenu'] = $updated_submenus;
            }
        }

        return $navigation_array;
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
            $settings['label_singular'] = __( 'People Group', 'disciple_tools' );
            $settings['label_plural']   = __( 'People Groups', 'disciple_tools' );
        }

        return $settings;
    }

    public function dt_set_roles_and_permissions( $expected_roles ) {

        if ( ! isset( $expected_roles['multiplier'] ) ) {
            $expected_roles['multiplier'] = [
                'label'       => __( 'Multiplier', 'disciple-tools-plugin-starter-template' ),
                'description' => 'Interacts with Contacts and Groups',
                'permissions' => [
                    'list_all_' . $this->post_type => true,
                    'view_any_' . $this->post_type => true
                ]
            ];
        }

        // if the user can access contact they also can access this post type
        foreach ( $expected_roles as $role => $role_value ) {
            if ( isset( $role_value['permissions']['access_contacts'] ) && $role_value['permissions']['access_contacts'] ) {
                $expected_roles[ $role ]['permissions'][ 'access_' . $this->post_type ]   = true;
                $expected_roles[ $role ]['permissions'][ 'list_all_' . $this->post_type ] = true;
                $expected_roles[ $role ]['permissions'][ 'view_any_' . $this->post_type ] = true;
            }
        }

        if ( isset( $expected_roles['administrator'] ) ) {
            $expected_roles['administrator']['permissions'][ 'list_all_' . $this->post_type ]   = true;
            $expected_roles['administrator']['permissions'][ 'create_' . $this->post_type ]     = true;
            $expected_roles['administrator']['permissions'][ 'view_any_' . $this->post_type ]   = true;
            $expected_roles['administrator']['permissions'][ 'update_any_' . $this->post_type ] = true;
            $expected_roles['administrator']['permissions']['edit_peoplegroups']                = true;
        }
        if ( isset( $expected_roles['dt_admin'] ) ) {
            $expected_roles['dt_admin']['permissions'][ 'list_all_' . $this->post_type ]   = true;
            $expected_roles['dt_admin']['permissions'][ 'create_' . $this->post_type ]     = true;
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
            $fields['requires_update']    = [
                'name'        => __( 'Requires Update', 'disciple_tools' ),
                'description' => '',
                'type'        => 'boolean',
                'default'     => false,
            ];
            $fields['contacts']           = [
                'name'           => __( 'Contacts', 'disciple_tools' ),
                'type'           => 'connection',
                'post_type'      => 'contacts',
                'tile'           => 'connections',
                'p2p_direction'  => 'to',
                'p2p_key'        => 'contacts_to_peoplegroups',
                'icon'           => get_template_directory_uri() . '/dt-assets/images/contact-generation.svg',
                'create-icon'    => get_template_directory_uri() . '/dt-assets/images/add-contact.svg',
                'in_create_form' => true,
                'connection_count_field' => [ 'post_type' => 'peoplegroups', 'field_key' => 'contact_count', 'connection_field' => 'contacts' ]
            ];
            $fields['contact_count']      = [
                'name'          => __( 'Contacts Total', 'disciple_tools' ),
                'type'          => 'number',
                'default'       => '0',
                'tile'          => 'connections',
                'show_in_table' => true
            ];
            $fields['groups']             = [
                'name'           => __( 'Groups', 'disciple_tools' ),
                'type'           => 'connection',
                'post_type'      => 'groups',
                'p2p_direction'  => 'to',
                'p2p_key'        => 'groups_to_peoplegroups',
                'tile'           => 'connections',
                'icon'           => get_template_directory_uri() . '/dt-assets/images/groups.svg',
                'create-icon'    => get_template_directory_uri() . '/dt-assets/images/add-group.svg',
                'in_create_form' => true,
                'connection_count_field' => [ 'post_type' => 'peoplegroups', 'field_key' => 'group_total', 'connection_field' => 'groups' ]
            ];
            $fields['group_total']        = [
                'name'          => __( 'Groups Total', 'disciple_tools' ),
                'type'          => 'number',
                'default'       => '0',
                'tile'          => 'connections',
                'show_in_table' => true
            ];
            $fields['location_grid']      = [
                'name'           => __( 'Locations', 'disciple_tools' ),
                'description'    => _x( 'The general location where this contact is located.', 'Optional Documentation', 'disciple_tools' ),
                'type'           => 'location',
                'mapbox'         => false,
                'in_create_form' => true,
                'tile'           => 'details',
                'icon'           => get_template_directory_uri() . '/dt-assets/images/location.svg',
            ];
            $fields['location_grid_meta'] = [
                'name'        => __( 'Locations', 'disciple_tools' ),
                //system string does not need translation
                'description' => _x( 'The general location where this contact is located.', 'Optional Documentation', 'disciple_tools' ),
                'type'        => 'location_meta',
                'tile'        => 'details',
                'mapbox'      => false,
                'hidden'      => true,
                'icon'        => get_template_directory_uri() . '/dt-assets/images/location.svg?v=2',
            ];
            $fields['contact_address']    = [
                'name'         => __( 'Address', 'disciple_tools' ),
                'icon'         => get_template_directory_uri() . '/dt-assets/images/house.svg',
                'type'         => 'communication_channel',
                'tile'         => 'details',
                'mapbox'       => false,
                'customizable' => false
            ];
            $fields['last_modified'] = [
                'name'          => __( 'Last Modified', 'disciple_tools' ),
                'type'          => 'date',
                'default'       => 0,
                'icon'          => get_template_directory_uri() . '/dt-assets/images/calendar-range.svg',
                'customizable'  => false,
                'show_in_table' => false
            ];

            if ( DT_Mapbox_API::get_key() ) {
                $fields['contact_address']['custom_display'] = true;
                $fields['contact_address']['mapbox']         = true;
                unset( $fields['contact_address']['tile'] );
                $fields['location_grid']['mapbox']      = true;
                $fields['location_grid_meta']['mapbox'] = true;
                $fields['location_grid']['hidden']      = true;
                $fields['location_grid_meta']['hidden'] = false;
            }

            /**
             * Generation and peer connection fields
             */
//            $fields["parents"]  = [
//                "name"          => __( 'Parents', 'disciple_tools' ),
//                'description'   => '',
//                "type"          => "connection",
//                "post_type"     => $this->post_type,
//                "p2p_direction" => "from",
//                "p2p_key"       => $this->post_type . "_to_" . $this->post_type,
//                'tile'          => 'connections',
//                'icon'          => get_template_directory_uri() . '/dt-assets/images/group-parent.svg',
//                'create-icon'   => get_template_directory_uri() . '/dt-assets/images/add-group.svg',
//            ];
//            $fields["peers"]    = [
//                "name"          => __( 'Peers', 'disciple_tools' ),
//                'description'   => '',
//                "type"          => "connection",
//                "post_type"     => $this->post_type,
//                "p2p_direction" => "any",
//                "p2p_key"       => $this->post_type . "_to_peers",
//                'tile'          => 'connections',
//                'icon'          => get_template_directory_uri() . '/dt-assets/images/group-peer.svg',
//                'create-icon'   => get_template_directory_uri() . '/dt-assets/images/add-group.svg',
//            ];
//            $fields["children"] = [
//                "name"          => __( 'Children', 'disciple_tools' ),
//                'description'   => '',
//                "type"          => "connection",
//                "post_type"     => $this->post_type,
//                "p2p_direction" => "to",
//                "p2p_key"       => $this->post_type . "_to_" . $this->post_type,
//                'tile'          => 'connections',
//                'icon'          => get_template_directory_uri() . '/dt-assets/images/group-child.svg',
//                'create-icon'   => get_template_directory_uri() . '/dt-assets/images/add-group.svg',
//            ];


            $fields['tags']['tile'] = 'details';

            /**
             * Joshua Project Fields
             */
            $fields['jp_Population']          = [
                'name'          => __( 'Population', 'disciple_tools' ),
                'type'          => 'number',
                'default'       => '0',
                'show_in_table' => true,
                'tile'          => $this->tile_key
            ];
            $fields['jp_ROP3']                = [
                'name'          => __( 'People Group Code', 'disciple_tools' ),
                'type'          => 'number',
                'default'       => '0',
                'show_in_table' => false,
                'tile'          => $this->tile_key
            ];
            $fields['jp_PeopleID3']           = [
                'name'          => __( 'People ID', 'disciple_tools' ),
                'type'          => 'number',
                'default'       => '0',
                'show_in_table' => false,
                'tile'          => $this->tile_key
            ];
            $fields['jp_PrimaryLanguageName'] = [
                'name'          => __( 'Primary Language', 'disciple_tools' ),
                'type'          => 'text',
                'default'       => '',
                'show_in_table' => true,
                'tile'          => $this->tile_key
            ];
            $fields['jp_Ctry']                = [
                'name'          => __( 'Country', 'disciple_tools' ),
                'type'          => 'text',
                'default'       => '',
                'show_in_table' => true,
                'tile'          => $this->tile_key
            ];

        }

        return $fields;
    }

    public function dt_details_additional_tiles( $tiles, $post_type = '' ) {
        if ( $post_type === $this->post_type ) {
            $tiles['connections']     = [ 'label' => __( 'Connections', 'disciple_tools' ) ];
//            $tiles["other"]           = [ "label" => __( "Other", 'disciple_tools' ) ];
            $tiles[ $this->tile_key ] = [ 'label' => __( 'Joshua Project', 'disciple_tools' ) ];
            unset( $tiles['status'] );
        }

        return $tiles;
    }

    //build list page filters
    public function dt_user_list_filters( $filters, $post_type ) {
        if ( $post_type === $this->post_type ) {
            $listed_posts = DT_Posts::list_posts( $post_type, [] );
            $all_count    = 0;
            if ( ! is_wp_error( $listed_posts ) ) {
                $all_count = $listed_posts['total'] ?? count( $listed_posts['posts'] );
            }
            $filters['tabs'][] = [
                'key'   => 'all',
                'label' => _x( 'All', 'List Filters', 'disciple_tools' ),
                'count' => $all_count,
                'order' => 10
            ];

            // add assigned to me filters
            $filters['filters'][] = [
                'ID'    => 'all',
                'tab'   => 'all',
                'name'  => _x( 'All', 'List Filters', 'disciple_tools' ),
                'query' => [
                    'sort' => '-post_date'
                ],
                'count' => $all_count
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

