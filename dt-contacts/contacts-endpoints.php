<?php
if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

/**
 * Class Disciple_Tools_Contacts_Endpoints
 */
class Disciple_Tools_Contacts_Endpoints
{

    /**
     * @var object Public_Hooks instance variable
     */
    private static $_instance = null;

    /**
     * Public_Hooks. Ensures only one instance of Public_Hooks is loaded or can be loaded.
     *
     * @return Disciple_Tools_Contacts_Endpoints instance
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
    private $version = 1;
    private $context = "dt";
    private $namespace;
    private $public_namespace;
    private $namespace_v2 = 'dt-posts/v2';


    /**
     * Disciple_Tools_Contacts_Endpoints constructor.
     */
    public function __construct() {
        $this->namespace = $this->context . "/v" . intval( $this->version );
        $this->public_namespace = 'dt-public/v1';
        add_action( 'rest_api_init', [ $this, 'add_api_routes' ] );
    }

    /**
     * Add the api routes
     */
    public function add_api_routes() {
        //setup v2
        $this->setup_contacts_specific_endpoints( $this->namespace_v2 );
        //setup v1
        $this->setup_contacts_specific_endpoints( $this->namespace );

        /**
         * Deprecated v1 endpoints
         */
        register_rest_route(
            $this->namespace, '/dt-public/contact/create', [
                'methods'  => 'POST',
                'callback' => [ $this, 'public_create_contact' ],
            ]
        );
        register_rest_route(
            $this->namespace, '/contact/create', [
                "methods"  => "POST",
                "callback" => [ $this, 'create_contact' ],
            ]
        );
        register_rest_route(
            $this->namespace, '/contact/(?P<id>\d+)', [
                "methods"  => "GET",
                "callback" => [ $this, 'get_contact' ],
            ]
        );
        register_rest_route(
            $this->namespace, '/contact/(?P<id>\d+)', [
                "methods"  => "POST",
                "callback" => [ $this, 'update_contact' ],
            ]
        );
        register_rest_route(
            $this->namespace, '/contacts', [
                "methods"  => "GET",
                "callback" => [ $this, 'get_viewable_contacts' ],
            ]
        );
        register_rest_route(
            $this->namespace, '/contacts/search', [
                "methods"  => "GET",
                "callback" => [ $this, 'search_viewable_contacts' ],
            ]
        );
        register_rest_route(
            $this->namespace, '/contacts/compact', [
                "methods"  => "GET",
                "callback" => [ $this, 'get_contacts_compact' ],
            ]
        );
        register_rest_route(
            $this->namespace, '/contact/(?P<id>\d+)/comments', [
                "methods"  => "GET",
                "callback" => [ $this, 'get_comments' ],
            ]
        );
        register_rest_route(
            $this->namespace, '/contact/(?P<id>\d+)/comment', [
                "methods"  => "POST",
                "callback" => [ $this, 'post_comment' ],
            ]
        );
        register_rest_route(
            $this->namespace, '/dt-public/contact/(?P<id>\d+)/comment', [
                "methods"  => "POST",
                "callback" => [ $this, 'public_post_comment' ],
            ]
        );
        register_rest_route(
            $this->namespace, '/contact/(?P<id>\d+)/comment/update', [
                "methods"  => "POST",
                "callback" => [ $this, 'update_comment' ],
            ]
        );
        register_rest_route(
            $this->namespace, '/contact/(?P<id>\d+)/comment', [
                "methods"  => "DELETE",
                "callback" => [ $this, 'delete_comment' ],
            ]
        );
        register_rest_route(
            $this->namespace, '/contact/(?P<id>\d+)/activity', [
                "methods"  => "GET",
                "callback" => [ $this, 'get_activity' ],
            ]
        );
        register_rest_route(
            $this->namespace, '/contact/(?P<id>\d+)/activity/(?P<activity_id>\d+)', [
                "methods"  => "GET",
                "callback" => [ $this, 'get_single_activity' ],
            ]
        );
        register_rest_route(
            $this->namespace, '/contact/(?P<id>\d+)/shared-with', [
                "methods"  => "GET",
                "callback" => [ $this, 'shared_with' ],
            ]
        );
        register_rest_route(
            $this->namespace, '/contact/(?P<id>\d+)/remove-shared', [
                "methods"  => "POST",
                "callback" => [ $this, 'remove_shared' ],
            ]
        );
        register_rest_route(
            $this->namespace, '/contact/(?P<id>\d+)/add-shared', [
                "methods"  => "POST",
                "callback" => [ $this, 'add_shared' ],
            ]
        );
        register_rest_route(
            $this->namespace, '/contact/(?P<id>\d+)/following', [
                "methods"  => "GET",
                "callback" => [ $this, 'get_following' ],
            ]
        );
        register_rest_route(
            $this->namespace, '/contact/multi-select-options', [
                "methods" => "GET",
                "callback" => [ $this, 'get_multi_select_options' ]
            ]
        );
        register_rest_route(
            $this->public_namespace, '/contact/transfer', [
                "methods"  => "POST",
                "callback" => [ $this, 'public_contact_transfer' ],
            ]
        );
        register_rest_route(
            $this->namespace, '/contacts/settings', [
                "methods"  => "GET",
                "callback" => [ $this, 'get_settings' ],
            ]
        );

    }

