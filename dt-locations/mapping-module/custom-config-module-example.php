<?php

class DT_Mapping_Module_Example_Filters
{
    private static $_instance = null;
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    } // End instance()

    public function __construct()
    {

        /**
         * dt_mapping_module_has_permissions
         * @see    mapping.php:56
         */
        add_filter( 'dt_mapping_module_has_permissions', [ $this, 'custom_permission_check' ] );

        /**
         * dt_mapping_module_endpoints
         * @see     mapping.php:77
         */
        add_filter( 'dt_mapping_module_endpoints', [ $this, 'add_custom_endpoints'], 10, 1 );

        /**
         * dt_mapping_module_url_base
         * @see     mapping.php:102
         */
        add_filter( 'dt_mapping_module_url_base', [ $this, 'custom_url_base' ] );

        /**
         * dt_mapping_module_translations
         * @see     mapping.php:119 125
         */
        add_filter( 'dt_mapping_module_translations', [ $this, 'custom_translations' ] );

        /**
         * dt_mapping_module_data
         * @see     mapping.php:220
         * @see     mapping.php:292
         */
        add_filter( 'dt_mapping_module_data', [ $this, 'custom_data_filter'], 10, 1 );

        /**
         * dt_mapping_module_map_level_by_geoname
         * @see     mapping.php:389
         */
        add_filter( 'dt_mapping_module_map_level_by_geoname', [ $this, 'map_level_by_gename_filter'], 10, 1 );

        /**
         * dt_mapping_module_custom_population_divisions
         * @see     mapping.php:748
         */
        add_filter( 'dt_mapping_module_custom_population_divisions', [ $this, 'custom_population_division' ] );
    }

    /**
     * custom_permission_check
     *
     * @return bool
     */
    public function custom_permission_check(): bool {
        /**
         * Add logic to evaluate current user and return a bool decision on permission to the mapping module
         * Example below gives permission to dispatchers and admins.
         */
        if ( current_user_can( 'view_any_contacts' ) || current_user_can( 'view_project_metrics' ) ) {
            return true;
        }
        return false;
    }

    /**
     * add_custom_endpoints
     *
     * @param $endpoints
     *
     * @return mixed
     */
    public function add_custom_endpoints( $endpoints ) {
        /**
         * Add new endpoint here
         */
        return $endpoints;
    }

    /**
     * Set the base url for the mapping links to respond to.
     *
     * @param $base_url (default is '
     *
     * @return string
     */
    public function custom_url_base( $base_url ) {
        /**
         * Add new url base for listener
         */
        return $base_url;
    }

    /**
     * custom_translations
     *
     * @param $translations
     *
     * @return mixed
     */
    public function custom_translations( $translations ) {
        /**
         * Add translation strings
         */
        return $translations;
    }

    /**
     * Populates the heat map
     *
     * @param $data
     *
     * @return array
     */
    public function custom_data_filter( $data ) {
        /**
         * Filter data here
         */
        return $data;
    }

    /**
     * Pre-processes map_level data before delivery
     *
     * @param $data
     *
     * @return mixed
     */
    public function map_level_by_gename_filter( $data ) {
        /**
         * Add filter here
         */
        return $data;
    }

    public function custom_population_division( $data ) { // @todo move this to a admin tab for configuration
        /**********************************************************************************************************
         * Filter to supply custom divisions geographic unit.
         *
         * @example     [
         *                  6252001 => 5000
         *              ]
         *
         *              This would make the "United States" ( i.e. 6252001) use divisions of 5000
         */
        return $data;
    }


}