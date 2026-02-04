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
    private $context = 'dt-posts';
    private $namespace;

    /**
     * Disciple_Tools_Posts_Endpoints constructor.
     */
    public function __construct() {
        $this->namespace = $this->context . '/v' . intval( $this->version );
        add_action( 'rest_api_init', [ $this, 'add_api_routes' ] );
    }

    /**
     * Add the api routes
     */
    public function add_api_routes() {
        $arg_schemas = [
            'post_type' => [
                'description' => 'The post type',
                'type' => 'string',
                'required' => true,
                'validate_callback' => [ $this, 'prefix_validate_args' ]
            ],
            'id' => [
                'description' => 'The id of the post',
                'type' => 'integer',
                'required' => true,
                'validate_callback' => [ $this, 'prefix_validate_args' ]
            ],
            'comment_id' => [
                'description' => 'The id of the comment',
                'type' => 'integer',
                'required' => true,
                'validate_callback' => [ $this, 'prefix_validate_args' ]
            ],
            'date' => [
                'description' => 'The date the comment was made',
                'type' => 'string',
                'required' => false,
                'validate_callback' => [ $this, 'prefix_validate_args' ]
            ],
            'comment_type' => [
                'description' => 'The type of the comment',
                'type' => 'string',
                'required' => false,
                'validate_callback' => [ $this, 'prefix_validate_args' ]
            ]
        ];

        //create_post
        register_rest_route(
            $this->namespace, '/(?P<post_type>\w+)/', [
                [
                    'methods'  => 'POST',
                    'callback' => [ $this, 'create_post' ],
                    'args' => [
                        'post_type' => $arg_schemas['post_type'],
                    ],
                    'permission_callback' => '__return_true',
                ]
            ]
        );
        //get_post
        register_rest_route(
            $this->namespace, '/(?P<post_type>\w+)/(?P<id>\d+)', [
                [
                    'methods'  => 'GET',
                    'callback' => [ $this, 'get_post' ],
                    'args' => [
                        'post_type' => $arg_schemas['post_type'],
                        'id' => $arg_schemas['id'],
                    ],
                    'permission_callback' => '__return_true',
                ]
            ]
        );
        //update_post
        register_rest_route(
            $this->namespace, '/(?P<post_type>\w+)/(?P<id>\d+)', [
                [
                    'methods'  => 'POST',
                    'callback' => [ $this, 'update_post' ],
                    'args' => [
                        'post_type' => $arg_schemas['post_type'],
                        'id' => $arg_schemas['id'],
                    ],
                    'permission_callback' => '__return_true',
                ]
            ]
        );
        //delete_post
        register_rest_route(
            $this->namespace, '/(?P<post_type>\w+)/(?P<id>\d+)', [
                [
                    'methods'  => 'DELETE',
                    'callback' => [ $this, 'delete_post' ],
                    'args' => [
                        'post_type' => $arg_schemas['post_type'],
                        'id' => $arg_schemas['id'],
                    ],
                    'permission_callback' => '__return_true',
                ]
            ]
        );

        //get_posts
        register_rest_route(
            $this->namespace, '/(?P<post_type>\w+)', [
                [
                    'methods'  => 'GET',
                    'callback' => [ $this, 'get_list' ],
                    'args' => [
                        'post_type' => $arg_schemas['post_type'],
                    ],
                    'permission_callback' => '__return_true',
                ]
            ]
        );
        //list posts
        register_rest_route(
            $this->namespace, '/(?P<post_type>\w+)/list', [
                [
                    'methods'  => [ 'GET', 'POST' ],
                    'callback' => [ $this, 'get_list' ],
                    'args' => [
                        'post_type' => $arg_schemas['post_type'],
                    ],
                    'permission_callback' => '__return_true',
                ]
            ]
        );

        //split_by
        register_rest_route(
            $this->namespace, '/(?P<post_type>\w+)/split_by', [
                [
                    'methods' => 'POST',
                    'callback' => [ $this, 'split_by' ],
                    'args' => [
                        'post_type' => $arg_schemas['post_type'],
                    ],
                    'permission_callback' => '__return_true',
                ]
            ]
        );

        //get_posts_for_typeahead
        register_rest_route(
            $this->namespace, '/(?P<post_type>\w+)/compact', [
                [
                    'methods'  => 'GET',
                    'callback' => [ $this, 'get_posts_for_typeahead' ],
                    'args' => [
                        's' => [
                            'description' => 'The text to search for',
                            'type' => 'string',
                            'required' => false,
                            'validate_callback' => [ $this, 'prefix_validate_args' ]
                        ],
                        'post_type' => $arg_schemas['post_type'],
                    ],
                    'permission_callback' => '__return_true',
                ]
            ]
        );

        //get_comments
        register_rest_route(
            $this->namespace, '/(?P<post_type>\w+)/(?P<id>\d+)/comments', [
                [
                    'methods'  => 'GET',
                    'callback' => [ $this, 'get_comments' ],
                    'args' => [
                        'post_type' => $arg_schemas['post_type'],
                        'id' => $arg_schemas['id'],
                    ],
                    'permission_callback' => '__return_true',
                ]
            ]
        );
        //add_comment
        register_rest_route(
            $this->namespace, '/(?P<post_type>\w+)/(?P<id>\d+)/comments', [
                [
                    'methods'  => 'POST',
                    'callback' => [ $this, 'add_comment' ],
                    'args' => [
                        'comment' => [
                            'description' => 'The comment text',
                            'type' => 'string',
                            'required' => true,
                            'validate_callback' => [ $this, 'prefix_validate_args' ]
                        ],
                        'post_type' => $arg_schemas['post_type'],
                        'id' => $arg_schemas['id'],
                        'date' => $arg_schemas['date'],
                        'comment_type' => $arg_schemas['comment_type']
                    ],
                    'permission_callback' => '__return_true',
                ]
            ]
        );
        //update_comment
        register_rest_route(
            $this->namespace, '/(?P<post_type>\w+)/(?P<id>\d+)/comments/(?P<comment_id>\d+)', [
                [
                    'methods'  => 'POST',
                    'callback' => [ $this, 'update_comment' ],
                    'args' => [
                        'comment' => [
                            'description' => 'The comment text',
                            'type' => 'string',
                            'required' => true,
                            'validate_callback' => [ $this, 'prefix_validate_args' ]
                        ],
                        'post_type' => $arg_schemas['post_type'],
                        'id' => $arg_schemas['id'],
                        'comment_id' => $arg_schemas['comment_id'],
                        'comment_type' => $arg_schemas['comment_type']
                    ],
                    'permission_callback' => '__return_true',
                ]
            ]
        );
        //delete_comment
        register_rest_route(
            $this->namespace, '/(?P<post_type>\w+)/(?P<id>\d+)/comments/(?P<comment_id>\d+)', [
                [
                    'methods'  => 'DELETE',
                    'callback' => [ $this, 'delete_comment' ],
                    'args' => [
                        'post_type' => $arg_schemas['post_type'],
                        'id' => $arg_schemas['id'],
                        'comment_id' => $arg_schemas['comment_id'],
                    ],
                    'permission_callback' => '__return_true',
                ]
            ]
        );
        //toggle comment reaction
        register_rest_route(
            $this->namespace, '/(?P<post_type>\w+)/(?P<id>\d+)/comments/(?P<comment_id>\d+)/react', [
                [
                    'methods'  => 'POST',
                    'callback' => [ $this, 'toggle_comment_reaction' ],
                    'args' => [
                        'post_type' => $arg_schemas['post_type'],
                        'id' => $arg_schemas['id'],
                        'comment_id' => $arg_schemas['comment_id'],
                    ],
                    'permission_callback' => '__return_true',
                ]
            ]
        );
        //get_activity
        register_rest_route(
            $this->namespace, '/(?P<post_type>\w+)/(?P<id>\d+)/activity', [
                [
                    'methods'  => 'GET',
                    'callback' => [ $this, 'get_activity' ],
                    'args' => [
                        'post_type' => $arg_schemas['post_type'],
                        'id' => $arg_schemas['id'],
                    ],
                    'permission_callback' => '__return_true',
                ]
            ]
        );
        //revert_activity_history
        register_rest_route(
            $this->namespace, '/(?P<post_type>\w+)/(?P<id>\d+)/revert_activity_history', [
                [
                    'methods'  => 'POST',
                    'callback' => [ $this, 'revert_activity_history' ],
                    'args' => [
                        'post_type' => $arg_schemas['post_type'],
                        'id' => $arg_schemas['id'],
                    ],
                    'permission_callback' => '__return_true',
                ]
            ]
        );
        //get_single_activity
        register_rest_route(
            $this->namespace, '/(?P<post_type>\w+)/(?P<id>\d+)/activity/(?P<activity_id>\d+)', [
                [
                    'methods'  => 'GET',
                    'callback' => [ $this, 'get_single_activity' ],
                    'args' => [
                        'post_type' => $arg_schemas['post_type'],
                        'id' => $arg_schemas['id'],
                        'activity_id' => [
                            'description' => 'The id of the activity',
                            'type' => 'integer',
                            'required' => true,
                            'validate_callback' => [ $this, 'prefix_validate_args' ]
                        ]
                    ],
                    'permission_callback' => '__return_true',
                ]
            ]
        );
        //get_shares
        register_rest_route(
            $this->namespace, '/(?P<post_type>\w+)/(?P<id>\d+)/shares', [
                [
                    'methods'  => 'GET',
                    'callback' => [ $this, 'get_shares' ],
                    'args' => [
                        'post_type' => $arg_schemas['post_type'],
                        'id' => $arg_schemas['id'],
                    ],
                    'permission_callback' => '__return_true',
                ]
            ]
        );
        //add_share
        register_rest_route(
            $this->namespace, '/(?P<post_type>\w+)/(?P<id>\d+)/shares', [
                [
                    'methods'  => 'POST',
                    'callback' => [ $this, 'add_share' ],
                    'args' => [
                        'user_id' => [
                            'description' => 'The ID of the user to share the record with',
                            'type' => 'integer',
                            'required' => true,
                            'validate_callback' => [ $this, 'prefix_validate_args' ]
                        ],
                        'post_type' => $arg_schemas['post_type'],
                        'id' => $arg_schemas['id'],
                    ],
                    'permission_callback' => '__return_true',
                ]
            ]
        );

        //add_share
        register_rest_route(
            $this->namespace, '/(?P<post_type>\w+)/(?P<id>\d+)/shares', [
                [
                    'methods'  => 'DELETE',
                    'callback' => [ $this, 'remove_share' ],
                    'args' => [
                        'user_id' => [
                            'description' => 'The ID of the user to unshared the record with',
                            'type' => 'integer',
                            'required' => true,
                            'validate_callback' => [ $this, 'prefix_validate_args' ]
                        ],
                        'post_type' => $arg_schemas['post_type'],
                        'id' => $arg_schemas['id'],
                    ],
                    'permission_callback' => '__return_true',
                ]
            ]
        );
        //get_following
        register_rest_route(
            $this->namespace, '/(?P<post_type>\w+)/(?P<id>\d+)/following', [
                [
                    'methods'  => 'GET',
                    'callback' => [ $this, 'get_following' ],
                    'args' => [
                        'post_type' => $arg_schemas['post_type'],
                        'id' => $arg_schemas['id'],
                    ],
                    'permission_callback' => '__return_true',
                ]
            ]
        );
        //Get multiselect values
        register_rest_route(
            $this->namespace, '/(?P<post_type>\w+)/multi-select-values', [
                [
                    'methods'  => 'GET',
                    'callback' => [ $this, 'get_multi_select_values' ],
                    'args' => [
                        'post_type' => $arg_schemas['post_type'],
                        'field' => [
                            'description' => 'The field key',
                            'type' => 'string',
                            'required' => true,
                            'validate_callback' => [ $this, 'prefix_validate_args' ]
                        ],
                        's' => [
                            'description' => 'Filter values to this query',
                            'type' => 'string',
                            'required' => false,
                            'validate_callback' => [ $this, 'prefix_validate_args' ]
                        ],
                    ],
                    'permission_callback' => '__return_true',
                ]
            ]
        );
        //Get Post Settings
        register_rest_route(
            $this->namespace, '/(?P<post_type>\w+)/settings', [
                [
                    'methods'  => 'GET',
                    'callback' => [ $this, 'get_post_settings' ],
                    'args' => [
                        'post_type' => $arg_schemas['post_type'],
                    ],
                    'permission_callback' => '__return_true',
                ]
            ]
        );
        //Get Post Field Settings
        register_rest_route(
            'dt-public/v' . intval( $this->version ), '/(?P<post_type>\w+)/settings_fields', [
                [
                    'methods'  => 'POST',
                    'callback' => [ $this, 'get_post_field_settings' ],
                    'args' => [
                        'post_type' => $arg_schemas['post_type'],
                    ],
                    'permission_callback' => '__return_true',
                ]
            ]
        );

        //Request Record Access
        register_rest_route(
            $this->namespace, '/(?P<post_type>\w+)/(?P<id>\d+)/request_record_access', [
                [
                    'methods'  => 'POST',
                    'callback' => [ $this, 'request_record_access' ],
                    'permission_callback' => '__return_true',
                    'args'     => [
                        'post_type' => $arg_schemas['post_type'],
                        'id'        => $arg_schemas['id']
                    ]
                ]
            ]
        );

        //Advanced Search
        register_rest_route(
            $this->namespace . '/posts/search', '/advanced_search', [
                'methods'             => 'GET',
                'callback'            => [ $this, 'advanced_search' ],
                'permission_callback' => '__return_true',
            ]
        );

        //Check if a field value exists in the database, given the post type and field key
        register_rest_route(
            $this->namespace, '/(?P<post_type>\w+)/check_field_value_exists', [
                'methods'  => 'POST',
                'callback' => [ $this, 'check_field_value_exists' ],
                'args'     => [
                    'post_type' => $arg_schemas['post_type'],
                ],
                'permission_callback' => function ( WP_REST_Request $request ){
                    $params = $request->get_params();
                    $post_type = sanitize_text_field( wp_unslash( $params['post_type'] ) );
                    return DT_Posts::can_create( $post_type );
                },
            ]
        );

        //post_messaging
        register_rest_route(
            $this->namespace, '/(?P<post_type>\w+)/(?P<id>\d+)/post_messaging', [
                [
                    'methods'  => 'POST',
                    'callback' => [ $this, 'post_messaging' ],
                    'args' => [
                        'post_type' => $arg_schemas['post_type'],
                        'id' => $arg_schemas['id']
                    ],
                    'permission_callback' => function( WP_REST_Request $request ) {
                        $params = $request->get_params();
                        $post_type = sanitize_text_field( wp_unslash( $params['post_type'] ) );
                        return DT_Posts::can_access( $post_type );
                    }
                ]
            ]
        );

        //Storage Uploads
        register_rest_route(
            $this->namespace, '/(?P<post_type>\w+)/(?P<id>\d+)/storage_upload', [
                'methods'  => 'POST',
                'callback' => [ $this, 'storage_upload' ],
                'args'     => [
                    'post_type' => $arg_schemas['post_type'],
                    'id' => $arg_schemas['id']
                ],
                'permission_callback' => function ( WP_REST_Request $request ) {
                    $params = $request->get_params();
                    return DT_Posts::can_update( sanitize_text_field( wp_unslash( $params['post_type'] ) ), sanitize_text_field( wp_unslash( $params['id'] ) ) );
                }
            ]
        );

        //Storage Delete Single File
        register_rest_route(
            $this->namespace, '/(?P<post_type>\w+)/(?P<id>\d+)/storage_delete_single', [
                'methods'  => 'POST',
                'callback' => [ $this, 'storage_delete_single' ],
                'args'     => [
                    'post_type' => $arg_schemas['post_type'],
                    'id' => $arg_schemas['id']
                ],
                'permission_callback' => function ( WP_REST_Request $request ) {
                    $params = $request->get_params();
                    return DT_Posts::can_update( sanitize_text_field( wp_unslash( $params['post_type'] ) ), sanitize_text_field( wp_unslash( $params['id'] ) ) );
                }
            ]
        );

        //Storage Rename Single File
        register_rest_route(
            $this->namespace, '/(?P<post_type>\w+)/(?P<id>\d+)/storage_rename_single', [
                'methods'  => 'POST',
                'callback' => [ $this, 'storage_rename_single' ],
                'args'     => [
                    'post_type' => $arg_schemas['post_type'],
                    'id' => $arg_schemas['id']
                ],
                'permission_callback' => function ( WP_REST_Request $request ) {
                    $params = $request->get_params();
                    return DT_Posts::can_update( sanitize_text_field( wp_unslash( $params['post_type'] ) ), sanitize_text_field( wp_unslash( $params['id'] ) ) );
                }
            ]
        );

        //Storage Download Single File
        register_rest_route(
            $this->namespace, '/(?P<post_type>\w+)/(?P<id>\d+)/storage_download', [
                'methods'  => 'POST',
                'callback' => [ $this, 'storage_download' ],
                'args'     => [
                    'post_type' => $arg_schemas['post_type'],
                    'id' => $arg_schemas['id']
                ],
                'permission_callback' => function ( WP_REST_Request $request ) {
                    $params = $request->get_params();
                    return DT_Posts::can_view( sanitize_text_field( wp_unslash( $params['post_type'] ) ), sanitize_text_field( wp_unslash( $params['id'] ) ) );
                }
            ]
        );

        //Storage Delete
        register_rest_route(
            $this->namespace, '/(?P<post_type>\w+)/(?P<id>\d+)/storage_delete', [
                'methods'  => 'POST',
                'callback' => [ $this, 'storage_delete' ],
                'args'     => [
                    'post_type' => $arg_schemas['post_type'],
                    'id' => $arg_schemas['id']
                ],
                'permission_callback' => function ( WP_REST_Request $request ) {
                    $params = $request->get_params();
                    return DT_Posts::can_update( sanitize_text_field( wp_unslash( $params['post_type'] ) ), sanitize_text_field( wp_unslash( $params['id'] ) ) );
                }
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
            if ( $param === 'post_type' ){
                $post_types = DT_Posts::get_post_types();
                // Support advanced search all post type option
                if ( ( $value !== 'all' ) && ! in_array( $value, $post_types ) ) {
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

    public function create_post( WP_REST_Request $request ) {
        $fields     = $request->get_json_params() ?? $request->get_body_params();
        $url_params = $request->get_url_params();
        $get_params = $request->get_query_params();
        $silent     = isset( $get_params['silent'] ) && $get_params['silent'] === 'true';
        $check_dups = !empty( $get_params['check_for_duplicates'] ) ? explode( ',', $get_params['check_for_duplicates'] ) : [];
        $do_not_overwrite_existing_fields = !empty( $get_params['do_not_overwrite_existing_fields'] );
        $post       = DT_Posts::create_post( $url_params['post_type'], $fields, $silent, true, [
            'check_for_duplicates' => $check_dups,
            'do_not_overwrite_existing_fields' => $do_not_overwrite_existing_fields
        ] );
        return $post;
    }

    public function get_post( WP_REST_Request $request ){
        $url_params = $request->get_url_params();
        return DT_Posts::get_post( $url_params['post_type'], $url_params['id'] );
    }


    public function update_post( WP_REST_Request $request ){
        $fields = $request->get_json_params() ?? $request->get_body_params();
        $url_params = $request->get_url_params();
        $get_params = $request->get_query_params();
        $silent = isset( $get_params['silent'] ) && $get_params['silent'] === 'true';
        $do_not_overwrite_existing_fields = !empty( $get_params['do_not_overwrite_existing_fields'] );
        return DT_Posts::update_post( $url_params['post_type'], $url_params['id'], $fields, $silent, true, [
            'do_not_overwrite_existing_fields' => $do_not_overwrite_existing_fields
        ] );
    }

    public function delete_post( WP_REST_Request $request ){
        $url_params = $request->get_url_params();
        return DT_Posts::delete_post( $url_params['post_type'], $url_params['id'] );
    }

    public function get_list( WP_REST_Request $request ){
        $url_params = $request->get_url_params();
        $get_params = $request->get_json_params() ?? $request->get_query_params();
        return DT_Posts::list_posts( $url_params['post_type'], $get_params );
    }

    public function split_by( WP_REST_Request $request ){
        $url_params = $request->get_url_params();
        $get_params = $request->get_json_params() ?? $request->get_query_params();
        return DT_Posts::split_by( $url_params['post_type'], $get_params );
    }

    public function get_posts_for_typeahead( WP_REST_Request $request ){
        $url_params = $request->get_url_params();
        $get_params = $request->get_query_params();
        $search = isset( $get_params['s'] ) ? $get_params['s'] : '';
        return DT_Posts::get_viewable_compact( $url_params['post_type'], $search, $get_params );
    }

    public function get_activity( WP_REST_Request $request ){
        $url_params = $request->get_url_params();
        $get_params = $request->get_query_params();
        return DT_Posts::get_post_activity( $url_params['post_type'], $url_params['id'], $get_params );
    }

    public function revert_activity_history( WP_REST_Request $request ){
        $url_params = $request->get_url_params();
        $data = $request->get_json_params() ?? $request->get_body_params();
        return DT_Posts::revert_post_activity_history( $url_params['post_type'], $url_params['id'], $data );
    }

    public function get_single_activity( WP_REST_Request $request ){
        $url_params = $request->get_url_params();
        return DT_Posts::get_post_single_activity( $url_params['post_type'], $url_params['id'], $url_params['activity_id'] );
    }

    public function get_shares( WP_REST_Request $request ){
        $url_params = $request->get_url_params();
        return DT_Posts::get_shared_with( $url_params['post_type'], $url_params['id'] );
    }

    public function add_share( WP_REST_Request $request ){
        $url_params = $request->get_url_params();
        $body = $request->get_json_params() ?? $request->get_body_params();
        return DT_Posts::add_shared( $url_params['post_type'], $url_params['id'], $body['user_id'] );
    }

    public function remove_share( WP_REST_Request $request ){
        $url_params = $request->get_url_params();
        $body = $request->get_json_params() ?? $request->get_body_params();
        return DT_Posts::remove_shared( $url_params['post_type'], $url_params['id'], $body['user_id'] );
    }

    public function get_comments( WP_REST_Request $request ){
        $url_params = $request->get_url_params();
        $get_params = $request->get_query_params();
        return DT_Posts::get_post_comments( $url_params['post_type'], $url_params['id'], true, 'all', [
            'offset' => $get_params['offset'] ?? 0,
            'number' => $get_params['number'] ?? ''
        ] );
    }

    public function add_comment( WP_REST_Request $request ){
        $url_params = $request->get_url_params();
        $get_params = $request->get_query_params();
        $body = $request->get_json_params() ?? $request->get_body_params();
        $silent = isset( $get_params['silent'] ) && $get_params['silent'] === 'true';
        $args = [];
        if ( isset( $body['date'] ) ){
            $args['comment_date'] = $body['date'];
        }
        if ( isset( $body['meta'] ) ) {
            $args['comment_meta'] = $body['meta'];
        }
        $type = 'comment';
        if ( isset( $body['comment_type'] ) ){
            $type = $body['comment_type'];
        }

        $result = DT_Posts::add_post_comment( $url_params['post_type'], $url_params['id'], $body['comment'], $type, $args, true, $silent );
        if ( is_wp_error( $result ) ) {
            return $result;
        } else {
            $ret = get_comment( $result )->to_array();
            unset( $ret['children'] );
            unset( $ret['populated_children'] );
            unset( $ret['post_fields'] );
            $ret['comment_meta'] = get_comment_meta( $ret['comment_ID'] );
            return $ret;
        }
    }

    public function update_comment( WP_REST_Request $request ){
        $url_params = $request->get_url_params();
        $body = $request->get_json_params() ?? $request->get_body_params();
        $type = 'comment';
        $args = [];
        if ( isset( $body['comment_type'] ) ){
            $type = $body['comment_type'];
        }
        if ( isset( $body['meta'] ) ) {
            $args['comment_meta'] = $body['meta'];
        }
        $result = DT_Posts::update_post_comment( $url_params['comment_id'], $body['comment'], true, $type, $args );
        if ( is_wp_error( $result ) ) {
            return $result;
        } else {
            $ret = get_comment( $result )->to_array();
            unset( $ret['children'] );
            unset( $ret['populated_children'] );
            unset( $ret['post_fields'] );
            $ret['comment_meta'] = get_comment_meta( $ret['comment_ID'] );
            return $ret;
        }
    }

    public function delete_comment( WP_REST_Request $request ){
        $url_params = $request->get_url_params();
        $result = DT_Posts::delete_post_comment( $url_params['comment_id'] );
        return $result;
    }

    public function toggle_comment_reaction( WP_REST_Request $request ){
        $url_params = $request->get_url_params();
        $post_params = $request->get_json_params();
        $result = DT_Posts::toggle_post_comment_reaction( $url_params['post_type'], $url_params['id'], $url_params['comment_id'], $post_params['reaction'] );
        return $result;
    }

    public function get_following( WP_REST_Request $request ) {
        $url_params = $request->get_url_params();
        return DT_Posts::get_users_following_post( $url_params['post_type'], $url_params['id'] );
    }

    public function get_multi_select_values( WP_REST_Request $request ){
        $url_params = $request->get_url_params();
        $get_params = $request->get_query_params();
        $search = isset( $get_params['s'] ) ? $get_params['s'] : '';
        return DT_Posts::get_multi_select_options( $url_params['post_type'], $get_params['field'], $search );
    }

    public function get_post_settings( WP_REST_Request $request ){
        $url_params = $request->get_url_params();
        if ( ! ( DT_Posts::can_access( $url_params['post_type'] ) || DT_Posts::can_create( $url_params['post_type'] ) ) ){
            return new WP_Error( __FUNCTION__, 'No permissions to read ' . $url_params['post_type'], [ 'status' => 403 ] );
        }
        return DT_Posts::get_post_settings( $url_params['post_type'] );
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

        return DT_Posts::get_post_field_settings( $url_params['post_type'] );
    }

    public function request_record_access( WP_REST_Request $request ): string {
        $url_params = $request->get_url_params();

        return DT_Posts::request_record_access( $url_params['post_type'], $url_params['id'] );
    }

    public function advanced_search( WP_REST_Request $request ): array {
        $query     = urldecode( $request->get_param( 'query' ) );
        $post_type = $request->get_param( 'post_type' );
        $offset    = intval( $request->get_param( 'offset' ) );
        $post      = ( strtolower( $request->get_param( 'post' ) ) === 'true' );
        $comment   = ( strtolower( $request->get_param( 'comment' ) ) === 'true' );
        $meta      = ( strtolower( $request->get_param( 'meta' ) ) === 'true' );
        $archived  = ( strtolower( $request->get_param( 'archived' ) ) === 'true' );
        $status    = $request->get_param( 'status' );

        return DT_Posts::advanced_search( $query, $post_type, $offset, [
            'post'    => $post,
            'comment' => $comment,
            'meta'    => $meta,
            'archived'    => $archived,
            'status'  => $status
        ] );
    }

    public function check_field_value_exists( WP_REST_Request $request ) {
        $params = $request->get_params();
        $communication_channels = DT_Posts::get_field_settings_by_type( $params['post_type'], 'communication_channel' );
        if ( in_array( $params['post_type'], $communication_channels ) ) {
            return new WP_Error( __METHOD__, 'Invalid communication_channel' );
        }
        if ( isset( $params['post_type'] ) && isset( $params['communication_channel'] ) && isset( $params['field_value'] ) ) {
            global $wpdb;
            $result = $wpdb->get_results( $wpdb->prepare(
                "SELECT `post_id`
                    FROM $wpdb->postmeta
                    WHERE meta_key LIKE %s
                    AND meta_value = %s;", $params['communication_channel'] . '_%', $params['field_value'] ) );
            return $result;
        }
        return [];
    }

    public function post_messaging( WP_REST_Request $request ){
        $params = $request->get_params();
        if ( !isset( $params['post_type'], $params['id'], $params['subject'], $params['from_name'], $params['send_method'], $params['message'] ) ) {
            return new WP_Error( __METHOD__, 'Missing parameters.' );
        }

        return DT_Posts::post_messaging( $params['post_type'], $params['id'], [
            'subject' => $params['subject'],
            'from_name' => $params['from_name'],
            'reply_to' => $params['reply_to'] ?? '',
            'send_method' => $params['send_method'],
            'message' => $params['message']
        ] );
    }

    public function storage_upload( WP_REST_Request $request ) {
        $params = $request->get_params();
        //phpcs:ignore WordPress.Security.NonceVerification.Missing
        if ( !isset( $params['post_type'], $params['id'], $params['meta_key'], $_FILES['storage_upload_files'] ) ) {
            return new WP_Error( __METHOD__, 'Missing parameters.' );
        }

        if ( !DT_Storage_API::is_enabled() ) {
            return new WP_Error( __METHOD__, 'DT_Storage_API Unavailable.' );
        }

        $post_type = $params['post_type'];
        $post_id = $params['id'];
        $meta_key = $params['meta_key'];
        $key_prefix = $params['key_prefix'] ?? '';
        $files = dt_recursive_sanitize_array( $_FILES['storage_upload_files'] ); //phpcs:ignore WordPress.Security.NonceVerification.Missing

        // Determine storage upload requester type.
        $upload_type = $params['upload_type'] ?? 'post';

        // Check if this is a multi-file field (file_upload type)
        $is_multi_file = isset( $params['is_multi_file'] ) && $params['is_multi_file'] === 'true';

        // Validate storage_s3_url_duration for presigned URL expiration (e.g. '+10 years', '+24 hours')
        $storage_s3_url_duration = isset( $params['storage_s3_url_duration'] ) ? sanitize_text_field( wp_unslash( $params['storage_s3_url_duration'] ) ) : '';
        $url_params = [];
        if ( !empty( $storage_s3_url_duration ) ) {
            try {
                new \DateTimeImmutable( $storage_s3_url_duration );
                $url_params = [ 'duration' => $storage_s3_url_duration ];
            } catch ( \Exception $e ) {
                // Invalid duration string, fall back to default (+24 hours).
                $url_params = [];
            }
        }

        // Process accordingly by requested upload type.
        $meta_key_value = '';
        switch ( $upload_type ) {
            case 'post':
                // To avoid a buildup of stale object storage keys, reuse existing keys for single file.
                // For multi-file, get existing array or initialize empty array.
                if ( $is_multi_file ) {
                    $meta_key_value = get_post_meta( $post_id, $meta_key, true );
                    if ( !is_array( $meta_key_value ) ) {
                        $meta_key_value = [];
                    }
                } else {
                    $meta_key_value = get_post_meta( $post_id, $meta_key, true );
                }
                break;
        }

        // Process all uploaded files.
        $uploaded_files = [];
        $uploaded_keys = [];
        $file_count = is_array( $files['name'] ) ? count( $files['name'] ) : 1;

        for ( $i = 0; $i < $file_count; $i++ ) {
            $uploaded_file = [
                'name' => $files['name'][$i],
                'full_path' => $files['full_path'][$i] ?? '',
                'type' => $files['type'][$i],
                'tmp_name' => $files['tmp_name'][$i],
                'error' => $files['error'][$i],
                'size' => $files['size'][$i]
            ];

            // For multi-file fields, don't reuse keys (always create new)
            $existing_key = $is_multi_file ? '' : $meta_key_value;

            // Push an uploaded file to backend storage service.
            $uploaded = DT_Storage_API::upload_file( $key_prefix, $uploaded_file, $existing_key );

            // Handle WP_Error returns from DT_Storage_API::upload_file()
            if ( is_wp_error( $uploaded ) ) {
                // Continue processing other files even if one fails
                $uploaded_files[] = [
                    'uploaded' => false,
                    'uploaded_key' => '',
                    'name' => $uploaded_file['name'],
                    'uploaded_msg' => $uploaded->get_error_message()
                ];
                continue;
            }

            // If successful, collect uploaded file info.
            if ( $uploaded['uploaded'] === true && !empty( $uploaded['uploaded_key'] ) ) {
                $uploaded_key = $uploaded['uploaded_key'];
                $uploaded_keys[] = $uploaded_key;

                // Build file object with metadata
                $file_object = [
                    'key' => $uploaded_key,
                    'name' => $uploaded_file['name'],
                    'type' => $uploaded_file['type'],
                    'size' => $uploaded_file['size'],
                    'uploaded_at' => current_time( 'mysql' ),
                ];

                // Add file URL
                if ( DT_Storage_API::is_enabled() ) {
                    $file_object['url'] = DT_Storage_API::get_file_url( $uploaded_key, $url_params );
                }

                // Add thumbnail keys and URLs if available (for images)
                if ( !empty( $uploaded['uploaded_thumbnail_key'] ) ) {
                    $file_object['thumbnail_key'] = $uploaded['uploaded_thumbnail_key'];
                    if ( DT_Storage_API::is_enabled() ) {
                        $file_object['thumbnail_url'] = DT_Storage_API::get_file_url( $uploaded['uploaded_thumbnail_key'], $url_params );
                    }
                }
                if ( !empty( $uploaded['uploaded_large_thumbnail_key'] ) ) {
                    $file_object['large_thumbnail_key'] = $uploaded['uploaded_large_thumbnail_key'];
                    if ( DT_Storage_API::is_enabled() ) {
                        $file_object['large_thumbnail_url'] = DT_Storage_API::get_file_url( $uploaded['uploaded_large_thumbnail_key'], $url_params );
                    }
                }

                $uploaded_files[] = [
                    'uploaded' => true,
                    'uploaded_key' => $uploaded_key,
                    'file' => $file_object,
                    'uploaded_msg' => null
                ];
            }
        }

        // If successful, persist uploaded file keys.
        if ( !empty( $uploaded_keys ) ) {
            switch ( $upload_type ) {
                case 'post':
                    // Store old value for activity logging
                    $old_meta_value = $meta_key_value;
                    
                    if ( $is_multi_file ) {
                        // Append new files to existing array
                        $existing_files = is_array( $meta_key_value ) ? $meta_key_value : [];
                        foreach ( $uploaded_files as $uploaded_file_data ) {
                            if ( $uploaded_file_data['uploaded'] && isset( $uploaded_file_data['file'] ) ) {
                                $existing_files[] = $uploaded_file_data['file'];
                            }
                        }
                        update_post_meta( $post_id, $meta_key, $existing_files );
                        
                        // Log activity for file upload
                        $post_settings = DT_Posts::get_post_settings( $post_type );
                        $field_name = $post_settings['fields'][ $meta_key ]['name'] ?? $meta_key;
                        $uploaded_file_names = [];
                        foreach ( $uploaded_files as $uploaded_file_data ) {
                            if ( $uploaded_file_data['uploaded'] && isset( $uploaded_file_data['file']['name'] ) ) {
                                $uploaded_file_names[] = $uploaded_file_data['file']['name'];
                            }
                        }
                        $file_count = count( $uploaded_file_names );
                        if ( $file_count === 1 ) {
                            $object_note = sprintf( _x( 'Uploaded file: %s to %s', 'file_upload activity', 'disciple_tools' ), $uploaded_file_names[0], $field_name );
                        } else {
                            $file_list = implode( ', ', array_slice( $uploaded_file_names, 0, 3 ) );
                            if ( $file_count > 3 ) {
                                $file_list .= sprintf( _x( ' and %d more', 'file_upload activity', 'disciple_tools' ), $file_count - 3 );
                            }
                            $object_note = sprintf( _x( 'Uploaded %d files: %s to %s', 'file_upload activity', 'disciple_tools' ), $file_count, $file_list, $field_name );
                        }
                        
                        dt_activity_insert( [
                            'action'            => 'field_update',
                            'object_type'       => $post_type,
                            'object_id'         => $post_id,
                            'object_name'       => get_the_title( $post_id ),
                            'meta_key'          => $meta_key,
                            'meta_value'        => maybe_serialize( $existing_files ),
                            'old_value'         => maybe_serialize( $old_meta_value ),
                            'field_type'        => 'file_upload',
                            'object_note'       => $object_note,
                        ] );
                    } else {
                        // Single file: use first uploaded key (backward compatibility)
                        update_post_meta( $post_id, $meta_key, $uploaded_keys[0] );
                        
                        // Log activity for single file upload
                        $post_settings = DT_Posts::get_post_settings( $post_type );
                        $field_name = $post_settings['fields'][ $meta_key ]['name'] ?? $meta_key;
                        $file_name = '';
                        if ( !empty( $uploaded_files[0]['file']['name'] ) ) {
                            $file_name = $uploaded_files[0]['file']['name'];
                        } else {
                            $file_name = basename( $uploaded_keys[0] );
                        }
                        $object_note = sprintf( _x( 'Uploaded file: %s to %s', 'file_upload activity', 'disciple_tools' ), $file_name, $field_name );
                        
                        dt_activity_insert( [
                            'action'            => 'field_update',
                            'object_type'       => $post_type,
                            'object_id'         => $post_id,
                            'object_name'       => get_the_title( $post_id ),
                            'meta_key'          => $meta_key,
                            'meta_value'        => $uploaded_keys[0],
                            'old_value'         => $old_meta_value ? $old_meta_value : '',
                            'field_type'        => 'file_upload',
                            'object_note'       => $object_note,
                        ] );
                    }
                    break;

                case 'image_comment':
                    $comment = apply_filters( 'dt_upload_image_comment', ' ', $uploaded_file );
                    // Proceed with associated comment creation (only first file for comments).
                    DT_Posts::add_post_comment( $post_type, $post_id, $comment, 'comment', [
                        'comment_meta' => [
                            $meta_key => $uploaded_keys[0]
                        ]
                    ], true, true );
                    break;
                case 'audio_comment':
                    $uploaded_file['audio_language'] = $params['audio_language'] ?? 'en';

                    $comment = apply_filters( 'dt_upload_audio_comment', ' ', $uploaded_file );
                    // Proceed with associated comment creation (only first file for comments).
                    DT_Posts::add_post_comment( $post_type, $post_id, $comment, 'comment', [
                        'comment_meta' => [
                            $meta_key => $uploaded_keys[0]
                        ]
                    ], true, true );
                    break;
            }
        }

        // Return results
        if ( $is_multi_file ) {
            return [
                'uploaded' => !empty( $uploaded_keys ),
                'uploaded_keys' => $uploaded_keys,
                'uploaded_files' => $uploaded_files,
                'uploaded_msg' => null
            ];
        } else {
            // Backward compatibility: return single file format
            $first_result = !empty( $uploaded_files ) ? $uploaded_files[0] : [
                'uploaded' => false,
                'uploaded_key' => '',
                'uploaded_msg' => null
            ];
            return [
                'uploaded' => $first_result['uploaded'] ?? false,
                'uploaded_key' => $first_result['uploaded_key'] ?? '',
                'uploaded_msg' => $first_result['uploaded_msg'] ?? null
            ];
        }
    }

    public function storage_delete_single( WP_REST_Request $request ) {
        $params = $request->get_params();
        if ( !isset( $params['post_type'], $params['id'], $params['meta_key'], $params['file_key'] ) ) {
            return new WP_Error( __METHOD__, 'Missing parameters.' );
        }

        if ( !( method_exists( 'DT_Storage_API', 'delete_file' ) && DT_Storage_API::is_enabled() ) ) {
            return new WP_Error( __METHOD__, 'DT_Storage_API Delete Function Unavailable.' );
        }

        $post_type = $params['post_type'];
        $post_id = $params['id'];
        $meta_key = $params['meta_key'];
        $file_key_to_delete = sanitize_text_field( wp_unslash( $params['file_key'] ) );

        // Fetch existing meta key value (should be an array for multi-file fields).
        $meta_key_value = get_post_meta( $post_id, $meta_key, true );

        if ( empty( $meta_key_value ) ) {
            return [
                'deleted' => false,
                'deleted_key' => '',
                'error' => 'No files found for this field.'
            ];
        }

        // Handle array of files (multi-file field).
        if ( is_array( $meta_key_value ) ) {
            $file_found = false;
            $updated_files = [];
            $deleted_file_name = '';

            foreach ( $meta_key_value as $file_object ) {
                // Handle both array format (with 'key') and string format (backward compatibility).
                $file_key = is_array( $file_object ) && isset( $file_object['key'] )
                    ? $file_object['key']
                    : ( is_string( $file_object ) ? $file_object : '' );

                if ( $file_key === $file_key_to_delete ) {
                    $file_found = true;
                    // Extract file name before deletion
                    if ( is_array( $file_object ) && isset( $file_object['name'] ) ) {
                        $deleted_file_name = $file_object['name'];
                    } else {
                        $deleted_file_name = basename( $file_key );
                    }
                    // Delete file from storage.
                    $result = DT_Storage_API::delete_file( $file_key );
                    if ( $result && isset( $result['file_deleted'] ) && $result['file_deleted'] ) {
                        // File deleted successfully, don't add it back to array.
                        continue;
                    }
                }

                // Keep file in array.
                $updated_files[] = $file_object;
            }

            if ( $file_found ) {
                // Store old value for activity logging
                $old_meta_value = $meta_key_value;
                
                // Update post meta with remaining files.
                if ( !empty( $updated_files ) ) {
                    update_post_meta( $post_id, $meta_key, $updated_files );
                    $new_meta_value = $updated_files;
                } else {
                    // No files left, delete meta key.
                    delete_post_meta( $post_id, $meta_key );
                    $new_meta_value = '';
                }
                
                // Log activity for file deletion
                $post_settings = DT_Posts::get_post_settings( $post_type );
                $field_name = $post_settings['fields'][ $meta_key ]['name'] ?? $meta_key;
                $object_note = sprintf( _x( 'Deleted file: %s from %s', 'file_upload activity', 'disciple_tools' ), $deleted_file_name, $field_name );
                
                dt_activity_insert( [
                    'action'            => 'field_update',
                    'object_type'       => $post_type,
                    'object_id'         => $post_id,
                    'object_name'       => get_the_title( $post_id ),
                    'meta_key'          => $meta_key,
                    'meta_value'        => maybe_serialize( $new_meta_value ),
                    'old_value'         => maybe_serialize( $old_meta_value ),
                    'field_type'        => 'file_upload',
                    'object_note'       => $object_note,
                ] );

                return [
                    'deleted' => true,
                    'deleted_key' => $file_key_to_delete
                ];
            } else {
                return [
                    'deleted' => false,
                    'deleted_key' => '',
                    'error' => 'File not found in field.'
                ];
            }
        } else {
            // Single file format (backward compatibility).
            if ( $meta_key_value === $file_key_to_delete ) {
                $result = DT_Storage_API::delete_file( $file_key_to_delete );
                $deleted = $result['file_deleted'] ?? false;

                if ( $deleted ) {
                    // Store old value for activity logging
                    $old_meta_value = $meta_key_value;
                    delete_post_meta( $post_id, $meta_key );
                    
                    // Log activity for file deletion
                    $post_settings = DT_Posts::get_post_settings( $post_type );
                    $field_name = $post_settings['fields'][ $meta_key ]['name'] ?? $meta_key;
                    $file_name = basename( $file_key_to_delete );
                    $object_note = sprintf( _x( 'Deleted file: %s from %s', 'file_upload activity', 'disciple_tools' ), $file_name, $field_name );
                    
                    dt_activity_insert( [
                        'action'            => 'field_update',
                        'object_type'       => $post_type,
                        'object_id'         => $post_id,
                        'object_name'       => get_the_title( $post_id ),
                        'meta_key'          => $meta_key,
                        'meta_value'        => '',
                        'old_value'         => $old_meta_value,
                        'field_type'        => 'file_upload',
                        'object_note'       => $object_note,
                    ] );
                }

                return [
                    'deleted' => $deleted,
                    'deleted_key' => $file_key_to_delete
                ];
            } else {
                return [
                    'deleted' => false,
                    'deleted_key' => '',
                    'error' => 'File key does not match.'
                ];
            }
        }
    }

    public function storage_rename_single( WP_REST_Request $request ) {
        $params = $request->get_params();
        if ( !isset( $params['post_type'], $params['id'], $params['meta_key'], $params['file_key'], $params['new_name'] ) ) {
            return new WP_Error( __METHOD__, 'Missing parameters.' );
        }

        $post_type = $params['post_type'];
        $post_id = $params['id'];
        $meta_key = sanitize_text_field( wp_unslash( $params['meta_key'] ) );
        $file_key_to_rename = sanitize_text_field( wp_unslash( $params['file_key'] ) );
        $new_name = trim( sanitize_file_name( wp_unslash( $params['new_name'] ) ) );

        if ( empty( $new_name ) ) {
            return [
                'renamed' => false,
                'error' => 'File name cannot be empty.',
            ];
        }

        $meta_key_value = get_post_meta( $post_id, $meta_key, true );

        if ( empty( $meta_key_value ) ) {
            return [
                'renamed' => false,
                'error' => 'No files found for this field.',
            ];
        }

        if ( is_array( $meta_key_value ) ) {
            $file_found = false;
            $updated_files = [];
            $old_file_name = '';

            foreach ( $meta_key_value as $file_object ) {
                $file_key = is_array( $file_object ) && isset( $file_object['key'] )
                    ? $file_object['key']
                    : ( is_string( $file_object ) ? $file_object : '' );

                if ( $file_key === $file_key_to_rename ) {
                    $file_found = true;
                    // Extract old file name before renaming
                    if ( is_array( $file_object ) && isset( $file_object['name'] ) ) {
                        $old_file_name = $file_object['name'];
                    } else {
                        $old_file_name = basename( $file_key );
                    }
                    
                    if ( is_array( $file_object ) ) {
                        $file_object['name'] = $new_name;
                        $updated_files[] = $file_object;
                    } else {
                        $updated_files[] = [
                            'key' => $file_key,
                            'name' => $new_name,
                        ];
                    }
                } else {
                    $updated_files[] = $file_object;
                }
            }

            if ( $file_found ) {
                // Store old value for activity logging
                $old_meta_value = $meta_key_value;
                update_post_meta( $post_id, $meta_key, $updated_files );
                
                // Log activity for file rename
                $post_settings = DT_Posts::get_post_settings( $post_type );
                $field_name = $post_settings['fields'][ $meta_key ]['name'] ?? $meta_key;
                $object_note = sprintf( _x( 'Renamed file from %s to %s in %s', 'file_upload activity', 'disciple_tools' ), $old_file_name, $new_name, $field_name );
                
                dt_activity_insert( [
                    'action'            => 'field_update',
                    'object_type'       => $post_type,
                    'object_id'         => $post_id,
                    'object_name'       => get_the_title( $post_id ),
                    'meta_key'          => $meta_key,
                    'meta_value'        => maybe_serialize( $updated_files ),
                    'old_value'         => maybe_serialize( $old_meta_value ),
                    'field_type'        => 'file_upload',
                    'object_note'       => $object_note,
                ] );
                
                return [
                    'renamed' => true,
                    'file_key' => $file_key_to_rename,
                    'new_name' => $new_name,
                ];
            } else {
                return [
                    'renamed' => false,
                    'error' => 'File not found in field.',
                ];
            }
        } else {
            return [
                'renamed' => false,
                'error' => 'Invalid file data format.',
            ];
        }
    }

    public function storage_download( WP_REST_Request $request ) {
        $params = $request->get_params();
        if ( !isset( $params['post_type'], $params['id'], $params['meta_key'], $params['file_key'] ) ) {
            return new WP_Error( __METHOD__, 'Missing parameters.' );
        }

        if ( !( method_exists( 'DT_Storage_API', 'get_file_url' ) && DT_Storage_API::is_enabled() ) ) {
            return new WP_Error( __METHOD__, 'DT_Storage_API Download Function Unavailable.' );
        }

        $post_type = sanitize_text_field( wp_unslash( $params['post_type'] ) );
        $post_id = sanitize_text_field( wp_unslash( $params['id'] ) );
        $meta_key = sanitize_text_field( wp_unslash( $params['meta_key'] ) );
        $file_key = sanitize_text_field( wp_unslash( $params['file_key'] ) );

        // Verify file exists in post meta and extract file info
        $meta_key_value = get_post_meta( $post_id, $meta_key, true );
        $file_found = false;
        $file_name = '';
        $file_type = '';

        if ( is_array( $meta_key_value ) ) {
            foreach ( $meta_key_value as $file_object ) {
                $file_key_from_meta = is_array( $file_object ) && isset( $file_object['key'] )
                    ? $file_object['key']
                    : ( is_string( $file_object ) ? $file_object : '' );

                if ( $file_key_from_meta === $file_key ) {
                    $file_found = true;
                    $file_name = is_array( $file_object ) && isset( $file_object['name'] )
                        ? $file_object['name']
                        : basename( $file_key );

                    // Extract type from metadata (priority 1 for content-type)
                    $file_type = is_array( $file_object ) && isset( $file_object['type'] )
                        ? $file_object['type']
                        : '';
                    break;
                }
            }
        } elseif ( $meta_key_value === $file_key ) {
            $file_found = true;
            $file_name = basename( $file_key );
            $file_type = '';
        }

        if ( !$file_found ) {
            return new WP_Error( __METHOD__, 'File not found in post meta.' );
        }

        // Generate presigned URL
        $presigned_url = DT_Storage_API::get_file_url( $file_key );

        if ( empty( $presigned_url ) ) {
            return new WP_Error( __METHOD__, 'Failed to generate download URL.' );
        }

        // Fetch file from S3 (server-side, no CORS)
        // Note: Do not use 'stream' => true as it tries to use URL as filename, causing "File name too long" errors
        $response = wp_remote_get( $presigned_url, [
            'timeout' => 300, // 5 minutes for large files
            'redirection' => 5,
        ] );

        if ( is_wp_error( $response ) ) {
            return new WP_Error( __METHOD__, 'Failed to fetch file from storage: ' . $response->get_error_message() );
        }

        $response_code = wp_remote_retrieve_response_code( $response );
        if ( $response_code !== 200 ) {
            return new WP_Error( __METHOD__, 'Failed to fetch file from storage. Response code: ' . $response_code );
        }

        // Get file content
        $file_content = wp_remote_retrieve_body( $response );

        // Determine content type with priority:
        // 1. From metadata (file_type variable)
        // 2. From S3 response header
        // 3. From file extension (fallback)
        $content_type = 'application/octet-stream'; // Default fallback

        if ( !empty( $file_type ) ) {
            // Priority 1: Use type from metadata database
            $content_type = $file_type;
        } else {
            // Priority 2: Try S3 response header
            $content_type = wp_remote_retrieve_header( $response, 'content-type' );

            if ( empty( $content_type ) ) {
                // Priority 3: Extension-based detection
                $file_ext = strtolower( pathinfo( $file_name, PATHINFO_EXTENSION ) );
                $mime_types = [
                    'pdf' => 'application/pdf',
                    'doc' => 'application/msword',
                    'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'txt' => 'text/plain',
                    'csv' => 'text/csv',
                    'json' => 'application/json',
                    'xml' => 'application/xml',
                    'html' => 'text/html',
                    'htm' => 'text/html',
                    'jpg' => 'image/jpeg',
                    'jpeg' => 'image/jpeg',
                    'png' => 'image/png',
                    'gif' => 'image/gif',
                ];
                $content_type = isset( $mime_types[ $file_ext ] )
                    ? $mime_types[ $file_ext ]
                    : 'application/octet-stream';
            }
        }

        // Set headers for file download
        header( 'Content-Type: ' . $content_type );
        header( 'Content-Disposition: attachment; filename="' . esc_attr( $file_name ) . '"' );
        header( 'Content-Length: ' . strlen( $file_content ) );
        header( 'Cache-Control: no-cache, must-revalidate' );
        header( 'Pragma: no-cache' );

        // Output file content
        echo $file_content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

        // Exit to prevent REST API wrapper from interfering
        exit;
    }

    public function storage_delete( WP_REST_Request $request ) {
        $params = $request->get_params();
        if ( !isset( $params['post_type'], $params['id'], $params['meta_key'] ) ) {
            return new WP_Error( __METHOD__, 'Missing parameters.' );
        }

        if ( !( method_exists( 'DT_Storage_API', 'delete_file' ) && DT_Storage_API::is_enabled() ) ) {
            return new WP_Error( __METHOD__, 'DT_Storage_API Delete Function Unavailable.' );
        }

        $post_type = $params['post_type'];
        $post_id = $params['id'];
        $meta_key = $params['meta_key'];

        // Fetch existing meta key value.
        $meta_key_value = get_post_meta( $post_id, $meta_key, true );

        if ( empty( $meta_key_value ) ) {
            return [
                'deleted' => false,
                'deleted_key' => '',
                'error' => 'No files found for this field.'
            ];
        }

        // Handle array of files (multi-file field).
        if ( is_array( $meta_key_value ) ) {
            $deleted_keys = [];
            $deleted_count = 0;
            $old_meta_value = $meta_key_value;

            foreach ( $meta_key_value as $file_object ) {
                // Handle both array format (with 'key') and string format.
                $file_key = is_array( $file_object ) && isset( $file_object['key'] )
                    ? $file_object['key']
                    : ( is_string( $file_object ) ? $file_object : '' );

                if ( !empty( $file_key ) ) {
                    $result = DT_Storage_API::delete_file( $file_key );
                    if ( $result && isset( $result['file_deleted'] ) && $result['file_deleted'] ) {
                        $deleted_keys[] = $file_key;
                        $deleted_count++;
                    }
                }
            }

            // Delete corresponding meta data.
            delete_post_meta( $post_id, $meta_key );
            
            // Log activity for deleting all files
            if ( $deleted_count > 0 ) {
                $post_settings = DT_Posts::get_post_settings( $post_type );
                $field_name = $post_settings['fields'][ $meta_key ]['name'] ?? $meta_key;
                if ( $deleted_count === 1 ) {
                    $file_name = '';
                    foreach ( $meta_key_value as $file_object ) {
                        if ( is_array( $file_object ) && isset( $file_object['name'] ) ) {
                            $file_name = $file_object['name'];
                            break;
                        }
                    }
                    if ( empty( $file_name ) && !empty( $deleted_keys[0] ) ) {
                        $file_name = basename( $deleted_keys[0] );
                    }
                    $object_note = sprintf( _x( 'Deleted file: %s from %s', 'file_upload activity', 'disciple_tools' ), $file_name, $field_name );
                } else {
                    $object_note = sprintf( _x( 'Deleted all %d files from %s', 'file_upload activity', 'disciple_tools' ), $deleted_count, $field_name );
                }
                
                dt_activity_insert( [
                    'action'            => 'field_update',
                    'object_type'       => $post_type,
                    'object_id'         => $post_id,
                    'object_name'       => get_the_title( $post_id ),
                    'meta_key'          => $meta_key,
                    'meta_value'        => '',
                    'old_value'         => maybe_serialize( $old_meta_value ),
                    'field_type'        => 'file_upload',
                    'object_note'       => $object_note,
                ] );
            }

            return [
                'deleted' => $deleted_count > 0,
                'deleted_count' => $deleted_count,
                'deleted_keys' => $deleted_keys
            ];
        } else {
            // Single file format (backward compatibility).
            $old_meta_value = $meta_key_value;
            $result = DT_Storage_API::delete_file( $meta_key_value );
            $deleted = $result['file_deleted'] ?? false;
            $deleted_key = $result['file_key'] ?? '';

            // Finally, delete corresponding meta data.
            delete_post_meta( $post_id, $meta_key );
            
                // Log activity for file deletion
                if ( $deleted ) {
                    $post_settings = DT_Posts::get_post_settings( $post_type );
                    $field_name = $post_settings['fields'][ $meta_key ]['name'] ?? $meta_key;
                    $file_name = basename( $old_meta_value );
                    $object_note = sprintf( _x( 'Deleted file: %s from %s', 'file_upload activity', 'disciple_tools' ), $file_name, $field_name );
                
                dt_activity_insert( [
                    'action'            => 'field_update',
                    'object_type'       => $post_type,
                    'object_id'         => $post_id,
                    'object_name'       => get_the_title( $post_id ),
                    'meta_key'          => $meta_key,
                    'meta_value'        => '',
                    'old_value'         => $old_meta_value,
                    'field_type'        => 'file_upload',
                    'object_note'       => $object_note,
                ] );
            }

            return [
                'deleted' => $deleted,
                'deleted_key' => $deleted_key
            ];
        }
    }
}
