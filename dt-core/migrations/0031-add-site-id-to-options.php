<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

/**
 * find and the missed seeker path activity and create records for them.
 *
 */
class Disciple_Tools_Migration_0031 extends Disciple_Tools_Migration
{
    public function up() {
        $token = 'dt_site_id';
        $site_id = get_option( $token );
        if ( $site_id ) {
            return;
        }

        $dt_site_partner_profile = get_option( 'dt_site_partner_profile' );
        if ( $dt_site_partner_profile ) {
            $site_id = $dt_site_partner_profile['partner_id'] ?? false;
        }

        if ( ! $site_id ) {
            $site_id = hash( 'SHA256', site_url() . time() );
        }

        add_option( $token, $site_id );
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
