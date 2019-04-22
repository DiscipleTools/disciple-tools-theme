<?php
/**
 * Custom endpoints file
 *
 * @package  Disciple_Tools
 * @category Plugin
 * @author   Chasm.Solutions & Kingdom.Training
 * @since    0.1.0
 */
if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly.

/**
 * Class Disciple_Tools_Groups_Endpoints
 */
class Disciple_Tools_Groups_Endpoints
{

    private static $_instance = null;

    /**
     * @return \Disciple_Tools_Groups_Endpoints|null
     */
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    private $version = 1;
    private $context = "dt";
    private $namespace;

    /**
     * Disciple_Tools_Groups_Endpoints constructor.
     */
    public function __construct() {
        $this->namespace = $this->context . "/v" . intval( $this->version );
        add_action( 'rest_api_init', [ $this, 'add_api_routes' ] );
    }

    public function add_api_routes() {
        register_rest_route(
            $this->namespace, '/groups', [
                "methods"  => "GET",
                "callback" => [ $this, 'get_viewable_groups' ],
            ]
        );
        register_rest_route(
            $this->namespace, '/groups/search', [
                "methods"  => "GET",
                "callback" => [ $this, 'search_viewable_groups' ],
            ]
        );
        register_rest_route(
            $this->namespace, '/groups/compact', [
                'methods'  => 'GET',
                'callback' => [ $this, 'get_groups_compact' ],
            ]
        );
        register_rest_route(
            $this->namespace, '/group/(?P<id>\d+)', [
                'methods'  => 'POST',
                'callback' => [ $this, 'update_group' ],
            ]
        );
        register_rest_route(
            $this->namespace, '/group/(?P<id>\d+)', [
                'methods'  => 'GET',
                'callback' => [ $this, 'get_group' ],
            ]
        );
        register_rest_route(
            $this->namespace, '/group/(?P<id>\d+)/comments', [
                "methods"  => "GET",
                "callback" => [ $this, 'get_comments' ],
            ]
        );
        register_rest_route(
            $this->namespace, '/group/(?P<id>\d+)/comment', [
                "methods"  => "POST",
                "callback" => [ $this, 'post_comment' ],
            ]
        );
        register_rest_route(
            $this->namespace, '/group/(?P<id>\d+)/comment/update', [
                "methods"  => "POST",
                "callback" => [ $this, 'update_comment' ],
            ]
        );
        register_rest_route(
            $this->namespace, '/group/(?P<id>\d+)/comment', [
                "methods"  => "DELETE",
                "callback" => [ $this, 'delete_comment' ],
            ]
        );
        register_rest_route(
            $this->namespace, '/group/(?P<id>\d+)/activity', [
                "methods"  => "GET",
                "callback" => [ $this, 'get_activity' ],
            ]
        );
        register_rest_route(
            $this->namespace, '/group/(?P<id>\d+)/shared-with', [
                "methods"  => "GET",
                "callback" => [ $this, 'shared_with' ],
            ]
        );

        register_rest_route(
            $this->namespace, '/group/(?P<id>\d+)/remove-shared', [
                "methods"  => "POST",
                "callback" => [ $this, 'remove_shared' ],
            ]
        );

        register_rest_route(
            $this->namespace, '/group/(?P<id>\d+)/add-shared', [
                "methods"  => "POST",
                "callback" => [ $this, 'add_shared' ],
            ]
        );

        register_rest_route(
            $this->namespace, '/group/create', [
                "methods" => "POST",
                "callback" => [ $this, 'create_group' ],
            ]
        );
        register_rest_route(
            $this->namespace, '/group/counts', [
                "methods" => "GET",
                "callback" => [ $this, 'get_group_default_filter_counts' ],
            ]
        );
        register_rest_route(
            $this->namespace, '/groups/settings', [
                "methods"  => "GET",
                "callback" => [ $this, 'get_settings' ],
            ]
        );
        register_rest_route(
            $this->namespace, '/group/(?P<id>\d+)/following', [
                "methods"  => "GET",
                "callback" => [ $this, 'get_following' ],
            ]
        );
    }

    /**
     * @param WP_REST_Request $request
     *
     * @return array|\WP_Query
     */
    public function get_viewable_groups( WP_REST_Request $request ) {
        $params = $request->get_params();
        $most_recent = isset( $params["most_recent"] ) ? $params["most_recent"] : 0;
        $groups = Disciple_Tools_Groups::get_viewable_groups( $most_recent );
        if ( is_wp_error( $groups ) ) {
            return $groups;
        }

        return [
            "groups" => $this->add_related_info_to_groups( $groups["groups"] ),
            "total" => $groups["total"],
            "deleted" => $groups["deleted"]
        ];
    }

