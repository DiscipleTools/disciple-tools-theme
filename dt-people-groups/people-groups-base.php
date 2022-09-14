<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly.

class Disciple_Tools_People_Groups_Base {
    public static $post_type = 'peoplegroups';
    public $single_name = 'People Group';
    public $plural_name = 'People Groups';

    public function __construct() {
        //setup post type
        add_action( 'after_setup_theme', [ $this, 'after_setup_theme' ], 100 );
        add_filter( 'dt_set_roles_and_permissions', [ $this, 'dt_set_roles_and_permissions' ], 20, 1 ); //after contacts
        add_filter( 'dt_get_post_type_settings', [ $this, 'dt_get_post_type_settings' ], 20, 2 );

        add_filter( 'dt_custom_fields_settings', [ $this, 'dt_custom_fields_settings' ], 10, 2 );

    }

    public function after_setup_theme(){
        $this->single_name = __( 'People Group', 'disciple_tools' );
        $this->plural_name = __( 'People Groups', 'disciple_tools' );

        if ( class_exists( 'Disciple_Tools_Post_Type_Template' ) ) {
            new Disciple_Tools_Post_Type_Template( $this->post_type, $this->single_name, $this->plural_name );
        }
    }

    public function dt_get_post_type_settings( $settings, $post_type ){
        if ( $post_type === $this->post_type ){
            $settings['label_singular'] = __( 'Starter', 'disciple-tools-plugin-starter-template' );
            $settings['label_plural'] = __( 'Starters', 'disciple-tools-plugin-starter-template' );
        }
        return $settings;
    }

    public function dt_set_roles_and_permissions( $expected_roles ){

        if ( !isset( $expected_roles['multiplier'] ) ){
            $expected_roles['multiplier'] = [

                'label' => __( 'Multiplier', 'disciple-tools-plugin-starter-template' ),
                'description' => 'Interacts with Contacts and Groups',
                'permissions' => []
            ];
        }

        // if the user can access contact they also can access this post type
        foreach ( $expected_roles as $role => $role_value ){
            if ( isset( $role_value['permissions']['access_contacts'] ) && $role_value['permissions']['access_contacts'] ){
                $expected_roles[$role]['permissions']['access_' . $this->post_type ] = true;
//                $expected_roles[$role]['permissions']['create_' . $this->post_type] = true;
//                $expected_roles[$role]['permissions']['update_' . $this->post_type] = true;
            }
        }

        if ( isset( $expected_roles['administrator'] ) ){
            $expected_roles['administrator']['permissions']['view_any_'.$this->post_type ] = true;
            $expected_roles['administrator']['permissions']['update_any_'.$this->post_type ] = true;
            $expected_roles['administrator']['permissions']['edit_peoplegroups'] = true;
        }
        if ( isset( $expected_roles['dt_admin'] ) ){
            $expected_roles['dt_admin']['permissions']['view_any_'.$this->post_type ] = true;
            $expected_roles['dt_admin']['permissions']['update_any_'.$this->post_type ] = true;
            $expected_roles['dt_admin']['permissions']['edit_peoplegroups'] = true;
        }
        return $expected_roles;
    }

}

