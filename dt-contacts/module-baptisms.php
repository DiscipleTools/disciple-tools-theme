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
            let post_id = window.detailsSettings.post_id;
            // Baptism date
            let modalBaptismDatePicker = $('input#modal-baptism-date-picker');
            modalBaptismDatePicker.datepicker({
              constrainInput: false,
              dateFormat: 'yy-mm-dd',
              onSelect: function (date) {
                window.API.update_post('contacts', post_id, { baptism_date: date })
                .then((resp) => {
                  if (this.value) {
                    this.value = window.SHAREDFUNCTIONS.formatDate(
                      resp['baptism_date']['timestamp'],
                    );
                  }
                })
                .catch(window.handleAjaxError);
              },
              changeMonth: true,
              changeYear: true,
              yearRange: '-20:+10',
            });
            let openBaptismModal = function (newContact) {
              if (
                !post.baptism_date ||
                !(post.milestones || []).includes('milestone_baptized') ||
                (post.baptized_by || []).length === 0
              ) {
                $('#baptism-modal').foundation('open');
                if (!window.Typeahead['.js-typeahead-modal_baptized_by']) {
                  $.typeahead({
                    input: '.js-typeahead-modal_baptized_by',
                    minLength: 0,
                    accent: true,
                    searchOnFocus: true,
                    source: window.TYPEAHEADS.typeaheadContactsSource(),
                    templateValue: '{{name}}',
                    template: window.TYPEAHEADS.contactListRowTemplate,
                    matcher: function (item) {
                      return parseInt(item.ID) !== parseInt(post.ID);
                    },
                    dynamic: true,
                    hint: true,
                    emptyTemplate: window.SHAREDFUNCTIONS.escapeHTML(
                      window.wpApiShare.translations.no_records_found,
                    ),
                    multiselect: {
                      matchOn: ['ID'],
                      data: function () {
                        return (post['baptized_by'] || []).map((g) => {
                          return { ID: g.ID, name: g.post_title };
                        });
                      },
                      callback: {
                        onCancel: function (node, item) {
                          window.API.update_post('contacts', post_id, {
                            baptized_by: { values: [{ value: item.ID, delete: true }] },
                          }).catch((err) => {
                            console.error(err);
                          });
                        },
                      },
                      href:
                        window.SHAREDFUNCTIONS.escapeHTML(window.wpApiShare.site_url) +
                        '/contacts/{{ID}}',
                    },
                    callback: {
                      onClick: function (node, a, item) {
                        window.API.update_post('contacts', post_id, {
                          baptized_by: { values: [{ value: item.ID }] },
                        }).catch((err) => {
                          console.error(err);
                        });
                        this.addMultiselectItemLayout(item);
                        event.preventDefault();
                        this.hideLayout();
                        this.resetInput();
                      },
                      onResult: function (node, query, result, resultCount) {
                        let text = window.TYPEAHEADS.typeaheadHelpText(
                          resultCount,
                          query,
                          result,
                        );
                        $('#modal_baptized_by-result-container').html(text);
                      },
                      onHideLayout: function () {
                        $('.modal_baptized_by-result-container').html('');
                      },
                    },
                  });
                }
                if ( ( newContact.baptism_date?.timestamp || 0 ) > 0) {
                  modalBaptismDatePicker.datepicker(
                    'setDate',
                    window.moment
                    .unix(newContact['baptism_date']['timestamp'])
                    .format('YYYY-MM-DD'),
                  );
                  modalBaptismDatePicker.val(
                    window.SHAREDFUNCTIONS.formatDate(
                      newContact['baptism_date']['timestamp'],
                    ),
                  );
                }
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

            /**
             * detect if an update is made on the milestone field for baptized.
             */
            $(document).on(
              'dt_multi_select-updated',
              function(e, newContact, fieldKey, optionKey, action) {
                if (optionKey==='milestone_baptized' && action==='add') {
                  openBaptismModal(newContact);
                }
              },
            );
            /**
             * If a baptism date is added
             */
            $(document).on('dt_date_picker-updated', function(e, newContact, id, date) {
              if (
                id==='baptism_date' &&
                newContact.baptism_date &&
                newContact.baptism_date.timestamp
              ) {
                openBaptismModal(newContact);
              }
            });
          })
        </script>
        <div class="reveal" id="baptism-modal" data-reveal data-close-on-click="false">

            <h3><?php echo esc_html( $field_settings['baptized']['name'] ?? '' )?></h3>
            <p><?php esc_html_e( 'Who was this contact baptized by and when?', 'disciple_tools' )?></p>

            <div>
                <div class="section-subheader">
                    <?php echo esc_html( $field_settings['baptized_by']['name'] ?? '' )?>
                </div>
                <div class="modal_baptized_by details">
                    <var id="modal_baptized_by-result-container" class="result-container modal_baptized_by-result-container"></var>
                    <div id="modal_baptized_by_t" name="form-modal_baptized_by" class="scrollable-typeahead typeahead-margin-when-active">
                        <div class="typeahead__container">
                            <div class="typeahead__field">
                                <span class="typeahead__query">
                                    <input class="js-typeahead-modal_baptized_by input-height"
                                           name="modal_baptized_by[query]"
                                           placeholder="<?php echo esc_html_x( 'Search multipliers and contacts', 'input field placeholder', 'disciple_tools' ) ?>"
                                           autocomplete="off">
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <span class="section-subheader"><?php echo esc_html( $field_settings['baptism_date']['name'] )?></span>
                <input type="text" data-date-format='yy-mm-dd' value="<?php echo esc_html( $post['baptism_date']['timestamp'] ?? '' );?>" id="modal-baptism-date-picker" autocomplete="off">

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
