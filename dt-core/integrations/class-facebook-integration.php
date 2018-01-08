<?php
if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

/**
 * Class Disciple_Tools_Facebook_Integration
 */
class Disciple_Tools_Facebook_Integration
{
    /**
     * Disciple_Tools_Admin The single instance of Disciple_Tools_Admin.
     *
     * @var    object
     * @access private
     * @since  0.1.0
     */
    private static $_instance = null;

    /**
     * Main Disciple_Tools_Facebook_Integration Instance
     * Ensures only one instance of Disciple_Tools_Facebook_Integration is loaded or can be loaded.
     *
     * @since  0.1.0
     * @static
     * @return Disciple_Tools_Facebook_Integration instance
     */
    public static function instance()
    {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }

        return self::$_instance;
    } // End instance()

    private $version = 1.0;
    private $context = "dt-facebook";
    private $namespace;

    /**
     * Constructor function.
     *
     * @access public
     * @since  0.1.0
     */
    public function __construct()
    {
        $this->namespace = $this->context . "/v" . intval( $this->version );
        add_action( 'rest_api_init', [ $this, 'add_api_routes' ] );
        add_action( 'dt_contact_meta_boxes_setup', [ $this, 'add_contact_meta_box' ] );
//        add_action( 'admin_notices', [ $this, 'dt_admin_notice' ] );
        add_action( 'wp_ajax_dt-facebook-notice-dismiss', [ $this, 'dismiss_error' ] );
    } // End __construct()

    /**
     * Setup the api routs for the plugin
     *
     * @since  0.1.0
     */
    public function add_api_routes()
    {

        register_rest_route(
            $this->namespace, 'webhook', [
                'methods'  => 'POST',
                'callback' => [ $this, 'update_from_facebook' ],
            ]
        );
        register_rest_route(
            $this->namespace, 'webhook', [
                'methods'  => 'GET',
                'callback' => [ $this, 'verify_facebook_webhooks' ],
            ]
        );
        register_rest_route(
            $this->namespace, "auth", [
                'methods'  => "GET",
                'callback' => [ $this, 'authenticate_app' ],
            ]
        );
        register_rest_route(
            $this->namespace, "add-app", [
                'methods'  => "POST",
                'callback' => [ $this, 'add_app' ],
            ]
        );
        register_rest_route(
            $this->namespace, 'rebuild', [
                "methods"  => "GET",
                'callback' => [ $this, 'rebuild_all_data' ],
            ]
        );
    }

    /**
     * Admin notice
     */
    function dt_admin_notice()
    {
        $error = get_option( 'dt_facebook_error', "" );
        if ( $error ) { ?>
            <div class="notice notice-error dt-facebook-notice is-dismissible">
                <p><?php echo esc_html( $error ); ?></p>
            </div>
        <?php }
    }

    function dismiss_error()
    {
        update_option( 'dt_facebook_error', "" );
    }


    /**
     * Get reports for Facebook pages with stats enabled
     * for the past 10 years (if available)
     */
    public function rebuild_all_data()
    {
        $this->immediate_response();
        $facebook_pages = get_option( "dt_facebook_pages", [] );
        foreach ( $facebook_pages as $page_id => $facebook_page ) {
            if ( isset( $facebook_page->rebuild ) && $facebook_page->rebuild == true ) {
                $long_time_ago = date( 'Y-m-d', strtotime( '-10 years' ) );
                $reports = Disciple_Tools_Reports_Integrations::facebook_prepared_data( $long_time_ago, $facebook_page );
                foreach ( $reports as $report ) {
                    dt_report_insert( $report );
                }
            }
        }
    }

    /**
     * Render the Facebook Settings Page
     */
    public function facebook_settings_page()
    {

        ?>
        <h1>Facebook Integration Settings</h1>
        <h3>Hook up Disciple tools to a Facebook app in order to get contacts or useful stats from your Facebook
            pages. </h3>
        <div class="wrap">
            <div id="poststuff">
                <div id="post-body" class="metabox-holder columns-2">
                    <div id="post-body-content">

                        <form action="<?php echo esc_url( $this->get_rest_url() ); ?>/add-app" method="post">
                            <input type="hidden" name="_wpnonce" id="_wpnonce"
                                   value="' . wp_create_nonce( 'wp_rest' ) . '"/>
                            <p>For this integration to work, go to your <a href="https://developers.facebook.com/apps">Facebook
                                    app's settings page</a>.
                                Under <strong>Add Platform</strong>, choose the website option, put:
                                <strong><?php echo esc_url( get_site_url() ); ?></strong> as the site URL and click save
                                changes.<br>
                                From your Facebook App's settings page get the App ID and the App Secret and paste them
                                bellow and click the "Save App Settings" button.<br>
                                If you have any Facebook pages, they should appear in the Facebook Pages Table
                                bellow.<br>
                                You will need to re-authenticate (by clicking the "Save App Settings" button bellow) if:<br>
                                &nbsp;&nbsp; •You change your Facebook account password<br>
                                &nbsp;&nbsp; •You delete or de­authorize your Facebook App
                            </p>
                            <p></p>
                            <table class="widefat striped">

                                <thead>
                                <th>Facebook App Settings</th>
                                <th></th>
                                </thead>

                                <tbody>

                                <tr>
                                    <td>Facebook App Id</td>
                                    <td>
                                        <input type="text" class="regular-text" name="app_id"
                                               value="<?php echo esc_attr( get_option( "disciple_tools_facebook_app_id", "" ) ); ?>"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Facebook App Secret</td>
                                    <td>
                                        <input type="text" class="regular-text" name="app_secret"
                                               value="<?php echo esc_attr( get_option( "disciple_tools_facebook_app_secret" ) ); ?>"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Access Token</td>
                                    <td>
                                        <?php echo( get_option( "disciple_tools_facebook_access_token" ) ? "Access token is saved" : "No Access Token" ) ?>
                                    </td>
                                </tr>

                                <tr>
                                    <td>Save app</td>
                                    <td><input type="submit" class="button" name="save_app" value="Save app Settings"/>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </form>

                        <br>
                        <form action="" method="post">
                            <input type="hidden" name="_wpnonce" id="_wpnonce"
                                   value="<?php echo esc_attr( wp_create_nonce( 'wp_rest' ) ); ?>"/>
                            <?php $this->facebook_settings_functions(); ?>
                            <table id="facebook_pages" class="widefat striped">
                                <thead>
                                <th>Facebook Pages</th>
                                </thead>
                                <tbody>
                                <?php
                                $facebook_pages = get_option( "dt_facebook_pages", [] );

                                foreach ( $facebook_pages as $id => $facebook_page ){
                                ?>
                                <tr>
                                    <td><?php echo esc_html( $facebook_page->name( $id ) ); ?></td>
                                    <td>
                                        <label for="<?php echo esc_attr( $facebook_page->name -integrate ); ?>">Sync
                                            Contacts </label>
                                        <input name="<?php echo esc_attr( $facebook_page->name - integrate ); ?>"
                                               type="checkbox"
                                               value="<?php echo esc_attr( $facebook_page->name ); ?>" <?php echo checked( 1, isset( $facebook_page->integrate ) ? $facebook_page->integrate : false, false ); ?> />
                                    </td>
                                    <td>
                                        <label for="<?php echo esc_attr( $facebook_page->name - report ); ?>">Include in
                                            Stats </label>
                                        <input name="<?php echo esc_attr( $facebook_page->name - report ); ?>"
                                               type="checkbox"
                                               value="<?php echo esc_attr( $facebook_page->name ); ?>" <?php echo checked( 1, isset( $facebook_page->report ) ? $facebook_page->report : false, false ); ?> />
                                    </td>
                                    <?php
                                }
                                    ?>
                                </tbody>
                            </table>
                            <input type="submit" class="button" name="get_pages" value="Refresh Page List"/>
                            <input type="submit" class="button" name="save_pages" value="Save Pages Settings"/>


                        </form>
                    </div><!-- end post-body-content -->

                </div><!-- post-body meta box container -->
            </div><!--poststuff end -->
        </div><!-- wrap end -->

        <?php
    }

    /**
     * Display an error message
     *
     * @param $err
     */
    private function display_error( $err )
    {
        $err = date( "Y-m-d h:i:sa" ) . ' ' . $err; ?>
        <div class="notice notice-error is-dismissible">
            <p><?php echo esc_html( $err ); ?></p>
        </div>
        <?php
        update_option( 'dt_facebook_error', $err );
    }

    /**
     * Functions for the pages section of the Facebook settings
     */
    public function facebook_settings_functions()
    {
        // Check noonce
        if ( isset( $_POST['dt_app_form_noonce'] ) && !wp_verify_nonce( sanitize_key( $_POST['dt_app_form_noonce'] ), 'dt_app_form' ) ) {
            echo 'Are you cheating? Where did this form come from?';

            return;
        }

        // get the pages the user has access to.
        if ( isset( $_POST["get_pages"] ) ) {
            $url = "https://graph.facebook.com/v2.8/me/accounts?access_token=" . get_option( 'disciple_tools_facebook_access_token' );
            $request = wp_remote_get( $url );

            if ( is_wp_error( $request ) ) {
                $this->display_error( $request );
            } else {
                $body = wp_remote_retrieve_body( $request );
                $data = json_decode( $body );
                if ( !empty( $data ) && isset( $data->data ) ) {
                    $pages = get_option( "dt_facebook_pages", [] );
                    foreach ( $data->data as $page ) {
                        $pages[ $page->id ] = $page;
                    }
                    update_option( "dt_facebook_pages", $pages );
                }
                if ( !empty( $data ) && isset( $data->error ) ) {
                    $this->display_error( $data->error->message );
                }
            }
        }

        //save changes made to the pages in the page list
        if ( isset( $_POST["save_pages"] ) ) {
            $get_historical_data = false;
            $facebook_pages = get_option( "dt_facebook_pages", [] );
            foreach ( $facebook_pages as $id => $facebook_page ) {
                //if sync contact checkbox is selected
                $integrate = str_replace( ' ', '_', $facebook_page->name . "-integrate" );
                if ( isset( $_POST[ $integrate ] ) ) {
                    $facebook_page->integrate = 1;
                } else {
                    $facebook_page->integrate = 0;
                }
                //if the include in stats checkbox is selected
                $report = str_replace( ' ', '_', $facebook_page->name . "-report" );
                if ( isset( $_POST[ $report ] ) ) {
                    $facebook_page->report = 1;
                    $facebook_page->rebuild = true;
                    $get_historical_data = true;
                } else {
                    $facebook_page->report = 0;
                }
                //Add the page to the apps subscriptions (to allow webhooks)
                if ( $facebook_page->integrate == 1 && ( !isset( $facebook_page->subscribed ) || ( isset( $facebook_page->subscribed ) && $facebook_page->subscribed != 1 ) ) ) {
                    $url = "https://graph.facebook.com/v2.8/" . $id . "/subscribed_apps?access_token=" . $facebook_page->access_token;
                    $request = wp_remote_post( $url );
                    if ( is_wp_error( $request ) ) {
                        $this->display_error( $request );
                    } else {
                        $body = wp_remote_retrieve_body( $request );
                        $data = json_decode( $body );
                        if ( !empty( $data ) && isset( $data->error ) ) {
                            $this->display_error( $data->error->message );
                        }
                        $facebook_page->subscribed = 1;
                    }
                }
                //enable and set up webhooks for getting page feed and conversations
                if ( isset( $facebook_page->subscribed ) && $facebook_page->subscribed == 1 && !isset( $facebook_page->webhooks ) ) {
                    $url = "https://graph.facebook.com/v2.8/" . $id . "/subscriptions?access_token=" . get_option( "disciple_tools_facebook_app_id", "" ) . "|" . get_option( "disciple_tools_facebook_app_secret", "" );
                    $request = wp_remote_post(
                        $url, [
                            'body' => [
                                'object'       => 'page',
                                'callback_url' => $this->get_rest_url() . "/webhook",
                                'verify_token' => $this->authorize_secret(),
                                'fields'       => [ 'conversations', 'feed' ],
                            ],
                        ]
                    );
                    if ( is_wp_error( $request ) ) {
                        $this->display_error( $request );
                    } else {

                        $body = wp_remote_retrieve_body( $request );
                        $data = json_decode( $body );
                        if ( !empty( $data ) && isset( $data->error ) ) {
                            $this->display_error( $data->error->message );
                        }
                        if ( !empty( $data ) && isset( $data->success ) ) {
                            $facebook_page->webhooks_set = 1;
                        }
                    }
                }
            }
            update_option( "dt_facebook_pages", $facebook_pages );
            //if a new page is added, get the reports for that page.
            if ( $get_historical_data === true ) {
                wp_remote_get( $this->get_rest_url() . "/rebuild" );
            }
        }
    }

    public function get_rest_url()
    {
        return get_site_url() . "/wp-json/" . $this->namespace;
    }


    /**
     * Facebook Authentication and webhooks
     */

    // Generate authorization secret
    static function authorize_secret()
    {
        return 'dt_auth_' . substr( md5( AUTH_KEY ? AUTH_KEY : get_bloginfo( 'url' ) ), 0, 10 );
    }

    /**
     * called by facebook when initialising the webook
     *
     * @return mixed
     */
    public function verify_facebook_webhooks()
    {
        if ( isset( $_GET["hub_verify_token"] ) && $_GET["hub_verify_token"] === $this->authorize_secret() ) {
            if ( isset( $_GET['hub_challenge'] ) ) {
                return sanitize_text_field( wp_unslash( $_GET['hub_challenge'] ) );
            }
        }
    }

    /**
     * Facebook waits for a response from our server to see if we received the webhook update
     * If our server does not respond, Facebook will try the webhook again
     * Because we go on to do more ajax and database calls which takes several seconds
     * we need to respond to the return right away.
     */
    private function immediate_response()
    {
        // Buffer all upcoming output...
        ob_start();
        // Send your response.
        new WP_REST_Response( "ok", 200 );
        // Get the size of the output.
        $size = ob_get_length();
        // Disable compression (in case content length is compressed).
        header( "Content-Encoding: none" );
        // Set the content length of the response.
        header( "Content-Length: {$size}" );
        // Close the connection.
        header( "Connection: close" );
        // Flush all output.
        ob_end_flush();
        ob_flush();
        flush();
        // Close current session (if it exists).
        // TODO: look into whether session_ functions should really be used
        // here, as PHPCS does not like it
        // @codingStandardsIgnoreStart
        if( session_id() ) {
            session_write_close();
        }
        //for nginx systems
        session_write_close(); //close the session
        // @codingStandardsIgnoreEnd
        fastcgi_finish_request(); //this returns 200 to the user, and processing continues
    }

    /**
     * authenticate the facebook app to get the user access token and facebook pages
     *
     * @param  $get
     *
     * @return bool
     */
    public function authenticate_app( $get )
    {

        //get the access token

        if ( isset( $get["state"] ) && strpos( $get['state'], $this->authorize_secret() ) !== false && isset( $get["code"] ) ) {
            $url = "https://graph.facebook.com/v2.8/oauth/access_token";
            $url .= "?client_id=" . get_option( "disciple_tools_facebook_app_id" );
            $url .= "&redirect_uri=" . $this->get_rest_url() . "/auth";
            $url .= "&client_secret=" . get_option( "disciple_tools_facebook_app_secret" );
            $url .= "&code=" . $get["code"];

            $request = wp_remote_get( $url );
            if ( is_wp_error( $request ) ) {
                $this->display_error( $request->get_error_message() );

                return $request->errors;
            } else {
                $body = wp_remote_retrieve_body( $request );
                $data = json_decode( $body );
                if ( !empty( $data ) ) {
                    if ( isset( $data->access_token ) ) {
                        update_option( 'disciple_tools_facebook_access_token', $data->access_token );

                        $facebook_pages_url = "https://graph.facebook.com/v2.8/me/accounts?access_token=" . $data->access_token;
                        $pages_request = wp_remote_get( $facebook_pages_url );

                        if ( is_wp_error( $pages_request ) ) {
                            $this->display_error( $pages_request );
                            echo "There was an error";
                        } else {
                            $pages_body = wp_remote_retrieve_body( $pages_request );
                            $pages_data = json_decode( $pages_body );
                            if ( !empty( $pages_data ) ) {
                                if ( isset( $pages_data->data ) ) {
                                    $pages = get_option( "dt_facebook_pages", [] );
                                    foreach ( $pages_data->data as $page ) {
                                        $pages[ $page->id ] = $page;
                                    }
                                    update_option( "dt_facebook_pages", $pages );
                                } elseif ( isset( $pages_data->error ) && isset( $pages_data->error->messages ) ) {
                                    $this->display_error( $data->error->message );
                                }
                            }
                        }
                    }
                    if ( isset( $data->error ) ) {
                        $this->display_error( $data->error->message );
                    }
                }
            }
        }
        wp_redirect( admin_url( "options-general.php?page=" . $this->context ) );
        exit;
    }

    /**
     * redirect workfloww for authorizing the facebook app
     */
    public function add_app()
    {
        // Check noonce
        if ( isset( $_POST['_wpnonce'] ) && !wp_verify_nonce( sanitize_key( $_POST['_wpnonce'] ), 'wp_rest' ) ) {
            return 'Are you cheating? Where did this form come from?';
        }
        if ( current_user_can( "manage_options" ) && check_admin_referer( 'wp_rest' ) && isset( $_POST["save_app"] ) && isset( $_POST["app_secret"] ) && isset( $_POST["app_id"] ) ) {
            update_option( 'disciple_tools_facebook_app_id', sanitize_key( $_POST["app_id"] ) );
            update_option( 'disciple_tools_facebook_app_secret', sanitize_key( $_POST["app_secret"] ) );
            delete_option( 'disciple_tools_facebook_access_token' );

            $url = "https://facebook.com/v2.8/dialog/oauth";
            $url .= "?client_id=" . sanitize_key( $_POST["app_id"] );
            $url .= "&redirect_uri=" . $this->get_rest_url() . "/auth";
            $url .= "&scope=public_profile,read_insights,manage_pages,read_page_mailboxes";
            $url .= "&state=" . $this->authorize_secret();

            wp_redirect( $url );
            exit;
        }

        return "ok";
    }





    /**
     * Handle updates from facebook via webhooks
     * - conversations
     */

    /**
     * This is the route called by the Facebook webhook.
     */
    public function update_from_facebook()
    {
        //respond to facebook immediately
        $this->immediate_response();

        //decode the facebook post request from json
        $input = json_decode( file_get_contents( 'php://input' ), true );

        foreach ( $input['entry'] as $entry ) {
            $facebook_page_id = $entry['id'];
            if ( $entry['changes'] ) {
                foreach ( $entry['changes'] as $change ) {
                    if ( $change['field'] == "conversations" ) {
                        //there is a new update in a conversation
                        $thread_id = $change['value']['thread_id'];
                        $this->get_conversation_update( $facebook_page_id, $thread_id );
                    } elseif ( $change['field'] == "feed" ) {
                        //the facebook page feed has an update
                    }
                }
            }
        }
    }

    /**
     * get the conversation details from facebook
     *
     * @param $page_id
     * @param $thread_id , the id for the conversation containing the messages
     */
    private function get_conversation_update( $page_id, $thread_id )
    {
        //check the settings array to see if we have settings saved for the page
        //get the access token and custom page name by looking for the page Id
        $facebook_pages = get_option( "dt_facebook_pages", [] );
        //if we have the access token, get and save the conversation
        //make sure the "sync contacts" setting is set.
        if ( isset( $facebook_pages[ $page_id ] ) && isset( $facebook_pages[ $page_id ]->integrate ) && $facebook_pages[ $page_id ]->integrate == 1 ) {

            $access_token = $facebook_pages[ $page_id ]->access_token;
            $uri_for_conversations = "https://graph.facebook.com/v2.7/" . $thread_id . "?fields=message_count,messages{from,created_time,message},updated_time,participants&access_token=" . $access_token;
            $response = wp_remote_get( $uri_for_conversations );
            $page_name = $facebook_pages[ $page_id ]->name;

            $body = json_decode( $response["body"], true );
            if ( $body ) {
                $participants = $body["participants"]["data"];
                //go through each participant to save their conversations on their contact record
                foreach ( $participants as $participant ) {
                    if ( (string) $participant["id"] != $page_id ) {
                        $this->update_or_create_contact( $participant, $body["messages"], $body["updated_time"], $page_id, $page_name, $body["message_count"] );
                    }
                }
            }
        }
    }

    /**
     * Get all the records if we don't already have them.
     *
     * @param  $current_records , the records (messages) gotten with the initial api call
     * @param  $paging          , the object containing the paging urls
     * @param  $count           , the number of records facebook has
     *
     * @return array, all the records
     */
    private function get_facebook_object_with_paging( $current_records, $paging, $count )
    {
        if ( count( $current_records ) >= $count ) {
            return $current_records;
        } else {
            $response = wp_remote_get( $paging["next"] );
            $more_records = json_decode( $response["body"], true );
            $current_records = array_map( "unserialize", array_unique( array_map( "serialize", array_merge( $current_records, $more_records["data"] ) ) ) );

            if ( !isset( $more_records["paging"] ) ) {
                return $current_records;
            } else {
                return $this->get_facebook_object_with_paging( $current_records, $more_records["paging"], $count );
            }
        }
    }

    /**
     * Find the facebook id in contacts and update or create the record. Then retrieve any missing messages
     * from the conversation.
     *
     * @param $participant
     * @param $messages      , the messaging object from facebook
     * @param $updated_time  , the time of the last message
     * @param $page_id       , the id of the facebook page where the conversation is happening
     * @param $page_name     , the name given to the facebook page in settings
     * @param $message_count , the number of messages in the conversation
     */
    private function update_or_create_contact( $participant, $messages, $updated_time, $page_id, $page_name, $message_count )
    {
        $facebook_url = "https://www.facebook.com/" . $participant["id"];
        $query = new WP_Query(
            [
                'post_type'  => 'contacts',
                'meta_key'   => 'facebook',
                'meta_value' => $facebook_url,
            ]
        );

        $post_id = null;
        $existing_messages = [];
        //update contact
        if ( $query->have_posts() && $query->found_posts == 1 ) {
            $post = $query->post;
            $post_id = $post->ID;
            $fields = get_post_custom( $post_id );
            $existing_messages = isset( $fields["facebook_messages"][0] ) ? unserialize( $fields["facebook_messages"][0] ) : [];
            update_post_meta( $post_id, "last_actual_contact", $updated_time );
        } elseif ( !$query->have_posts() ) {
            //create contact
            $post_title = $participant["name"];
            $post_type = 'contacts';
            $post_content = ' ';
            $post_status = "publish";
            $source = "Facebook Page: " . $page_name;

            $post = [
                "post_title"   => $post_title,
                'post_type'    => $post_type,
                "post_content" => $post_content,
                "post_status"  => $post_status,
                "meta_input"   => [
                    "facebook"                 => $facebook_url,
                    "preferred_contact_method" => "Facebook",
                    "sources"                  => $source,
                ],
            ];
            $post_id = wp_insert_post( $post );
        }

        if ( $post_id ) {
            $new_messages = $messages["data"];
            //merge the old and new messages and make sure they are unique (deduplicate)
            $current_messages = array_map( "unserialize", array_unique( array_map( "serialize", array_merge( $new_messages, $existing_messages ) ) ) );
            $all_messages = $this->get_facebook_object_with_paging( $current_messages, $messages["paging"], $message_count );
            update_post_meta( $post_id, "facebook_messages", $all_messages );
        }
    }

    /**
     * Hook for setting up a metabox on the contact post_type
     *
     * @param $contact_post_type
     */
    public function add_contact_meta_box( $contact_post_type )
    {
        add_meta_box( $contact_post_type . '_facebook', __( 'Facebook', 'disciple_tools' ), [ $this, 'load_facebook_meta_box' ], $contact_post_type, 'side', 'low', [ $contact_post_type ] );
    }

    /**
     * Sort messages in a conversation by date
     *
     * @param  $a , date of the first message
     * @param  $b , date of the second message
     *
     * @return int
     */
    private function sort_function( $a, $b )
    {
        return strtotime( $a["created_time"] ) - strtotime( $b["created_time"] );
    }

    /**
     * Load the messages in the facebook meta_box
     *
     * @param $contact_post_type
     */
    public function load_facebook_meta_box( $contact_post_type )
    {
        global $post_id;
        $fields = get_post_custom( $post_id );
        $field_data = [];
        $field_data['facebook_messages'] = [
            'name'        => __( 'Facebook Messages', 'disciple_tools' ),
            'description' => '',
            'type'        => 'serialized',
            'default'     => serialize( [] ),
            'section'     => 'facebook',
        ];

        if ( !is_string( $contact_post_type ) ) {
            $contact_post_type = $contact_post_type->post_type;
        }

        ?>
        <input type="hidden" name="<?php echo esc_attr( "dt_{$contact_post_type}_noonce" ); ?>"
               id="<?php echo esc_attr( "dt_{$contact_post_type}_noonce" ); ?>"
               value="<?php echo esc_attr( wp_create_nonce( plugin_basename( dirname( disciple_tools()->plugin_path ) ) ) ); ?>"/>
        <?php

        if ( 0 < count( $field_data ) ) {
            ?>
            <table class="form-table">
                <tbody>

                <?php
                foreach ( $field_data as $k => $v ) {
                    $data = $v['default'];
                    if ( isset( $fields[ $k ] ) && isset( $fields[ $k ][0] ) ) {
                        $data = $fields[ $k ][0];
                    }
                    $type = $v['type'];

                    switch ( $type ) {
                        case 'serialized':
                            if ( gettype( $data ) == "string" ) {
                                $data = unserialize( $data );
                                ?>
                                <strong>Messages</strong>
                                <ul>
                                    <?php
                                    usort( $data, [ $this, "sort_function" ] );
                                    foreach ( $data as $o ) {
                                        ?>
                                        <li><?php echo esc_html( $o["from"]["name"] . ': ' . $o["message"] ); ?></li>
                                        <?php
                                    }
                                    ?>
                                </ul>
                                <?php
                            }
                            break;

                        default:
                            break;
                    }
                }

                ?>
                </tbody>
            </table>
            <?php
        }
    }

}