    private function setup_contacts_specific_endpoints( string $namespace ){
        register_rest_route(
            $namespace, '/contacts/mergedetails', [
                "methods" => "GET",
                "callback" => [ $this, 'get_viewable_contacts' ]
            ]
        );
        register_rest_route(
            $namespace, '/contact/counts', [
                "methods" => "GET",
                "callback" => [ $this, 'get_contact_counts' ]
            ]
        );

        register_rest_route(
            $namespace, '/contact/list-sources', [
                "methods" => "GET",
                "callback" => [ $this, 'list_sources' ],
            ]
        );
        register_rest_route(
            $namespace, '/contacts/(?P<id>\d+)/duplicates', [
                "methods"  => "GET",
                "callback" => [ $this, 'get_duplicates_on_contact' ],
            ]
        );
        register_rest_route(
            $namespace, '/contact/transfer', [
                "methods"  => "POST",
                "callback" => [ $this, 'contact_transfer' ],
            ]
        );
        register_rest_route(
            $namespace, '/contacts/(?P<id>\d+)/revert/(?P<activity_id>\d+)', [
                "methods"  => "GET",
                "callback" => [ $this, 'revert_activity' ],
            ]
        );
        register_rest_route(
            $namespace, '/contacts/(?P<id>\d+)/accept', [
                "methods"  => "POST",
                "callback" => [ $this, 'accept_contact' ],
            ]
        );
    }


    /**
     * Create a contact from the PUBLIC api.
     *
     * @param  WP_REST_Request $request as application/json
     *
     * @access public
     * @since  0.1.0
     * @return array|WP_Error The new contact Id on success, an error on failure
     */
    public function public_create_contact( WP_REST_Request $request ) {
        $params = $request->get_params();
        $site_key = Site_Link_System::verify_transfer_token( $params['transfer_token'] );
        $silent = isset( $params["silent"] ) && ( $params["silent"] === "true" || $params["silent"] == true );
        if ( !$site_key ){
            return new WP_Error(
                "contact_creation_error",
                "Invalid or missing transfer_token", [ 'status' => 401 ]
            );
        }

        if ( isset( $params["fields"] ) ) {
            $result = Disciple_Tools_Contacts::create_contact( $params["fields"], false, $silent );
            return $result; // Could be permission WP_Error
        } else {
            return new WP_Error(
                "contact_creation_error",
                "missing fields param", [ 'status' => 401 ]
            );
        }
    }

