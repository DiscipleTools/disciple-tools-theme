<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly.

class DT_Groups_Base extends DT_Module_Base {
    private static $_instance = null;
    public $post_type = "groups";
    public $module = "groups_base";
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
        add_action( 'p2p_init', [ $this, 'p2p_init' ] );
        add_filter( 'dt_custom_fields_settings', [ $this, 'dt_custom_fields_settings' ], 10, 2 );
        add_filter( 'dt_details_additional_tiles', [ $this, 'dt_details_additional_tiles' ], 10, 2 );
        add_action( 'dt_details_additional_section', [ $this, 'dt_details_additional_section' ], 20, 2 );
        add_action( 'wp_enqueue_scripts', [ $this, 'scripts' ], 99 );

        // hooks
        add_action( "post_connection_removed", [ $this, "post_connection_removed" ], 10, 4 );
        add_action( "post_connection_added", [ $this, "post_connection_added" ], 10, 4 );
        add_filter( "dt_post_update_fields", [ $this, "dt_post_update_fields" ], 10, 3 );
        add_filter( "dt_post_create_fields", [ $this, "dt_post_create_fields" ], 10, 2 );
        add_action( "dt_post_created", [ $this, "dt_post_created" ], 10, 3 );
        add_action( "dt_comment_created", [ $this, "dt_comment_created" ], 10, 4 );

