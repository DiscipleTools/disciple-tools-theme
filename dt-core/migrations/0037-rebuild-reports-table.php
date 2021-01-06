<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

/**
 * Class Disciple_Tools_Migration_0037
 */
class Disciple_Tools_Migration_0037 extends Disciple_Tools_Migration {

    /**
     * @throws \Exception  Got error when creating table $name.
     */
    public function up() {
        global $wpdb;
        $old_table_data = [];
        $renamed_table = $wpdb->prefix . 'dt_reports_old';

        // get old report data
        if ( ! isset( $wpdb->dt_reports ) ){
            $wpdb->dt_reports = $wpdb->prefix . 'dt_reports';
        }
        $column_exists = $wpdb->query( $wpdb->prepare("
            SELECT DISTINCT COUNT( COLUMN_NAME )
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = %s
            AND TABLE_NAME = '{$wpdb->dt_reports}'
            AND COLUMN_NAME = %s;
        ", DB_NAME, 'report_source' ));

        if ( $column_exists == 1 ){
            $old_table_data = $wpdb->get_results( "SELECT id, report_source, report_date FROM $wpdb->dt_reports", ARRAY_A );
        }

        $wpdb->query( "DROP TABLE IF EXISTS $wpdb->dt_reports" );

        // create new reports table
        $expected_tables = $this->get_expected_tables();
        foreach ( $expected_tables as $name => $table) {
            $rv = $wpdb->query( $table ); // WPCS: unprepared SQL OK
            if ( $rv == false ) {
                throw new Exception( "Got error when creating table $name: $wpdb->last_error" );
            }
        }

        // install old data
        if ( ! empty( $old_table_data ) ) {
            foreach ( $old_table_data as $row ){
                $wpdb->insert( $wpdb->dt_reports,
                    [
                        'id' => $row['id'],
                        'post_id' => 0,
                        'type' => $row['report_source'],
                        'value' => 0,
                        'time_end' => strtotime( $row['report_date'] ),
                        'timestamp' => strtotime( $row['report_date'] )
                    ],
                    [
                        '%d',
                    '%d',
                    '%s',
                    '%d',
                    '%d',
                    '%d'
                    ]
                );

            }
        }

    }

    /**
     * @throws \Exception  Got error when dropping table $name.
     */
    public function down() {

    }

    /**
     * @return array
     */
    public function get_expected_tables(): array {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        return array(
            "{$wpdb->prefix}dt_reports" =>
                "CREATE TABLE `{$wpdb->prefix}dt_reports` (
                      `id` bigint(22) unsigned NOT NULL AUTO_INCREMENT,
                      `hash` varchar(65) DEFAULT NULL,
                      `post_id` bigint(22) NOT NULL,
                      `type` varchar(100) NOT NULL,
                      `subtype` varchar(100) DEFAULT NULL,
                      `payload` longtext,
                      `value` int(11) NOT NULL DEFAULT '0',
                      `lng` float DEFAULT NULL,
                      `lat` float DEFAULT NULL,
                      `level` varchar(100) DEFAULT NULL,
                      `label` varchar(255) DEFAULT NULL,
                      `grid_id` bigint(22) DEFAULT NULL,
                      `time_begin` int(11) DEFAULT NULL,
                      `time_end` int(11) DEFAULT NULL,
                      `timestamp` int(11) NOT NULL,
                      PRIMARY KEY (`id`),
                      KEY `post_id` (`post_id`),
                      KEY `type` (`type`),
                      KEY `subtype` (`subtype`),
                      KEY `lng` (`lng`),
                      KEY `lat` (`lat`),
                      KEY `level` (`level`),
                      KEY `grid_id` (`grid_id`),
                      KEY `time_begin` (`time_begin`),
                      KEY `time_end` (`time_end`),
                      KEY `timestamp` (`timestamp`)
            ) $charset_collate;",
        );
    }

    /**
     * Test function
     */
    public function test() {
        $this->test_expected_tables();
    }

}
