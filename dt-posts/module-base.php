<?php

abstract class DT_Module_Base{
    public $post_type = "contacts";
    public $module = "";

    public function __construct(){}

    protected function check_enabled_and_prerequisites(){
        $modules = dt_get_option( 'dt_post_type_modules' );
        $module_enabled = isset( $modules[$this->module]["enabled"] ) ? $modules[$this->module]["enabled"] : false;
        foreach ( $modules[$this->module]["prerequisites"] as $prereq ){
            $prereq_enabled = isset( $modules[$prereq]["enabled"] ) ? $modules[$prereq]["enabled"] : false;
            if ( !$prereq_enabled ){
                return false;
            }
        }
        return $module_enabled;
    }

}
