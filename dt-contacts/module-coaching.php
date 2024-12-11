<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly.

add_filter( 'dt_post_type_modules', function( $modules ){
    $modules['contact_coaching_module'] = [
        'name' => 'Coaching Module',
        'enabled' => true,
        'prerequisites' => [ 'contacts_base' ],
        'post_type' => 'contacts',
        'description' => ''
    ];
    return $modules;
}, 10, 1 );

class DT_Contacts_Coaching extends DT_Module_Base {
    public $post_type = 'contacts';
    public $module = 'contact_coaching_module';

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

        //hooks
        add_action( 'post_connection_added', [ $this, 'post_connection_added' ], 10, 4 );
        add_filter( 'dt_post_create_fields', [ $this, 'dt_post_create_fields' ], 10, 2 );

        //list
        add_filter( 'dt_user_list_filters', [ $this, 'dt_user_list_filters' ], 10, 2 );
    }

    public function dt_custom_fields_settings( $fields, $post_type ){
        if ( $post_type === 'contacts' ){
            $fields['coaching'] = [
                'name' => __( 'Is Coaching', 'disciple_tools' ),
                'description' => _x( 'Who is this contact coaching', 'Optional Documentation', 'disciple_tools' ),
                'type' => 'connection',
                'post_type' => 'contacts',
                'p2p_direction' => 'to',
                'p2p_key' => 'contacts_to_contacts',
                'tile' => 'other',
                'icon' => get_template_directory_uri() . '/dt-assets/images/coaching.svg?v=2',
            ];
            $fields['coached_by'] = [
                'name' => __( 'Coached by', 'disciple_tools' ),
                'description' => _x( 'Who is coaching this contact', 'Optional Documentation', 'disciple_tools' ),
                'type' => 'connection',
                'post_type' => 'contacts',
                'p2p_direction' => 'from',
                'p2p_key' => 'contacts_to_contacts',
                'tile' => 'status',
                'icon' => get_template_directory_uri() . '/dt-assets/images/coach.svg?v=2',
            ];
        }
        return $fields;
    }

    public function post_connection_added( $post_type, $post_id, $post_key, $value ){
        if ( $post_type === 'contacts' ){
            if ( $post_key === 'coached_by' ){
                $user_id = get_post_meta( $value, 'corresponds_to_user', true );
                if ( $user_id ){
                    DT_Posts::add_shared( $post_type, $post_id, $user_id, null, false, false, true );
                }
            }
        }
    }

    //Add, remove or modify fields before the fields are processed in post create
    public function dt_post_create_fields( $fields, $post_type ){
        if ( $post_type === 'contacts' ){
            //mark a new user contact as being coached be the user who added the new user.
            if ( isset( $fields['type'] ) && $fields['type'] === 'user' ){
                $current_user_contact = Disciple_Tools_Users::get_contact_for_user( get_current_user_id() );
                if ( $current_user_contact && !is_wp_error( $current_user_contact ) ){
                    $fields['coached_by'] = [ 'values' => [ [ 'value' => $current_user_contact ] ] ];
                }
            }
        }
        return $fields;
    }


    public static function dt_user_list_filters( $filters, $post_type ) {
        if ( $post_type === 'contacts' ) {

            global $wpdb;
            $performance_mode = get_option( 'dt_performance_mode', false );
            $user_post_id = Disciple_Tools_Users::get_contact_for_user( get_current_user_id() ) ?? 0;
            $coached_by_me = $performance_mode ? 0 : $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(p2p_to) FROM $wpdb->p2p WHERE p2p_to = %s AND p2p_type = 'contacts_to_contacts'", esc_sql( $user_post_id ) ) );

            $filters['filters'][] = [
                'ID' => 'my_coached',
                'visible' => '1',
                'type' => 'default',
                'tab' => 'default',
                'name' => __( 'Coached by me', 'disciple_tools' ),
                'count' => $coached_by_me > 0 ? $coached_by_me : '',
                'query' => [
                    'coached_by' => [ 'me' ],
                    'overall_status' => [ '-closed' ],
                    'sort' => 'seeker_path',
                ],
                'labels' => [
                    [
                        'id' => 'me',
                        'name' => __( 'Coached by me', 'disciple_tools' ),
                        'field' => 'coached_by',
                    ],
                ],
            ];
        }

        //translation for default fields
        foreach ( $filters['filters'] as $index => $filter ) {
            if ( $filter['name'] === 'Coached by me' ) {
                $filters['filters'][$index]['name'] = __( 'Coached by me', 'disciple_tools' );
                $filters['filters'][$index]['labels'][0]['name'] = __( 'Coached by me', 'disciple_tools' );
            }
        }
        return $filters;
    }
}
DT_Contacts_Coaching::instance();
