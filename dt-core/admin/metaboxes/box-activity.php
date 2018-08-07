<?php
/**
 * Disciple_Tools_Metabox_Activity
 *
 * @class   Disciple_Tools_Metabox_Activity
 * @version 0.1.0
 * @since   0.1.0
 * @package Disciple_Tools
 *
 */

if ( !defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * @return \Disciple_Tools_Metabox_Activity
 */
function dt_activity_metabox() {
    $object = new Disciple_Tools_Metabox_Activity();

    return $object;
}

/**
 * Class Disciple_Tools_Metabox_Activity
 */
class Disciple_Tools_Metabox_Activity
{

    /**
     * Constructor function.
     *
     * @access public
     * @since  0.1.0
     */
    public function __construct() {

    } // End __construct()

    /**
     * Gets an array of activities for a contact record
     *
     * @param        $id
     * @param string $order
     *
     * @return array|null|object|\WP_Error
     */
    public function activity_list_for_id( $id, $order = 'DESC' ) {
        global $wpdb;

        if ( strtolower( $order ) != "desc" && strtolower( $order ) != "asc" ) {
            return new WP_Error( 'bad_argument_supplied', "Order argument expected to be ASC or DESC" );
        }

        // Query activity with the contact id
        $list = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT
                    *
                FROM
                    `$wpdb->dt_activity_log`
                WHERE
                    `object_id` = %s
                    AND `object_id` != ''
                    AND `object_id` IS NOT NULL
                ORDER BY "
                // phpcs:ignore WordPress.WP.PreparedSQL.NotPrepared
                . sanitize_sql_orderby( "`hist_time` $order" )
                . ";",
                $id
            ), ARRAY_A
        );

        // Return activity array from contact id
        return $list;
    }

    /**
     * Echos the list contents of the activity metabox
     * @param $id
     *
     * @return \WP_Error
     */
    public function activity_meta_box( $id ) {
        $list = $this->activity_list_for_id( $id );
        if ( is_wp_error( $list ) ) {
            echo 'List not available';
            return new WP_Error( 'bad_argument_supplied', "Order argument expected to be ASC or DESC" );
        }

        ?>
        <table class="widefat striped" width="100%">
            <tr>
                <th>Name</th>
                <th>Action</th>
                <th>Note</th>
                <th>Date</th>
            </tr>

            <?php foreach ( $list as $item ): ?>
                <?php
                    $user = get_user_by( 'id', $item['user_id'] );
                if ( $user ) {
                    $user_name = $user->display_name;
                } else {
                    $user_name = __( 'unknown', 'dt_webform' );
                }
                ?>

                <tr>

                    <td><?php echo esc_html( $user_name ); ?></td>
                    <td><?php echo esc_html( strip_tags( $item['action'] ) ); ?></td>
                    <td style="-ms-word-break: break-all; word-break: break-all; word-break: break-word; -webkit-hyphens: none; -moz-hyphens: none; -ms-hyphens: none; hyphens: none;"><?php echo esc_html( strip_tags( $item['object_note'] ) ); ?></td>
                    <td><?php echo esc_html( date( 'm/d/Y h:i:s', $item['hist_time'] ) ); ?></td>

                </tr>
            <?php endforeach; ?>
        </table>
        <?php
    }
}
