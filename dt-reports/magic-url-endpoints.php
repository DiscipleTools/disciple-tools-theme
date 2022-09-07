<?php

/**
 * Disciple_Tools_Magic_Endpoints
 *
 * @class      Disciple_Tools_Magic_Endpoints
 * @version    0.1.0
 * @since      0.1.0
 * @package    Disciple.Tools
 * @author     Disciple.Tools
 */

if ( !defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class Disciple_Tools_Magic_Endpoints
 */
class Disciple_Tools_Magic_Endpoints
{
    /**
     * Disciple_Tools_Magic_Endpoints The single instance of Disciple_Tools_Magic_Endpoints.
     *
     * @var     object
     * @access    private
     * @since     0.1.0
     */
    private static $_instance = null;

    /**
     * Main Disciple_Tools_Magic_Endpoints Instance
     * Ensures only one instance of Disciple_Tools_Magic_Endpoints is loaded or can be loaded.
     *
     * @since 0.1.0
     * @static
     * @return Disciple_Tools_Magic_Endpoints instance
     */
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    } // End instance()

    /**
     * Constructor function.
     *
     * @access  public
     * @since   0.1.0
     */
    public function __construct() {
        add_action( 'rest_api_init', [ $this, 'add_api_routes' ] );
    } // End __construct()

    public function add_api_routes() {
        $version = '1';
        $namespace = 'dt/v' . $version;

        $arg_schemas = [
            "post_type" => [
                "description" => "The post type",
                "type" => 'string',
                "required" => true,
                "validate_callback" => [ 'Disciple_Tools_Posts_Endpoints', "prefix_validate_args_static" ]
            ],
        ];

        register_rest_route(
            $namespace, '/(?P<post_type>\w+)/email_magic', [
                [
                    "methods"  => "POST",
                    "callback" => [ $this, 'email_magic' ],
                    "args" => [
                        "post_type" => $arg_schemas["post_type"],
                    ],
                    'permission_callback' => '__return_true',
                ]
            ]
        );

    }

    /**
     * Get tract from submitted address
     *
     * @param  WP_REST_Request $request
     *
     * @access public
     * @since  0.1.0
     * @return string|WP_Error|array The contact on success
     */
    public function email_magic( WP_REST_Request $request ) {
        $params = $request->get_params();

        if ( ! isset( $params['root'], $params['type'], $params['post_type'] ) ) {
            return new WP_Error( __METHOD__, "Missing essential params", [ 'status' => 400 ] );
        }

        if ( ! isset( $params['post_ids'] ) || empty( $params['post_ids'] ) ) {
            return new WP_Error( __METHOD__, "Missing list of post ids", [ 'status' => 400 ] );
        }

        $magic = new DT_Magic_URL( $params['root'] );
        $type = $magic->list_types();
        if ( ! isset( $type[$params['type']] ) ) {
            return new WP_Error( __METHOD__, "Magic link type not found", [ 'status' => 400 ] );
        } else {
            $name = $type[$params['type']]['name'] ?? '';
            $meta_key = $type[$params['type']]['meta_key'];
        }

        $errors = [];
        $success = [];

        foreach ( $params['post_ids'] as $post_id ) {
            $post_record = DT_Posts::get_post( $params['post_type'], $post_id, true, true );
            if ( is_wp_error( $post_record ) || empty( $post_record ) ){
                $errors[$post_id] = 'no permission';
                continue;
            }

            // check if email exists to send to
            $emails = [];
            if ( isset( $params['email'] ) && ! empty( $params['email'] ) ){
                $emails[]   = $params['email'];
            }
            else if ( isset( $post_record['contact_email'][0] ) ) {
                $emails[]  = $post_record['contact_email'][0]['value'];
            }
            else if ( isset( $post_record['reporter'] ) && ! empty( $post_record['reporter'] ) ) {
                dt_write_log('here');

                foreach( $post_record['reporter'] as $reporter ) {
                    $contact_post = DT_Posts::get_post( 'contacts', $reporter['ID'], true, false );
                    if ( isset( $contact_post['contact_email'] ) && ! empty( $contact_post['contact_email'] ) ) {
                        foreach( $contact_post['contact_email'] as $contact_email ) {
                            $emails[]  = $contact_email['value'];
                        }
                    }
                }
            }
            else {
                $errors[ $post_id ] = 'no email';
                continue;
            }


            // check if magic key exists, or needs created
            if ( ! isset( $post_record[$meta_key] ) ) {
                $key = dt_create_unique_key();
                update_post_meta( $post_id, $meta_key, $key );
                $link = DT_Magic_URL::get_link_url( $params['root'], $params['type'], $key );
            }
            else {
                $link = DT_Magic_URL::get_link_url( $params['root'], $params['type'], $post_record[$meta_key] );
            }

            $note = '';
            if ( isset( $params['note'] ) && ! empty( $params['note'] ) ) {
                $note = $params['note'];
            }

            $subject = $name;
            $message_plain_text = $note . '

'           . $name . ': ' . $link;

            // send email
            foreach( $emails as $email ) {
                $sent = dt_send_email( $email, $subject, $message_plain_text ); dt_write_log($sent);
                if ( is_wp_error( $sent ) || ! $sent ) {
                    $errors[$post_id] = $sent;
                }
                else {
                    $success[$post_id] = $sent;
                    dt_activity_insert( [
                        'action'            => 'sent_app_link',
                        'object_type'       => $params['post_type'],
                        'object_subtype'    => 'email',
                        'object_id'         => $post_id,
                        'object_name'       => $post_record['title'],
                        'object_note'       => $name . ' (app) sent to ' . $email,
                    ] );
                }
            }

        }

        return [
            'total_unsent' => ( ! empty( $success ) ) ? count( $errors ) : 0,
            'total_sent' => ( ! empty( $success ) ) ? count( $success ) : 0,
            'errors' => $errors,
            'success' => $success
        ];
    }

}
Disciple_Tools_Magic_Endpoints::instance();
