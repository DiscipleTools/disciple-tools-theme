<?php

class DT_Geocode_API_Loader {
    // Singleton
    private static $_instance = null;
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct() {
        // load through sym
        $dir = scandir( getcwd() );
        foreach ( $dir as $file ) {
            if ( 'php' === substr( $file, -3, 3 ) ) {
                require_once( $file );
            }
        }
    }
}
DT_Geocode_API_Loader::instance();