    /**
     * Create a contact
     *
     * @param  WP_REST_Request $request
     *
     * @access public
     * @since  0.1.0
     * @return string|array The contact on success
     */
    public function create_contact( WP_REST_Request $request ) {
        $fields = $request->get_json_params() ?? $request->get_params();
        $get_params = $request->get_query_params();
        $silent = false;
        if ( isset( $get_params["silent"] ) && $get_params["silent"] === "true" ){
            $silent = true;
        }
        $result = Disciple_Tools_Contacts::create_contact( $fields, true, $silent );
        if ( is_wp_error( $result ) ) {
            return $result;
        }

        return [
            "post_id"   => (int) $result,
            "permalink" => get_post_permalink( $result ),
        ];
    }

    /**
     * Get a single contact by ID
     *
     * @param  WP_REST_Request $request
     *
     * @access public
     * @since  0.1.0
     * @return array|WP_Error The contact on success
     */
    public function get_contact( WP_REST_Request $request ) {
        $params = $request->get_params();
        if ( isset( $params['id'] ) ) {
            $result = DT_Posts::get_post( 'contacts', $params['id'], true );

            return $result; // Could be permission WP_Error
        } else {
            return new WP_Error( "get_contact_error", "Please provide a valid id", [ 'status' => 400 ] );
        }
    }

    /**
     * Update a single contact by ID
     *
     * @param  WP_REST_Request $request
     *
     * @access public
     * @since  0.1.0
     * @return WP_REST_Response|WP_Error Contact_id on success
     */
    public function update_contact( WP_REST_Request $request ) {
        $params = $request->get_params();
        $body = $request->get_json_params() ?? $request->get_params();
        if ( isset( $params['id'] ) ) {
            return DT_Posts::update_post( 'contacts', $params['id'], $body, true );
        } else {
            return new WP_Error( "update_contact", "Missing a valid contact id", [ 'status' => 400 ] );
        }
    }



    /**
     * @param array $contacts
     *
     * @return array
     */
    private function add_related_info_to_contacts( array $contacts ): array
    {
        $contact_ids = array_map(
            function( $c ){ return $c->ID; },
            $contacts
        );
        $geonames = Disciple_Tools_Mapping_Queries::get_geoname_ids_and_names_for_post_ids( $contact_ids );
        p2p_type( 'contacts_to_groups' )->each_connected( $contacts, [], 'groups' );
        $rv = [];
        foreach ( $contacts as $contact ) {
            $meta_fields = get_post_custom( $contact->ID );
            $contact_array = [];
            $contact_array["ID"] = $contact->ID;
            $contact_array["post_title"] = $contact->post_title;
            $contact_array["is_team_contact"] = $contact->is_team_contact ?? false;
            $contact_array['permalink'] = get_post_permalink( $contact->ID );
            $contact_array['overall_status'] = get_post_meta( $contact->ID, 'overall_status', true );
            $contact_array['locations'] = []; // @todo remove or rewrite? Because of geonames upgrade.
            foreach ( $geonames[$contact->ID] as $location ) {
                $contact_array['locations'][] = $location["name"]; // @todo remove or rewrite? Because of geonames upgrade.
            }
            $contact_array['groups'] = [];
            foreach ( $contact->groups as $group ) {
                $contact_array['groups'][] = [
                    'id'         => $group->ID,
                    'post_title' => $group->post_title,
                    'permalink'  => get_permalink( $group->ID ),
                ];
            }
            $contact_array['phone_numbers'] = [];
            $contact_array['requires_update'] = false;
            foreach ( $meta_fields as $meta_key => $meta_value ) {
                if ( strpos( $meta_key, "contact_phone" ) === 0 && strpos( $meta_key, "details" ) === false ) {
                    $contact_array['phone_numbers'] = array_merge( $contact_array['phone_numbers'], $meta_value );
                } elseif ( $meta_key === "milestones" ) {
                    $contact_array["milestones"] = $meta_value;
                } elseif ( $meta_key === "seeker_path" ) {
                    $contact_array[ $meta_key ] = $meta_value[0] ? $meta_value[0] : "none";
                } elseif ( $meta_key == "assigned_to" ) {
                    $type_and_id = explode( '-', $meta_value[0] );
                    if ( $type_and_id[0] == 'user' && isset( $type_and_id[1] ) ) {
                        $user = get_user_by( 'id', (int) $type_and_id[1] );
                        $contact_array["assigned_to"] = [
                            "id" => $type_and_id[1],
                            "type" => $type_and_id[0],
                            "name" => ( $user ? $user->display_name : "Nobody" ),
                            'user_login' => ( $user ? $user->user_login : "nobody" )
                        ];
                    }
                } elseif ( $meta_key == "requires_update" ) {
                    $contact_array[ $meta_key ] = $this->yes_no_to_boolean( $meta_value[0] );
                } elseif ( $meta_key == 'last_modified' ) {
                    $contact_array[ $meta_key ] = (int) $meta_value[0];
                }
            }

            $user_id = get_current_user_id();
            if ( isset( $contact_array["overall_status"] ) && isset( $contact_array["assigned_to"]["id"] ) &&
                 $contact_array["overall_status"] === "assigned" && $contact_array["assigned_to"]["id"] == $user_id){
                $contact_array["requires_update"] = true;
            }
            $rv[] = $contact_array;
        }
        return $rv;
    }

