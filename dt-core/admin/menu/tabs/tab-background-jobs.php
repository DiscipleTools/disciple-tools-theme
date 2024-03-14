<?php

if ( !defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class Disciple_Tools_Tab_Logs
 */
class Disciple_Tools_Tab_Background_Jobs extends Disciple_Tools_Abstract_Menu_Base
{
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
        add_action( 'admin_enqueue_scripts', [ $this, 'admin_enqueue_scripts' ] );

        parent::__construct();
    } // End __construct()

    public function add_submenu() {
        add_submenu_page('edit.php?post_type=background_jobs', __( 'Background Jobs', 'disciple_tools' ), __( 'Background Jobs', 'disciple_tools' ), 'manage_dt', 'dt_utilities&tab=background_jobs', [
            'Disciple_Tools_Settings_Menu',
            'content'
        ]);
        add_submenu_page('dt_utilities', __( 'Background Jobs', 'disciple_tools' ), __( 'Background Jobs', 'disciple_tools' ), 'manage_dt', 'dt_utilities&tab=background_jobs', [
            'Disciple_Tools_Settings_Menu',
            'content'
        ]);
    }

    public function add_tab( $tab ) {
        echo '<a href="' . esc_url( admin_url() ) . 'admin.php?page=dt_utilities&tab=background_jobs" class="nav-tab ';
        if ( $tab == 'background_jobs' ) {
            echo 'nav-tab-active';
        }
        echo '">' . esc_attr__( 'Background Jobs' ) . '</a>';
    }

    public function content( $tab ) {
        if ( 'background_jobs' == $tab ) :

            $this->template( 'begin' );

            $this->display_settings();
            $this->display_jobs();

            $this->template( 'right_column' );

            $this->template( 'end' );

        endif;
    }

    private function display_settings() {

        $this->box( 'top', 'Job Queue Settings', [ 'col_span' => 4 ] );

        ?>
        <form method="POST">
            <table class="widefat striped">
                <tr>
                    <td>
                        Background Jobs Queued: <span id="job-queue-total"><?php $this->display_job_total(); ?></span>

                        <p>
                            <?php $this->display_job_queue_cron_schedule(); ?>
                        </p>
                    </td>
                    <td>
                        <button type="button" class="process-jobs-button" id="process-jobs-button">Process Jobs</button>
                        <span id="process-jobs-loading-spinner" style="display: inline-block" class="loading-spinner"></span>
                        <span class="process-jobs-result-text"></span>
                    </td>
                </tr>
            </table>
            <br>
        </form>

        <?php

        $this->box( 'bottom' );
    }


    private function background_job_queue_counts() {
        global $wpdb;

        wp_queue_wpdb_init();

        return [
            'jobs' => $wpdb->get_var( "SELECT COUNT(id) FROM $wpdb->queue_jobs" )
        ];
    }

    private function display_job_queue_cron_schedule() {
        $wp_queue_cron_schedule = wp_get_schedules();
        if ( array_key_exists( 'wp_queue_connections_databaseconnection', $wp_queue_cron_schedule ) ) {
            echo esc_html( 'The queue is scheduled to be processed: ' . $wp_queue_cron_schedule['wp_queue_connections_databaseconnection']['display'] );
        } else {
            echo esc_html( 'wp_queue has not setup a CRON.' );
        }
    }

    private function display_job_total() {
        $background_job_counts = $this->background_job_queue_counts();
        echo esc_html( $background_job_counts['jobs'] );
    }

    private function display_jobs() {
        global $wpdb;

        // Obtain list of recent error logs
        $jobs = $wpdb->get_results("
        SELECT que.job AS job_details, que.category, que.attempts, que.priority, que.available_at, que.created_at
        FROM $wpdb->queue_jobs que
        ORDER BY que.created_at
        DESC LIMIT 200");

        $this->box( 'top', 'Background Jobs', [ 'col_span' => 4 ] );

        ?>
        <table class="widefat striped">
            <tr>
                <th>Name</th>
                <th>Category</th>
                <th>Attempts</th>
                <th>Priority</th>
                <th>Processing Time</th>
                <th>Created Time</th>
            </tr>
        <?php
        if ( !empty( $jobs ) ) {
            foreach ( $jobs as $job ) {
                //Find the job name from the serialized job
                preg_match( ':"(.*?)":', $job->job_details, $job_name );

                echo '<tr>';
                echo '<td>' . esc_attr( $job_name[1] ) . '</td>';
                echo '<td>' . esc_attr( $job->category ) . '</td>';
                echo '<td>' . esc_attr( $job->attempts ) . '</td>';
                echo '<td>' . esc_attr( $job->priority ) . '</td>';
                echo '<td>' . esc_attr( gmdate( 'Y-m-d h:i:sa', strtotime( esc_attr( $job->available_at ) ) ) ) . '</td>';
                echo '<td>' . esc_attr( gmdate( 'Y-m-d h:i:sa', strtotime( esc_attr( $job->created_at ) ) ) ) . '</td>';
                echo '</tr>';
            }
        }
        echo '</table>';
        $this->box( 'bottom' );
    }


    public function admin_enqueue_scripts() {

        wp_enqueue_script( 'dt_utilities_scripts_script', disciple_tools()->admin_js_url . 'dt-utilities-scripts.js', [
            'jquery',
            'wp-color-picker'
        ], filemtime( disciple_tools()->admin_js_path . 'dt-utilities-scripts.js' ), true );

    }
}

Disciple_Tools_Tab_Background_Jobs::instance();
