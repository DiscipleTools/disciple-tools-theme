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
        require_once( 'google-api.php' );
        require_once( 'ipstack-api.php' );
        require_once( 'location-grid-geocoder.php' );
        require_once( 'mapbox-api.php' );
    }
}
DT_Geocode_API_Loader::instance();
