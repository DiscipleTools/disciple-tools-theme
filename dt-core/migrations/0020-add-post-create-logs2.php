<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

/**
 * Class Disciple_Tools_Migration_0020
 *
 * @note    We were not previously tracking post creation in the dt activity log, but this is no a key piece of data
 *          to build metrics on "pace" from. So this migration rebuilds that data from the post_date field. This might
 *          not be 100% accurate, but should give a good creation date for most contacts and groups.
 *
 *          This also includes an upgrade to a previous release which did not have the relative prefix included with
 *          the database name.
 */
class Disciple_Tools_Migration_0020 extends Disciple_Tools_Migration
{
    public function up() {
        global $wpdb;
        // get get posts
        $object_id_index_exists = $wpdb->query( $wpdb->prepare("
                select distinct index_name
                from information_schema.statistics
                where table_schema = %s
                and table_name = '$wpdb->dt_activity_log'
                and index_name like %s
            ", DB_NAME, 'object_id_index' ));
        if ( $object_id_index_exists === 0 ){
            $wpdb->query( "ALTER TABLE $wpdb->dt_activity_log ADD INDEX object_id_index (object_id)" );
        }
        $posts = $wpdb->get_results( "
            SELECT ID, post_title, post_date, post_type, log.object_id, log.hist_time
            FROM $wpdb->posts post
            LEFT JOIN $wpdb->dt_activity_log log ON ( log.action = 'created' AND post.ID = log.object_id )
            WHERE ( post_type = 'contacts' OR post_type = 'groups' )
            AND log.object_id IS NULL
            ",
            ARRAY_A
        );
        $query = "INSERT INTO $wpdb->dt_activity_log ( action, object_type, object_name, object_id, user_caps, hist_time ) VALUES ";

        // create activity log
        if ( ! empty( $posts ) ) {
            foreach ( $posts as $item ) {

                $hist_time = strtotime( $item['post_date'] );
                if ( ! $hist_time ) {
                    $hist_time = time();
                }
                $query .= " ( 'created', '" . esc_sql( $item['post_type'] ). "', '" . esc_sql( $item['post_title'] ) . "', '" . esc_sql( $item['ID'] ) . "', 'administrator', '" . esc_sql( $hist_time ) . "' ), ";

            }
            $query .= ';';
            $query = str_replace( ", ;", ";", $query ); //remove last comma

            $wpdb->query( $query );  //phpcs:ignore
        }
    }

    public function down() {
        return;
    }

    public function test() {
    }

    public function get_expected_tables(): array
    {
        return [];
    }
}
