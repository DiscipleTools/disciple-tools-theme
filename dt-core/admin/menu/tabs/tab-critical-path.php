<?php

/**
 * Disciple Tools
 *
 * @class      Disciple_Tools_Critical_Path_Tab
 * @version    0.1.0
 * @since      0.1.0
 * @package    Disciple_Tools
 * @author     Chasm.Solutions & Kingdom.Training
 */

if ( !defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class Disciple_Tools_Critical_Path_Tab
 */
class Disciple_Tools_Critical_Path_Tab extends Disciple_Tools_Abstract_Menu_Base
{
    private static $_instance = null;
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    } // End instance()

    public function __construct() {
        add_action( 'admin_menu', [ $this, 'add_submenu' ], 99 );
        add_action( 'dt_settings_tab_menu', [ $this, 'add_tab' ], 99, 1 );
        add_action( 'dt_settings_tab_content', [ $this, 'content' ], 99, 1 );

        parent::__construct();
    }

    public function add_tab( $tab ) {
        echo '<a href="'. esc_url( admin_url() ).'admin.php?page=dt_options&tab=critical_path" class="nav-tab ';
        if ( $tab == 'critical_path' || !isset( $tab ) ) {
            echo 'nav-tab-active';
        }
        echo '">' . esc_html__( 'Critical Path' ) . '</a>';
    }

    public function add_submenu() {
        add_submenu_page( 'dt_options', __( 'Critical Path', 'disciple_tools' ), __( 'Critical Path', 'disciple_tools' ), 'manage_dt', 'dt_options&tab=critical_path', [ 'Disciple_Tools_Settings_Menu', 'content' ] );
    }

    public function content( $tab ) {
        if ( 'critical_path' == $tab ) :
            $this->process_critical_path_sources();
            $this->process_add();
            $this->process_delete();

            /* Right Column*/
            $this->template( 'begin' );

            $this->box( 'top', 'Manual Reports (before)' );
            $this->list_reports( 'before' );
            $this->box( 'bottom' );

            $this->box( 'top', 'Manual Reports (after)' );
            $this->list_reports( 'after' );
            $this->box( 'bottom' );

            /** Right Column */
            $this->template( 'right_column' );

            $this->box( 'top', 'Add New' );
            $this->add_new_box();
            $this->box( 'bottom' );

            $this->box( 'top', 'Sources' );
            $this->critical_path_sources_box();
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
                    'report_date' => $submitted_records['submit_date'] ?? current_time( 'mysql' ),
                    'report_source' => $submitted_records['source'],
                    'category' => 'manual'
                ),
                array(
                    '%s',
                    '%s',
                    '%s',
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

    public function process_delete() {
        if ( isset( $_POST['delete'] ) && isset( $_POST['dt_delete_button_nonce'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_POST['dt_delete_button_nonce'] ) ), 'dt_delete_button'. get_current_user_id() ) ) {
            global $wpdb;

            $submitted_post_id = sanitize_text_field( wp_unslash( $_POST['delete'] ) );
            $deleted_reports = $wpdb->delete( $wpdb->dt_reports, [ 'id' => $submitted_post_id ] );
            $deleted_reportmeta = $wpdb->delete( $wpdb->dt_reportmeta, [ 'report_id' => $submitted_post_id ] );

            if ( $deleted_reports && $deleted_reportmeta ) {
                echo '<div class="notice notice-success"><p>'. esc_attr( 'Successfully deleted' ) .'</p></div>';
            } else {
                echo '<div class="notice notice-error"><p>'. esc_attr( 'Unable to delete. Check error logs.' ) .'</p></div>';
                dt_write_log( $wpdb );
            }
        }
    }

    public function add_new_box() {
        $sources = get_option( 'dt_critical_path_sources', [] );
        ?>
        <form method="post">
            <?php wp_nonce_field( 'dt_add_new_box'. get_current_user_id(), 'dt_add_new_box_nonce', false, true ) ?>
            <input type="hidden" value="<?php echo esc_attr( current_time( 'mysql' ) ) ?>" name="submit_date" />
            <input type="hidden" value="<?php echo esc_attr( get_current_user_id() ) ?>" name="author" />
            <dl>
                <dt>
                    <p>
                    <label for="source"><?php esc_attr_e( 'Source' ) ?></label><br>
                    <select name="source" id="source" style="width:100%;">
                        <?php
                        foreach ( $sources as $source ) {
                            echo '<option value="'. esc_attr( $source['key'] ).'">'. esc_attr( $source['label'] ).'</option>';
                        }
                        ?>
                    </select>
                    </p>
                </dt>
                <dt>
                    <p>
                    <label for="year"><?php esc_attr_e( 'Year' ) ?></label><br>
                    <select name="year" id="year" style="width:100%;">
                        <?php
                        $current_year = (int) date( 'Y' );
                        $number_of_years = 20;
                        for ( $i = 0; $number_of_years >= $i; $i++ ) {
                            echo '<option>'. esc_attr( $current_year ).'</option>';
                            $current_year--;
                        }
                        ?>
                    </select>
                    </p>
                </dt>
                <dt>
                    <p>
                        <label for="section"><?php esc_attr_e( 'Section' ) ?></label><br>
                        <select name="section" id="section" style="width:100%;">
                            <option value="before"><?php esc_attr_e( 'Before' ) ?></option>
                            <option value="after"><?php esc_attr_e( 'After' ) ?></option>
                        </select>
                    </p>
                </dt>

                <dt>
                    <p>
                    <label for="total"><?php esc_attr_e( 'Total' ) ?></label><br>
                    <input name="total" type="number" id="total" value="" style="width:100%;" />
                    </p>
                </dt>
                <dt>
                    <p>
                    <button type="submit" class="button"><?php esc_attr_e( 'Add' ) ?></button>
                    </p>
                </dt>
            </dl>

        </form>
        <?php
    }

    /**
     * Set base user assigns the catch-all user
     */
    public function list_reports( $location ) {

        global $wpdb;
        $sources = get_option( 'dt_critical_path_sources', [] );

        $results = $wpdb->get_results("
            SELECT a.id,
              a.report_date,
              a.report_source,
              b.meta_value as submit_date,
              c.meta_value as author,
              d.meta_value as source,
              e.meta_value as year,
              f.meta_value as type,
              g.meta_value as section,
              h.meta_value as total
            FROM $wpdb->dt_reports as a
            LEFT JOIN $wpdb->dt_reportmeta as b
              ON b.report_id=a.id
              AND b.meta_key = 'submit_date'
            LEFT JOIN $wpdb->dt_reportmeta as c
              ON a.id=c.report_id
                 AND c.meta_key = 'author'
            LEFT JOIN $wpdb->dt_reportmeta as d
              ON a.id=d.report_id
                 AND d.meta_key = 'source'
            LEFT JOIN $wpdb->dt_reportmeta as e
              ON a.id=e.report_id
                 AND e.meta_key = 'year'
            LEFT JOIN $wpdb->dt_reportmeta as f
              ON a.id=f.report_id
                 AND f.meta_key = 'type'
            LEFT JOIN $wpdb->dt_reportmeta as g
              ON a.id=g.report_id
                 AND g.meta_key = 'section'
            LEFT JOIN $wpdb->dt_reportmeta as h
              ON a.id=h.report_id
                 AND h.meta_key = 'total'
            WHERE a.category = 'manual'
            AND a.id IN ( SELECT MAX( bb.report_id )
                FROM $wpdb->dt_reportmeta as bb
                  LEFT JOIN $wpdb->dt_reportmeta as d
                    ON bb.report_id=d.report_id
                       AND d.meta_key = 'source'
                  LEFT JOIN $wpdb->dt_reportmeta as e
                    ON bb.report_id=e.report_id
                       AND e.meta_key = 'year'
                WHERE bb.meta_key = 'submit_date'
                  GROUP BY d.meta_value, e.meta_value
             )
            GROUP BY a.report_source, e.meta_value
            ORDER BY a.report_source, e.meta_value DESC;
        ", ARRAY_A );

        ?>
        <form method="post">
        <?php wp_nonce_field( 'dt_delete_button'. get_current_user_id(), 'dt_delete_button_nonce', false, true ) ?>

         <table class="widefat striped">
            <thead>
                <tr>
                    <th style="width:20%;">Source</th>
                    <th style="width:20%;">Year</th>
                    <th style="width:20%;">Total</th>
                    <th style="width:20%;">Added</th>
                    <th style="width:30px;">Modify</th>
                </tr>
            </thead>
            <tbody>
            <?php
            foreach ( $sources as $source ) {
                foreach ( $results as $result ) {
                    if ( $location === $result['section'] && $result['source'] === $source['key'] ) :
                        ?>
                        <tr>
                            <td><?php echo esc_attr( $source['label'] ) ?></td>
                            <td><?php echo esc_attr( $result['year'] ) ?></td>
                            <td><?php echo esc_attr( number_format( $result['total'], 0, '.', ',' ) ) ?></td>
                            <td><span style="font-size:.8em;"><?php echo esc_attr( $result['submit_date'] ) ?></span></td>
                            <td><button type="submit" class="button" name="delete" value="<?php echo esc_attr( $result['id'] ) ?>"><?php esc_attr_e( 'Delete' ) ?></button></td>
                        </tr>
                        <?php
                    endif;
                }
            }

            ?>
            </tbody>
            </table>
        </form>
        <?php
    }

    public function sources_box() {
        esc_html_e( 'Sources can be edited on the custom lists tab. Disabled list items will show up in source selection as well.', "disciple_tools" );
    }

    /**
     * Prints the sources settings box.
     */
    public function critical_path_sources_box() {
        $site_custom_lists = get_option( 'dt_critical_path_sources', [] );
        ?>
        <form method="post" name="critical_path_sources_form" id="critical_path_sources_form">

            <?php wp_nonce_field( 'critical_path_sources'. get_current_user_id(), 'critical_path_sources_nonce', false, true ) ?>

            <table class="widefat">
                <thead><tr><td>Label</td><td>Order</td><td>Delete</td></tr></thead><tbody>
                <?php
                foreach ( $site_custom_lists as $index => $source ) {
                    echo '<tr>
                        <td>' . esc_attr( $source['label'] ) . '</td>
                        <td>' . esc_attr( $source['order'] ) . '</td>
                        <td><button type="submit" name="delete_field" value="' . esc_attr( $index ) . '" class="button small" >delete</button></td>
                      </tr>';
                }
                ?>
            </table>
            <br>
            <button type="button" onclick="jQuery('#add_source').toggle();" class="button">Add/Edit</button>
            <button type="submit" style="float:right;" class="button">Save</button>
            <div id="add_source" style="display:none;">
                <table width="100%">
                    <tr>
                        <td>
                            <hr>
                            <br>
                            <input type="text" name="add_input_field[label]" placeholder="label"/><br>
                            <input type="number" name="add_input_field[order]" placeholder="order"/><br>
                            <button type="submit">Add/Edit</button>
                        </td>
                    </tr>
                </table>
            </div>
        </form>
        <script>
            function add_form() {
                jQuery('#add_source').append(`
                `)
            }
        </script>
        <?php
    }

    public function process_critical_path_sources() {
        if ( isset( $_POST['critical_path_sources_nonce'] ) ) {
            $site_custom_lists = get_option( 'dt_critical_path_sources', [] );

            if ( !wp_verify_nonce( sanitize_key( $_POST['critical_path_sources_nonce'] ), 'critical_path_sources'. get_current_user_id() ) ) {
                return;
            }

            // Process a field to delete.
            if ( isset( $_POST['delete_field'] ) ) {
                $delete_key = sanitize_text_field( wp_unslash( $_POST['delete_field'] ) );
                unset( $site_custom_lists[ $delete_key ] );
            }
            // Process addition or update
            elseif ( isset( $_POST['add_input_field']['label'] ) ) {

                $label = sanitize_text_field( wp_unslash( $_POST['add_input_field']['label'] ) );
                $label_index = null;
                if ( empty( $label ) ) {
                    return;
                }

                if ( isset( $_POST['add_input_field']['order'] ) ) {
                    $order = sanitize_text_field( wp_unslash( $_POST['add_input_field']['order'] ) );
                }

                $key = sanitize_key( strtolower( str_replace( ' ', '_', $label ) ) );

                foreach ( $site_custom_lists as $index => $site_custom_list ) {
                    if ( $site_custom_list['label'] === $label ) {
                        $label_index = $index;
                    }
                }

                // strip and make lowercase process
                $site_custom_lists[$label_index] = [
                    'label'         => $label,
                    'key'           => $key,
                    'order'         => $order ?? 50,
                ];
            }

            // Sort new array
            usort($site_custom_lists, function( $a, $b) {
                return $a['order'] <=> $b['order'];
            });

            // Update the site option
            update_option( 'dt_critical_path_sources', $site_custom_lists, true );
        }
    }

}
Disciple_Tools_Critical_Path_Tab::instance();