    public function search_viewable_groups( WP_REST_Request $request ) {
        $params = $request->get_params();
        $result = Disciple_Tools_Groups::search_viewable_groups( $params, true );
        if ( is_wp_error( $result ) ) {
            return $result;
        }

        return [
            "groups" => $this->add_related_info_to_groups( $result["groups"] ),
            "total" => $result["total"],
        ];
    }


    /**
     * @param array $groups
     *
     * @return array
     */
    private function add_related_info_to_groups( array $groups ) {
        $group_ids = array_map(
            function( $g ){ return $g->ID; },
            $groups
        );
        $geonames = DT_Mapping_Module::instance()->query( "get_geoname_ids_and_names_for_post_ids", [ "post_ids" => $group_ids ] );
        p2p_type( 'contacts_to_groups' )->each_connected( $groups, [], 'members' );
        p2p_type( 'groups_to_leaders' )->each_connected( $groups, [], 'leaders' );
        $rv = [];
        foreach ( $groups as $group ) {
            $meta_fields = get_post_custom( $group->ID );
            $group_array = [];
            $group_array["ID"] = $group->ID;
            $group_array["post_title"] = $group->post_title;
            $group_array['permalink'] = get_post_permalink( $group->ID );
            $group_array['locations'] = [];
            foreach ( $geonames[$group->ID] as $location ) {
                $group_array['locations'][] = $location["name"];
            }
            $group_array['leaders'] = [];
            $group_array['member_count'] = $meta_fields["member_count"] ?? 0;
            foreach ( $group->leaders as $leader ){
                $group_array['leaders'][] = [
                    'post_title' => $leader->post_title,
                    'permalink'  => get_permalink( $leader->ID ),
                ];
            }
            $group_array['group_status'] = "";
            foreach ( $meta_fields as $meta_key => $meta_value ) {
                if ( $meta_key == 'group_status' ) {
                    $group_array[ $meta_key ] = $meta_value[0];
                } elseif ( $meta_key == 'group_type' ) {
                    $group_array[ $meta_key ] = $meta_value[0];
                } elseif ( $meta_key == 'last_modified' ) {
                    $group_array[ $meta_key ] = (int) $meta_value[0];
                }
            }
            if ( !isset( $group_array["last_modified"] ) ){
                $group_array["last_modified"] = 0;
            }
            $rv[] = $group_array;
        }

        return $rv;
    }

    /**
     * @param \WP_REST_Request $request
     *
     * @return array|\WP_Query
     */
    public function get_groups_compact( WP_REST_Request $request ) {
        $params = $request->get_params();
        $search = "";
        if ( isset( $params['s'] ) ) {
            $search = $params['s'];
        }
        $groups = Disciple_Tools_Groups::get_groups_compact( $search );

        return $groups;
    }

    /**
     * @param \WP_REST_Request $request
     *
     * @return int|\WP_Error
     */
    public function update_group( WP_REST_Request $request ) {
        $params = $request->get_params();
        $body = $request->get_json_params();
        if ( isset( $params['id'] ) ) {
            return Disciple_Tools_Groups::update_group( $params['id'], $body, true );
        } else {
            return new WP_Error( "update_contact", "Missing a valid contact id", [ 'status' => 400 ] );
        }
    }

    /**
     * Get a single group by ID
     *
     * @param  WP_REST_Request $request
     *
     * @access public
     * @since  0.1.0
     * @return array|WP_Error The group on success
     */
    public function get_group( WP_REST_Request $request ) {
        $params = $request->get_params();
        if ( isset( $params['id'] ) ) {
            $result = Disciple_Tools_Groups::get_group( $params['id'], true );

            return $result; // Could be permission WP_Error
        } else {
            return new WP_Error( "get_group_error", "Please provide a valid id", [ 'status' => 400 ] );
        }
    }


    /**
     * @param \WP_REST_Request $request
     *
     * @return false|int|\WP_Error|\WP_REST_Response
     */
    public function post_comment( WP_REST_Request $request ) {
        $params = $request->get_params();
        $body = $request->get_json_params();
        if ( isset( $params['id'] ) && isset( $body['comment'] ) ) {
            $result = Disciple_Tools_Groups::add_comment( $params['id'], $body["comment"] );

            if ( is_wp_error( $result ) ) {
                return $result;
            } else {
                $comment = get_comment( $result );

                return new WP_REST_Response( [
                    "comment_id" => $result,
                    "comment" => $comment
                ] );
            }
        } else {
            return new WP_Error( "post_comment", "Missing a valid group id", [ 'status' => 400 ] );
        }
    }


    /**
     * @param \WP_REST_Request $request
     *
     * @return false|int|\WP_Error|\WP_REST_Response
     */
    public function update_comment( WP_REST_Request $request ) {
        $params = $request->get_params();
        $body = $request->get_json_params();
        if ( isset( $params['id'] ) && isset( $body['comment_ID'] ) && isset( $body['comment_content'] ) ) {
            return Disciple_Tools_groups::update_comment( $params['id'], $body["comment_ID"], $body["comment_content"], true );
        } else {
            return new WP_Error( "post_comment", "Missing a valid group id, comment id or missing new comment.", [ 'status' => 400 ] );
        }
    }

