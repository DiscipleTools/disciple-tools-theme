<?php

if ( ! defined( 'DT_FUNCTIONS_READY' ) ){
    define( 'DT_FUNCTIONS_READY', true );


    /**
     * A simple function to assist with development and non-disruptive debugging.
     * -----------
     * -----------
     * REQUIREMENT:
     * WP Debug logging must be set to true in the wp-config.php file.
     * Add these definitions above the "That's all, stop editing! Happy blogging." line in wp-config.php
     * -----------
     * define( 'WP_DEBUG', true ); // Enable WP_DEBUG mode
     * define( 'WP_DEBUG_LOG', true ); // Enable Debug logging to the /wp-content/debug.log file
     * define( 'WP_DEBUG_DISPLAY', false ); // Disable display of errors and warnings
     * @ini_set( 'display_errors', 0 );
     * -----------
     * -----------
     * EXAMPLE USAGE:
     * (string)
     * write_log('THIS IS THE START OF MY CUSTOM DEBUG');
     * -----------
     * (array)
     * $an_array_of_things = ['an', 'array', 'of', 'things'];
     * write_log($an_array_of_things);
     * -----------
     * (object)
     * $an_object = new An_Object
     * write_log($an_object);
     */
    if ( ! function_exists( 'dt_write_log' ) ) {
        /**
         * A function to assist development only.
         * This function allows you to post a string, array, or object to the WP_DEBUG log.
         * It also prints elapsed time since the last call.
         *
         * @param $log
         */
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

    if ( !function_exists( 'dt_is_rest' ) ) {
        /**
         * Checks if the current request is a WP REST API request.
         *
         * Case #1: After WP_REST_Request initialisation
         * Case #2: Support "plain" permalink settings
         * Case #3: URL Path begins with wp-json/ (your REST prefix)
         *          Also supports WP installations in subfolders
         *
         * @returns boolean
         * @author matzeeable
         */
        function dt_is_rest( $namespace = null ) {
            $prefix = rest_get_url_prefix();
            if ( defined( 'REST_REQUEST' ) && REST_REQUEST
                 || isset( $_GET['rest_route'] )
                    && strpos( trim( sanitize_text_field( wp_unslash( $_GET['rest_route'] ) ), '\\/' ), $prefix, 0 ) === 0 ) {
                return true;
            }
            $rest_url    = wp_parse_url( site_url( $prefix ) );
            $current_url = wp_parse_url( add_query_arg( array() ) );
            $is_rest = strpos( $current_url['path'], $rest_url['path'], 0 ) === 0;
            if ( $namespace ){
                return $is_rest && strpos( $current_url['path'], $namespace ) != false;
            } else {
                return $is_rest;
            }
        }
    }

    /**
     * The the base site url with, including the subfolder if wp is installed in a subfolder.
     * @return string
     */
    function dt_get_url_path() {
        if ( isset( $_SERVER["HTTP_HOST"] ) ) {
            $url  = ( !isset( $_SERVER["HTTPS"] ) || @( $_SERVER["HTTPS"] != 'on' ) ) ? 'http://'. sanitize_text_field( wp_unslash( $_SERVER["HTTP_HOST"] ) ) : 'https://'. sanitize_text_field( wp_unslash( $_SERVER["HTTP_HOST"] ) );
            if ( isset( $_SERVER["REQUEST_URI"] ) ) {
                $url .= sanitize_text_field( wp_unslash( $_SERVER["REQUEST_URI"] ) );
            }
        }
        return trim( str_replace( get_site_url(), "", $url ), '/' );
    }
}