    /**
     * @param string $yes_no
     *
     * @return bool
     * @throws WP_Error|bool 'Expected yes or no'.
     */
    private static function yes_no_to_boolean( string $yes_no ) {
        if ( $yes_no === 'yes' || $yes_no === '1' ) {
            return true;
        } elseif ( $yes_no === 'no' ) {
            return false;
        } else {
            return false;
//            @todo move error to saving
//            throw new Error( "Expected yes or no, instead got $yes_no" );
        }
    }


    /**
     * Get Contacts viewable by a user
     *
     * @param  WP_REST_Request $request
     *
     * @access public
     * @since  0.1.0
     * @return array|WP_Error return the user's contacts
     */
    public function get_viewable_contacts( WP_REST_Request $request ) {
        $params = $request->get_params();
        $most_recent = isset( $params["most_recent"] ) ? $params["most_recent"] : 0;
        $result = Disciple_Tools_Contacts::get_viewable_contacts( (int) $most_recent, true );
        if ( is_wp_error( $result ) ) {
            return $result;
        }

        return [
            "contacts" => $this->add_related_info_to_contacts( $result["contacts"] ),
            "total" => $result["total"],
            "deleted" => $result["deleted"]
        ];
    }

    public function search_viewable_contacts( WP_REST_Request $request ) {
        $params = $request->get_params();
        $result = Disciple_Tools_Contacts::search_viewable_contacts( $params, true );
        if ( is_wp_error( $result ) ) {
            return $result;
        }

        return [
            "contacts" => $this->add_related_info_to_contacts( $result["contacts"] ),
            "total" => $result["total"],
        ];
    }


    /**
     * @param WP_REST_Request $request
     *
     * @return false|int|WP_Error|WP_REST_Response
     */
    public function post_comment( WP_REST_Request $request ) {
        $params = $request->get_params();
        $body = $request->get_json_params() ?? $request->get_params();
        $silent = isset( $params["silent"] ) && ( $params["silent"] === "true" || $params["silent"] == true );
        if ( isset( $params['id'] ) && isset( $body['comment'] ) ) {
            $result = DT_Posts::add_post_comment( 'contacts', $params['id'], $body["comment"], 'comment', [ "comment_date" => $body["date"] ?? null ], true, $silent );

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
            return new WP_Error( "post_comment", 'Missing a valid contact id or "comment" field', [ 'status' => 400 ] );
        }
    }

