<?php

abstract class DT_Module_Base{
    public $post_type = 'contacts';
    public $module = '';

    public function __construct() {
        add_filter( 'dt_record_picture', [ $this, 'dt_record_picture_base' ], 10, 3 );
        add_filter( 'dt_record_icon', [ $this, 'dt_record_icon_base' ], 10, 3 );
    }

    protected function check_enabled_and_prerequisites(){
        $modules = dt_get_option( 'dt_post_type_modules' );
        $module_enabled = isset( $modules[$this->module]['enabled'] ) ? $modules[$this->module]['enabled'] : false;
        foreach ( $modules[$this->module]['prerequisites'] as $prereq ){
            $prereq_enabled = isset( $modules[$prereq]['enabled'] ) ? $modules[$prereq]['enabled'] : false;
            if ( !$prereq_enabled ){
                return false;
            }
        }
        return $module_enabled;
    }

    public function dt_record_picture_base( $picture, $post_type, $post_id ){
        if ( !in_array( $post_type, [ 'contacts' ] ) ) {
            $picture = null;
        }

        return $picture;
    }

    public function dt_record_icon_base( $icon, $post_type, $post_id ){
        if ( !in_array( $post_type, [ 'contacts' ] ) ) {
            $icon = 'fi-torsos-all';
        }

        return $icon;
    }
}
