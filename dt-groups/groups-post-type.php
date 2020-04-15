<?php
if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly.

/**
 * Groups Post Type Class
 * All functionality pertaining to post types in Disciple_Tools.
 *
 * @package    Disciple_Tools
 * @category   Plugin
 * @author     Chasm.Solutions & Kingdom.Training
 * @since      0.1.0
 */
class Disciple_Tools_Groups_Post_Type
{
    /**
     * The post type token.
     *
     * @access public
     * @since  0.1.0
     * @var    string
     */
    public $post_type;

    /**
     * The post type singular label.
     *
     * @access public
     * @since  0.1.0
     * @var    string
     */
    public $singular;

    /**
     * The post type plural label.
     *
     * @access public
     * @since  0.1.0
     * @var    string
     */
    public $plural;

    public $search_items;

    /**
     * The post type args.
     *
     * @access public
     * @since  0.1.0
     * @var    array
     */
    public $args;

    /**
     * The taxonomies for this post type.
     *
     * @access public
     * @since  0.1.0
     * @var    array
     */
    public $taxonomies;

    /**
     * Disciple_Tools_Groups_Post_Type The single instance of Disciple_Tools_Groups_Post_Type.
     *
     * @var    object
     * @access private
     * @since  0.1.0
     */
    private static $_instance = null;

