<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly.

class DT_Groups_Base extends DT_Module_Base {
    private static $_instance = null;
    public $post_type = 'groups';
    public $module = 'groups_base';
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    } // End instance()

    public function __construct() {
        parent::__construct();
        if ( !self::check_enabled_and_prerequisites() ){
            return;
        }

        //setup post type
        add_action( 'after_setup_theme', [ $this, 'after_setup_theme' ], 100 );
        add_filter( 'dt_set_roles_and_permissions', [ $this, 'dt_set_roles_and_permissions' ], 20, 1 ); //after contacts

        //setup tiles and fields
        add_filter( 'dt_custom_fields_settings', [ $this, 'dt_custom_fields_settings' ], 10, 2 );
        add_filter( 'dt_details_additional_tiles', [ $this, 'dt_details_additional_tiles' ], 10, 2 );
        add_filter( 'dt_custom_tiles_after_combine', [ $this, 'dt_custom_tiles_after_combine' ], 10, 2 );
        add_action( 'dt_details_additional_section', [ $this, 'dt_details_additional_section' ], 20, 2 );
        add_action( 'wp_enqueue_scripts', [ $this, 'scripts' ], 99 );

        // hooks
        add_action( 'post_connection_added', [ $this, 'post_connection_added' ], 10, 4 );
        add_filter( 'dt_post_update_fields', [ $this, 'dt_post_update_fields' ], 10, 3 );
        add_filter( 'dt_post_create_fields', [ $this, 'dt_post_create_fields' ], 10, 2 );
        add_action( 'dt_post_created', [ $this, 'dt_post_created' ], 10, 3 );
        add_action( 'dt_comment_created', [ $this, 'dt_comment_created' ], 10, 4 );
        add_filter( 'dt_after_get_post_fields_filter', [ $this, 'dt_after_get_post_fields_filter' ], 10, 2 );
        add_filter( 'dt_get_post_type_settings', [ $this, 'dt_get_post_type_settings' ], 20, 2 );

        //list
        add_filter( 'dt_user_list_filters', [ $this, 'dt_user_list_filters' ], 10, 2 );
        add_filter( 'dt_filter_access_permissions', [ $this, 'dt_filter_access_permissions' ], 20, 2 );
    }

    public function after_setup_theme(){
        if ( class_exists( 'Disciple_Tools_Post_Type_Template' ) ) {
            new Disciple_Tools_Post_Type_Template( 'groups', __( 'Group', 'disciple_tools' ), __( 'Groups', 'disciple_tools' ) );
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
            $settings['label_singular'] = __( 'Group', 'disciple_tools' );
            $settings['label_plural'] = __( 'Groups', 'disciple_tools' );
            $settings['status_field'] = [
                'status_key' => 'group_status',
                'archived_key' => 'inactive',
            ];
        }
        return $settings;
    }

    public function dt_set_roles_and_permissions( $expected_roles ){
        // if the user can access contact they also can access group
        foreach ( $expected_roles as $role_key => $role ){
            if ( isset( $role['type'] ) && in_array( 'base', $role['type'], true ) ){
                $expected_roles[$role_key]['permissions']['access_' . $this->post_type] = true;
                $expected_roles[$role_key]['permissions']['create_' . $this->post_type] = true;
            }
        }

        if ( isset( $expected_roles['administrator'] ) ){
            $expected_roles['administrator']['permissions']['view_any_groups'] = true;
            $expected_roles['administrator']['permissions']['update_any_groups'] = true;
            $expected_roles['administrator']['permissions']['dt_all_admin_groups'] = true;
            $expected_roles['administrator']['permissions']['delete_any_groups'] = true;
        }
        if ( isset( $expected_roles['dispatcher'] ) ){
            $expected_roles['dispatcher']['permissions']['view_any_groups'] = true;
            $expected_roles['dispatcher']['permissions']['update_any_groups'] = true;
        }
        if ( isset( $expected_roles['dt_admin'] ) ){
            $expected_roles['dt_admin']['permissions']['view_any_groups'] = true;
            $expected_roles['dt_admin']['permissions']['update_any_groups'] = true;
        }

        return $expected_roles;
    }


    public function dt_custom_fields_settings( $fields, $post_type ){
        if ( $post_type === 'groups' ){
            $fields['requires_update'] = [
                'name'        => __( 'Requires Update', 'disciple_tools' ),
                'description' => '',
                'type'        => 'boolean',
                'default'     => false,
            ];
            $fields['duplicate_data'] = [
                'name' => 'Duplicates', //system string does not need translation
                'type' => 'array',
                'default' => [],
            ];
            $fields['group_status'] = [
                'name'        => __( 'Group Status', 'disciple_tools' ),
                'description' => _x( 'Set the current status of the group.', 'field description', 'disciple_tools' ),
                'type'        => 'key_select',
                'default'     => [
                    'active'   => [
                        'label' => __( 'Active', 'disciple_tools' ),
                        'description' => _x( 'The group is actively meeting.', 'field description', 'disciple_tools' ),
                        'color' => '#4CAF50'
                    ],
                    'inactive' => [
                        'label' => __( 'Inactive', 'disciple_tools' ),
                        'description' => _x( 'The group is no longer meeting.', 'field description', 'disciple_tools' ),
                        'color' => '#808080'
                    ],
                ],
                'tile'     => 'status',
                'icon' => get_template_directory_uri() . '/dt-assets/images/status.svg?v=2',
                'default_color' => '#366184',
                'show_in_table' => 10,
                'select_cannot_be_empty' => true
            ];
            $fields['assigned_to'] = [
                'name'        => __( 'Assigned To', 'disciple_tools' ),
                'description' => __( 'Select the main person who is responsible for reporting on this group.', 'disciple_tools' ),
                'type'        => 'user_select',
                'default'     => '',
                'tile' => 'status',
                'icon' => get_template_directory_uri() . '/dt-assets/images/assigned-to.svg?v=2',
                'show_in_table' => 16,
            ];
            $fields['coaches'] = [
                'name' => __( 'Group Coach / Church Planter', 'disciple_tools' ),
                'description' => _x( 'The person who planted and/or is coaching this group. Only one person can be assigned to a group while multiple people can be coaches / church planters of this group.', 'Optional Documentation', 'disciple_tools' ),
                'type' => 'connection',
                'post_type' => 'contacts',
                'p2p_direction' => 'from',
                'p2p_key' => 'groups_to_coaches',
                'tile' => 'status',
                'icon' => get_template_directory_uri() . '/dt-assets/images/coach.svg?v=2',
                'create-icon' => get_template_directory_uri() . '/dt-assets/images/add-contact.svg?v=2',
            ];


            $fields['group_type'] = [
                'name'        => __( 'Group Type', 'disciple_tools' ),
                'description' => '',
                'type'        => 'key_select',
                'default'     => [
                    'pre-group' => [
                        'label' => __( 'Pre-Group', 'disciple_tools' ),
                        'description' => _x( 'A group predominantly of non-believers.', 'Optional Documentation', 'disciple_tools' ),
                    ],
                    'group'     => [
                        'label' => __( 'Group', 'disciple_tools' ),
                        'description' => _x( 'A group having 3 or more believers but not identifying as church.', 'Optional Documentation', 'disciple_tools' ),
                    ],
                    'church'    => [
                        'label' => __( 'Church', 'disciple_tools' ),
                        'description' => _x( 'A group having 3 or more believers and identifying as church.', 'Optional Documentation', 'disciple_tools' ),
                    ],
                    'team'    => [
                        'label' => __( 'Team', 'disciple_tools' ),
                        'description' => _x( 'A special group that is not meeting as a church (or trying to become church).', 'Optional Documentation', 'disciple_tools' ),
                    ],
                ],
                'tile' => 'groups',
                'in_create_form' => true,
                'show_in_table' => 15,
                'icon' => get_template_directory_uri() . '/dt-assets/images/circle-square-triangle.svg?v=2',
                'select_cannot_be_empty' => true
            ];




            $fields['health_metrics'] = [
                'name' => __( 'Church Health', 'disciple_tools' ),
                'description' => _x( 'Track the progress and health of a group/church.', 'Optional Documentation', 'disciple_tools' ),
                'type' => 'multi_select',
                'default' => [
                    'church_baptism' => [
                        'label' => __( 'Baptism', 'disciple_tools' ),
                        'description' => _x( 'The group is baptising.', 'Optional Documentation', 'disciple_tools' ),
                        'icon' => get_template_directory_uri() . '/dt-assets/images/groups/baptism-2.svg'
                    ],
                    'church_bible' => [
                        'label' => __( 'Bible Study', 'disciple_tools' ),
                        'description' => _x( 'The group is studying the bible.', 'Optional Documentation', 'disciple_tools' ),
                        'icon' => get_template_directory_uri() . '/dt-assets/images/groups/word-2.svg'
                    ],
                    'church_communion' => [
                        'label' => __( 'Communion', 'disciple_tools' ),
                        'description' => _x( 'The group is practicing communion.', 'Optional Documentation', 'disciple_tools' ),
                        'icon' => get_template_directory_uri() . '/dt-assets/images/groups/communion-2.svg'
                    ],
                    'church_fellowship' => [
                        'label' => __( 'Fellowship', 'disciple_tools' ),
                        'description' => _x( 'The group is fellowshiping.', 'Optional Documentation', 'disciple_tools' ),
                        'icon' => get_template_directory_uri() . '/dt-assets/images/groups/heart-2.svg'
                    ],
                    'church_giving' => [
                        'label' => __( 'Giving', 'disciple_tools' ),
                        'description' => _x( 'The group is giving.', 'Optional Documentation', 'disciple_tools' ),
                        'icon' => get_template_directory_uri() . '/dt-assets/images/groups/giving-2.svg'
                    ],
                    'church_prayer' => [
                        'label' => __( 'Prayer', 'disciple_tools' ),
                        'description' => _x( 'The group is praying.', 'Optional Documentation', 'disciple_tools' ),
                        'icon' => get_template_directory_uri() . '/dt-assets/images/groups/prayer-2.svg'
                    ],
                    'church_praise' => [
                        'label' => __( 'Praise', 'disciple_tools' ),
                        'description' => _x( 'The group is praising.', 'Optional Documentation', 'disciple_tools' ),
                        'icon' => get_template_directory_uri() . '/dt-assets/images/groups/praise-2.svg'
                    ],
                    'church_sharing' => [
                        'label' => __( 'Sharing the Gospel', 'disciple_tools' ),
                        'description' => _x( 'The group is sharing the gospel.', 'Optional Documentation', 'disciple_tools' ),
                        'icon' => get_template_directory_uri() . '/dt-assets/images/groups/evangelism-2.svg'
                    ],
                    'church_leaders' => [
                        'label' => __( 'Leaders', 'disciple_tools' ),
                        'description' => _x( 'The group has leaders.', 'Optional Documentation', 'disciple_tools' ),
                        'icon' => get_template_directory_uri() . '/dt-assets/images/groups/leadership-2.svg'
                    ],
                    'church_commitment' => [
                        'label' => __( 'Church Commitment', 'disciple_tools' ),
                        'description' => _x( 'The group has committed to be church.', 'Optional Documentation', 'disciple_tools' ),
                        'icon' => get_template_directory_uri() . '/dt-assets/images/groups/covenant.svg'
                    ],
                ],
                'tile' => 'health-metrics',
                'custom_display' => true
            ];

            $fields['start_date'] = [
                'name'        => __( 'Start Date', 'disciple_tools' ),
                'description' => _x( 'The date this group began meeting.', 'Optional Documentation', 'disciple_tools' ),
                'type'        => 'date',
                'default'     => time(),
                'tile' => 'details',
                'icon' => get_template_directory_uri() . '/dt-assets/images/calendar-plus.svg?v=2',
            ];
            $fields['church_start_date'] =[
                'name' => __( 'Church Start Date', 'disciple_tools' ),
                'description' => _x( 'The date this group first identified as being a church.', 'Optional Documentation', 'disciple_tools' ),
                'type' => 'date',
                'default'     => time(),
                'tile' => 'details',
                'icon' => get_template_directory_uri() . '/dt-assets/images/calendar-check.svg?v=2',

            ];
            $fields['end_date'] = [
                'name'        => __( 'End Date', 'disciple_tools' ),
                'description' => _x( 'The date this group stopped meeting (if applicable).', 'Optional Documentation', 'disciple_tools' ),
                'type'        => 'date',
                'default'     => '',
                'tile' => 'details',
                'icon' => get_template_directory_uri() . '/dt-assets/images/calendar-remove.svg?v=2',
            ];



            $fields['member_count'] = [
                'name' => __( 'Member Count', 'disciple_tools' ),
                'description' => _x( 'The number of members in this group. It will automatically be updated when new members are added or removed in the member list. Change this number manually to include people who may not be in the system but are also members of the group.', 'Optional Documentation', 'disciple_tools' ),
                'type' => 'number',
                'default' => '',
                'tile' => 'relationships',
                'show_in_table' => 25,
                'icon' => get_template_directory_uri() . '/dt-assets/images/tally.svg?v=2',
            ];
            $fields['members'] = [
                'name' => __( 'Member List', 'disciple_tools' ),
                'description' => _x( 'The contacts who are members of this group.', 'Optional Documentation', 'disciple_tools' ),
                'type' => 'connection',
                'post_type' => 'contacts',
                'p2p_direction' => 'to',
                'p2p_key' => 'contacts_to_groups',
                'icon' => get_template_directory_uri() . '/dt-assets/images/list.svg?v=2',
                'connection_count_field' => [ 'post_type' => 'groups', 'field_key' => 'member_count', 'connection_field' => 'members' ]
            ];
            $fields['leaders'] = [
                'name' => __( 'Leaders', 'disciple_tools' ),
                'description' => '',
                'type' => 'connection',
                'post_type' => 'contacts',
                'p2p_direction' => 'from',
                'p2p_key' => 'groups_to_leaders',
                'show_in_table' => 30,
                'connection_count_field' => [ 'post_type' => 'groups', 'field_key' => 'leader_count', 'connection_field' => 'leaders' ]
            ];
            $fields['leader_count'] = [
                'name' => __( 'Leader Count', 'disciple_tools' ),
                'type' => 'number',
                'default' => '',
                'tile' => 'relationships',
                'icon' => get_template_directory_uri() . '/dt-assets/images/groups/leaders.svg',
            ];

            $fields['parent_groups'] = [
                'name' => __( 'Parent Group', 'disciple_tools' ),
                'description' => _x( 'A group that founded this group.', 'Optional Documentation', 'disciple_tools' ),
                'type' => 'connection',
                'post_type' => 'groups',
                'p2p_direction' => 'from',
                'p2p_key' => 'groups_to_groups',
                'tile' => 'groups',
                'icon' => get_template_directory_uri() . '/dt-assets/images/group-parent.svg?v=2',
                'create-icon' => get_template_directory_uri() . '/dt-assets/images/add-group.svg?v=2',
            ];

            $fields['peer_groups'] = [
                'name' => __( 'Peer Group', 'disciple_tools' ),
                'description' => _x( "A related group that isn't a parent/child in relationship. It might indicate groups that collaborate, are about to merge, recently split, etc.", 'Optional Documentation', 'disciple_tools' ),
                'type' => 'connection',
                'post_type' => 'groups',
                'p2p_direction' => 'any',
                'p2p_key' => 'groups_to_peers',
                'tile' => 'groups',
                'icon' => get_template_directory_uri() . '/dt-assets/images/group-peer.svg?v=2',
                'create-icon' => get_template_directory_uri() . '/dt-assets/images/add-group.svg?v=2',
            ];

            $fields['child_groups'] = [
                'name' => __( 'Child Group', 'disciple_tools' ),
                'description' => _x( 'A group that has been birthed out of this group.', 'Optional Documentation', 'disciple_tools' ),
                'type' => 'connection',
                'post_type' => 'groups',
                'p2p_direction' => 'to',
                'p2p_key' => 'groups_to_groups',
                'tile' => 'groups',
                'icon' => get_template_directory_uri() . '/dt-assets/images/group-child.svg?v=2',
                'create-icon' => get_template_directory_uri() . '/dt-assets/images/add-group.svg?v=2',
            ];





            // Group Locations
            $fields['contact_address'] = [
                'name' => __( 'Address', 'disciple_tools' ),
                'icon' => get_template_directory_uri() . '/dt-assets/images/house.svg?v=2',
                'type' => 'communication_channel',
                'tile' => 'details',
                'mapbox'    => false,
                'customizable' => false
            ];

            if ( DT_Mapbox_API::get_key() ){
                $fields['contact_address']['custom_display'] = true;
                $fields['contact_address']['mapbox'] = true;
                unset( $fields['contact_address']['tile'] );
            }



            $field_fields_enabled = self::four_fields_is_enabled();

            $fields['four_fields_unbelievers'] = [
                'name' => __( 'Unbelievers', 'disciple_tools' ),
                'description' => _x( 'Number of unbelievers in this group.', 'Optional Documentation', 'disciple_tools' ),
                'type' => 'text',
                'default' => '',
                'tile' => 'four-fields',
                'custom_display' => true,
                'hidden' => !$field_fields_enabled,
            ];
            $fields['four_fields_believers'] = [
                'name' => __( 'Believers', 'disciple_tools' ),
                'description' => _x( 'Number of believers in this group.', 'Optional Documentation', 'disciple_tools' ),
                'type' => 'text',
                'default' => '',
                'tile' => 'four-fields',
                'custom_display' => true,
                'hidden' => !$field_fields_enabled,
            ];
            $fields['four_fields_accountable'] = [
                'name' => __( 'Accountable', 'disciple_tools' ),
                'description' => _x( 'Number of people in accountability group.', 'Optional Documentation', 'disciple_tools' ),
                'type' => 'text',
                'default' => '',
                'tile' => 'four-fields',
                'custom_display' => true,
                'hidden' => !$field_fields_enabled,
            ];
            $fields['four_fields_church_commitment'] = [
                'name' => __( 'Church Commitment', 'disciple_tools' ),
                'description' => _x( 'Is this a church - yes or no?', 'Optional Documentation', 'disciple_tools' ),
                'type' => 'text',
                'default' => '',
                'tile' => 'four-fields',
                'custom_display' => true,
                'hidden' => !$field_fields_enabled,
            ];
            $fields['four_fields_multiplying'] = [
                'name' => __( 'Multiplying', 'disciple_tools' ),
                'description' => _x( 'Number of people helping start other groups. How many members are multiplying?', 'Optional Documentation', 'disciple_tools' ),
                'type' => 'text',
                'default' => '',
                'tile' => 'four-fields',
                'custom_display' => true,
                'hidden' => !$field_fields_enabled,
            ];

        }

        if ( $post_type === 'contacts' ){
            $fields['groups'] = [
                'name' => __( 'Groups', 'disciple_tools' ),
                'description' => _x( 'Groups this contact is a member of.', 'Optional Documentation', 'disciple_tools' ),
                'type' => 'connection',
                'post_type' => 'groups',
                'p2p_direction' => 'from',
                'p2p_key' => 'contacts_to_groups',
                'tile' => 'other',
                'icon' => get_template_directory_uri() . '/dt-assets/images/group-type.svg?v=2',
                'create-icon' => get_template_directory_uri() . '/dt-assets/images/add-group.svg?v=2',
                'show_in_table' => 35,
                'connection_count_field' => [ 'post_type' => 'groups', 'field_key' => 'member_count', 'connection_field' => 'members' ]
            ];
            $fields['group_leader'] = [
                'name' => __( 'Leader of Group', 'disciple_tools' ),
                'type' => 'connection',
                'p2p_direction' => 'to',
                'p2p_key' => 'groups_to_leaders',
                'post_type' => 'groups',
                'tile' => 'no_tile',
                'icon' => get_template_directory_uri() . '/dt-assets/images/foot.svg?v=2',
                'connection_count_field' => [ 'post_type' => 'groups', 'field_key' => 'leader_count', 'connection_field' => 'leaders' ]
            ];
            $fields['group_coach'] = [
                'name' => __( 'Coach of Group', 'disciple_tools' ),
                'type' => 'connection',
                'p2p_direction' => 'to',
                'p2p_key' => 'groups_to_coaches',
                'post_type' => 'groups',
                'tile' => 'no_tile',
                'icon' => get_template_directory_uri() . '/dt-assets/images/coach.svg?v=2',
            ];
        }
        return $fields;
    }

    public function dt_details_additional_section( $section, $post_type ) {
        self::display_health_metrics_tile( $section, $post_type );
        self::display_four_fields_tile( $section, $post_type );
        self::display_group_relationships_tile( $section, $post_type );
    }

    private function display_health_metrics_tile( $section, $post_type ) {
        if ( $post_type === 'groups' && $section === 'health-metrics' ) {
            $fields = DT_Posts::get_post_field_settings( $post_type );
            if ( self::church_metrics_is_enabled() ) : ?>
                <div class="grid-x">
                    <div style="margin-right:auto; margin-left:auto;min-height:302px">
                        <div class="health-circle" id="health-items-container">
                            <div class="health-grid">
                                <?php $fields = DT_Posts::get_post_field_settings( $post_type );
                                if ( empty( $fields['health_metrics']['default'] ) ): ?>
                                    <div class="custom-group-health-item empty-health" id="health-metrics" style="filter: opacity(0.35);">
                                        <img src="<?php echo esc_attr( get_template_directory_uri() . '/dt-assets/images/dots.svg' ); ?>">
                                        <div class="empty-health-text">
                                            <?php echo esc_html( 'Empty', 'disciple_tools' ); ?>
                                        </div>
                                    </div>
                                <?php else : ?>
                                    <?php foreach ( $fields['health_metrics']['default'] as $key => $option ) : ?>
                                        <?php if ( $key !== 'church_commitment' ) : ?>
                                            <?php
                                            if ( empty( $option['icon'] ) || ! isset( $option['icon'] ) ) {
                                                $option['icon'] = get_template_directory_uri() . '/dt-assets/images/groups/missing.svg';
                                            }
                                            if ( ! isset( $option['description'] ) ) {
                                                $option['description'] = '';
                                            }
                                            ?>
                                            <div class="health-item" id="icon_<?php echo esc_attr( strtolower( $key ) ) ?>" title="<?php echo esc_attr( $option['description'] ); ?>">
                                                <?php
                                                if ( !empty( $option['font-icon'] ) && strpos( $option['font-icon'], 'undefined' ) === false ){
                                                    ?>
                                                    <i class="<?php echo esc_html( $option['font-icon'] ); ?> dt-icon"></i>
                                                    <?php
                                                } elseif ( !empty( $option['icon'] ) && strpos( $option['icon'], 'undefined' ) === false ) {
                                                    ?>
                                                    <img src="<?php echo esc_attr( $option['icon'] ); ?>">
                                                    <?php
                                                }
                                                ?>
                                            </div>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div>
                    <span><?php echo esc_html( $fields['health_metrics']['default']['church_commitment']['label'] ); ?></span>
                    <input type="checkbox" id="is-church-switch" class="dt-switch">
                    <label class="dt-switch" for="is-church-switch" style="vertical-align: top;"></label>
                </div>
        <?php endif;
        }
    }

    private function display_four_fields_tile( $section, $post_type ) {
        if ( $post_type === 'groups' && $section === 'four-fields' ) {
            if ( self::four_fields_is_enabled() ) : ?>
                <section id="four-fields" class="xlarge-6 large-12 medium-6 cell">
                        <div class="section-body">
                            <div style="background:url('<?php echo esc_attr( get_template_directory_uri() . '/dt-assets/images/four-fields.svg?v=2' ); ?>');background-size: 100% 100%;height: 379px;display: grid;grid-template-columns: 1fr 1fr 1fr;grid-template-rows: auto;justify-items: center;align-items: center;" id="four-fields-inputs">
                            </div>
                        </div>
                </section>
            <?php endif;
        }
    }

    public static function church_metrics_is_enabled() {
        $group_preferences = dt_get_option( 'group_preferences' );
        if ( $group_preferences['church_metrics'] == 1 ) {
            return true;
        }
    }

    public static function four_fields_is_enabled() {
        $group_preferences = dt_get_option( 'group_preferences' );
        if ( $group_preferences['four_fields'] == 1 ) {
            return true;
        }
    }

    private function display_group_relationships_tile( $section, $post_type ) {
        if ( $post_type === 'groups' && $section === 'relationships' ) {
            $fields = DT_Posts::get_post_field_settings( $post_type );
            $post = DT_Posts::get_post( 'groups', get_the_ID() );
            ?>
            <div class="section-subheader members-header" style="padding-top: 10px;">
                <div style="padding-bottom: 5px; margin-right:10px; display: inline-block">
                    <img class="dt-icon" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/list.svg?v=2' ) ?>"/>
                    <?php esc_html_e( 'Member List', 'disciple_tools' ) ?>
                </div>
                <button type="button" class="create-new-record" data-connection-key="members" style="height: 36px;">
                    <?php echo esc_html__( 'Create', 'disciple_tools' )?>
                    <img style="height: 14px; width: 14px" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/small-add.svg?v=2' ) ?>"/>
                </button>
                <button type="button"
                        class="add-new-member">
                    <?php echo esc_html__( 'Select', 'disciple_tools' )?>
                    <img style="height: 16px; width: 16px" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/add-group.svg?v=2' ) ?>"/>
                </button>
            </div>
            <div class="members-section" style="margin-bottom:10px">
                <div id="empty-members-list-message"><?php esc_html_e( "To add new members, click on 'Create' or 'Select'.", 'disciple_tools' ) ?></div>
                <div class="member-list">

                </div>
            </div>
            <div class="reveal" id="add-new-group-member-modal" data-reveal style="min-height:500px">
                <h3><?php echo esc_html_x( 'Add members from existing contacts', 'Add members modal', 'disciple_tools' )?></h3>
                <p><?php echo esc_html_x( "In the 'Member List' field, type the name of an existing contact to add them to this group.", 'Add members modal', 'disciple_tools' )?></p>

                <?php render_field_for_display( 'members', $fields, $post, false ); ?>

                <div class="grid-x pin-to-bottom">
                    <div class="cell">
                        <hr>
                        <span style="float:right; bottom: 0;">
                    <button class="button" data-close aria-label="Close reveal" type="button">
                        <?php echo esc_html__( 'Close', 'disciple_tools' )?>
                    </button>
                </span>
                    </div>
                </div>
                <button class="close-button" data-close aria-label="<?php esc_html_e( 'Close', 'disciple_tools' ); ?>" type="button">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php }
    }

    public function dt_details_additional_tiles( $tiles, $post_type = '' ){

        if ( $post_type === 'groups' ){
            $tiles['relationships'] = [ 'label' => __( 'Member List', 'disciple_tools' ) ];
            $tiles['health-metrics'] = [ 'label' => __( 'Church Health', 'disciple_tools' ) ];
            if ( self::four_fields_is_enabled() ){
                $tiles['four-fields'] = [
                    'label' => __( 'Four Fields', 'disciple_tools' ),
                    'description' => " Zúme article on 4 Fields: https://zume.training/four-fields-tool \r\n\r\n" . _x( 'There are 5 squares in the Four Fields diagram. Starting in the top left quadrant and going clockwise and the fifth being in the middle, they stand for:', 'Optional Documentation', 'disciple_tools' ),
                ];
            }
            $tiles['groups'] = [ 'label' => __( 'Groups', 'disciple_tools' ) ];
            $tiles['other'] = [ 'label' => __( 'Other', 'disciple_tools' ) ];
        }
        return $tiles;
    }

    public function dt_custom_tiles_after_combine( $tile_options, $post_type = '' ){

        if ( $post_type === 'groups' ) {
            foreach ( $tile_options as $tile_key => $_ ) {
                if ( !$this->is_tile_enabled( $post_type, $tile_key ) ) {
                    unset( $tile_options[$tile_key] );
                    continue;
                }
            }
        }

        return $tile_options;
    }

    /**
     * Is the tile disabled by some higher preference
     */
    public function is_tile_enabled( $post_type, $tile_key ) {
        $preferences = [];

        if ( $post_type === 'groups' ) {
            $preferences = dt_get_option( 'group_preferences' );
        }

        if ( !isset( $preferences ) || empty( $preferences ) ) {
            return true;
        }

        // get the correct key for the preferences
        // If the same key as the tile is used in the preferences option then we have no need for the map.
        $key_map = [
            'four-fields' => 'four_fields',
            'health-metrics' => 'church_metrics',
        ];

        $preference_key = $tile_key;

        if ( array_key_exists( $tile_key, $key_map ) ) {
            $preference_key = $key_map[$tile_key];
        }

        return isset( $preferences[$preference_key] ) ? $preferences[$preference_key] : true;
    }


    //action when a post connection is added during create or update
    public function post_connection_added( $post_type, $post_id, $field_key, $value ){
        if ( $post_type === 'groups' ){
            if ( $field_key === 'members' ){
                // share the group with the owner of the contact when a member is added to a group
                $assigned_to = get_post_meta( $value, 'assigned_to', true );
                if ( $assigned_to && strpos( $assigned_to, '-' ) !== false ){
                    $user_id = explode( '-', $assigned_to )[1];
                    if ( $user_id ){
                        DT_Posts::add_shared( $post_type, $post_id, $user_id, null, false, false );
                    }
                }
            }
            if ( $field_key === 'coaches' ){
                // share the group with the coach when a coach is added.
                $user_id = get_post_meta( $value, 'corresponds_to_user', true );
                if ( $user_id ){
                    DT_Posts::add_shared( 'groups', $post_id, $user_id, null, false, false, false );
                }
            }
        }
        if ( $post_type === 'contacts' && $field_key === 'groups' ){
            // share the group with the owner of the contact.
            $assigned_to = get_post_meta( $post_id, 'assigned_to', true );
            if ( $assigned_to && strpos( $assigned_to, '-' ) !== false ){
                $user_id = explode( '-', $assigned_to )[1];
                if ( $user_id ){
                    DT_Posts::add_shared( 'groups', $value, $user_id, null, false, false );
                }
            }
        }
    }


    //filter at the start of post update
    public function dt_post_update_fields( $fields, $post_type, $post_id ){
        if ( $post_type === 'groups' ){
            $existing_group = DT_Posts::get_post( 'groups', $post_id, true, false );
            //if group is updated to church, set the group start date
            if ( isset( $fields['group_type'] ) && empty( $fields['church_start_date'] ) && empty( $existing_group['church_start_date'] ) && $fields['group_type'] === 'church' ){
                $fields['church_start_date'] = time();
            }
            //if group is updated to inactive, set the group end date
            if ( isset( $fields['group_status'] ) && empty( $fields['end_date'] ) && empty( $existing_group['end_date'] ) && $fields['group_status'] === 'inactive' ){
                $fields['end_date'] = time();
            }
        }
        if ( $post_type === 'contacts' ){
            //if updating a contact to be a loader of a group, also add the contact to the group members
            if ( isset( $fields['group_leader']['values'] ) ){
                $existing_contact = DT_Posts::get_post( 'contacts', $post_id, true, false );
                foreach ( $fields['group_leader']['values'] as $leader ){
                    $is_in_group = false;
                    foreach ( $existing_contact['groups'] as $group ){
                        if ( (int) $group['ID'] === (int) $leader['value'] ){
                            $is_in_group = true;
                        }
                    }
                    if ( !$is_in_group && empty( $leader['delete'] ) ){
                        $fields['groups']['values'][] = [
                            'value' => $leader['value']
                        ];
                    }
                }
            }
        }
        return $fields;
    }


    //check to see if the group is marked as needing an update
    //if yes: mark as updated
    private static function check_requires_update( $group_id ){
        if ( get_current_user_id() ){
            $requires_update = get_post_meta( $group_id, 'requires_update', true );
            if ( $requires_update == 'yes' || $requires_update == true || $requires_update == '1' ){
                //don't remove update needed if the user is a dispatcher (and not assigned to the groups.)
                if ( DT_Posts::can_view_all( 'groups' ) ){
                    if ( dt_get_user_id_from_assigned_to( get_post_meta( $group_id, 'assigned_to', true ) ) === get_current_user_id() ){
                        update_post_meta( $group_id, 'requires_update', false );
                    }
                } else {
                    update_post_meta( $group_id, 'requires_update', false );
                }
            }
        }
    }

    //filter when a comment is created
    public function dt_comment_created( $post_type, $post_id, $comment_id, $type ){
        if ( $post_type === 'groups' ){
            if ( $type === 'comment' ){
                self::check_requires_update( $post_id );
            }
        }
    }

    // add members meta to post details
    public function dt_after_get_post_fields_filter( $fields, $details_post_type ) {
        if ( $details_post_type !== $this->post_type || empty( $fields['members'] ) ) {
            return $fields;
        }
        global $wpdb;
        // loop through the members array, and get the overall_status and milestones meta data
        // for each member
        $field_settings = DT_Posts::get_post_field_settings( 'contacts' );
        $overall_status_settings = $field_settings['overall_status']['default'];
        $milestone_settings = $field_settings['milestones']['default'];

        $defaults_to_display = [
            'baptized',
            'has_bible',
        ];
        $default_milestones_to_display = apply_filters( 'dt_members_extra_data', $defaults_to_display );

        $default_milestone_keys = array_map( function ( $milestone ) {
            return "milestone_$milestone";
        }, $default_milestones_to_display);

        // set up the MySQL OR string to get multiple posts at once
        $members_post_ids = [];
        foreach ( $fields['members'] as $member ) {
            $member_id = $member['ID'];
            $members_post_ids[] = "post_id = $member_id";
        }
        $members_or_string = implode( ' OR ', $members_post_ids );

        $results = $wpdb->get_results( $wpdb->prepare( "
        SELECT *
        FROM $wpdb->postmeta AS pm
        WHERE
            ( %1s )
            AND
            (
                meta_key = 'milestones'
                OR meta_key = 'overall_status'
            )
        ORDER BY pm.post_id ASC
        ", $members_or_string ) );

        // order the results by id in a lookup array
        $results_by_post_id = [];
        foreach ( $results as $result ) {
            if ( !key_exists( $result->post_id, $results_by_post_id ) ) {
                $results_by_post_id[$result->post_id] = [];
            }
            $results_by_post_id[$result->post_id][] = $result;
        }

        // pump the member metadata into the members array of the post
        foreach ( $fields['members'] as $key => $member ) {
            $member_id = $member['ID'];
            $member_data = key_exists( $member_id, $results_by_post_id ) ? $results_by_post_id[$member_id] : [];
            $data = [
                'milestones' => [],
            ];
            foreach ( $member_data as $meta ) {
                if ( $meta->meta_key === 'milestones' && in_array( $meta->meta_value, $default_milestone_keys, true ) ) {
                    $data['milestones'][] = $milestone_settings[$meta->meta_value];
                } elseif ( $meta->meta_key === 'overall_status' && isset( $overall_status_settings[$meta->meta_value] ) ) {
                    $data['overall_status'] = $overall_status_settings[$meta->meta_value];
                }
            }
            // uniqueify the milestones array
            $data['milestones'] = array_reduce( $data['milestones'], function ( $array, $milestone ){
                if ( !in_array( $milestone, $array, true ) ) {
                    $array[] = $milestone;
                    return $array;
                }
                return $array;
            }, []);
            $fields['members'][$key]['data'] = $data;
        }

        return $fields;
    }

    // filter at the start of post creation
    public function dt_post_create_fields( $fields, $post_type ){
        if ( $post_type === 'groups' ) {
            if ( !isset( $fields['group_status'] ) ) {
                $fields['group_status'] = 'active';
            }
            if ( !isset( $fields['group_type'] ) ) {
                $fields['group_type'] = 'pre-group';
            }
            if ( !isset( $fields['assigned_to'] ) ) {
                $fields['assigned_to'] = sprintf( 'user-%d', get_current_user_id() );
            }
            if ( !isset( $fields['start_date'] ) ) {
                $fields['start_date'] = time();
            }
            if ( isset( $fields['group_type'] ) && !isset( $fields['church_start_date'] ) && $fields['group_type'] === 'church' ){
                $fields['church_start_date'] = time();
            }
        }
        return $fields;
    }

    //action when a post has been created
    public function dt_post_created( $post_type, $post_id, $initial_fields ){
        if ( $post_type === 'groups' ){
            do_action( 'dt_group_created', $post_id, $initial_fields );
        }
    }


    //list page filters function
    private static function get_my_groups_status_type(){
        global $wpdb;

        $results = $wpdb->get_results( $wpdb->prepare( "
            SELECT status.meta_value as group_status, pm.meta_value as group_type, count(pm.post_id) as count, count(un.post_id) as update_needed
            FROM $wpdb->postmeta pm
            INNER JOIN $wpdb->postmeta status ON( status.post_id = pm.post_id AND status.meta_key = 'group_status' )
            INNER JOIN $wpdb->posts a ON( a.ID = pm.post_id AND a.post_type = 'groups' and a.post_status = 'publish' )
            INNER JOIN $wpdb->postmeta as assigned_to ON a.ID=assigned_to.post_id
              AND assigned_to.meta_key = 'assigned_to'
              AND assigned_to.meta_value = CONCAT( 'user-', %s )
            LEFT JOIN $wpdb->postmeta un ON ( un.post_id = pm.post_id AND un.meta_key = 'requires_update' AND un.meta_value = '1' )
            WHERE pm.meta_key = 'group_type'
            GROUP BY status.meta_value, pm.meta_value
        ", get_current_user_id() ), ARRAY_A);

        return $results;
    }

    //list page filters function
    private static function get_all_groups_status_type(){
        global $wpdb;
        if ( current_user_can( 'view_any_groups' ) ){
            $results = $wpdb->get_results("
                SELECT status.meta_value as group_status, pm.meta_value as group_type, count(pm.post_id) as count, count(un.post_id) as update_needed
                FROM $wpdb->postmeta pm
                INNER JOIN $wpdb->postmeta status ON( status.post_id = pm.post_id AND status.meta_key = 'group_status' )
                INNER JOIN $wpdb->posts a ON( a.ID = pm.post_id AND a.post_type = 'groups' and a.post_status = 'publish' )
                LEFT JOIN $wpdb->postmeta un ON ( un.post_id = pm.post_id AND un.meta_key = 'requires_update' AND un.meta_value = '1' )
                WHERE pm.meta_key = 'group_type'
                GROUP BY status.meta_value, pm.meta_value
            ", ARRAY_A);
        } else {
            $results = $wpdb->get_results($wpdb->prepare("
                SELECT status.meta_value as group_status, pm.meta_value as group_type, count(pm.post_id) as count, count(un.post_id) as update_needed
                FROM $wpdb->postmeta pm
                INNER JOIN $wpdb->postmeta status ON( status.post_id = pm.post_id AND status.meta_key = 'group_status' )
                INNER JOIN $wpdb->posts a ON( a.ID = pm.post_id AND a.post_type = 'groups' and a.post_status = 'publish' )
                LEFT JOIN $wpdb->dt_share AS shares ON ( shares.post_id = a.ID AND shares.user_id = %s )
                LEFT JOIN $wpdb->postmeta assigned_to ON ( assigned_to.post_id = pm.post_id AND assigned_to.meta_key = 'assigned_to' && assigned_to.meta_value = %s )
                LEFT JOIN $wpdb->postmeta un ON ( un.post_id = pm.post_id AND un.meta_key = 'requires_update' AND un.meta_value = '1' )
                WHERE pm.meta_key = 'group_type'
                AND ( shares.user_id IS NOT NULL OR assigned_to.meta_value IS NOT NULL )
                GROUP BY status.meta_value, pm.meta_value
            ", get_current_user_id(), 'user-' . get_current_user_id() ), ARRAY_A);
        }

        return $results;
    }

    //build list page filters
    public static function dt_user_list_filters( $filters, $post_type ){
        if ( $post_type === 'groups' ){
            $performance_mode = get_option( 'dt_performance_mode', false );
            $fields = DT_Posts::get_post_field_settings( $post_type );
            $post_label_plural = DT_Posts::get_post_settings( $post_type )['label_plural'];
            /**
             * Setup my group filters
             */
            $counts = [];
            if ( !$performance_mode ) {
                $counts = self::get_my_groups_status_type();
                $active_counts = [];
                $update_needed = 0;
                $status_counts = [];
                $total_my = 0;
                foreach ( $counts as $count ){
                    $total_my += $count['count'];
                    dt_increment( $status_counts[$count['group_status']], $count['count'] );
                    if ( $count['group_status'] === 'active' ){
                        if ( isset( $count['update_needed'] ) ) {
                            $update_needed += (int) $count['update_needed'];
                        }
                        dt_increment( $active_counts[$count['group_type']], $count['count'] );
                    }
                }
            }


            $filters['tabs'][] = [
                'key' => 'assigned_to_me',
                'label' => __( 'Assigned to me', 'disciple_tools' ),
                'count' => $total_my ?? '',
                'order' => 20
            ];
            // add assigned to me filters
            $filters['filters'][] = [
                'ID' => 'my_all',
                'tab' => 'assigned_to_me',
                'name' => __( 'All', 'disciple_tools' ),
                'query' => [
                    'assigned_to' => [ 'me' ],
                ],
                'count' => $total_my ?? '',
            ];
            foreach ( $fields['group_status']['default'] as $status_key => $status_value ) {
                if ( isset( $status_counts[$status_key] ) || $performance_mode ){
                    $filters['filters'][] = [
                        'ID' => 'my_' . $status_key,
                        'tab' => 'assigned_to_me',
                        'name' => $status_value['label'],
                        'query' => [
                            'assigned_to' => [ 'me' ],
                            'group_status' => [ $status_key ],
                        ],
                        'count' => $status_counts[$status_key] ?? ''
                    ];
                    if ( $status_key === 'active' ){
                        if ( $performance_mode || ( $update_needed ?? 0 ) > 0 ){
                            $filters['filters'][] = [
                                'ID' => 'my_update_needed',
                                'tab' => 'assigned_to_me',
                                'name' => $fields['requires_update']['name'],
                                'query' => [
                                    'assigned_to' => [ 'me' ],
                                    'group_status' => [ 'active' ],
                                    'requires_update' => [ true ],
                                ],
                                'count' => $update_needed ?? '',
                                'subfilter' => true
                            ];
                        }
                        foreach ( $fields['group_type']['default'] as $group_type_key => $group_type_value ) {
                            if ( isset( $active_counts[$group_type_key] ) || $performance_mode ) {
                                $filters['filters'][] = [
                                    'ID' => 'my_' . $group_type_key,
                                    'tab' => 'assigned_to_me',
                                    'name' => $group_type_value['label'],
                                    'query' => [
                                        'assigned_to' => [ 'me' ],
                                        'group_status' => [ 'active' ],
                                        'group_type' => [ $group_type_key ],
                                    ],
                                    'count' => $active_counts[$group_type_key] ?? '',
                                    'subfilter' => true
                                ];
                            }
                        }
                    }
                }
            }


            $counts = [];
            if ( !$performance_mode ){
                $counts = self::get_all_groups_status_type();
                $active_counts = [];
                $update_needed = 0;
                $status_counts = [];
                $total_all = 0;
                foreach ( $counts as $count ){
                    $total_all += $count['count'];
                    dt_increment( $status_counts[$count['group_status']], $count['count'] );
                    if ( $count['group_status'] === 'active' ){
                        if ( isset( $count['update_needed'] ) ) {
                            $update_needed += (int) $count['update_needed'];
                        }
                        dt_increment( $active_counts[$count['group_type']], $count['count'] );
                    }
                }
            }
            $filters['tabs'][] = [
                'key' => 'all',
                'label' => __( 'Default Filters', 'disciple_tools' ),
                'count' => $total_all ?? '',
                'order' => 10
            ];
            // add assigned to me filters
            $filters['filters'][] = [
                'ID' => 'all',
                'tab' => 'all',
                'name' => sprintf( _x( 'All %s', 'All records', 'disciple_tools' ), $post_label_plural ),
                'count' => $total_all ?? ''
            ];
            $filters['filters'][] = [
                'ID' => 'favorite',
                'tab' => 'all',
                'name' => sprintf( _x( 'Favorite %s', 'Favorite Contacts', 'disciple_tools' ), $post_label_plural ),
                'query' => [
                    'fields' => [ 'favorite' => [ '1' ] ],
                ],
                'labels' => [
                    [ 'id' => '1', 'name' => __( 'Favorite', 'disciple_tools' ) ]
                ]
            ];
            $filters['filters'][] = [
                'ID' => 'recent',
                'tab' => 'all',
                'name' => __( 'My Recently Viewed', 'disciple_tools' ),
                'query' => [
                    'dt_recent' => true
                ],
                'labels' => [
                    [ 'id' => 'recent', 'name' => __( 'Last 30 viewed', 'disciple_tools' ) ]
                ]
            ];

            foreach ( $fields['group_status']['default'] as $status_key => $status_value ) {
                if ( isset( $status_counts[$status_key] ) || $performance_mode ){
                    $filters['filters'][] = [
                        'ID' => 'all_' . $status_key,
                        'tab' => 'all',
                        'name' => $status_value['label'],
                        'query' => [
                            'group_status' => [ $status_key ],
                        ],
                        'count' => $status_counts[$status_key] ?? ''
                    ];
                    if ( $status_key === 'active' ){
                        if ( ( $update_needed ?? 0 ) > 0 || $performance_mode ){
                            $filters['filters'][] = [
                                'ID' => 'all_update_needed',
                                'tab' => 'all',
                                'name' => $fields['requires_update']['name'],
                                'query' => [
                                    'group_status' => [ 'active' ],
                                    'requires_update' => [ true ],
                                ],
                                'count' => $update_needed ?? '',
                                'subfilter' => true
                            ];
                        }
                        foreach ( $fields['group_type']['default'] as $group_type_key => $group_type_value ) {
                            if ( isset( $active_counts[$group_type_key] ) || $performance_mode ) {
                                $filters['filters'][] = [
                                    'ID' => 'all_' . $group_type_key,
                                    'tab' => 'all',
                                    'name' => $group_type_value['label'],
                                    'query' => [
                                        'group_status' => [ 'active' ],
                                        'group_type' => [ $group_type_key ],
                                    ],
                                    'count' => $active_counts[$group_type_key] ?? '',
                                    'subfilter' => true
                                ];
                            }
                        }
                    }
                }
            }
        }
        return $filters;
    }

    public static function dt_filter_access_permissions( $permissions, $post_type ){
        if ( $post_type === 'groups' ){
            if ( DT_Posts::can_view_all( $post_type ) ){
                $permissions = [];
            }
        }
        return $permissions;
    }

    public function scripts(){
        if ( is_singular( 'groups' ) && get_the_ID() && DT_Posts::can_view( $this->post_type, get_the_ID() ) ){
            wp_enqueue_script( 'dt_groups', get_template_directory_uri() . '/dt-groups/groups.js', [
                'jquery',
                'details'
            ], filemtime( get_theme_file_path() . '/dt-groups/groups.js' ), true );
        }
    }
}