    /*
     * @return false|int|WP_Error|WP_REST_Response
     */
    public function public_post_comment( WP_REST_Request $request ) {
        $params = $request->get_params();
        $body = $request->get_json_params() ?? $request->get_params();
        $site_key = Site_Link_System::verify_transfer_token( $params['transfer_token'] );
        $silent = isset( $params["silent"] ) && ( $params["silent"] === "true" || $params["silent"] == true );
        if ( !$site_key ){
            return new WP_Error(
                "contact_creation_error",
                "Invalid or missing transfer_token", [ 'status' => 401 ]
            );
        }
        if ( isset( $params['id'] ) && isset( $body['comment'] ) ) {
            $result = DT_Posts::add_post_comment( 'contacts', $params['id'], $body["comment"], "comment", [ "comment_date" => $body["date"] ?? null ], false, $silent );

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
            return new WP_Error( "post_comment", 'Missing a valid contact id or "comment" field', [ 'status' => 400 ] );
        }
    }

    /**
     * @param WP_REST_Request $request
     *
     * @return false|int|WP_Error|WP_REST_Response
     */
    public function update_comment( WP_REST_Request $request ) {
        $params = $request->get_params();
        $body = $request->get_json_params() ?? $request->get_params();
        if ( isset( $params['id'] ) && isset( $body['comment_ID'] ) && isset( $body['comment_content'] ) ) {
            return DT_Posts::update_post_comment( $body["comment_ID"], $body["comment_content"], true );
        } else {
            return new WP_Error( "post_comment", "Missing a valid contact id, comment id or missing new comment.", [ 'status' => 400 ] );
        }
    }

    /**
     * @param WP_REST_Request $request
     *
     * @return false|int|WP_Error|WP_REST_Response
     */
    public function delete_comment( WP_REST_Request $request ) {
        $params = $request->get_params();
        $body = $request->get_json_params() ?? $request->get_params();
        if ( isset( $params['id'] ) && isset( $body['comment_ID'] ) ) {
            return DT_Posts::delete_post_comment( $body["comment_ID"], true );
        } else {
            return new WP_Error( "post_comment", "Missing a valid contact id or comment id", [ 'status' => 400 ] );
        }
    }

    /**
     * @param WP_REST_Request $request
     *
     * @return array|int|WP_Error|WP_REST_Response
     */
    public function get_comments( WP_REST_Request $request ) {
        $params = $request->get_params();
        if ( isset( $params['id'] ) ) {
            $result = DT_Posts::get_post_comments( 'contacts', $params['id'] );

            if ( is_wp_error( $result ) ) {
                return $result;
            } else {
                return new WP_REST_Response( $result["comments"] );
            }
        } else {
            return new WP_Error( "get_comments", "Missing a valid contact id", [ 'status' => 400 ] );
        }
    }

    /**
     * @param WP_REST_Request $request
     *
     * @return array|null|object|WP_Error|WP_REST_Response
     */
    public function get_activity( WP_REST_Request $request ) {
        $params = $request->get_params();
        if ( isset( $params['id'] ) ) {
            $result = DT_Posts::get_post_activity( 'contacts', $params['id'] );
            if ( is_wp_error( $result ) ) {
                return $result;
            } else {
                return new WP_REST_Response( $result["activity"] );
            }
        } else {
            return new WP_Error( "get_activity", "Missing a valid contact id", [ 'status' => 400 ] );
        }
    }

    /**
     * @param WP_REST_Request $request
     *
     * @return array|null|object|WP_Error|WP_REST_Response
     */
    public function get_single_activity( WP_REST_Request $request ) {
        $params = $request->get_params();
        if ( isset( $params['id'] ) && isset( $params["activity_id"] ) ) {
            $result = DT_Posts::get_post_single_activity( 'contacts', $params['id'], $params["activity_id"] );
            if ( is_wp_error( $result ) ) {
                return $result;
            } else {
                return new WP_REST_Response( $result );
            }
        } else {
            return new WP_Error( "get_activity", "Missing a valid contact id or activity id", [ 'status' => 400 ] );
        }
    }

