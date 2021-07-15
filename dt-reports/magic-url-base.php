<?php

abstract class DT_Magic_Url_Base {
    public $magic = false;
    public $parts = false;

    public $post_type = '';
    public $type = '';
    public $type_name = '';
    public $page_title = '';
    public $type_actions = [
        '' => "Manage",
    ];

    public $module = ""; // lets a magic url be a module as well

    public $allowed_scripts = [];
    public $allowed_styles = [];

    public function __construct() {

        // register type
        $this->magic = new DT_Magic_URL( $this->root );
        add_filter( 'dt_magic_url_register_types', [ $this, 'dt_magic_url_register_types' ], 10, 1 );
        // register REST and REST access
        add_filter( 'dt_allow_rest_access', [ $this, 'authorize_url' ], 10, 1 );

        // fail if not valid url
        $this->parts = $this->magic->parse_url_parts();
        if ( !$this->parts ){
            return;
        }

        // fail if does not match type
        if ( $this->type !== $this->parts['type'] ){
            return;
        }

        add_filter( 'dt_blank_access', [ $this, '_has_access' ] );

        add_filter( "dt_blank_title", [ $this, "page_tab_title" ] );
        // register url and access
        add_filter( 'dt_templates_for_urls', [ $this, 'register_url' ], 199, 1 );
        add_filter( 'dt_allow_non_login_access', function (){
            return true;
        }, 100, 1 );
        add_action( 'wp_print_scripts', [ $this, 'print_scripts' ], 1500 );
        add_action( 'wp_print_styles', [ $this, 'print_styles' ], 1500 );
    }

    public function check_parts_match(){
        if ( isset( $this->parts["post_id"], $this->parts["root"], $this->parts["type"] ) ){
            if ( $this->type === $this->parts["type"] && $this->root === $this->parts["root"] && !empty( $this->parts["post_id"] ) ){
                return true;
            }
        }
        return false;
    }

    public function _has_access() : bool {
        $parts = $this->parts;

        // test 1 : correct url root and type
        if ( $parts ){ // parts returns false
            return true;
        }

        return false;
    }

    public function page_tab_title( $title ){
        return $this->page_title;
    }

    public function dt_magic_url_register_types( array $types ) : array {
        if ( ! isset( $types[$this->root] ) ) {
            $types[$this->root] = [];
        }
        $types[$this->root][$this->type] = [
            'name' => $this->type_name,
            'root' => $this->root,
            'type' => $this->type,
            'meta_key' => $this->root . '_' . $this->type . '_magic_key',
            'actions' => $this->type_actions,
            'post_type' => $this->post_type,
        ];
        return $types;
    }

    public function register_url( $template_for_url ){
        $parts = $this->parts;

        // test 1 : correct url root and type
        if ( ! $parts ){ // parts returns false
            return $template_for_url;
        }

        // test 2 : only base url requested
        if ( empty( $parts['public_key'] ) ){ // no public key present
            $template_for_url[ $parts['root'] . '/'. $parts['type'] ] = 'template-blank.php';
            return $template_for_url;
        }

        // test 3 : no specific action requested
        if ( empty( $parts['action'] ) ){ // only root public key requested
            $template_for_url[ $parts['root'] . '/'. $parts['type'] . '/' . $parts['public_key'] ] = 'template-blank.php';
            return $template_for_url;
        }

        // test 4 : valid action requested
        $actions = $this->magic->list_actions( $parts['type'] );
        if ( isset( $actions[ $parts['action'] ] ) ){
            $template_for_url[ $parts['root'] . '/'. $parts['type'] . '/' . $parts['public_key'] . '/' . $parts['action'] ] = 'template-blank.php';
        }

        return $template_for_url;
    }


    /**
     * Open default restrictions for access to registered endpoints
     * @param $authorized
     * @return bool
     */
    public function authorize_url( $authorized ){
        if ( isset( $_SERVER['REQUEST_URI'] ) && strpos( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ), $this->root . '/v1/'.$this->type ) !== false ) {
            $authorized = true;
        }
        return $authorized;
    }

    public function print_scripts(){
        // @link /disciple-tools-theme/dt-assets/functions/enqueue-scripts.php
        $allowed_js = array_merge(
            $this->allowed_scripts,
            [
                'jquery',
                'jquery-core',
                'jquery-ui',
                'lodash',
                'lodash-core',
                'site-js',
                'shared-functions',
                'moment',
                'datepicker'
            ]
        );

        global $wp_scripts;

        if ( isset( $wp_scripts ) ){
            foreach ( $wp_scripts->queue as $key => $item ){
                if ( ! in_array( $item, $allowed_js ) ){
                    unset( $wp_scripts->queue[$key] );
                }
            }
        }
        if ( isset( $wp_scripts ) ){
            foreach ( $wp_scripts->registered as $key => $item ){
                if ( ! in_array( $key, $allowed_js ) ){
                    unset( $wp_scripts->registered[$key] );
                }
            }
        }
        unset( $wp_scripts->registered['mapbox-search-widget']->extra['group'] ); //lets the mapbox geocoder work
    }

    public function print_styles(){
        // @link /disciple-tools-theme/dt-assets/functions/enqueue-scripts.php
        $allowed_css = array_merge(
            $this->allowed_styles,
            [
                'foundation-css',
                'site-css',
                'jquery-ui-site-css',
                'datepicker-css',
            ]
        );

        global $wp_styles;
        if ( isset( $wp_styles ) ) {
            foreach ($wp_styles->queue as $key => $item) {
                if ( !in_array( $item, $allowed_css )) {
                    unset( $wp_styles->queue[$key] );
                }
            }
        }
    }

    protected function check_module_enabled_and_prerequisites(){
        $modules = dt_get_option( 'dt_post_type_modules' );
        $module_enabled = isset( $modules[$this->module]["enabled"] ) ? $modules[$this->module]["enabled"] : false;
        foreach ( $modules[$this->module]["prerequisites"] as $prereq ){
            $prereq_enabled = isset( $modules[$prereq]["enabled"] ) ? $modules[$prereq]["enabled"] : false;
            if ( !$prereq_enabled ){
                return false;
            }
        }
        return $module_enabled;
    }

}
