<?php

/**
 * Disciple Tools
 *
 * @class      Disciple_Tools_Critical_Path_Tab
 * @version    0.1.0
 * @since      0.1.0
 * @package    Disciple.Tools
 * @author     Disciple.Tools
 */

if ( !defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}


/**
 * Class Disciple_Tools_Critical_Path_Tab
 */
class Disciple_Tools_Metric_Reports_Tab extends Disciple_Tools_Abstract_Menu_Base
{
    private static $_instance = null;
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    } // End instance()

    public function __construct() {
        add_action( 'admin_menu', [ $this, 'add_submenu' ], 1 );
        add_action( 'dt_metrics_tab_menu', [ $this, 'add_tab' ], 1, 1 );
        add_action( 'dt_metrics_tab_content', [ $this, 'content' ], 99, 1 );

        parent::__construct();
    }

    public function add_submenu() {
        add_submenu_page( 'dt_metrics', __( 'Report List', 'disciple_tools' ), __( 'Report List', 'disciple_tools' ), 'manage_dt', 'dt_metrics', [ 'Disciple_Tools_Metrics_Menu', 'content' ] );
    }

    public function add_tab( $tab ) {
        echo '<a href="'. esc_url( admin_url() ).'admin.php?page=dt_metrics&tab=list" class="nav-tab ';
        if ( $tab == 'list' || !isset( $tab ) ) {
            echo 'nav-tab-active';
        }
        echo '">' . esc_html__( 'List' ) . '</a>';
    }

    public function content( $tab ) {
        if ( 'list' == $tab ) :
            $this->process_add();

            /* Right Column*/
            $this->template( 'begin' );

            $this->box( 'top', 'Reports' );
            $this->list_reports();
            $this->box( 'bottom' );


            /** Right Column */
            $this->template( 'right_column' );

            $this->box( 'top', 'Add New' );
            $this->add_new_box();
            $this->box( 'bottom' );

            /** End */
            $this->template( 'end' );

        endif;
    }

    public function process_add() {
        if ( isset( $_POST['dt_add_new_box_nonce'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_POST['dt_add_new_box_nonce'] ) ), 'dt_add_new_box'. get_current_user_id() ) ) {
            global $wpdb;

            if ( ! ( isset( $_POST['submit_date'] ) && isset( $_POST['source'] ) && isset( $_POST['year'] ) && isset( $_POST['section'] ) && isset( $_POST['total'] ) ) ) {
                return;
            }

            // parse the post submission
            $submitted_records = array_map( 'sanitize_text_field', wp_unslash( $_POST ) );

            // Insert record
            $record_success = $wpdb->insert(
                $wpdb->dt_reports,
                array(
                    'post_id' => 0,
                    'type' => $submitted_records['source'],
                    'value' => 0,
                    'time_end' => strtotime( $submitted_records['submit_date'] ) ?? time(),
                    'timestamp' => strtotime( $submitted_records['submit_date'] ) ?? time()
                ),
                array(
                    '%d',
                '%d',
                '%s',
                '%d',
                '%d',
                '%d'
                )
            );

            if ( ! $record_success ) {
                echo '<div class="notice notice-error"><p>'. esc_attr( 'Unable to create report. Insert Failure' ) .'</p></div>';
                return;
            }

            $record_id = $wpdb->insert_id;
            $meta_result = [];
            unset( $submitted_records['dt_add_new_box_nonce'] );

            foreach ( $submitted_records as $key => $value ) {
                $meta_result[] = $wpdb->insert(
                    $wpdb->dt_reportmeta,
                    [
                                'report_id' => $record_id,
                                'meta_key' => $key,
                                'meta_value' => $value,
                        ],
                    [
                                '%d',
                                '%s',
                                '%s',
                        ]
                );
            }

            // return success admin notice
            echo '<div class="notice notice-success"><p>'. esc_attr( 'Successfully recorded' ) .'</p></div>';
        }
    }

    public function add_new_box() {
        ?>
        <a class="button-primary button" href="?page=dt_metrics&tab=new">Create New Report</a>
        <?php
    }


    public function list_reports() {

        global $wpdb;
        $sources = get_option( 'dt_critical_path_sources', [] );

        $results = $wpdb->get_results("
            SELECT * FROM $wpdb->dt_reports report
            JOIN $wpdb->dt_reportmeta rm ON ( rm.report_id = report.id )
            WHERE report.type = 'monthly_report'
            GROUP BY report.id, rm.meta_key
            ORDER BY report.time_end DESC
        ", ARRAY_A );
        $reports = [];
        foreach ( $results as $result ){
            if ( !isset( $reports[ $result["id"] ] ) ) {
                $reports[ $result["id"] ] = [
                    "report_date" => gmdate( 'F Y', $result["time_end"] ),
                    "report_values" => []
                ];
            }
            $reports[ $result["id"] ]["values"][ $result["meta_key"] ] = $result["meta_value"];
        }

        ?>
        <form method="post">


         <table class="widefat striped">
            <thead>
                <tr>
                    <th style="width:20%;">Date</th>
                    <?php foreach ( $sources as $source ): ?>
                    <th><?php echo esc_html( $source["label"] ) ?></th>
                    <?php endforeach;?>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ( $reports as $report_id => $report ) : ?>
                <tr>
                    <td>
                        <?php echo esc_html( $report["report_date"] ) ?>
                    </td>
                    <?php foreach ( $sources as $source ): ?>
                    <td><?php echo esc_html( isset( $report["values"][ $source["key"] ] ) ? $report["values"][ $source["key"] ] : '' ) ?></td>
                    <?php endforeach;?>
                    <td><a class="button button-secondary" href="?page=dt_metrics&tab=edit&report_id=<?php echo esc_html( $report_id ) ?>">Edit</a> </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
            </table>
        </form>
        <?php
    }
}
Disciple_Tools_Metric_Reports_Tab::instance();