    /**
     * @param WP_REST_Request $request
     *
     * @return array|null|object|WP_Error|WP_REST_Response
     */
    public function revert_activity( WP_REST_Request $request ) {
        $params = $request->get_params();
        if ( isset( $params['id'] ) && isset( $params["activity_id"] ) ) {
            $result = Disciple_Tools_Contacts::revert_activity( $params['id'], $params["activity_id"] );
            if ( is_wp_error( $result ) ) {
                return $result;
            } else {
                return new WP_REST_Response( $result );
            }
        } else {
            return new WP_Error( "get_activity", "Missing a valid contact id or activity id", [ 'status' => 400 ] );
        }
    }

    /**
     * @param WP_REST_Request $request
     *
     * @return array|WP_Error|WP_REST_Response
     */
    public function accept_contact( WP_REST_Request $request ) {
        $params = $request->get_params();
        $body = $request->get_json_params() ?? $request->get_params();
        if ( isset( $params['id'] ) ) {
            $result = Disciple_Tools_Contacts::accept_contact( $params['id'], $body["accept"] );

            if ( is_wp_error( $result ) ) {
                return $result;
            } else {
                return new WP_REST_Response( $result );
            }
        } else {
            return new WP_Error( "accept_contact", "Missing a valid contact id", [ 'status' => 400 ] );
        }
    }

    /**
     * @param WP_REST_Request $request
     *
     * @return array|mixed|WP_Error|WP_REST_Response
     */
    public function shared_with( WP_REST_Request $request ) {
        $params = $request->get_params();
        if ( isset( $params['id'] ) ) {
            $result = DT_Posts::get_shared_with( 'contacts', $params['id'] );

            if ( is_wp_error( $result ) ) {
                return $result;
            } else {
                return new WP_REST_Response( $result );
            }
        } else {
            return new WP_Error( 'shared_with', "Missing a valid contact id", [ 'status' => 400 ] );
        }
    }

    /**
     * @param WP_REST_Request $request
     *
     * @return false|int|WP_Error|WP_REST_Response
     */
    public function remove_shared( WP_REST_Request $request ) {
        $params = $request->get_params();
        if ( isset( $params['id'] ) ) {
            $result = DT_Posts::remove_shared( 'contacts', $params['id'], $params['user_id'] );

            if ( is_wp_error( $result ) ) {
                return $result;
            } else {
                return new WP_REST_Response( $result );
            }
        } else {
            return new WP_Error( 'remove_shared', "Missing a valid contact id", [ 'status' => 400 ] );
        }
    }

    /**
     * @param WP_REST_Request $request
     *
     * @return false|int|WP_Error|WP_REST_Response
     */
    public function add_shared( WP_REST_Request $request ) {
        $params = $request->get_params();
        if ( isset( $params['id'] ) && isset( $params['user_id'] ) ) {
            $result = DT_Posts::add_shared( 'contacts', (int) $params['id'], (int) $params['user_id'] );

            if ( is_wp_error( $result ) ) {
                return $result;
            } else {
                return new WP_REST_Response( $result );
            }
        } else {
            return new WP_Error( 'add_shared', "Missing a valid contact or user id", [ 'status' => 400 ] );
        }
    }

    /**
     * @param WP_REST_Request $request
     *
     * @return array|WP_Query
     */
    public function get_contacts_compact( WP_REST_Request $request ) {
        $params = $request->get_params();
        $search = "";
        if ( isset( $params['s'] ) ) {
            $search = $params['s'];
        }
        $contacts = DT_Posts::get_viewable_compact( 'contacts', $search );

        return $contacts;
    }


