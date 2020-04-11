<?php
/**
 * DT Mapping Module
 *
 * @version 1.0 Initialized
 *          2.0 Migrated from Geonames to Location Grid
 *          3.0 Location Grid Modularized reusable Library, Geocoding Classes, Extended to Plugins and Themes
 */

if ( ! class_exists( 'DT_Mapping_Module_Loader' ) ) {
    class DT_Mapping_Module_Loader {
        public function __construct( $environment = 'disciple_tools', $custom_columns = false ) {
            /** Create globals */
            if ( ! isset( $dt_mapping ) ) {
                /**
                 * This global must be used through the mapping system so that the dt-mapping module continues to be an independent
                 * module, able to be included in non-disciple tools themes.
                 *
                 * it can be included in functions like other globals:
                 *      global $dt_mapping;
                 */
                $dt_mapping = [];
            }
            if ( ! isset( $dt_mapping['environment'] ) ) {
                /**
                 * Three supported environments: disciple_tools, theme, plugin
                 */
                switch ( $environment ) {
                    case 'theme':
                        $dt_mapping['environment'] = 'theme';
                        break;
                    case 'plugin':
                        $dt_mapping['environment'] = 'plugin';
                        break;
                    case 'disciple_tools':
                    default:
                        $dt_mapping['environment'] = 'disciple_tools';
                        break;
                }
            }
            if ( ! isset( $dt_mapping['version'] ) ) {
                $dt_mapping['version'] = '3.0';
            }
            require_once( 'globals.php' );
            /** end create globals */

            /** Configurations */
            require_once( 'mapping-module-config.php' );

            /** Additional columns */
            if ( ! $custom_columns ) {
                require_once( 'columns/add-contacts-column.php' );
                require_once( 'columns/add-groups-column.php' );
                require_once( 'columns/add-churches-column.php' );
                require_once( 'columns/add-users-column.php' );
            }

            /** Queries */
            require_once( 'mapping-queries.php' );

            /** Geocoding */
            require_once( 'geocode-api/google-api.php' );
            require_once( 'geocode-api/ipstack-api.php' );
            require_once( 'geocode-api/location-grid-geocoder.php' );
            require_once( 'geocode-api/mapbox-api.php' );

            /** Admin */
            require_once( 'mapping-admin.php' ); // can't filter for is_admin because of REST dependencies

            /** Core */
            require_once( 'mapping.php' );
            if ( DT_Mapbox_API::get_key() ) {
                require_once( 'mapbox-metrics.php' );
            } else {
                require_once( 'mapping-metrics.php' );
            }
        }
    }
}
