<?php
if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly.

/**
 * Disciple_Tools Plugin Contacts Post Type Class
 * All functionality pertaining to contacts post types in Disciple_Tools.
 *
 * @package  Disciple_Tools
 * @category Plugin
 * @author   Chasm.Solutions & Kingdom.Training
 * @since    0.1.0
 */

/**
 * Class Disciple_Tools_Contact_Post_Type
 */
class Disciple_Tools_Contact_Post_Type
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
     * Disciple_Tools_Admin_Menus The single instance of Disciple_Tools_Admin_Menus.
     *
     * @var    object
     * @access private
     * @since  0.1.0
     */
    private static $_instance = null;

    /**
     * Main Disciple_Tools_Contact_Post_Type Instance
     * Ensures only one instance of Disciple_Tools_Contact_Post_Type is loaded or can be loaded.
     *
     * @since  0.1.0
     * @static
     * @return Disciple_Tools_Contact_Post_Type instance
     */
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }

        return self::$_instance;
    } // End instance()

    /**
     * Disciple_Tools_Contact_Post_Type constructor.
     *
     * @param string $post_type
     * @param string $singular
     * @param string $plural
     * @param array  $args
     * @param array  $taxonomies
     */
    public function __construct( $post_type = 'contacts', $singular = '', $plural = '', $args = [], $taxonomies = [] ) {
        $this->post_type = 'contacts';
        $this->singular = _x( 'Contact', 'singular of contact', 'disciple_tools' );
        $this->plural = _x( 'Contacts', 'plural of contact', 'disciple_tools' );
        $this->args = [ 'menu_icon' => 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIyNCIgaGVpZ2h0PSIyNCIgdmlld0JveD0iMCAwIDI0IDI0Ij48ZyBjbGFzcz0ibmMtaWNvbi13cmFwcGVyIiBmaWxsPSIjZmZmZmZmIj48cGF0aCBmaWxsPSIjZmZmZmZmIiBkPSJNOSwxMmMyLjc1NywwLDUtMi4yNDMsNS01VjVjMC0yLjc1Ny0yLjI0My01LTUtNVM0LDIuMjQzLDQsNXYyQzQsOS43NTcsNi4yNDMsMTIsOSwxMnoiPjwvcGF0aD4gPHBhdGggZmlsbD0iI2ZmZmZmZiIgZD0iTTE1LjQyMywxNS4xNDVDMTQuMDQyLDE0LjYyMiwxMS44MDYsMTQsOSwxNHMtNS4wNDIsMC42MjItNi40MjQsMS4xNDZDMS4wMzUsMTUuNzI5LDAsMTcuMjMzLDAsMTguODg2VjI0IGgxOHYtNS4xMTRDMTgsMTcuMjMzLDE2Ljk2NSwxNS43MjksMTUuNDIzLDE1LjE0NXoiPjwvcGF0aD4gPHJlY3QgZGF0YS1jb2xvcj0iY29sb3ItMiIgeD0iMTYiIHk9IjMiIGZpbGw9IiNmZmZmZmYiIHdpZHRoPSI4IiBoZWlnaHQ9IjIiPjwvcmVjdD4gPHJlY3QgZGF0YS1jb2xvcj0iY29sb3ItMiIgeD0iMTYiIHk9IjgiIGZpbGw9IiNmZmZmZmYiIHdpZHRoPSI4IiBoZWlnaHQ9IjIiPjwvcmVjdD4gPHJlY3QgZGF0YS1jb2xvcj0iY29sb3ItMiIgeD0iMTkiIHk9IjEzIiBmaWxsPSIjZmZmZmZmIiB3aWR0aD0iNSIgaGVpZ2h0PSIyIj48L3JlY3Q+PC9nPjwvc3ZnPg==' ];
        $this->taxonomies = $taxonomies = [];

        add_action( 'init', [ $this, 'register_post_type' ] );
        add_action( 'init', [ $this, 'contacts_rewrites_init' ] );
        add_filter( 'post_type_link', [ $this, 'contacts_permalink' ], 1, 3 );
        add_filter( 'dt_get_post_type_settings', [ $this, 'get_post_type_settings_hook' ], 10, 2 );

        if ( is_admin() ) {
            add_filter( 'enter_title_here', [ $this, 'enter_title_here' ] );
        }
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
            'search_items'          => sprintf( __( 'Search %s', 'disciple_tools' ), $this->plural ),
        ];
        $rewrite = [
            'slug'       => 'contacts',
            'with_front' => true,
            'pages'      => true,
            'feeds'      => false,
        ];
        $capabilities = [
            'create_posts'        => 'do_not_allow',
            'edit_post'           => 'access_contacts',
            'read_post'           => 'access_contacts',
            'delete_post'         => 'delete_any_contacts',
            'delete_others_posts' => 'delete_any_contacts',
            'delete_posts'        => 'delete_any_contacts',
            'edit_posts'          => 'access_contacts',
            'edit_others_posts'   => 'update_any_contacts',
            'publish_posts'       => 'create_contacts',
            'read_private_posts'  => 'view_any_contacts',
        ];
        $defaults = [
            'label'                 => __( 'Contact', 'disciple_tools' ),
            'description'           => __( 'Contacts generated by the media to movement effort', 'disciple_tools' ),
            'labels'                => $labels,
            'public'                => true,
            'publicly_queryable'    => true,
            'show_ui'               => true,
            'show_in_menu'          => true,
            'query_var'             => true,
            'rewrite'               => $rewrite,
            'capabilities'          => $capabilities,
            'capability_type'       => 'contact',
            'has_archive'           => true, //$archive_slug,
            'hierarchical'          => false,
            'supports'              => [ 'title', 'comments' ],
            'menu_position'         => 5,
            'menu_icon'             => 'dashicons-groups',
            'show_in_admin_bar'     => true,
            'show_in_nav_menus'     => true,
            'can_export'            => true,
            'exclude_from_search'   => true,
            'show_in_rest'          => false
        ];

        $args = wp_parse_args( $this->args, $defaults );

        register_post_type( $this->post_type, $args );
    } // End register_post_type()


    public function get_contact_field_defaults( $post_id = null, $include_current_post = null ){
        $fields = [];

        $fields['assigned_to'] = [
            'name'        => __( 'Assigned To', 'disciple_tools' ),
            'description' => '',
            'type'        => 'user_select',
            'default'     => '',
            'section'     => 'status',
        ];
        // Status Section
        $fields['overall_status'] = [
            'name'        => __( 'Overall Status', 'disciple_tools' ),
            'description' => '',
            'type'        => 'key_select',
            'default'     => [
                'new'   => [
                    "label" => _x( 'New Contact', 'Contact Status', 'disciple_tools' ),
                    "color" => "#F43636",
                    "description" => _x( "The contact is new in the system", "Contact Status", 'disciple_tools' )
                ],
                'unassignable' => [
                    "color" => "#FF9800",
                    "label" => _x( 'Not Ready', 'Contact Status', 'disciple_tools' ),
                    "description" => _x( "There is not enough information to move forward with the contact at this time.", "Contact Status", 'disciple_tools' )
                ],
                'unassigned'   => [
                    "label" => _x( 'Dispatch Needed', 'Contact Status', 'disciple_tools' ),
                    "color" => "#F43636",
                    "description" => _x( "This contact needs to be assigned to a multiplier.", "Contact Status", 'disciple_tools' )
                ],
                'assigned'     => [
                    "color" => "#FF9800",
                    "label" => _x( "Waiting to be accepted", 'Contact Status', 'disciple_tools' ),
                    "description" => _x( "The contact has been assigned to someone, but has not yet been accepted by that person.", "Contact Status", 'disciple_tools' )
                ],
                'active'       => [
                    "color" => "#4CAF50",
                    "label" => _x( 'Active', 'Contact Status', 'disciple_tools' ),
                    "description" => _x( "The contact is progressing and/or continually being updated.", "Contact Status", 'disciple_tools' )
                ],
                'paused'       => [
                    "color" => "#FF9800",
                    "label" => _x( 'Paused', 'Contact Status', 'disciple_tools' ),
                    "description" => _x( "This contact is currently on hold (i.e. on vacation or not responding).", "Contact Status", 'disciple_tools' )
                ],
                'closed'       => [
                    "color" => "#F43636",
                    "label" => _x( 'Closed', 'Contact Status', 'disciple_tools' ),
                    "description" => _x( "This contact has made it known that they no longer want to continue or you have decided not to continue with him/her.", "Contact Status", 'disciple_tools' )
                ],
            ],
            'section'     => 'status',
            'customizable' => 'add_only'
        ];

        //these fields must stay in order of importance
        $fields['seeker_path'] = [
            'name'        => __( 'Seeker Path', 'disciple_tools' ),
            'description' => '',
            'type'        => 'key_select',
            'default'     => [
                'none'        => [ "label" => __( 'Contact Attempt Needed', 'disciple_tools' ) ],
                'attempted'   => [ "label" => __( 'Contact Attempted', 'disciple_tools' ) ],
                'established' => [ "label" => __( 'Contact Established', 'disciple_tools' ) ],
                'scheduled'   => [ "label" => __( 'First Meeting Scheduled', 'disciple_tools' ) ],
                'met'         => [ "label" => __( 'First Meeting Complete', 'disciple_tools' ) ],
                'ongoing'     => [ "label" => __( 'Ongoing Meetings', 'disciple_tools' ) ],
                'coaching'    => [ "label" => __( 'Being Coached', 'disciple_tools' ) ],
            ],
            'section'     => 'status',
            'customizable' => 'add_only'
        ];
        $fields['requires_update'] = [
            'name'        => __( 'Requires Update', 'disciple_tools' ),
            'description' => '',
            'type'        => 'boolean',
            'default'     => false,
            'section'     => 'status',
        ];

        $id = $post->ID ?? $post_id;
        if ( $include_current_post && ( $id || ( isset( $post->ID ) && $post->post_status != 'auto-draft' ) ) ) { // if being called for a specific record or new record.
            // Contact Channels Section
            $methods   = $this->contact_fields( $id );
            foreach ( $methods as $k => $v ) {
                $fields[ $k ] = [
                    'name'        => ucwords( $v['name'] ),
                    'description' => '',
                    'type'        => 'text',
                    'default'     => '',
                    'section'     => 'info',
                ];
            }
        }


        $fields["milestones"] = [
            "name"    => __( 'Faith Milestones', 'disciple_tools' ),
            "type"    => "multi_select",
            "default" => [
                "milestone_has_bible"     => [ "label" => __( 'Has Bible', 'disciple_tools' ) ],
                "milestone_reading_bible" => [ "label" => __( 'Reading Bible', 'disciple_tools' ) ],
                "milestone_belief"        => [ "label" => __( 'States Belief', 'disciple_tools' ) ],
                "milestone_can_share"     => [ "label" => __( 'Can Share Gospel/Testimony', 'disciple_tools' ) ],
                "milestone_sharing"       => [ "label" => __( 'Sharing Gospel/Testimony', 'disciple_tools' ) ],
                "milestone_baptized"      => [ "label" => __( 'Baptized', 'disciple_tools' ) ],
                "milestone_baptizing"     => [ "label" => __( 'Baptizing', 'disciple_tools' ) ],
                "milestone_in_group"      => [ "label" => __( 'In Church/Group', 'disciple_tools' ) ],
                "milestone_planting"      => [ "label" => __( 'Starting Churches', 'disciple_tools' ) ],
            ],
            "customizable" => "add_only"
        ];


        $fields['baptism_date'] = [
            'name'        => __( 'Baptism Date', 'disciple_tools' ),
            'description' => '',
            'type'        => 'date',
            'default'     => '',
            'section'     => 'misc',
        ];

        $fields['baptism_generation'] = [
            'name'        => __( 'Baptism Generation', 'disciple_tools' ),
            'description' => '',
            'type'        => 'text',
            'default'     => '',
            'section'     => 'misc',
        ];

        // Misc Information fields

        $fields['gender'] = [
            'name'        => __( 'Gender', 'disciple_tools' ),
            'description' => '',
            'type'        => 'key_select',
            'default'     => [
                'not-set' => [ "label" => '' ],
                'male'    => [ "label" => __( 'Male', 'disciple_tools' ) ],
                'female'  => [ "label" => __( 'Female', 'disciple_tools' ) ],
            ],
            'section'     => 'misc',
        ];
        $fields['age'] = [
            'name'        => __( 'Age', 'disciple_tools' ),
            'description' => '',
            'type'        => 'key_select',
            'default'     => [
                'not-set' => [ "label" => '' ],
                '<19'     => [ "label" => __( 'Under 18 years old', 'disciple_tools' ) ],
                '<26'     => [ "label" => __( '18-25 years old', 'disciple_tools' ) ],
                '<41'     => [ "label" => __( '26-40 years old', 'disciple_tools' ) ],
                '>41'     => [ "label" => __( 'Over 40 years old', 'disciple_tools' ) ],
            ],
            'section'     => 'misc',
        ];

        $fields["reason_unassignable"] = [
            'name'        => __( 'Reason Not Ready', "disciple_tools" ),
            'description' => '',
            'type'        => 'key_select',
            'default'     => [
                'none'         => [ "label" => '', ],
                'insufficient' => [ "label" => __( 'Insufficient Contact Information', "disciple_tools" ) ],
                'location'     => [ "label" => __( 'Unknown Location', "disciple_tools" ) ],
                'media'        => [ "label" => __( 'Only wants media', "disciple_tools" ) ],
                'outside_area' => [ "label" => __( 'Outside Area', "disciple_tools" ) ],
                'needs_review' => [ "label" => __( 'Needs Review', "disciple_tools" ) ],
                'awaiting_confirmation' => [ "label" => __( 'Waiting for Confirmation', "disciple_tools" ) ],
            ],
            'section'     => 'misc',
            'customizable' => 'all'
        ];

        $fields['reason_paused'] = [
            'name'        => __( 'Reason Paused' ),
            'description' => '',
            'type'        => 'key_select',
            'default' => [
                'none'                 => [ "label" => '' ],
                'vacation'             => [ "label" => __( 'On Vacation', 'disciple_tools' ) ],
                'not_responding'       => [ "label" => __( 'Not Responding', 'disciple_tools' ) ],
                'not_available'        => [ "label" => __( 'Not available', 'disciple_tools' ) ],
                'little_interest'      => [ "label" => __( 'Little interest/hunger', 'disciple_tools' ) ],
                'no_initiative'        => [ "label" => __( 'No initiative', 'disciple_tools' ) ],
                'questionable_motives' => [ "label" => __( 'Questionable motives', 'disciple_tools' ) ],
                'ball_in_their_court'  => [ "label" => __( 'Ball is in his/her court', 'disciple_tools' ) ],
                'wait_and_see'         => [ "label" => __( 'Want to see if/how they respond to automated text messages', 'disciple_tools' ) ],
            ],
            'section'     => 'misc',
            'customizable' => 'all'
        ];

        $fields['reason_closed'] = [
            'name'        => __( 'Reason Closed', 'disciple_tools' ),
            'description' => '',
            'type'        => 'key_select',
            'default'     => [
                'none'                 => [ "label" => '' ],
                'duplicate'            => [ "label" => __( 'Duplicate', 'disciple_tools' ) ],
                'insufficient'         => [ "label" => __( 'Insufficient contact info', 'disciple_tools' ) ],
                'denies_submission'    => [ "label" => __( 'Denies submitting contact request', 'disciple_tools' ) ],
                'hostile_self_gain'    => [ "label" => __( 'Hostile, playing game or self gain', 'disciple_tools' ) ],
                'games'                => [ "label" => __( 'Playing games', 'disciple_tools' ) ],
                'apologetics'          => [ "label" => __( 'Only wants to argue or debate', 'disciple_tools' ) ],
                'media_only'           => [ "label" => __( 'Just wanted media or book', 'disciple_tools' ) ],
                'no_longer_interested' => [ "label" => __( 'No longer interested', 'disciple_tools' ) ],
                'no_longer_responding' => [ "label" => __( 'No longer responding', 'disciple_tools' ) ],
                'already_connected'    => [ "label" => __( 'Already in church or connected with others', 'disciple_tools' ) ],
                'transfer'             => [ "label" => __( 'Transferred contact to partner', 'disciple_tools' ) ],
                'martyred'             => [ "label" => __( 'Martyred', 'disciple_tools' ) ],
                'moved'                => [ "label" => __( 'Moved or relocated', 'disciple_tools' ) ],
                'unknown'              => [ "label" => __( 'Unknown', 'disciple_tools' ) ]
            ],
            'section'     => 'misc',
            'customizable' => 'all'
        ];
        $fields['accepted'] = [
            'name'        => __( 'Accepted', 'disciple_tools' ),
            'description' => '',
            'type'        => 'boolean',
            'default'     => false,
            'section'     => 'status',
            'hidden'      => true
        ];

        $sources_default = [];
        foreach ( dt_get_option( 'dt_site_custom_lists' )['sources'] as $key => $value ) {
            $sources_default[ $key ] = $value;
        }

        $fields['sources'] = [
            'name'        => __( 'Sources', 'disciple_tools' ),
            'description' => '',
            'type'        => 'multi_select',
            'default'     => [],
            'section'     => 'misc',
        ];

        $fields["source_details"] = [
            "name" => __( "Source Details", 'disciple_tools' ),
            'description' => '',
            'type' => 'text',
            'default' => '',
            'section'     => 'misc',
        ];

        // contact buttons
        $fields['quick_button_no_answer'] = [
            'name'        => __( 'No Answer', 'disciple_tools' ),
            'description' => '',
            'type'        => 'number',
            'default'     => 0,
            'section'     => 'quick_buttons',
            'icon'        => "no-answer.svg",
        ];
        $fields['quick_button_contact_established'] = [
            'name'        => __( 'Contact Established', 'disciple_tools' ),
            'description' => '',
            'type'        => 'number',
            'default'     => 0,
            'section'     => 'quick_buttons',
            'icon'        => "successful-conversation.svg",
        ];
        $fields['quick_button_meeting_scheduled'] = [
            'name'        => __( 'Meeting Scheduled', 'disciple_tools' ),
            'description' => '',
            'type'        => 'number',
            'default'     => 0,
            'section'     => 'quick_buttons',
            'icon'        => "meeting-scheduled.svg",
        ];
        $fields['quick_button_meeting_complete'] = [
            'name'        => __( 'Meeting Complete', 'disciple_tools' ),
            'description' => '',
            'type'        => 'number',
            'default'     => 0,
            'section'     => 'quick_buttons',
            'icon'        => "meeting-complete.svg",
        ];
        $fields['quick_button_no_show'] = [
            'name'        => __( 'Meeting No-show', 'disciple_tools' ),
            'description' => '',
            'type'        => 'number',
            'default'     => 0,
            'section'     => 'quick_buttons',
            'icon'        => "no-show.svg",
        ];

        $fields['corresponds_to_user'] = [
            'name' => __( 'Corresponds to user', 'disciple_tools' ),
            'description' => 'The id of the user this contact corresponds to',
            'type' => 'number',
            'default' => 0,
            'section' => 'misc'
        ];
        $fields["type"] = [
            'name'        => __( 'Contact type', 'disciple_tools' ),
            'description' => '',
            'type'        => 'key_select',
            'default'     => [
                'media'    => [ "label" => __( 'Media', 'disciple_tools' ) ],
                'next_gen' => [ "label" => __( 'Next Generation', 'disciple_tools' ) ],
                'user'     => [ "label" => __( 'User', 'disciple_tools' ) ],
                'partner'  => [ "label" => __( 'Partner', 'disciple_tools' ) ],
            ],
            'section'     => 'misc',
            'hidden'      => true
        ];
        $fields["last_modified"] =[
            'name' => __( 'Last modified', 'disciple_tools' ),
            'description' => 'The time of the last modification',
            'type' => 'number',
            'default' => 0,
            'section' => 'admin'
        ];
        $fields["duplicate_data"] = [
            "name" => __( 'Duplicates', 'disciple_tools' ),
            'description' => 'Possible contact duplicates',
            'type' => 'array',
            'default' => [],
            'section' => 'admin'
        ];
        $fields['tags'] = [
            'name'        => __( 'Tags', 'disciple_tools' ),
            'description' => '',
            'type'        => 'multi_select',
            'default'     => [],
            'section'     => 'misc',
        ];
        $fields["follow"] = [
            'name'        => __( 'Follow', 'disciple_tools' ),
            'description' => 'Users following this contact',
            'type'        => 'multi_select',
            'default'     => [],
            'section'     => 'misc',
            'hidden'      => true
        ];
        $fields["unfollow"] = [
            'name'        => __( 'Un-Follow', 'disciple_tools' ),
            'description' => 'Users not following this contact',
            'type'        => 'multi_select',
            'default'     => [],
            'section'     => 'misc',
            'hidden'      => true
        ];
        $fields["tags"] = [
            "name" => __( "Tags", "disciple_tools" ),
            "type" => "multi_select",
            "default" => []
        ];
        $fields["duplicate_of"] = [
            "name" => __( "Duplicate of", "disciple_tools" ),
            "type" => "text",
            "default" => '',
            "hidden" => true
        ];
        $fields["relation"] = [
            "name" => __( "Relation", "disciple_tools" ),
            "type" => "connection",
            "p2p_direction" => "any",
            "p2p_key" => "contacts_to_relation"
        ];
        $fields["coached_by"] = [
            "name" => __( "Coached by", "disciple_tools" ),
            "type" => "connection",
            "p2p_direction" => "from",
            "p2p_key" => "contacts_to_contacts"
        ];
        $fields["coaching"] = [
            "name" => __( "Coached", "disciple_tools" ),
            "type" => "connection",
            "p2p_direction" => "to",
            "p2p_key" => "contacts_to_contacts"
        ];
        $fields["baptized"] = [
            "name" => __( "Baptized", "disciple_tools" ),
            "type" => "connection",
            "p2p_direction" => "to",
            "p2p_key" => "baptizer_to_baptized"
        ];
        $fields["baptized_by"] = [
            "name" => __( "Baptized by", "disciple_tools" ),
            "type" => "connection",
            "p2p_direction" => "from",
            "p2p_key" => "baptizer_to_baptized"
        ];
        $fields["people_groups"] = [
            "name" => __( "People Groups", "disciple_tools" ),
            "type" => "connection",
            "p2p_direction" => "from",
            "p2p_key" => "contacts_to_peoplegroups"
        ];
        $fields["groups"] = [
            "name" => __( "Groups", "disciple_tools" ),
            "type" => "connection",
            "p2p_direction" => "from",
            "p2p_key" => "contacts_to_groups"
        ];
        $fields["subassigned"] = [
            "name" => __( "Sub-assigned to", "disciple_tools" ),
            "type" => "connection",
            "p2p_direction" => "to",
            "p2p_key" => "contacts_to_subassigned"
        ];
        $fields['location_grid'] = [
            'name'        => __( 'Locations', 'disciple_tools' ),
            'type'        => 'location',
            'default'     => [],
        ];
        $fields['location_lnglat'] = [
            'name'        => __( 'Coordinates', 'disciple_tools' ),
            'type'        => 'location',
            'default'     => [],
        ];
        $fields['reminders'] = [
            'name' => __( 'Reminders', 'disciple_tools' ),
            'type' => 'post_user_meta',
        ];

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

        $cached = wp_cache_get( "contact_field_settings" );
        if ( $load_from_cache && $cached ){
            return $cached;
        }
        $fields = $this->get_contact_field_defaults( $post_id, $include_current_post );
        $fields = apply_filters( 'dt_custom_fields_settings', $fields, "contacts" );
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
        if ( isset( $custom_field_options["contacts"] )){
            foreach ( $custom_field_options["contacts"] as $key => $field ){
                $field_type = $field["type"] ?? $fields[$key]["type"] ?? "";
                if ( $field_type ) {
                    if ( !isset( $fields[ $key ] ) ) {
                        $fields[ $key ] = $field;
                    } else {
                        if ( isset( $field["name"] ) ) {
                            $fields[ $key ]["name"] = $field["name"];
                        }
                        if ( isset( $field["tile"] ) ) {
                            $fields[ $key ]["tile"] = $field["tile"];
                        }
                        if ( $field_type === "key_select" || $field_type === "multi_select" ) {
                            if ( isset( $field["default"] ) ) {
                                $fields[ $key ]["default"] = array_replace_recursive( $fields[ $key ]["default"], $field["default"] );
                            }
                        }
                    }
                    if ( $field_type === "key_select" || $field_type === "multi_select" ) {
                        if ( isset( $field["order"] ) ) {
                            $with_order = [];
                            foreach ( $field["order"] as $ordered_key ) {
                                $with_order[ $ordered_key ] = [];
                            }
                            foreach ( $fields[ $key ]["default"] as $option_key => $option_value ) {
                                $with_order[ $option_key ] = $option_value;
                            }
                            $fields[ $key ]["default"] = $with_order;
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

        wp_cache_set( "contact_field_settings", $fields );
        return $fields;
    } // End get_custom_fields_settings()

    public function get_post_type_settings_hook( $settings, $post_type ){
        if ( $post_type === "contacts" ){
            $fields = $this->get_custom_fields_settings();
            $settings = [
                'sources' => dt_get_option( 'dt_site_custom_lists' )['sources'],
                'fields' => $fields,
                'address_types' => dt_get_option( "dt_site_custom_lists" )["contact_address_types"],
                'channels' => $this->get_channels_list(),
                'connection_types' => array_keys( array_filter( $fields, function ( $a ) {
                    return $a["type"] === "connection";
                } ) ),
                'label_singular' => $this->singular,
                'label_plural' => $this->plural,
                'post_type' => 'contacts'
            ];
        }
        return $settings;
    }

    /**
     * Field: Contact Fields
     *
     * @return array
     */
    public function contact_fields( int $post_id ) {
        global $wpdb, $post;

        $fields = [];
        $current_fields = [];

        $id = $post->ID ?? $post_id;
        if ( isset( $post->ID ) || isset( $post_id ) ) {
            $current_fields = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT
                        meta_key
                    FROM
                        `$wpdb->postmeta`
                    WHERE
                        post_id = %d
                        AND meta_key LIKE %s
                    ORDER BY
                        meta_key DESC",
                    $id,
                    $wpdb->esc_like( 'contact_' ) . '%'
                ),
                ARRAY_A
            );
        }

        foreach ( $current_fields as $value ) {
            $names = explode( '_', $value['meta_key'] );
            $tag = null;

            if ( strpos( $value["meta_key"], "_details" ) == false ) {
                $details = get_post_meta( $id, $value['meta_key'] . "_details", true );
                if ( $details && isset( $details["type"] ) ) {
                    if ( $names[1] != $details["type"] ) {
                        $tag = ' (' . ucwords( $details["type"] ) . ')';
                    }
                }
                $fields[ $value['meta_key'] ] = [
                    'name' => ucwords( $names[1] ) . $tag,
                    'tag'  => $names[1],
                ];
            }
        }

        return $fields;
    }


    /**
     * Helper function to create the unique metakey for contacts channels.
     *
     * @param $channel_key
     * @param $field_type
     *
     * @return string
     */
    public function create_channel_metakey( $channel_key, $field_type ) {
        return $field_type . '_' . $channel_key . '_' . $this->unique_hash(); // build key
    }

    /**
     * Create a unique hash for the key.
     *
     * @return bool|string
     */
    public function unique_hash() {
        return substr( md5( rand( 10000, 100000 ) ), 0, 3 ); // create a unique 3 digit key
    }

    /**
     * Get a list of the contact channels and their types
     *
     * @access public
     * @since  0.1.0
     * @return mixed
     */
    public function get_channels_list() {
        $channel_list = [
            "phone"     => [
                "label" => __( 'Phone', 'disciple_tools' ),
                "types" => [
                    "primary" => [ "label" => __( 'Primary', 'disciple_tools' ) ],
                    "mobile"  => [ "label" => __( 'Mobile', 'disciple_tools' ) ],
                    "work"    => [ "label" => __( 'Work', 'disciple_tools' ) ],
                    "home"    => [ "label" => __( 'Home', 'disciple_tools' ) ],
                    "other"   => [ "label" => __( 'Other', 'disciple_tools' ) ],
                ],
            ],
            "email"     => [
                "label" => __( 'Email', 'disciple_tools' ),
                "types" => [
                    "primary" => [ "label" => __( 'Primary', 'disciple_tools' ) ],
                    "work"    => [ "label" => __( 'Work', 'disciple_tools' ) ],
                    "other"   => [ "label" => __( 'Other', 'disciple_tools' ) ],
                ],
            ],
            "address" => [
                "label" => __( "Address", 'disciple_tools' ),
                "types" => dt_get_option( "dt_site_custom_lists" )["contact_address_types"]
            ],
            "facebook"  => [
                "label" => __( 'Facebook', 'disciple_tools' ),
                "types" => [
                    "facebook" => [
                        "label" => __( 'Facebook', 'disciple_tools' ),
                    ],
                ],
                "icon" => get_template_directory_uri() . "/dt-assets/images/facebook.svg",
                "hide_domain" => true
            ],
            "twitter"   => [
                "label" => __( 'Twitter', 'disciple_tools' ),
                "types" => [
                    "twitter" => [ "label" => __( 'Twitter', 'disciple_tools' ) ],
                ],
                "icon" => get_template_directory_uri() . "/dt-assets/images/twitter.svg",
                "hide_domain" => true
            ],
            "instagram" => [
                "label" => __( 'Instagram', 'disciple_tools' ),
                "types" => [
                    "instagram" => [ "label" => __( 'Instagram', 'disciple_tools' ) ],
                ],
            ],
            "skype"     => [
                "label" => __( 'Skype', 'disciple_tools' ),
                "types" => [
                    "skype" => [ "label" => __( 'Skype', 'disciple_tools' ) ],
                ],
            ],
            "other"     => [
                "label" => __( 'Other', 'disciple_tools' ),
                "types" => [
                    "other" => [ "label" => __( 'Other', 'disciple_tools' ) ],
                ],
            ],
        ];

        $custom_channels = dt_get_option( "dt_custom_channels" );
        foreach ( $custom_channels as $custom_key => $custom_value ){
            $channel_list[$custom_key] = array_merge( $channel_list[$custom_key] ?? [], $custom_value );
        }
        return apply_filters( 'dt_custom_channels', $channel_list );
    }


    /**
     * Customise the "Enter title here" text.
     *
     * @access public
     * @since  0.1.0
     *
     * @param  string $title
     *
     * @return string
     */
    public function enter_title_here( $title ) {
        if ( get_post_type() == $this->post_type ) {
            $title = __( 'Enter the contact name here', 'disciple_tools' );
        }

        return $title;
    } // End enter_title_here()

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
    public function contacts_permalink( $post_link, $post ) {
        if ( $post->post_type === "contacts" ) {
            return home_url( "contacts/" . $post->ID . '/' );
        } else {
            return $post_link;
        }
    }

    public function contacts_rewrites_init() {
        add_rewrite_rule( 'contacts/([0-9]+)?$', 'index.php?post_type=contacts&p=$matches[1]', 'top' );
    }


} // End Class
