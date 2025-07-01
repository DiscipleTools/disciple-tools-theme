<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly.

class DT_Contacts_Access extends DT_Module_Base {
    public $post_type = 'contacts';
    public $module = 'access_module';

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
        //permissions
        add_filter( 'dt_set_roles_and_permissions', [ $this, 'dt_set_roles_and_permissions' ], 10, 1 );
        add_filter( 'dt_can_view_permission', [ $this, 'can_view_permission_filter' ], 10, 3 );
        add_filter( 'dt_can_update_permission', [ $this, 'can_update_permission_filter' ], 10, 3 );

        //setup fields
        add_action( 'rest_api_init', [ $this, 'add_api_routes' ] );
        add_filter( 'dt_custom_fields_settings', [ $this, 'dt_custom_fields_settings' ], 20, 2 );

        //display tiles and fields
        add_filter( 'dt_details_additional_tiles', [ $this, 'dt_details_additional_tiles' ], 20, 2 );
        add_action( 'dt_record_top_above_details', [ $this, 'dt_record_top_above_details' ], 20, 2 );
        add_action( 'dt_render_field_for_display_template', [ $this, 'dt_render_field_for_display_template' ], 20, 7 );
        add_filter( 'dt_render_field_for_display_fields', [ $this, 'dt_render_field_for_display_fields' ], 100, 3 );
        add_action( 'dt_comment_action_quick_action', [ $this, 'dt_comment_action_quick_action' ], 10, 1 );

        add_action( 'wp_enqueue_scripts', [ $this, 'scripts' ], 99 );

        //list
        add_filter( 'dt_user_list_filters', [ $this, 'dt_user_list_filters' ], 20, 2 );
        add_filter( 'dt_filter_access_permissions', [ $this, 'dt_filter_access_permissions' ], 20, 2 );

        //api
        add_filter( 'dt_post_update_fields', [ $this, 'dt_post_update_fields' ], 10, 4 );
        add_action( 'dt_comment_created', [ $this, 'dt_comment_created' ], 20, 4 );
        add_filter( 'dt_post_create_fields', [ $this, 'dt_post_create_fields' ], 5, 2 );
        add_action( 'dt_post_updated', [ $this, 'dt_post_updated' ], 10, 5 );


