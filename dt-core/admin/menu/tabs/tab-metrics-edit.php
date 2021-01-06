<?php

if ( !defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class Disciple_Tools_Metric_Edit_Tab
 */
class Disciple_Tools_Metric_Edit_Tab extends Disciple_Tools_Abstract_Menu_Base
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
        add_action( 'admin_menu', [ $this, 'add_submenu' ], 90 );
        add_action( 'init', [ $this, 'process_data' ] );
        add_action( 'dt_metrics_tab_content', [ $this, 'content' ], 99, 1 );
        parent::__construct();
    } // End __construct()

    public function add_submenu() {
        add_submenu_page( 'dt_metrics', __( 'Create New', 'disciple_tools' ), __( 'Create New', 'disciple_tools' ), 'manage_dt', 'dt_metrics&tab=new', [ 'Disciple_Tools_Metrics_Menu', 'content' ] );
    }

    public function add_tab( $tab ) {
        echo '<a href="'. esc_url( admin_url() ).'admin.php?page=dt_metrics&tab=new" class="nav-tab ';
        if ( $tab == 'sources' ) {
            echo 'nav-tab-active';
        }
        echo '">'. esc_attr__( 'Create Report', 'disciple_tools' ) .'</a>';
    }

    public function process_data(){
        if ( !empty( $_POST ) ){
            if ( isset( $_POST['report_edit_nonce'] ) && wp_verify_nonce( sanitize_key( $_POST['report_edit_nonce'] ), 'report_edit' ) && isset( $_POST["report"] ) && ! empty( $_POST["report"] ) ) {
                dt_write_log( $_POST );
                $post_report = dt_recursive_sanitize_array( $_POST["report"] );

                if ( isset( $_POST["create_report"], $_POST["report"]["year"], $_POST["report"]["month"] ) ){
                    $year = sanitize_key( wp_unslash( $_POST["report"]["year"] ) );
                    $month = sanitize_key( wp_unslash( $_POST["report"]["month"] ) );
                    $report = [
                        "post_id" => 0,
                        "type" => "monthly_report",
                        "payload" => null,
                        "time_end" => strtotime( $year . '-' . $month . '-01' ),
                        "timestamp" => time(),
                        'meta_input' => []
                    ];

                    foreach ( $post_report as $key => $value ){
                        $key = sanitize_text_field( wp_unslash( $key ) );
                        $value = sanitize_text_field( wp_unslash( $value ) );
                        $report["meta_input"][ $key] = $value;
                    }

                    $new_id = Disciple_Tools_Reports::insert( $report );
                    if ( ! empty( $new_id ) ) {
                        wp_redirect( '?page=dt_metrics&tab=edit&report_id='.$new_id );
                    }
                } elseif ( isset( $_POST["update_report"] ) ) {
                    $id = isset( $_GET["report_id"] ) ? sanitize_key( wp_unslash( $_GET["report_id"] ) ) : null;
                    if ( ! empty( $id ) ) {
                        global $wpdb;
                        $current_meta_raw = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $wpdb->dt_reportmeta WHERE report_id = %s", $id ), ARRAY_A );
                        $current_meta = [];
                        foreach ( $current_meta_raw as $value ){
                            $current_meta[$value['meta_key']] = $value;
                        }

                        $post_report = dt_recursive_sanitize_array( $_POST["report"] );
                        foreach ( $post_report as $key => $value ){
                            $key = sanitize_text_field( wp_unslash( $key ) );
                            $value = sanitize_text_field( wp_unslash( $value ) );

                            if ( isset( $current_meta[ $key ] ) ) {
                                Disciple_Tools_Reports::update_meta( $id, $key, $value );
                            } else {
                                Disciple_Tools_Reports::add_meta( $id, $key, $value );
                            }
                        }
                    }
                } elseif ( isset( $_POST["delete_report"] ) ) {
                    $id = isset( $_GET["report_id"] ) ? sanitize_key( wp_unslash( $_GET["report_id"] ) ) : null;
                    Disciple_Tools_Reports::delete( $id );
                    wp_redirect( '?page=dt_metrics&tab=list' );
                }
            }
        }
    }

    public function content( $tab ) {
        if ( 'edit' == $tab ) {
            self::template( 'begin' );

            $this->table( "edit" );

            self::template( 'right_column' );
            self::template( 'end' );
        }
        if ( 'new' == $tab ) {
            self::template( 'begin' );

            $this->table( $tab );

            self::template( 'right_column' );

            self::template( 'end' );
        }
    }

    public function table( $tab ) {
        $sources = get_option( 'dt_critical_path_sources', [] );
        $report = [
            "year" => '',
            "month" => ''
        ];

        if ( $tab == "new" ){
            $this->box( 'top', 'Create new Report' );
        } else {
            $this->box( 'top', 'Edit' );
            $id = isset( $_GET["report_id"] ) ? sanitize_key( wp_unslash( $_GET["report_id"] ) ) : null;
            $result = Disciple_Tools_Reports::get( $id, 'id_and_meta' );
            $report["year"] = gmdate( 'Y', $result["time_end"] );
            $report["month"] = gmdate( 'm', $result["time_end"] );

            foreach ( $result['meta_input'] as $meta ){
                $report[$meta["meta_key"]] = $meta["meta_value"];
            }
        }
        ?>

        <form method="POST" action="">
            <?php if ( $tab === 'edit' ) : ?>
            <p >
                <button type="submit" style="float:right; margin: 10px;" name="delete_report" class="button button-secondary">DELETE Report</button>
            </p>
            <?php else : ?>
                <p>Reports are tracked monthly. Select the year and month of your report and fill out as many fields as you can. You can come back later to update the fields if you need to.</p>
            <?php endif; ?>
            <?php wp_nonce_field( 'report_edit', 'report_edit_nonce' ); ?>
            <table class="widefat striped">
                <thead>
                <tr>
                    <th></th>
                    <th></th>
                    <th>Description</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>Year</td>
                    <td>
                        <?php if ( empty( $report["year"] ) ) : ?>
                        <select name="report[year]" id="year">
                            <?php
                            $current_year = (int) gmdate( 'Y' );
                            $number_of_years = 20;
                            for ( $i = 0; $number_of_years >= $i; $i++ ): ?>
                                <option <?php echo esc_html( (int) $report["year"] == $current_year ? 'selected' : '' ) ?>>
                                    <?php echo esc_attr( $current_year )?>
                                </option>
                                <?php $current_year--;
                            endfor;
                            ?>
                        </select>
                        <?php else :
                            echo esc_html( $report["year"] );
                        endif; ?>
                    </td>
                </tr>
                <tr>
                    <td>Month</td>
                    <td>
                        <?php if ( empty( $report["month"] ) ) : ?>
                        <select name="report[month]" id="month">
                            <?php
                            $number_of_months = 12;
                            for ( $i = 1; $number_of_months >= $i; $i++ ) : ?>
                                <option value="<?php echo esc_html( $i ) ?>" <?php echo esc_html( (int) $report["month"] == $i ? 'selected' : '' ) ?>>
                                    <?php echo esc_attr( DateTime::createFromFormat( '!m', $i )->format( 'F' ) ) ?>
                                </option>
                            <?php endfor;?>
                        </select>
                        <?php else :
                            echo esc_attr( DateTime::createFromFormat( '!m', $report["month"] )->format( 'F' ) );
                        endif; ?>
                    </td>
                </tr>
                <?php foreach ( $sources as $source ) : ?>
                <tr>
                    <td><?php echo esc_html( $source["label"] ) ?></td>
                    <td>
                        <input name="report[<?php echo esc_html( $source["key"] ) ?>]"
                               value="<?php echo esc_html( isset( $report[$source["key"] ] ) ? $report[$source["key"] ] : '' ) ?>">
                    </td>
                    <td>
                        <?php echo esc_html( $source["description"] ?? '' ) ?>
                    </td>
                </tr>
                <?php endforeach;?>
                </tbody>

            </table>
            <p style="margin-top: 10px">

            <?php if ( $tab === 'new' ) : ?>
                <button type="submit" name="create_report" value="true" class="button button-primary">Create Report</button>
            <?php else : ?>
                <button type="submit" name="update_report" value="true" class="button button-primary">Update Report</button>
            <?php endif; ?>
            </p>
        </form>

        <?php
        $this->box( 'bottom' );
    }

}
Disciple_Tools_Metric_Edit_Tab::instance();

