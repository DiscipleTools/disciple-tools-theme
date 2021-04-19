<?php
/**
 * Custom advanced search endpoints file
 */

/**
 * Class Disciple_Tools_Search_Endpoints
 */
class DT_Search_Endpoints {

    private $version = 1;
    private $context = "dt-search";
    private $namespace;

    /**
     * Disciple_Tools_Users_Endpoints constructor.
     */
    public function __construct() {
        $this->namespace = $this->context . "/v" . $this->version;
        add_action( 'rest_api_init', [ $this, 'add_api_routes' ] );
    }

    /**
     * Setup for API routes
     */
    public function add_api_routes() {
        register_rest_route(
            $this->namespace, '/search', [
                'methods'             => 'GET',
                'callback'            => [ $this, 'search' ],
                'permission_callback' => '__return_true',
            ]
        );
    }

    /**
     * @param WP_REST_Request $request
     *
     * @return array|WP_Error
     */
    public function search( WP_REST_Request $request ): array {
        return DT_Search_Posts::query( urldecode( $request->get_param( 'query' ) ) );
    }
}
