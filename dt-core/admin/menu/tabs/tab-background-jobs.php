<?php

if ( !defined( 'ABSPATH' ) ){
    exit; // Exit if accessed directly
}

/**
 * Class Disciple_Tools_Tab_Logs
 */
class Disciple_Tools_Tab_Background_Jobs extends Disciple_Tools_Abstract_Menu_Base{
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
        add_action( 'admin_menu', [ $this, 'add_submenu' ], 125 );
        add_action( 'dt_utilities_tab_menu', [ $this, 'add_tab' ], 125, 1 );
        add_action( 'dt_utilities_tab_content', [ $this, 'content' ], 125, 1 );

        parent::__construct();
    } // End __construct()

    public function add_submenu(){
        add_submenu_page( 'edit.php?post_type=background_jobs', __( 'Background Jobs', 'disciple_tools' ), __( 'Background Jobs', 'disciple_tools' ), 'manage_dt', 'dt_utilities&tab=background_jobs', [
            'Disciple_Tools_Settings_Menu',
            'content'
        ] );
        add_submenu_page( 'dt_utilities', __( 'Background Jobs', 'disciple_tools' ), __( 'Background Jobs', 'disciple_tools' ), 'manage_dt', 'dt_utilities&tab=background_jobs', [
            'Disciple_Tools_Settings_Menu',
            'content'
        ] );
    }

    public function add_tab( $tab ){
        echo '<a href="' . esc_url( admin_url() ) . 'admin.php?page=dt_utilities&tab=background_jobs" class="nav-tab ';
        if ( $tab == 'background_jobs' ){
            echo 'nav-tab-active';
        }
        echo '">' . esc_attr__( 'Background Jobs' ) . '</a>';
    }

    public function content( $tab ){
        if ( 'background_jobs' == $tab ) :

            $this->template( 'begin' );

            $this->process_settings();
            $this->display_job_total();
            $this->display_settings();
            $this->display_jobs();

            $this->template( 'right_column' );

            $this->template( 'end' );

        endif;
    }

    private function process_settings(){
        if ( isset( $_POST['background_jobs_nonce'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_POST['background_jobs_nonce'] ) ), 'background_jobs_nonce' ) ){
            update_option( 'dt_background_jobs_enabled', isset( $_POST['background_jobs_enabled'] ) ? 1 : 0 );
            update_option( 'dt_background_jobs_display_count', isset( $_POST['background_jobs_display_count'] ) ? intval( $_POST['background_jobs_display_count'] ) : 20 );
        }
    }

    private function fetch_display_count(): int{
        $display_count_option = get_option( 'dt_background_jobs_display_count' );
        return ( $display_count_option > 0 ) ? intval( $display_count_option ) : 20;
    }

    private function display_settings(){

        $this->box( 'top', 'Logging Settings', [ 'col_span' => 4 ] );

        ?>
        <form method="POST">
            <input type="hidden" name="background_jobs_nonce" id="background_jobs_nonce"
                   value="<?php echo esc_attr( wp_create_nonce( 'background_jobs_nonce' ) ) ?>"/>
            <table class="widefat striped">
                <tr>
                    <td align="right">
                        <input type="number" id="background_jobs_display_count"
                               name="background_jobs_display_count"
                               value="<?php echo esc_html( $this->fetch_display_count() ) ?>"/>
                    </td>
                    <td>Number Of Background Jobs To Be Displayed</td>
                </tr>
            </table>
            <br>
            <span style="float:right;"><button type="submit"
                                               class="button float-right"><?php esc_html_e( 'Update', 'disciple_tools' ) ?></button></span>
        </form>
        <?php

        $this->box( 'bottom' );
    }


    private function background_job_queue_counts() {
        global $wpdb;

        wp_queue_wpdb_init();

        return [
            'jobs' => $wpdb->get_var( "SELECT COUNT(id) FROM $wpdb->queue_jobs" ),
            'failures' => $wpdb->get_var( "SELECT COUNT(id) FROM $wpdb->queue_failures" )
        ];
    }

    private function display_job_total() {
                $background_job_counts = $this->background_job_queue_counts();
                echo esc_html( sprintf( 'Background Jobs Queue: Jobs: %1$s, Failures: %2$s', $background_job_counts['jobs'], $background_job_counts['failures'] ) );
    }

    private function display_jobs(){
        global $wpdb;

        // Obtain list of recent error logs
        $jobs = $wpdb->get_results( $wpdb->prepare( "
SELECT que.job AS job_details, que.category, que.attempts, que.priority, que.available_at
FROM $wpdb->queue_jobs que
ORDER BY que.available_at
DESC LIMIT %d", $this->fetch_display_count() ) );

        $this->box( 'top', 'Background Jobs', [ 'col_span' => 4 ] );

        ?>
        <table class="widefat striped">
            <tr>
                <th>Name</th>
                <th>Category</th>
                <th>Subject</th>
                <th>Attempts</th>
                <th>Available At</th>
            </tr>
        <?php
        if ( !empty( $jobs ) ){
            foreach ( $jobs as $job ){
                //Find the job name
                preg_match( ':"(.*?)":', $job->job_details, $job_name );

                echo '<tr>';
                echo '<td>' . esc_attr( $job_name[1] ) . '</td>';
                echo '<td>' . esc_attr( $job->category ) . '</td>';
                echo '<td>' . esc_attr( $job->attempts ) . '</td>';
                echo '<td>' . esc_attr( $job->priority ) . '</td>';
                echo '<td>' . esc_attr( gmdate( 'Y-m-d h:i:sa', strtotime( esc_attr( $job->available_at ) ) ) ) . '</td>';
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

Disciple_Tools_Tab_Background_Jobs::instance();