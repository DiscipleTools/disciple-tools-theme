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

/** Setup location grid database in $wpdb */


/** Create global */
if ( ! isset( $dt_mapping ) ) {
    /**
     * This global must be used through the mapping system so that he dt-mapping module continues to be an independent
     * module, able to be included in non-disciple tools themes.
     *
     * it can be included in functions like other globals:
     *      global $dt_mapping;
     */
    $dt_mapping = [];
}

/** Add versions */
if ( ! isset( $dt_mapping['version'] ) ) {
    /**
     * @version     1.0 First generation
     *              1.1 Converted to transportable module. Established global $dt_mapping;
     */
    $dt_mapping['version'] = 1.1;
}
if ( ! isset( $dt_mapping['required_dt_theme_version'] ) ) {
    $dt_mapping['required_dt_theme_version'] = '0.22.0';
}

/** Add disciple tools check */
if ( ! isset( $dt_mapping['is_disciple_tools'] )  ) {
    $wp_theme = wp_get_theme();
    if ( strpos( $wp_theme->get_template(), "disciple-tools-theme" ) !== false || $wp_theme->name === "Disciple Tools" ) {
        $dt_mapping['is_disciple_tools'] = 1;
    } else {
        $dt_mapping['is_disciple_tools'] = 0;
    }
}

/** Add path */
if ( ! isset( $dt_mapping['path'] ) ) {
    if ( $dt_mapping['is_disciple_tools'] ) {
        $dt_mapping['path'] = trailingslashit( get_template_directory() ) . 'dt-mapping/';
    } else {
        $dt_mapping['path'] = trailingslashit( plugin_dir_path( __DIR__ ) );
    }
}

/** Add url */
if ( ! isset( $dt_mapping['url'] ) ) {
    if ( $dt_mapping['is_disciple_tools'] ) {
        $dt_mapping['url'] = trailingslashit(get_stylesheet_directory_uri() ) . 'dt-mapping/' ;
    } else {
        $dt_mapping['url'] = trailingslashit( plugin_dir_url(__FILE__) );
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

/**
 * This is the modifiable url for downloading the location_grid and people groups source files for the DT system.
 * The filter can be used to override the default GitHub location and move this to a custom mirror or fork.
 * @return string
 */
function dt_get_theme_data_url() {
    return apply_filters( 'disciple_tools_theme_data_url', 'https://raw.githubusercontent.com/DiscipleTools/disciple-tools-theme-data/master/' );
}
if ( ! isset( $dt_mapping['disciple_tools_theme_data_url'] ) ) {
    $dt_mapping['disciple_tools_theme_data_url'] = dt_get_theme_data_url();
}

if ( ! isset( $dt_mapping['spinner'] ) ) {
    $dt_mapping['spinner'] = $dt_mapping['url'] . 'spinner.svg';
}

if ( ! isset( $dt_mapping['theme'] ) ) {
    $wp_theme = wp_get_theme();
    $dt_mapping['theme'] = [
        'current_theme_name' => $wp_theme->name,
        'current_theme_version' => $wp_theme->version,
    ];
}

$GLOBALS['dt_mapping'] = $dt_mapping;
