<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly.

class Disciple_Tools_Post_Type_Template {

    public $post_type;
    public $singular;
    public $plural;
    public $search_items;
    public $hidden;

    public function __construct( string $post_type, string $singular, string $plural ) {
        $post_type_updates = get_option( 'dt_custom_post_types', [] );
        $this->post_type = $post_type;
        $this->singular = $post_type_updates[$this->post_type]['label_singular'] ?? $singular;
        $this->plural = $post_type_updates[$this->post_type]['label_plural'] ?? $plural;
        $this->search_items = sprintf( _x( 'Search %s', "Search 'something'", 'disciple_tools' ), $this->plural );
        $this->hidden = $post_type_updates[$this->post_type]['hidden'] ?? false;
        add_action( 'init', [ $this, 'register_post_type' ] );
        add_action( 'init', [ $this, 'rewrite_init' ] );
        add_filter( 'post_type_link', [ $this, 'permalink' ], 1, 3 );
        add_filter( 'desktop_navbar_menu_options', [ $this, 'add_navigation_links' ], 20 );
        add_filter( 'dt_nav_add_post_menu', [ $this, 'dt_nav_add_post_menu' ], 10, 1 );
        add_filter( 'dt_templates_for_urls', [ $this, 'add_template_for_url' ] );
        add_filter( 'dt_get_post_type_settings', [ $this, 'dt_get_post_type_settings' ], 10, 4 );
        add_filter( 'dt_get_post_type_settings', [ $this, 'dt_get_post_type_settings_after' ], 1000, 4 );
        add_filter( 'dt_registered_post_types', [ $this, 'dt_registered_post_types' ], 10, 1 );
        add_filter( 'dt_details_additional_section_ids', [ $this, 'dt_details_additional_section_ids' ], 10, 2 );
        add_action( 'init', [ $this, 'register_p2p_connections' ], 50, 0 );
        add_filter( 'dt_capabilities', [ $this, 'dt_capabilities' ], 50, 1 );
        add_filter( 'dt_set_roles_and_permissions', [ $this, 'dt_set_roles_and_permissions' ], 11, 1 );
        add_filter( 'dt_record_icon', [ $this, 'dt_record_icon' ], 100, 3 );

    }

    public function register_post_type(){
        $labels = [
            'name'                  => $this->plural,
            'singular_name'         => $this->singular,
            'menu_name'             => $this->plural,
            'search_items'          => $this->search_items,
        ];
        $rewrite = [
            'slug'       => $this->post_type,
            'with_front' => true,
            'pages'      => true,
            'feeds'      => false,
        ];
        $capabilities = [
            'create_posts'        => 'do_not_allow',
            'edit_post'           => 'dt_all_admin_' . $this->post_type, // needed for bulk edit
            'read_post'           => 'do_not_allow',
            'delete_post'         => 'dt_all_admin_' . $this->post_type, // delete individual post
            'delete_others_posts' => 'do_not_allow',
            'delete_posts'        => 'dt_all_admin_' . $this->post_type, // bulk delete posts
            'edit_posts'          => 'dt_all_admin_' . $this->post_type, //menu link in WP Admin
            'edit_others_posts'   => 'do_not_allow',
            'publish_posts'       => 'do_not_allow',
            'read_private_posts'  => 'do_not_allow',
        ];
        $defaults = [
            'label'                 => $this->singular,
            'labels'                => $labels,
            'public'                => false,
            'publicly_queryable'    => true,
            'show_ui'               => true,
            'show_in_menu'          => false,
            'query_var'             => false,
            'show_in_admin_bar'     => false,
            'rewrite'               => $rewrite,
            'capabilities'          => $capabilities,
            'capability_type'       => $this->post_type,
            'has_archive'           => true, //$archive_slug,
            'hierarchical'          => false,
            'supports'              => [ 'title' ],
            'menu_position'         => 5,
            'menu_icon'             => 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIyNCIgaGVpZ2h0PSIyNCIgdmlld0JveD0iMCAwIDI0IDI0Ij48ZyBjbGFzcz0ibmMtaWNvbi13cmFwcGVyIiBmaWxsPSIjZmZmZmZmIj48cGF0aCBmaWxsPSIjZmZmZmZmIiBkPSJNOSwxMmMyLjc1NywwLDUtMi4yNDMsNS01VjVjMC0yLjc1Ny0yLjI0My01LTUtNVM0LDIuMjQzLDQsNXYyQzQsOS43NTcsNi4yNDMsMTIsOSwxMnoiPjwvcGF0aD4gPHBhdGggZmlsbD0iI2ZmZmZmZiIgZD0iTTE1LjQyMywxNS4xNDVDMTQuMDQyLDE0LjYyMiwxMS44MDYsMTQsOSwxNHMtNS4wNDIsMC42MjItNi40MjQsMS4xNDZDMS4wMzUsMTUuNzI5LDAsMTcuMjMzLDAsMTguODg2VjI0IGgxOHYtNS4xMTRDMTgsMTcuMjMzLDE2Ljk2NSwxNS43MjksMTUuNDIzLDE1LjE0NXoiPjwvcGF0aD4gPHJlY3QgZGF0YS1jb2xvcj0iY29sb3ItMiIgeD0iMTYiIHk9IjMiIGZpbGw9IiNmZmZmZmYiIHdpZHRoPSI4IiBoZWlnaHQ9IjIiPjwvcmVjdD4gPHJlY3QgZGF0YS1jb2xvcj0iY29sb3ItMiIgeD0iMTYiIHk9IjgiIGZpbGw9IiNmZmZmZmYiIHdpZHRoPSI4IiBoZWlnaHQ9IjIiPjwvcmVjdD4gPHJlY3QgZGF0YS1jb2xvcj0iY29sb3ItMiIgeD0iMTkiIHk9IjEzIiBmaWxsPSIjZmZmZmZmIiB3aWR0aD0iNSIgaGVpZ2h0PSIyIj48L3JlY3Q+PC9nPjwvc3ZnPg==',
            'show_in_nav_menus'     => false,
            'can_export'            => true,
            'exclude_from_search'   => true,
            'show_in_rest'          => false
        ];

        // Adjust defaults accordingly, prior to registration.
        $defaults = apply_filters( 'dt_register_post_type_defaults', $defaults, $this->post_type );
        register_post_type( $this->post_type, $defaults );
    }

