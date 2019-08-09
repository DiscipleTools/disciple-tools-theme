<?php
/**
 * General class for GDPR functions
 */

// required for table classes
if ( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/**
 * Scrub the user data from comment content
 *
 * @param      $post_id
 *
 * @return array|int
 */
function dt_get_comments_with_redacted_user_data( $post_id ) {
    $comments = get_comments( [ 'post_id' => $post_id ] );
    if ( empty( $comments ) ) {
        return $comments;
    }
    $email_note = __( 'redacted email' );
    $name_note = __( 'redacted name' );
    $redacted_note = __( 'redacted' );

    $users = get_users();

    foreach ( $comments as $index => $comment ) {
        $comment_content = $comment["comment_content"];

        // replace @mentions with user number
        preg_match_all( '/\@\[(.*?)\]\((.+?)\)/', $comment_content, $matches );
        foreach ( $matches[0] as $match_key => $match ){
            $comment_content = str_replace( $match, '@' . $redacted_note . '_' . $matches[2][$match_key], $comment_content );
        }

        // replace non-@mention references to login names, display names, or user emails
        foreach ( $users as $user ) {
            if ( ! empty( $user->data->user_login ) ) {
                $comment_content = str_replace( $user->data->user_login, '(' . $name_note . ')', $comment_content );
            }
            if ( ! empty( $user->data->display_name ) ) {
                $comment_content = str_replace( $user->data->display_name, '(' . $name_note . ')', $comment_content );
            }
            if ( ! empty( $user->data->user_nicename ) ) {
                $comment_content = str_replace( $user->data->user_nicename, '(' . $name_note . ')', $comment_content );
            }
            if ( ! empty( $user->data->user_email ) ) {
                $comment_content = str_replace( $user->data->user_email, '(' . $email_note . ')', $comment_content );
            }
        }

        // replace duplicate notes
        $comment_content = str_replace( site_url(), '#', $comment_content );

        $comments[$index]->comment_content = $comment_content;
    }

    return $comments;
}

class Disciple_Tools_GDPR
{
    public function __construct() {
    }

    public function contact_data_export_page() {
        if ( ! current_user_can( 'manage_dt' ) ) {
            wp_die( esc_html__( 'Sorry, you are not allowed to export personal data on this site.' ) );
        }

        $this->_personal_data_handle_actions();
        $this->_personal_data_cleanup_requests();

        // "Borrow" xfn.js for now so we don't have to create new files.
        wp_enqueue_script( 'xfn' );

        $requests_table = new DT_Privacy_Data_Export_Requests_Table( array(
            'plural'   => 'privacy_requests',
            'singular' => 'privacy_request',
        ) );
        $requests_table->process_bulk_action();
        $requests_table->prepare_items();
        ?>
        <div class="wrap nosubsub">
            <h1><?php esc_html_e( 'Export Personal Data', "disciple_tools" ); ?></h1>
            <hr class="wp-header-end" />

            <?php settings_errors(); ?>

            <form method="post" class="wp-privacy-request-form">
                <h2><?php esc_html_e( 'Add Data Export Request', "disciple_tools" ); ?></h2>
                <p><?php esc_html_e( 'An email will be sent to the user at this email address asking them to verify the request.', "disciple_tools" ); ?></p>

                <div class="wp-privacy-request-form-field">
                    <label for="username_or_email_for_privacy_request"><?php esc_html_e( 'Username or email address', "disciple_tools" ); ?></label>
                    <input type="text" required class="regular-text" id="username_or_email_for_privacy_request" name="username_or_email_for_privacy_request" />
                    <?php submit_button( __( 'Send Request' ), 'secondary', 'submit', false ); ?>
                </div>
                <?php wp_nonce_field( 'personal-data-request' ); ?>
                <input type="hidden" name="action" value="add_export_personal_data_request" />
                <input type="hidden" name="type_of_action" value="export_personal_data" />
            </form>
            <hr />

            <?php $requests_table->views(); ?>

            <form class="search-form wp-clearfix">
                <?php $requests_table->search_box( __( 'Search Requests' ), 'requests' ); ?>
                <input type="hidden" name="page" value="export_personal_data" />
                <input type="hidden" name="filter-status" value="<?php echo isset( $_REQUEST['filter-status'] ) ? esc_attr( sanitize_text_field( wp_unslash( $_REQUEST['filter-status'] ) ) ) : ''; ?>" />
                <input type="hidden" name="orderby" value="<?php echo isset( $_REQUEST['orderby'] ) ? esc_attr( sanitize_text_field( wp_unslash( $_REQUEST['orderby'] ) ) ) : ''; ?>" />
                <input type="hidden" name="order" value="<?php echo isset( $_REQUEST['order'] ) ? esc_attr( sanitize_text_field( wp_unslash( $_REQUEST['order'] ) ) ) : ''; ?>" />
            </form>

            <form method="post">
                <?php
                $requests_table->display();
                $requests_table->embed_scripts();
                ?>
            </form>
        </div>
        <?php
    }

    public function contact_data_removal_page() {
        /*
         * Require both caps in order to make it explicitly clear that delegating
         * erasure from network admins to single-site admins will give them the
         * ability to affect global users, rather than being limited to the site
         * that they administer.
         */
        if ( ! current_user_can( 'manage_dt' ) ) {
            wp_die( esc_html__( 'Sorry, you are not allowed to erase data on this site.' ) );
        }

        $this->_personal_data_handle_actions();
        $this->_personal_data_cleanup_requests();

        // "Borrow" xfn.js for now so we don't have to create new files.
        wp_enqueue_script( 'xfn' );

        $requests_table = new DT_Privacy_Data_Removal_Requests_Table( array(
            'plural'   => 'privacy_requests',
            'singular' => 'privacy_request',
        ) );

        $requests_table->process_bulk_action();
        $requests_table->prepare_items();

        ?>
        <div class="wrap nosubsub">
            <h1><?php esc_html_e( 'Erase Personal Data', "disciple_tools" ); ?></h1>
            <hr class="wp-header-end" />

            <?php settings_errors(); ?>

            <form method="post" class="wp-privacy-request-form">
                <h2><?php esc_html_e( 'Add Data Erasure Request', "disciple_tools" ); ?></h2>
                <p><?php esc_html_e( 'An email will be sent to the user at this email address asking them to verify the request.', "disciple_tools" ); ?></p>

                <div class="wp-privacy-request-form-field">
                    <label for="username_or_email_for_privacy_request"><?php esc_html_e( 'Username or email address', "disciple_tools" ); ?></label>
                    <input type="text" required class="regular-text" id="username_or_email_for_privacy_request" name="username_or_email_for_privacy_request" />
                    <?php submit_button( __( 'Send Request' ), 'secondary', 'submit', false ); ?>
                </div>
                <?php wp_nonce_field( 'personal-data-request' ); ?>
                <input type="hidden" name="action" value="add_remove_personal_data_request" />
                <input type="hidden" name="type_of_action" value="remove_personal_data" />
            </form>
            <hr />

            <?php $requests_table->views(); ?>

            <form class="search-form wp-clearfix">
                <?php $requests_table->search_box( __( 'Search Requests' ), 'requests' ); ?>
                <input type="hidden" name="page" value="remove_personal_data" />
                <input type="hidden" name="filter-status" value="<?php echo isset( $_REQUEST['filter-status'] ) ? esc_attr( sanitize_text_field( wp_unslash( $_REQUEST['filter-status'] ) ) ) : ''; ?>" />
                <input type="hidden" name="orderby" value="<?php echo isset( $_REQUEST['orderby'] ) ? esc_attr( sanitize_text_field( wp_unslash( $_REQUEST['orderby'] ) ) ) : ''; ?>" />
                <input type="hidden" name="order" value="<?php echo isset( $_REQUEST['order'] ) ? esc_attr( sanitize_text_field( wp_unslash( $_REQUEST['order'] ) ) ) : ''; ?>" />
            </form>

            <form method="post">
                <?php
                $requests_table->display();
                $requests_table->embed_scripts();
                ?>
            </form>
        </div>
        <?php
    }

    /**
     * Resend an existing request and return the result.
     *
     * @since 4.9.6
     * @access private
     *
     * @param int $request_id Request ID.
     * @return bool|WP_Error Returns true/false based on the success of sending the email, or a WP_Error object.
     */
    public function _privacy_resend_request( $request_id ) {
        $request_id = absint( $request_id );
        $request    = get_post( $request_id );

        if ( ! $request || 'user_request' !== $request->post_type ) {
            return new WP_Error( 'privacy_request_error', __( 'Invalid request.' ) );
        }

        $result = wp_send_user_request( $request_id );

        if ( is_wp_error( $result ) ) {
            return $result;
        } elseif ( ! $result ) {
            return new WP_Error( 'privacy_request_error', __( 'Unable to initiate confirmation request.' ) );
        }

        return true;
    }

    public function _personal_data_handle_actions() {
        if ( isset( $_POST['privacy_action_email_retry'] ) ) {
            check_admin_referer( 'bulk-privacy_requests' );

            $request_id = absint( current( array_keys( (array) sanitize_text_field( wp_unslash( $_POST['privacy_action_email_retry'] ) ) ) ) );
            $result     = $this->_privacy_resend_request( $request_id );

            if ( is_wp_error( $result ) ) {
                add_settings_error(
                    'privacy_action_email_retry',
                    'privacy_action_email_retry',
                    $result->get_error_message(),
                    'error'
                );
            } else {
                add_settings_error(
                    'privacy_action_email_retry',
                    'privacy_action_email_retry',
                    __( 'Confirmation request sent again successfully.' ),
                    'updated'
                );
            }
        } elseif ( isset( $_POST['action'] ) ) {
            $action = isset( $_POST['action'] ) ? sanitize_key( wp_unslash( $_POST['action'] ) ) : '';

            switch ( $action ) {
                case 'add_export_personal_data_request':
                case 'add_remove_personal_data_request':
                    check_admin_referer( 'personal-data-request' );

                    if ( ! isset( $_POST['type_of_action'], $_POST['username_or_email_for_privacy_request'] ) ) {
                        add_settings_error(
                            'action_type',
                            'action_type',
                            __( 'Invalid action.' ),
                            'error'
                        );
                    }
                    $action_type               = sanitize_text_field( wp_unslash( $_POST['type_of_action'] ) );
                    $username_or_email_address = sanitize_text_field( wp_unslash( $_POST['username_or_email_for_privacy_request'] ) );
                    $email_address             = '';

                    if ( ! in_array( $action_type, _wp_privacy_action_request_types(), true ) ) {
                        add_settings_error(
                            'action_type',
                            'action_type',
                            __( 'Invalid action.' ),
                            'error'
                        );
                    }

                    if ( ! is_email( $username_or_email_address ) ) {
                        $user = get_user_by( 'login', $username_or_email_address );
                        if ( ! $user instanceof WP_User ) {
                            add_settings_error(
                                'username_or_email_for_privacy_request',
                                'username_or_email_for_privacy_request',
                                __( 'Unable to add this request. A valid email address or username must be supplied.' ),
                                'error'
                            );
                        } else {
                            $email_address = $user->user_email;
                        }
                    } else {
                        $email_address = $username_or_email_address;
                    }

                    if ( empty( $email_address ) ) {
                        break;
                    }

                    $request_id = dt_create_user_request( $email_address, $action_type );

                    if ( is_wp_error( $request_id ) ) {
                        add_settings_error(
                            'username_or_email_for_privacy_request',
                            'username_or_email_for_privacy_request',
                            $request_id->get_error_message(),
                            'error'
                        );
                        break;
                    } elseif ( ! $request_id ) {
                        add_settings_error(
                            'username_or_email_for_privacy_request',
                            'username_or_email_for_privacy_request',
                            __( 'Unable to initiate confirmation request.' ),
                            'error'
                        );
                        break;
                    }

                    wp_send_user_request( $request_id );

                    add_settings_error(
                        'username_or_email_for_privacy_request',
                        'username_or_email_for_privacy_request',
                        __( 'Confirmation request initiated successfully.' ),
                        'updated'
                    );
                    break;
            }
        }
    }

    /**
     * Cleans up failed and expired requests before displaying the list table.
     *
     * @since 4.9.6
     * @access private
     */
    public function _personal_data_cleanup_requests() {
        /** This filter is documented in wp-includes/user.php */
        $expires        = (int) apply_filters( 'contact_request_key_expiration', DAY_IN_SECONDS );

        $requests_query = new WP_Query( array(
            'post_type'      => 'contact_request',
            'posts_per_page' => -1,
            'post_status'    => 'request-pending',
            'fields'         => 'ids',
            'date_query'     => array(
                array(
                    'column' => 'post_modified_gmt',
                    'before' => $expires . ' seconds ago',
                ),
            ),
        ) );

        $request_ids = $requests_query->posts;

        foreach ( $request_ids as $request_id ) {
            wp_update_post( array(
                'ID'            => $request_id,
                'post_status'   => 'request-failed',
                'post_password' => '',
            ) );
        }
    }
}


/**
 * DT_Privacy_Requests_Table class.
 *
 * @since 4.9.6
 */
abstract class DT_Privacy_Requests_Table extends WP_List_Table {

    /**
     * Action name for the requests this table will work with. Classes
     * which inherit from DT_Privacy_Requests_Table should define this.
     *
     * Example: 'export_personal_data'.
     *
     * @since 4.9.6
     *
     * @var string $request_type Name of action.
     */
    protected $request_type = 'INVALID';

    /**
     * Post type to be used.
     *
     * @since 4.9.6
     *
     * @var string $post_type The post type.
     */
    protected $post_type = 'INVALID';

    /**
     * Get columns to show in the list table.
     *
     * @since 4.9.6
     *
     * @return array Array of columns.
     */
    public function get_columns() {
        $columns = array(
            'cb'                => '<input type="checkbox" />',
            'email'             => __( 'Requester' ),
            'status'            => __( 'Status' ),
            'created_timestamp' => __( 'Requested' ),
            'next_steps'        => __( 'Next Steps' ),
        );
        return $columns;
    }

    /**
     * Get a list of sortable columns.
     *
     * @since 4.9.6
     *
     * @return array Default sortable columns.
     */
    protected function get_sortable_columns() {
        return array();
    }

    /**
     * Default primary column.
     *
     * @since 4.9.6
     *
     * @return string Default primary column name.
     */
    protected function get_default_primary_column_name() {
        return 'email';
    }

    /**
     * Count number of requests for each status.
     *
     * @since 4.9.6
     *
     * @return object Number of posts for each status.
     */
    protected function get_request_counts() {
        global $wpdb;

        $cache_key = $this->post_type . '-' . $this->request_type;
        $counts    = wp_cache_get( $cache_key, 'counts' );

        if ( false !== $counts ) {
            return $counts;
        }

        $query = "
			SELECT post_status, COUNT( * ) AS num_posts
			FROM {$wpdb->posts}
			WHERE post_type = %s
			AND post_name = %s
			GROUP BY post_status";

        // @codingStandardsIgnoreLine
        $results = (array) $wpdb->get_results( $wpdb->prepare( $query, $this->post_type, $this->request_type ), ARRAY_A );
        $counts  = array_fill_keys( get_post_stati(), 0 );

        foreach ( $results as $row ) {
            $counts[ $row['post_status'] ] = $row['num_posts'];
        }

        $counts = (object) $counts;
        wp_cache_set( $cache_key, $counts, 'counts' );

        return $counts;
    }

    /**
     * Get an associative array ( id => link ) with the list of views available on this table.
     *
     * @since 4.9.6
     *
     * @return array Associative array of views in the format of $view_name => $view_markup.
     */
    protected function get_views() {
        $current_status = isset( $_REQUEST['filter-status'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['filter-status'] ) ) : '';
        $statuses       = _wp_privacy_statuses();
        $views          = array();
        $admin_url      = admin_url( 'admin.php?page=dt_utilities&tab=' . $this->request_type );
        $counts         = $this->get_request_counts();

        $current_link_attributes = empty( $current_status ) ? ' class="current" aria-current="page"' : '';
        $views['all']            = '<a href="' . esc_url( $admin_url ) . "\" $current_link_attributes>" . esc_html__( 'All' ) . ' (' . absint( array_sum( (array) $counts ) ) . ')</a>';

        foreach ( $statuses as $status => $label ) {
            $current_link_attributes = $status === $current_status ? ' class="current" aria-current="page"' : '';
            $views[ $status ]        = '<a href="' . esc_url( add_query_arg( 'filter-status', $status, $admin_url ) ) . "\" $current_link_attributes>" . esc_html( $label ) . ' (' . absint( $counts->$status ) . ')</a>';
        }

        return $views;
    }

    /**
     * Get bulk actions.
     *
     * @since 4.9.6
     *
     * @return array List of bulk actions.
     */
    protected function get_bulk_actions() {
        return array(
            'delete' => __( 'Remove' ),
            'resend' => __( 'Resend email' ),
        );
    }

    /**
     * Process bulk actions.
     *
     * @since 4.9.6
     */
    public function process_bulk_action() {
        $action      = $this->current_action();
        $request_ids = isset( $_REQUEST['request_id'] ) ? wp_parse_id_list( wp_unslash( $_REQUEST['request_id'] ) ) : array();

        $count       = 0;

        if ( $request_ids ) {
            check_admin_referer( 'bulk-privacy_requests' );
        }

        switch ( $action ) {
            case 'delete':
                foreach ( $request_ids as $request_id ) {
                    if ( wp_delete_post( $request_id, true ) ) {
                        $count ++;
                    }
                }

                add_settings_error(
                    'bulk_action',
                    'bulk_action',
                    /* translators: %d: number of requests */
                    sprintf( _n( 'Deleted %d request', 'Deleted %d requests', $count ), $count ),
                    'updated'
                );
                break;
            case 'resend':
                foreach ( $request_ids as $request_id ) {
                    $resend = _wp_privacy_resend_request( $request_id );

                    if ( $resend && ! is_wp_error( $resend ) ) {
                        $count++;
                    }
                }

                add_settings_error(
                    'bulk_action',
                    'bulk_action',
                    /* translators: %d: number of requests */
                    sprintf( _n( 'Re-sent %d request', 'Re-sent %d requests', $count ), $count ),
                    'updated'
                );
                break;
        }
    }

    /**
     * Prepare items to output.
     *
     * @since 4.9.6
     */
    public function prepare_items() {
        global $wpdb;

        $primary               = $this->get_primary_column_name();
        $this->_column_headers = array(
            $this->get_columns(),
            array(),
            $this->get_sortable_columns(),
            $primary,
        );

        $this->items    = array();
        $posts_per_page = $this->get_items_per_page( $this->request_type . '_requests_per_page' );
        $args           = array(
            'post_type'      => $this->post_type,
            'post_name__in'  => array( $this->request_type ),
            'posts_per_page' => $posts_per_page,
            'offset'         => isset( $_REQUEST['paged'] ) ? max( 0, absint( $_REQUEST['paged'] ) - 1 ) * $posts_per_page : 0,
            'post_status'    => 'any',
            's'              => isset( $_REQUEST['s'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['s'] ) ) : '',
        );

        if ( ! empty( $_REQUEST['filter-status'] ) ) {
            $filter_status       = isset( $_REQUEST['filter-status'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['filter-status'] ) ) : '';
            $args['post_status'] = $filter_status;
        }

        $requests_query = new WP_Query( $args );
        $requests       = $requests_query->posts;

        foreach ( $requests as $request ) {
            $this->items[] = $this->get_user_request_data( $request->ID );
        }

        $this->items = array_filter( $this->items );

        $this->set_pagination_args(
            array(
                'total_items' => $requests_query->found_posts,
                'per_page'    => $posts_per_page,
            )
        );
    }

    /**
     * Return data about a user request.
     *
     * @since 4.9.6
     *
     * @param int $request_id Request ID to get data about.
     * @return WP_User_Request|false
     */
    public function get_user_request_data( $request_id ) {
        $request_id = absint( $request_id );
        $post       = get_post( $request_id );

        if ( ! $post || 'contact_request' !== $post->post_type ) {
            return false;
        }

        return new WP_User_Request( $post );
    }



    /**
     * Checkbox column.
     *
     * @since 4.9.6
     *
     * @param WP_User_Request $item Item being shown.
     * @return string Checkbox column markup.
     */
    public function column_cb( $item ) {
        return sprintf( '<input type="checkbox" name="request_id[]" value="%1$s" /><span class="spinner"></span>', esc_attr( $item->ID ) );
    }

    /**
     * Status column.
     *
     * @since 4.9.6
     *
     * @param WP_User_Request $item Item being shown.
     * @return string Status column markup.
     */
    public function column_status( $item ) {
        $status        = get_post_status( $item->ID );
        $status_object = get_post_status_object( $status );

        if ( ! $status_object || empty( $status_object->label ) ) {
            return '-';
        }

        $timestamp = false;

        switch ( $status ) {
            case 'request-confirmed':
                $timestamp = $item->confirmed_timestamp;
                break;
            case 'request-completed':
                $timestamp = $item->completed_timestamp;
                break;
        }

        echo '<span class="status-label status-' . esc_attr( $status ) . '">';
        echo esc_html( $status_object->label );

        if ( $timestamp ) {
            echo ' (' . esc_attr( $this->get_timestamp_as_date( $timestamp ) ) . ')';
        }

        echo '</span>';
    }

    /**
     * Convert timestamp for display.
     *
     * @since 4.9.6
     *
     * @param int $timestamp Event timestamp.
     * @return string Human readable date.
     */
    protected function get_timestamp_as_date( $timestamp ) {
        if ( empty( $timestamp ) ) {
            return '';
        }

        $time_diff = current_time( 'timestamp', true ) - $timestamp;

        if ( $time_diff >= 0 && $time_diff < DAY_IN_SECONDS ) {
            /* translators: human readable timestamp */
            return sprintf( __( '%s ago' ), human_time_diff( $timestamp ) );
        }

        return date_i18n( get_option( 'date_format' ), $timestamp );
    }

    /**
     * Default column handler.
     *
     * @since 4.9.6
     *
     * @param WP_User_Request $item        Item being shown.
     * @param string          $column_name Name of column being shown.
     * @return string Default column output.
     */
    public function column_default( $item, $column_name ) {
        $cell_value = $item->$column_name;

        if ( in_array( $column_name, array( 'created_timestamp' ), true ) ) {
            return $this->get_timestamp_as_date( $cell_value );
        }

        return $cell_value;
    }

    /**
     * Actions column. Overridden by children.
     *
     * @since 4.9.6
     *
     * @param WP_User_Request $item Item being shown.
     * @return string Email column markup.
     */
    public function column_email( $item ) {
        return sprintf( '<a href="%1$s">%2$s</a> %3$s', esc_url( 'mailto:' . $item->email ), $item->email, $this->row_actions( array() ) );
    }

    /**
     * Next steps column. Overridden by children.
     *
     * @since 4.9.6
     *
     * @param WP_User_Request $item Item being shown.
     */
    public function column_next_steps( $item ) {}

    /**
     * Generates content for a single row of the table,
     *
     * @since 4.9.6
     *
     * @param WP_User_Request $item The current item.
     */
    public function single_row( $item ) {
        $status = $item->status;

        echo '<tr id="request-' . esc_attr( $item->ID ) . '" class="status-' . esc_attr( $status ) . '">';
        $this->single_row_columns( $item );
        echo '</tr>';
    }

    /**
     * Embed scripts used to perform actions. Overridden by children.
     *
     * @since 4.9.6
     */
    public function embed_scripts() {}
}

/**
 * DT_Privacy_Data_Export_Requests_Table class.
 *
 * @since 4.9.6
 */
class DT_Privacy_Data_Export_Requests_Table extends DT_Privacy_Requests_Table {
    /**
     * Action name for the requests this table will work with.
     *
     * @since 4.9.6
     *
     * @var string $request_type Name of action.
     */
    protected $request_type = 'export_contact_data';

    /**
     * Post type for the requests.
     *
     * @since 4.9.6
     *
     * @var string $post_type The post type.
     */
    protected $post_type = 'contact_request';

    /**
     * Actions column.
     *
     * @since 4.9.6
     *
     * @param WP_User_Request $item Item being shown.
     * @return string Email column markup.
     */
    public function column_email( $item ) {
        /** This filter is documented in wp-admin/includes/ajax-actions.php */
        $exporters       = apply_filters( 'dt_privacy_personal_data_exporters', array() );
        $exporters_count = count( $exporters );
        $request_id      = $item->ID;
        $nonce           = wp_create_nonce( 'wp-privacy-export-personal-data-' . $request_id );

        $download_data_markup = '<div class="export-personal-data" ' .
            'data-exporters-count="' . esc_attr( $exporters_count ) . '" ' .
            'data-request-id="' . esc_attr( $request_id ) . '" ' .
            'data-nonce="' . esc_attr( $nonce ) .
            '">';

        $download_data_markup .= '<span class="export-personal-data-idle"><button type="button" class="button-link export-personal-data-handle">' . __( 'Download Personal Data' ) . '</button></span>' .
            '<span style="display:none" class="export-personal-data-processing" >' . __( 'Downloading Data...' ) . '</span>' .
            '<span style="display:none" class="export-personal-data-success"><button type="button" class="button-link export-personal-data-handle">' . __( 'Download Personal Data Again' ) . '</button></span>' .
            '<span style="display:none" class="export-personal-data-failed">' . __( 'Download has failed.' ) . ' <button type="button" class="button-link">' . __( 'Retry' ) . '</button></span>';

        $download_data_markup .= '</div>';

        $row_actions = array(
            'download-data' => $download_data_markup,
        );

        return sprintf( '<a href="%1$s">%2$s</a> %3$s', esc_url( 'mailto:' . $item->email ), $item->email, $this->row_actions( $row_actions ) );
    }

    /**
     * Displays the next steps column.
     *
     * @since 4.9.6
     *
     * @param WP_User_Request $item Item being shown.
     */
    public function column_next_steps( $item ) {
        $status = $item->status;

        switch ( $status ) {
            case 'request-pending':
                esc_html_e( 'Waiting for confirmation', "disciple_tools" );
                break;
            case 'request-confirmed':
                /** This filter is documented in wp-admin/includes/ajax-actions.php */
                $exporters       = apply_filters( 'wp_privacy_personal_data_exporters', array() );
                $exporters_count = count( $exporters );
                $request_id      = $item->ID;
                $nonce           = wp_create_nonce( 'wp-privacy-export-personal-data-' . $request_id );

                echo '<div class="export-personal-data" ' .
                    'data-send-as-email="1" ' .
                    'data-exporters-count="' . esc_attr( $exporters_count ) . '" ' .
                    'data-request-id="' . esc_attr( $request_id ) . '" ' .
                    'data-nonce="' . esc_attr( $nonce ) .
                    '">';

                ?>
                <span class="export-personal-data-idle"><button type="button" class="button export-personal-data-handle"><?php esc_attr_e( 'Email Data' ); ?></button></span>
                <span style="display:none" class="export-personal-data-processing button updating-message" ><?php esc_attr_e( 'Sending Email...' ); ?></span>
                <span style="display:none" class="export-personal-data-success success-message" ><?php esc_attr_e( 'Email sent.' ); ?></span>
                <span style="display:none" class="export-personal-data-failed"><?php esc_attr_e( 'Email could not be sent.' ); ?> <button type="button" class="button export-personal-data-handle"><?php esc_attr_e( 'Retry' ); ?></button></span>
                <?php

                echo '</div>';
                break;
            case 'request-failed':
                submit_button( __( 'Retry' ), 'secondary', 'privacy_action_email_retry[' . $item->ID . ']', false );
                break;
            case 'request-completed':
                echo '<a href="' . esc_url( wp_nonce_url( add_query_arg( array(
                        'action'     => 'delete',
                        'request_id' => array( $item->ID ),
                ), admin_url( 'admin.php?page=dt_utilities&tab=gdpr-export' ) ), 'bulk-privacy_requests' ) ) . '" class="button">' . esc_html__( 'Remove request' ) . '</a>';
                break;
        }
    }
}

/**
 * DT_Privacy_Data_Removal_Requests_Table class.
 *
 * @since 4.9.6
 */
class DT_Privacy_Data_Removal_Requests_Table extends DT_Privacy_Requests_Table {
    /**
     * Action name for the requests this table will work with.
     *
     * @since 4.9.6
     *
     * @var string $request_type Name of action.
     */
    protected $request_type = 'remove_contact_data';

    /**
     * Post type for the requests.
     *
     * @since 4.9.6
     *
     * @var string $post_type The post type.
     */
    protected $post_type = 'contact_request';

    /**
     * Actions column.
     *
     * @since 4.9.6
     *
     * @param WP_User_Request $item Item being shown.
     * @return string Email column markup.
     */
    public function column_email( $item ) {
        $row_actions = array();

        // Allow the administrator to "force remove" the personal data even if confirmation has not yet been received.
        $status = $item->status;
        if ( 'request-confirmed' !== $status ) {
            /** This filter is documented in wp-admin/includes/ajax-actions.php */
            $erasers       = apply_filters( 'wp_privacy_personal_data_erasers', array() );
            $erasers_count = count( $erasers );
            $request_id    = $item->ID;
            $nonce         = wp_create_nonce( 'wp-privacy-erase-personal-data-' . $request_id );

            $remove_data_markup = '<div class="remove-personal-data force-remove-personal-data" ' .
                'data-erasers-count="' . esc_attr( $erasers_count ) . '" ' .
                'data-request-id="' . esc_attr( $request_id ) . '" ' .
                'data-nonce="' . esc_attr( $nonce ) .
                '">';

            $remove_data_markup .= '<span class="remove-personal-data-idle"><button type="button" class="button-link remove-personal-data-handle">' . __( 'Force Erase Personal Data' ) . '</button></span>' .
                '<span style="display:none" class="remove-personal-data-processing" >' . __( 'Erasing Data...' ) . '</span>' .
                '<span style="display:none" class="remove-personal-data-failed">' . __( 'Force Erase has failed.' ) . ' <button type="button" class="button-link remove-personal-data-handle">' . __( 'Retry' ) . '</button></span>';

            $remove_data_markup .= '</div>';

            $row_actions = array(
                'remove-data' => $remove_data_markup,
            );
        }

        return sprintf( '<a href="%1$s">%2$s</a> %3$s', esc_url( 'mailto:' . $item->email ), $item->email, $this->row_actions( $row_actions ) );
    }

    /**
     * Next steps column.
     *
     * @since 4.9.6
     *
     * @param WP_User_Request $item Item being shown.
     */
    public function column_next_steps( $item ) {
        $status = $item->status;

        switch ( $status ) {
            case 'request-pending':
                esc_html_e( 'Waiting for confirmation', "disciple_tools" );
                break;
            case 'request-confirmed':
                /** This filter is documented in wp-admin/includes/ajax-actions.php */
                $erasers       = apply_filters( 'wp_privacy_personal_data_erasers', array() );
                $erasers_count = count( $erasers );
                $request_id    = $item->ID;
                $nonce         = wp_create_nonce( 'wp-privacy-erase-personal-data-' . $request_id );

                echo '<div class="remove-personal-data" ' .
                    'data-force-erase="1" ' .
                    'data-erasers-count="' . esc_attr( $erasers_count ) . '" ' .
                    'data-request-id="' . esc_attr( $request_id ) . '" ' .
                    'data-nonce="' . esc_attr( $nonce ) .
                    '">';

                ?>
                <span class="remove-personal-data-idle"><button type="button" class="button remove-personal-data-handle"><?php esc_attr_e( 'Erase Personal Data' ); ?></button></span>
                <span style="display:none" class="remove-personal-data-processing button updating-message" ><?php esc_attr_e( 'Erasing Data...' ); ?></span>
                <span style="display:none" class="remove-personal-data-failed"><?php esc_attr_e( 'Erasing Data has failed.' ); ?> <button type="button" class="button remove-personal-data-handle"><?php esc_attr_e( 'Retry' ); ?></button></span>
                <?php

                echo '</div>';

                break;
            case 'request-failed':
                submit_button( __( 'Retry' ), 'secondary', 'privacy_action_email_retry[' . $item->ID . ']', false );
                break;
            case 'request-completed':
                echo '<a href="' . esc_url( wp_nonce_url( add_query_arg( array(
                        'action'     => 'delete',
                        'request_id' => array( $item->ID ),
                ), admin_url( 'admin.php?page=dt_utilities&tab=gdpr-erase' ) ), 'bulk-privacy_requests' ) ) . '" class="button">' . esc_html__( 'Remove request' ) . '</a>';
                break;
        }
    }

}

/**
 * Create and log a user request to perform a specific action.
 *
 * Requests are stored inside a post type named `user_request` since they can apply to both
 * users on the site, or guests without a user account.
 *
 * @since 4.9.6
 *
 * @param string $email_address User email address. This can be the address of a registered or non-registered user.
 * @param string $action_name   Name of the action that is being confirmed. Required.
 * @param array  $request_data  Misc data you want to send with the verification request and pass to the actions once the request is confirmed.
 * @return int|WP_Error Returns the request ID if successful, or a WP_Error object on failure.
 */
function dt_create_user_request( $email_address = '', $action_name = '', $request_data = array() ) {
    $email_address = sanitize_email( $email_address );
    $action_name   = sanitize_key( $action_name );

    if ( ! is_email( $email_address ) ) {
        return new WP_Error( 'invalid_email', __( 'Invalid email address.' ) );
    }

    if ( ! $action_name ) {
        return new WP_Error( 'invalid_action', __( 'Invalid action name.' ) );
    }

    $user    = get_user_by( 'email', $email_address );
    $user_id = $user && ! is_wp_error( $user ) ? $user->ID : 0;

    // Check for duplicates.
    $requests_query = new WP_Query( array(
        'post_type'     => 'contact_request',
        'post_name__in' => array( $action_name ),  // Action name stored in post_name column.
        'title'         => $email_address, // Email address stored in post_title column.
        'post_status'   => 'any',
        'fields'        => 'ids',
    ) );

    if ( $requests_query->found_posts ) {
        return new WP_Error( 'duplicate_request', __( 'A request for this email address already exists.' ) );
    }

    $request_id = wp_insert_post( array(
        'post_author'   => $user_id,
        'post_name'     => $action_name,
        'post_title'    => $email_address,
        'post_content'  => wp_json_encode( $request_data ),
        'post_status'   => 'request-pending',
        'post_type'     => 'contact_request',
        'post_date'     => current_time( 'mysql', false ),
        'post_date_gmt' => current_time( 'mysql', true ),
    ), true );

    return $request_id;
}
