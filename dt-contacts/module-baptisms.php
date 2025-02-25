<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly.

add_filter( 'dt_post_type_modules', function( $modules ){
    $modules['contacts_baptisms_module'] = [
        'name' => 'Baptisms',
        'enabled' => true,
        'prerequisites' => [ 'contacts_base' ],
        'post_type' => 'contacts',
        'description' => 'Track contact baptism relationships, dates and generations',
        'submodule' => true,
    ];
    return $modules;
}, 10, 1 );

class DT_Contacts_Baptisms extends DT_Module_Base {
    public $post_type = 'contacts';
    public $module = 'contacts_baptisms_module';

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
        add_action( 'dt_record_footer', [ $this, 'dt_record_footer' ], 10, 2 );

        //hooks
        add_action( 'post_connection_removed', [ $this, 'post_connection_removed' ], 10, 4 );
        add_action( 'post_connection_added', [ $this, 'post_connection_added' ], 10, 4 );
    }

    public function dt_custom_fields_settings( $fields, $post_type ){
        if ( $post_type === 'contacts' ){
            $fields['baptism_date'] = [
                'name' => __( 'Baptism Date', 'disciple_tools' ),
                'description' => '',
                'type' => 'date',
                'icon' => get_template_directory_uri() . '/dt-assets/images/calendar-heart.svg?v=2',
                'tile' => 'details',
            ];

            $fields['baptism_generation'] = [
                'name'        => __( 'Baptism Generation', 'disciple_tools' ),
                'type'        => 'number',
                'default'     => '',
            ];
            $fields['baptized_by'] = [
                'name' => __( 'Baptized by', 'disciple_tools' ),
                'description' => _x( 'Who baptized this contact', 'Optional Documentation', 'disciple_tools' ),
                'type' => 'connection',
                'post_type' => 'contacts',
                'p2p_direction' => 'from',
                'p2p_key' => 'baptizer_to_baptized',
                'tile'     => 'faith',
                'icon' => get_template_directory_uri() . '/dt-assets/images/baptism.svg?v=2',
            ];
            $fields['baptized'] = [
                'name' => __( 'Baptized', 'disciple_tools' ),
                'description' => _x( 'Who this contact has baptized', 'Optional Documentation', 'disciple_tools' ),
                'type' => 'connection',
                'post_type' => 'contacts',
                'p2p_direction' => 'to',
                'p2p_key' => 'baptizer_to_baptized',
                'tile'     => 'faith',
                'icon' => get_template_directory_uri() . '/dt-assets/images/child.svg?v=2',
            ];

        }
        return $fields;
    }

    public function dt_details_additional_tiles( $tiles, $post_type = '' ){
        if ( $post_type === 'contacts' ){
            $tiles['faith'] = [
                'label' => __( 'Faith', 'disciple_tools' )
            ];
        }
        return $tiles;
    }


    public function post_connection_added( $post_type, $post_id, $post_key, $value ){
        if ( $post_type === 'contacts' ){
            if ( $post_key === 'baptized' ){
                Disciple_Tools_Counter_Baptism::reset_baptism_generations_on_contact_tree( $value );
                $field_settings = DT_Posts::get_post_field_settings( $post_type );
                if ( isset( $field_settings['milestones'] ) && empty( $field_settings['milestones']['hidden'] ) ){
                    $milestones = get_post_meta( $post_id, 'milestones' );
                    if ( empty( $milestones ) || !in_array( 'milestone_baptizing', $milestones ) ){
                        add_post_meta( $post_id, 'milestones', 'milestone_baptizing' );
                    }
                }
                Disciple_Tools_Counter_Baptism::reset_baptism_generations_on_contact_tree( $post_id );
            }
            if ( $post_key === 'baptized_by' ){
                $field_settings = DT_Posts::get_post_field_settings( $post_type );
                if ( isset( $field_settings['milestones'] ) && empty( $field_settings['milestones']['hidden'] ) ){

                    $milestones = get_post_meta( $post_id, 'milestones' );
                    if ( empty( $milestones ) || !in_array( 'milestone_baptized', $milestones ) ){
                        add_post_meta( $post_id, 'milestones', 'milestone_baptized' );
                    }
                }
                Disciple_Tools_Counter_Baptism::reset_baptism_generations_on_contact_tree( $post_id );
            }
        }
    }
    public function post_connection_removed( $post_type, $post_id, $post_key, $value ){
        if ( $post_type === 'contacts' ){
            if ( $post_key === 'baptized_by' ){
                Disciple_Tools_Counter_Baptism::reset_baptism_generations_on_contact_tree( $post_id );
            }
            if ( $post_key === 'baptized' ){
                Disciple_Tools_Counter_Baptism::reset_baptism_generations_on_contact_tree( $value );
            }
        }
    }


    public function dt_record_footer( $post_type, $post_id ){
        if ( $post_type !== 'contacts' ){
            return;
        }
        get_template_part( 'dt-assets/parts/modals/modal', 'revert' );
        $field_settings = DT_Posts::get_post_field_settings( $post_type );
        $post = DT_Posts::get_post( 'contacts', $post_id );
        ?>
        <script>
          jQuery(document).ready(function ($) {
            let post = window.detailsSettings.post_fields;
            let openBaptismModal = function (newContact) {
              if (
                !post.baptism_date ||
                !(post.milestones || []).includes('milestone_baptized') ||
                (post.baptized_by || []).length === 0
              ) {
                $('#baptism-modal').foundation('open');
              }
              post = newContact;
            };
            $('#close-baptism-modal').on('click', function () {
              location.reload();
            });

            /**
             * detect if an update is made on the baptized_by field.
             */
            $(document).on('dt_record_updated', function (e, response, request) {
              post = response;
              if (
                request?.baptized_by && response?.baptized_by && response?.baptized_by[0]
              ) {
                openBaptismModal(response);
              }
            });

            $(document).on('dt:post:update', function (e) {
              const {response, field, value} = e?.detail;

              // open modal when baptism milestone is set
              if (field === 'milestones' && value.values.some(x => x.value === 'milestone_baptized' && !x.delete)) {
                openBaptismModal(response);
                //todo: instead of refresh, update the component value of appropriate fields
              }

              // open modal when baptism_date is set
              if (field === 'baptism_date' && response.baptism_date && response.baptism_date.timestamp) {
                openBaptismModal(response)
              }
            });

          })
        </script>
        <div class="reveal" id="baptism-modal" data-reveal data-close-on-click="false">

            <h3><?php echo esc_html( $field_settings['baptized']['name'] ?? '' )?></h3>
            <p><?php esc_html_e( 'Who was this contact baptized by and when?', 'disciple_tools' )?></p>

            <div>

                <?php DT_Components::render_connection( 'baptized_by', $field_settings, $post ) ?>
                <?php DT_Components::render_date( 'baptism_date', $field_settings, $post ) ?>

            </div>


            <div class="grid-x">
                <button class="button" data-close type="button" id="close-baptism-modal">
                    <?php echo esc_html__( 'Close', 'disciple_tools' )?>
                </button>
                <button class="close-button" data-close aria-label="<?php esc_html_e( 'Close', 'disciple_tools' ); ?>" type="button">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        </div>
        <?php
    }
}
DT_Contacts_Baptisms::instance();