    public function rewrite_init(){
        add_rewrite_rule( $this->post_type . '/([0-9]+)?$', 'index.php?post_type=' . $this->post_type . '&p=$matches[1]', 'top' );
    }

    public function dt_set_roles_and_permissions( $expected_roles ){
        $custom_post_types = get_option( 'dt_custom_post_types', [] );
        if ( empty( $custom_post_types[$this->post_type]['roles'] ) && isset( $custom_post_types[$this->post_type]['is_custom'] ) && $custom_post_types[$this->post_type]['is_custom'] ){
            if ( isset( $expected_roles['administrator'] ) ){
                $expected_roles['administrator']['permissions']['access_' . $this->post_type] = true;
                $expected_roles['administrator']['permissions']['view_any_' . $this->post_type] = true;
                $expected_roles['administrator']['permissions']['update_any_' . $this->post_type] = true;
                $expected_roles['administrator']['permissions']['dt_all_admin_' . $this->post_type] = true;
                $expected_roles['administrator']['permissions']['delete_any_' . $this->post_type] = true;
                $expected_roles['administrator']['permissions']['create_' . $this->post_type] = true;
            }
        }

        return $expected_roles;
    }

    /**
     * Run on activation.
     */
    public function activation() {
        $this->flush_rewrite_rules();
    }

    /**
     * Flush the rewrite rules
     */
    private function flush_rewrite_rules() {
        $this->register_post_type();
        flush_rewrite_rules();
    } // End flush_rewrite_rules()

    public function permalink( $post_link, $post ) {
        if ( $post->post_type === $this->post_type ) {
            return home_url( $this->post_type . '/' . $post->ID . '/' );
        } else {
            return $post_link;
        }
    }

    public function add_navigation_links( $tabs ) {
        if ( current_user_can( 'access_' . $this->post_type ) ) {
            $tabs[$this->post_type] = [
                'link' => site_url( "/$this->post_type/" ),
                'label' => $this->plural,
                'icon' => '',
                'hidden' => $this->hidden,
                'submenu' => []
            ];
        }
        return $tabs;
    }

    public function dt_nav_add_post_menu( $links ){
        if ( current_user_can( 'create_' . $this->post_type ) ){
            $links[] = [
                'label' => sprintf( esc_html__( 'New %s', 'disciple_tools' ), esc_html( $this->singular ) ),
                'link' => esc_url( site_url( '/' ) ) . esc_html( $this->post_type ) . '/new',
                'icon' => get_template_directory_uri() . '/dt-assets/images/circle-add-green.svg',
                'hidden' => $this->hidden,
            ];
        }
        return $links;
    }

