<?php
/**
 * DT URL Magic Utilities
 * These static functions support the process of offering magic links in the DT System
 *
 * Initialized as new DT_Magic_URL( string $root );
 *
 * @version 1.0 Initialized.
 */
/**
 * The Magic URl utilities support the idea of extending specific access to non-wp users and storing their intections
 * to a post type as data.
 *
 * The magic url structure is as follows: {yoursite}/root/type/key/action
 *
 *
 *
 * Best practice is to define a root for the entire plugin extension, i.e. reports
 * Then define one type for every key set you will store to the post type record.
 * The key is created and stored to the posttype record in the meta_value. This key is both unique and reissuable.
 * Define actions for different pages or interfaces as needed for a single post type to interact with.
 *
 * Example: my-site.com/tools/coaching/2F0f8f71c06af5abe937f448ef49ad7c0f2b550b1e0513470075abb3a4ac28032e/baptisms
 *
 * For example, if you want to build three kinds of pages for a contact:
 * 1. Root : tools
 * 2. Types : coaching, growth
 * 3. Keys: (coaching) tools_coaching_key (hash, sha256), (growth) tools_growth_key (hash, sha256)
 * 4. Actions: (coaching) generation-map, baptisms, prayer-calendar (growth) home, edit, verify, help
 *
 * 1. The root of the magic url plugin is subscription (this name needs to not be in conflict with other system root names, like contacts or groups.
 * 2. Every type begins a branch of keys to be installed to the post type. In this way, one contact might be given one
 * kind of tool but not another. Or you can revoke one part of access but not another. Most applications will only use one type, but keeping place the
 * the type pattern future proofs the application for expansion.
 * 3. The keys are 64 character SHA256 hashes randomly generated. The combination of the meta_key: subscription and the
 * meta_value: key gives the matching post_id of the contact.
 * 4. A micro app can function completely within one page driven entirely by javascript and the REST api, but if the micro app needs it,
 * you can load unlimited additional pages. Or by some other qualification, a set of tools to one contact and a larger set to another contact.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

if ( ! function_exists( 'dt_registered_types' ) ) {
    /**
     * Retrieve magic link registered types array.
     * @see DT_Magic_URL registered_types() for description
     * @return array
     */
    function dt_get_registered_types( $by_key = false ) {
        return DT_Magic_URL::registered_types_static( $by_key );
    }
}


