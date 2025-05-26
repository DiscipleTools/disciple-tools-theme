<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly.

class DT_Contacts_Base {
    private static $_instance = null;
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    } // End instance()

    public $post_type = 'contacts';

    public function __construct() {
        //setup post type
        add_action( 'after_setup_theme', [ $this, 'after_setup_theme' ], 100 );
        add_filter( 'dt_set_roles_and_permissions', [ $this, 'dt_set_roles_and_permissions' ], 10, 1 );
        add_filter( 'dt_capabilities', [ $this, 'dt_capabilities' ], 100, 1 );

        //setup tiles and fields
        add_action( 'wp_enqueue_scripts', [ $this, 'scripts' ], 99 );
        add_filter( 'dt_custom_fields_settings', [ $this, 'dt_custom_fields_settings' ], 5, 2 );
        add_filter( 'dt_custom_fields_settings_after_combine', [ $this, 'dt_custom_fields_settings_after_combine' ], 10, 2 );
        add_filter( 'dt_details_additional_tiles', [ $this, 'dt_details_additional_tiles' ], 10, 2 );
        add_filter( 'dt_details_additional_tiles', [ $this, 'dt_details_additional_tiles_after' ], 100, 2 );
        add_action( 'dt_record_admin_actions', [ $this, 'dt_record_admin_actions' ], 10, 2 );
        add_action( 'dt_record_footer', [ $this, 'dt_record_footer' ], 10, 2 );
        add_action( 'dt_record_notifications_section', [ $this, 'dt_record_notifications_section' ], 10, 2 );
        add_filter( 'dt_record_icon', [ $this, 'dt_record_icon' ], 10, 3 );
        add_filter( 'dt_get_post_type_settings', [ $this, 'dt_get_post_type_settings' ], 20, 2 );

        // hooks
        add_action( 'rest_api_init', [ $this, 'add_api_routes' ] );
        add_action( 'post_connection_removed', [ $this, 'post_connection_removed' ], 10, 4 );
        add_action( 'post_connection_added', [ $this, 'post_connection_added' ], 10, 4 );
        add_filter( 'dt_post_update_fields', [ $this, 'update_post_field_hook' ], 10, 3 );
        add_filter( 'dt_post_updated', [ $this, 'dt_post_updated' ], 10, 5 );
        add_action( 'dt_post_created', [ $this, 'dt_post_created' ], 10, 3 );
        add_filter( 'dt_post_create_fields', [ $this, 'dt_post_create_fields' ], 20, 2 );
        add_filter( 'dt_comments_additional_sections', [ $this, 'add_comm_channel_comment_section' ], 100, 2 );


        //list
        add_filter( 'dt_user_list_filters', [ $this, 'dt_user_list_filters' ], 10, 2 );
        add_filter( 'dt_search_viewable_posts_query', [ $this, 'dt_search_viewable_posts_query' ], 10, 1 );

        //notifications
        add_filter( 'dt_filter_users_receiving_comment_notification', [ $this, 'dt_filter_users_receiving_comment_notification' ], 10, 4 );
    }


    public function after_setup_theme(){
        if ( class_exists( 'Disciple_Tools_Post_Type_Template' ) ) {
            new Disciple_Tools_Post_Type_Template( 'contacts', __( 'Contact', 'disciple_tools' ), __( 'Contacts', 'disciple_tools' ) );
        }
    }

    /**
     * Set the singular and plural translations for this post types settings
     * The add_filter is set onto a higher priority than the one in Disciple_tools_Post_Type_Template
     * so as to enable localisation changes. Otherwise the system translation passed in to the custom post type
     * will prevail.
     */
    public function dt_get_post_type_settings( $settings, $post_type ){
        if ( $post_type === $this->post_type ){
            $settings['label_singular'] = __( 'Contact', 'disciple_tools' );
            $settings['label_plural'] = __( 'Contacts', 'disciple_tools' );
            $settings['status_field'] = [
                'status_key' => 'overall_status',
                'archived_key' => 'closed',
            ];
        }
        return $settings;
    }

    public function dt_capabilities( $capabilities ){
        $capabilities['dt_all_access_' . $this->post_type] = [
            'source' => DT_Posts::get_label_for_post_type( $this->post_type, false, false ),
            'label' => 'Manage all access and media contacts',
            'description' => 'View and update all access and media contacts',
            'post_type' => $this->post_type
        ];
        if ( isset( $capabilities['view_any_contacts'] ) ){
            $capabilities['view_any_contacts']['label'] = 'View all, including private';
            $capabilities['view_any_contacts']['description'] = 'The user can view any contact, including private contacts';
        }
        return $capabilities;
    }

    public function dt_set_roles_and_permissions( $expected_roles ){
        foreach ( $expected_roles as $role_key => $role ){
            if ( isset( $role['type'] ) && in_array( 'base', $role['type'], true ) ){
                $expected_roles[$role_key]['permissions']['access_contacts'] = true;
                $expected_roles[$role_key]['permissions']['create_contacts'] = true;
            }
        }

        $expected_roles['administrator']['permissions']['dt_all_admin_contacts'] = true;
        $expected_roles['administrator']['permissions']['delete_any_contacts'] = true;

        return $expected_roles;
    }

    public function dt_custom_fields_settings( $fields, $post_type ){
        if ( $post_type === 'contacts' ){
            $fields['nickname'] = [
                'name' => __( 'Nickname', 'disciple_tools' ),
                'type' => 'text',
                'tile' => 'details',
                'icon' => get_template_directory_uri() . '/dt-assets/images/nametag.svg?v=2',
            ];
            $post_type_settings = get_option( 'dt_custom_post_types', [] );
            $private_contacts_enabled = $post_type_settings['contacts']['enable_private_contacts'] ?? false;
            $fields['type'] = [
                'name'        => __( 'Contact Type', 'disciple_tools' ),
                'type'        => 'key_select',
                'default'     => [
                    'user' => [
                        'label' => __( 'User', 'disciple_tools' ),
                        'description' => __( 'Representing a User in the system', 'disciple_tools' ),
                        'color' => '#3F729B',
                        'hidden' => true,
                        'in_create_form' => false,
                    ],
                    'personal' => [
                        'label' => __( 'Private Contact', 'disciple_tools' ),
                        'color' => '#9b379b',
                        'description' => __( 'A friend, family member or acquaintance', 'disciple_tools' ),
                        'visibility' => __( 'Only me', 'disciple_tools' ),
                        'icon' => get_template_directory_uri() . '/dt-assets/images/locked.svg?v=2',
                        'order' => 50,
                        'hidden' => !$private_contacts_enabled,
                        'default' => false
                    ],
                    'access' => [
                        'label' => __( 'Standard Contact', 'disciple_tools' ),
                        'color' => '#2196F3',
                        'description' => __( 'A contact to collaborate on', 'disciple_tools' ),
                        'visibility' => __( 'Me and project leadership', 'disciple_tools' ),
                        'icon' => get_template_directory_uri() . '/dt-assets/images/share.svg?v=2',
                        'order' => 20,
                        'default' => true,
                    ],
                    'access_placeholder' => [
                        'label' => __( 'Connection', 'disciple_tools' ),
                        'color' => '#FF9800',
                        'description' => __( 'Connected to a contact, or generational fruit', 'disciple_tools' ),
                        'icon' => get_template_directory_uri() . '/dt-assets/images/share.svg?v=2',
                        'order' => 40,
                        'visibility' => __( 'Collaborators', 'disciple_tools' ),
                        'in_create_form' => false,
                    ],
                    'placeholder' => [
                        'label' => __( 'Private Connection', 'disciple_tools' ),
                        'color' => '#FF9800',
                        'description' => __( 'Connected to a contact, or generational fruit', 'disciple_tools' ),
                        'icon' => get_template_directory_uri() . '/dt-assets/images/locked.svg?v=2',
                        'order' => 40,
                        'visibility' => __( 'Only me', 'disciple_tools' ),
                        'in_create_form' => false,
                        'hidden' => !$private_contacts_enabled,
                    ],
                ],
                'description' => 'See full documentation here: https://disciple.tools/docs/contact-types',
                'icon' => get_template_directory_uri() . '/dt-assets/images/circle-square-triangle.svg?v=2',
                'customizable' => false
            ];

            $fields['duplicate_data'] = [
                'name' => 'Duplicates', //system string does not need translation
                'type' => 'array',
                'default' => [],
                'hidden' => true
            ];
            $fields['duplicate_of'] = [
                'name' => 'Duplicate of', //system string does not need translation
                'type' => 'text',
                'hidden' => true,
                'customizable' => false,
            ];

            $fields['languages'] = [
                'name' => __( 'Languages', 'disciple_tools' ),
                'type' => 'multi_select',
                'default' => dt_get_option( 'dt_working_languages' ) ?: [],
                'icon' => get_template_directory_uri() . '/dt-assets/images/languages.svg?v=2',
                'tile' => 'no_tile'
            ];

            //add communication channels
            $fields['contact_phone'] = [
                'name' => __( 'Phone', 'disciple_tools' ),
                'icon' => get_template_directory_uri() . '/dt-assets/images/phone.svg?v=2',
                'type' => 'communication_channel',
                'tile' => 'details',
                'customizable' => true,
                'in_create_form' => true,
                'messagingServices' => [
                    'Signal' => [
                        'name' => __( 'Signal', 'disciple_tools' ),
                        'link' => 'https://signal.me/#p/PHONE_NUMBER',
                        'icon' => get_template_directory_uri() . '/dt-assets/images/signal.svg'
                    ],
                    'Viber' => [
                        'name' => __( 'Viber', 'disciple_tools' ),
                        'link' => 'viber://chat?number=PHONE_NUMBER',
                        'icon' => get_template_directory_uri() . '/dt-assets/images/viber.svg'
                    ],
                    'Whatsapp' => [
                        'name' => __( 'WhatsApp', 'disciple_tools' ),
                        'link' => 'https://api.whatsapp.com/send?phone=PHONE_NUMBER_NO_PLUS',
                        'icon' => get_template_directory_uri() . '/dt-assets/images/whatsapp.svg'
                    ],
                ]
            ];
            $fields['contact_email'] = [
                'name' => __( 'Email', 'disciple_tools' ),
                'icon' => get_template_directory_uri() . '/dt-assets/images/email.svg?v=2',
                'type' => 'communication_channel',
                'tile' => 'details',
                'in_create_form' => true,
                'customizable' => true
            ];

            $fields['contact_address'] = [
                'name' => __( 'Address', 'disciple_tools' ),
                'icon' => get_template_directory_uri() . '/dt-assets/images/house.svg?v=2',
                'type' => 'communication_channel',
                'tile' => 'details',
                'mapbox'    => false,
                'in_create_form' => true,
                'customizable' => true
            ];
            if ( DT_Mapbox_API::get_key() ){
                $fields['contact_address']['custom_display'] = true;
                $fields['contact_address']['mapbox'] = true;
                $fields['contact_address']['hidden'] = true;
                unset( $fields['contact_address']['tile'] );
            }

            // add social media
            $fields['contact_facebook'] = [
                'name' => __( 'Facebook', 'disciple_tools' ),
                'icon' => get_template_directory_uri() . '/dt-assets/images/facebook.svg?v=2',
                'hide_domain' => true,
                'type' => 'communication_channel',
                'tile' => 'details',
                'customizable' => true
            ];
            $fields['contact_other'] = [
                'name' => __( 'Other Social Links', 'disciple_tools' ),
                'icon' => get_template_directory_uri() . '/dt-assets/images/chat.svg?v=2',
                'hide_domain' => false,
                'type' => 'communication_channel',
                'tile' => 'details',
                'customizable' => true
            ];

            $fields['relation'] = [
                'name' => sprintf( _x( 'Connections to other %s', 'connections to other records', 'disciple_tools' ), __( 'Contacts', 'disciple_tools' ) ),
                'description' => _x( 'Relationship this contact has with another contact in the system.', 'Optional Documentation', 'disciple_tools' ),
                'type' => 'connection',
                'post_type' => 'contacts',
                'p2p_direction' => 'any',
                'p2p_key' => 'contacts_to_relation',
                'tile' => 'other',
                'in_create_form' => [ 'placeholder' ],
                'icon' => get_template_directory_uri() . '/dt-assets/images/connection-people.svg?v=2',
            ];

            $fields['gender'] = [
                'name'        => __( 'Gender', 'disciple_tools' ),
                'type'        => 'key_select',
                'default'     => [
                    'male'    => [ 'label' => __( 'Male', 'disciple_tools' ) ],
                    'female'  => [ 'label' => __( 'Female', 'disciple_tools' ) ],
                ],
                'tile'     => 'details',
                'icon' => get_template_directory_uri() . '/dt-assets/images/gender-male-female.svg',
            ];

            $fields['age'] = [
                'name'        => __( 'Age', 'disciple_tools' ),
                'type'        => 'key_select',
                'default'     => [
                    'not-set' => [ 'label' => '' ],
                    '<19'     => [ 'label' => __( 'Under 18 years old', 'disciple_tools' ) ],
                    '<26'     => [ 'label' => __( '18-25 years old', 'disciple_tools' ) ],
                    '<41'     => [ 'label' => __( '26-40 years old', 'disciple_tools' ) ],
                    '>41'     => [ 'label' => __( 'Over 40 years old', 'disciple_tools' ) ],
                ],
                'tile'     => 'details',
                'icon' => get_template_directory_uri() . '/dt-assets/images/contact-age.svg?v=2',
                'select_cannot_be_empty' => true //backwards compatible since we already have an "none" value
            ];

            $fields['requires_update'] = [
                'name'        => __( 'Requires Update', 'disciple_tools' ),
                'type'        => 'boolean',
                'default'     => false,
            ];

            $fields['overall_status'] = [
                'name' => __( 'Contact Status', 'disciple_tools' ),
                'description' => _x( 'The Contact Status describes the progress in communicating with the contact.', 'Contact Status field description', 'disciple_tools' ),
                'tile'     => 'status',
                'type' => 'key_select',
                'select_cannot_be_empty' => true,
                'default_color' => '#4CAF50',
                'default' => [
                    'active'       => [
                        'label' => __( 'Active', 'disciple_tools' ),
                        'description' => _x( 'The contact is progressing and/or continually being updated.', 'Contact Status field description', 'disciple_tools' ),
                        'color' => '#4CAF50',
                        'default' => true,
                    ],
                    'new'   => [
                        'label' => __( 'New Contact', 'disciple_tools' ),
                        'description' => _x( 'The contact is new in the system.', 'Contact Status field description', 'disciple_tools' ),
                        'color' => '#F43636',
                    ],
                    'closed' => [
                        'label' => __( 'Archived', 'disciple_tools' ),
                        'color' => '#808080',
                        'description' => _x( 'This contact has made it known that they no longer want to continue or you have decided not to continue with him/her.', 'Contact Status field description', 'disciple_tools' ),
                    ]
                ]
            ];

            $fields['assigned_to'] = [
                'name'        => __( 'Assigned To', 'disciple_tools' ),
                'description' => __( 'Select the main person who is responsible for reporting on this contact.', 'disciple_tools' ),
                'type'        => 'user_select',
                'default'     => '',
                'tile'        => 'status',
                'icon' => get_template_directory_uri() . '/dt-assets/images/assigned-to.svg?v=2',
                'show_in_table' => 25,
                'custom_display' => false
            ];

            $fields['subassigned'] = [
                'name' => __( 'Sub-assigned to', 'disciple_tools' ),
                'description' => __( 'Contact or User assisting the Assigned To user to follow up with the contact.', 'disciple_tools' ),
                'type' => 'connection',
                'post_type' => 'contacts',
                'p2p_direction' => 'to',
                'p2p_key' => 'contacts_to_subassigned',
                'tile' => 'status',
                'custom_display' => false,
                'icon' => get_template_directory_uri() . '/dt-assets/images/subassigned.svg?v=2',
            ];

            $fields['subassigned_on'] = [
                'name' => __( 'Sub-assigned on other Contacts', 'disciple_tools' ),
                'description' => __( 'Contacts this contacts is subassigned on', 'disciple_tools' ),
                'type' => 'connection',
                'post_type' => 'contacts',
                'p2p_direction' => 'from',
                'p2p_key' => 'contacts_to_subassigned',
                'tile' => 'no_tile',
                'custom_display' => false,
                'icon' => get_template_directory_uri() . '/dt-assets/images/subassigned.svg?v=2',
            ];

            $sources_default = [
                'personal'           => [
                    'label'       => __( 'Personal', 'disciple_tools' ),
                    'key'         => 'personal',
                ],
                'web'           => [
                    'label'       => __( 'Web', 'disciple_tools' ),
                    'key'         => 'web',
                ],
                'transfer' => [
                    'label'       => __( 'Transfer', 'disciple_tools' ),
                    'key'         => 'transfer',
                    'description' => __( 'Contacts transferred from a partnership with another Disciple.Tools site.', 'disciple_tools' ),
                ]
            ];
            foreach ( dt_get_option( 'dt_site_custom_lists' )['sources'] as $key => $value ) {
                if ( !isset( $sources_default[$key] ) ) {
                    if ( isset( $value['enabled'] ) && $value['enabled'] === false ) {
                        $value['deleted'] = true;
                    }
                    $sources_default[ $key ] = $value;
                }
            }

            $fields['sources'] = [
                'name'        => __( 'Sources', 'disciple_tools' ),
                'description' => _x( 'The website, event or location this contact came from.', 'Optional Documentation', 'disciple_tools' ),
                'type'        => 'multi_select',
                'default'     => $sources_default,
                'tile'     => 'details',
                'customizable' => 'all',
                'display' => 'typeahead',
                'icon' => get_template_directory_uri() . '/dt-assets/images/arrow-collapse-all.svg?v=2',
                'only_for_types' => [ 'access' ],
                'in_create_form' => [ 'access' ]
            ];
        }
        return $fields;
    }

    /**
     * Filter that runs after the default fields and custom settings have been combined
     * @param $fields
     * @param $post_type
     * @return mixed
     */
    public function dt_custom_fields_settings_after_combine( $fields, $post_type ){
        if ( $post_type === 'contacts' ){
            //make sure disabled communication channels also have the hidden field set
            foreach ( $fields as $field_key => $field_value ){
                if ( isset( $field_value['type'] ) && $field_value['type'] === 'communication_channel' ){
                    if ( isset( $field_value['enabled'] ) && $field_value['enabled'] === false ){
                        $fields[$field_key]['hidden'] = true;
                    }
                }
            }
        }
        return $fields;
    }

    public static function dt_record_admin_actions( $post_type, $post_id ){
        if ( $post_type === 'contacts' ){
            $post = DT_Posts::get_post( $post_type, $post_id );
            if ( empty( $post['archive'] ) && isset( $post['type']['key'] ) && ( $post['type']['key'] === 'personal' || $post['type']['key'] === 'placeholder' ) ) :?>
                <li>
                    <a data-open="archive-record-modal">
                        <img class="dt-icon" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/archive.svg?v=2' ) ?>"/>
                        <?php echo esc_html( sprintf( _x( 'Archive %s', 'Archive Contact', 'disciple_tools' ), DT_Posts::get_post_settings( $post_type )['label_singular'] ) ) ?></a>
                </li>
            <?php endif; ?>

            <li>
                <a data-open="contact-type-modal">
                    <img class="dt-icon" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/circle-square-triangle.svg?v=2' ) ?>"/>
                    <?php echo esc_html( sprintf( _x( 'Change %s Type', 'Change Record Type', 'disciple_tools' ), DT_Posts::get_post_settings( $post_type )['label_singular'] ) ) ?></a>
            </li>
            <li><a data-open="merge-dupe-edit-modal">
                    <img class="dt-icon"
                         src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/duplicate.svg?v=2' ) ?>"/>

                    <?php esc_html_e( 'See duplicates', 'disciple_tools' ) ?></a></li>
            <?php
        }
    }


    public function dt_record_footer( $post_type, $post_id ){
        if ( $post_type === 'contacts' ) :
            //revert modal
            get_template_part( 'dt-assets/parts/modals/modal', 'revert' );

            $contact_fields = DT_Posts::get_post_field_settings( $post_type );
            $post = DT_Posts::get_post( $post_type, $post_id );

            //replace urls with links
            $url = '@(http)?(s)?(://)?(([a-zA-Z])([-\w]+\.)+([^\s\.]+[^\s]*)+[^,.\s])@';
            $contact_fields['type']['description'] = preg_replace( $url, '<a href="http$2://$4" target="_blank" title="$0">$0</a>', $contact_fields['type']['description'] );
            ?>
            <div class="reveal" id="archive-record-modal" data-reveal data-reset-on-close>
                <h3><?php echo esc_html( sprintf( _x( 'Archive %s', 'Archive Contact', 'disciple_tools' ), DT_Posts::get_post_settings( $post_type )['label_singular'] ) ) ?></h3>
                <p><?php echo esc_html( sprintf( _x( 'Are you sure you want to archive %s?', 'Are you sure you want to archive name?', 'disciple_tools' ), $post['name'] ) ) ?></p>

                <div class="grid-x">
                    <button class="button button-cancel clear" data-close aria-label="Close reveal" type="button">
                        <?php echo esc_html__( 'Cancel', 'disciple_tools' )?>
                    </button>
                    <button class="button alert loader" type="button" id="archive-record">
                        <?php esc_html_e( 'Archive', 'disciple_tools' ); ?>
                    </button>
                    <button class="close-button" data-close aria-label="<?php esc_html_e( 'Close', 'disciple_tools' ); ?>" type="button">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            </div>

            <div class="reveal" id="contact-type-modal" data-reveal>
                <h3><?php echo esc_html( $contact_fields['type']['name'] ?? '' )?></h3>
                <p><?php echo nl2br( wp_kses_post( $contact_fields['type']['description'] ?? '' ) )?></p>
                <p><?php esc_html_e( 'Choose an option:', 'disciple_tools' )?></p>

                <select id="type-options">
                    <?php
                    foreach ( $contact_fields['type']['default'] as $option_key => $option ) {
                        if ( !empty( $option['label'] ) && ( !isset( $option['hidden'] ) || $option['hidden'] !== true ) ) {
                            $selected = ( $option_key === ( $post['type']['key'] ?? '' ) ) ? 'selected' : '';
                            ?>
                            <option value="<?php echo esc_attr( $option_key ) ?>" <?php echo esc_html( $selected ) ?>>
                                <?php echo esc_html( $option['label'] ?? '' ) ?>
                                <?php if ( !empty( $option['description'] ) ){
                                    echo esc_html( ' - ' . $option['description'] ?? '' );
                                } ?>
                            </option>
                            <?php
                        }
                    }
                    ?>
                </select>

                <button class="button button-cancel clear" data-close aria-label="Close reveal" type="button">
                    <?php echo esc_html__( 'Cancel', 'disciple_tools' )?>
                </button>
                <button class="button loader" type="button" id="confirm-type-close" data-field="closed">
                    <?php echo esc_html__( 'Confirm', 'disciple_tools' )?>
                </button>
                <button class="close-button" data-close aria-label="<?php esc_html_e( 'Close', 'disciple_tools' ); ?>" type="button">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <script type="text/javascript">
                jQuery('#confirm-type-close').on('click', function(){
                    $(this).toggleClass('loading')
                    API.update_post('contacts', <?php echo esc_html( GET_THE_ID() ); ?>, {type:$('#type-options').val()}).then(contactData=>{
                        window.location.reload()
                    }).catch(err => { console.error(err) })
                })
            </script>
        <?php endif;
    }


    public function dt_details_additional_tiles( $tiles, $post_type = '' ){
        return $tiles;
    }

    public function dt_details_additional_tiles_after( $tiles, $post_type = '' ){
        if ( $post_type === 'contacts' ){
            $tiles['other'] = [ 'label' => __( 'Other', 'disciple_tools' ) ];
        }
        return $tiles;
    }


    public function post_connection_added( $post_type, $post_id, $post_key, $value ){
        if ( $post_type === 'contacts' ){
            if ( $post_key === 'subassigned' ){
                $user_id = get_post_meta( $value, 'corresponds_to_user', true );
                if ( $user_id ){
                    DT_Posts::add_shared( $post_type, $post_id, $user_id, null, false, false, false );
                    Disciple_Tools_Notifications::insert_notification_for_subassigned( $user_id, $post_id );
                }
            }
        }
    }
    public function post_connection_removed( $post_type, $post_id, $post_key, $value ){
    }

    // Runs after post is created and fields are processed.
    public function dt_post_created( $post_type, $post_id, $initial_request_fields ){
        if ( $post_type === 'contacts' ){
            $post = DT_Posts::get_post( $post_type, $post_id, true, false );
            if ( !isset( $post['type']['key'] ) || $post['type']['key'] !== 'access' ){
                return;
            }
            //check for duplicate along other access contacts
            $this->check_for_duplicates( $post_type, $post_id );
        }
    }

    public function update_post_field_hook( $fields, $post_type, $post_id ){
        return $fields;
    }

    public function dt_post_updated( $post_type, $post_id, $update_fields, $old_post, $new_post ){
        if ( $post_type === $this->post_type ){
            //make sure a contact is shared with the user when they change the contact type to personal
            if ( isset( $update_fields['type'] ) && $update_fields['type'] === 'personal' && $old_post['type']['key'] !== 'personal' && !empty( get_current_user_id() ) ){
                DT_Posts::add_shared( 'contacts', $post_id, get_current_user_id(), null, false, false, false );
            }
        }
    }

    //Add, remove or modify fields before the fields are processed in post create
    public function dt_post_create_fields( $fields, $post_type ){
        if ( $post_type === 'contacts' ){
            if ( !isset( $fields['type'] ) && isset( $fields['additional_meta']['created_from'] ) ){
                $from_post = DT_Posts::get_post( 'contacts', $fields['additional_meta']['created_from'], true, false );
                if ( !is_wp_error( $from_post ) && isset( $from_post['type']['key'] ) ){
                    switch ( $from_post['type']['key'] ){
                        case 'personal':
                        case 'placeholder':
                            $fields['type'] = 'placeholder';
                            break;
                        case 'access':
                        case 'access_placeholder':
                        case 'user':
                            $fields['type'] = 'access_placeholder';
                            break;
                    }
                }
            }

            //set default contact type to acccess
            if ( !isset( $fields['type'] ) ){
                $fields['type'] = 'access';
            }
            //set default overall status
            if ( !isset( $fields['overall_status'] ) ){
                if ( get_current_user_id() ){
                    $fields['overall_status'] = 'active';
                } else {
                    $fields['overall_status'] = 'new';
                }
            }
        }
        return $fields;
    }

    /*
     * Check other access contacts for possible duplicates
     */
    private function check_for_duplicates( $post_type, $post_id ){
        if ( get_current_user_id() === 0 ){
            $current_user = wp_get_current_user();
            $had_cap = current_user_can( 'dt_all_access_contacts' );
            $current_user->add_cap( 'dt_all_access_contacts' );
            $dup_ids = DT_Duplicate_Checker_And_Merging::ids_of_non_dismissed_duplicates( $post_type, $post_id, true );
            if ( ! is_wp_error( $dup_ids ) && sizeof( $dup_ids['ids'] ) < 10 ){
                $comment = __( 'This record might be a duplicate of: ', 'disciple_tools' );
                foreach ( $dup_ids['ids'] as $id_of_duplicate ){
                    $comment .= " \n -  [$id_of_duplicate]($id_of_duplicate)";
                }
                $args = [
                    'user_id' => 0,
                    'comment_author' => __( 'Duplicate Checker', 'disciple_tools' )
                ];
                DT_Posts::add_post_comment( $post_type, $post_id, $comment, 'duplicate', $args, false, true );
            }
            if ( !$had_cap ){
                $current_user->remove_cap( 'dt_all_access_contacts' );
            }
        }
    }

    public function dt_filter_users_receiving_comment_notification( $users_to_notify, $post_type, $post_id, $comment ){
        if ( $post_type === 'contacts' ){
            $post = DT_Posts::get_post( $post_type, $post_id );
            if ( !is_wp_error( $post ) && isset( $post['type']['key'] ) && $post['type']['key'] === 'access' ){
                $following_all = get_users( [
                    'meta_key' => 'dt_follow_all',
                    'meta_value' => true
                ] );
                foreach ( $following_all as $user ){
                    if ( !in_array( $user->ID, $users_to_notify ) ){
                        $users_to_notify[] = $user->ID;
                    }
                }
            }
        }
        return $users_to_notify;
    }

    //list page filters function
    public static function dt_user_list_filters( $filters, $post_type ){
        if ( $post_type === 'contacts' ){
            $performance_mode = get_option( 'dt_performance_mode', false );
            $shared_by_type_counts = $performance_mode ? [] : DT_Posts_Metrics::get_shared_with_meta_field_counts( 'contacts', 'type' );
            $post_label_plural = DT_Posts::get_post_settings( $post_type )['label_plural'];
            $private_contacts_enabled = $post_type_settings['contacts']['enable_private_contacts'] ?? false;


            $filters['tabs'][] = [
                'key' => 'default',
                'label' => __( 'Default Filters', 'disciple_tools' ),
                'order' => 7
            ];
            $filters['filters'][] = [
                'ID' => 'all_my_contacts',
                'tab' => 'default',
                'name' => sprintf( _x( 'All %s', 'All records', 'disciple_tools' ), $post_label_plural ),
                'labels' =>[
                    [
                        'id' => 'all',
                        'name' => sprintf( _x( 'All %s I can view', 'All records I can view', 'disciple_tools' ), $post_label_plural ),
                    ]
                ],
                'query' => [
                    'sort' => '-post_date',
                ],
            ];

            $filters['filters'][] = [
                'ID' => 'favorite',
                'tab' => 'default',
                'name' => sprintf( _x( 'Favorite %s', 'Favorite Contacts', 'disciple_tools' ), $post_label_plural ),
                'query' => [
                    'fields' => [ 'favorite' => [ '1' ] ],
                    'sort' => 'name'
                ],
                'labels' => [
                    [ 'id' => '1', 'name' => __( 'Favorite', 'disciple_tools' ) ]
                ]
            ];
            $filters['filters'][] = [
                'ID' => 'recent',
                'tab' => 'default',
                'name' => __( 'My Recently Viewed', 'disciple_tools' ),
                'query' => [
                    'dt_recent' => true
                ],
                'labels' => [
                    [ 'id' => 'recent', 'name' => __( 'Last 30 viewed', 'disciple_tools' ) ]
                ]
            ];
            // add assigned to me filters
            $filters['filters'][] = [
                'ID' => 'my_all',
                'tab' => 'default',
                'name' => __( 'My Assigned Contacts', 'disciple_tools' ),
                'query' => [
                    'assigned_to' => [ 'me' ],
                    'subassigned' => [ 'me' ],
                    'combine' => [ 'subassigned' ],
                    'overall_status' => [ '-closed' ],
                    'type' => [ 'access' ],
                    'sort' => 'overall_status',
                ],
                'labels' => [
                    [ 'name' => __( 'My Follow-Up', 'disciple_tools' ), 'field' => 'combine', 'id' => 'subassigned' ],
                    [ 'name' => __( 'Assigned to me', 'disciple_tools' ), 'field' => 'assigned_to', 'id' => 'me' ],
                    [ 'name' => __( 'Sub-assigned to me', 'disciple_tools' ), 'field' => 'subassigned', 'id' => 'me' ],
                ],
                'count' => $total_my ?? '',
            ];
            if ( $private_contacts_enabled ){
                $filters['filters'][] = [
                    'ID' => 'personal',
                    'tab' => 'default',
                    'name' => __( 'Personal', 'disciple_tools' ),
                    'query' => [
                        'type' => [ 'personal' ],
                        'sort' => 'name',
                        'overall_status' => [ '-closed' ],
                    ],
                    'count' => $shared_by_type_counts['keys']['personal'] ?? '',
                ];
            }
            $filters['filters'][] = [
                'ID' => 'placeholder',
                'tab' => 'default',
                'name' => sprintf( _x( 'Connected %s', 'Personal records', 'disciple_tools' ), $post_label_plural ),
                'query' => [
                    'type' => [ 'placeholder' ],
                    'overall_status' => [ '-closed' ],
                    'sort' => 'name'
                ],
                'count' => $shared_by_type_counts['keys']['placeholder'] ?? '',
            ];
            $filters['filters'] = self::add_default_custom_list_filters( $filters['filters'] );
        }
        return $filters;
    }

    //list page filters function
    private static function add_default_custom_list_filters( $filters ){
        if ( empty( $filters ) ){
            $filters = [];
        }
        $default_filters = [
            [
                'ID' => 'my_shared',
                'visible' => '1',
                'type' => 'default',
                'tab' => 'custom',
                'name' => __( 'Shared with me', 'disciple_tools' ),
                'query' => [
                    'shared_with' => [ 'me' ],
                    'sort' => 'name',
                ],
                'labels' => [
                    [
                        'id' => 'me',
                        'name' => __( 'Shared with me', 'disciple_tools' ),
                        'field' => 'shared_with'
                    ],
                ],
            ],
            [
                'ID' => 'my_subassigned',
                'visible' => '1',
                'type' => 'default',
                'tab' => 'custom',
                'name' => 'Subassigned to me',
                'query' => [
                    'subassigned' => [ 'me' ],
                    'sort' => 'overall_status',
                ],
                'labels' => [
                    [
                        'id' => 'me',
                        'name' => 'Subassigned to me',
                        'field' => 'subassigned',
                    ],
                ],
            ],
        ];
        //prepend filter if it is not already created.
        $contact_filter_ids = array_map( function ( $a ){
            return $a['ID'];
        }, $filters );
        foreach ( $default_filters as $filter ) {
            if ( !in_array( $filter['ID'], $contact_filter_ids ) ){
                array_unshift( $filters, $filter );
            }
        }
        //translation for default fields
        foreach ( $filters as $index => $filter ) {
            if ( $filter['name'] === 'Shared with me' ) {
                $filters[$index]['name'] = __( 'Shared with me', 'disciple_tools' );
                $filters[$index]['labels'][0]['name'] = __( 'Shared with me', 'disciple_tools' );
            }
            if ( $filter['name'] === 'Subassigned to me' ) {
                $filters[$index]['name'] = __( 'Subassigned only', 'disciple_tools' );
                $filters[$index]['labels'][0]['name'] = __( 'Subassigned only', 'disciple_tools' );
            }
        }
        return $filters;
    }

    public function dt_search_viewable_posts_query( $query ){
        if ( isset( $query['combine'] ) && in_array( 'subassigned', $query['combine'] ) && isset( $query['assigned_to'], $query['subassigned'] ) ){
            $a = $query['assigned_to'];
            $s = $query['subassigned'];
            unset( $query['assigned_to'] );
            unset( $query['subassigned'] );
            $query[] = [ 'assigned_to' => $a, 'subassigned' => $s ];
        }
        return $query;
    }


    public function scripts(){
        if ( is_singular( 'contacts' ) && get_the_ID() && DT_Posts::can_view( $this->post_type, get_the_ID() ) ){
            wp_enqueue_script( 'dt_contacts', get_template_directory_uri() . '/dt-contacts/contacts.js', [
                'jquery',
            ], filemtime( get_theme_file_path() . '/dt-contacts/contacts.js' ), true );
        }
    }

    public function add_api_routes() {
        $namespace = 'dt-posts/v2';
        register_rest_route(
            $namespace, '/contacts/(?P<id>\d+)/revert/(?P<activity_id>\d+)', [
                'methods'  => 'GET',
                'callback' => [ $this, 'revert_activity' ],
                'permission_callback' => '__return_true',
            ]
        );
    }

    /**
     * Revert an activity
     * @todo move this work for any post type
     * @param WP_REST_Request $request
     * @return array|WP_Error
     */
    public function revert_activity( WP_REST_Request $request ) {
        $params = $request->get_params();
        if ( isset( $params['id'] ) && isset( $params['activity_id'] ) ) {
            $contact_id = $params['id'];
            $activity_id = $params['activity_id'];
            if ( !DT_Posts::can_update( 'contacts', $contact_id ) ) {
                return new WP_Error( __FUNCTION__, 'You do not have permission for this', [ 'status' => 403 ] );
            }
            $activity = DT_Posts::get_post_single_activity( 'contacts', $contact_id, $activity_id );
            if ( empty( $activity->old_value ) ){
                if ( strpos( $activity->meta_key, 'quick_button_' ) !== false ){
                    $activity->old_value = 0;
                }
            }
            update_post_meta( $contact_id, $activity->meta_key, $activity->old_value ?? '' );
            return DT_Posts::get_post( 'contacts', $contact_id );
        } else {
            return new WP_Error( 'get_activity', 'Missing a valid contact id or activity id', [ 'status' => 400 ] );
        }
    }



    public function add_comm_channel_comment_section( $sections, $post_type ){
        if ( $post_type === 'contacts' ){
            $channels = DT_Posts::get_post_field_settings( $post_type );
            foreach ( $channels as $channel_key => $channel_option ) {
                if ( $channel_option['type'] !== 'communication_channel' ) {
                    continue;
                }
                $enabled = !isset( $channel_option['enabled'] ) || $channel_option['enabled'] !== false;
                if ( $channel_key == 'contact_phone' || $channel_key == 'contact_email' || $channel_key == 'contact_address' || !$enabled ){
                    continue;
                }
                $sections[] = [
                    'key' => $channel_key,
                    'label' => esc_html( $channel_option['name'] ?? $channel_key )
                ];
            }

            // Extract custom comment types.
            $comment_type_options  = dt_get_option( 'dt_comment_types' );
            $comment_type_fields = $comment_type_options[ $post_type ] ?? [];
            foreach ( $comment_type_fields ?? [] as $key => $type ){
                if ( isset( $type['is_comment_type'] ) && $type['is_comment_type'] ){

                    // Ensure label adopts the correct name translation.
                    $label = ( isset( $type['translations'] ) && !empty( $type['translations'][determine_locale()] ) ) ? $type['translations'][determine_locale()] : ( $type['name'] ?? $key );
                    if ( empty( $label ) ){
                        $label = $key;
                    }

                    // Safeguard against duplicates.
                    $already_assigned = $this->comm_channel_comment_section_already_assigned( $sections, $key );
                    if ( $already_assigned === false ){

                        // Package custom comment type.
                        $packaged_type = $type;
                        $packaged_type['key'] = $key;
                        $packaged_type['label'] = esc_html( $label );
                        $sections[] = $packaged_type;

                    } else {

                        // Update pre-existing custom comment types.
                        $sections[$already_assigned] = $type;
                        $sections[$already_assigned]['key'] = $key;
                        $sections[$already_assigned]['label'] = esc_html( $label );

                    }
                }
            }

            // Finally, move all original custom comment types to bottom spots!
            foreach ( $sections ?? [] as $section ){
                if ( isset( $section['is_comment_type'] ) && $section['is_comment_type'] && ( substr( $section['key'], 0, strlen( $section['key_prefix'] ) ) === $section['key_prefix'] ) ){
                    $section_idx = $this->comm_channel_comment_section_already_assigned( $sections, $section['key'] );
                    if ( $section_idx !== false ){
                        $unshift_section = $sections[$section_idx];
                        unset( $sections[$section_idx] );
                        $sections[] = $unshift_section;
                    }
                }
            }
        }

        return $sections;
    }

    private function comm_channel_comment_section_already_assigned( $sections, $key ){
        $found = false;
        foreach ( $sections ?? [] as $idx => $section ){
            if ( isset( $section['key'] ) && $section['key'] == $key ){
                $found = $idx;
            }
        }

        return $found;
    }

    public function dt_record_notifications_section( $post_type, $dt_post ){
        if ( $post_type === 'contacts' ):
            $post_settings = DT_Posts::get_post_settings( $post_type );
            ?>
            <!-- archived -->
            <section class="cell small-12 archived-notification"
                     style="display: <?php echo esc_html( ( isset( $dt_post['overall_status']['key'] ) && $dt_post['overall_status']['key'] === 'closed' ) ? 'block' : 'none' ) ?> ">
                <div class="bordered-box detail-notification-box" style="background-color:#333">
                    <h4>
                        <img class="dt-white-icon" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/alert-circle-exc.svg?v=2?v=2' ) ?>"/>
                        <?php echo esc_html( sprintf( __( 'This %s is archived', 'disciple_tools' ), strtolower( $post_settings['label_singular'] ) ) ) ?>
                    </h4>
                    <button class="button" id="unarchive-record"><?php esc_html_e( 'Restore', 'disciple_tools' )?></button>
                </div>
            </section>
        <?php endif;
    }
    public function dt_record_icon( $icon, $post_type, $dt_post ){
        if ( $post_type == 'contacts' ) {
            $icon = 'mdi mdi-account-box-outline';
        }
        return $icon;
    }
}