    /**
     * @param \WP_REST_Request $request
     *
     * @return false|int|\WP_Error|\WP_REST_Response
     */
    public function delete_comment( WP_REST_Request $request ) {
        $params = $request->get_params();
        $body = $request->get_json_params();
        if ( isset( $params['id'] ) && isset( $body['comment_ID'] ) ) {
            return Disciple_Tools_groups::delete_comment( $params['id'], $body["comment_ID"], true );
        } else {
            return new WP_Error( "post_comment", "Missing a valid group id or comment id", [ 'status' => 400 ] );
        }
    }

    /**
     * @param \WP_REST_Request $request
     *
     * @return array|int|\WP_Error
     */
    public function get_comments( WP_REST_Request $request ) {
        $params = $request->get_params();
        if ( isset( $params['id'] ) ) {
            return Disciple_Tools_Groups::get_comments( $params['id'] );
        } else {
            return new WP_Error( "get_comments", "Missing a valid group id", [ 'status' => 400 ] );
        }
    }

    /**
     * @param \WP_REST_Request $request
     *
     * @return array|null|object|\WP_Error
     */
    public function get_activity( WP_REST_Request $request ) {
        $params = $request->get_params();
        if ( isset( $params['id'] ) ) {
            return Disciple_Tools_Groups::get_activity( $params['id'] );
        } else {
            return new WP_Error( "get_activity", "Missing a valid group id", [ 'status' => 400 ] );
        }
    }

    /**
     * @param \WP_REST_Request $request
     *
     * @return array|mixed|\WP_Error|\WP_REST_Response
     */
    public function shared_with( WP_REST_Request $request ) {
        $params = $request->get_params();
        if ( isset( $params['id'] ) ) {
            $result = Disciple_Tools_Groups::get_shared_with_on_group( $params['id'] );

            if ( is_wp_error( $result ) ) {
                return $result;
            } else {
                return new WP_REST_Response( $result );
            }
        } else {
            return new WP_Error( 'shared_with', "Missing a valid group id", [ 'status' => 400 ] );
        }
    }

    /**
     * @param \WP_REST_Request $request
     *
     * @return false|int|\WP_Error|\WP_REST_Response
     */
    public function remove_shared( WP_REST_Request $request ) {
        $params = $request->get_params();
        if ( isset( $params['id'] ) ) {
            $result = Disciple_Tools_Groups::remove_shared_on_group( $params['id'], $params['user_id'] );

            if ( is_wp_error( $result ) ) {
                return $result;
            } else {
                return new WP_REST_Response( $result );
            }
        } else {
            return new WP_Error( 'remove_shared', "Missing a valid group id", [ 'status' => 400 ] );
        }
    }

    /**
     * @param \WP_REST_Request $request
     *
     * @return false|int|\WP_Error|\WP_REST_Response
     */
    public function add_shared( WP_REST_Request $request ) {
        $params = $request->get_params();
        if ( isset( $params['id'] ) ) {
            $result = Disciple_Tools_Groups::add_shared_on_group( $params['id'], $params['user_id'] );

            if ( is_wp_error( $result ) ) {
                return $result;
            } else {
                return new WP_REST_Response( $result );
            }
        } else {
            return new WP_Error( 'add_shared', "Missing a valid group id", [ 'status' => 400 ] );
        }
    }

    /**
     * @param \WP_REST_Request $request
     *
     * @return array|int|\WP_Error
     */
    public function create_group( WP_REST_Request $request ) {
        $fields = $request->get_json_params();
        $result = Disciple_Tools_Groups::create_group( $fields, true );
        if ( is_wp_error( $result ) ) {
            return $result;
        }
        return array(
            "post_id" => (int) $result,
            "permalink" => get_post_permalink( $result ),
        );
    }

    public function get_group_default_filter_counts( WP_REST_Request $request ){
        $params = $request->get_params();
        $tab = $params["tab"] ?? null;
        $show_closed = isset( $params["closed"] ) && $params["closed"] == "true";
        return Disciple_Tools_Groups::get_group_default_filter_counts( $tab, $show_closed );
    }

    public function get_settings(){
        return Disciple_Tools_Groups::get_settings();
    }

    public function get_following( WP_REST_Request $request ) {
        $params = $request->get_params();
        if ( isset( $params['id'] ) ) {
            return Disciple_Tools_Posts::get_users_following_post( "groups", $params['id'] );
        } else {
            return new WP_Error( __FUNCTION__, "Missing a valid group id", [ 'status' => 400 ] );
        }
    }

}
