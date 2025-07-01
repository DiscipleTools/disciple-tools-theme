<?php
if ( !defined( 'ABSPATH' ) ){
    exit;
} // Exit if accessed directly

/**
 * Class Disciple_Tools_Migration_0049
 *
 * Adds index to activity table
 */
class Disciple_Tools_Migration_0050 extends Disciple_Tools_Migration{
    public function up(){
        $custom_roles = get_option( 'dt_custom_roles', [] );

        // Terminate if no data has been detected.
        if ( empty( $custom_roles ) ){
            return;
        }

        // Start reshaping of legacy custom roles.
        $updated_custom_roles = [];
        foreach ( $custom_roles as $role => $role_settings ){
            $updated_custom_roles[$role] = $role_settings;

            // Reshape nested capabilities; which is the main point of focus.
            if ( isset( $updated_custom_roles[$role]['capabilities'] ) && is_array( $updated_custom_roles[$role]['capabilities'] ) ){
                $capabilities = $updated_custom_roles[$role]['capabilities'];

                // Determine if array is to be reshaped; based on key data types.
                $is_legacy_shape = false;
                $capability_keys = array_keys( $capabilities );
                foreach ( $capability_keys as $key ){
                    $is_legacy_shape = is_numeric( $key );
                }

                // If legacy, then reshape; setting all identified capabilities to true.
                if ( $is_legacy_shape ){
                    $updated_capabilities = [];
                    foreach ( $capabilities ?? [] as $capability ){
                        $updated_capabilities[$capability] = true;
                    }

                    $updated_custom_roles[$role]['capabilities'] = $updated_capabilities;
                }
            }
        }

        // Persist updated custom roles.
        if ( !empty( $updated_custom_roles ) ){
            update_option( 'dt_custom_roles', $updated_custom_roles );
        }
    }

    public function down(){
    }

    /**
     * @throws \Exception ...
     */
    public function test(){

        // Must not be able to identify any legacy shaped capability arrays.
        $custom_roles = get_option( 'dt_custom_roles', [] );

        // Terminate if no data has been detected.
        if ( empty( $custom_roles ) ){
            return;
        }

        // Start search of legacy custom roles and throw exception if any detected.
        foreach ( $custom_roles as $role => $role_settings ){
            if ( isset( $role_settings['capabilities'] ) && is_array( $role_settings['capabilities'] ) ){
                $capability_keys = array_keys( $role_settings['capabilities'] );
                foreach ( $capability_keys as $key ){
                    if ( is_numeric( $key ) ){
                        throw new Exception( 'Legacy capability array detected.' );
                    }
                }
            }
        }
    }

    public function get_expected_tables(): array{
        return [];
    }
}
