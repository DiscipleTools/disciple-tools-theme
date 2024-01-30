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
            'post_type' => [
                'description' => 'The post type',
                'type' => 'string',
                'required' => true,
                'validate_callback' => [ 'Disciple_Tools_Posts_Endpoints', 'prefix_validate_args_static' ]
            ],
        ];

        register_rest_route(
            $namespace, '/(?P<post_type>\w+)/email_magic', [
                [
                    'methods'  => 'POST',
                    'callback' => [ $this, 'email_magic' ],
                    'args' => [
                        'post_type' => $arg_schemas['post_type'],
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
            return new WP_Error( __METHOD__, 'Missing essential params', [ 'status' => 400 ] );
        }

        if ( ! isset( $params['post_ids'] ) || empty( $params['post_ids'] ) ) {
            return new WP_Error( __METHOD__, 'Missing list of post ids', [ 'status' => 400 ] );
        }

        $magic = new DT_Magic_URL( $params['root'] );
        $type = $magic->list_types();
        if ( ! isset( $type[$params['type']] ) ) {
            return new WP_Error( __METHOD__, 'Magic link type not found', [ 'status' => 400 ] );
        } else {
            $name = $type[$params['type']]['name'] ?? '';
            $meta_key = $type[$params['type']]['meta_key'];
        }

        $errors = [];
        $success = [];

        /**
         * Bulk Field Filter
         *
         * This filter allows for targeting a specific contact connection field to loop through and send emails.
         * The default field name is 'reporter', but this can be overwritten and pointed towards assigned_to, subassigned_to,
         * coaching, or another custom contact connection field.
         *
         * @param (string)
         */
        $target_field = apply_filters( 'dt_bulk_email_connection_field', null, $params['post_type'], $params );

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
            else if ( $target_field && isset( $post_record[$target_field] ) && ! empty( $post_record[$target_field] ) ) {
                foreach ( $post_record[$target_field] as $reporter ) {
                    $contact_post = DT_Posts::get_post( 'contacts', $reporter['ID'], true, false );
                    if ( isset( $contact_post['contact_email'] ) && ! empty( $contact_post['contact_email'] ) ) {
                        foreach ( $contact_post['contact_email'] as $contact_email ) {
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

            // set subject, avoiding DT prefix duplicates.
            $subject = $params['subject'] ?? $name;
            $subject = str_replace( '{{app}}', $name, $subject );

            // set final message shape.
            $message = str_replace(
                [
                    '{{name}}',
                    '{{app}}',
                    '{{link}}'
                ],
                [
                    $post_record['name'] ?? '',
                    $name,
                    $link
                ],
                $note
            );

            // convert to html, containing a button version of url magic link.
            $message = $this->convert_to_html( $subject, $name, $message, $link );

            // send email
            foreach ( $emails as $email ) {
                $headers = [
                    'Content-Type: text/html; charset=UTF-8',
                ];
                $is_sent = dt_schedule_mail( $email, $subject, $message, $headers );

                $success[$post_id] = $is_sent;
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

        return [
            'total_unsent' => ( ! empty( $success ) ) ? count( $errors ) : 0,
            'total_sent' => ( ! empty( $success ) ) ? count( $success ) : 0,
            'errors' => $errors,
            'success' => $success
        ];
    }

    private function convert_to_html( $subject, $app, $message, $link ) {

        // Load email template and replace content placeholder.
        $email_template = file_get_contents( trailingslashit( get_template_directory() ) . 'dt-notifications/email-template.html' );
        if ( $email_template ) {

            // Swap out link with a temporary placeholder.
            $message = str_replace( $link, '{{MAGIC_LINK}}', $message );

            // Clean < and > around text links in WP 3.1.
            $message = preg_replace( '#<(https?://[^*]+)>#', '$1', $message );

            // Convert line breaks.
            if ( apply_filters( 'dt_email_template_convert_line_breaks', true ) ) {
                $message = nl2br( $message );
            }

            // Convert URLs to links.
            if ( apply_filters( 'dt_email_template_convert_urls', true ) ) {
                $message = make_clickable( $message );
            }

            // Update title & footer placeholders.
            $email_template = str_replace( '{{EMAIL_TEMPLATE_TITLE}}', $subject, $email_template );
            $email_template = str_replace( '{{EMAIL_TEMPLATE_FOOTER}}', get_bloginfo( 'name' ), $email_template );

            // Convert link into a workable ui button widget.
            $link_button_html = '
            <table role="presentation" border="0" cellpadding="0" cellspacing="0" class="btn btn-primary" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; box-sizing: border-box; width: 100%; min-width: 100%;" width="100%">
                <tbody>
                  <tr>
                    <td align="left" style="font-family: Helvetica, sans-serif; font-size: 16px; vertical-align: top; padding-bottom: 16px;" valign="top">
                      <table role="presentation" border="0" cellpadding="0" cellspacing="0" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: auto;">
                        <tbody>
                          <tr>
                            <td style="font-family: Helvetica, sans-serif; font-size: 16px; vertical-align: top; border-radius: 4px; text-align: center; background-color: #0867ec;" valign="top" align="center" bgcolor="#0867ec">
                              <a href="'. esc_attr( $link ) .'" target="_blank" style="border: solid 2px #0867ec; border-radius: 4px; box-sizing: border-box; cursor: pointer; display: inline-block; font-size: 16px; font-weight: bold; margin: 0; padding: 12px 24px; text-decoration: none; text-transform: capitalize; background-color: #0867ec; border-color: #0867ec; color: #ffffff;">'. esc_html( $app ) .'</a>
                            </td>
                          </tr>
                        </tbody>
                      </table>
                    </td>
                  </tr>
                </tbody>
              </table>
            ';
            $message = str_replace( '{{MAGIC_LINK}}', $link_button_html, $message );

            return str_replace( '{{EMAIL_TEMPLATE_CONTENT}}', $message, $email_template );
        }

        return $message;
    }

}
Disciple_Tools_Magic_Endpoints::instance();