    public function add_template_for_url( $template_for_url ){
        $template_for_url[$this->post_type] = 'archive-template.php';
        $template_for_url[$this->post_type . '/new'] = 'template-new-post.php';
        $template_for_url[$this->post_type . '/new-bulk'] = 'template-new-bulk-post.php';
        $template_for_url[$this->post_type . '/mergedetails'] = 'template-merge-post-details.php';
        return $template_for_url;
    }

    public static function get_base_post_type_fields(){
        $fields = [];
        $fields['name'] = [
            'name' => __( 'Name', 'disciple_tools' ),
            'type' => 'text',
            'tile' => 'details',
            'in_create_form' => true,
            'required' => true,
            'icon' => get_template_directory_uri() . '/dt-assets/images/name.svg',
            'show_in_table' => 5
        ];
        $fields['record_picture'] = [
            'name' => __( 'Picture', 'disciple_tools' ),
            'type' => 'image',
            'show_in_table' => 1,
            'hidden' => !class_exists( 'DT_Storage' ) || !DT_Storage::is_enabled()
        ];
        $fields['last_modified'] =[
            'name' => __( 'Last Modified', 'disciple_tools' ),
            'type' => 'date',
            'default' => 0,
            'icon' => get_template_directory_uri() . '/dt-assets/images/calendar-range.svg',
            'customizable' => false,
            'show_in_table' => 100
        ];
        $fields['post_date'] =[
            'name' => __( 'Creation Date', 'disciple_tools' ),
            'type' => 'date',
            'default' => 0,
            'icon' => get_template_directory_uri() . '/dt-assets/images/calendar-plus.svg',
            'customizable' => false,
        ];
        $fields['favorite'] = [
            'name'        => __( 'Favorite', 'disciple_tools' ),
            'type'        => 'boolean',
            'default'     => false,
            'private'     => true,
            'show_in_table' => 6,
            'icon' => get_template_directory_uri() . '/dt-assets/images/star.svg'
        ];
        $fields['tags'] = [
            'name'        => __( 'Tags', 'disciple_tools' ),
            'description' => _x( 'A useful way to group related items.', 'Optional Documentation', 'disciple_tools' ),
            'type'        => 'tags',
            'default'     => [],
            'tile'        => 'other',
            'icon' => get_template_directory_uri() . '/dt-assets/images/tag.svg',
        ];
        $fields['follow'] = [
            'name'        => __( 'Follow', 'disciple_tools' ),
            'type'        => 'multi_select',
            'default'     => [],
            'hidden'      => true
        ];
        $fields['unfollow'] = [
            'name'        => __( 'Un-Follow', 'disciple_tools' ),
            'type'        => 'multi_select',
            'default'     => [],
            'hidden'      => true
        ];
        $fields['tasks'] = [
            'name' => __( 'Tasks', 'disciple_tools' ),
            'type' => 'task',
            'icon' => get_template_directory_uri() . '/dt-assets/images/calendar-clock.svg',
            'private' => true
        ];
        //notes field used for adding comments when creating a record
        $fields['notes'] = [
            'name' => 'Notes',
            'type' => 'array',
            'hidden' => true
        ];

        // add location fields
        $fields['location_grid'] = [
            'name'        => __( 'Locations', 'disciple_tools' ),
            'description' => _x( 'The general location where this record is located.', 'Optional Documentation', 'disciple_tools' ),
            'type'        => 'location',
            'mapbox'    => false,
            'in_create_form' => true,
            'tile' => 'details',
            'icon' => get_template_directory_uri() . '/dt-assets/images/location.svg?v=2',
            'mode' => 'normal'
        ];
        $fields['location_grid_meta'] = [
            'name'        => __( 'Locations or Address', 'disciple_tools' ),
            'type'        => 'location_meta',
            'tile'      => 'details',
            'mapbox'    => false,
            'hidden' => true,
            'in_create_form' => true,
            'icon' => get_template_directory_uri() . '/dt-assets/images/map-marker-multiple.svg?v=2'
        ];

        if ( DT_Mapbox_API::get_key() ) {
            $fields['location_grid']['mode'] = 'geolocation';
            $fields['location_grid']['mapbox'] = true;
            $fields['location_grid']['hidden'] = true;
            $fields['location_grid_meta']['mapbox'] = true;
            $fields['location_grid_meta']['hidden'] = false;
        }

        return $fields;
    }