        //list
        add_filter( "dt_user_list_filters", [ $this, "dt_user_list_filters" ], 10, 2 );
        add_filter( "dt_filter_access_permissions", [ $this, "dt_filter_access_permissions" ], 20, 2 );

    }

    public function after_setup_theme(){
        if ( class_exists( 'Disciple_Tools_Post_Type_Template' )) {
            new Disciple_Tools_Post_Type_Template( "groups", __( 'Group', 'disciple_tools' ), __( 'Groups', 'disciple_tools' ) );
        }
    }
    public function dt_set_roles_and_permissions( $expected_roles ){
        if ( !isset( $expected_roles["multiplier"] ) ){
            $expected_roles["multiplier"] = [
                "label" => __( 'Multiplier', 'disciple_tools' ),
                "permissions" => []
            ];
        }
        // if the user can access contact they also can access group
        foreach ( $expected_roles as $role => $role_value ){
            if ( isset( $expected_roles[$role]["permissions"]['access_contacts'] ) && $expected_roles[$role]["permissions"]['access_contacts'] ){
                $expected_roles[$role]["permissions"]['access_' . $this->post_type] = true;
                $expected_roles[$role]["permissions"]['create_' . $this->post_type] = true;
            }
        }

        if ( isset( $expected_roles["administrator"] ) ){
            $expected_roles["administrator"]["permissions"]['view_any_groups'] = true;
            $expected_roles["administrator"]["permissions"]['update_any_groups'] = true;
            $expected_roles["administrator"]["permissions"]["dt_all_admin_groups"] = true;
        }
        if ( isset( $expected_roles["dispatcher"] ) ){
            $expected_roles["dispatcher"]["permissions"]['view_any_groups'] = true;
            $expected_roles["dispatcher"]["permissions"]['update_any_groups'] = true;
        }
        if ( isset( $expected_roles["dt_admin"] ) ){
            $expected_roles["dt_admin"]["permissions"]['view_any_groups'] = true;
            $expected_roles["dt_admin"]["permissions"]['update_any_groups'] = true;
        }

        return $expected_roles;
    }


    public function dt_custom_fields_settings( $fields, $post_type ){
        if ( $post_type === 'groups' ){
            $fields['tags'] = [
                'name'        => __( 'Tags', 'disciple_tools' ),
                'description' => _x( 'A useful way to group related items and can help group contacts associated with noteworthy characteristics. e.g. business owner, sports lover. The contacts can also be filtered using these tags.', 'Optional Documentation', 'disciple_tools' ),
                'type'        => 'multi_select',
                'default'     => [],
                'tile'        => 'other',
                'custom_display' => true,
                'icon' => get_template_directory_uri() . '/dt-assets/images/tag.svg'
            ];
            $fields["follow"] = [
                'name'        => __( 'Follow', 'disciple_tools' ),
                'type'        => 'multi_select',
                'default'     => [],
                'section'     => 'misc',
                'hidden'      => true
            ];
            $fields["unfollow"] = [
                'name'        => __( 'Un-Follow', 'disciple_tools' ),
                'type'        => 'multi_select',
                'default'     => [],
                'hidden'      => true
            ];
            $fields["requires_update"] = [
                'name'        => __( 'Requires Update', 'disciple_tools' ),
                'description' => '',
                'type'        => 'boolean',
                'default'     => false,
            ];
            $fields['tasks'] = [
                'name' => __( 'Tasks', 'disciple_tools' ),
                'type' => 'post_user_meta',
            ];
            $fields["duplicate_data"] = [
                "name" => 'Duplicates', //system string does not need translation
                'type' => 'array',
                'default' => [],
            ];
            $fields['assigned_to'] = [
                'name'        => __( 'Assigned To', 'disciple_tools' ),
                'description' => __( "Select the main person who is responsible for reporting on this group.", 'disciple_tools' ),
                'type'        => 'user_select',
                'default'     => '',
                'tile' => 'status',
                'icon' => get_template_directory_uri() . '/dt-assets/images/assigned-to.svg',
                "show_in_table" => 16,
                'custom_display' => true,
            ];
            $fields["coaches"] = [
                "name" => __( 'Group Coach / Church Planter', 'disciple_tools' ),
                'description' => _x( 'The person who planted and/or is coaching this group. Only one person can be assigned to a group while multiple people can be coaches / church planters of this group.', 'Optional Documentation', 'disciple_tools' ),
                "type" => "connection",
                "post_type" => "contacts",
                "p2p_direction" => "from",
                "p2p_key" => "groups_to_coaches",
                'tile' => 'status',
                'custom_display' => true,
                'icon' => get_template_directory_uri() . '/dt-assets/images/coach.svg',
                'create-icon' => get_template_directory_uri() . '/dt-assets/images/add-contact.svg',
            ];
            $fields['group_status'] = [
                'name'        => __( 'Group Status', 'disciple_tools' ),
                'description' => _x( 'Set the current status of the group.', 'field description', 'disciple_tools' ),
                'type'        => 'key_select',
                'default'     => [
                    'inactive' => [
                        'label' => __( 'Inactive', 'disciple_tools' ),
                        'description' => _x( 'The group is no longer meeting.', 'field description', 'disciple_tools' ),
                        'color' => "#F43636"
                    ],
                    'active'   => [
                        'label' => __( 'Active', 'disciple_tools' ),
                        'description' => _x( 'The group is actively meeting.', 'field description', 'disciple_tools' ),
                        'color' => "#4CAF50"
                    ],
                ],
                'tile'     => 'status',
                'custom_display' => true,
                'icon' => get_template_directory_uri() . '/dt-assets/images/status.svg',
                "default_color" => "#366184",
                "show_in_table" => 10,
            ];

            $fields['group_type'] = [
                'name'        => __( 'Group Type', 'disciple_tools' ),
                'description' => '',
                'type'        => 'key_select',
                'default'     => [
                    'pre-group' => [
                        "label" => __( 'Pre-Group', 'disciple_tools' ),
                        "description" => _x( "A group predominantly of non-believers.", 'Optional Documentation', 'disciple_tools' ),
                    ],
                    'group'     => [
                        "label" => __( 'Group', 'disciple_tools' ),
                        "description" => _x( "A group having 3 or more believers but not identifying as church.", 'Optional Documentation', 'disciple_tools' ),
                    ],
                    'church'    => [
                        "label" => __( 'Church', 'disciple_tools' ),
                        "description" => _x( "A group having 3 or more believers and identifying as church.", 'Optional Documentation', 'disciple_tools' ),
                    ],
                    'team'    => [
                        "label" => __( 'Team', 'disciple_tools' ),
                        "description" => _x( "A special group that is not meeting as a church (or trying to become church).", 'Optional Documentation', 'disciple_tools' ),
                    ],
                ],
                "customizable" => "add_only",
                'tile' => 'groups',
                'in_create_form' => true,
                "show_in_table" => 15,
                "icon" => get_template_directory_uri() . '/dt-assets/images/circle-square-triangle.svg',
            ];




            $fields['health_metrics'] = [
                "name" => __( 'Church Health', 'disciple_tools' ),
                'description' => _x( "Track the progress and health of a group/church.", 'Optional Documentation', 'disciple_tools' ),
                "type" => "multi_select",
                "default" => [
                    "church_baptism" => [
                        "label" => __( "Baptism", 'disciple_tools' ),
                        "description" => _x( "The group is baptising.", 'Optional Documentation', 'disciple_tools' ),
                        "image" => get_template_directory_uri() . '/dt-assets/images/groups/baptism.svg'
                    ],
                    "church_bible" => [
                        "label" => __( "Bible Study", 'disciple_tools' ),
                        "description" => _x( "The group is studying the bible.", 'Optional Documentation', 'disciple_tools' ),
                        "image" => get_template_directory_uri() . '/dt-assets/images/groups/word.svg'
                    ],
                    "church_communion" => [
                        "label" => __( "Communion", 'disciple_tools' ),
                        "description" => _x( "The group is practicing communion.", 'Optional Documentation', 'disciple_tools' ),
                        "image" => get_template_directory_uri() . '/dt-assets/images/groups/communion.svg'
                    ],
                    "church_fellowship" => [
                        "label" => __( "Fellowship", 'disciple_tools' ),
                        "description" => _x( "The group is fellowshiping.", 'Optional Documentation', 'disciple_tools' ),
                        "image" => get_template_directory_uri() . '/dt-assets/images/groups/heart.svg'
                    ],
                    "church_giving" => [
                        "label" => __( "Giving", 'disciple_tools' ),
                        "description" => _x( "The group is giving.", 'Optional Documentation', 'disciple_tools' ),
                        "image" => get_template_directory_uri() . '/dt-assets/images/groups/giving.svg'
                    ],
                    "church_prayer" => [
                        "label" => __( "Prayer", 'disciple_tools' ),
                        "description" => _x( "The group is praying.", 'Optional Documentation', 'disciple_tools' ),
                        "image" => get_template_directory_uri() . '/dt-assets/images/groups/prayer.svg'
                    ],
                    "church_praise" => [
                        "label" => __( "Praise", 'disciple_tools' ),
                        "description" => _x( "The group is praising.", 'Optional Documentation', 'disciple_tools' ),
                        "image" => get_template_directory_uri() . '/dt-assets/images/groups/praise.svg'
                    ],
                    "church_sharing" => [
                        "label" => __( "Sharing the Gospel", 'disciple_tools' ),
                        "description" => _x( "The group is sharing the gospel.", 'Optional Documentation', 'disciple_tools' ),
                        "image" => get_template_directory_uri() . '/dt-assets/images/groups/evangelism.svg'
                    ],
                    "church_leaders" => [
                        "label" => __( "Leaders", 'disciple_tools' ),
                        "description" => _x( "The group has leaders.", 'Optional Documentation', 'disciple_tools' ),
                        "image" => get_template_directory_uri() . '/dt-assets/images/groups/leadership.svg'
                    ],
                    "church_commitment" => [
                        "label" => __( "Church Commitment", 'disciple_tools' ),
                        "description" => _x( "The group has committed to be church.", 'Optional Documentation', 'disciple_tools' ),
                        "image" => get_template_directory_uri() . '/dt-assets/images/groups/covenant.svg'
                    ],
                ],
                "customizable" => "add_only",
                'tile' => 'health-metrics',
                'custom_display' => true
            ];

            $fields['start_date'] = [
                'name'        => __( 'Start Date', 'disciple_tools' ),
                'description' => _x( 'The date this group began meeting.', 'Optional Documentation', 'disciple_tools' ),
                'type'        => 'date',
                'default'     => time(),
                'tile' => 'details',
                'icon' => get_template_directory_uri() . '/dt-assets/images/date-start.svg',
            ];
            $fields['church_start_date'] =[
                'name' => __( 'Church Start Date', 'disciple_tools' ),
                'description' => _x( 'The date this group first identified as being a church.', 'Optional Documentation', 'disciple_tools' ),
                'type' => 'date',
                'default'     => time(),
                'tile' => 'details',
                'icon' => get_template_directory_uri() . '/dt-assets/images/date-success.svg',

            ];
            $fields['end_date'] = [
                'name'        => __( 'End Date', 'disciple_tools' ),
                'description' => _x( 'The date this group stopped meeting (if applicable).', 'Optional Documentation', 'disciple_tools' ),
                'type'        => 'date',
                'default'     => '',
                'tile' => 'details',
                'icon' => get_template_directory_uri() . '/dt-assets/images/date-end.svg',
            ];



            $fields["member_count"] = [
                'name' => __( 'Member Count', 'disciple_tools' ),
                'description' => _x( 'The number of members in this group. It will automatically be updated when new members are added or removed in the member list. Change this number manually to included people who may not be in the system but are also members of the group.', 'Optional Documentation', 'disciple_tools' ),
                'type' => 'number',
                'default' => '',
                'tile' => 'relationships',
                "show_in_table" => 25,
                "icon" => get_template_directory_uri() . '/dt-assets/images/tally.svg',
            ];
            $fields["members"] = [
                "name" => __( 'Member List', 'disciple_tools' ),
                'description' => _x( 'The contacts who are members of this group.', 'Optional Documentation', 'disciple_tools' ),
                "type" => "connection",
                "post_type" => "contacts",
                "p2p_direction" => "to",
                "p2p_key" => "contacts_to_groups",
                "icon" => get_template_directory_uri() . '/dt-assets/images/list.svg',
            ];
            $fields["leaders"] = [
                "name" => __( 'Leaders', 'disciple_tools' ),
                'description' => '',
                "type" => "connection",
                "post_type" => "contacts",
                "p2p_direction" => "from",
                "p2p_key" => "groups_to_leaders",
                "show_in_table" => 30
            ];
            $fields["leader_count"] = [
                'name' => __( 'Leader Count', 'disciple_tools' ),
                'description' => _x( 'The number of members in this group. It will automatically be updated when new members are added or removed in the member list. Change this number manually to included people who may not be in the system but are also members of the training.', 'Optional Documentation', 'disciple_tools' ),
                'type' => 'number',
                'default' => '',
                'tile' => 'relationships',
                "icon" => get_template_directory_uri() . '/dt-assets/images/tallying.svg',
            ];

            $fields["parent_groups"] = [
                "name" => __( 'Parent Group', 'disciple_tools' ),
                'description' => _x( 'A group that founded this group.', 'Optional Documentation', 'disciple_tools' ),
                "type" => "connection",
                "post_type" => "groups",
                "p2p_direction" => "from",
                "p2p_key" => "groups_to_groups",
                'tile' => 'groups',
                'icon' => get_template_directory_uri() . '/dt-assets/images/group-parent.svg',
                'create-icon' => get_template_directory_uri() . '/dt-assets/images/add-group.svg',
            ];

            $fields["peer_groups"] = [
                "name" => __( 'Peer Group', 'disciple_tools' ),
                'description' => _x( "A related group that isn't a parent/child in relationship. It might indicate groups that collaborate, are about to merge, recently split, etc.", 'Optional Documentation', 'disciple_tools' ),
                "type" => "connection",
                "post_type" => "groups",
                "p2p_direction" => "any",
                "p2p_key" => "groups_to_peers",
                'tile' => 'groups',
                'icon' => get_template_directory_uri() . '/dt-assets/images/group-peer.svg',
                'create-icon' => get_template_directory_uri() . '/dt-assets/images/add-group.svg',
            ];

            $fields["child_groups"] = [
                "name" => __( 'Child Group', 'disciple_tools' ),
                'description' => _x( 'A group that has been birthed out of this group.', 'Optional Documentation', 'disciple_tools' ),
                "type" => "connection",
                "post_type" => "groups",
                "p2p_direction" => "to",
                "p2p_key" => "groups_to_groups",
                'tile' => 'groups',
                'icon' => get_template_directory_uri() . '/dt-assets/images/group-child.svg',
                'create-icon' => get_template_directory_uri() . '/dt-assets/images/add-group.svg',
            ];





            // Group Locations
            $fields['location_grid'] = [
                'name'        => __( 'Locations', 'disciple_tools' ),
                'description' => _x( 'The general location where this contact is located.', 'Optional Documentation', 'disciple_tools' ),
                'type'        => 'location',
                'mapbox'    => false,
                "in_create_form" => true,
                "tile" => "details",
                "icon" => get_template_directory_uri() . "/dt-assets/images/location.svg",
            ];

            $fields['location_grid_meta'] = [
                'name'        => __( 'Locations', 'disciple_tools' ), //system string does not need translation
                'type'        => 'location_meta',
                "tile"      => "details",
                'mapbox'    => false,
                'hidden' => true
            ];

            $fields["contact_address"] = [
                "name" => __( 'Address', 'disciple_tools' ),
                "icon" => get_template_directory_uri() . "/dt-assets/images/house.svg",
                "type" => "communication_channel",
                "tile" => "details",
                'mapbox'    => false,
                "customizable" => false
            ];

            if ( DT_Mapbox_API::get_key() ){
                $fields["contact_address"]["hidden"] = true;
                $fields["contact_address"]["mapbox"] = true;
                $fields["location_grid"]["mapbox"] = true;
                $fields["location_grid_meta"]["mapbox"] = true;
            }



            $fields["people_groups"] = [
                "name" => __( 'People Groups', 'disciple_tools' ),
                'description' => _x( 'The people groups represented by this group.', 'Optional Documentation', 'disciple_tools' ),
                "type" => "connection",
                "post_type" => "peoplegroups",
                "p2p_direction" => "from",
                "p2p_key" => "groups_to_peoplegroups",
                "tile" => "details"
            ];

            /* 4 fields */
            $fields["four_fields_unbelievers"] = [
                'name' => __( 'Unbelievers', 'disciple_tools' ),
                'description' => _x( 'Number of unbelievers in this group.', 'Optional Documentation', 'disciple_tools' ),
                'type' => 'text',
                'default' => ''
            ];
            $fields["four_fields_believers"] = [
                'name' => __( 'Believers', 'disciple_tools' ),
                'description' => _x( 'Number of believers in this group.', 'Optional Documentation', 'disciple_tools' ),
                'type' => 'text',
                'default' => ''
            ];
            $fields["four_fields_accountable"] = [
                'name' => __( 'Accountable', 'disciple_tools' ),
                'description' => _x( 'Number of people in accountability group.', 'Optional Documentation', 'disciple_tools' ),
                'type' => 'text',
                'default' => ''
            ];
            $fields["four_fields_church_commitment"] = [
                'name' => __( 'Church Commitment', 'disciple_tools' ),
                'description' => _x( 'Is this a church - yes or no?', 'Optional Documentation', 'disciple_tools' ),
                'type' => 'text',
                'default' => ''
            ];
            $fields["four_fields_multiplying"] = [
                'name' => __( 'Multiplying', 'disciple_tools' ),
                'description' => _x( 'Number of people helping start other groups. How many members are multiplying?', 'Optional Documentation', 'disciple_tools' ),
                'type' => 'text',
                'default' => ''
            ];



        }

        if ( $post_type === "contacts" ){
            $fields["groups"] = [
                "name" => __( "Groups", 'disciple_tools' ),
                "description" => _x( "Groups this contact is a member of.", 'Optional Documentation', 'disciple_tools' ),
                "type" => "connection",
                "post_type" => "groups",
                "p2p_direction" => "from",
                "p2p_key" => "contacts_to_groups",
                "tile" => "other",
                'icon' => get_template_directory_uri() . "/dt-assets/images/group-type.svg",
                'create-icon' => get_template_directory_uri() . "/dt-assets/images/add-group.svg",
                "show_in_table" => 35
            ];
        }
        return $fields;
    }

    public function dt_details_additional_section( $section, $post_type ){
        // Display 'Group Status' tile
        if ( $post_type === "groups" && $section === "status" ){
            $group = DT_Posts::get_post( $post_type, get_the_ID() );
            $group_fields = DT_Posts::get_post_field_settings( $post_type );
            ?>

                <div class="cell small-12 medium-4">
                    <?php $group_fields['group_status']["custom_display"] = false ?>
                    <?php render_field_for_display( "group_status", $group_fields, $group, true ); ?>
                </div>
                <div class="cell small-12 medium-4">
                    <div class="section-subheader">
                        <img src="<?php echo esc_url( get_template_directory_uri() ) . '/dt-assets/images/assigned-to.svg' ?>">
                        <?php echo esc_html( $group_fields["assigned_to"]["name"] )?>
                        <button class="help-button" data-section="assigned-to-help-text">
                            <img class="help-icon" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/help.svg' ) ?>"/>
                        </button>
                    </div>

                    <div class="assigned_to details">
                        <var id="assigned_to-result-container" class="result-container assigned_to-result-container"></var>
                        <div id="assigned_to_t" name="form-assigned_to" class="scrollable-typeahead">
                            <div class="typeahead__container">
                                <div class="typeahead__field">
                                    <span class="typeahead__query">
                                        <input class="js-typeahead-assigned_to input-height"
                                               name="assigned_to[query]" placeholder="<?php echo esc_html_x( "Search Users", 'input field placeholder', 'disciple_tools' ) ?>"
                                               autocomplete="off">
                                    </span>
                                    <span class="typeahead__button">
                                        <button type="button" class="search_assigned_to typeahead__image_button input-height" data-id="assigned_to_t">
                                            <img src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/chevron_down.svg' ) ?>"/>
                                        </button>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="cell small-12 medium-4">
                    <?php $group_fields['coaches']["custom_display"] = false ?>
                    <?php render_field_for_display( "coaches", $group_fields, $group, true ); ?>
                </div>
        <?php }

        // Display 'Other' tile
        if ( $post_type === "groups" && $section === "other" ) :
            $fields = DT_Posts::get_post_field_settings( $post_type );
            ?>
            <div class="section-subheader">
                <img class="dt-icon" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/tag.svg' ) ?>"/>
                <?php echo esc_html( $fields["tags"]["name"] ) ?>
            </div>
            <div class="tags">
                <var id="tags-result-container" class="result-container"></var>
                <div id="tags_t" name="form-tags" class="scrollable-typeahead typeahead-margin-when-active">
                    <div class="typeahead__container">
                        <div class="typeahead__field">
                            <span class="typeahead__query">
                                <input class="js-typeahead-tags input-height"
                                       name="tags[query]"
                                       placeholder="<?php echo esc_html( sprintf( _x( "Search %s", "Search 'something'", 'disciple_tools' ), $fields["tags"]['name'] ) )?>"
                                       autocomplete="off">
                            </span>
                            <span class="typeahead__button">
                                <button type="button" data-open="create-tag-modal" class="create-new-tag typeahead__image_button input-height">
                                    <img src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/tag-add.svg' ) ?>"/>
                                </button>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif;

        // Display 'Health Metrics' tile
        if ( $post_type === "groups" && $section === "health-metrics" ) {
            $group_preferences = dt_get_option( 'group_preferences' );
            $fields = DT_Posts::get_post_field_settings( $post_type );
            //<!-- Health Metrics-->
            if ( ! empty( $group_preferences['church_metrics'] ) ) : ?>

                <div class="grid-x">
                    <div style="margin-right:auto; margin-left:auto;min-height:302px">
                        <object id="church-svg-wrapper" type="image/svg+xml" data="<?php echo esc_attr( get_template_directory_uri() . '/dt-assets/images/groups/church-wheel.svg' ); ?>"></object>
                    </div>
                </div>
                <div style="display:flex;flex-wrap:wrap;margin-top:10px" class=" js-progress-bordered-box half-opacity">
                    <?php foreach ( $fields["health_metrics"]["default"] as $key => $option ) : ?>
                        <div class="group-progress-button-wrapper">
                            <button  class="group-progress-button" id="<?php echo esc_html( $key ) ?>">
                                <img src="<?php echo esc_html( $option["image"] ?? "" ) ?>">
                            </button>
                            <p><?php echo esc_html( $option["label"] ) ?> </p>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

        <?php }
            // Display 'Four Fields' tile
        if ( $post_type === "groups" && $section === "four-fields" ) {
            $group_preferences = dt_get_option( 'group_preferences' );
            $fields = DT_Posts::get_post_field_settings( $post_type );
            //<!-- Health Metrics-->
            if ( ! empty( $group_preferences['four_fields'] ) ) : ?>
                <!-- Four Fields -->
                <section id="four-fields" class="xlarge-6 large-12 medium-6 cell">

                        <div class="section-body">
                            <!-- start collapse -->
                            <div style="background:url('<?php echo esc_attr( get_template_directory_uri() . '/dt-assets/images/four-fields.svg' ); ?>');background-size: 100% 100%;height: 379px;display: grid;grid-template-columns: 1fr 1fr 1fr;grid-template-rows: auto;justify-items: center;align-items: center;" id="four-fields-inputs">

                            </div>
                            <!-- end collapse -->
                        </div>
                </section>
            <?php endif; ?>



        <?php }

        if ( $post_type === "groups" && $section === "relationships" ) {
            $fields = DT_Posts::get_post_field_settings( $post_type );
            $post = DT_Posts::get_post( "groups", get_the_ID() );
            ?>
            <div class="section-subheader members-header" style="padding-top: 10px;">
                <div style="padding-bottom: 5px; margin-right:10px; display: inline-block">
                    <img class="dt-icon" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/list.svg' ) ?>"/>
                    <?php esc_html_e( "Member List", 'disciple_tools' ) ?>
                </div>
                <button type="button" class="create-new-record" data-connection-key="members" style="height: 36px;">
                    <?php echo esc_html__( 'Create', 'disciple_tools' )?>
                    <img style="height: 14px; width: 14px" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/small-add.svg' ) ?>"/>
                </button>
                <button type="button"
                        class="add-new-member">
                    <?php echo esc_html__( 'Select', 'disciple_tools' )?>
                    <img style="height: 16px; width: 16px" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/add-group.svg' ) ?>"/>
                </button>
            </div>
            <div class="members-section" style="margin-bottom:10px">
                <div id="empty-members-list-message"><?php esc_html_e( "To add new members, click on 'Create' or 'Select'.", 'disciple_tools' ) ?></div>
                <div class="member-list">

                </div>
            </div>
            <div class="reveal" id="add-new-group-member-modal" data-reveal style="min-height:500px">
                <h3><?php echo esc_html_x( "Add members from existing contacts", 'Add members modal', 'disciple_tools' )?></h3>
                <p><?php echo esc_html_x( "In the 'Member List' field, type the name of an existing contact to add them to this group.", 'Add members modal', 'disciple_tools' )?></p>

                <?php render_field_for_display( "members", $fields, $post, false ); ?>

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
                <button class="close-button" data-close aria-label="Close modal" type="button">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php }
    }

    public function p2p_init(){
        /**
         * Group members field
         */
        p2p_register_connection_type(
            [
                'name'           => 'contacts_to_groups',
                'from'           => 'contacts',
                'to'             => 'groups',
                'admin_box' => [
                    'show' => false,
                ],
                'title'          => [
                    'from' => __( 'Contacts', 'disciple_tools' ),
                    'to'   => __( 'Members', 'disciple_tools' ),
                ]
            ]
        );
        /**
         * Group leaders field
         */
        p2p_register_connection_type(
            [
                'name'           => 'groups_to_leaders',
                'from'           => 'groups',
                'to'             => 'contacts',
                'admin_box' => [
                    'show' => false,
                ],
                'title'          => [
                    'from' => __( 'Groups', 'disciple_tools' ),
                    'to'   => __( 'Leaders', 'disciple_tools' ),
                ]
            ]
        );
        /**
         * Group coaches field
         */
        p2p_register_connection_type(
            [
                'name'           => 'groups_to_coaches',
                'from'           => 'groups',
                'to'             => 'contacts',
                'admin_box' => [
                    'show' => false,
                ],
                'title'          => [
                    'from' => __( 'Groups', 'disciple_tools' ),
                    'to'   => __( 'Coaches', 'disciple_tools' ),
                ]
            ]
        );
        /**
         * Parent and child groups
         */
        p2p_register_connection_type(
            [
                'name'         => 'groups_to_groups',
                'from'         => 'groups',
                'to'           => 'groups',
                'title'        => [
                    'from' => __( 'Planted by', 'disciple_tools' ),
                    'to'   => __( 'Planting', 'disciple_tools' ),
                ],
            ]
        );
        /**
         * Peer groups
         */
        p2p_register_connection_type( [
            'name'         => 'groups_to_peers',
            'from'         => 'groups',
            'to'           => 'groups',
        ] );
        /**
         * Group People Groups field
         */
        p2p_register_connection_type(
            [
                'name'        => 'groups_to_peoplegroups',
                'from'        => 'groups',
                'to'          => 'peoplegroups',
                'title'       => [
                    'from' => __( 'People Groups', 'disciple_tools' ),
                    'to'   => __( 'Groups', 'disciple_tools' ),
                ]
            ]
        );
    }

    public function dt_details_additional_tiles( $tiles, $post_type = "" ){
        if ( $post_type === "groups" ){
            $tiles["relationships"] = [ "label" => __( "Member List", 'disciple_tools' ) ];
            $tiles["health-metrics"] = [ "label" => __( "Church Health", 'disciple_tools' ) ];
            $tiles["four-fields"] = [ "label" => __( "Four Fields", 'disciple_tools' ) ];
            $tiles["groups"] = [ "label" => __( "Groups", 'disciple_tools' ) ];
            $tiles["other"] = [ "label" => __( "Other", 'disciple_tools' ) ];
        }
        return $tiles;
    }


    //action when a post connection is added during create or update
    public function post_connection_added( $post_type, $post_id, $field_key, $value ){
        if ( $post_type === "groups" ){
            if ( $field_key === "members" ){
                // share the group with the owner of the contact when a member is added to a group
                $assigned_to = get_post_meta( $value, "assigned_to", true );
                if ( $assigned_to && strpos( $assigned_to, "-" ) !== false ){
                    $user_id = explode( "-", $assigned_to )[1];
                    if ( $user_id ){
                        DT_Posts::add_shared( $post_type, $post_id, $user_id, null, false, false );
                    }
                }
                self::update_group_member_count( $post_id );
            }
            if ( $field_key === "leaders" ){
                self::update_group_leader_count( $post_id );
            }
            if ( $field_key === "coaches" ){
                // share the group with the coach when a coach is added.
                $user_id = get_post_meta( $value, "corresponds_to_user", true );
                if ( $user_id ){
                    DT_Posts::add_shared( "groups", $post_id, $user_id, null, false, false, false );
                }
            }
        }
        if ( $post_type === "contacts" && $field_key === "groups" ){
            self::update_group_member_count( $value );
            // share the group with the owner of the contact.
            $assigned_to = get_post_meta( $post_id, "assigned_to", true );
            if ( $assigned_to && strpos( $assigned_to, "-" ) !== false ){
                $user_id = explode( "-", $assigned_to )[1];
                if ( $user_id ){
                    DT_Posts::add_shared( "groups", $value, $user_id, null, false, false );
                }
            }
        }
    }

    //action when a post connection is removed during create or update
    public function post_connection_removed( $post_type, $post_id, $field_key, $value ){
        if ( $post_type === "groups" ){
            if ( $field_key === "members" ){
                self::update_group_member_count( $post_id, "removed" );
            }
            if ( $field_key === "leaders" ){
                self::update_group_leader_count( $post_id, "removed" );
            }
        }
        if ( $post_type === "contacts" && $field_key === "groups" ){
            self::update_group_member_count( $value, "removed" );
        }
    }

    //filter at the start of post update
    public function dt_post_update_fields( $fields, $post_type, $post_id ){
        if ( $post_type === "groups" ){
            if ( isset( $fields["assigned_to"] ) ) {
                if ( filter_var( $fields["assigned_to"], FILTER_VALIDATE_EMAIL ) ){
                    $user = get_user_by( "email", $fields["assigned_to"] );
                    if ( $user ) {
                        $fields["assigned_to"] = $user->ID;
                    } else {
                        return new WP_Error( __FUNCTION__, "Unrecognized user", $fields["assigned_to"] );
                    }
                }
                //make sure the assigned to is in the right format (user-1)
                if ( is_numeric( $fields["assigned_to"] ) ||
                    strpos( $fields["assigned_to"], "user" ) === false ){
                    $fields["assigned_to"] = "user-" . $fields["assigned_to"];
                }
                $user_id = explode( '-', $fields["assigned_to"] )[1];
                if ( $user_id ){
                    DT_Posts::add_shared( "groups", $post_id, $user_id, null, false, true, false );
                }
            }
            $existing_group = DT_Posts::get_post( 'groups', $post_id, true, false );
            if ( isset( $fields["group_type"] ) && empty( $fields["church_start_date"] ) && empty( $existing_group["church_start_date"] ) && $fields["group_type"] === 'church' ){
                $fields["church_start_date"] = time();
            }
            if ( isset( $fields["group_status"] ) && empty( $fields["end_date"] ) && empty( $existing_group["end_date"] ) && $fields["group_status"] === 'inactive' ){
                $fields["end_date"] = time();
            }
        }
        return $fields;
    }

    //update the group member count when members and added or removed.
    private static function update_group_member_count( $group_id, $action = "added" ){
        $group = get_post( $group_id );
        $args = [
            'connected_type'   => "contacts_to_groups",
            'connected_direction' => 'to',
            'connected_items'  => $group,
            'nopaging'         => true,
            'suppress_filters' => false,
        ];
        $members = get_posts( $args );
        $member_count = get_post_meta( $group_id, 'member_count', true );
        if ( sizeof( $members ) > intval( $member_count ) ){
            update_post_meta( $group_id, 'member_count', sizeof( $members ) );
        } elseif ( $action === "removed" ){
            update_post_meta( $group_id, 'member_count', intval( $member_count ) - 1 );
        }
    }

    private static function update_group_leader_count( $group_id, $action = "added" ){
        $list = get_post( $group_id );
        $args = [
            'connected_type'   => "groups_to_leaders",
            'connected_direction' => 'from',
            'connected_items'  => $list,
            'nopaging'         => true,
            'suppress_filters' => false,
        ];
        $leaders = get_posts( $args );
        $leader_count = get_post_meta( $group_id, 'leader_count', true );
        if ( sizeof( $leaders ) > intval( $leader_count ) ){
            update_post_meta( $group_id, 'leader_count', sizeof( $leaders ) );
        } elseif ( $action === "removed" ){
            update_post_meta( $group_id, 'leader_count', intval( $leader_count - 1 ) );
        }
    }


    //check to see if the group is marked as needing an update
    //if yes: mark as updated
    private static function check_requires_update( $group_id ){
        if ( get_current_user_id() ){
            $requires_update = get_post_meta( $group_id, "requires_update", true );
            if ( $requires_update == "yes" || $requires_update == true || $requires_update == "1"){
                //don't remove update needed if the user is a dispatcher (and not assigned to the groups.)
                if ( DT_Posts::can_view_all( 'groups' ) ){
                    if ( dt_get_user_id_from_assigned_to( get_post_meta( $group_id, "assigned_to", true ) ) === get_current_user_id() ){
                        update_post_meta( $group_id, "requires_update", false );
                    }
                } else {
                    update_post_meta( $group_id, "requires_update", false );
                }
            }
        }
    }

    //filter when a comment is created
    public function dt_comment_created( $post_type, $post_id, $comment_id, $type ){
        if ( $post_type === "groups" ){
            if ( $type === "comment" ){
                self::check_requires_update( $post_id );
            }
        }
    }

    // filter at the start of post creation
    public function dt_post_create_fields( $fields, $post_type ){
        if ( $post_type === "groups" ) {
            if ( !isset( $fields["group_status"] ) ) {
                $fields["group_status"] = "active";
            }
            if ( !isset( $fields["group_type"] ) ) {
                $fields["group_type"] = "pre-group";
            }
            if ( !isset( $fields["assigned_to"] ) ) {
                $fields["assigned_to"] = sprintf( "user-%d", get_current_user_id() );
            }
            if ( !isset( $fields["start_date"] ) ) {
                $fields["start_date"] = time();
            }
            if ( isset( $fields["group_type"] ) && !isset( $fields["church_start_date"] ) && $fields["group_type"] === 'church' ){
                $fields["church_start_date"] = time();
            }
            if ( isset( $fields["assigned_to"] ) ) {
                if ( filter_var( $fields["assigned_to"], FILTER_VALIDATE_EMAIL ) ){
                    $user = get_user_by( "email", $fields["assigned_to"] );
                    if ( $user ) {
                        $fields["assigned_to"] = $user->ID;
                    } else {
                        return new WP_Error( __FUNCTION__, "Unrecognized user", $fields["assigned_to"] );
                    }
                }
                //make sure the assigned to is in the right format (user-1)
                if ( is_numeric( $fields["assigned_to"] ) ||
                    strpos( $fields["assigned_to"], "user" ) === false ){
                    $fields["assigned_to"] = "user-" . $fields["assigned_to"];
                }
            }
        }
        return $fields;
    }

    //action when a post has been created
    public function dt_post_created( $post_type, $post_id, $initial_fields ){
        if ( $post_type === "groups" ){
            do_action( "dt_group_created", $post_id, $initial_fields );
            $group = DT_Posts::get_post( 'groups', $post_id, true, false );
            if ( isset( $group["assigned_to"] )) {
                if ( $group["assigned_to"]["id"] ) {
                    DT_Posts::add_shared( "groups", $post_id, $group["assigned_to"]["id"], null, false, false, false );
                }
            }
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
            $counts = self::get_my_groups_status_type();
            $fields = DT_Posts::get_post_field_settings( $post_type );
            /**
             * Setup my group filters
             */
            $active_counts = [];
            $update_needed = 0;
            $status_counts = [];
            $total_my = 0;
            foreach ( $counts as $count ){
                $total_my += $count["count"];
                dt_increment( $status_counts[$count["group_status"]], $count["count"] );
                if ( $count["group_status"] === "active" ){
                    if ( isset( $count["update_needed"] ) ) {
                        $update_needed += (int) $count["update_needed"];
                    }
                    dt_increment( $active_counts[$count["group_type"]], $count["count"] );
                }
            }


            $filters["tabs"][] = [
                "key" => "assigned_to_me",
                "label" => _x( "Assigned to me", 'List Filters', 'disciple_tools' ),
                "count" => $total_my,
                "order" => 20
            ];
            // add assigned to me filters
            $filters["filters"][] = [
                'ID' => 'my_all',
                'tab' => 'assigned_to_me',
                'name' => _x( "All", 'List Filters', 'disciple_tools' ),
                'query' => [
                    'assigned_to' => [ 'me' ],
                    'sort' => 'group_status'
                ],
                "count" => $total_my,
            ];
            foreach ( $fields["group_status"]["default"] as $status_key => $status_value ) {
                if ( isset( $status_counts[$status_key] ) ){
                    $filters["filters"][] = [
                        "ID" => 'my_' . $status_key,
                        "tab" => 'assigned_to_me',
                        "name" => $status_value["label"],
                        "query" => [
                            'assigned_to' => [ 'me' ],
                            'group_status' => [ $status_key ],
                            'sort' => 'group_type'
                        ],
                        "count" => $status_counts[$status_key]
                    ];
                    if ( $status_key === "active" ){
                        if ( $update_needed > 0 ){
                            $filters["filters"][] = [
                                "ID" => 'my_update_needed',
                                "tab" => 'assigned_to_me',
                                "name" => $fields["requires_update"]["name"],
                                "query" => [
                                    'assigned_to' => [ 'me' ],
                                    'group_status' => [ 'active' ],
                                    'requires_update' => [ true ],
                                ],
                                "count" => $update_needed,
                                'subfilter' => true
                            ];
                        }
                        foreach ( $fields["group_type"]["default"] as $group_type_key => $group_type_value ) {
                            if ( isset( $active_counts[$group_type_key] ) ) {
                                $filters["filters"][] = [
                                    "ID" => 'my_' . $group_type_key,
                                    "tab" => 'assigned_to_me',
                                    "name" => $group_type_value["label"],
                                    "query" => [
                                        'assigned_to' => [ 'me' ],
                                        'group_status' => [ 'active' ],
                                        'group_type' => [ $group_type_key ],
                                        'sort' => 'name'
                                    ],
                                    "count" => $active_counts[$group_type_key],
                                    'subfilter' => true
                                ];
                            }
                        }
                    }
                }
            }

            $counts = self::get_all_groups_status_type();
            $active_counts = [];
            $update_needed = 0;
            $status_counts = [];
            $total_all = 0;
            foreach ( $counts as $count ){
                $total_all += $count["count"];
                dt_increment( $status_counts[$count["group_status"]], $count["count"] );
                if ( $count["group_status"] === "active" ){
                    if ( isset( $count["update_needed"] ) ) {
                        $update_needed += (int) $count["update_needed"];
                    }
                    dt_increment( $active_counts[$count["group_type"]], $count["count"] );
                }
            }
            $filters["tabs"][] = [
                "key" => "all",
                "label" => _x( "All", 'List Filters', 'disciple_tools' ),
                "count" => $total_all,
                "order" => 10
            ];
            // add assigned to me filters
            $filters["filters"][] = [
                'ID' => 'all',
                'tab' => 'all',
                'name' => _x( "All", 'List Filters', 'disciple_tools' ),
                'query' => [
                    'sort' => 'group_type'
                ],
                "count" => $total_all
            ];

            foreach ( $fields["group_status"]["default"] as $status_key => $status_value ) {
                if ( isset( $status_counts[$status_key] ) ){
                    $filters["filters"][] = [
                        "ID" => 'all_' . $status_key,
                        "tab" => 'all',
                        "name" => $status_value["label"],
                        "query" => [
                            'group_status' => [ $status_key ],
                            'sort' => 'group_type'
                        ],
                        "count" => $status_counts[$status_key]
                    ];
                    if ( $status_key === "active" ){
                        if ( $update_needed > 0 ){
                            $filters["filters"][] = [
                                "ID" => 'all_update_needed',
                                "tab" => 'all',
                                "name" => $fields["requires_update"]["name"],
                                "query" => [
                                    'group_status' => [ 'active' ],
                                    'requires_update' => [ true ],
                                ],
                                "count" => $update_needed,
                                'subfilter' => true
                            ];
                        }
                        foreach ( $fields["group_type"]["default"] as $group_type_key => $group_type_value ) {
                            if ( isset( $active_counts[$group_type_key] ) ) {
                                $filters["filters"][] = [
                                    "ID" => 'all_' . $group_type_key,
                                    "tab" => 'all',
                                    "name" => $group_type_value["label"],
                                    "query" => [
                                        'group_status' => [ 'active' ],
                                        'group_type' => [ $group_type_key ],
                                        'sort' => 'name'
                                    ],
                                    "count" => $active_counts[$group_type_key],
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
        if ( $post_type === "groups" ){
            if ( DT_Posts::can_view_all( $post_type ) ){
                $permissions = [];
            }
        }
        return $permissions;
    }

    public function scripts(){
        if ( is_singular( "groups" ) ){
            wp_enqueue_script( 'dt_groups', get_template_directory_uri() . '/dt-groups/groups.js', [
                'jquery',
                'details'
            ], filemtime( get_theme_file_path() . '/dt-groups/groups.js' ), true );
        }
    }


}
