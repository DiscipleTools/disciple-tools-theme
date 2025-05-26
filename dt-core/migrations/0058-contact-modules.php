<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

/**
 * Class Disciple_Tools_Migration_0058
 *
 * Split dmm module into more modules
 */
class Disciple_Tools_Migration_0058 extends Disciple_Tools_Migration {
    public function up() {
        //skip this migration on a new install
        if ( dt_get_initial_install_meta( 'migration_number' ) >= 58 ){
            return;
        }

        $module_options = get_option( 'dt_post_type_modules', [] );
        if ( isset( $module_options['dmm_module']['enabled'] ) && $module_options['dmm_module']['enabled'] === false ){
            $module_options['contacts_baptisms_module'] = [ 'enabled' => false ];
            $module_options['contacts_coaching_module'] = [ 'enabled' => false ];
            $module_options['contacts_faith_module']    = [ 'enabled' => false ];
            update_option( 'dt_post_type_modules', $module_options );
        }
    }

    public function down() {
    }

    public function test() {
    }

    public function get_expected_tables(): array {
        return [];
    }
}
