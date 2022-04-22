<?php
if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

abstract class DT_Magic_Url_Base {
    public $magic = false;
    public $parts = false;

    public $post_type = '';
    public $type = '';
    public $type_name = '';
    private $meta_key;
    public $page_title = '';
    public $page_description = '';
    public $type_actions = [
        '' => "Manage",
    ];
    public $show_bulk_send = false; // enables bulk send of magic links from list page
    public $show_app_tile = false; // enables addition to "app" tile sharing features

    public $module = ""; // Lets a magic url be a module as well
    public $instance_id = ""; // Allows having multiple versions of the same magic link for a user. Creating different meta_keys.
    public $meta = []; // Allows for instance specific data.
    public $translatable = [ 'query' ]; // Order of translatable flags to be checked. Translate on first hit..!

    public function __construct() {

        // check for an instance_id in the magic_link url
        $id = $this->fetch_incoming_link_param( 'id' );
        $this->instance_id = ( ! empty( $id ) ) ? $id : '';

        // register type
        $this->magic = new DT_Magic_URL( $this->root );
        add_filter( 'dt_magic_url_register_types', [ $this, 'dt_magic_url_register_types' ], 10, 1 );
        // register REST and REST access
        add_filter( 'dt_allow_rest_access', [ $this, 'authorize_url' ], 10, 1 );
        // add send and tiles
        add_filter( 'dt_settings_apps_list', [ $this, 'dt_settings_apps_list' ], 10, 1 );

        // fail if not valid url
        $this->parts = $this->magic->parse_url_parts();
        if ( !$this->parts ){

            return;
        }

        // fail if does not match type
        if ( $this->type !== $this->parts['type'] ){
            return;
        }

        // register url and access
        add_filter( 'dt_blank_access', [ $this, '_has_access' ] ); // gives access once above tests are passed
        add_filter( 'dt_templates_for_urls', [ $this, 'register_url' ], 199, 1 ); // registers url as valid once tests are passed
        add_filter( 'dt_allow_non_login_access', function (){ // allows non-logged in visit
            return true;
        }, 100, 1 );
        add_filter( "dt_blank_title", [ $this, "page_tab_title" ] ); // adds basic title to browser tab
        add_action( 'wp_print_scripts', [ $this, 'print_scripts' ], 5 ); // authorizes scripts
        add_action( 'wp_print_footer_scripts', [ $this, 'print_scripts' ], 5 ); // authorizes scripts
        add_action( 'wp_print_styles', [ $this, 'print_styles' ], 1500 ); // authorizes styles

        add_action( 'dt_blank_head', [ $this, '_header' ] );
        add_action( 'dt_blank_footer', [ $this, '_footer' ] );

        // determine language locale to be adopted
        $this->determine_language_locale( $this->parts );
    }

    /**
     * Switch to default DT translation text domain
     *
     * @return void
     */
    public function hard_switch_to_default_dt_text_domain(): void {
        unload_textdomain( "disciple_tools" );
        load_theme_textdomain( 'disciple_tools', get_template_directory() . '/dt-assets/translation' );
    }

    /**
     * Extract incoming link specific parameters; E.g. instance id...
     *
     * @param $param
     *
     * @return string
     */
    public function fetch_incoming_link_param( $param ): string {
        if ( isset( $_SERVER['REQUEST_URI'] ) ) {
            parse_str( parse_url( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ), PHP_URL_QUERY ), $link_params );

            return $link_params[ $param ] ?? '';
        }

