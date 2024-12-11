<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly.

add_filter( 'dt_post_type_modules', function( $modules ){
    $modules['contacts_followup_module'] = [
        'name' => 'Followup Module',
        'enabled' => true,
        'prerequisites' => [ 'contacts_base' ],
        'post_type' => 'contacts',
        'description' => 'Followup fields, filters and workflows'
    ];
    return $modules;
}, 10, 1 );

class DT_Contacts_Followup extends DT_Module_Base {
    public $post_type = 'contacts';
    public $module = 'contacts_followup_module';

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

        add_action( 'dt_comment_action_quick_action', [ $this, 'dt_comment_action_quick_action' ], 10, 1 );
    }

    public function dt_custom_fields_settings( $fields, $post_type ){
        $declared_fields = $fields;
        if ( $post_type === 'contacts' ){
            $fields['quick_button_no_answer'] = [
                'name'        => __( 'Contact Attempted', 'disciple_tools' ),
                'description' => '',
                'type'        => 'number',
                'default'     => 0,
                'section'     => 'quick_buttons',
                'icon'        => get_template_directory_uri() . '/dt-assets/images/send.svg',
                'customizable' => false
            ];
            $fields['quick_button_contact_established'] = [
                'name'        => __( 'Contact Established', 'disciple_tools' ),
                'description' => '',
                'type'        => 'number',
                'default'     => 0,
                'section'     => 'quick_buttons',
                'icon'        => get_template_directory_uri() . '/dt-assets/images/account-voice.svg?v=2',
                'customizable' => false
            ];
            $fields['quick_button_meeting_scheduled'] = [
                'name'        => __( 'Meeting Scheduled', 'disciple_tools' ),
                'description' => '',
                'type'        => 'number',
                'default'     => 0,
                'section'     => 'quick_buttons',
                'icon'        => get_template_directory_uri() . '/dt-assets/images/calendar-plus.svg?v=2',
                'customizable' => false
            ];
            $fields['quick_button_meeting_complete'] = [
                'name'        => __( 'Meeting Complete', 'disciple_tools' ),
                'description' => '',
                'type'        => 'number',
                'default'     => 0,
                'section'     => 'quick_buttons',
                'icon'        => get_template_directory_uri() . '/dt-assets/images/calendar-check.svg?v=2',
                'customizable' => false
            ];
            $fields['quick_button_no_show'] = [
                'name'        => __( 'Meeting No-show', 'disciple_tools' ),
                'description' => '',
                'type'        => 'number',
                'default'     => 0,
                'section'     => 'quick_buttons',
                'icon'        => get_template_directory_uri() . '/dt-assets/images/calendar-remove.svg?v=2',
                'customizable' => false
            ];
        }
        return dt_array_merge_recursive_distinct( $declared_fields, $fields );
    }


    /**
     * Adds the quick actions dropdown to the contact comments section
     * @param $post_type
     * @return void
     */
    public function dt_comment_action_quick_action( $post_type ){
        if ( $post_type === 'contacts' ){
            $contact = DT_Posts::get_post( 'contacts', get_the_ID() );
            $contact_fields = DT_Posts::get_post_field_settings( $post_type );
            ?>

            <ul class="dropdown menu" data-dropdown-menu style="display: inline-block">
                <li style="border-radius: 5px">
                    <a class="button menu-white-dropdown-arrow"
                       style="background-color: #00897B; color: white;">
                        <?php esc_html_e( 'Quick Actions', 'disciple_tools' ) ?></a>
                    <ul class="menu is-dropdown-submenu" style="width: max-content">
                        <?php
                        foreach ( $contact_fields as $field => $val ) {
                            if ( strpos( $field, 'quick_button' ) === 0 ) {
                                $current_value = 0;
                                if ( isset( $contact[$field] ) ) {
                                    $current_value = $contact[$field];
                                } ?>
                                <li class="quick-action-menu" data-id="<?php echo esc_attr( $field ) ?>">
                                    <a>
                                        <?php dt_render_field_icon( $val ); ?>
                                        <?php echo esc_html( $val['name'] ); ?>
                                        (<span class="<?php echo esc_attr( $field ) ?>"><?php echo esc_html( $current_value ); ?></span>)
                                    </a>
                                </li>
                                <?php
                            }
                        }
                        ?>
                    </ul>
                </li>
            </ul>
            <button class="help-button" data-section="quick-action-help-text">
                <img class="help-icon"
                     src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/help.svg?v=2' ) ?>"/>
            </button>
            <?php
        }
    }
}
DT_Contacts_Followup::instance();