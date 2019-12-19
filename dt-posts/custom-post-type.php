<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly.

class Disciple_Tools_Post_Type_Template {

    public $post_type;
    public $singular;
    public $plural;

    public function __construct( string $post_type, string $singular, string $plural ) {
        $this->post_type = $post_type;
        $this->singular = $singular;
        $this->plural = $plural;
        add_action( 'init', [ $this, 'register_post_type' ] );
        add_action( 'init', [ $this, 'rewrite_init' ] );
        add_filter( 'post_type_link', [ $this, 'permalink' ], 1, 3 );
        add_action( 'dt_top_nav_desktop', [ $this, 'add_menu_link' ] );
        add_filter( 'dt_templates_for_urls', [ $this, 'add_template_for_url' ] );
        add_action( 'dt_nav_add_post_menu', [ $this, 'dt_nav_add_post_menu' ] );
        add_filter( 'dt_get_post_type_settings', [ $this, 'dt_get_post_type_settings' ], 10, 2 );
        add_filter( 'dt_registered_post_types', [ $this, 'dt_registered_post_types' ], 10, 1 );
        add_filter( 'dt_details_additional_section_ids', [ $this, 'dt_details_additional_section_ids' ], 10, 2 );
    }

    public function register_post_type(){
        $labels = [
            'name'                  => $this->plural,
            'singular_name'         => $this->singular,
            'menu_name'             => $this->plural,
            'search_items'          => sprintf( _x( "Search %s", "Search 'something'", 'disciple_tools' ), $this->plural ),
        ];
        $rewrite = [
            'slug'       => $this->post_type,
            'with_front' => true,
            'pages'      => true,
            'feeds'      => false,
        ];
        $capabilities = [
//            'create_posts'        => 'do_not_allow', //@todo reenable
            'edit_post'           => 'access_' . $this->post_type,
            'read_post'           => 'access_' . $this->post_type,
            'delete_post'         => 'delete_any_' . $this->post_type,
            'delete_others_posts' => 'delete_any_' . $this->post_type,
            'delete_posts'        => 'delete_any_' . $this->post_type,
            'edit_posts'          => 'access_' . $this->post_type,
            'edit_others_posts'   => 'update_any_' . $this->post_type,
            'publish_posts'       => 'create_' . $this->post_type,
            'read_private_posts'  => 'view_any_' . $this->post_type,
        ];
        $defaults = [
            'label'                 => $this->singular,
            'labels'                => $labels,
            'public'                => true,
            'publicly_queryable'    => true,
            'show_ui'               => true,
            'show_in_menu'          => true,
            'query_var'             => true,
            'rewrite'               => $rewrite,
            'capabilities'          => $capabilities,
            'capability_type'       => $this->post_type,
            'has_archive'           => true, //$archive_slug,
            'hierarchical'          => false,
            'supports'              => [ 'title', 'comments' ],
            'menu_position'         => 5,
            'menu_icon'             => 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIyNCIgaGVpZ2h0PSIyNCIgdmlld0JveD0iMCAwIDI0IDI0Ij48ZyBjbGFzcz0ibmMtaWNvbi13cmFwcGVyIiBmaWxsPSIjZmZmZmZmIj48cGF0aCBmaWxsPSIjZmZmZmZmIiBkPSJNOSwxMmMyLjc1NywwLDUtMi4yNDMsNS01VjVjMC0yLjc1Ny0yLjI0My01LTUtNVM0LDIuMjQzLDQsNXYyQzQsOS43NTcsNi4yNDMsMTIsOSwxMnoiPjwvcGF0aD4gPHBhdGggZmlsbD0iI2ZmZmZmZiIgZD0iTTE1LjQyMywxNS4xNDVDMTQuMDQyLDE0LjYyMiwxMS44MDYsMTQsOSwxNHMtNS4wNDIsMC42MjItNi40MjQsMS4xNDZDMS4wMzUsMTUuNzI5LDAsMTcuMjMzLDAsMTguODg2VjI0IGgxOHYtNS4xMTRDMTgsMTcuMjMzLDE2Ljk2NSwxNS43MjksMTUuNDIzLDE1LjE0NXoiPjwvcGF0aD4gPHJlY3QgZGF0YS1jb2xvcj0iY29sb3ItMiIgeD0iMTYiIHk9IjMiIGZpbGw9IiNmZmZmZmYiIHdpZHRoPSI4IiBoZWlnaHQ9IjIiPjwvcmVjdD4gPHJlY3QgZGF0YS1jb2xvcj0iY29sb3ItMiIgeD0iMTYiIHk9IjgiIGZpbGw9IiNmZmZmZmYiIHdpZHRoPSI4IiBoZWlnaHQ9IjIiPjwvcmVjdD4gPHJlY3QgZGF0YS1jb2xvcj0iY29sb3ItMiIgeD0iMTkiIHk9IjEzIiBmaWxsPSIjZmZmZmZmIiB3aWR0aD0iNSIgaGVpZ2h0PSIyIj48L3JlY3Q+PC9nPjwvc3ZnPg==',
            'show_in_admin_bar'     => true,
            'show_in_nav_menus'     => true,
            'can_export'            => true,
            'exclude_from_search'   => true,
            'show_in_rest'          => false
        ];

        register_post_type( $this->post_type, $defaults );



        $roles = [ 'dispatcher', 'administrator', 'dt_admin', 'multiplier', 'marketer', 'strategist' ];
        foreach ( $roles as $role ) {
            $role = get_role( $role );
            $role->add_cap( 'access_' . $this->post_type );
            $role->add_cap( 'create_' . $this->post_type );
            if ( $role != "multiplier" ){
                $role->add_cap( 'view_any_' . $this->post_type );
                $role->add_cap( 'update_any_' . $this->post_type );
                $role->add_cap( 'delete_any_' . $this->post_type );
            }
        }
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

    public function add_menu_link(){
        if ( current_user_can( 'access_' . $this->post_type ) ) : ?>
            <li><a href="<?php echo esc_url( site_url( '/' . $this->post_type . '/' ) ); ?>"><?php echo esc_html( $this->plural ); ?></a></li>
        <?php endif;
    }

    public function add_template_for_url( $template_for_url ){
        $template_for_url[$this->post_type] = 'archive-template.php';
        $template_for_url[$this->post_type . '/new'] = 'template-new-post.php';
        return $template_for_url;
    }

    public function dt_nav_add_post_menu(){
        ?>
        <li>
            <a href="<?php echo esc_url( site_url( '/' ) ) . esc_html( $this->post_type ) . '/new'; ?>"><?php echo sprintf( esc_html__( 'New %s', 'disciple_tools' ), esc_html( $this->singular ) ) ?></a>
        </li>
        <?php
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

        $cached = wp_cache_get( $this->post_type . "_field_settings" );
        if ( $load_from_cache && $cached ){
            return $cached;
        }
//        $fields = $this->get_contact_field_defaults( $post_id, $include_current_post );
        $fields = [];
        $fields = apply_filters( 'dt_custom_fields_settings', $fields, $this->post_type );
        foreach ( $fields as $field_key => $field ){
            if ( $field["type"] === "key_select" || $field["type"] === "multi_select" ){
                foreach ( $field["default"] as $option_key => $option_value ){
                    if ( !is_array( $option_value )){
                        $fields[$field_key]["default"][$option_key] = [ "label" => $option_value ];
                    }
                }
            }
        }
        $custom_field_options = dt_get_option( "dt_field_customizations" );
        if ( isset( $custom_field_options[$this->post_type] )){
            foreach ( $custom_field_options[$this->post_type] as $key => $field ){
                $field_type = $field["type"] ?? $fields[$key]["type"] ?? "";
                if ( $field_type ) {
                    if ( !isset( $fields[ $key ] ) ) {
                        $fields[ $key ] = $field;
                    } else {
                        if ( isset( $field["name"] ) ) {
                            $fields[ $key ]["name"] = $field["name"];
                        }
                        if ( isset( $field["tile"] ) ) {
                            $fields[ $key ]["tile"] = $field["tile"];
                        }
                        if ( $field_type === "key_select" || $field_type === "multi_select" ) {
                            if ( isset( $field["default"] ) ) {
                                $fields[ $key ]["default"] = array_replace_recursive( $fields[ $key ]["default"], $field["default"] );
                            }
                        }
                    }
                    if ( $field_type === "key_select" || $field_type === "multi_select" ) {
                        if ( isset( $field["order"] ) ) {
                            $with_order = [];
                            foreach ( $field["order"] as $ordered_key ) {
                                $with_order[ $ordered_key ] = [];
                            }
                            foreach ( $fields[ $key ]["default"] as $option_key => $option_value ) {
                                $with_order[ $option_key ] = $option_value;
                            }
                            $fields[ $key ]["default"] = $with_order;
                        }
                    }
                }
            }
        }
        if ( $with_deleted_options === false ){
            foreach ( $fields as $field_key => $field ){
                if ( $field["type"] === "key_select" || $field["type"] === "multi_select" ){
                    foreach ( $field["default"] as $option_key => $option_value ){
                        if ( isset( $option_value["deleted"] ) && $option_value["deleted"] == true ){
                            unset( $fields[$field_key]["default"][$option_key] );
                        }
                    }
                }
            }
        }

        wp_cache_set( $this->post_type . "_field_settings", $fields );
        return $fields;
    } // End get_custom_fields_settings()

    public function dt_get_post_type_settings( $settings, $post_type ){
        if ( $post_type === $this->post_type){
            $fields = $this->get_custom_fields_settings();
            $settings = [
                'fields' => $fields,
                'channels' => [],
                'connection_types' => array_keys( array_filter( $fields, function ( $a ) {
                    return $a["type"] === "connection";
                } ) ),
                'label_singular' => $this->singular,
                'label_plural' => $this->plural,
                'post_type' => $this->post_type
            ];
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
