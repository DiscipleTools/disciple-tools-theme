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
             *          'root' => 'root_name' (string),
             *          'type' => 'type_name' (string),
             *          'meta_key' => 'rootname_typename_public_key' (string),
             *          'actions' => [
             *              '' => 'Home' (string),
             *              'edit' => 'Edit' (string),
             *              'instructions' => 'Instructions' (string),
             *          ] (array)
             *      ] (array)
             * ], (array)
             * 'root_name' => [
             *      'type_name' => [
             *          'root' => 'root_name' (string),
             *          'type' => 'type_name' (string),
             *          'meta_key' => 'rootname_typename_public_key' (string),
             *          'actions' => [
             *              '' => 'Home' (string),
             *              'edit' => 'Edit' (string),
             *              'instructions' => 'Instructions' (string),
             *          ] (array)
             *      ] (array)
             * ] (array)
             */
            return apply_filters( 'dt_magic_url_register_types', $types = [] );
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
            foreach ( $types as $type){
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
            $url_path = self::get_url_path();
            $url_path = strtok( $url_path, '?' ); //allow get parameters
            $parts = explode( '/', $url_path );
            $parts = array_map( 'sanitize_key', wp_unslash( $parts ) );

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
                            return false;
                        } else {
                            $elements['post_id'] = $user_id;
                        }
                    } else {
                        // get post_id
                        $post_id = self::get_post_id( $elements['meta_key'], $parts[2] );
                        if ( ! $post_id ){ // fail if no post id for public key
                            return false;
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
                return $elements;
            }
            return false;
        }

        public function parse_wp_rest_url_parts( $public_key ){
            // get required url elements
            $all_types = $this->registered_types();
            $root = $this->root;
            $types = self::list_types();

            // get url, create parts array and sanitize
            $url_path = self::get_url_path();
            $url_path = strtok( $url_path, '?' ); //allow get parameters
            $parts = explode( '/', $url_path );
            $parts = array_map( 'sanitize_key', wp_unslash( $parts ) );

            // test :
            // correct root
            // approved type
            if ( isset( $parts[0] ) && "wp-json" === $parts[0] && isset( $parts[1] ) && $root === $parts[1] && isset( $parts[3] ) && isset( $types[$parts[3]] ) ){
                $elements = [
                    'root' => '',
                    'type' => '',
                    'meta_key' => '',
                    'public_key' => '',
                    'action' => '',
                    'post_id' => '',
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
                if ( !empty( $public_key ) ){
                    $elements['public_key'] = $public_key;

                    // test that meta_key is set
                    if ( ! isset( $types[$elements['type']]['meta_key'] ) ) {
                        return false;
                    }
                    $elements['meta_key'] = $types[$elements['type']]['meta_key'];

                    if ( 'user' === $types[$elements['type']]['post_type'] ) {
                        // if user
                        $user_id = self::get_user_id( $elements['meta_key'], $parts[2] );
                        if ( ! $user_id ){ // fail if no post id for public key
                            return false;
                        } else {
                            $elements['post_id'] = $user_id;
                        }
                    } else {
                        // get post_id
                        $post_id = self::get_post_id( $elements['meta_key'], $parts[2] );
                        if ( ! $post_id ){ // fail if no post id for public key
                            return false;
                        } else {
                            $elements['post_id'] = $post_id;
                        }
                    }
                }

                return $elements;
            }
            return false;
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
            if ( empty( $parts['post_id'] )) { // fail if no post id
                return false;
            }
            return $parts;
        }

        public function get_url_path() {
            if ( isset( $_SERVER["HTTP_HOST"] ) ) {
                $url  = ( !isset( $_SERVER["HTTPS"] ) || @( $_SERVER["HTTPS"] != 'on' ) ) ? 'http://'. sanitize_text_field( wp_unslash( $_SERVER["HTTP_HOST"] ) ) : 'https://'. sanitize_text_field( wp_unslash( $_SERVER["HTTP_HOST"] ) );
                if ( isset( $_SERVER["REQUEST_URI"] ) ) {
                    $url .= sanitize_text_field( wp_unslash( $_SERVER["REQUEST_URI"] ) );
                }
                return trim( str_replace( get_site_url(), "", $url ), '/' );
            }
            return '';
        }

        /**
         * Generates a unique id key
         * @return string
         */
        public function create_unique_key() : string {
            try {
                $hash = hash( 'sha256', bin2hex( random_bytes( 256 ) ) );
            } catch ( Exception $exception ) {
                $hash = hash( 'sha256', bin2hex( rand( 0, 1234567891234567890 ) . microtime() ) );
            }
            return $hash;
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
            return $this->create_unique_key();
        }
    }
}