        return '';
    }

    public function fetch_incoming_user_lang( $parts = [] ): string {
        if ( ! empty( $parts['post_type'] ) && ! empty( $parts['post_id'] ) ) {
            if ( $parts['post_type'] === 'user' ) {
                return get_user_locale( $parts['post_id'] );
            }
        }

        return '';
    }

    public function fetch_incoming_contact_lang( $parts = [] ): string {
        if ( ! empty( $parts['post_type'] ) && ! empty( $parts['post_id'] ) ) {
            if ( $parts['post_type'] === 'contacts' ) {
                $languages = get_post_meta( $parts['post_id'], 'languages', false );
                if ( ! empty( $languages ) && ! is_wp_error( $languages ) ) {
                    foreach ( $languages as $lang ) {

                        // First, ensure $lang is not already within required locale shape
                        if ( strpos( $lang, "_" ) !== false ) {
                            return $lang;
                        }

                        // Next, attempt to locate corresponding default locale within global languages list
                        $global_lang_list = dt_get_global_languages_list();
                        if ( ! empty( $global_lang_list ) && isset( $global_lang_list[ $lang ], $global_lang_list[ $lang ]['default_locale'] ) ) {
                            return $global_lang_list[ $lang ]['default_locale'];
                        }

                        // If not found, then attempt to locate within available languages list
                        foreach ( dt_get_available_languages() ?? [] as $avail_lang ) {
                            if ( isset( $avail_lang['language'] ) && $avail_lang['language'] === $lang ) {
                                return $avail_lang['language'];
                            }
                        }
                    }
                }
            }
        }

        return '';
    }

    /**
     * Determine language locale to be adopted; based on translatable flags
     *
     * @param array $parts
     *
     * @return void
     */
    public function determine_language_locale( array $parts = [] ): void {

        $lang           = null;
        $flag_satisfied = false;

        // Determine language locale to be adopted
        foreach ( $this->translatable ?? [] as $flag ) {
            if ( ! $flag_satisfied ) {
                switch ( $flag ) {
                    case 'query':
                        $lang = $this->fetch_incoming_link_param( 'lang' );
                        break;
                    case 'user':
                        $lang = $this->fetch_incoming_user_lang( $parts );
                        break;
                    case 'contact':
                        $lang = $this->fetch_incoming_contact_lang( $parts );
                        break;
                }
                $flag_satisfied = ! empty( $lang );
            }
        }

        // If determined, associate with relevant hook
        if ( ! empty( $lang ) ) {
            add_filter( 'determine_locale', function ( $locale ) use ( $lang ) {
                $lang_code = sanitize_text_field( wp_unslash( $lang ) );
                if ( ! empty( $lang_code ) ) {
                    return apply_filters( 'ml_locale_change', $lang_code );
                }

                return $locale;
            } );
        }
    }

    /**
     * Test for core parts elements
     * @note    Use the true/false to include or exclude testing for the post_id in the registered magic link type. Test for
 *              post_id if building magic link from a contact, group, etc. Don't test if building magic link for a user or
     *          non-post_type based link.
     *
     * @note    Primarily used in 'extends' classes for a progress check inside the construct. See stater plugin / magic link
     * @return bool
     */
    public function check_parts_match( $test_post_id = true ){
        if ( $test_post_id ) {
            if ( isset( $this->parts["post_id"], $this->parts["root"], $this->parts["type"] ) ){
                if ( $this->type === $this->parts["type"] && $this->root === $this->parts["root"] && !empty( $this->parts["post_id"] ) ){
                    return true;
                }
            }
        } else {
            if ( isset( $this->parts["root"], $this->parts["type"] ) ){
                if ( $this->type === $this->parts["type"] && $this->root === $this->parts["root"] ){
                    return true;
                }
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

    /**
     * Builds page title for browser tab
     * @note Copy function to 'extends' class to override or modify
     * @return string
     */
    public function page_tab_title(){
        return $this->page_title;
    }

    /**
     * Builds registered magic link
     * @param array $types
     * @return array
     */
    public function dt_magic_url_register_types( array $types ): array {
        if ( ! isset( $types[ $this->root ] ) ) {
            $types[ $this->root ] = [];
        }

        $meta_key_appendage                  = ( ! empty( $this->instance_id ) ) ? '_' . $this->instance_id : '';
        $this->meta_key                      = $this->root . '_' . $this->type . '_magic_key' . $meta_key_appendage;
        $types[ $this->root ][ $this->type ] = [
            'name'           => $this->type_name,
            'root'           => $this->root,
            'type'           => $this->type,
            'meta_key'       => $this->meta_key,
            'actions'        => $this->type_actions,
            'post_type'      => $this->post_type,
            'instance_id'    => $this->instance_id,
            'show_bulk_send' => $this->show_bulk_send,
            'show_app_tile'  => $this->show_app_tile,
            'key'            => $this->root . '_' . $this->type . '_magic_key',
            'url_base'       => $this->root . '/' . $this->type,
            'label'          => $this->page_title,
            'description'    => $this->page_description,
            'meta'           => $this->meta
        ];

        return $types;
    }

    /**
     * Tests the url and if it matches as an approved magic link it loads the appropriate template.
     * @param $template_for_url
     * @return mixed
     */
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
     * Used as an alternate to register_url, primarily for root home page applications
     */
    public function theme_redirect() {
        $path = get_theme_file_path( 'template-blank.php' );
        include( $path );
        die();
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

    /**
     * Authorizes scripts allowed to load in magic link
     *
     * Controls the linked scripts loaded into the header.
     * @note This overrides standard DT header assets which natively have login authentication requirements.
     */
    public function print_scripts(){
        // @link /disciple-tools-theme/dt-assets/functions/enqueue-scripts.php
        $allowed_js = apply_filters( 'dt_magic_url_base_allowed_js', [
            'jquery',
            'jquery-ui',
            'lodash',
            'lodash-core',
            'site-js',
            'shared-functions',
            'moment',
            'datepicker'
        ]);

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

    /**
     * Authorizes styles allowed to load in magic link
     *
     * Controls the linked styles loaded into the header.
     * @note This overrides standard DT header assets.
     */
    public function print_styles(){
        // @link /disciple-tools-theme/dt-assets/functions/enqueue-scripts.php
        $allowed_css = apply_filters( 'dt_magic_url_base_allowed_css', [
            'jquery-ui-site-css',
            'foundation-css',
            'site-css',
            'datepicker-css'
        ]);

        global $wp_styles;
        if ( isset( $wp_styles ) ) {
            foreach ( $wp_styles->queue as $key => $item ) {
                if ( !in_array( $item, $allowed_css ) ) {
                    unset( $wp_styles->queue[$key] );
                }
            }
        }
    }

    /**
     * Loads enqueued scripts and custom printed scripts to header
     * @note this is a required method because the standard DT header includes authentication requirements
     * @note Copy function to 'extends' class to override or modify
     */
    public function _header(){
        wp_head();
        $this->header_style();
        $this->header_javascript();
    }
    /**
     * Loads enqueued styles and custom printed styles to header
     * @note Copy function to 'extends' class to override or modify
     */
    public function _footer(){
        $this->footer_javascript();
        wp_footer();
    }

    /**
     * Adds printed styles to header
     * @note Copy function to 'extends' class to override or modify
     */
    public function header_style(){}

    /**
     * Adds printed scripts to header
     * @note Copy function to 'extends' class to override or modify
     */
    public function header_javascript(){}

    /**
     * Adds printed scripts to footer
     * @note Copy function to 'extends' class to override or modify
     */
    public function footer_javascript(){}

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

    public function dt_settings_apps_list( $apps_list ) {
        if ( 'user' === $this->post_type ) {
            $apps_list[$this->meta_key] = [
                'key' => $this->meta_key,
                'url_base' => $this->root. '/'. $this->type,
                'label' => $this->page_title,
                'description' => $this->page_description,
            ];
        }
        return $apps_list;
    }

}
