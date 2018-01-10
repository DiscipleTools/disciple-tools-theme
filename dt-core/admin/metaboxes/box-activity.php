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
function dt_activity_metabox()
{
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
    public function __construct()
    {

    } // End __construct()

    /**
     * Gets an array of activities for a contact record
     *
     * @param        $id
     * @param string $order
     *
     * @return array|null|object
     * @throws \Error Order argument expected to be ASC or DESC.
     */
    public function activity_list_for_id( $id, $order = 'DESC' )
    {
        global $wpdb;

        if ( strtolower( $order ) != "desc" && strtolower( $order ) != "asc" ) {
            throw new Error( "Order argument expected to be ASC or DESC" );
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
                ORDER BY "
                // @codingStandardsIgnoreLine
                . " `hist_time` $order
                ;",
                $id
            ), ARRAY_A
        );

        // Return activity array from contact id
        return $list;
    }

    /**
     * Echos the list contents of the activity metabox
     *
     * @param $id
     */
    public function activity_meta_box( $id )
    {
        $list = $this->activity_list_for_id( $id );

        ?>
        <table class="widefat striped" width="100%">
            <tr>
                <th>Name</th>
                <th>Action</th>
                <th>Note</th>
                <th>Date</th>
            </tr>

            <?php foreach ( $list as $item ): ?>
                <?php $user = get_user_by( 'id', $item['user_id'] ); ?>

                <tr>

                    <td><?php echo esc_html( $user->display_name ); ?></td>
                    <td><?php echo esc_html( strip_tags( $item['action'] ) ); ?></td>
                    <td><?php echo esc_html( strip_tags( $item['object_note'] ) ); ?></td>
                    <td><?php echo esc_html( date( 'm/d/Y h:i:s', $item['hist_time'] ) ); ?></td>

                </tr>
            <?php endforeach; ?>
        </table>
        <?php
    }
}
