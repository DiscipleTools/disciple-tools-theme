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
        add_action( 'after_setup_theme', array( $this, 'after_setup_theme' ), 100 );
        add_filter( 'dt_set_roles_and_permissions', array( $this, 'dt_set_roles_and_permissions' ), 10, 1 );
        add_filter( 'dt_capabilities', array( $this, 'dt_capabilities' ), 100, 1 );

        //setup tiles and fields
        add_action( 'wp_enqueue_scripts', array( $this, 'scripts' ), 99 );
        add_filter( 'dt_custom_fields_settings', array( $this, 'dt_custom_fields_settings' ), 5, 2 );
        add_filter( 'dt_custom_fields_settings_after_combine', array( $this, 'dt_custom_fields_settings_after_combine' ), 10, 2 );
        add_filter( 'dt_details_additional_tiles', array( $this, 'dt_details_additional_tiles' ), 10, 2 );
        add_filter( 'dt_details_additional_tiles', array( $this, 'dt_details_additional_tiles_after' ), 100, 2 );
        add_action( 'dt_record_admin_actions', array( $this, 'dt_record_admin_actions' ), 10, 2 );
        add_action( 'dt_record_footer', array( $this, 'dt_record_footer' ), 10, 2 );
        add_action( 'dt_record_notifications_section', array( $this, 'dt_record_notifications_section' ), 10, 2 );
        add_filter( 'dt_record_icon', array( $this, 'dt_record_icon' ), 10, 3 );
        add_filter( 'dt_get_post_type_settings', array( $this, 'dt_get_post_type_settings' ), 20, 2 );

        // hooks
        add_action( 'rest_api_init', array( $this, 'add_api_routes' ) );
        add_action( 'post_connection_removed', array( $this, 'post_connection_removed' ), 10, 4 );
        add_action( 'post_connection_added', array( $this, 'post_connection_added' ), 10, 4 );
        add_filter( 'dt_post_update_fields', array( $this, 'update_post_field_hook' ), 10, 3 );
        add_filter( 'dt_post_updated', array( $this, 'dt_post_updated' ), 10, 5 );
        add_filter( 'dt_post_create_fields', array( $this, 'dt_post_create_fields' ), 20, 2 );
        add_filter( 'dt_comments_additional_sections', array( $this, 'add_comm_channel_comment_section' ), 100, 2 );


        //list
        add_filter( 'dt_user_list_filters', array( $this, 'dt_user_list_filters' ), 10, 2 );
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
            $settings['status_field'] = array(
                'status_key' => 'overall_status',
                'archived_key' => 'closed',
            );
        }
        return $settings;
    }

    public function dt_capabilities( $capabilities ){
        $capabilities['dt_all_access_' . $this->post_type] = array(
            'source' => DT_Posts::get_label_for_post_type( $this->post_type, false, false ),
            'label' => 'Manage all access and media contacts',
            'description' => 'View and update all access and media contacts',
            'post_type' => $this->post_type,
        );
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
            $fields['nickname'] = array(
                'name' => __( 'Nickname', 'disciple_tools' ),
                'type' => 'text',
                'tile' => 'details',
                'icon' => get_template_directory_uri() . '/dt-assets/images/nametag.svg?v=2',
            );
            $contact_preferences = get_option( 'dt_contact_preferences', array() );
            $fields['type'] = array(
                'name'        => __( 'Contact Type', 'disciple_tools' ),
                'type'        => 'key_select',
                'default'     => array(
                    'user' => array(
                        'label' => __( 'User', 'disciple_tools' ),
                        'description' => __( 'Representing a User in the system', 'disciple_tools' ),
                        'color' => '#3F729B',
                        'hidden' => true,
                        'in_create_form' => false,
                    ),
                    'personal' => array(
                        'label' => __( 'Private Contact', 'disciple_tools' ),
                        'color' => '#9b379b',
                        'description' => __( 'A friend, family member or acquaintance', 'disciple_tools' ),
                        'visibility' => __( 'Only me', 'disciple_tools' ),
                        'icon' => get_template_directory_uri() . '/dt-assets/images/locked.svg?v=2',
                        'order' => 50,
                        'hidden' => !empty( $contact_preferences['hide_personal_contact_type'] ),
                        'default' => true,
                    ),
                ),
                'description' => 'See full documentation here: https://disciple.tools/user-docs/getting-started-info/contacts/contact-types',
                'icon' => get_template_directory_uri() . '/dt-assets/images/circle-square-triangle.svg?v=2',
                'customizable' => false,
            );
            $fields['duplicate_data'] = array(
                'name' => 'Duplicates', //system string does not need translation
                'type' => 'array',
                'default' => array(),
                'hidden' => true,
            );
            $fields['duplicate_of'] = array(
                'name' => 'Duplicate of', //system string does not need translation
                'type' => 'text',
                'hidden' => true,
            );

            $fields['languages'] = array(
                'name' => __( 'Languages', 'disciple_tools' ),
                'type' => 'multi_select',
                'default' => dt_get_option( 'dt_working_languages' ) ?: array(),
                'icon' => get_template_directory_uri() . '/dt-assets/images/languages.svg?v=2',
                'tile' => 'no_tile',
            );

            //add communication channels
            $fields['contact_phone'] = array(
                'name' => __( 'Phone', 'disciple_tools' ),
                'icon' => get_template_directory_uri() . '/dt-assets/images/phone.svg?v=2',
                'type' => 'communication_channel',
                'tile' => 'details',
                'customizable' => false,
                'in_create_form' => true,
                'messagingServices' => array(
                    'Signal' => array(
                        'name' => __( 'Signal', 'disciple_tools' ),
                        'link' => 'https://signal.me/#p/PHONE_NUMBER',
                        'icon' => get_template_directory_uri() . '/dt-assets/images/signal.svg',
                    ),
                    'Viber' => array(
                        'name' => __( 'Viber', 'disciple_tools' ),
                        'link' => 'viber://chat?number=PHONE_NUMBER',
                        'icon' => get_template_directory_uri() . '/dt-assets/images/viber.svg',
                    ),
                    'Whatsapp' => array(
                        'name' => __( 'WhatsApp', 'disciple_tools' ),
                        'link' => 'https://api.whatsapp.com/send?phone=PHONE_NUMBER_NO_PLUS',
                        'icon' => get_template_directory_uri() . '/dt-assets/images/signal.svg',
                    ),
                ),
            );
            $fields['contact_email'] = array(
                'name' => __( 'Email', 'disciple_tools' ),
                'icon' => get_template_directory_uri() . '/dt-assets/images/email.svg?v=2',
                'type' => 'communication_channel',
                'tile' => 'details',
                'customizable' => false,
            );

            // add location fields
            $fields['location_grid'] = array(
                'name'        => __( 'Locations', 'disciple_tools' ),
                'description' => _x( 'The general location where this contact is located.', 'Optional Documentation', 'disciple_tools' ),
                'type'        => 'location',
                'mapbox'    => false,
                'in_create_form' => true,
                'tile' => 'details',
                'icon' => get_template_directory_uri() . '/dt-assets/images/location.svg?v=2',
            );
            $fields['location_grid_meta'] = array(
                'name'        => __( 'Locations or Address', 'disciple_tools' ),
                'type'        => 'location_meta',
                'tile'      => 'details',
                'mapbox'    => false,
                'hidden' => true,
                'in_create_form' => true,
                'icon' => get_template_directory_uri() . '/dt-assets/images/map-marker-multiple.svg?v=2',
            );
            $fields['contact_address'] = array(
                'name' => __( 'Address', 'disciple_tools' ),
                'icon' => get_template_directory_uri() . '/dt-assets/images/house.svg?v=2',
                'type' => 'communication_channel',
                'tile' => 'details',
                'mapbox'    => false,
                'customizable' => false,
            );
            if ( DT_Mapbox_API::get_key() ){
                $fields['contact_address']['custom_display'] = true;
                $fields['contact_address']['mapbox'] = true;
                $fields['contact_address']['hidden'] = true;
                unset( $fields['contact_address']['tile'] );
                $fields['location_grid']['mapbox'] = true;
                $fields['location_grid']['hidden'] = true;
                $fields['location_grid_meta']['mapbox'] = true;
                $fields['location_grid_meta']['hidden'] = false;
            }

            // add social media
            $fields['contact_facebook'] = array(
                'name' => __( 'Facebook', 'disciple_tools' ),
                'icon' => get_template_directory_uri() . '/dt-assets/images/facebook.svg?v=2',
                'hide_domain' => true,
                'type' => 'communication_channel',
                'tile' => 'details',
                'customizable' => false,
            );
            $fields['contact_twitter'] = array(
                'name' => __( 'Twitter', 'disciple_tools' ),
                'icon' => get_template_directory_uri() . '/dt-assets/images/twitter.svg?v=2',
                'hide_domain' => true,
                'type' => 'communication_channel',
                'tile' => 'details',
                'customizable' => false,
            );
            $fields['contact_other'] = array(
                'name' => __( 'Other Social Links', 'disciple_tools' ),
                'icon' => get_template_directory_uri() . '/dt-assets/images/chat.svg?v=2',
                'hide_domain' => false,
                'type' => 'communication_channel',
                'tile' => 'details',
                'customizable' => false,
            );

            $fields['relation'] = array(
                'name' => sprintf( _x( 'Connections to other %s', 'connections to other records', 'disciple_tools' ), __( 'Contacts', 'disciple_tools' ) ),
                'description' => _x( 'Relationship this contact has with another contact in the system.', 'Optional Documentation', 'disciple_tools' ),
                'type' => 'connection',
                'post_type' => 'contacts',
                'p2p_direction' => 'any',
                'p2p_key' => 'contacts_to_relation',
                'tile' => 'other',
                'in_create_form' => array( 'placeholder' ),
                'icon' => get_template_directory_uri() . '/dt-assets/images/connection-people.svg?v=2',
            );

            $fields['gender'] = array(
                'name'        => __( 'Gender', 'disciple_tools' ),
                'type'        => 'key_select',
                'default'     => array(
                    'male'    => array( 'label' => __( 'Male', 'disciple_tools' ) ),
                    'female'  => array( 'label' => __( 'Female', 'disciple_tools' ) ),
                ),
                'tile'     => 'details',
                'icon' => get_template_directory_uri() . '/dt-assets/images/gender-male-female.svg',
            );

            $fields['age'] = array(
                'name'        => __( 'Age', 'disciple_tools' ),
                'type'        => 'key_select',
                'default'     => array(
                    'not-set' => array( 'label' => '' ),
                    '<19'     => array( 'label' => __( 'Under 18 years old', 'disciple_tools' ) ),
                    '<26'     => array( 'label' => __( '18-25 years old', 'disciple_tools' ) ),
                    '<41'     => array( 'label' => __( '26-40 years old', 'disciple_tools' ) ),
                    '>41'     => array( 'label' => __( 'Over 40 years old', 'disciple_tools' ) ),
                ),
                'tile'     => 'details',
                'icon' => get_template_directory_uri() . '/dt-assets/images/contact-age.svg?v=2',
                'select_cannot_be_empty' => true, //backwards compatible since we already have an "none" value
            );

            $fields['requires_update'] = array(
                'name'        => __( 'Requires Update', 'disciple_tools' ),
                'type'        => 'boolean',
                'default'     => false,
            );

            $fields['overall_status'] = array(
                'name' => __( 'Contact Status', 'disciple_tools' ),
                'description' => _x( 'The Contact Status describes the progress in communicating with the contact.', 'Contact Status field description', 'disciple_tools' ),
                'type' => 'key_select',
                'default' => array(
                    'active'       => array(
                        'label' => __( 'Active', 'disciple_tools' ),
                        'description' => _x( 'The contact is progressing and/or continually being updated.', 'Contact Status field description', 'disciple_tools' ),
                        'color' => '#4CAF50',
                    ),
                    'closed' => array(
                        'label' => __( 'Archived', 'disciple_tools' ),
                        'color' => '#808080',
                        'description' => _x( 'This contact has made it known that they no longer want to continue or you have decided not to continue with him/her.', 'Contact Status field description', 'disciple_tools' ),
                    ),
                ),
            );
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
            $tiles['other'] = array( 'label' => __( 'Other', 'disciple_tools' ) );
        }
        return $tiles;
    }


    public function post_connection_added( $post_type, $post_id, $post_key, $value ){
    }
    public function post_connection_removed( $post_type, $post_id, $post_key, $value ){
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
            if ( !isset( $fields['type'] ) ){
                $fields['type'] = 'personal';
            }
        }
        return $fields;
    }

    //list page filters function
    public static function dt_user_list_filters( $filters, $post_type ){
        if ( $post_type === 'contacts' ){
            $shared_by_type_counts = DT_Posts_Metrics::get_shared_with_meta_field_counts( 'contacts', 'type' );
            $post_label_plural = DT_Posts::get_post_settings( $post_type )['label_plural'];

            $filters['tabs'][] = array(
                'key' => 'default',
                'label' => __( 'Default Filters', 'disciple_tools' ),
                'order' => 7,
            );
            $filters['filters'][] = array(
                'ID' => 'all_my_contacts',
                'tab' => 'default',
                'name' => sprintf( _x( 'All %s', 'All records', 'disciple_tools' ), $post_label_plural ),
                'labels' =>array(
                    array(
                        'id' => 'all',
                        'name' => sprintf( _x( 'All %s I can view', 'All records I can view', 'disciple_tools' ), $post_label_plural ),
                    ),
                ),
                'query' => array(
                    'sort' => '-post_date',
                ),
            );

            $filters['filters'][] = array(
                'ID' => 'favorite',
                'tab' => 'default',
                'name' => sprintf( _x( 'Favorite %s', 'Favorite Contacts', 'disciple_tools' ), $post_label_plural ),
                'query' => array(
                    'fields' => array( 'favorite' => array( '1' ) ),
                    'sort' => 'name',
                ),
                'labels' => array(
                    array( 'id' => '1', 'name' => __( 'Favorite', 'disciple_tools' ) ),
                ),
            );
            $filters['filters'][] = array(
                'ID' => 'recent',
                'tab' => 'default',
                'name' => __( 'My Recently Viewed', 'disciple_tools' ),
                'query' => array(
                    'dt_recent' => true,
                ),
                'labels' => array(
                    array( 'id' => 'recent', 'name' => __( 'Last 30 viewed', 'disciple_tools' ) ),
                ),
            );
            $filters['filters'][] = array(
                'ID' => 'personal',
                'tab' => 'default',
                'name' => __( 'Personal', 'disciple_tools' ),
                'query' => array(
                    'type' => array( 'personal' ),
                    'sort' => 'name',
                    'overall_status' => array( '-closed' ),
                ),
                'count' => $shared_by_type_counts['keys']['personal'] ?? 0,
            );
            $filters['filters'] = self::add_default_custom_list_filters( $filters['filters'] );
        }
        return $filters;
    }

    //list page filters function
    private static function add_default_custom_list_filters( $filters ){
        if ( empty( $filters ) ){
            $filters = array();
        }
        $default_filters = array(
            array(
                'ID' => 'my_shared',
                'visible' => '1',
                'type' => 'default',
                'tab' => 'custom',
                'name' => __( 'Shared with me', 'disciple_tools' ),
                'query' => array(
                    'shared_with' => array( 'me' ),
                    'sort' => 'name',
                ),
                'labels' => array(
                    array(
                        'id' => 'me',
                        'name' => __( 'Shared with me', 'disciple_tools' ),
                        'field' => 'shared_with',
                    ),
                ),
            ),
        );
        //prepend filter if it is not already created.
        $contact_filter_ids = array_map( function ( $a ) {
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
        }
        return $filters;
    }


    public function scripts(){
        if ( is_singular( 'contacts' ) && get_the_ID() && DT_Posts::can_view( $this->post_type, get_the_ID() ) ){
            wp_enqueue_script( 'dt_contacts', get_template_directory_uri() . '/dt-contacts/contacts.js', array(
                'jquery',
            ), filemtime( get_theme_file_path() . '/dt-contacts/contacts.js' ), true );
        }
    }

    public function add_api_routes() {
        $namespace = 'dt-posts/v2';
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
                $sections[] = array(
                    'key' => $channel_key,
                    'label' => esc_html( $channel_option['name'] ?? $channel_key ),
                );
            }

            // Extract custom comment types.
            $comment_type_options  = dt_get_option( 'dt_comment_types' );
            $comment_type_fields = $comment_type_options[ $post_type ] ?? array();
            foreach ( $comment_type_fields ?? array() as $key => $type ){
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
            foreach ( $sections ?? array() as $section ){
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
        foreach ( $sections ?? array() as $idx => $section ){
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
            $gender = isset( $dt_post['gender'] ) ? $dt_post['gender']['key'] : 'male';
            $icon = 'fi-torso';
            if ( $gender == 'female' ) {
                $icon = $icon.'-'.$gender;
            }
        }
        return $icon;
    }
}
