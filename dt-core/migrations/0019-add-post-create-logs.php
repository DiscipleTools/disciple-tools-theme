<?php

/**
 * Class Disciple_Tools_Migration_0018
 *
 * @note    We were not previously tracking post creation in the dt activity log, but this is no a key piece of data
 *          to build metrics on "pace" from. So this migration rebuilds that data from the post_date field. This might
 *          not be 100% accurate, but should give a good creation date for most contacts and groups.
 */
class Disciple_Tools_Migration_0019 extends Disciple_Tools_Migration
{
    public function up()
    {
        global $wpdb;
        // get get posts
        $posts = $wpdb->get_results( "SELECT ID, post_title, post_date, post_type FROM $wpdb->posts WHERE post_type = 'contacts' OR post_type = 'groups'", ARRAY_A );
        $create_records = $wpdb->get_results( "SELECT object_id as ID FROM wp_dt_activity_log WHERE action = 'created' and ( object_type = 'contacts' OR object_type = 'groups' )", ARRAY_A );

        // create activity log
        if ( ! empty( $posts ) ) {
            foreach ( $posts as $item ) {
                if ( array_search( $item[ 'ID' ], $create_records ) ) {
                    continue;
                }

                $hist_time = strtotime( $item[ 'post_date' ] );
                if ( ! $hist_time ) {
                    $hist_time = time();
                }

                $wpdb->insert(
                    $wpdb->dt_activity_log,
                    [
                        'action'         => 'created',
                        'object_type'    => $item[ 'post_type' ],
                        'object_subtype' => '',
                        'object_name'    => $item[ 'post_title' ],
                        'object_id'      => $item[ 'ID' ],
                        'user_id'        => 0,
                        'user_caps'      => 'administrator',
                        'hist_ip'        => '0',
                        'hist_time'      => $hist_time,
                        'object_note'    => ' ',
                        'meta_id'        => ' ',
                        'meta_key'       => ' ',
                        'meta_value'     => ' ',
                        'meta_parent'    => ' ',
                        'old_value'      => ' ',
                        'field_type'     => ' ',
                    ],
                    [ '%s', '%s', '%s', '%s', '%d', '%d', '%s', '%s', '%d', '%s', '%d', '%s', '%s', '%d' ]
                );
            }
        }
    }

    public function down()
    {
        return;
    }

    public function test()
    {
    }

    public function get_expected_tables(): array
    {
        return [];
    }
}