    public function get_multi_select_options( WP_REST_Request $request ){
        $params = $request->get_params();
        $search = $params["s"] ?? "";
        if ( isset( $params['field'] ) ){
            return Disciple_Tools_Contacts::get_multi_select_options( "contacts", $params["field"], $search );
        } else {
            return new WP_Error( 'get_multi_select_options', "Missing field for request", [ 'status' => 400 ] );
        }
    }

    public function get_contact_counts( WP_REST_Request $request ){
        $params = $request->get_params();
        $tab = $params["tab"] ?? null;
        $show_closed = isset( $params["closed"] ) && $params["closed"] == "true";
        return Disciple_Tools_Contacts::get_count_of_contacts( $tab, $show_closed );
    }

    public function list_sources() {
        return Disciple_Tools_Contacts::list_sources();
    }

    public function get_duplicates_on_contact( WP_REST_Request $request ){
        $params = $request->get_params();
        $contact_id = $params["id"] ?? null;
        if ( $contact_id ){
            return Disciple_Tools_Contacts::get_duplicates_on_contact( $contact_id );
        } else {
            return new WP_Error( 'get_duplicates_on_contact', "Missing field for request", [ 'status' => 400 ] );
        }
    }

    public function contact_transfer( WP_REST_Request $request ){

        if ( ! ( current_user_can( 'view_any_contacts' ) || current_user_can( 'manage_dt' ) ) ) {
            return new WP_Error( __METHOD__, 'Insufficient permissions' );
        }

        $params = $request->get_params();
        if ( ! isset( $params['contact_id'] ) || ! isset( $params['site_post_id'] ) ){
            return new WP_Error( __METHOD__, "Missing required parameters.", [ 'status' => 400 ] );
        }

        return Disciple_Tools_Contacts_Transfer::contact_transfer( $params['contact_id'], $params['site_post_id'] );

    }

    public function public_contact_transfer( WP_REST_Request $request ){

        $params = $this->process_token( $request );
        if ( is_wp_error( $params ) ) {
            return [
                'status' => 'FAIL',
                'error' => __( 'Transfer token error.', 'disciple_tools' ),
            ];
        }

        if ( ! current_user_can( 'create_contacts' ) ) {
            return [
                'status' => 'FAIL',
                'error' => __( 'Permission error.', 'disciple_tools' ),
            ];
        }

        if ( isset( $params['contact_data'] ) ) {
            $result = Disciple_Tools_Contacts_Transfer::receive_transferred_contact( $params );
            if ( is_wp_error( $result ) ) {
                return [
                    'status' => 'FAIL',
                    'error' => $result->get_error_message(),
                ];
            } else {
                return [
                    'status' => 'OK',
                    'error' => $result['errors'],
                ];
            }
        } else {
            return [
                'status' => 'FAIL',
                'error' => 'Missing required parameter'
            ];
        }
    }

    /**
     * Public key processing utility. Use this at the beginning of public endpoints
     *
     * @param WP_REST_Request $request
     *
     * @return array|WP_Error
     */
    public function process_token( WP_REST_Request $request ) {

        $params = $request->get_params();

        // required token parameter challenge
        if ( ! isset( $params['transfer_token'] ) ) {
            return new WP_Error( __METHOD__, 'Missing parameters.' );
        }

        $valid_token = Site_Link_System::verify_transfer_token( $params['transfer_token'] );

        // required valid token challenge
        if ( ! $valid_token ) {
            dt_write_log( $valid_token );
            return new WP_Error( __METHOD__, 'Invalid transfer token' );
        }

        return $params;
    }

    public function get_settings(){
        return Disciple_Tools_Contacts::get_settings();
    }

    public function get_following( WP_REST_Request $request ) {
        $params = $request->get_params();
        if ( isset( $params['id'] ) ) {
            return DT_Posts::get_users_following_post( "contacts", $params['id'] );
        } else {
            return new WP_Error( __FUNCTION__, "Missing a valid group id", [ 'status' => 400 ] );
        }
    }
}