        //users table fields
        add_filter( 'dt_users_fields', [ $this, 'dt_users_fields' ], 10, 1 );
    }

    public function dt_set_roles_and_permissions( $expected_roles ){
        $base_contacts_permissions = [ 'access_contacts' => true, 'create_contacts' => true ];
        $all_user_caps = Disciple_Tools_Roles::default_user_caps();
        $metrics_caps = Disciple_Tools_Roles::default_all_metrics_caps();
        $expected_roles['dispatcher'] = [
            'label' => __( 'Dispatcher', 'disciple_tools' ),
            'description' => 'Monitor new D.T contacts and assign them to waiting Multipliers',
            'permissions' => array_merge( $base_contacts_permissions, $all_user_caps, $metrics_caps ),
            'type' => [ 'base', 'access' ],
            'order' => 20
        ];
        $expected_roles['partner'] = [
            'label' => __( 'Partner', 'disciple_tools' ),
            'description' => 'Allow access to a specific contact source so a partner can see progress',
            'permissions' => array_merge( $base_contacts_permissions, $all_user_caps ),
            'type' => [ 'base', 'access' ],
            'order' => 35
        ];
        $expected_roles['marketer'] = [
            'label' => __( 'Digital Responder', 'disciple_tools' ),
            'description' => 'Talk to leads online and report in D.T when Contacts are ready for follow-up',
            'permissions' => array_merge( $base_contacts_permissions, $all_user_caps, $metrics_caps ),
            'type' => [ 'base', 'access' ],
            'order' => 50
        ];

        $expected_roles['marketer']['permissions']['access_specific_sources'] = true;
        $expected_roles['marketer']['permissions']['assign_any_contacts'] = true;
        $expected_roles['partner']['permissions']['access_specific_sources'] = true;

        $expected_roles['dispatcher']['permissions']['dt_all_access_contacts'] = true;
        $expected_roles['dispatcher']['permissions']['assign_any_contacts'] = true;
        $expected_roles['dispatcher']['permissions']['list_users'] = true;
        $expected_roles['dispatcher']['permissions']['dt_list_users'] = true;

        $expected_roles['administrator']['permissions']['dt_all_access_contacts'] = true;
        $expected_roles['administrator']['permissions']['assign_any_contacts'] = true;
        $expected_roles['dt_admin']['permissions']['dt_all_access_contacts'] = true;
        $expected_roles['dt_admin']['permissions']['assign_any_contacts'] = true;

        return $expected_roles;
    }

    public function dt_custom_fields_settings( $fields, $post_type ){
        $declared_fields = $fields;
        if ( $post_type === 'contacts' ){

            $fields['seeker_path'] = [
                'name'        => __( 'Seeker Path', 'disciple_tools' ),
                'description' => _x( 'Set the status of your progression with the contact. These are the steps that happen in a specific order to help a contact move forward.', 'Seeker Path field description', 'disciple_tools' ),
                'type'        => 'key_select',
                'default'     => [
                    'none'        => [
                      'label' => __( 'Contact Attempt Needed', 'disciple_tools' ),
                      'description' => ''
                    ],
                    'attempted'   => [
                      'label' => __( 'Contact Attempted', 'disciple_tools' ),
                      'description' => ''
                    ],
                    'established' => [
                      'label' => __( 'Contact Established', 'disciple_tools' ),
                      'description' => ''
                    ],
                    'scheduled'   => [
                      'label' => __( 'First Meeting Scheduled', 'disciple_tools' ),
                      'description' => ''
                    ],
                    'met'         => [
                      'label' => __( 'First Meeting Complete', 'disciple_tools' ),
                      'description' => ''
                    ],
                    'ongoing'     => [
                      'label' => __( 'Ongoing Meetings', 'disciple_tools' ),
                      'description' => ''
                    ],
                    'coaching'    => [
                      'label' => __( 'Being Coached', 'disciple_tools' ),
                      'description' => ''
                    ],
                ],
                'tile' => 'followup',
                'show_in_table' => 15,
                'only_for_types' => [ 'access' ],
                'icon' => get_template_directory_uri() . '/dt-assets/images/sign-post.svg?v=2',
            ];

            $fields['overall_status'] = [
                'name'        => __( 'Contact Status', 'disciple_tools' ),
                'type'        => 'key_select',
                'default_color' => '#366184',
                'default'     => [
                    'new'   => [], //already declared. Here to indicate order
                    'unassignable' => [
                        'label' => __( 'Not Ready', 'disciple_tools' ),
                        'description' => _x( 'There is not enough information to move forward with the contact at this time.', 'Contact Status field description', 'disciple_tools' ),
                        'color' => '#FF9800',
                    ],
                    'unassigned'   => [
                        'label' => __( 'Dispatch Needed', 'disciple_tools' ),
                        'description' => _x( 'This contact needs to be assigned to a multiplier.', 'Contact Status field description', 'disciple_tools' ),
                        'color' => '#F43636',
                    ],
                    'assigned'     => [
                        'label' => __( 'Waiting to be accepted', 'disciple_tools' ),
                        'description' => _x( 'The contact has been assigned to someone, but has not yet been accepted by that person.', 'Contact Status field description', 'disciple_tools' ),
                        'color' => '#FF9800',
                    ],
                    'active'       => [], //already declared. Here to indicate order
                    'paused'       => [
                        'label' => __( 'Paused', 'disciple_tools' ),
                        'description' => _x( 'This contact is currently on hold (i.e. on vacation or not responding).', 'Contact Status field description', 'disciple_tools' ),
                        'color' => '#FF9800',
                    ],
                    'closed' => [] //already declared. Here to indicate order
                ],
                'tile'     => 'status',
                'customizable' => 'add_only',
                'custom_display' => true,
                'icon' => get_template_directory_uri() . '/dt-assets/images/status.svg?v=2',
                'show_in_table' => 10,
                'only_for_types' => [ 'access' ],
                'select_cannot_be_empty' => true
            ];

            $fields['reason_unassignable'] = [
                'name'        => __( 'Reason Not Ready', 'disciple_tools' ),
                'description' => _x( 'The main reason the contact is not ready to be assigned to a user.', 'Optional Documentation', 'disciple_tools' ),
                'type'        => 'key_select',
                'default'     => [
                    'none'         => [
                        'label' => '',
                    ],
                    'insufficient' => [
                        'label' => __( 'Insufficient Contact Information', 'disciple_tools' )
                    ],
                    'location'     => [
                        'label' => __( 'Unknown Location', 'disciple_tools' )
                    ],
                    'media'        => [
                        'label' => __( 'Only wants media', 'disciple_tools' )
                    ],
                    'outside_area' => [
                        'label' => __( 'Outside Area', 'disciple_tools' )
                    ],
                    'needs_review' => [
                        'label' => __( 'Needs Review', 'disciple_tools' )
                    ],
                    'awaiting_confirmation' => [
                        'label' => __( 'Waiting for Confirmation', 'disciple_tools' )
                    ],
                ],
                'customizable' => 'all',
                'only_for_types' => [ 'access' ]
            ];

            $fields['reason_paused'] = [
                'name'        => __( 'Reason Paused', 'disciple_tools' ),
                'description' => _x( 'A paused contact is one you are not currently interacting with but expect to in the future.', 'Optional Documentation', 'disciple_tools' ),
                'type'        => 'key_select',
                'default' => [
                    'none'                 => [ 'label' => '' ],
                    'vacation'             => [ 'label' => _x( 'Contact on vacation', 'Reason Paused label', 'disciple_tools' ) ],
                    'not_responding'       => [ 'label' => _x( 'Contact not responding', 'Reason Paused label', 'disciple_tools' ) ],
                    'not_available'        => [ 'label' => _x( 'Contact not available', 'Reason Paused label', 'disciple_tools' ) ],
                    'little_interest'      => [ 'label' => _x( 'Contact has little interest/hunger', 'Reason Paused label', 'disciple_tools' ) ],
                    'no_initiative'        => [ 'label' => _x( 'Contact shows no initiative', 'Reason Paused label', 'disciple_tools' ) ],
                    'questionable_motives' => [ 'label' => _x( 'Contact has questionable motives', 'Reason Paused label', 'disciple_tools' ) ],
                    'ball_in_their_court'  => [ 'label' => _x( 'Ball is in the contact\'s court', 'Reason Paused label', 'disciple_tools' ) ],
                    'wait_and_see'         => [ 'label' => _x( 'We want to see if/how the contact responds to automated text messages', 'Reason Paused label', 'disciple_tools' ) ],
                ],
                'customizable' => 'all',
                'only_for_types' => [ 'access' ]
            ];

            $fields['reason_closed'] = [
                'name'        => __( 'Reason Archived', 'disciple_tools' ),
                'description' => _x( "A closed contact is one you can't or don't wish to interact with.", 'Optional Documentation', 'disciple_tools' ),
                'type'        => 'key_select',
                'default'     => [
                    'none'                 => [ 'label' => '' ],
                    'duplicate'            => [ 'label' => _x( 'Duplicate', 'Reason Closed label', 'disciple_tools' ) ],
                    'insufficient'         => [ 'label' => _x( 'Insufficient contact info', 'Reason Closed label', 'disciple_tools' ) ],
                    'denies_submission'    => [ 'label' => _x( 'Denies submitting contact request', 'Reason Closed label', 'disciple_tools' ) ],
                    'hostile_self_gain'    => [ 'label' => _x( 'Hostile, playing games or self gain', 'Reason Closed label', 'disciple_tools' ) ],
                    'apologetics'          => [ 'label' => _x( 'Only wants to argue or debate', 'Reason Closed label', 'disciple_tools' ) ],
                    'media_only'           => [ 'label' => _x( 'Just wanted media or book', 'Reason Closed label', 'disciple_tools' ) ],
                    'no_longer_interested' => [ 'label' => _x( 'No longer interested', 'Reason Closed label', 'disciple_tools' ) ],
                    'no_longer_responding' => [ 'label' => _x( 'No longer responding', 'Reason Closed label', 'disciple_tools' ) ],
                    'already_connected'    => [ 'label' => _x( 'Already in church or connected with others', 'Reason Closed label', 'disciple_tools' ) ],
                    'transfer'             => [ 'label' => _x( 'Transferred contact to partner', 'Reason Closed label', 'disciple_tools' ) ],
                    'martyred'             => [ 'label' => _x( 'Martyred', 'Reason Closed label', 'disciple_tools' ) ],
                    'moved'                => [ 'label' => _x( 'Moved or relocated', 'Reason Closed label', 'disciple_tools' ) ],
                    'gdpr'                 => [ 'label' => _x( 'GDPR request', 'Reason Closed label', 'disciple_tools' ) ],
                    'unknown'              => [ 'label' => _x( 'Unknown', 'Reason Closed label', 'disciple_tools' ) ]
                ],
                'customizable' => 'all',
                'only_for_types' => [ 'access' ]
            ];

            $fields['accepted'] = [
                'name'        => __( 'Accepted', 'disciple_tools' ),
                'type'        => 'boolean',
                'default'     => false,
                'hidden'      => true,
                'only_for_types' => [ 'access' ]
            ];

            $fields['campaigns'] = [
                'name' => __( 'Campaigns', 'disciple_tools' ),
                'description' => _x( 'Marketing campaigns or access activities that this contact interacted with.', 'Optional Documentation', 'disciple_tools' ),
                'tile' => 'details',
                'type'        => 'tags',
                'default'     => [],
                'icon' => get_template_directory_uri() . '/dt-assets/images/megaphone.svg?v=2',
                'only_for_types' => [ 'access' ],
            ];

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

            $declared_fields  = dt_array_merge_recursive_distinct( $declared_fields, $fields );

            //order overall status options
            uksort( $declared_fields['overall_status']['default'], function ( $a, $b ) use ( $fields ){
                return array_search( $a, array_keys( $fields['overall_status']['default'] ) ) <=> array_search( $b, array_keys( $fields['overall_status']['default'] ) );
            } );
            $fields = $declared_fields;
        }

        return $fields;
    }

    public function add_api_routes(){
        $namespace = 'dt-posts/v2';
        register_rest_route(
            $namespace, '/contacts/(?P<id>\d+)/accept', [
                'methods'  => 'POST',
                'callback' => [ $this, 'accept_contact' ],
                'permission_callback' => '__return_true',
            ]
        );

        register_rest_route(
            $namespace, '/contacts/assignment-list', [
                'methods'  => 'GET',
                'callback' => [ $this, 'get_dispatch_list' ],
                'permission_callback' => '__return_true',
            ]
        );
    }

    public function dt_details_additional_tiles( $sections, $post_type = '' ){
        if ( is_singular( 'contacts' ) ) {
            $contact = DT_Posts::get_post( 'contacts', get_the_ID() );
            if ( is_wp_error( $contact ) || ( isset( $contact['type']['key'] ) && $contact['type']['key'] !== 'access' ) ) {
                return $sections;
            }
        }
        if ( $post_type === 'contacts' ){
            $sections['followup'] = [
                'label' => __( 'Follow Up', 'disciple_tools' ),
                'display_for' => [
                    'type' => [ 'access' ],
                ]
            ];
        }
        return $sections;
    }

    public function dt_render_field_for_display_fields( $fields, $field_key, $post ){
        if ( $post && $post['post_type'] === 'contacts' && $field_key === 'assigned_to' ){
            $fields['assigned_to']['custom_display'] = true;
        }
        return $fields;
    }

    public function dt_render_field_for_display_template( $post, $field_type, $field_key, $required_tag, $display_field_id, $custom_display = false, $fields = [] ){
        $contact_fields = !empty( $fields ) ? $fields : DT_Posts::get_post_field_settings( 'contacts' );
        if ( isset( $post['post_type'] ) && isset( $post['ID'] ) ) {
            $can_update = DT_Posts::can_update( $post['post_type'], $post['ID'] );
        } else {
            $can_update = true;
        }
        $disabled = 'disabled';
        if ( $can_update || ( isset( $post['assigned_to']['id'] ) && $post['assigned_to']['id'] == get_current_user_id() ) ) {
            $disabled = '';
        }
        if ( isset( $post['post_type'] ) && $post['post_type'] === 'contacts' && $field_key === 'overall_status'
            && isset( $contact_fields[$field_key] ) && $custom_display
            && empty( $contact_fields[$field_key]['hidden'] )
            ){
            $contact = $post;
            if ( !dt_field_enabled_for_record_type( $contact_fields[$field_key], $post ) ){
                return;
            }
            ?>
                <div class="section-subheader">
                    <?php dt_render_field_icon( $contact_fields[$field_key] ) ?>
                    <?php echo esc_html( $contact_fields[$field_key]['name'] ) ?>
                </div>
                <?php
                $active_color = '#366184';
                $current_key = $contact['overall_status']['key'] ?? '';
                if ( isset( $contact_fields['overall_status']['default'][ $current_key ]['color'] ) ){
                    $active_color = $contact_fields['overall_status']['default'][ $current_key ]['color'];
                }
                ?>
                <select id="overall_status" class="select-field color-select" style="margin-bottom:0; background-color: <?php echo esc_html( $active_color ) ?>" <?php echo esc_html( $disabled ); ?>>
                    <?php foreach ( $contact_fields['overall_status']['default'] as $key => $option ){
                        $value = $option['label'] ?? '';
                        if ( $current_key === $key ) {
                            ?>
                            <option value="<?php echo esc_html( $key ) ?>" selected><?php echo esc_html( $value ); ?></option>
                        <?php } else { ?>
                            <option value="<?php echo esc_html( $key ) ?>"><?php echo esc_html( $value ); ?></option>
                        <?php } ?>
                    <?php } ?>
                </select>
                <p>
                    <span id="reason">
                        <?php
                        $hide_edit_button = false;
                        $status_key = isset( $contact['overall_status']['key'] ) ? $contact['overall_status']['key'] : '';
                        if ( $status_key === 'paused' &&
                            isset( $contact['reason_paused']['label'] ) ){
                            echo '(' . esc_html( $contact['reason_paused']['label'] ) . ')';
                        } else if ( $status_key === 'closed' &&
                            isset( $contact['reason_closed']['label'] ) ){
                            echo '(' . esc_html( $contact['reason_closed']['label'] ) . ')';
                        } else if ( $status_key === 'unassignable' &&
                            isset( $contact['reason_unassignable']['label'] ) ){
                            echo '(' . esc_html( $contact['reason_unassignable']['label'] ) . ')';
                        } else {
                            if ( !in_array( $status_key, [ 'paused', 'closed', 'unassignable' ] ) ){
                                $hide_edit_button = true;
                            }
                        }
                        ?>
                    </span>
                    <button id="edit-reason" <?php if ( $hide_edit_button ) : ?> style="display: none"<?php endif; ?> ><i class="fi-pencil"></i></button>
                </p>
            <div class="reveal" id="paused-contact-modal" data-reveal>
                <h3><?php echo esc_html( $contact_fields['reason_paused']['name'] ?? '' )?></h3>
                <p><?php echo esc_html( $contact_fields['reason_paused']['description'] ?? '' )?></p>
                <p><?php esc_html_e( 'Choose an option:', 'disciple_tools' )?></p>

                <select id="reason-paused-options">
                    <?php
                    foreach ( $contact_fields['reason_paused']['default'] as $reason_key => $option ) {
                        if ( $option['label'] ) {
                            ?>
                            <option value="<?php echo esc_attr( $reason_key ) ?>"
                                <?php if ( ( $contact['reason_paused']['key'] ?? '' ) === $reason_key ) {
                                    echo 'selected';
                                } ?>>
                                <?php echo esc_html( $option['label'] ?? '' ) ?>
                            </option>
                            <?php
                        }
                    }
                    ?>
                </select>
                <button class="button button-cancel clear" data-close aria-label="Close reveal" type="button">
                    <?php echo esc_html__( 'Cancel', 'disciple_tools' )?>
                </button>
                <button class="button loader confirm-reason-button" type="button" id="confirm-pause" data-field="paused">
                    <?php echo esc_html__( 'Confirm', 'disciple_tools' )?>
                </button>
                <button class="close-button" data-close aria-label="<?php esc_html_e( 'Close', 'disciple_tools' ); ?>" type="button">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="reveal" id="unassignable-contact-modal" data-reveal>
                <h3><?php echo esc_html( $contact_fields['reason_unassignable']['name'] ?? '' )?></h3>
                <p><?php echo esc_html( $contact_fields['reason_unassignable']['description'] ?? '' )?></p>
                <p><?php esc_html_e( 'Choose an option:', 'disciple_tools' )?></p>

                <select id="reason-unassignable-options">
                    <?php
                    foreach ( $contact_fields['reason_unassignable']['default'] as $reason_key => $option ) {
                        if ( isset( $option['label'] ) ) {
                            ?>
                            <option value="<?php echo esc_attr( $reason_key ) ?>"
                                <?php if ( ( $contact['unassignable_paused']['key'] ?? '' ) === $reason_key ) {
                                    echo 'selected';
                                } ?>>
                                <?php echo esc_html( $option['label'] ?? '' ) ?>
                            </option>
                            <?php
                        }
                    }
                    ?>
                </select>
                <button class="button button-cancel clear" data-close aria-label="Close reveal" type="button">
                    <?php echo esc_html__( 'Cancel', 'disciple_tools' )?>
                </button>
                <button class="button loader confirm-reason-button" type="button" id="confirm-unassignable" data-field="unassignable">
                    <?php echo esc_html__( 'Confirm', 'disciple_tools' )?>
                </button>
                <button class="close-button" data-close aria-label="<?php esc_html_e( 'Close', 'disciple_tools' ); ?>" type="button">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="reveal" id="closed-contact-modal" data-reveal>
                <h3><?php echo esc_html( $contact_fields['reason_closed']['name'] ?? '' )?></h3>
                <p><?php echo esc_html( $contact_fields['reason_closed']['description'] ?? '' )?></p>
                <p><?php esc_html_e( 'Choose an option:', 'disciple_tools' )?></p>

                <select id="reason-closed-options">
                    <?php
                    foreach ( $contact_fields['reason_closed']['default'] as $reason_key => $option ) {
                        if ( !empty( $option['label'] ) ) {
                            $selected = ( $reason_key === ( $contact['reason_closed']['key'] ?? '' ) ) ? 'selected' : '';
                            ?>
                            <option
                                value="<?php echo esc_attr( $reason_key ) ?>" <?php echo esc_html( $selected ) ?>> <?php echo esc_html( $option['label'] ?? '' ) ?></option>
                            <?php
                        }
                    }
                    ?>
                </select>
                <button class="button button-cancel clear" data-close aria-label="Close reveal" type="button">
                    <?php echo esc_html__( 'Cancel', 'disciple_tools' )?>
                </button>
                <button class="button loader confirm-reason-button" type="button" id="confirm-close" data-field="closed">
                    <?php echo esc_html__( 'Confirm', 'disciple_tools' )?>
                </button>
                <button class="close-button" data-close aria-label="<?php esc_html_e( 'Close', 'disciple_tools' ); ?>" type="button">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <?php
        }


        if ( isset( $post['post_type'] ) && $post['post_type'] === 'contacts' && $field_key === 'assigned_to'
            && isset( $contact_fields[$field_key] ) && !empty( $contact_fields[$field_key]['custom_display'] )
            && empty( $contact_fields[$field_key]['hidden'] ) ){
            $button_class =( current_user_can( 'dt_all_access_contacts' ) || current_user_can( 'list_users' ) ) ? 'advanced_user_select' : 'search_assigned_to'
            ?>
            <div class="section-subheader">
                <img src="<?php echo esc_url( $contact_fields[$field_key]['icon'] ) ?>">
                <?php echo esc_html( $contact_fields[$field_key]['name'] ) ?>
            </div>
            <div id="<?php echo esc_html( $field_key ); ?>" class="<?php echo esc_html( $display_field_id ); ?> dt_user_select">
                <var id="<?php echo esc_html( $display_field_id ); ?>-result-container" class="result-container <?php echo esc_html( $display_field_id ); ?>-result-container"></var>
                <div id="<?php echo esc_html( $display_field_id ); ?>_t" name="form-<?php echo esc_html( $display_field_id ); ?>" class="scrollable-typeahead">
                    <div class="typeahead__container" style="margin-bottom: 0">
                        <div class="typeahead__field">
                            <span class="typeahead__query">
                                <input class="js-typeahead-<?php echo esc_html( $display_field_id ); ?> input-height" dir="auto"
                                       name="<?php echo esc_html( $display_field_id ); ?>[query]" placeholder="<?php echo esc_html_x( 'Search Users', 'input field placeholder', 'disciple_tools' ) ?>"
                                       data-field_type="user_select"
                                       data-field="<?php echo esc_html( $field_key ); ?> <?php echo esc_html( $disabled ); ?>"
                                       autocomplete="off" <?php echo esc_html( $disabled ); ?>>
                            </span>
                            <span class="typeahead__button">
                                <button type="button" class="<?php echo esc_html( $button_class ); ?> typeahead__image_button input-height" data-id="<?php echo esc_html( $field_key ); ?>" <?php echo esc_html( $disabled ); ?>>
                                    <i class="fi-magnifying-glass"></i>
                                </button>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            <?php
            $is_dispatcher = dt_current_user_has_role( 'dispatcher' ) || current_user_can( 'dt_all_access_contacts' );
            $roles = [
                'multiplier' => [
                    'label' => __( 'Multipliers', 'disciple_tools' )
                ],
                'dispatcher' => [
                    'label' => __( 'Dispatchers', 'disciple_tools' )
                ],
                'marketer' => [
                    'label' => __( 'Digital Responders', 'disciple_tools' )
                ],
            ];
            if ( $is_dispatcher ) { ?>
            <div class="reveal" id="assigned_to_user_modal" data-reveal>
                <section class="small-12 grid-y grid-margin-y cell dispatcher-tile">
                    <div class="cell dt-filter-tabs">
                        <h4 class="section-header"><?php esc_html_e( 'Assign To', 'disciple_tools' ); ?> <span id="dispatch-tile-loader" style="display: inline-block; margin-left: 10px" class="loading-spinner"></span></h4>
                        <div class="section-body">
                            <ul class="horizontal tabs" data-tabs id="assign-role-tabs">
                                <?php foreach ( $roles as $key => $value ) : ?>
                                    <li class="tabs-title <?php echo esc_html( $key === 'multiplier' ? 'is-active' : '' ); ?>">
                                        <a href="#<?php echo esc_html( $key ); ?>" data-field="<?php echo esc_html( $key ); ?>">
                                            <?php echo esc_html( $value['label'] ); ?>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                            <div class="tabs-column-right users-select-panel" style="margin-top:20px; display: none">
                                <div id="defined-lists" style="padding-top:0">
                                    <div class="grid-x grid-margin-x" style="margin-top:5px">
                                        <div class="medium-4 cell">
                                            <div class="input-group">
                                                <input id="search-users-filtered" class="input-group-field" type="text" placeholder="Multipliers" <?php echo esc_html( $disabled ); ?>>
                                                <div class="input-group-button">
                                                    <button type="button" class="button hollow"><i class="fi-magnifying-glass"></i></button>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="medium-8 cell">
                                            <div id="user-list-filters" style="margin-bottom:3px">
                                                <!--filters is filled out by js-->
                                            </div>
                                            <div class="populated-list">
                                                <!--users list is filled out by js-->
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
                <button class="close-button" data-close aria-label="<?php esc_html_e( 'Close', 'disciple_tools' ); ?>" type="button">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <?php }
        }
    }


    /**
     * Notification Banner for Accepting an assigned contact
     * @param $post_type
     * @param $contact
     * @return void
     */
    public function dt_record_top_above_details( $post_type, $contact ){
        if ( $post_type === 'contacts' && isset( $contact['type'] ) && $contact['type']['key'] === 'access' ) {
            $current_user = wp_get_current_user();
            if ( isset( $contact['overall_status'] ) && $contact['overall_status']['key'] == 'assigned' &&
                isset( $contact['assigned_to'] ) && $contact['assigned_to']['id'] == $current_user->ID ) { ?>
                <section class="cell accept-contact" id="accept-contact">
                    <div class="bordered-box detail-notification-box">
                        <h4><?php esc_html_e( 'This contact has been assigned to you.', 'disciple_tools' )?></h4>
                        <button class="accept-button button small accept-decline" data-action="accept"><?php esc_html_e( 'Accept', 'disciple_tools' )?></button>
                        <button class="decline-button button small accept-decline" data-action="decline"><?php esc_html_e( 'Decline', 'disciple_tools' )?></button>
                    </div>
                </section>
                <?php
            }
        }
    }


    public function dt_post_update_fields( $fields, $post_type, $post_id, $existing_post ){
        if ( $post_type === 'contacts' ){
            if ( ( !isset( $existing_post['type']['key'] ) || $existing_post['type']['key'] !== 'access' ) && ( !isset( $fields['type'] ) || $fields['type'] !== 'access' ) ){
                return $fields;
            }
            //make sure an access contact is assigned to a user
            if ( isset( $fields['assigned_to'] ) ) {
                if ( !isset( $existing_post['assigned_to'] ) || $fields['assigned_to'] !== $existing_post['assigned_to']['assigned-to'] ){
                    $user_id = dt_get_user_id_from_assigned_to( $fields['assigned_to'] );
                    if ( !isset( $fields['overall_status'] ) && ( !isset( $existing_post['overall_status']['key'] ) || $existing_post['overall_status']['key'] !== 'closed' ) ){
                        if ( $user_id != get_current_user_id() ){
                            if ( current_user_can( 'assign_any_contacts' ) ) {
                                $fields['overall_status'] = 'assigned';
                            }
                            $fields['accepted'] = false;
                        } elseif ( isset( $existing_post['overall_status']['key'] ) && $existing_post['overall_status']['key'] === 'assigned' ) {
                            $fields['overall_status'] = 'active';
                        }
                    }
                    if ( $user_id ){
                        DT_Posts::add_shared( 'contacts', $post_id, $user_id, null, false, true, false );
                    }
                }
            }
            if ( isset( $fields['seeker_path'] ) ){
                self::update_quick_action_buttons( $post_id, $fields['seeker_path'] );
            }
            foreach ( $fields as $field_key => $value ){
                if ( strpos( $field_key, 'quick_button' ) !== false ){
                    self::handle_quick_action_button_event( $post_id, [ $field_key => $value ] );
                }
            }
            if ( isset( $fields['overall_status'], $fields['reason_paused'] ) && $fields['overall_status'] === 'paused' ){
                $fields['requires_update'] = false;
            }
            if ( isset( $fields['overall_status'], $fields['reason_closed'] ) && $fields['overall_status'] === 'closed' ){
                $fields['requires_update'] = false;
            }
            //if a contact type is changed to access
            if ( isset( $fields['type'] ) && $fields['type'] === 'access' ){
                //set the status to active if there is no status
                if ( !isset( $existing_post['overall_status'] ) && !isset( $fields['overall_status'] ) ){
                    $fields['overall_status'] = 'active';
                }
                //assign the contact to the user
                if ( !isset( $existing_post['assigned_to'] ) && !isset( $fields['assigned_to'] ) && get_current_user_id() ){
                    $fields['assigned_to'] = get_current_user_id();
                }
            }
        }
        return $fields;
    }

    public function dt_comment_created( $post_type, $post_id, $created_comment_id, $comment_type ){
        if ( $post_type === 'contacts' ){
            if ( $comment_type === 'comment' ){
                self::check_requires_update( $post_id );
            }
        }
    }

    // Add, remove or modify fields before the fields are processed on post create.
    public function dt_post_create_fields( $fields, $post_type ){
        if ( $post_type !== 'contacts' ){
            return $fields;
        }
        if ( isset( $fields['additional_meta']['created_from'] ) ){
            $from_post = DT_Posts::get_post( 'contacts', $fields['additional_meta']['created_from'], true, false );
            if ( !is_wp_error( $from_post ) && isset( $from_post['type']['key'] ) && in_array( $from_post['type']['key'], [ 'access', 'access_placeholder', 'user' ] ) ){
                $fields['type'] = 'access_placeholder';
            }
        }


        /**
         * Stop here if the type is not "access"
         */
        if ( !isset( $fields['type'] ) || $fields['type'] !== 'access' ){
            return $fields;
        }
        if ( !isset( $fields['assigned_to'] ) ){
            if ( get_current_user_id() ) {
                $fields['assigned_to'] = sprintf( 'user-%d', get_current_user_id() );
            } else {
                $base_id = dt_get_base_user( true );
                if ( is_wp_error( $base_id ) ) { // if default editor does not exist, get available administrator
                    $users = get_users( [ 'role' => 'administrator' ] );
                    if ( count( $users ) > 0 ) {
                        foreach ( $users as $user ) {
                            $base_id = $user->ID;
                        }
                    }
                }
                if ( !empty( $base_id ) ){
                    $fields['assigned_to'] = sprintf( 'user-%d', $base_id );
                }
            }
        }
        if ( !isset( $fields['seeker_path'] ) ){
            $fields['seeker_path'] = 'none';
        }

        if ( !isset( $fields['sources'] ) ) {
            $fields['sources'] = [ 'values' => [ [ 'value' => 'personal' ] ] ];
        }
        return $fields;
    }

    //Runs after fields are processed on update
    public function dt_post_updated( $post_type, $post_id, $initial_request_fields, $post_fields_before_update, $contact ){
        if ( $post_type === 'contacts' ){
            if ( !isset( $contact['type']['key'] ) || $contact['type']['key'] !== 'access' ){
                return;
            }
            self::check_seeker_path( $post_id, $contact, $post_fields_before_update );
        }
    }



    /**
     * Make sure activity is created for all the steps before the current seeker path
     *
     * @param $contact_id
     * @param $contact
     * @param $previous_values
     */
    public function check_seeker_path( $contact_id, $contact, $previous_values ){
        if ( isset( $contact['seeker_path']['key'] ) && $contact['seeker_path']['key'] != 'none' ){
            $current_key = $contact['seeker_path']['key'];
            $prev_key = isset( $previous_values['seeker_path']['key'] ) ? $previous_values['seeker_path']['key'] : 'none';
            $field_settings = DT_Posts::get_post_field_settings( 'contacts' );
            $seeker_path_options = $field_settings['seeker_path']['default'];
            $option_keys = array_keys( $seeker_path_options );
            $current_index = array_search( $current_key, $option_keys );
            $prev_option_key = $option_keys[ $current_index - 1 ];

            if ( $prev_option_key != $prev_key && $current_index > array_search( $prev_key, $option_keys ) ){
                global $wpdb;
                $seeker_path_activity = $wpdb->get_results( $wpdb->prepare( "
                    SELECT meta_value, hist_time, meta_id
                    FROM $wpdb->dt_activity_log
                    WHERE object_id = %s
                    AND meta_key = 'seeker_path'
                ", $contact_id), ARRAY_A );
                $existing_keys = [];
                $most_recent = 0;
                $meta_id = 0;
                foreach ( $seeker_path_activity as $activity ){
                    $existing_keys[] = $activity['meta_value'];
                    if ( $activity['hist_time'] > $most_recent ){
                        $most_recent = $activity['hist_time'];
                    }
                    $meta_id = $activity['meta_id'];
                }
                $activity_to_create = [];
                for ( $i = $current_index; $i > 0; $i-- ){
                    if ( !in_array( $option_keys[$i], $existing_keys ) ){
                        $activity_to_create[] = $option_keys[$i];
                    }
                }
                foreach ( $activity_to_create as $missing_key ){
                    $wpdb->query( $wpdb->prepare("
                        INSERT INTO $wpdb->dt_activity_log
                        ( action, object_type, object_subtype, object_id, user_id, hist_time, meta_id, meta_key, meta_value, field_type )
                        VALUES ( 'field_update', 'contacts', 'seeker_path', %s, %d, %d, %d, 'seeker_path', %s, 'key_select' )",
                        $contact_id, get_current_user_id(), $most_recent - 1, $meta_id, $missing_key
                    ));
                }
            }
        }
    }

    //list page filters function
    public static function dt_user_list_filters( $filters, $post_type ){
        if ( $post_type === 'contacts' ){
            $performance_mode = get_option( 'dt_performance_mode', false );
            $fields = DT_Posts::get_post_field_settings( $post_type );

            /**
             * Setup my contacts filters
             */
            if ( !$performance_mode ){
                $counts = self::get_my_contacts_status_seeker_path();
                $active_counts = [];
                $update_needed = 0;
                $status_counts = [];
                $total_my = 0;
                foreach ( $counts as $count ){
                    $total_my += $count['count'];
                    dt_increment( $status_counts[$count['overall_status']], $count['count'] );
                    if ( $count['overall_status'] === 'active' ){
                        if ( isset( $count['update_needed'] ) ) {
                            $update_needed += (int) $count['update_needed'];
                        }
                        dt_increment( $active_counts[$count['seeker_path']], $count['count'] );
                    }
                }
                if ( !isset( $status_counts['closed'] ) ) {
                    $status_counts['closed'] = '';
                }
            }

            foreach ( $fields['overall_status']['default'] as $status_key => $status_value ) {
                if ( isset( $status_counts[$status_key] ) || $performance_mode ) {
                    $filters['filters'][] = [
                        'ID' => 'my_' . $status_key,
                        'tab' => 'default',
                        'name' => $status_value['label'],
                        'query' => [
                            'assigned_to' => [ 'me' ],
                            'subassigned' => [ 'me' ],
                            'combine' => [ 'subassigned' ],
                            'type' => [ 'access' ],
                            'overall_status' => [ $status_key ],
                            'sort' => 'seeker_path'
                        ],
                        'labels' => [
                            [ 'name' => $status_value['label'] ],
                            [ 'name' => __( 'Assigned to me', 'disciple_tools' ), 'field' => 'assigned_to', 'id' => 'me' ],
                            [ 'name' => __( 'Sub-assigned to me', 'disciple_tools' ), 'field' => 'subassigned', 'id' => 'me' ],
                        ],
                        'count' => $status_counts[$status_key] ?? '',
                        'subfilter' => 1
                    ];
                    if ( $status_key === 'active' ){
                        if ( ( $update_needed ?? 0 ) > 0 ){
                            $filters['filters'][] = [
                                'ID' => 'my_update_needed',
                                'tab' => 'default',
                                'name' => $fields['requires_update']['name'],
                                'query' => [
                                    'assigned_to' => [ 'me' ],
                                    'subassigned' => [ 'me' ],
                                    'combine' => [ 'subassigned' ],
                                    'overall_status' => [ 'active' ],
                                    'requires_update' => [ true ],
                                    'type' => [ 'access' ],
                                    'sort' => 'seeker_path'
                                ],
                                'labels' => [
                                    [ 'name' => $fields['requires_update']['name'] ],
                                    [ 'name' => __( 'Assigned to me', 'disciple_tools' ), 'field' => 'assigned_to', 'id' => 'me' ],
                                    [ 'name' => __( 'Sub-assigned to me', 'disciple_tools' ), 'field' => 'subassigned', 'id' => 'me' ],
                                ],
                                'count' => $update_needed ?? '',
                                'subfilter' => 2
                            ];
                        }
                        if ( isset( $fields['seeker_path']['default'] ) && is_array( $fields['seeker_path']['default'] ) ){
                            foreach ( $fields['seeker_path']['default'] as $seeker_path_key => $seeker_path_value ){
                                if ( isset( $active_counts[$seeker_path_key] ) || $performance_mode ){
                                    $filters['filters'][] = [
                                        'ID' => 'my_' . $seeker_path_key,
                                        'tab' => 'default',
                                        'name' => $seeker_path_value['label'],
                                        'query' => [
                                            'assigned_to' => [ 'me' ],
                                            'subassigned' => [ 'me' ],
                                            'combine' => [ 'subassigned' ],
                                            'overall_status' => [ 'active' ],
                                            'seeker_path' => [ $seeker_path_key ],
                                            'type' => [ 'access' ],
                                            'sort' => 'name'
                                        ],
                                        'labels' => [
                                            [ 'name' => $seeker_path_value['label'] ],
                                            [ 'name' => __( 'Assigned to me', 'disciple_tools' ), 'field' => 'assigned_to', 'id' => 'me' ],
                                            [ 'name' => __( 'Sub-assigned to me', 'disciple_tools' ), 'field' => 'subassigned', 'id' => 'me' ],
                                        ],
                                        'count' => $active_counts[$seeker_path_key] ?? '',
                                        'subfilter' => 2
                                    ];
                                }
                            }
                        }
                    }
                }
            }

            /**
             * Setup dispatcher filters
             */
            if ( current_user_can( 'dt_all_access_contacts' ) || current_user_can( 'access_specific_sources' ) ) {
                if ( !$performance_mode ){
                    $counts = self::get_all_contacts_status_seeker_path();
                    $all_active_counts = [];
                    $all_update_needed = 0;
                    $all_status_counts = [];
                    $total_all = 0;
                    foreach ( $counts as $count ){
                        $total_all += $count['count'];
                        dt_increment( $all_status_counts[$count['overall_status']], $count['count'] );
                        if ( $count['overall_status'] === 'active' ){
                            if ( isset( $count['update_needed'] ) ) {
                                $all_update_needed += (int) $count['update_needed'];
                            }
                            dt_increment( $all_active_counts[$count['seeker_path']], $count['count'] );
                        }
                    }
                    if ( !isset( $all_status_counts['closed'] ) ) {
                        $all_status_counts['closed'] = '';
                    }
                }
                $filters['tabs'][] = [
                    'key' => 'all_dispatch',
//                    "label" => __( "Follow-Up", 'disciple_tools' ),
                    'label' => sprintf( _x( 'Follow-Up %s', 'All records', 'disciple_tools' ), DT_Posts::get_post_settings( $post_type )['label_plural'] ),
                    'count' => $total_all ?? '',
                    'order' => 10
                ];
                // add assigned to me filters
                $filters['filters'][] = [
                    'ID' => 'all_dispatch',
                    'tab' => 'all_dispatch',
                    'name' => __( 'All Follow-Up', 'disciple_tools' ),
                    'query' => [
                        'overall_status' => [ '-closed' ],
                        'type' => [ 'access' ],
                        'sort' => 'overall_status'
                    ],
                    'count' => $total_all ?? '',
                ];

                foreach ( $fields['overall_status']['default'] as $status_key => $status_value ) {
                    if ( isset( $all_status_counts[$status_key] ) || $performance_mode ) {
                        $filters['filters'][] = [
                            'ID' => 'all_' . $status_key,
                            'tab' => 'all_dispatch',
                            'name' => $status_value['label'],
                            'query' => [
                                'overall_status' => [ $status_key ],
                                'type' => [ 'access' ],
                                'sort' => 'seeker_path'
                            ],
                            'count' => $all_status_counts[$status_key] ?? ''
                        ];
                        if ( $status_key === 'active' ){
                            if ( ( $all_update_needed ?? 0 ) > 0 || $performance_mode ){
                                $filters['filters'][] = [
                                    'ID' => 'all_update_needed',
                                    'tab' => 'all_dispatch',
                                    'name' => $fields['requires_update']['name'],
                                    'query' => [
                                        'overall_status' => [ 'active' ],
                                        'requires_update' => [ true ],
                                        'type' => [ 'access' ],
                                        'sort' => 'seeker_path'
                                    ],
                                    'count' => $all_update_needed ?? '',
                                    'subfilter' => true
                                ];
                            }
                            if ( isset( $fields['seeker_path']['default'] ) && is_array( $fields['seeker_path']['default'] ) ) {
                                foreach ( $fields['seeker_path']['default'] as $seeker_path_key => $seeker_path_value ) {
                                    if ( isset( $all_active_counts[$seeker_path_key] ) || $performance_mode ) {
                                        $filters['filters'][] = [
                                            'ID' => 'all_' . $seeker_path_key,
                                            'tab' => 'all_dispatch',
                                            'name' => $seeker_path_value['label'],
                                            'query' => [
                                                'overall_status' => [ 'active' ],
                                                'seeker_path' => [ $seeker_path_key ],
                                                'type' => [ 'access' ],
                                                'sort' => 'name'
                                            ],
                                            'count' => $all_active_counts[$seeker_path_key] ?? '',
                                            'subfilter' => true
                                        ];
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        return $filters;
    }


    //list page filters function
    private static function get_all_contacts_status_seeker_path(){
        global $wpdb;
        $results = [];

        $can_view_all = false;
        if ( current_user_can( 'access_specific_sources' ) ) {
            $sources = get_user_option( 'allowed_sources', get_current_user_id() ) ?: [];
            if ( empty( $sources ) || in_array( 'all', $sources ) ) {
                $can_view_all = true;
            }
        }

        if ( current_user_can( 'dt_all_access_contacts' ) || $can_view_all ) {
            $results = $wpdb->get_results("
                SELECT status.meta_value as overall_status, pm.meta_value as seeker_path, count(pm.post_id) as count, count(un.post_id) as update_needed
                FROM $wpdb->postmeta pm
                INNER JOIN $wpdb->postmeta status ON( status.post_id = pm.post_id AND status.meta_key = 'overall_status' AND status.meta_value != 'closed' )
                INNER JOIN $wpdb->posts a ON( a.ID = pm.post_id AND a.post_type = 'contacts' and a.post_status = 'publish' )
                INNER JOIN $wpdb->postmeta type ON ( type.post_id = pm.post_id AND type.meta_key = 'type' AND type.meta_value = 'access' )
                LEFT JOIN $wpdb->postmeta un ON ( un.post_id = pm.post_id AND un.meta_key = 'requires_update' AND un.meta_value = '1' )
                WHERE pm.meta_key = 'seeker_path'
                GROUP BY status.meta_value, pm.meta_value
            ", ARRAY_A);
        } else if ( current_user_can( 'access_specific_sources' ) ) {
            $sources = get_user_option( 'allowed_sources', get_current_user_id() ) ?: [];
            $sources_sql = dt_array_to_sql( $sources );
            // phpcs:disable
            $results = $wpdb->get_results( $wpdb->prepare( "
                SELECT status.meta_value as overall_status, pm.meta_value as seeker_path, count(pm.post_id) as count, count(un.post_id) as update_needed
                FROM $wpdb->postmeta pm
                INNER JOIN $wpdb->postmeta status ON( status.post_id = pm.post_id AND status.meta_key = 'overall_status' AND status.meta_value != 'closed' )
                INNER JOIN $wpdb->posts a ON( a.ID = pm.post_id AND a.post_type = 'contacts' and a.post_status = 'publish' )
                INNER JOIN $wpdb->postmeta type ON ( type.post_id = pm.post_id AND type.meta_key = 'type'  AND type.meta_value = 'access'  )
                LEFT JOIN $wpdb->postmeta un ON ( un.post_id = pm.post_id AND un.meta_key = 'requires_update' AND un.meta_value = '1' )
                WHERE pm.meta_key = 'seeker_path'
                AND (
                    pm.post_id IN ( SELECT post_id from $wpdb->postmeta as source where source.meta_value IN ( $sources_sql ) )
                    OR pm.post_id IN ( SELECT post_id FROM $wpdb->dt_share AS shares where shares.user_id = %s )
                )
                GROUP BY status.meta_value, pm.meta_value
            ", esc_sql( get_current_user_id() ) ) , ARRAY_A );
            // phpcs:enable
        }
        return $results;
    }

    //list page filters function
    private static function get_my_contacts_status_seeker_path(){
        global $wpdb;
        $user_post = Disciple_Tools_Users::get_contact_for_user( get_current_user_id() ) ?? 0;
        $results = $wpdb->get_results( $wpdb->prepare( "
            SELECT status.meta_value as overall_status, pm.meta_value as seeker_path, count(pm.post_id) as count, count(un.post_id) as update_needed
            FROM $wpdb->postmeta pm
            INNER JOIN $wpdb->postmeta status ON( status.post_id = pm.post_id AND status.meta_key = 'overall_status' AND status.meta_value != 'closed')
            INNER JOIN $wpdb->posts a ON( a.ID = pm.post_id AND a.post_type = 'contacts' and a.post_status = 'publish' )
            LEFT JOIN $wpdb->postmeta un ON ( un.post_id = pm.post_id AND un.meta_key = 'requires_update' AND un.meta_value = '1' )
            INNER JOIN $wpdb->postmeta type ON ( type.post_id = pm.post_id AND type.meta_key = 'type' AND type.meta_value = 'access' )
            WHERE pm.meta_key = 'seeker_path'
            AND (
                pm.post_id IN ( SELECT post_id FROM $wpdb->postmeta WHERE meta_key = 'assigned_to' AND meta_value = CONCAT( 'user-', %s ) )
                OR pm.post_id IN ( SELECT p2p_to from $wpdb->p2p WHERE p2p_from = %s AND p2p_type = 'contacts_to_subassigned' )
            )
            GROUP BY status.meta_value, pm.meta_value
        ", get_current_user_id(), $user_post ), ARRAY_A);
        return $results;
    }

    public static function dt_filter_access_permissions( $permissions, $post_type ){
        if ( $post_type === 'contacts' ){
            if ( DT_Posts::can_view_all( $post_type ) ){
                $permissions['type'] = [ 'access', 'user', 'access_placeholder' ];
            } else if ( current_user_can( 'dt_all_access_contacts' ) ){
                //give user permission to all contacts af type 'access'
                $permissions[] = [ 'type' => [ 'access', 'user', 'access_placeholder' ] ];
            } else if ( current_user_can( 'access_specific_sources' ) ){
                //give user permission to all 'access' that also have a source the user can view.
                $allowed_sources = get_user_option( 'allowed_sources', get_current_user_id() ) ?: [];
                if ( empty( $allowed_sources ) || in_array( 'all', $allowed_sources, true ) ){
                    $permissions['type'] = [ 'access', 'access_placeholder' ];
                } elseif ( !in_array( 'restrict_all_sources', $allowed_sources ) ){
                    $permissions[] = [ 'type' => [ 'access' ], 'sources' => $allowed_sources ];
                }
            }
        }
        return $permissions;
    }

    // filter for access to a specific record
    public function can_view_permission_filter( $has_permission, $post_id, $post_type ){
        if ( $post_type === 'contacts' ){
            if ( current_user_can( 'dt_all_access_contacts' ) ){
                $contact_type = get_post_meta( $post_id, 'type', true );
                if ( $contact_type === 'access' || $contact_type === 'user' || $contact_type === 'access_placeholder' ){
                    return true;
                }
            }
            //check if the user has access to all posts of a specific source
            if ( current_user_can( 'access_specific_sources' ) ){
                $contact_type = get_post_meta( $post_id, 'type', true );
                if ( $contact_type === 'access' || $contact_type === 'access_placeholder' ){
                    $sources = get_user_option( 'allowed_sources', get_current_user_id() ) ?: [];
                    if ( empty( $sources ) || in_array( 'all', $sources ) ) {
                        return true;
                    }
                    $post_sources = get_post_meta( $post_id, 'sources' );
                    foreach ( $post_sources as $s ){
                        if ( in_array( $s, $sources ) ){
                            return true;
                        }
                    }
                }
            }
        }
        return $has_permission;
    }
    public function can_update_permission_filter( $has_permission, $post_id, $post_type ){
        if ( current_user_can( 'dt_all_access_contacts' ) ){
            $contact_type = get_post_meta( $post_id, 'type', true );
            if ( $contact_type === 'access' || $contact_type === 'user' || $contact_type === 'access_placeholder' ){
                return true;
            }
        }
        //check if the user has access to all posts of a specific source
        if ( current_user_can( 'access_specific_sources' ) ){
            $contact_type = get_post_meta( $post_id, 'type', true );
            if ( $contact_type === 'access' || $contact_type === 'access_placeholder' ){
                $sources = get_user_option( 'allowed_sources', get_current_user_id() ) ?: [];
                if ( empty( $sources ) || in_array( 'all', $sources ) ){
                    return true;
                }
                $post_sources = get_post_meta( $post_id, 'sources' );
                foreach ( $post_sources as $s ){
                    if ( in_array( $s, $sources ) ){
                        return true;
                    }
                }
            }
        }
        return $has_permission;
    }

    public function scripts(){
        if ( is_singular( 'contacts' ) && get_the_ID() && DT_Posts::can_view( $this->post_type, get_the_ID() ) ){
            wp_enqueue_script( 'dt_contacts_access', get_template_directory_uri() . '/dt-contacts/module-access.js', [
                'jquery',
            ], filemtime( get_theme_file_path() . '/dt-contacts/module-access.js' ), true );
            wp_localize_script( 'dt_contacts_access', 'dt_contacts_access', [
                'translations' => [
                    'all' => __( 'All', 'disciple_tools' ),
                    'ready' => __( 'Ready', 'disciple_tools' ),
                    'recent' => __( 'Recent', 'disciple_tools' ),
                    'location' => __( 'Location', 'disciple_tools' ),
                    'assign' => __( 'Assign', 'disciple_tools' ),
                    'language' => __( 'Language', 'disciple_tools' ),
                    'gender' => __( 'Gender', 'disciple_tools' ),
                ],
            ] );
        }
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
                    <?php foreach ( $contact_fields as $field => $val ){
                        if ( strpos( $field, 'quick_button' ) === 0 ){
                            $current_value = 0;
                            if ( isset( $contact[$field] ) ){
                                $current_value = $contact[$field];
                            } ?>
                            <li class="quick-action-menu" data-id="<?php echo esc_attr( $field ) ?>">
                              <a>
                                  <?php dt_render_field_icon( $val ); ?>
                                  <?php echo esc_html( $val['name'] ); ?>
                                (<span
                                  class="<?php echo esc_attr( $field ) ?>"><?php echo esc_html( $current_value ); ?></span>)
                              </a>
                            </li>
                        <?php }
                    } ?>
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

    private static function handle_quick_action_button_event( int $contact_id, array $field, bool $check_permissions = true ) {
        $update = [];
        $key = key( $field );

        if ( $key == 'quick_button_no_answer' ) {
            $update['seeker_path'] = 'attempted';
        } elseif ( $key == 'quick_button_phone_off' ) {
            $update['seeker_path'] = 'attempted';
        } elseif ( $key == 'quick_button_contact_established' ) {
            $update['seeker_path'] = 'established';
        } elseif ( $key == 'quick_button_meeting_scheduled' ) {
            $update['seeker_path'] = 'scheduled';
        } elseif ( $key == 'quick_button_meeting_complete' ) {
            $update['seeker_path'] = 'met';
        }

        if ( isset( $update['seeker_path'] ) ) {
            self::check_requires_update( $contact_id );
            return self::update_seeker_path( $contact_id, $update['seeker_path'], $check_permissions );
        } else {
            return $contact_id;
        }
    }

    public static function update_quick_action_buttons( $contact_id, $seeker_path ){
        if ( $seeker_path === 'established' ){
            $quick_button = get_post_meta( $contact_id, 'quick_button_contact_established', true );
            if ( empty( $quick_button ) || $quick_button == '0' ){
                update_post_meta( $contact_id, 'quick_button_contact_established', '1' );
            }
        }
        if ( $seeker_path === 'scheduled' ){
            $quick_button = get_post_meta( $contact_id, 'quick_button_meeting_scheduled', true );
            if ( empty( $quick_button ) || $quick_button == '0' ){
                update_post_meta( $contact_id, 'quick_button_meeting_scheduled', '1' );
            }
        }
        if ( $seeker_path === 'met' ){
            $quick_button = get_post_meta( $contact_id, 'quick_button_meeting_complete', true );
            if ( empty( $quick_button ) || $quick_button == '0' ){
                update_post_meta( $contact_id, 'quick_button_meeting_complete', '1' );
            }
        }
        self::check_requires_update( $contact_id );
    }

    private static function update_seeker_path( int $contact_id, string $path_option, $check_permissions = true ) {
        $contact_fields = DT_Posts::get_post_field_settings( 'contacts' );
        $seeker_path_options = $contact_fields['seeker_path']['default'];
        $option_keys = array_keys( $seeker_path_options );
        $current_seeker_path = get_post_meta( $contact_id, 'seeker_path', true );
        $current_index = array_search( $current_seeker_path, $option_keys );
        $new_index = array_search( $path_option, $option_keys );
        if ( $new_index > $current_index ) {
            $current_index = $new_index;
            $update = DT_Posts::update_post( 'contacts', $contact_id, [ 'seeker_path' => $path_option ], $check_permissions );
            if ( is_wp_error( $update ) ) {
                return $update;
            }
            $current_seeker_path = $path_option;
        }

        return [
            'currentKey' => $current_seeker_path,
            'current' => $seeker_path_options[ $option_keys[ $current_index ] ],
            'next'    => isset( $option_keys[ $current_index + 1 ] ) ? $seeker_path_options[ $option_keys[ $current_index + 1 ] ] : '',
        ];
    }

    //check to see if the contact is marked as needing an update
    //if yes: mark as updated
    private static function check_requires_update( $contact_id ){
        if ( get_current_user_id() ){
            $requires_update = get_post_meta( $contact_id, 'requires_update', true );
            if ( $requires_update == 'yes' || $requires_update == true || $requires_update == '1' ){
                //don't remove update needed if the user is a dispatcher (and not assigned to the contacts.)
                if ( current_user_can( 'dt_all_access_contacts' ) ){
                    if ( dt_get_user_id_from_assigned_to( get_post_meta( $contact_id, 'assigned_to', true ) ) === get_current_user_id() ){
                        update_post_meta( $contact_id, 'requires_update', false );
                    }
                } else {
                    update_post_meta( $contact_id, 'requires_update', false );
                }
            }
        }
    }

    public static function accept_contact( WP_REST_Request $request ){
        $params = $request->get_params();
        $body = $request->get_json_params() ?? $request->get_body_params();
        if ( !isset( $params['id'] ) ) {
            return new WP_Error( 'accept_contact', 'Missing a valid contact id', [ 'status' => 400 ] );
        } else {
            $contact_id = $params['id'];
            $accepted = $body['accept'];
            if ( !DT_Posts::can_update( 'contacts', $contact_id ) ) {
                return new WP_Error( __FUNCTION__, 'You do not have permission for this', [ 'status' => 403 ] );
            }

            if ( $accepted ) {
                $update = [
                    'overall_status' => 'active',
                    'accepted' => true
                ];
                dt_activity_insert(
                    [
                        'action'         => 'assignment_accepted',
                        'object_type'    => get_post_type( $contact_id ),
                        'object_subtype' => '',
                        'object_name'    => get_the_title( $contact_id ),
                        'object_id'      => $contact_id,
                        'meta_id'        => '', // id of the comment
                        'meta_key'       => '',
                        'meta_value'     => '',
                        'meta_parent'    => '',
                        'object_note'    => '',
                    ]
                );
                return DT_Posts::update_post( 'contacts', $contact_id, $update, true );
            } else {
                $assign_to_id = 0;
                $last_activity = DT_Posts::get_most_recent_activity_for_field( $contact_id, 'assigned_to' );
                if ( isset( $last_activity->user_id ) ){
                    $assign_to_id = $last_activity->user_id;
                } else {
                    $base_user = dt_get_base_user( true );
                    if ( $base_user ){
                        $assign_to_id = $base_user;
                    }
                }

                $update = [
                    'assigned_to' => $assign_to_id,
                    'overall_status' => 'unassigned'
                ];
                $contact = DT_Posts::update_post( 'contacts', $contact_id, $update, true );
                $current_user = wp_get_current_user();
                dt_activity_insert(
                    [
                        'action'         => 'assignment_decline',
                        'object_type'    => get_post_type( $contact_id ),
                        'object_subtype' => 'decline',
                        'object_name'    => get_the_title( $contact_id ),
                        'object_id'      => $contact_id,
                        'meta_id'        => '', // id of the comment
                        'meta_key'       => '',
                        'meta_value'     => '',
                        'meta_parent'    => '',
                        'object_note'    => ''
                    ]
                );
                Disciple_Tools_Notifications::insert_notification_for_assignment_declined( $current_user->ID, $assign_to_id, $contact_id );
                return $contact;
            }
        }
    }


    public function get_dispatch_list( WP_REST_Request $request ) {
        if ( !current_user_can( 'dt_all_access_contacts' ) || !current_user_can( 'list_users' ) ){
            return new WP_Error( __FUNCTION__, __( 'No permission' ), [ 'status' => 403 ] );
        }
        $params = $request->get_query_params();

        $user_data = DT_User_Management::get_users( true );

        $last_assignments = $this->get_assignments();
        $location_data = $this->get_location_data( $params['location_ids'] );
        $gender_data = $this->get_gender_data();

        $list = [];
        $user_fields = Disciple_Tools_Users::get_users_fields();
        $workload_status_options = $user_fields['workload_status']['options'];
        foreach ( $user_data as $user ) {
            $roles = maybe_unserialize( $user['roles'] );
            if ( isset( $roles['multiplier'] ) || isset( $roles['dt_admin'] ) || isset( $roles['dispatcher'] ) || isset( $roles['marketer'] ) ) {
                $u = [
                    'name' => wp_specialchars_decode( $user['display_name'] ),
                    'ID' => $user['ID'],
                    'avatar' => get_avatar_url( $user['ID'], [ 'size' => '16', 'scheme' => 'https' ] ),
                    'last_assignment' => $last_assignments[$user['ID']] ?? null,
                    'roles' => array_keys( $roles ),
                    'location' => null,
                    'languages' => [],
                    'gender' => null,
                ];
                $user_languages = get_user_option( 'user_languages', $user['ID'] );
                if ( $user_languages ) {
                    $u['languages'] = $user_languages;
                }
                //extra information for the dispatcher
                $workload_status = $user['workload_status'] ?? null;
                if ( $workload_status && isset( $workload_status_options[$workload_status]['color'] ) ) {
                    $u['status'] = $workload_status;
                    $u['status_color'] = $workload_status_options[$workload_status]['color'];
                }
                if ( isset( $location_data[$user['ID']] ) ){
                    $u['location'] = $location_data[$user['ID']]['level'];
                    $u['best_location_match'] = $location_data[$user['ID']]['match_name'];
                }
                if ( isset( $gender_data[$user['ID']] ) ) {
                    $u['gender'] = $gender_data[$user['ID']];
                }

                $u['update_needed'] = (int) $user['number_update'] ?? 0;

                $list[] = $u;
            }
        }

        return apply_filters( 'dt_get_dispatch_list', $list, $params['post_type'], $params['post_id'] );
    }

    private function get_assignments() {
        global $wpdb;
        $last_assignment_query = $wpdb->get_results( "
            SELECT meta_value as user, MAX(hist_time) as assignment_date
            from $wpdb->dt_activity_log as log
            WHERE meta_key = 'assigned_to'
            GROUP by meta_value",
        ARRAY_A );
        $last_assignments =[];
        foreach ( $last_assignment_query as $assignment ){
            $user_id = str_replace( 'user-', '', $assignment['user'] );
            $last_assignments[$user_id] = $assignment['assignment_date'];
        }

        return $last_assignments;
    }

    private function get_location_data( $location_ids ) {
        global $wpdb;

        $location_data = [];
        if ( isset( $location_ids ) ) {
            foreach ( $location_ids as $grid_id ){
                $location = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $wpdb->dt_location_grid WHERE grid_id = %s", esc_sql( $grid_id ) ), ARRAY_A );
                $levels = [];

                if ( $grid_id === '1' ){
                    $match_location_ids = '( 1 )';
                } else {
                    $match_location_ids = '( ';
                    for ( $i = 0; $i <= ( (int) $location['level'] ); $i++ ) {
                        $levels[ $location['admin'. $i . '_grid_id']] = [ 'level' => $i ];
                        $match_location_ids .= $location['admin'. $i . '_grid_id'] . ', ';
                    }
                    $match_location_ids .= ')';

                }

                $match_location_ids = str_replace( ', )', ' )', $match_location_ids );
                //phpcs:disable
                //already sanitized IN value
                $location_names = $wpdb->get_results( "
                    SELECT alt_name, grid_id
                    FROM $wpdb->dt_location_grid
                    WHERE grid_id IN $match_location_ids
                ", ARRAY_A);

                //get users with the same location grid.
                $users_in_location = $wpdb->get_results( $wpdb->prepare("
                    SELECT user_id, meta_value as grid_id
                    FROM $wpdb->usermeta um
                    WHERE um.meta_key = %s
                    AND um.meta_value IN $match_location_ids
                ", "{$wpdb->prefix}location_grid"), ARRAY_A );
                //phpcs:enable

                foreach ( $location_names as $l ){
                    if ( isset( $levels[$l['grid_id']] ) ) {
                        $levels[$l['grid_id']]['name'] = $l['alt_name'];
                    }
                }

                //0 if the location is exact match. 1 if the matched location is the parent etc
                foreach ( $users_in_location as $l ){
                    $level = (int) $location['level'] - $levels[$l['grid_id']]['level'];
                    if ( !isset( $location_data[$l['user_id']] ) || $location_data[$l['user_id']]['level'] > $level ){
                        $location_data[$l['user_id']] = [
                            'level' => $level,
                            'match_name' => $levels[$l['grid_id']]['name']
                        ];
                    }
                }
            }
        }
        return $location_data;
    }

    private function get_gender_data() {
        global $wpdb;
        $gender_data = [];

        $gender_query = $wpdb->get_results( $wpdb->prepare("
            SELECT user_id, meta_value as gender
            from $wpdb->usermeta
            WHERE meta_key = %s", "{$wpdb->prefix}user_gender"),
        ARRAY_A );

        foreach ( $gender_query as $data ){
            $gender_data[$data['user_id']] = $data['gender'];
        }

        return $gender_data;
    }


    /**
     * Fields to add by the ACCESS module
     * @param $fields
     * @return mixed
     */
    public function dt_users_fields( $fields ){
        $fields['number_new_assigned'] = [
            'label' => 'Accept Needed',
            'type' => 'number',
            'table' => 'postmeta',
            'meta_key' => 'overall_status',
            'meta_value' => 'assigned',
        ];
        $fields['number_active'] = [
            'label' => 'Active',
            'type' => 'number',
            'table' => 'postmeta',
            'meta_key' => 'overall_status',
            'meta_value' => 'active',
        ];
        $fields['number_assigned_to'] = [
            'label' => 'Assigned',
            'type' => 'number',
            'table' => 'postmeta',
            'meta_key' => 'assigned_to',
            'hidden' => true,
        ];
        $fields['number_update'] = [
            'label' => 'Update Needed',
            'type' => 'number',
            'table' => 'postmeta',
            'meta_key' => 'requires_update',
            'meta_value' => '1',
        ];


        return $fields;
    }
}