if ( ! class_exists( 'DT_Magic_URL' ) ) {
    class DT_Magic_URL {

        public $root;
        public $namespace = 'dt-magic-url/v1';

//        private static $_instance = null;
//        public static function instance( string $root ) {
//            if ( is_null( self::$_instance ) ) {
//                self::$_instance = new self( $root );
//            }
//            return self::$_instance;
//        } // End instance()

        public function __construct( string $root ) {
            $this->root = $root;

            add_filter( 'dt_custom_fields_settings', [ $this, '_register_custom_fields' ], 10, 2 );
        }

        public function registered_types() : array {
            /**
             * Expected structure of type response
             * 'root_name' => [
             *      'type_name' => [
             *          'name' => 'Name' (string)
             *          'root' => 'root_name' (string),
             *          'type' => 'type_name' (string),
             *          'meta_key' => 'rootname_typename_public_key' (string),
             *          'actions' => [
             *              '' => 'Home' (string),
             *              'edit' => 'Edit' (string),
             *              'instructions' => 'Instructions' (string),
             *          ] (array),
             *          'instance_id' => 0 (int),
             *          'show_bulk_send' => false (bool),
             *          'show_app_tile' => false (bool),
             *          'key' => 'rootname_typename_public_key' (string),
             *          'url_base' => 'rootname/typename' (string),
             *          'label' => 'page_title' (string),
             *          'description' => 'page_description' (string),
             *          'meta' => [] (array)
             *      ] (array)
             * ], (array)
             * 'root_name' => [
             *      'type_name' => [
             *          'name' => 'Name' (string)
             *          'root' => 'root_name' (string),
             *          'type' => 'type_name' (string),
             *          'meta_key' => 'rootname_typename_public_key' (string),
             *          'actions' => [
             *              '' => 'Home' (string),
             *              'edit' => 'Edit' (string),
             *              'instructions' => 'Instructions' (string),
             *          ] (array),
             *          'instance_id' => 0 (int),
             *          'show_bulk_send' => false (bool),
             *          'show_app_tile' => false (bool),
             *          'key' => 'rootname_typename_public_key' (string),
             *          'url_base' => 'rootname/typename' (string),
             *          'label' => 'page_title' (string),
             *          'description' => 'page_description' (string),
             *          'meta' => [] (array)
             *      ] (array)
             * ] (array)
             */
            return apply_filters( 'dt_magic_url_register_types', $types = [] );
        }

        public static function registered_types_static( $by_key = false ) : array {
            $apps = apply_filters( 'dt_magic_url_register_types', $types = [] );
            if ( $by_key ) {
                $by_key = [];
                foreach ( $apps as $root => $types ) {
                    foreach ( $types as $type => $values ) {
                        $by_key[$values['meta_key']] = $values;
                    }
                }
                return $by_key;
            }
            return $apps;
        }

        /**
         * Each meta_key for a type must be registered to the post type so that the
         * dt-posts REST api will reflect it.
         * @param $fields
         * @param $post_type
         * @return array
         */
        public function _register_custom_fields( $fields, $post_type ) {
            $types = self::list_types();
            if ( empty( $types ) ) {
                return $fields;
            }

            foreach ( $types as $type ){
                if ( $post_type === $type['post_type'] ){
                    if ( !isset( $fields[$type['meta_key']] ) ){
                        $fields[$type['meta_key']] = [
                            'name'   => $type['name'],
                            'type'   => 'hash',
                            'hidden' => true,
                        ];
                    }
                }
            }
            return $fields;
        }

        /**
         * Extract type list from registered root
         * @return array
         */
        public function list_types() : array {
            $all_types = $this->registered_types();
            if ( isset( $all_types[$this->root] ) ) {
                return $all_types[$this->root];
            }
            return [];
        }

        /**
         * Extract actions from registered root and type
         * @param $type
         * @return array
         */
        public function list_actions( $type ) : array {
            $types = self::list_types();
            if ( isset( $types[$type]['actions'] ) && ! empty( $types[$type]['actions'] ) && is_array( $types[$type]['actions'] ) ) {
                return $types[$type]['actions'];
            }
            return [];
        }

        /**
         * Create list of url root/type bases for url registry
         * @example root/type1, root/type2, root/type3
         * @return array
         */
        public function list_url_bases() : array{
            $url = [];
            $root = $this->root;
            $types = self::list_types();
            foreach ( $types as $type ){
                $url[] = $root . '/'. $type;
            }
            return $url;
        }

        public function parse_url_parts(){

            // get required url elements
            $all_types = $this->registered_types();
            $root = $this->root;
            $types = self::list_types();

            // get url, create parts array and sanitize
            $url_path = dt_get_url_path( true );
            $parts = explode( '/', $url_path );

            // test :
            // correct root
            // approved type
            if ( isset( $parts[0] ) && $root === $parts[0] && isset( $parts[1] ) && isset( $types[$parts[1]] ) ){
                $elements = [
                    'root' => '',
                    'type' => '',
                    'meta_key' => '',
                    'public_key' => '',
                    'action' => '',
                    'post_id' => '',
                    'post_type' => '',
                    'instance_id' => ''
                ];
                if ( isset( $parts[0] ) && ! empty( $parts[0] ) ){
                    $elements['root'] = $parts[0];

                    // test for valid root
                    if ( ! isset( $all_types[$elements['root']] ) ) {
                        return false;
                    }
                }
                if ( isset( $parts[1] ) && ! empty( $parts[1] ) ){
                    $elements['type'] = $parts[1];

                    // test for valid type
                    if ( ! isset( $all_types[$elements['root']][$elements['type']] ) ) {
                        return false;
                    }
                }
                if ( isset( $parts[2] ) && ! empty( $parts[2] ) ){
                    $elements['public_key'] = $parts[2];

                    // test that meta_key is set
                    if ( ! isset( $types[$elements['type']]['meta_key'] ) ) {
                        return false;
                    }
                    $elements['meta_key'] = $types[$elements['type']]['meta_key'];

                    if ( 'user' === $types[$elements['type']]['post_type'] ) {
                        // if user
                        $user_id = self::get_user_id( $elements['meta_key'], $parts[2] );
                        if ( ! $user_id ){ // fail if no post id for public key
                            self::redirect_to_expired_landing_page();
                        } else {
                            $elements['post_id'] = $user_id;
                        }
                    } else {
                        // get post_id
                        $post_id = self::get_post_id( $elements['meta_key'], $parts[2] );
                        if ( ! $post_id ){ // fail if no post id for public key
                            self::redirect_to_expired_landing_page();
                        } else {
                            $elements['post_id'] = $post_id;
                        }
                    }
                }
                if ( isset( $parts[3] ) && ! empty( $parts[3] ) ){
                    $elements['action'] = $parts[3];

                    // test for valid type
                    if ( ! isset( $all_types[$elements['root']][$elements['type']]['actions'][$elements['action']] ) ) {
                        return false;
                    }
                }
                if ( isset( $all_types[$elements['root']][$elements['type']]['post_type'] ) ) {
                    $elements['post_type'] = $all_types[$elements['root']][$elements['type']]['post_type'];
                }
                $instance_id = $types[ $elements['type'] ]['instance_id'];
                if ( ! empty( $instance_id ) ) {
                    $elements['instance_id'] = $instance_id;
                }

                // Wider callout to ensure link is still valid.
                if ( apply_filters( 'dt_magic_link_continue', true, $elements ) === false ) {
                    self::redirect_to_expired_landing_page();
                }

                return $elements;
            }
            return false;
        }

        public function parse_wp_rest_url_parts( $params ){
            // get required url elements
            $all_types = $this->registered_types();
            $root = $this->root;
            $types = self::list_types();

            // get url, create parts array and sanitize
            $url_path = dt_get_url_path( true );
            $parts = explode( '/', $url_path );
            $parts = array_map( 'sanitize_key', wp_unslash( $parts ) );

            // test :
            // correct root
            // approved type
            if ( isset( $parts[0] ) && 'wp-json' === $parts[0] && isset( $parts[1] ) && $root === $parts[1] && isset( $parts[3] ) && isset( $types[$parts[3]] ) ){
                $elements = [
                    'root' => '',
                    'type' => '',
                    'meta_key' => '',
                    'public_key' => '',
                    'action' => '',
                    'post_id' => '',
                    'post_type' => '',
                ];
                if ( isset( $parts[1] ) && ! empty( $parts[1] ) ){
                    $elements['root'] = $parts[1];

                    // test for valid root
                    if ( ! isset( $all_types[$elements['root']] ) ) {
                        return false;
                    }
                }
                if ( isset( $parts[3] ) && ! empty( $parts[3] ) ){
                    $elements['type'] = $parts[3];

                    // test for valid type
                    if ( ! isset( $all_types[$elements['root']][$elements['type']] ) ) {
                        return false;
                    }
                }
                $public_key = $params['parts']['public_key'];
                if ( !empty( $public_key ) ){
                    $elements['public_key'] = $public_key;

                    // test that meta_key is set
                    if ( ! isset( $types[$elements['type']]['meta_key'] ) ) {
                        return false;
                    }
                    $elements['meta_key'] = $params['parts']['meta_key'];

                    if ( 'user' === $types[$elements['type']]['post_type'] ) {
                        // if user
                        $user_id = self::get_user_id( $elements['meta_key'], $public_key );
                        if ( ! $user_id ){ // fail if no user id for public key
                            self::redirect_to_expired_landing_page();
                        } else {
                            $elements['post_id'] = $user_id;
                        }
                    } else {
                        // get post_id
                        $post_id = self::get_post_id( $elements['meta_key'], $public_key );
                        if ( ! $post_id ){ // fail if no post id for public key
                            self::redirect_to_expired_landing_page();
                        } else {
                            $elements['post_id'] = $post_id;
                        }
                    }
                }
                if ( isset( $all_types[$elements['root']][$elements['type']]['post_type'] ) ) {
                    $elements['post_type'] = $all_types[$elements['root']][$elements['type']]['post_type'];
                }

                return $elements;
            }
            return false;
        }

        /**
         * Verify that a rest endpoint has all the needed magic link values set
         * and that they match the expected values
         *
         * @param WP_REST_Request $request
         * @return bool
         */
        public function verify_rest_endpoint_permissions_on_post( WP_REST_Request $request ){
            $params = $request->get_params();
            if ( !isset( $params['parts']['meta_key'], $params['parts']['public_key'], $params['parts']['post_id'], $params['parts']['type'], $params['parts']['root'] ) ){
                return false;
            }
            $parts = $this->parse_wp_rest_url_parts( $params );
            if ( empty( $parts ) ){
                return false;
            }
            if ( $parts['root'] !== $params['parts']['root'] || $parts['type'] !== $params['parts']['type'] ){
                return false;
            }
            if ( $parts['meta_key'] !== $params['parts']['meta_key'] || $parts['public_key'] !== $params['parts']['public_key'] ){
                return false;
            }
            if ( (int) $parts['post_id'] !== (int) $params['parts']['post_id'] ){
                return false;
            }
            return true;
        }

        public function get_post_id( string $meta_key, string $public_key ){
            global $wpdb;
            $result = $wpdb->get_var( $wpdb->prepare( "
                SELECT pm.post_id
                FROM $wpdb->postmeta as pm
                WHERE pm.meta_key = %s
                  AND pm.meta_value = %s
                  ", $meta_key, $public_key ) );
            if ( ! empty( $result ) && ! is_wp_error( $result ) ){
                return $result;
            }
            return false;
        }

        public function get_user_id( string $meta_key, string $public_key ){
            global $wpdb;
            $site_meta_key = $wpdb->prefix . $meta_key;
            $result = $wpdb->get_var( $wpdb->prepare( "
                SELECT pm.user_id
                FROM $wpdb->usermeta as pm
                WHERE pm.meta_key = %s
                  AND pm.meta_value = %s
                  ", $site_meta_key, $public_key ) );
            if ( ! empty( $result ) && ! is_wp_error( $result ) ){
                return $result;
            }
            return false;
        }

        public function is_valid_base_url( string $type ) {
            $parts = self::parse_url_parts();
            if ( empty( $parts ) ){ // fail if not prayer url
                return false;
            }
            if ( $type !== $parts['type'] ){ // fail if not saturation type
                return false;
            }
            if ( ! empty( $parts['public_key'] ) ) { // fail if not specific contact
                return false;
            }
            return $parts;
        }

        public function is_valid_key_url( string $type ) {
            $parts = self::parse_url_parts();
            if ( empty( $parts ) ){ // fail if not prayer url
                return false;
            }
            if ( $type !== $parts['type'] ){ // fail if not saturation type
                return false;
            }
            if ( empty( $parts['public_key'] ) ) { // fail if not specific contact
                return false;
            }
            if ( empty( $parts['post_id'] ) ) { // fail if no post id
                return false;
            }
            return $parts;
        }

        /**
         * Generates a unique id key
         * @return string
         */
        public static function create_unique_key() : string {
            return dt_create_unique_key();
        }

        public function get_current_public_key(){
            $parts = self::parse_url_parts();
            if ( isset( $parts['public_key'] ) && ! empty( $parts['public_key'] ) ) {
                return $parts['public_key'];
            }
            return false;
        }

        public function get_current_action(){
            $parts = self::parse_url_parts();
            if ( isset( $parts['action'] ) && ! empty( $parts['action'] ) ) {
                return (string) $parts['action'];
            }
            return false;
        }

        public static function get_link_url( $magic_url_root, $magic_url_type, $magic_url_key, $action = null ){
            $link = trailingslashit( site_url() ) . $magic_url_root . '/' . $magic_url_type . '/' . $magic_url_key;
            if ( $action ){
                $link .= '/' . $action;
            }
            return $link;
        }

        public static function get_public_key_meta_key( $magic_url_root, $magic_url_type ){
            return $magic_url_root . '_' . $magic_url_type . '_magic_key';
        }

        /**
         * Get the magic url link for a past
         *
         * @param $post_type
         * @param $post_id
         * @param $magic_url_root
         * @param $magic_url_type
         * @param null $action
         * @return string
         */
        public static function get_link_url_for_post( $post_type, $post_id, $magic_url_root, $magic_url_type, $action = null ): string{
            $key_name = self::get_public_key_meta_key( $magic_url_root, $magic_url_type );
            $key = get_post_meta( $post_id, $key_name, true );
            if ( empty( $key ) ){
                $key = dt_create_unique_key();
                update_post_meta( $post_id, $key_name, $key );
            }
            return self::get_link_url( $magic_url_root, $magic_url_type, $key, $action );
        }


        /**
         * Open default restrictions for access to registered endpoints
         * @param $authorized
         * @return bool
         */
        public function authorize_url( $authorized ){
            if ( isset( $_SERVER['REQUEST_URI'] ) && strpos( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ), $this->namespace . '/create_key' ) !== false ) {
                $authorized = true;
            }
            return $authorized;
        }

        /**
         * Register REST Endpoints
         * @link https://github.com/DiscipleTools/disciple-tools-theme/wiki/Site-to-Site-Link for outside of wordpress authentication
         */
        public function add_api_routes() {
            register_rest_route(
                $this->namespace, '/create_key', [
                    [
                        'methods'  => WP_REST_Server::READABLE,
                        'callback' => [ $this, 'api_create_key' ],
                        'permission_callback' => '__return_true',
                    ],
                ]
            );
        }

        public function api_create_key( WP_REST_Request $request ){
            return self::create_unique_key();
        }

        /**
         * Filters and returns registered types that allow bulk send.
         * @return array
         */
        public static function list_bulk_send( $post_type = null ) {
            $registered_list = self::registered_types_static();
            $bulk_send_list = [];
            foreach ( $registered_list as $root_key => $root_values ) {
                foreach ( $root_values as $type_key => $type_values ) {
                    if ( isset( $type_values['show_bulk_send'] ) && $type_values['show_bulk_send'] && ( !$post_type || $type_values['post_type'] === $post_type ) ){
                        if ( ! isset( $bulk_send_list[$root_key] ) ) {
                            $bulk_send_list[$root_key] = [];
                        }
                        $bulk_send_list[$root_key][$type_key] = $type_values;
                    }
                }
            }
            return $bulk_send_list;
        }

        public function redirect_to_expired_landing_page(){
            $path = get_theme_file_path( 'dt-reports/magic-url-landing-page.php' );
            include( $path );
            die();
        }
    }
}