    public function dt_get_post_type_settings( $settings, $post_type, $return_cache = true, $load_tags = false ){
        if ( $post_type === $this->post_type ){
            $cached = wp_cache_get( $post_type . '_type_settings' );
            if ( $cached && $return_cache ){
                return $cached;
            }
            $fields = DT_Posts::get_post_field_settings( $this->post_type, false, false, $load_tags );
            $channels = [];
            foreach ( $fields as $field_key => $field_value ){
                if ( $field_value['type'] === 'communication_channel' ){
                    $field_value['label'] = $field_value['name'];
                    $channels[str_replace( 'contact_', '', $field_key )] = $field_value;
                }
            }
            $s = [
                'fields' => $fields,
                'channels' => $channels,
                'connection_types' => array_keys( array_filter( $fields, function ( $a ) {
                    return $a['type'] === 'connection';
                } ) ),
                'magic_link_apps' => dt_get_registered_types( true ),
                'label_singular' => $this->singular,
                'label_plural' => $this->plural,
                'post_type' => $this->post_type,
                'hidden' => $this->hidden
            ];
            $settings = dt_array_merge_recursive_distinct( $settings, $s );

            wp_cache_set( $post_type . '_type_settings', $settings );
        }
        return $settings;
    }

    public function dt_get_post_type_settings_after( $settings, $post_type ){
        if ( $post_type === $this->post_type ){
            $post_type_updates = get_option( 'dt_custom_post_types', [] );
            if ( !empty( $post_type_updates[$post_type]['label_singular'] ) ){
                $settings['label_singular'] = $post_type_updates[$post_type]['label_singular'];
            }
            if ( !empty( $post_type_updates[$post_type]['label_plural'] ) ){
                $settings['label_plural'] = $post_type_updates[$post_type]['label_plural'];
            }
            $settings['is_custom'] = $post_type_updates[$post_type]['is_custom'] ?? false;
        }
        return $settings;
    }

    public function dt_registered_post_types( $post_types ){
        $post_types[] = $this->post_type;
        return $post_types;
    }

    public function dt_details_additional_section_ids( $sections, $post_type ){
//        if ( $post_type === $this->post_type ) {
//            $sections[] = 'details';
//        }
        return $sections;
    }


    /**
     * register p2p connections dynamically based on the connection field declaration
     */
    public function register_p2p_connections(){
        $fields = DT_Posts::get_post_field_settings( $this->post_type, false );
        foreach ( $fields as $field_key => &$field ){
            if ( !isset( $field['name'] ) ){
                $field['name'] = $field_key; //set a field name so integration can depend on it.
            }
            //register a connection if it is not set
            if ( $field['type'] === 'connection' && isset( $field['p2p_key'], $field['post_type'] ) ){
                $p2p_type = p2p_type( $field['p2p_key'] );
                if ( $p2p_type === false ){
                    if ( $field['p2p_direction'] === 'to' ){
                        p2p_register_connection_type(
                            [
                                'name'        => $field['p2p_key'],
                                'to'          => $this->post_type,
                                'from'        => $field['post_type']
                            ]
                        );
                    } else {
                        p2p_register_connection_type(
                            [
                                'name'        => $field['p2p_key'],
                                'from'        => $this->post_type,
                                'to'          => $field['post_type']
                            ]
                        );
                    }
                }
            }
        }
    }
    /**
     * Declare Default D.T post roles
     */
    public function dt_capabilities( $capabilities ){
        $capabilities['access_' . $this->post_type] = [
            'source' => $this->plural,
            'label' => 'View and Manage',
            'description' => sprintf( 'The user can access the UI for %s and manage their %s', $this->plural, $this->plural ),
            'post_type' => $this->post_type
        ];
//        $capabilities['update_'  . $this->post_type] = [
//            'source' => $this->plural,
//            'description' => 'The user can edit existing ' . $this->plural,
//        ];
        $capabilities['create_'  . $this->post_type] = [
            'source' => $this->plural,
            'label' => 'Create Records',
            'description' => sprintf( 'The user can create %s', $this->plural ),
            'post_type' => $this->post_type
        ];
        $capabilities['view_any_'  . $this->post_type] = [
            'source' => $this->plural,
            'label' => 'View All',
            'description' => sprintf( 'The user can view any %s', $this->singular ),
            'post_type' => $this->post_type
        ];
        $capabilities['update_any_'  . $this->post_type] = [
            'source' => $this->plural,
            'label' => 'Update Any',
            'description' => sprintf( 'The user can update any %s', $this->singular ),
            'post_type' => $this->post_type
        ];
        $capabilities['delete_any_'  . $this->post_type] = [
            'source' => $this->plural,
            'label' => 'Delete Any',
            'description' => sprintf( 'The user can delete any %s', $this->singular ),
            'post_type' => $this->post_type
        ];
//        $capabilities['dt_all_admin_' . $this->post_type] = [
//            'source' => $this->plural,
//            'label' => 'Update in WP Admin',
//            'description' => sprintf( 'Access and update %s on the WP Admin site.', $this->plural ),
//            'post_type' => $this->post_type
//        ];
        $capabilities['list_all_' .$this->post_type ] = [
            'source' => $this->plural,
            'label' => 'Preview All',
            'description' => sprintf( 'The user can list all %s records, but not view record details. Useful in typeahead searches.', $this->singular ),
            'post_type' => $this->post_type
        ];

        return $capabilities;
    }

