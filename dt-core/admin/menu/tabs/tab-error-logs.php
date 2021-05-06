<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class Disciple_Tools_Tab_Logs
 */
class Disciple_Tools_Tab_Logs extends Disciple_Tools_Abstract_Menu_Base {
    private static $_instance = null;

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
        add_action( 'admin_menu', [ $this, 'add_submenu' ], 125 );
        add_action( 'dt_utilities_tab_menu', [ $this, 'add_tab' ], 125, 1 );
        add_action( 'dt_utilities_tab_content', [ $this, 'content' ], 125, 1 );

        parent::__construct();
    } // End __construct()

    public function add_submenu() {
        add_submenu_page( 'edit.php?post_type=logs', __( 'Import', 'disciple_tools' ), __( 'Import', 'disciple_tools' ), 'manage_dt', 'dt_utilities&tab=logs', [
            'Disciple_Tools_Settings_Menu',
            'content'
        ] );
        add_submenu_page( 'dt_utilities', __( 'Error Logs', 'disciple_tools' ), __( 'Error Logs', 'disciple_tools' ), 'manage_dt', 'dt_utilities&tab=logs', [
            'Disciple_Tools_Settings_Menu',
            'content'
        ] );
    }

    public function add_tab( $tab ) {
        echo '<a href="' . esc_url( admin_url() ) . 'admin.php?page=dt_utilities&tab=logs" class="nav-tab ';
        if ( $tab == 'logs' ) {
            echo 'nav-tab-active';
        }
        echo '">' . esc_attr__( 'Error Logs' ) . '</a>';
    }

    public function content( $tab ) {
        if ( 'logs' == $tab ) :

            $this->template( 'begin' );

            $this->process_settings();
            $this->display_settings();
            $this->display_logs();

            $this->template( 'right_column' );

            $this->template( 'end' );

        endif;
    }

    private function process_settings() {
        if ( isset( $_POST['email_error_logs_nonce'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_POST['email_error_logs_nonce'] ) ), 'email_error_logs_nonce' ) ) {
            update_option( 'dt_error_log_dispatch_emails', isset( $_POST['dispatch_error_log_emails'] ) ? 1 : 0 );
            update_option( 'dt_error_log_display_count', isset( $_POST['number_of_error_logs_to_display'] ) ? intval( $_POST['number_of_error_logs_to_display'] ) : 20 );
            update_option( 'dt_error_log_enforce_retention_policy', isset( $_POST['enforce_retention_policy'] ) ? 0 : 1 );
            update_option( 'dt_error_log_retention_period_count', isset( $_POST['retention_period_days'] ) ? intval( $_POST['retention_period_days'] ) : 30 );
        }
    }

    private function is_dispatch_emails_enabled(): bool {
        return boolval( get_option( 'dt_error_log_dispatch_emails' ) );
    }

    private function fetch_display_count(): int {
        $display_count_option = get_option( 'dt_error_log_display_count' );
        return ( $display_count_option > 0 ) ? intval( $display_count_option ) : 20;
    }

    private function is_enforce_retention_policy_enabled(): bool {
        return ! boolval( get_option( 'dt_error_log_enforce_retention_policy' ) );
    }

    private function fetch_retention_period_count(): int {
        $retention_period_count = get_option( 'dt_error_log_retention_period_count' );
        return ( $retention_period_count > 0 ) ? intval( $retention_period_count ) : 30;
    }

    private function display_settings() {

        $this->box( 'top', 'Logging Settings', [ "col_span" => 4 ] );

        ?>
        <form method="POST">
            <input type="hidden" name="email_error_logs_nonce" id="email_error_logs_nonce"
                   value="<?php echo esc_attr( wp_create_nonce( 'email_error_logs_nonce' ) ) ?>"/>
            <table class="widefat striped">
                <tr>
                    <td align="right">
                        <input type="checkbox" id="dispatch_error_log_emails"
                               name="dispatch_error_log_emails" <?php echo esc_html( $this->is_dispatch_emails_enabled() ? 'checked' : '' ) ?> />
                    </td>
                    <td>Dispatch Error Log Notification Emails</td>
                </tr>
                <tr>
                    <td align="right">
                        <input type="number" id="number_of_error_logs_to_display"
                               name="number_of_error_logs_to_display" value="<?php echo esc_html( $this->fetch_display_count() ) ?>"/>
                    </td>
                    <td>Number Of Error Logs To Be Displayed</td>
                </tr>
                <tr>
                    <td align="right">
                        <input type="checkbox" id="enforce_retention_policy"
                               name="enforce_retention_policy" <?php echo esc_html( $this->is_enforce_retention_policy_enabled() ? 'checked' : '' ) ?> />
                    </td>
                    <td>Enforce Error Log Retention Policy</td>
                </tr>
                <tr>
                    <td align="right">
                        <input type="number" id="retention_period_days"
                               name="retention_period_days" value="<?php echo esc_html( $this->fetch_retention_period_count() ) ?>"/>
                    </td>
                    <td>Retention Period (Days)</td>
                </tr>
            </table>
            <br>
            <span style="float:right;"><button type="submit"
                                               class="button float-right"><?php esc_html_e( "Update", 'disciple_tools' ) ?></button></span>
        </form>
        <?php

        $this->box( 'bottom' );
    }

    private function display_logs() {
        global $wpdb;

        // Obtain list of recent error logs
        $logs = $wpdb->get_results( $wpdb->prepare( "
SELECT act.hist_time, if(act.user_id > 0, usr.display_name, '') user, act.meta_key, act.meta_value, act.object_note
FROM $wpdb->dt_activity_log act
LEFT JOIN $wpdb->users AS usr ON (usr.ID = act.user_id)
WHERE (act.action = 'error_log')
ORDER BY act.hist_time
DESC LIMIT %d", $this->fetch_display_count() ) );

        $this->box( 'top', 'Error Logs', [ "col_span" => 4 ] );

        ?>
        <table class="widefat striped">
            <tr>
                <th>Timestamp</th>
                <th>User</th>
                <th>Key</th>
                <th>Value</th>
                <th>Note</th>
            </tr>
        <?php
        if ( ! empty( $logs ) ) {
            foreach ( $logs as $log ) {
                echo '<tr>';
                echo '<td>' . esc_attr( gmdate( "Y-m-d h:i:sa", esc_attr( $log->hist_time ) ) ) . '</td>';
                echo '<td>' . esc_attr( $log->user ) . '</td>';
                echo '<td>' . esc_attr( $log->meta_key ) . '</td>';
                echo '<td>' . esc_attr( $this->format_meta_value( maybe_unserialize( $log->meta_value ) ) ) . '</td>';
                echo '<td>' . esc_attr( $log->object_note ) . '</td>';
                echo '</tr>';
            }
        }
        echo '</table>';
        $this->box( 'bottom' );
    }

    private function format_meta_value( $meta_value ): string {
        if ( is_array( $meta_value ) ) {
            $key_value = array();

            foreach ( array_keys( $meta_value ) as $key ) {
                array_push( $key_value, $key . ": " . $this->format_meta_value( $meta_value[ $key ] ) );
            }

            return implode( ", ", $key_value );
        }

        return $meta_value ?? '';
    }
}

Disciple_Tools_Tab_Logs::instance();
