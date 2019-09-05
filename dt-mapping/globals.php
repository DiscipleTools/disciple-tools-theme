<?php
if ( ! function_exists( 'dt_write_log' ) ) {
    // @note Included here because the module can be used independently
    function dt_write_log( $log ) {
        if ( true === WP_DEBUG ) {
            global $dt_write_log_microtime;
            $now = microtime( true );
            if ( $dt_write_log_microtime > 0 ) {
                $elapsed_log = sprintf( "[elapsed:%5dms]", ( $now - $dt_write_log_microtime ) * 1000 );
            } else {
                $elapsed_log = "[elapsed:-------]";
            }
            $dt_write_log_microtime = $now;
            if ( is_array( $log ) || is_object( $log ) ) {
                error_log( $elapsed_log . " " . print_r( $log, true ) );
            } else {
                error_log( "$elapsed_log $log" );
            }
        }
    }
}
if ( ! function_exists( 'dt_get_theme_data_url' ) ) {
    function dt_get_theme_data_url() {
        /**
         * This is the modifiable url for downloading the location_grid and people groups source files for the DT system.
         * The filter can be used to override the default GitHub location and move this to a custom mirror or fork.
         * @return string
         */
        return apply_filters( 'disciple_tools_theme_data_url', 'https://raw.githubusercontent.com/DiscipleTools/disciple-tools-theme-data/master/' );
    }
}

/** Add required DT Level */
if ( ! isset( $dt_mapping['required_dt_theme_version'] ) ) {
    $dt_mapping['required_dt_theme_version'] = '0.22.0';
}

/** Add path */
if ( ! isset( $dt_mapping['path'] ) ) {
    switch ( $dt_mapping['environment'] ) {
        case 'plugin':
            $dt_mapping['path'] = trailingslashit( plugin_dir_path( __FILE__ ) );
            break;
        case 'theme':
        case 'disciple_tools':
        default:
            $dt_mapping['path'] = trailingslashit( get_template_directory() ) . 'dt-mapping/';
            break;
    }
}


/** Add url */
if ( ! isset( $dt_mapping['url'] ) ) {
    switch ( $dt_mapping['environment'] ) {
        case 'plugin':
            $dt_mapping['url'] = trailingslashit( plugin_dir_url(__FILE__) );
            break;
        case 'theme':
        case 'disciple_tools':
        default:
            $dt_mapping['url'] = trailingslashit( get_stylesheet_directory_uri()) . 'dt-mapping/';
            break;
    }
}

/** Add location api url */
if ( ! isset( $dt_mapping['location_api_url'] ) ) {
    $dt_mapping['location_api_url'] = $dt_mapping['url'] . 'location-grid-list-api.php';
}

/** Add drill-down.js url */
if ( ! isset( $dt_mapping['drill_down_js_url'] ) ) {
    $dt_mapping['drill_down_js_url'] = $dt_mapping['url'] . 'drill-down.js';
}
if ( ! isset( $dt_mapping['drill_down_js_version'] ) ) {
    $dt_mapping['drill_down_js_version'] = 1.2;
}

/** Add mapping.js url */
if ( ! isset( $dt_mapping['mapping_js_url'] ) ) {
    $dt_mapping['mapping_js_url'] = $dt_mapping['url'] . 'mapping.js';
}
if ( ! isset( $dt_mapping['mapping_js_version'] ) ) {
    $dt_mapping['mapping_js_version'] = 0.1;
}

/** Add configuration module */
if ( ! isset( $dt_mapping['module_config_path'] ) ) {
    $dt_mapping['module_config_path'] = apply_filters( 'mapping_module_config_path', 'mapping-module-config.php' );
}

/** Add dt options */
if ( ! isset( $dt_mapping['options'] ) ) {
    $all_options = wp_load_alloptions();
    $dt_mapping['options'] = [];
    foreach( $all_options as $key => $value ) {
        if ( substr( $key, 0, 3 ) === 'dt_' ) {
            $dt_mapping['options'][$key] = $value;
        }
    }
}

/** Add github theme data url */
if ( ! isset( $dt_mapping['disciple_tools_theme_data_url'] ) ) {
    $dt_mapping['disciple_tools_theme_data_url'] = dt_get_theme_data_url();
}

/** Add spinner */
if ( ! isset( $dt_mapping['spinner'] ) ) {
    $dt_mapping['spinner'] = $dt_mapping['url'] . 'spinner.svg';
}

/** Add theme info */
if ( ! isset( $dt_mapping['theme'] ) ) {
    $wp_theme = wp_get_theme();
    $dt_mapping['theme'] = [
        'current_theme_name' => $wp_theme->name,
        'current_theme_version' => $wp_theme->version,
    ];
}

/** Add globals to global object */
$GLOBALS['dt_mapping'] = $dt_mapping;

/** Add location grid database name */
global $wpdb;
$wpdb->dt_location_grid = $wpdb->prefix .'dt_location_grid';

/*******************************************************************************************************************
 * MIGRATION ENGINE
 ******************************************************************************************************************/
require_once( 'class-migration-engine.php' );
try {
    DT_Mapping_Module_Migration_Engine::migrate( DT_Mapping_Module_Migration_Engine::$migration_number );
} catch ( Throwable $e ) {
    $migration_error = new WP_Error( 'migration_error', 'Migration engine for mapping module failed to migrate.', [ 'error' => $e ] );
    dt_write_log( $migration_error );
}
/*******************************************************************************************************************/

if ( ! function_exists( 'dt_mapping_path' ) ) {
    function dt_mapping_path( $echo = false ) {
        global $dt_mapping;
        if ( $echo ) {
            echo $dt_mapping['path'];
        }
        return $dt_mapping['path'];
    }
}
