<?php

if ( !defined( 'ABSPATH' ) ){
    exit; // Exit if accessed directly
}

/**
 * Class Disciple_Tools_Tab_Logs
 */
class Disciple_Tools_Tab_Email_Logs extends Disciple_Tools_Abstract_Menu_Base{
    private static $_instance = null;

    public static function instance(){
        if ( is_null( self::$_instance ) ){
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
    public function __construct(){
        add_action( 'admin_menu', array( $this, 'add_submenu' ), 125 );
        add_action( 'dt_utilities_tab_menu', array( $this, 'add_tab' ), 125, 1 );
        add_action( 'dt_utilities_tab_content', array( $this, 'content' ), 125, 1 );

        parent::__construct();
    } // End __construct()

    public function add_submenu(){
        add_submenu_page( 'edit.php?post_type=email_logs', __( 'Email Logs', 'disciple_tools' ), __( 'Email Logs', 'disciple_tools' ), 'manage_dt', 'dt_utilities&tab=email_logs', array(
            'Disciple_Tools_Settings_Menu',
            'content',
        ) );
        add_submenu_page( 'dt_utilities', __( 'Email Logs', 'disciple_tools' ), __( 'Email Logs', 'disciple_tools' ), 'manage_dt', 'dt_utilities&tab=email_logs', array(
            'Disciple_Tools_Settings_Menu',
            'content',
        ) );
    }

    public function add_tab( $tab ){
        echo '<a href="' . esc_url( admin_url() ) . 'admin.php?page=dt_utilities&tab=email_logs" class="nav-tab ';
        if ( $tab == 'email_logs' ){
            echo 'nav-tab-active';
        }
        echo '">' . esc_attr__( 'Email Logs' ) . '</a>';
    }

    public function content( $tab ){
        if ( 'email_logs' == $tab ) :

            $this->template( 'begin' );

            $this->process_settings();
            $this->display_settings();
            $this->display_logs();

            $this->template( 'right_column' );

            $this->template( 'end' );

        endif;
    }

    private function process_settings(){
        if ( isset( $_POST['email_logs_nonce'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_POST['email_logs_nonce'] ) ), 'email_logs_nonce' ) ){
            update_option( 'dt_email_logs_enabled', isset( $_POST['email_logs_enabled'] ) ? 1 : 0 );
            update_option( 'dt_email_logs_display_count', isset( $_POST['email_logs_display_count'] ) ? intval( $_POST['email_logs_display_count'] ) : 20 );
        }
    }

    private function is_email_logs_enabled(): bool{
        return boolval( get_option( 'dt_email_logs_enabled' ) );
    }

    private function fetch_display_count(): int{
        $display_count_option = get_option( 'dt_email_logs_display_count' );
        return ( $display_count_option > 0 ) ? intval( $display_count_option ) : 20;
    }

    private function display_settings(){

        $this->box( 'top', 'Logging Settings', array( 'col_span' => 4 ) );

        ?>
        <form method="POST">
            <input type="hidden" name="email_logs_nonce" id="email_logs_nonce"
                   value="<?php echo esc_attr( wp_create_nonce( 'email_logs_nonce' ) ) ?>"/>
            <table class="widefat striped">
                <tr>
                    <td align="right">
                        <input type="checkbox" id="email_logs_enabled"
                               name="email_logs_enabled" <?php echo esc_html( $this->is_email_logs_enabled() ? 'checked' : '' ) ?> />
                    </td>
                    <td>Enable Email Logging</td>
                </tr>
                <tr>
                    <td align="right">
                        <input type="number" id="email_logs_display_count"
                               name="email_logs_display_count"
                               value="<?php echo esc_html( $this->fetch_display_count() ) ?>"/>
                    </td>
                    <td>Number Of Email Logs To Be Displayed</td>
                </tr>
            </table>
            <br>
            <span style="float:right;"><button type="submit"
                                               class="button float-right"><?php esc_html_e( 'Update', 'disciple_tools' ) ?></button></span>
        </form>
        <?php

        $this->box( 'bottom' );
    }

    private function display_logs(){
        global $wpdb;

        // Obtain list of recent error logs
        $logs = $wpdb->get_results( $wpdb->prepare( "
SELECT act.hist_time, if(act.user_id > 0, usr.display_name, '') user, act.object_name, act.meta_value, act.object_note
FROM $wpdb->dt_activity_log act
LEFT JOIN $wpdb->users AS usr ON (usr.ID = act.user_id)
WHERE (act.action = 'mail_sent')
ORDER BY act.hist_time
DESC LIMIT %d", $this->fetch_display_count() ) );

        $this->box( 'top', 'Email Logs', array( 'col_span' => 4 ) );

        ?>
        <table class="widefat striped">
            <tr>
                <th>Timestamp</th>
                <th>User</th>
                <th>Subject</th>
                <th>To</th>
                <th>Message</th>
            </tr>
        <?php
        if ( !empty( $logs ) ){
            foreach ( $logs as $log ){
                echo '<tr>';
                echo '<td>' . esc_attr( gmdate( 'Y-m-d h:i:sa', esc_attr( $log->hist_time ) ) ) . '</td>';
                echo '<td>' . esc_attr( $log->user ) . '</td>';
                echo '<td>' . esc_attr( $log->object_name ) . '</td>';
                echo '<td>' . esc_attr( $this->format_object_name( json_decode( $log->meta_value, true ) ) ) . '</td>';
                echo '<td>' . esc_attr( $log->object_note ) . '</td>';
                echo '</tr>';
            }
        }
        echo '</table>';
        $this->box( 'bottom' );
    }

    private function format_object_name( $obj_name ): string{
        if ( is_array( $obj_name ) ){
            $key_value = array();

            foreach ( array_keys( $obj_name ) as $key ){
                $key_value[] = $this->format_object_name( $obj_name[$key] );
            }

            return implode( ', ', $key_value );
        }

        return $obj_name ?? '';
    }
}

Disciple_Tools_Tab_Email_Logs::instance();
