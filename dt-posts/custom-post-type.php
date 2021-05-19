<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly.

class Disciple_Tools_Post_Type_Template {

    public $post_type;
    public $singular;
    public $plural;
    public $search_items;

    public function __construct( string $post_type, string $singular, string $plural ) {
        $this->post_type = $post_type;
        $this->singular = $singular;
        $this->plural = $plural;
        $this->search_items = sprintf( _x( "Search %s", "Search 'something'", 'disciple_tools' ), $this->plural );
        add_action( 'init', [ $this, 'register_post_type' ] );
        add_action( 'init', [ $this, 'rewrite_init' ] );
        add_filter( 'post_type_link', [ $this, 'permalink' ], 1, 3 );
        add_filter( 'desktop_navbar_menu_options', [ $this, 'add_navigation_links' ], 20 );
        add_filter( 'dt_nav_add_post_menu', [ $this, 'dt_nav_add_post_menu' ], 10, 1 );
        add_filter( 'dt_templates_for_urls', [ $this, 'add_template_for_url' ] );
        add_filter( 'dt_get_post_type_settings', [ $this, 'dt_get_post_type_settings' ], 10, 2 );
        add_filter( 'dt_registered_post_types', [ $this, 'dt_registered_post_types' ], 10, 1 );
        add_filter( 'dt_details_additional_section_ids', [ $this, 'dt_details_additional_section_ids' ], 10, 2 );
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
            'show_in_menu'          => true,
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
            'show_in_nav_menus'     => true,
            'can_export'            => false,
            'exclude_from_search'   => true,
            'show_in_rest'          => false
        ];

        register_post_type( $this->post_type, $defaults );

    }

    public function rewrite_init(){
        add_rewrite_rule( $this->post_type . '/([0-9]+)?$', 'index.php?post_type=' . $this->post_type . '&p=$matches[1]', 'top' );
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
                "link" => site_url( "/$this->post_type/" ),
                "label" => $this->plural,
                'icon' => '',
                'hidden' => false,
                'submenu' => []
            ];
        }
        return $tabs;
    }

    public function dt_nav_add_post_menu( $links ){
        if ( current_user_can( "create_" . $this->post_type ) ){
            $links[] = [
                'label' => sprintf( esc_html__( 'New %s', 'disciple_tools' ), esc_html( $this->singular ) ),
                'link' => esc_url( site_url( '/' ) ) . esc_html( $this->post_type ) . '/new',
                'icon' => get_template_directory_uri() . "/dt-assets/images/circle-add-plus.svg",
                'hidden' => false,
            ];
        }
        return $links;
    }

    public function add_template_for_url( $template_for_url ){
        $template_for_url[$this->post_type] = 'archive-template.php';
        $template_for_url[$this->post_type . '/new'] = 'template-new-post.php';
        return $template_for_url;
    }

    public static function get_base_post_type_fields(){
        $fields = [];
        $fields["name"] = [
            'name' => __( "Name", 'disciple_tools' ),
            'type' => 'text',
            'tile' => 'details',
            'in_create_form' => true,
            'required' => true,
            'icon' => get_template_directory_uri() . "/dt-assets/images/name.svg",
            "show_in_table" => 5
        ];
        $fields["last_modified"] =[
            'name' => __( 'Last Modified', 'disciple_tools' ),
            'type' => 'date',
            'default' => 0,
            'customizable' => false,
            "show_in_table" => 100
        ];
        $fields["post_date"] =[
            'name' => __( 'Creation Date', 'disciple_tools' ),
            'type' => 'date',
            'default' => 0,
            'customizable' => false,
        ];
        $fields['favorite'] = [
            'name'        => __( 'Favorite', 'disciple_tools' ),
            'type'        => 'boolean',
            'default'     => false,
            'private'     => true,
            "show_in_table" => 6,
            "icon" => get_template_directory_uri() . "/dt-assets/images/star.svg"
        ];
        //tasks, location, ppl group? follow, unfollow?
        return $fields;
    }

    /**
     * Get the settings for the custom fields.
     *
     * @param bool $with_deleted_options
     * @param bool $load_from_cache
     *
     * @return mixed
     */
    public function get_custom_fields_settings( $with_deleted_options = false, $load_from_cache = true ) {
        return DT_Posts::get_post_field_settings( $this->post_type, $load_from_cache, $with_deleted_options );
    }

    public function dt_get_post_type_settings( $settings, $post_type ){
        if ( $post_type === $this->post_type ){
            $cached = wp_cache_get( $post_type . "_type_settings" );
            if ( $cached ){
                return $cached;
            }
            $fields = $this->get_custom_fields_settings();
            $channels = [];
            foreach ( $fields as $field_key => $field_value ){
                if ( $field_value["type"] === "communication_channel" ){
                    $field_value["label"] = $field_value["name"];
                    $channels[str_replace( "contact_", "", $field_key )] = $field_value;
                }
            }
            $s = [
                'fields' => $fields,
                'channels' => $channels,
                'connection_types' => array_keys( array_filter( $fields, function ( $a ) {
                    return $a["type"] === "connection";
                } ) ),
                'label_singular' => $this->singular,
                'label_plural' => $this->plural,
                'post_type' => $this->post_type
            ];
            $settings = dt_array_merge_recursive_distinct( $settings, $s );

            wp_cache_set( $post_type . "_type_settings", $settings );
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
}

/**
 * Build default filter available on all post type list pages
 */
add_filter( "dt_user_list_filters", "base_dt_user_list_filters", 100, 2 );
function base_dt_user_list_filters( $filters, $post_type ){
    // check of the all tab is declared
    $tab_names = array_map( function ( $f ){
        return $f["key"];
    }, $filters["tabs"] );
    if ( in_array( 'all', $tab_names, true ) ){

        $filter_ids = array_map( function ( $f ){
            return $f["ID"];
        }, $filters["filters"] );

        // add favorite posts filter to all abb
        if ( !in_array( 'favorite', $filter_ids ) ){
            $post_type_settings = DT_Posts::get_post_settings( $post_type );
            $filters["filters"][] = [
                'ID' => 'favorite',
                'tab' => 'all',
                'name' => sprintf( _x( "Favorite %s", 'Favorite Contacts', 'disciple_tools' ), $post_type_settings["label_plural"] ),
                'query' => [
                    "fields" => [ "favorite" => [ "1" ] ],
                    'sort' => "name"
                ],
                'labels' => [
                    [ "id" => "1", "name" => __( "Favorite", "disciple_tools" ) ]
                ]
            ];
        }
        // add recently viewed filter to all tab
        if ( !in_array( 'recent', $filter_ids, true ) ){
            $filters["filters"][] = [
                'ID' => 'recent',
                'tab' => 'all',
                'name' => __( "My Recently Viewed", 'disciple_tools' ),
                'query' => [
                    'dt_recent' => true
                ],
                'labels' => [
                    [ "id" => 'recent', 'name' => __( "Last 30 viewed", 'disciple_tools' ) ]
                ]
            ];
        }
    }
    return $filters;
}
