<?php
declare(strict_types=1);

if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

/**
 * Class Disciple_Tools_Migration_0042
 *
 * Adds Notifications Queue Table
 */
class Disciple_Tools_Migration_0047 extends Disciple_Tools_Migration {
    public function up() {
        global $wpdb;

        if ( ! isset( $wpdb->dt_roles ) ) {
            $wpdb->dt_roles = $wpdb->prefix . 'dt_roles';
        }

        $charset_collate = $wpdb->get_charset_collate();
        $rv = $wpdb->query(
            "CREATE TABLE IF NOT EXISTS $wpdb->dt_roles (
                `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                `role_description` LONGTEXT NOT NULL,
                `role_label` varchar(254) NOT NULL,
                `role_slug` varchar(60) NOT NULL UNIQUE,
                `role_capabilities` LONGTEXT NOT NULL,
                PRIMARY KEY (`id`)
            ) $charset_collate;"
        ); // WPCS: unprepared SQL OK
        if ( $rv == false ) {
            throw new Exception( "Got error when creating table $wpdb->dt_roles: $wpdb->last_error" );
        }
    }

    public function down() {
        global $wpdb;
        $rv = $wpdb->query( "DROP TABLE $wpdb->dt_roles" );
        if ( $rv == false ) {
            throw new Exception( "Got error when dropping table $wpdb->dt_roles: $wpdb->last_error" );
        }
    }

    public function test() {
    }

    public function get_expected_tables(): array {
        return [];
    }
}
