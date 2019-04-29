<?php
if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

/**
 * Class Disciple_Tools_Posts_Endpoints
 */
class Disciple_Tools_Posts_Endpoints {

    /**
     * @var object Public_Hooks instance variable
     */
    private static $_instance = null;

    /**
     * Public_Hooks. Ensures only one instance of Public_Hooks is loaded or can be loaded.
     *
     * @return Disciple_Tools_Posts_Endpoints instance
     */
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }

        return self::$_instance;
    } // End instance()

    /**
     * The Public_Hooks rest api variables
     */
    private $version = 2;
    private $context = "dt-posts";
    private $namespace;

    /**
     * Disciple_Tools_Posts_Endpoints constructor.
     */
    public function __construct() {
        $this->namespace = $this->context . "/v" . intval( $this->version );
        add_action( 'rest_api_init', [ $this, 'add_api_routes' ] );
    }

    /**
     * Add the api routes
     */
    public function add_api_routes() {
        register_rest_route(
            $this->namespace, '/(?P<post_type>\w+)/', [
                [
                    "methods"  => "POST",
                    "callback" => [ $this, 'create_post' ],
                    "args" => [
                        "post_type" => [
                            "description" => "The post type",
                            "type" => 'post_type',
                            "required" => true,
                            "validate_callback" => [ $this, "prefix_validate_args" ]
                        ],
                    ]
                ]
            ]
        );
        register_rest_route(
            $this->namespace, '/(?P<post_type>\w+)/(?P<id>\d+)', [
                [
                    "methods"  => "GET",
                    "callback" => [ $this, 'get_post' ],
                    "args" => [
                        "post_type" => [
                            "description" => "The post type",
                            "type" => 'post_type',
                            "required" => true,
                            "validate_callback" => [ $this, "prefix_validate_args" ]
                        ],
                    ]
                ]
            ]
        );
        register_rest_route(
            $this->namespace, '/(?P<post_type>\w+)/(?P<id>\d+)', [
                [
                    "methods"  => "POST",
                    "callback" => [ $this, 'update_post' ],
                    "args" => [
                        "post_type" => [
                            "description" => "The post type",
                            "type" => 'post_type',
                            "required" => true,
                            "validate_callback" => [ $this, "prefix_validate_args" ]
                        ],
                    ]
                ]
            ]
        );
        register_rest_route(
            $this->namespace, '/(?P<post_type>\w+)/(?P<id>\d+)/comments', [
                [
                    "methods"  => "GET",
                    "callback" => [ $this, 'get_comments' ],
                    "args" => [
                        "post_type" => [
                            "description" => "The post type",
                            "type" => 'post_type',
                            "required" => true,
                            "validate_callback" => [ $this, "prefix_validate_args" ]
                        ],
                    ]
                ]
            ]
        );
        register_rest_route(
            $this->namespace, '/(?P<post_type>\w+)/(?P<id>\d+)/comment', [
                [
                    "methods"  => "POST",
                    "callback" => [ $this, 'add_comment' ],
                    "args" => [
                        "comment" => [
                            "description" => "The comment text",
                            "type" => 'string',
                            "required" => true,
                            "validate_callback" => [ $this, "prefix_validate_args" ]
                        ],
                        "post_type" => [
                            "description" => "The post type",
                            "type" => 'post_type',
                            "required" => true,
                            "validate_callback" => [ $this, "prefix_validate_args" ]
                        ],
                    ]
                ]
            ]
        );
        register_rest_route(
            $this->namespace, '/(?P<post_type>\w+)/(?P<id>\d+)/comment/(?P<comment_id>\d+)', [
                [
                    "methods"  => "POST",
                    "callback" => [ $this, 'update_comment' ],
                    "args" => [
                        "comment" => [
                            "description" => "The comment text",
                            "type" => 'string',
                            "required" => true,
                            "validate_callback" => [ $this, "prefix_validate_args" ]
                        ],
                        "post_type" => [
                            "description" => "The post type",
                            "type" => 'post_type',
                            "required" => true,
                            "validate_callback" => [ $this, "prefix_validate_args" ]
                        ],
                    ]
                ]
            ]
        );
        register_rest_route(
            $this->namespace, '/(?P<post_type>\w+)/(?P<id>\d+)/comment/(?P<comment_id>\d+)', [
                [
                    "methods"  => "DELETE",
                    "callback" => [ $this, 'delete_comment' ],
                    "args" => [
                        "post_type" => [
                            "description" => "The post type",
                            "type" => 'post_type',
                            "required" => true,
                            "validate_callback" => [ $this, "prefix_validate_args" ]
                        ],
                    ]
                ]
            ]
        );
        register_rest_route(
            $this->namespace, '/(?P<post_type>\w+)/(?P<id>\d+)/activity', [
                [
                    "methods"  => "GET",
                    "callback" => [ $this, 'get_activity' ],
                    "args" => [
                        "post_type" => [
                            "description" => "The post type",
                            "type" => 'post_type',
                            "required" => true,
                            "validate_callback" => [ $this, "prefix_validate_args" ]
                        ],
                    ]
                ]
            ]
        );
//        @todo schema
    }

    /**
     *
     * @param mixed $value
     * @param WP_REST_Request $request Current request object.
     * @param string $param The name of the parameter in this case
     *
     * @return string|WP_Error
     */
    public function prefix_validate_args( $value, $request, $param ){
        $attributes = $request->get_attributes();

        if ( isset( $attributes['args'][ $param ] ) ) {
            $argument = $attributes['args'][ $param ];
            // Check to make sure our argument is a string.
            if ( 'string' === $argument['type'] && ! is_string( $value ) ) {
                return new WP_Error( 'rest_invalid_param', sprintf( esc_html__( '%1$s is not of type %2$s', 'Disciple_tools' ), $param, 'string' ), array( 'status' => 400 ) );
            }
            if ( 'integer' === $argument['type'] && ! is_numeric( $value ) ) {
                return new WP_Error( 'rest_invalid_param', sprintf( esc_html__( '%1$s is not of type %2$s', 'Disciple_tools' ), $param, 'integer' ), array( 'status' => 400 ) );
            }
            if ( 'post_type' === $argument['type'] ){
                $post_types = apply_filters( 'dt_registered_post_types', [ 'contacts', 'groups' ] );
                if ( !in_array( $value, $post_types ) ){
                    return new WP_Error( 'rest_invalid_param', sprintf( esc_html__( '%1$s is not a valid post type', 'Disciple_tools' ), $value ), array( 'status' => 400 ) );
                }
            }
        } else {
            // This code won't execute because we have specified this argument as required.
            // If we reused this validation callback and did not have required args then this would fire.
            return new WP_Error( 'rest_invalid_param', sprintf( esc_html__( '%s was not registered as a request argument.', 'my-textdomain' ), $param ), array( 'status' => 400 ) );
        }

        // If we got this far then the data is valid.
        return true;
    }

    public function create_post( WP_REST_Request $request ){
        $fields = $request->get_json_params();
        $url_params = $request->get_url_params();
        $get_params = $request->get_query_params();
        $silent = isset( $get_params["silent"] ) && $get_params["silent"] === "true";
        $post = DT_Posts::create_post( $url_params["post_type"], $fields, $silent );
        return $post;
    }

    public function get_post( WP_REST_Request $request ){
        $url_params = $request->get_url_params();
        return DT_Posts::get_post( $url_params["post_type"], $url_params["id"] );
    }

    public function update_post( WP_REST_Request $request ){
        $fields = $request->get_json_params();
        $url_params = $request->get_url_params();
        $get_params = $request->get_query_params();
        $silent = isset( $get_params["silent"] ) && $get_params["silent"] === "true";
        return DT_Posts::update_post( $url_params["post_type"], $url_params["id"], $fields, $silent );
    }


    public function get_comments( WP_REST_Request $request ){
        $url_params = $request->get_url_params();
        return Disciple_Tools_Posts::get_post_comments( $url_params["post_type"], $url_params["id"] );
    }

    public function get_activity( WP_REST_Request $request ){
        $url_params = $request->get_url_params();
        $post_settings = apply_filters( "dt_get_post_type_settings", [], $url_params["post_type"] );
        return Disciple_Tools_Posts::get_post_activity( $url_params["post_type"], $url_params["id"], $post_settings["fields"] );
    }

    public function add_comment( WP_REST_Request $request ){
        $url_params = $request->get_url_params();
        $get_params = $request->get_query_params();
        $body = $request->get_json_params();
        $silent = isset( $get_params["silent"] ) && $get_params["silent"] === "true";
        $result = Disciple_Tools_Posts::add_post_comment( $url_params["post_type"], $url_params["id"], $body["comment"], 'comment', [], true, $silent );
        if ( is_wp_error( $result ) ) {
            return $result;
        } else {
            return get_comment( $result );
        }
    }

    public function update_comment( WP_REST_Request $request ){
        $url_params = $request->get_url_params();
        $body = $request->get_json_params();
        $result = Disciple_Tools_Posts::update_post_comment( $url_params["comment_id"], $body["comment"] );
        if ( is_wp_error( $result ) ) {
            return $result;
        } else {
            return get_comment( $result );
        }
    }

    public function delete_comment( WP_REST_Request $request ){
        $url_params = $request->get_url_params();
        $result = Disciple_Tools_Posts::delete_post_comment( $url_params["comment_id"] );
        return $result;
    }

}