    /**
     * Main Disciple_Tools_Groups_Post_Type Instance
     * Ensures only one instance of Disciple_Tools_Groups_Post_Type is loaded or can be loaded.
     *
     * @since  0.1.0
     * @static
     * @return Disciple_Tools_Groups_Post_Type instance
     */
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }

        return self::$_instance;
    } // End instance()

    /**
     * Disciple_Tools_Groups_Post_Type constructor.
     *
     * @param string $post_type
     * @param string $singular
     * @param string $plural
     * @param array  $args
     * @param array  $taxonomies
     */
    public function __construct( $post_type = 'groups', $singular = '', $plural = '', $args = [], $taxonomies = [ 'Cities' ] ) {
        $this->post_type = 'groups';
        $this->singular = _x( 'Group', 'singular of group', 'disciple_tools' );
        $this->plural = _x( 'Groups', 'plural of groups', 'disciple_tools' );
        $this->search_items = sprintf( _x( "Search %s", "Search 'something'", 'disciple_tools' ), $this->plural );
        $this->args = [ 'menu_icon' => 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIyNCIgaGVpZ2h0PSIyNCIgdmlld0JveD0iMCAwIDI0IDI0Ij48ZyBjbGFzcz0ibmMtaWNvbi13cmFwcGVyIiBmaWxsPSIjZmZmZmZmIj48cGF0aCBmaWxsPSIjZmZmZmZmIiBkPSJNMTIsNkwxMiw2Yy0xLjY1NywwLTMtMS4zNDMtMy0zdjBjMC0xLjY1NywxLjM0My0zLDMtM2gwYzEuNjU3LDAsMywxLjM0MywzLDN2MEMxNSw0LjY1NywxMy42NTcsNiwxMiw2eiI+PC9wYXRoPiA8cGF0aCBkYXRhLWNvbG9yPSJjb2xvci0yIiBmaWxsPSIjZmZmZmZmIiBkPSJNNCwxOXYtOGMwLTEuMTMsMC4zOTEtMi4xNjIsMS4wMjYtM0gyYy0xLjEwNSwwLTIsMC44OTUtMiwydjZoMnY1YzAsMC41NTIsMC40NDgsMSwxLDFoMiBjMC41NTIsMCwxLTAuNDQ4LDEtMXYtMkg0eiI+PC9wYXRoPiA8cGF0aCBmaWxsPSIjZmZmZmZmIiBkPSJNMTQsMjRoLTRjLTAuNTUyLDAtMS0wLjQ0OC0xLTF2LTZINnYtNmMwLTEuNjU3LDEuMzQzLTMsMy0zaDZjMS42NTcsMCwzLDEuMzQzLDMsM3Y2aC0zdjYgQzE1LDIzLjU1MiwxNC41NTIsMjQsMTQsMjR6Ij48L3BhdGg+IDxwYXRoIGRhdGEtY29sb3I9ImNvbG9yLTIiIGZpbGw9IiNmZmZmZmYiIGQ9Ik00LDdMNCw3QzIuODk1LDcsMiw2LjEwNSwyLDV2MGMwLTEuMTA1LDAuODk1LTIsMi0yaDBjMS4xMDUsMCwyLDAuODk1LDIsMnYwIEM2LDYuMTA1LDUuMTA1LDcsNCw3eiI+PC9wYXRoPiA8cGF0aCBkYXRhLWNvbG9yPSJjb2xvci0yIiBmaWxsPSIjZmZmZmZmIiBkPSJNMjAsMTl2LThjMC0xLjEzLTAuMzkxLTIuMTYyLTEuMDI2LTNIMjJjMS4xMDUsMCwyLDAuODk1LDIsMnY2aC0ydjVjMCwwLjU1Mi0wLjQ0OCwxLTEsMWgtMiBjLTAuNTUyLDAtMS0wLjQ0OC0xLTF2LTJIMjB6Ij48L3BhdGg+IDxwYXRoIGRhdGEtY29sb3I9ImNvbG9yLTIiIGZpbGw9IiNmZmZmZmYiIGQ9Ik0yMCw3TDIwLDdjMS4xMDUsMCwyLTAuODk1LDItMnYwYzAtMS4xMDUtMC44OTUtMi0yLTJoMGMtMS4xMDUsMC0yLDAuODk1LTIsMnYwIEMxOCw2LjEwNSwxOC44OTUsNywyMCw3eiI+PC9wYXRoPjwvZz48L3N2Zz4=' ];

        add_action( 'init', [ $this, 'register_post_type' ] );
        add_action( 'init', [ $this, 'groups_rewrites_init' ] );
        add_filter( 'post_type_link', [ $this, 'groups_permalink' ], 1, 3 );
        add_filter( 'dt_get_post_type_settings', [ $this, 'get_post_type_settings_hook' ], 10, 2 );

    } // End __construct()

    /**
     * Register the post type.
     *
     * @access public
     * @return void
     */
    public function register_post_type() {
        $labels = [
            'name'                  => $this->plural,
            'singular_name'         => $this->singular,
            'menu_name'             => $this->plural,
            'search_items'          => $this->search_items,
        ];
        $capabilities = [
            'create_posts'        => 'do_not_allow',
            'edit_post'           => 'access_groups',
            'read_post'           => 'access_groups',
            'delete_post'         => 'delete_any_groups',
            'delete_others_posts' => 'delete_any_groups',
            'delete_posts'        => 'delete_any_groups',
            'edit_posts'          => 'access_groups',
            'edit_others_posts'   => 'update_any_groups',
            'publish_posts'       => 'create_groups',
            'read_private_posts'  => 'view_any_groups',
        ];

        $rewrite = [
            'slug'       => 'groups',
            'with_front' => true,
            'pages'      => true,
            'feeds'      => false,
        ];

        $defaults = [
            'labels'                => $labels,
            'public'                => true,
            'publicly_queryable'    => true,
            'show_ui'               => true,
            'show_in_menu'          => true,
            'query_var'             => true,
            'rewrite'               => $rewrite,
            'capability_type'       => 'group',
            'capabilities'          => $capabilities,
            'has_archive'           => true,
            'hierarchical'          => false,
            'supports'              => [ 'title', 'comments', 'revisions' ],
            'menu_position'         => 5,
            'menu_icon'             => 'dashicons-smiley',
            'show_in_rest'          => true,
            'rest_base'             => 'groups',
            'rest_controller_class' => 'WP_REST_Posts_Controller',
        ];

        $args = wp_parse_args( $this->args, $defaults );

        register_post_type( $this->post_type, $args );
    } // End register_post_type()



    public function get_group_field_defaults( $post_id = null, $include_current_post = null ){
        global $post;

        $fields = [];

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
            'section'     => 'info',
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
            'section'     => 'info',
            "customizable" => "add_only"
        ];

        $fields['assigned_to'] = [
            'name'        => __( 'Assigned To', 'disciple_tools' ),
            'description' => __( "Select the main person who is responsible for reporting on this group.", 'disciple_tools' ),
            'type'        => 'user_select',
            'default'     => '',
            'section'     => 'info',
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
                    "description" => _x( "The group is sharing the gospel.", 'field description', 'disciple_tools' ),
                    "image" => get_template_directory_uri() . '/dt-assets/images/groups/evangelism.svg'
                ],
                "church_leaders" => [
                    "label" => __( "Leaders", 'disciple_tools' ),
                    "description" => _x( "The group has leaders.", 'field description', 'disciple_tools' ),
                    "image" => get_template_directory_uri() . '/dt-assets/images/groups/leadership.svg'
                ],
                "church_commitment" => [
                    "label" => __( "Church Commitment", 'disciple_tools' ),
                    "description" => _x( "The group has committed to be church.", 'field description', 'disciple_tools' ),
                    "image" => get_template_directory_uri() . '/dt-assets/images/groups/covenant.svg'
                ],
            ],
            "customizable" => "add_only"
        ];

        /* 4 fields */
        $fields["four_fields_unbelievers"] = [
            'name' => __( 'Unbelievers', 'disciple_tools' ),
            'description' => _x( 'Number of unbelievers in this group.', 'field description', 'disciple_tools' ),
            'type' => 'text',
            'default' => ''
        ];
        $fields["four_fields_believers"] = [
            'name' => __( 'Believers', 'disciple_tools' ),
            'description' => _x( 'Number of believers in this group.', 'field description', 'disciple_tools' ),
            'type' => 'text',
            'default' => ''
        ];
        $fields["four_fields_accountable"] = [
            'name' => __( 'Accountable', 'disciple_tools' ),
            'description' => _x( 'Number of people in accountability group.', 'field description', 'disciple_tools' ),
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

        $fields['start_date'] = [
            'name'        => __( 'Start Date', 'disciple_tools' ),
            'description' => _x( 'The date this group began meeting.', 'Optional Documentation', 'disciple_tools' ),
            'type'        => 'date',
            'default'     => time(),
            'section'     => 'info',
        ];
        $fields['church_start_date'] =[
            'name' => __( 'Church Start Date', 'disciple_tools' ),
            'description' => _x( 'The date this group first identified as being a church.', 'Optional Documentation', 'disciple_tools' ),
            'type' => 'date',
            'default'     => time()
        ];
        $fields['end_date'] = [
            'name'        => __( 'End Date', 'disciple_tools' ),
            'description' => _x( 'The date this group stopped meeting (if applicable).', 'Optional Documentation', 'disciple_tools' ),
            'type'        => 'date',
            'default'     => '',
            'section'     => 'info',
        ];
        $fields["duplicate_data"] = [
            "name" => 'Duplicates', //system string does not need translation
            'type' => 'array',
            'default' => [],
            'section' => 'admin'
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
            'section'     => 'misc',
            'hidden'      => true
        ];
        $fields["parent_groups"] = [
            "name" => __( 'Parent Group', 'disciple_tools' ),
            'description' => _x( 'A group that founded this group.', 'Optional Documentation', 'disciple_tools' ),
            "type" => "connection",
            "post_type" => "groups",
            "p2p_direction" => "from",
            "p2p_key" => "groups_to_groups"
        ];
        $fields["peer_groups"] = [
            "name" => __( 'Peer Group', 'disciple_tools' ),
            'description' => _x( "A related group that isn't a parent/child in relationship. It might indicate groups that collaborate, are about to merge, recently split, etc.", 'Optional Documentation', 'disciple_tools' ),
            "type" => "connection",
            "post_type" => "groups",
            "p2p_direction" => "any",
            "p2p_key" => "groups_to_peers"
        ];
        $fields["child_groups"] = [
            "name" => __( 'Child Group', 'disciple_tools' ),
            'description' => _x( 'A group that has been birthed out of this group.', 'Optional Documentation', 'disciple_tools' ),
            "type" => "connection",
            "post_type" => "groups",
            "p2p_direction" => "to",
            "p2p_key" => "groups_to_groups"
        ];
        $fields["member_count"] = [
            'name' => __( 'Member Count', 'disciple_tools' ),
            'description' => _x( 'The number of members in this group. It will automatically be updated when new members are added or removed in the member list. Change this number manually to included people who may not be in the system but are also members of the group.', 'Optional Documentation', 'disciple_tools' ),
            'type' => 'text',
            'default' => ''
        ];
        $fields["members"] = [
            "name" => __( 'Member List', 'disciple_tools' ),
            'description' => _x( 'The contacts who are members of this group.', 'Optional Documentation', 'disciple_tools' ),
            "type" => "connection",
            "post_type" => "contacts",
            "p2p_direction" => "to",
            "p2p_key" => "contacts_to_groups"
        ];
        $fields["people_groups"] = [
            "name" => __( 'People Groups', 'disciple_tools' ),
            'description' => _x( 'The people groups represented by this group.', 'Optional Documentation', 'disciple_tools' ),
            "type" => "connection",
            "post_type" => "peoplegroups",
            "p2p_direction" => "from",
            "p2p_key" => "groups_to_peoplegroups"
        ];
        $fields["leaders"] = [
            "name" => __( 'Leaders', 'disciple_tools' ),
            'description' => '',
            "type" => "connection",
            "post_type" => "contacts",
            "p2p_direction" => "from",
            "p2p_key" => "groups_to_leaders"
        ];
        $fields["coaches"] = [
            "name" => __( 'Group Coach / Church Planter', 'disciple_tools' ),
            'description' => _x( 'The person who planted and/or is coaching this group. Only one person can be assigned to a group while multiple people can be coaches / church planters of this group.', 'Optional Documentation', 'disciple_tools' ),
            "type" => "connection",
            "post_type" => "contacts",
            "p2p_direction" => "from",
            "p2p_key" => "groups_to_coaches"
        ];
        $fields["requires_update"] = [
            'name'        => __( 'Requires Update', 'disciple_tools' ),
            'description' => '',
            'type'        => 'boolean',
            'default'     => false,
        ];
        $fields['location_grid'] = [
            'name'        => __( 'Locations', 'disciple_tools' ),
            'description' => _x( 'The general location where this group meets.', 'Optional Documentation', 'disciple_tools' ),
            'type'        => 'location',
            'default'     => [],
        ];
        $fields['location_grid_meta'] = [
            'name'        => 'Location Grid Meta', //system string does not need translation
            'type'        => 'location_meta',
            'default'     => [],
            'hidden' => true
        ];
        $fields['tasks'] = [
            'name' => __( 'Tasks', 'disciple_tools' ),
            'description' => __( 'Tasks related to this group.', 'disciple_tools' ),
            'type' => 'post_user_meta',
        ];
        $fields["last_modified"] =[
            'name' => __( 'Last modified', 'disciple_tools' ),
            'type' => 'number',
            'default' => 0,
            'section' => 'admin'
        ];


        $id = isset( $post->ID ) ? $post->ID : $post_id;
        if ( $include_current_post &&
             ( $id ||
               ( isset( $post->ID ) && $post->post_status != 'auto-draft' ) ) ) { // if being called for a specific record or new record.
            // Address
            $addresses = dt_address_metabox()->address_fields( $id );
            foreach ( $addresses as $k => $v ) { // sets all others third
                $fields[ $k ] = [
                    'name'        => ucwords( $v['name'] ),
                    'description' => '',
                    'type'        => 'text',
                    'default'     => '',
                    'section'     => 'address',
                ];
            }
        }

        return $fields;
    }

    /**
     * Get the settings for the custom fields.
     *
     * @param bool $include_current_post
     * @param int|null $post_id
     * @param bool $with_deleted_options
     * @param bool $load_from_cache
     *
     * @return mixed
     */
    public function get_custom_fields_settings( $include_current_post = true, int $post_id = null, $with_deleted_options = false, $load_from_cache = true ) {
        $cached = wp_cache_get( "group_fields_settings" );
        if ( $load_from_cache && $cached ){
            return $cached;
        }

        $fields = $this->get_group_field_defaults( $post_id, $include_current_post );
        $fields = apply_filters( 'dt_custom_fields_settings', $fields, "groups" );
        foreach ( $fields as $field_key => $field ){
            if ( $field["type"] === "key_select" || $field["type"] === "multi_select" ){
                foreach ( $field["default"] as $option_key => $option_value ){
                    if ( !is_array( $option_value )){
                        $fields[$field_key]["default"][$option_key] = [ "label" => $option_value ];
                    }
                }
            }
        }
        $custom_field_options = dt_get_option( "dt_field_customizations" );
        if ( isset( $custom_field_options["groups"] )){
            foreach ( $custom_field_options["groups"] as $key => $field ){
                $field_type = $field["type"] ?? $fields[$key]["type"] ?? "";
                if ( $field_type ){
                    if ( !isset( $fields[$key] )){
                        $fields[$key] = $field;
                    } else {
                        if ( isset( $field["name"] )){
                            $fields[$key]["name"] = $field["name"];
                        }
                        if ( isset( $field["tile"] ) ) {
                            $fields[ $key ]["tile"] = $field["tile"];
                        }
                        if ( $field_type === "key_select" || $field_type === "multi_select" ){
                            if ( isset( $field["default"] )){
                                $fields[$key]["default"] = array_replace_recursive( $fields[$key]["default"], $field["default"] );
                            }
                        }
                    }
                    if ( $field_type === "key_select" || $field_type === "multi_select" ){
                        if ( isset( $field["order"] )){
                            $with_order = [];
                            foreach ( $field["order"] as $ordered_key ){
                                $with_order[$ordered_key] = [];
                            }
                            foreach ( $fields[$key]["default"] as $option_key => $option_value ){
                                $with_order[$option_key] = $option_value;
                            }
                            $fields[$key]["default"] = $with_order;
                        }
                    }
                }
            }
        }
        if ( $with_deleted_options === false ){
            foreach ( $fields as $field_key => $field ){
                if ( $field["type"] === "key_select" || $field["type"] === "multi_select" ){
                    foreach ( $field["default"] as $option_key => $option_value ){
                        if ( isset( $option_value["deleted"] ) && $option_value["deleted"] == true ){
                            unset( $fields[$field_key]["default"][$option_key] );
                        }
                    }
                }
            }
        }
        wp_cache_set( "group_fields_settings", $fields );
        return $fields;
    } // End get_custom_fields_settings()


    /**
     * Run on activation.
     *
     * @access public
     * @since  0.1.0
     */
    public function activation() {
        $this->flush_rewrite_rules();
    } // End activation()

    /**
     * Flush the rewrite rules
     *
     * @access public
     * @since  0.1.0
     */
    private function flush_rewrite_rules() {
        $this->register_post_type();
        flush_rewrite_rules();
    } // End flush_rewrite_rules()

    /**
     * @param $post_link
     * @param $post
     *
     * @return string
     */
    public function groups_permalink( $post_link, $post ) {
        if ( $post->post_type === "groups" ) {
            return home_url( "groups/" . $post->ID . '/' );
        } else {
            return $post_link;
        }
    }

    public function groups_rewrites_init() {
        add_rewrite_rule( 'groups/([0-9]+)?$', 'index.php?post_type=groups&p=$matches[1]', 'top' );
    }

    public function get_channels_list(){
        return [
            "address" => [
                "label" => __( "Address", 'disciple_tools' ),
                'description' => __( "The address where this group meets. (e.g., 124 Market St or “Jon’s Famous Coffee Shop).", 'disciple_tools' ),
                "types" => []
            ]
        ];
    }

    public function get_post_type_settings_hook( $settings, $post_type ){
        if ( $post_type === "groups" ){
            $fields = $this->get_custom_fields_settings();
            $settings = [
                'fields' => $fields,
                'address_types' => $this->get_channels_list()["address"]["types"],
                'channels' => $this->get_channels_list(),
                'connection_types' => array_keys( array_filter( $fields, function ( $a ) {
                    return $a["type"] === "connection";
                } ) ),
                'label_singular' => $this->singular,
                'label_plural' => $this->plural,
                'post_type' => 'groups'
            ];
        }
        return $settings;
    }


} // End Class