    public function dt_record_icon( $icon, $post_type, $post_id ){
        if ( $icon ){
            return $icon;
        }
        $icon = 'mdi mdi-image-outline';

        return $icon;
    }
}

add_action( 'after_setup_theme', function (){
    $custom_post_types = get_option( 'dt_custom_post_types', [] );
    $already_registered = apply_filters( 'dt_registered_post_types', [] );

    foreach ( $custom_post_types as $post_type_key => $post_type ){
        if ( ( $post_type['is_custom'] ?? false ) && !in_array( $post_type_key, $already_registered, true ) ){
            new Disciple_Tools_Post_Type_Template( $post_type_key, $post_type['label_singular'] ?? $post_type_key, $post_type['label_plural'] ?? $post_type_key );
        }
    }
}, 200 );

/**
 * Set default list view permissions
 * only need to register this hook once
 */
add_filter( 'dt_filter_access_permissions', function ( $permissions, $post_type ){
    if ( DT_Posts::can_view_all( $post_type ) ){
        $permissions = [];
    }
    return $permissions;
}, 5, 2 );

/**
 * Build default filter available on all post type list pages
 */
add_filter( 'dt_user_list_filters', 'base_dt_user_list_filters', 100, 2 );
function base_dt_user_list_filters( $filters, $post_type ){
    // check of the all tab is declared
    $tab_names = array_map( function ( $f ){
        return $f['key'];
    }, $filters['tabs'] );

    if ( !in_array( 'all', $tab_names ) && !in_array( 'default', $tab_names ) ){
        $filters['tabs'][] = [
            'key' => 'all',
            'label' => __( 'Default Filters', 'disciple_tools' ),
            'query' => [
                'sort' => '-post_date'
            ],
            'order' => 10
        ];
        $tab_names[] = 'all';
    }

    if ( in_array( 'all', $tab_names, true ) ){

        $filter_ids = array_map( function ( $f ){
            return $f['ID'];
        }, $filters['filters'] );

        // add favorite posts filter to all abb
        if ( !in_array( 'default', $filter_ids ) && !in_array( 'all', $filter_ids ) ){
            $post_label_plural = DT_Posts::get_post_settings( $post_type )['label_plural'];
            $filters['filters'][] = [
                'ID' => 'all',
                'tab' => 'all',
                'name' => sprintf( _x( 'All %s', 'All records', 'disciple_tools' ), $post_label_plural ),
                'query' => [
                    'sort' => '-post_date'
                ],
            ];
        }
        // add favorite posts filter to all abb
        if ( !in_array( 'favorite', $filter_ids ) ){
            $post_type_settings = DT_Posts::get_post_settings( $post_type );
            $filters['filters'][] = [
                'ID' => 'favorite',
                'tab' => 'all',
                'name' => sprintf( _x( 'Favorite %s', 'Favorite Contacts', 'disciple_tools' ), $post_type_settings['label_plural'] ),
                'query' => [
                    'fields' => [ 'favorite' => [ '1' ] ],
                    'sort' => 'name'
                ],
                'labels' => [
                    [ 'id' => '1', 'name' => __( 'Favorite', 'disciple_tools' ) ]
                ]
            ];
        }
        // add recently viewed filter to all tab
        if ( !in_array( 'recent', $filter_ids, true ) ){
            $filters['filters'][] = [
                'ID' => 'recent',
                'tab' => 'all',
                'name' => __( 'My Recently Viewed', 'disciple_tools' ),
                'query' => [
                    'dt_recent' => true
                ],
                'labels' => [
                    [ 'id' => 'recent', 'name' => __( 'Last 30 viewed', 'disciple_tools' ) ]
                ]
            ];
        }
    }
    return $filters;
}
