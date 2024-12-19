<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly.

add_filter( 'dt_post_type_modules', function( $modules ){
    $modules['contacts_faith_module'] = [
        'name' => 'Faith',
        'enabled' => true,
        'prerequisites' => [ 'contacts_base' ],
        'post_type' => 'contacts',
        'description' => 'Track progress of contacts in their faith journey',
        'submodule' => true,
    ];
    return $modules;
}, 10, 1 );

class DT_Contacts_Faith extends DT_Module_Base {
    public $post_type = 'contacts';
    public $module = 'contacts_faith_module';

    private static $_instance = null;
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    } // End instance()

    public function __construct(){
        parent::__construct();
        if ( !self::check_enabled_and_prerequisites() ){
            return;
        }
        //setup fields
        add_filter( 'dt_custom_fields_settings', [ $this, 'dt_custom_fields_settings' ], 10, 2 );

         //display tiles and fields
        add_filter( 'dt_details_additional_tiles', [ $this, 'dt_details_additional_tiles' ], 10, 2 );
    }

    public function dt_custom_fields_settings( $fields, $post_type ){
        if ( $post_type === 'contacts' ){
            $fields['milestones'] = [
                'name'    => __( 'Faith Milestones', 'disciple_tools' ),
                'description' => _x( 'Assign which milestones the contact has reached in their faith journey. These are points in a contact’s spiritual journey worth celebrating but can happen in any order.', 'Optional Documentation', 'disciple_tools' ),
                'type'    => 'multi_select',
                'default' => [
                    'milestone_has_bible'     => [
                        'label' => __( 'Has Bible', 'disciple_tools' ),
                        'description' => '',
                        'icon' => get_template_directory_uri() . '/dt-assets/images/bible.svg?v=2',
                    ],
                    'milestone_reading_bible' => [
                        'label' => __( 'Reading Bible', 'disciple_tools' ),
                        'description' => '',
                        'icon' => get_template_directory_uri() . '/dt-assets/images/reading.svg?v=2',
                    ],
                    'milestone_belief'        => [
                        'label' => __( 'States Belief', 'disciple_tools' ),
                        'description' => '',
                        'icon' => get_template_directory_uri() . '/dt-assets/images/speak.svg?v=2',
                    ],
                    'milestone_can_share'     => [
                        'label' => __( 'Can Share Gospel/Testimony', 'disciple_tools' ),
                        'description' => '',
                        'icon' => get_template_directory_uri() . '/dt-assets/images/hand-heart.svg?v=2',
                    ],
                    'milestone_sharing'       => [
                        'label' => __( 'Sharing Gospel/Testimony', 'disciple_tools' ),
                        'description' => '',
                        'icon' => get_template_directory_uri() . '/dt-assets/images/account-voice.svg?v=2',
                    ],
                    'milestone_baptized'      => [
                        'label' => __( 'Baptized', 'disciple_tools' ),
                        'description' => '',
                        'icon' => get_template_directory_uri() . '/dt-assets/images/baptism.svg?v=2',
                    ],
                    'milestone_baptizing'     => [
                        'label' => __( 'Baptizing', 'disciple_tools' ),
                        'description' => '',
                        'icon' => get_template_directory_uri() . '/dt-assets/images/child.svg?v=2',
                    ],
                    'milestone_in_group'      => [
                        'label' => __( 'In Church/Group', 'disciple_tools' ),
                        'description' => '',
                        'icon' => get_template_directory_uri() . '/dt-assets/images/group-type.svg?v=2',
                    ],
                    'milestone_planting'      => [
                    'label' => __( 'Starting Churches', 'disciple_tools' ),
                        'description' => '',
                        'icon' => get_template_directory_uri() . '/dt-assets/images/stream.svg?v=2',
                    ],
                ],
                'tile' => 'faith',
                'show_in_table' => 20,
                'icon' => get_template_directory_uri() . '/dt-assets/images/bible.svg?v=2',
            ];
            $fields['faith_status'] =[
                'name' => __( 'Faith Status', 'disciple_tools' ),
                'description' => '',
                'type' => 'key_select',
                'default' => [
                    'seeker'     => [
                        'label' => __( 'Seeker', 'disciple_tools' ),
                    ],
                    'believer'     => [
                        'label' => __( 'Believer', 'disciple_tools' ),
                    ],
                    'leader'     => [
                        'label' => __( 'Leader', 'disciple_tools' ),
                    ],
                ],
                'tile' => 'status',
                'icon' => get_template_directory_uri() . '/dt-assets/images/cross.svg?v=2',
                'in_create_form' => true
            ];
        }
        return $fields;
    }

    public function dt_details_additional_tiles( $tiles, $post_type = '' ){
        if ( $post_type === 'contacts' ){
            $tiles['faith'] = [
                'label' => __( 'Faith', 'disciple_tools' )
            ];
//            if ( isset( $tiles['status'] ) && !isset( $tiles['status']['order'] ) ){
//                $tiles['status']['order'] = [ 'subassigned', 'faith_status', 'coached_by' ];
//            }
        }
        return $tiles;
    }
}
DT_Contacts_Faith::instance();
