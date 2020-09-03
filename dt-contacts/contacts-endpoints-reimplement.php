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


        register_rest_route(
            $this->public_namespace, '/contact/transfer', [
                "methods"  => "POST",
                "callback" => [ $this, 'public_contact_transfer' ],
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
            $namespace, '/contacts/(?P<id>\d+)/accept', [
                "methods"  => "POST",
                "callback" => [ $this, 'accept_contact' ],
            ]
        );
        //Merge Posts
        register_rest_route(
            $namespace, '/contacts/merge', [
                "methods"  => "POST",
                "callback" => [ $this, 'merge_posts' ],
            ]
        );
        //Dismiss Duplicates
        register_rest_route(
            $namespace, '/contacts/(?P<id>\d+)/dismiss-duplicates', [
                "methods"  => "GET",
                "callback" => [ $this, 'dismiss_post_duplicate' ]
            ]
        );
    }

    /**
     * @param WP_REST_Request $request
     *
     * @return array|WP_Error|WP_REST_Response
     */
    public function accept_contact( WP_REST_Request $request ) {
        $params = $request->get_params();
        $body = $request->get_json_params() ?? $request->get_body_params();
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


    public function get_duplicates_on_contact( WP_REST_Request $request ){
        $params = $request->get_params();
        $contact_id = $params["id"] ?? null;
        if ( $contact_id ){
            return Disciple_Tools_Contacts::get_duplicates_on_contact( $contact_id, $params["include_contacts"] ?? "" !== "false", $params["exact_match"] ?? "" === "true" );
        } else {
            return new WP_Error( 'get_duplicates_on_contact', "Missing field for request", [ 'status' => 400 ] );
        }
    }

    public function merge_posts( WP_REST_Request $request ){
        $body = $request->get_json_params() ?? $request->get_body_params();
        if ( isset( $body["contact1"], $body["contact2"] ) ) {
            return Disciple_Tools_Contacts::merge_posts( $body["contact1"], $body["contact2"], $body );
        }
        return false;
    }

    public function dismiss_post_duplicate( WP_REST_Request $request ){
        $url_params = $request->get_url_params();
        $get_params = $request->get_query_params();
        if ( isset( $get_params["id"] ) ) {
            if ( $get_params["id"] === "all" ){
                return Disciple_Tools_Contacts::dismiss_all( $url_params["id"] );
            } else {
                return Disciple_Tools_Contacts::dismiss_duplicate( $url_params["id"], $get_params["id"] );
            }
        }
        return false;
    }


    public function public_contact_transfer( WP_REST_Request $request ){

        $params = $this->process_token( $request );
        if ( is_wp_error( $params ) ) {
            return [
                'status' => 'FAIL',
                'error' => 'Transfer token error.'
            ];
        }

        if ( ! current_user_can( 'create_contacts' ) ) {
            return [
                'status' => 'FAIL',
                'error' => 'Permission error.'
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
}
