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
        $arg_schemas = [
            "post_type" => [
                "description" => "The post type",
                "type" => 'post_type',
                "required" => true,
                "validate_callback" => [ $this, "prefix_validate_args" ]
            ],
            "id" => [
                "description" => "The id of the post",
                "type" => 'integer',
                "required" => true,
                "validate_callback" => [ $this, "prefix_validate_args" ]
            ],
            "comment_id" => [
                "description" => "The id of the comment",
                "type" => 'integer',
                "required" => true,
                "validate_callback" => [ $this, "prefix_validate_args" ]
            ],
            "date" => [
                "description" => "The date the comment was made",
                'type' => 'string',
                'required' => false,
                "validate_callback" => [ $this, "prefix_validate_args" ]
            ],
            "comment_type" => [
                "description" => "The type of the comment",
                'type' => 'string',
                'required' => false,
                "validate_callback" => [ $this, "prefix_validate_args" ]
            ]
        ];

        //create_post
        register_rest_route(
            $this->namespace, '/(?P<post_type>\w+)/', [
                [
                    "methods"  => "POST",
                    "callback" => [ $this, 'create_post' ],
                    "args" => [
                        "post_type" => $arg_schemas["post_type"],
                    ]
                ]
            ]
        );
        //get_post
        register_rest_route(
            $this->namespace, '/(?P<post_type>\w+)/(?P<id>\d+)', [
                [
                    "methods"  => "GET",
                    "callback" => [ $this, 'get_post' ],
                    "args" => [
                        "post_type" => $arg_schemas["post_type"],
                        "id" => $arg_schemas["id"],
                    ]
                ]
            ]
        );
        //update_post
        register_rest_route(
            $this->namespace, '/(?P<post_type>\w+)/(?P<id>\d+)', [
                [
                    "methods"  => "POST",
                    "callback" => [ $this, 'update_post' ],
                    "args" => [
                        "post_type" => $arg_schemas["post_type"],
                        "id" => $arg_schemas["id"],
                    ]
                ]
            ]
        );
        //delete_post
        register_rest_route(
            $this->namespace, '/(?P<post_type>\w+)/(?P<id>\d+)', [
                [
                    "methods"  => "DELETE",
                    "callback" => [ $this, 'delete_post' ],
                    "args" => [
                        "post_type" => $arg_schemas["post_type"],
                        "id" => $arg_schemas["id"],
                    ]
                ]
            ]
        );

        //get_posts
        register_rest_route(
            $this->namespace, '/(?P<post_type>\w+)', [
                [
                    "methods"  => "GET",
                    "callback" => [ $this, 'get_list' ],
                    "args" => [
                        "post_type" => $arg_schemas["post_type"],
                    ]
                ]
            ]
        );
        //get_posts_for_typeahead
        register_rest_route(
            $this->namespace, '/(?P<post_type>\w+)/compact', [
                [
                    "methods"  => "GET",
                    "callback" => [ $this, 'get_posts_for_typeahead' ],
                    "args" => [
                        "s" => [
                            "description" => "The text to search for",
                            "type" => 'string',
                            "required" => false,
                            "validate_callback" => [ $this, "prefix_validate_args" ]
                        ],
                        "post_type" => $arg_schemas["post_type"],
                    ]
                ]
            ]
        );

        //get_comments
        register_rest_route(
            $this->namespace, '/(?P<post_type>\w+)/(?P<id>\d+)/comments', [
                [
                    "methods"  => "GET",
                    "callback" => [ $this, 'get_comments' ],
                    "args" => [
                        "post_type" => $arg_schemas["post_type"],
                        "id" => $arg_schemas["id"],
                    ]
                ]
            ]
        );
        //add_comment
        register_rest_route(
            $this->namespace, '/(?P<post_type>\w+)/(?P<id>\d+)/comments', [
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
                        "post_type" => $arg_schemas["post_type"],
                        "id" => $arg_schemas["id"],
                        "date" => $arg_schemas["date"],
                        'comment_type' => $arg_schemas["comment_type"]
                    ]
                ]
            ]
        );
        //update_comment
        register_rest_route(
            $this->namespace, '/(?P<post_type>\w+)/(?P<id>\d+)/comments/(?P<comment_id>\d+)', [
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
                        "post_type" => $arg_schemas["post_type"],
                        "id" => $arg_schemas["id"],
                        "comment_id" => $arg_schemas["comment_id"],
                        'comment_type' => $arg_schemas["comment_type"]
                    ]
                ]
            ]
        );
        //delete_comment
        register_rest_route(
            $this->namespace, '/(?P<post_type>\w+)/(?P<id>\d+)/comments/(?P<comment_id>\d+)', [
                [
                    "methods"  => "DELETE",
                    "callback" => [ $this, 'delete_comment' ],
                    "args" => [
                        "post_type" => $arg_schemas["post_type"],
                        "id" => $arg_schemas["id"],
                        "comment_id" => $arg_schemas["comment_id"],
                    ]
                ]
            ]
        );
        //get_activity
        register_rest_route(
            $this->namespace, '/(?P<post_type>\w+)/(?P<id>\d+)/activity', [
                [
                    "methods"  => "GET",
                    "callback" => [ $this, 'get_activity' ],
                    "args" => [
                        "post_type" => $arg_schemas["post_type"],
                        "id" => $arg_schemas["id"],
                    ]
                ]
            ]
        );
        //get_single_activity
        register_rest_route(
            $this->namespace, '/(?P<post_type>\w+)/(?P<id>\d+)/activity/(?P<activity_id>\d+)', [
                [
                    "methods"  => "GET",
                    "callback" => [ $this, 'get_single_activity' ],
                    "args" => [
                        "post_type" => $arg_schemas["post_type"],
                        "id" => $arg_schemas["id"],
                        "activity_id" => [
                            "description" => "The id of the activity",
                            "type" => 'integer',
                            "required" => true,
                            "validate_callback" => [ $this, "prefix_validate_args" ]
                        ]
                    ]
                ]
            ]
        );
        //get_shares
        register_rest_route(
            $this->namespace, '/(?P<post_type>\w+)/(?P<id>\d+)/shares', [
                [
                    "methods"  => "GET",
                    "callback" => [ $this, 'get_shares' ],
                    "args" => [
                        "post_type" => $arg_schemas["post_type"],
                        "id" => $arg_schemas["id"],
                    ]
                ]
            ]
        );
        //add_share
        register_rest_route(
            $this->namespace, '/(?P<post_type>\w+)/(?P<id>\d+)/shares', [
                [
                    "methods"  => "POST",
                    "callback" => [ $this, 'add_share' ],
                    "args" => [
                        "user_id" => [
                            "description" => "The ID of the user to share the record with",
                            "type" => 'integer',
                            "required" => true,
                            "validate_callback" => [ $this, "prefix_validate_args" ]
                        ],
                        "post_type" => $arg_schemas["post_type"],
                        "id" => $arg_schemas["id"],
                    ]
                ]
            ]
        );

        //add_share
        register_rest_route(
            $this->namespace, '/(?P<post_type>\w+)/(?P<id>\d+)/shares', [
                [
                    "methods"  => "DELETE",
                    "callback" => [ $this, 'remove_share' ],
                    "args" => [
                        "user_id" => [
                            "description" => "The ID of the user to unshared the record with",
                            "type" => 'integer',
                            "required" => true,
                            "validate_callback" => [ $this, "prefix_validate_args" ]
                        ],
                        "post_type" => $arg_schemas["post_type"],
                        "id" => $arg_schemas["id"],
                    ]
                ]
            ]
        );
        //get_following
        register_rest_route(
            $this->namespace, '/(?P<post_type>\w+)/(?P<id>\d+)/following', [
                [
                    "methods"  => "GET",
                    "callback" => [ $this, 'get_following' ],
                    "args" => [
                        "post_type" => $arg_schemas["post_type"],
                        "id" => $arg_schemas["id"],
                    ]
                ]
            ]
        );
        //Get multiselect values
        register_rest_route(
            $this->namespace, '/(?P<post_type>\w+)/multi-select-values', [
                [
                    "methods"  => "GET",
                    "callback" => [ $this, 'get_multi_select_values' ],
                    "args" => [
                        "post_type" => $arg_schemas["post_type"],
                        "field" => [
                            "description" => "The field key",
                            "type" => 'string',
                            "required" => true,
                            "validate_callback" => [ $this, "prefix_validate_args" ]
                        ],
                        "s" => [
                            "description" => "Filter values to this query",
                            "type" => 'string',
                            "required" => false,
                            "validate_callback" => [ $this, "prefix_validate_args" ]
                        ],
                    ]
                ]
            ]
        );
        //Get Post Settings
        register_rest_route(
            $this->namespace, '/(?P<post_type>\w+)/settings', [
                [
                    "methods"  => "GET",
                    "callback" => [ $this, 'get_post_settings' ],
                    "args" => [
                        "post_type" => $arg_schemas["post_type"],
                    ]
                ]
            ]
        );
        //Get Post Field Settings
        register_rest_route(
            "dt-public/v" . intval( $this->version ), '/(?P<post_type>\w+)/settings_fields', [
                [
                    "methods"  => "POST",
                    "callback" => [ $this, 'get_post_field_settings' ],
                    "args" => [
                        "post_type" => $arg_schemas["post_type"],
                    ]
                ]
            ]
        );
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
                return new WP_Error( 'rest_invalid_param', sprintf( '%1$s is not of type %2$s', $param, 'string' ), array( 'status' => 400 ) );
            }
            if ( 'integer' === $argument['type'] && ! is_numeric( $value ) ) {
                return new WP_Error( 'rest_invalid_param', sprintf( '%1$s is not of type %2$s', $param, 'integer' ), array( 'status' => 400 ) );
            }
            if ( 'post_type' === $argument['type'] ){
                $post_types = DT_Posts::get_post_types();
                if ( !in_array( $value, $post_types ) ){
                    return new WP_Error( 'rest_invalid_param', sprintf( '%1$s is not a valid post type', $value ), array( 'status' => 400 ) );
                }
            }
        } else {
            // This code won't execute because we have specified this argument as required.
            // If we reused this validation callback and did not have required args then this would fire.
            return new WP_Error( 'rest_invalid_param', sprintf( '%s was not registered as a request argument.', $param ), array( 'status' => 400 ) );
        }

        // If we got this far then the data is valid.
        return true;
    }
    public static function prefix_validate_args_static( $value, $request, $param ) {
        return self::instance()->prefix_validate_args( $value, $request, $param );
    }

    public function create_post( WP_REST_Request $request ){
        $fields = $request->get_json_params() ?? $request->get_body_params();
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
        $fields = $request->get_json_params() ?? $request->get_body_params();
        $url_params = $request->get_url_params();
        $get_params = $request->get_query_params();
        $silent = isset( $get_params["silent"] ) && $get_params["silent"] === "true";
        return DT_Posts::update_post( $url_params["post_type"], $url_params["id"], $fields, $silent );
    }

    public function delete_post( WP_REST_Request $request ){
        $url_params = $request->get_url_params();
        return DT_Posts::delete_post( $url_params["post_type"], $url_params["id"] );
    }

    public function get_list( WP_REST_Request $request ){
        $url_params = $request->get_url_params();
        $get_params = $request->get_json_params() ?? $request->get_query_params();
        return DT_Posts::list_posts( $url_params["post_type"], $get_params );
    }

    public function get_posts_for_typeahead( WP_REST_Request $request ){
        $url_params = $request->get_url_params();
        $get_params = $request->get_query_params();
        $search = isset( $get_params['s'] ) ? $get_params['s'] : '';
        return DT_Posts::get_viewable_compact( $url_params["post_type"], $search, $get_params );
    }


    public function get_activity( WP_REST_Request $request ){
        $url_params = $request->get_url_params();
        $get_params = $request->get_query_params();
        return DT_Posts::get_post_activity( $url_params["post_type"], $url_params["id"], $get_params );
    }

    public function get_single_activity( WP_REST_Request $request ){
        $url_params = $request->get_url_params();
        return DT_Posts::get_post_single_activity( $url_params["post_type"], $url_params["id"], $url_params["activity_id"] );
    }

    public function get_shares( WP_REST_Request $request ){
        $url_params = $request->get_url_params();
        return DT_Posts::get_shared_with( $url_params["post_type"], $url_params["id"] );
    }

    public function add_share( WP_REST_Request $request ){
        $url_params = $request->get_url_params();
        $body = $request->get_json_params() ?? $request->get_body_params();
        return DT_Posts::add_shared( $url_params["post_type"], $url_params["id"], $body['user_id'] );
    }

    public function remove_share( WP_REST_Request $request ){
        $url_params = $request->get_url_params();
        $body = $request->get_json_params() ?? $request->get_body_params();
        return DT_Posts::remove_shared( $url_params["post_type"], $url_params["id"], $body['user_id'] );
    }

    public function get_comments( WP_REST_Request $request ){
        $url_params = $request->get_url_params();
        $get_params = $request->get_query_params();
        return DT_Posts::get_post_comments( $url_params["post_type"], $url_params["id"], true, "all", [
            "offset" => $get_params['offset'] ?? 0,
            "number" => $get_params["number"] ?? ''
        ] );
    }

    public function add_comment( WP_REST_Request $request ){
        $url_params = $request->get_url_params();
        $get_params = $request->get_query_params();
        $body = $request->get_json_params() ?? $request->get_body_params();
        $silent = isset( $get_params["silent"] ) && $get_params["silent"] === "true";
        $args = [];
        if ( isset( $body["date"] ) ){
            $args["comment_date"] = $body["date"];
        }
        $type = 'comment';
        if ( isset( $body["comment_type"] ) ){
            $type = $body["comment_type"];
        }

        $result = DT_Posts::add_post_comment( $url_params["post_type"], $url_params["id"], $body["comment"], $type, $args, true, $silent );
        if ( is_wp_error( $result ) ) {
            return $result;
        } else {
            return get_comment( $result );
        }
    }

    public function update_comment( WP_REST_Request $request ){
        $url_params = $request->get_url_params();
        $body = $request->get_json_params() ?? $request->get_body_params();
        $type = 'comment';
        if ( isset( $body["comment_type"] ) ){
            $type = $body["comment_type"];
        }
        $result = DT_Posts::update_post_comment( $url_params["comment_id"], $body["comment"], true, $type );
        if ( is_wp_error( $result ) ) {
            return $result;
        } else {
            return get_comment( $result );
        }
    }

    public function delete_comment( WP_REST_Request $request ){
        $url_params = $request->get_url_params();
        $result = DT_Posts::delete_post_comment( $url_params["comment_id"] );
        return $result;
    }

    public function get_following( WP_REST_Request $request ) {
        $url_params = $request->get_url_params();
        return DT_Posts::get_users_following_post( $url_params["post_type"], $url_params["id"] );
    }

    public function get_multi_select_values( WP_REST_Request $request ){
        $url_params = $request->get_url_params();
        $get_params = $request->get_query_params();
        $search = isset( $get_params['s'] ) ? $get_params['s'] : '';
        return DT_Posts::get_multi_select_options( $url_params["post_type"], $get_params["field"], $search );
    }

    public function get_post_settings( WP_REST_Request $request ){
        $url_params = $request->get_url_params();
        if ( ! ( DT_Posts::can_access( $url_params["post_type"] ) || DT_Posts::can_create( $url_params["post_type"] ) ) ){
            return new WP_Error( __FUNCTION__, "No permissions to read " . $url_params["post_type"], [ 'status' => 403 ] );
        }
        return DT_Posts::get_post_settings( $url_params["post_type"] );
    }

    public function get_post_field_settings( WP_REST_Request $request ){
        $url_params = $request->get_url_params();

        /**
         * Access to the dt-public url and these field settings requires site to site link with valid token.
         */
        $params = $request->get_params();
        if ( ! isset( $params['transfer_token'] ) ) {
            return new WP_Error( __METHOD__, 'Missing parameters.' );
        }
        $valid_token = Site_Link_System::verify_transfer_token( $params['transfer_token'] );
        if ( ! $valid_token ) {
            dt_write_log( $valid_token );
            return new WP_Error( __METHOD__, 'Invalid transfer token' );
        }

        return DT_Posts::get_post_field_settings( $url_params["post_type"] );
    }

}
