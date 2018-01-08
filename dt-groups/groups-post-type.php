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
    public static function instance()
    {
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
    public function __construct( $post_type = 'groups', $singular = '', $plural = '', $args = [], $taxonomies = [ 'Cities' ] )
    {
        $this->post_type = 'groups';
        $this->singular = __( 'Group', 'disciple_tools' );
        $this->plural = __( 'Groups', 'disciple_tools' );
        $this->args = [ 'menu_icon' => dt_svg_icon() ];

        add_action( 'init', [ $this, 'register_post_type' ] );
        add_action( 'init', [ $this, 'groups_rewrites_init' ] );
        add_filter( 'post_type_link', [ $this, 'groups_permalink' ], 1, 3 );

        if ( is_admin() ) {
            global $pagenow;

            add_action( 'admin_menu', [ $this, 'meta_box_setup' ], 20 );
            add_action( 'save_post', [ $this, 'meta_box_save' ] );
            add_filter( 'enter_title_here', [ $this, 'enter_title_here' ] );
            //            add_filter( 'post_updated_messages', [ $this, 'updated_messages' ] );

            if ( $pagenow == 'edit.php' && isset( $_GET['post_type'] ) && esc_attr( sanitize_text_field( wp_unslash( $_GET['post_type'] ) ) ) == $this->post_type ) {
                add_filter( 'manage_edit-' . $this->post_type . '_columns', [ $this, 'register_custom_column_headings' ], 10, 1 );
                add_action( 'manage_posts_custom_column', [ $this, 'register_custom_columns' ], 10, 2 );
            }
        }
    } // End __construct()

    /**
     * Register the post type.
     *
     * @access public
     * @return void
     */
    public function register_post_type()
    {
        $labels = [
            'name'                  => $this->plural,
            'singular_name'         => $this->singular,
            'add_new'               => _x( 'Add New', 'groups', 'disciple_tools' ),
            'add_new_item'          => sprintf( __( 'Add New %s', 'disciple_tools' ), $this->singular ),
            'edit_item'             => sprintf( __( 'Edit %s', 'disciple_tools' ), $this->singular ),
            'update_item'           => sprintf( __( 'Update %s', 'disciple_tools' ), $this->singular ),
            'new_item'              => sprintf( __( 'New %s', 'disciple_tools' ), $this->singular ),
            'all_items'             => sprintf( __( 'All %s', 'disciple_tools' ), $this->plural ),
            'view_item'             => sprintf( __( 'View %s', 'disciple_tools' ), $this->singular ),
            'view_items'            => sprintf( __( 'View %s', 'disciple_tools' ), $this->plural ),
            'search_items'          => sprintf( __( 'Search %a', 'disciple_tools' ), $this->plural ),
            'not_found'             => sprintf( __( 'No %s Found', 'disciple_tools' ), $this->plural ),
            'not_found_in_trash'    => sprintf( __( 'No %s Found In Trash', 'disciple_tools' ), $this->plural ),
            'parent_item_colon'     => '',
            'menu_name'             => $this->plural,
            'featured_image'        => sprintf( __( 'Featured Image', 'disciple_tools' ), $this->plural ),
            'set_featured_image'    => sprintf( __( 'Set featured image', 'disciple_tools' ), $this->plural ),
            'remove_featured_image' => sprintf( __( 'Remove featured image', 'disciple_tools' ), $this->plural ),
            'use_featured_image'    => sprintf( __( 'Use as featured image', 'disciple_tools' ), $this->plural ),
            'insert_into_item'      => sprintf( __( 'Insert %s', 'disciple_tools' ), $this->plural ),
            'uploaded_to_this_item' => sprintf( __( 'Uploaded to this %s', 'disciple_tools' ), $this->plural ),
            'items_list'            => sprintf( __( '%s list', 'disciple_tools' ), $this->plural ),
            'items_list_navigation' => sprintf( __( '%s list navigation', 'disciple_tools' ), $this->plural ),
            'filter_items_list'     => sprintf( __( 'Filter %s list', 'disciple_tools' ), $this->plural ),
        ];
        $capabilities = [
            'edit_post'           => 'access_groups',
            'read_post'           => 'access_groups',
            'delete_post'         => 'delete_any_group',
            'delete_others_posts' => 'delete_any_group',
            'delete_posts'        => 'delete_any_group',
            'edit_posts'          => 'access_groups',
            'edit_others_posts'   => 'update_any_group',
            'publish_posts'       => 'create_groups',
            'read_private_posts'  => 'view_any_group',
        ];

        $single_slug = apply_filters( 'dt_single_slug', sanitize_title_with_dashes( _x( 'Group', 'single post url slug', 'disciple_tools' ) ) );
        $archive_slug = apply_filters( 'dt_archive_slug', sanitize_title_with_dashes( _x( 'Groups', 'post archive url slug', 'disciple_tools' ) ) );

        $defaults = [
            'labels'                => $labels,
            'public'                => true,
            'publicly_queryable'    => true,
            'show_ui'               => true,
            'show_in_menu'          => true,
            'query_var'             => true,
            'rewrite'               => [ 'slug' => $single_slug ],
            'capability_type'       => 'group',
            'capabilities'          => $capabilities,
            'has_archive'           => $archive_slug,
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


    /**
     * Add custom columns for the "manage" screen of this post type.
     *
     * @access public
     *
     * @param  string $column_name
     *
     * @since  0.1.0
     * @return void
     */
    public function register_custom_columns( $column_name )
    {
        //        global $post;

        switch ( $column_name ) {
            case 'image':
                break;

            default:
                break;
        }
    } // End register_custom_columns()

    /**
     * Add custom column headings for the "manage" screen of this post type.
     *
     * @access public
     *
     * @param  array $defaults
     *
     * @since  0.1.0
     * @return mixed
     */
    public function register_custom_column_headings( $defaults )
    {
        $new_columns = [ 'location' => __( 'Location', 'disciple_tools' ) ];

        $last_item = [];

        //		if ( isset( $defaults['date'] ) ) { unset( $defaults['date'] ); }

        if ( count( $defaults ) > 2 ) {
            $last_item = array_slice( $defaults, -1 );

            array_pop( $defaults );
        }
        $defaults = array_merge( $defaults, $new_columns );

        if ( is_array( $last_item ) && 0 < count( $last_item ) ) {
            foreach ( $last_item as $k => $v ) {
                $defaults[ $k ] = $v;
                break;
            }
        }

        return $defaults;
    } // End register_custom_column_headings()

    /**
     * Update messages for the post type admin.
     *
     * @since  0.1.0
     *
     * @param  array $messages Array of messages for all post types.
     *
     * @return array           Modified array.
     */
    public function updated_messages( $messages )
    {
        global $post;

        $messages[ $this->post_type ] = [
            0  => '', // Unused. Messages start at index 1.
            1  => sprintf( __( '%3$s updated. %1$sView %4$s%$2s', 'disciple_tools' ), [ '<a href="' . esc_url( get_permalink( $post->ID ) ) . '">', '</a>', $this->singular, strtolower( $this->singular ) ] ),
            2  => __( 'Custom field updated.', 'disciple_tools' ),
            3  => __( 'Custom field deleted.', 'disciple_tools' ),
            4  => sprintf( __( '%s updated.', 'disciple_tools' ), $this->singular ),
            5  => isset( $_GET['revision'] ) ? sprintf( __( '%1$s restored to revision from %2$s', 'disciple_tools' ), $this->singular, wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
            6  => sprintf( __( '%1$s published. %3$sView %2$s%4$s', 'disciple_tools' ), $this->singular, strtolower( $this->singular ), '<a href="' . esc_url( get_permalink( $post->ID ) ) . '">', '</a>' ),
            7  => sprintf( __( '%s saved.', 'disciple_tools' ), $this->singular ),
            8  => sprintf( __( '%1$s submitted. %2$sPreview %3$s%4$s', 'disciple_tools' ), $this->singular, strtolower( $this->singular ), '<a target="_blank" href="' . esc_url( add_query_arg( 'preview', 'true', get_permalink( $post->ID ) ) ) . '">', '</a>' ),
            9  => sprintf(
                __( '%1$s scheduled for: %3$s. %4$sPreview %$2s%5$s', 'disciple_tools' ),
                $this->singular,
                strtolower( $this->singular ),
                // translators: Publish box date format, see http://php.net/date
                '<strong>' . date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ) . '</strong>',
                '<a target="_blank" href="' . esc_url( get_permalink( $post->ID ) ) . '">',
                '</a>'
            ),
            10 => sprintf( __( '%1$s draft updated. %2$sPreview %3$s%4$s', 'disciple_tools' ), $this->singular, strtolower( $this->singular ), '<a target="_blank" href="' . esc_url( add_query_arg( 'preview', 'true', get_permalink( $post->ID ) ) ) . '">', '</a>' ),
        ];

        return $messages;
    } // End updated_messages()

    /**
     * Setup the meta box.
     *
     * @access public
     * @since  0.1.0
     * @return void
     */
    public function meta_box_setup()
    {
        add_meta_box( $this->post_type . '_type', __( 'Group Details', 'disciple_tools' ), [ $this, 'load_type_meta_box' ], $this->post_type, 'normal', 'high' );
        add_meta_box( $this->post_type . '_address', __( 'Address', 'disciple_tools' ), [ $this, 'load_address_meta_box' ], $this->post_type, 'normal', 'high' );
        add_meta_box( $this->post_type . '_info', __( 'Info', 'disciple_tools' ), [ $this, 'load_info_meta_box' ], $this->post_type, 'normal', 'high' );
        add_meta_box( $this->post_type . '_activity', __( 'Activity', 'disciple_tools' ), [ $this, 'load_activity_meta_box' ], $this->post_type, 'normal', 'low' );
    } // End meta_box_setup()

    /**
     * Load activity metabox
     */
    public function load_activity_meta_box()
    {
        dt_activity_metabox()->activity_meta_box( get_the_ID() );
    }

    /**
     * Load type metabox
     */
    public function load_type_meta_box()
    {
        $this->meta_box_content( 'church' ); // prints
        $this->meta_box_content( 'church_hidden' ); // prints
    }

    /**
     * Load type metabox
     */
    public function load_info_meta_box()
    {
        $this->meta_box_content( 'info' ); // prints
    }

    /**
     * Load address metabox
     */
    public function load_address_meta_box()
    {
        $this->meta_box_content( 'address' ); // prints
        dt_address_metabox()->add_new_address_field(); // prints
    }

    /**
     * The contents of our meta box.
     *
     * @param string $section
     */
    public function meta_box_content( $section = 'info' )
    {
        global $post_id;
        $fields = get_post_custom( $post_id );
        $field_data = $this->get_custom_fields_settings();

        echo '<input type="hidden" name="dt_' . esc_attr( $this->post_type ) . '_noonce" id="dt_' . esc_attr( $this->post_type ) . '_noonce" value="' . esc_attr( wp_create_nonce( 'update_dt_groups' ) ) . '" />';

        if ( 0 < count( $field_data ) ) {
            echo '<table class="form-table">' . "\n";
            echo '<tbody>' . "\n";

            foreach ( $field_data as $k => $v ) {

                if ( $v['section'] == $section || $section == 'all' ) {

                    $data = $v['default'];

                    if ( isset( $fields[ $k ] ) && isset( $fields[ $k ][0] ) ) {
                        $data = $fields[ $k ][0];
                    }

                    $type = $v['type'];

                    switch ( $type ) {

                        case 'text':
                            echo '<tr valign="top"><th scope="row"><label for="' . esc_attr( $k ) . '">' . esc_attr( $v['name'] ) . '</label></th><td><input name="' . esc_attr( $k ) . '" type="text" id="' . esc_attr( $k ) . '" class="regular-text" value="' . esc_attr( $data ) . '" />' . "\n";
                            echo '<p class="description">' . esc_html( $v['description'] ) . '</p>' . "\n";
                            echo '</td><tr/>' . "\n";
                            break;
                        case 'date':
                            echo '<tr valign="top"><th scope="row"><label for="' . esc_attr( $k ) . '">' . esc_attr( $v['name'] ) . '</label></th><td><input name="' . esc_attr( $k ) . '" class="datepicker regular-text" type="text" id="' . esc_attr( $k ) . '"  value="' . esc_attr( $data ) . '" />' . "\n";
                            echo '<p class="description">' . esc_html( $v['description'] ) . '</p>' . "\n";
                            echo '</td><tr/>' . "\n";

                            break;
                        case 'key_select':
                            echo '<tr class="' . esc_attr( $v['section'] ) . '" id="row_' . esc_attr( $k ) . '" valign="top"><th scope="row">
                                <label for="' . esc_attr( $k ) . '">' . esc_attr( $v['name'] ) . '</label></th>
                                <td>
                                <select name="' . esc_attr( $k ) . '" id="' . esc_attr( $k ) . '" class="regular-text">';
                            // Iterate the options
                            foreach ( $v['default'] as $kk => $vv ) {
                                echo '<option value="' . esc_attr( $kk ) . '" ';
                                if ( $kk == $data ) {
                                    echo 'selected';
                                }
                                echo '>' . esc_attr( $vv ) . '</option>';
                            }
                            echo '</select>' . "\n";
                            echo '<p class="description">' . esc_attr( $v['description'] ) . '</p>' . "\n";
                            echo '</td><tr/>' . "\n";
                            break;

                        case 'radio':
                            echo '<tr valign="top"><th scope="row">' . esc_attr( $v['name'] ) . '</th>
                                <td><fieldset>';
                            // Iterate the buttons
                            $increment_the_radio_button = 1;
                            foreach ( $v['default'] as $vv ) {
                                echo '<label for="' . esc_attr( "$k-$increment_the_radio_button" ) . '">' . esc_attr( $vv ) . '</label>
                                    <input class="drm-radio" type="radio" name="' . esc_attr( $k ) . '" id="' . esc_attr( $k . '-' . $increment_the_radio_button ) . '" value="' . esc_attr( $vv ) . '" ';
                                if ( $vv == $data ) {
                                    echo 'checked';
                                }
                                echo '>';
                                $increment_the_radio_button++;
                            }
                            echo '</fieldset>' . "\n";
                            echo '<p class="description">' . esc_attr( $v['description'] ) . '</p>' . "\n";
                            echo '</td><tr/>' . "\n";
                            break;
                        case 'custom':
                            echo '<tr valign="top"><th scope="row"><label for="' . esc_attr( $k ) . '" class="selectit">' . esc_attr( $v['name'] ) . '</label></th><td>';
                            echo wp_kses(
                                $v['default'],
                                [
                                    'a'      => [
                                        'id'    => [],
                                        'name'  => [],
                                        'href'  => [],
                                        'class' => [],
                                    ],
                                    'select' => [
                                        'id'    => [],
                                        'name'  => [],
                                        'class' => [],
                                    ],
                                    'option' => [
                                        'id'    => [],
                                        'name'  => [],
                                        'class' => [],
                                        'value' => [],
                                    ],
                                    'input'  => [
                                        'id'    => [],
                                        'name'  => [],
                                        'class' => [],
                                        'value' => [],
                                    ],
                                    'br'     => [],
                                    'strong' => [],
                                    'em'     => [],
                                ]
                            );
                            echo '</td><tr/>' . "\n";
                            break;

                        default:
                            break;
                    }
                }
            }

            echo '</tbody>' . "\n";
            echo '</table>' . "\n";
        }
    } // End meta_box_content()

    /**
     * Save meta box fields.
     *
     * @param int $post_id
     *
     * @return int
     * @throws \Exception 'Expected field to exist'.
     */
    public function meta_box_save( int $post_id )
    {
        //        global $post, $messages;

        // Verify
        if ( get_post_type() != $this->post_type ) {
            return $post_id;
        }
        $nonce_key = 'dt_' . $this->post_type . '_noonce';
        if ( isset( $_POST[ $nonce_key ] ) && !wp_verify_nonce( sanitize_key( $_POST[ $nonce_key ] ), 'update_dt_groups' ) ) {
            return $post_id;
        }

        if ( isset( $_POST['post_type'] ) && 'page' == esc_attr( sanitize_text_field( wp_unslash( $_POST['post_type'] ) ) ) ) {
            if ( !current_user_can( 'edit_page', $post_id ) ) {
                return $post_id;
            }
        } else {
            if ( !current_user_can( 'edit_post', $post_id ) ) {
                return $post_id;
            }
        }

        if ( isset( $_GET['action'] ) ) {
            if ( $_GET['action'] == 'trash' || $_GET['action'] == 'untrash' || $_GET['action'] == 'delete' ) {
                return $post_id;
            }
        }

        $field_data = $this->get_custom_fields_settings();
        $fields = array_keys( $field_data );

        if ( ( isset( $_POST['new-key-address'] ) && !empty( $_POST['new-key-address'] ) ) && ( isset( $_POST['new-value-address'] ) && !empty( $_POST['new-value-address'] ) ) ) { // catch and prepare new contact fields
            $k = explode( "_", sanitize_text_field( wp_unslash( $_POST['new-key-address'] ) ) );
            $type = $k[1];
            $number_key = dt_address_metabox()->create_channel_metakey( "address" );
            $details_key = $number_key . "_details";
            $details = [ 'type' => $type, 'verified' => false ];
            //save the field and the field details
            add_post_meta( $post_id, strtolower( $number_key ), sanitize_text_field( wp_unslash( $_POST['new-value-address'] ) ), true );
            add_post_meta( $post_id, strtolower( $details_key ), $details, true );
        }

        foreach ( $fields as $f ) {

            if ( isset( $_POST[ $f ] ) ) {

                ${$f} = strip_tags( trim( sanitize_text_field( wp_unslash( $_POST[ $f ] ) ) ) );

                if ( get_post_meta( $post_id, $f ) == '' ) {
                    add_post_meta( $post_id, $f, ${$f}, true );
                } elseif ( ${$f} == '' ) {
                    delete_post_meta( $post_id, $f, get_post_meta( $post_id, $f, true ) );
                } elseif ( ${$f} != get_post_meta( $post_id, $f, true ) ) {
                    update_post_meta( $post_id, $f, ${$f} );
                }
            } else {
                throw new Exception( "Expected field $f to exist" );
            }
        }

        return $post_id;
    } // End meta_box_save()

    /**
     * Field: The 'Assigned To' dropdown controller
     *
     * @return string
     */
    public function assigned_to_field()
    {
        global $post;

        //        $exclude_group = '';
        $exclude_user = '';
        ob_start();

        // Start drop down
        echo '<select name="assigned_to" id="assigned_to" class="edit-input">';

        // Set selected state
        if ( isset( $post->ID ) ) {
            $assigned_to = get_post_meta( $post->ID, 'assigned_to', true );
        }

        if ( empty( $assigned_to ) ) {
            // set default to dispatch
            echo '<option value="" selected></option>';
        } elseif ( !empty( $assigned_to ) ) { // If there is already a record
            $metadata = get_post_meta( $post->ID, 'assigned_to', true );
            $meta_array = explode( '-', $metadata ); // Separate the type and id
            $type = $meta_array[0]; // Build variables

            // Build option for current value
            if ( $type == 'user' && isset( $meta_array[1] ) ) {
                $id = $meta_array[1];
                $value = get_user_by( 'id', $id );
                if ( $value ) {
                    echo '<option value="user-' . esc_attr( $id ) . '" selected>' . esc_html( $value->display_name ) . '</option>';
                    echo '<option>---</option>';
                }
                // exclude the current id from the $results list
                $exclude_user = "'exclude' => $id";
            }
        }

        // Collect user list
        $args = [ 'role__not_in' => [ 'registered', 'prayer_supporter', 'project_supporter' ], 'fields' => [ 'ID', 'display_name' ], 'exclude' => $exclude_user, 'order' => 'ASC' ];
        $results = get_users( $args );

        // Loop user list
        foreach ( $results as $value ) {
            echo '<option value="user-' . esc_attr( $value->ID ) . '">' . esc_html( $value->display_name ) . '</option>';
        }

        // End drop down
        echo '</select>  ';

        return ob_get_clean();
    }

    /**
     * Get the settings for the custom fields.
     *
     * @param          $include_current_post
     * @param int|null $post_id
     *
     * @return mixed
     */
    public function get_custom_fields_settings( $include_current_post = true, int $post_id = null )
    {
        global $post;

        $fields = [];

        $fields['group_status'] = [
            'name'        => __( 'Group Status', 'disciple_tools' ),
            'description' => '',
            'type'        => 'key_select',
            'default'     => [
                'no_value'           => __( 'No value', 'disciple_tools' ),
                'active_pre_group'   => __( 'Active Pre-Group (Faithfully Sharing)', 'disciple_tools' ),
                'active_group'       => __( 'Active Group (Consistently Meeting)', 'disciple_tools' ),
                'active_church'      => __( 'Active Church (3+ Baptized)', 'disciple_tools' ),
                'inactive_church'    => __( 'Inactive Church', 'disciple_tools' ),
                'inactive_group'     => __( 'Inactive Group', 'disciple_tools' ),
                'inactive_pre_group' => __( 'Inactive Pre-Group', 'disciple_tools' ),
            ],
            'section'     => 'info',
        ];

        $fields['assigned_to'] = [
            'name'        => __( 'Assigned To', 'disciple_tools' ),
            'description' => '',
            'type'        => 'custom',
            'default'     => $this->assigned_to_field(),
            'section'     => 'info',
        ];

        // Church
        $fields['is_church'] = [
            'name'        => __( 'Is a Church', 'disciple_tools' ),
            'description' => '',
            'type'        => 'key_select',
            'default'     => [ '0' => __( 'No', 'disciple_tools' ), '1' => __( 'Yes', 'disciple_tools' ) ],
            'section'     => 'church',
        ];

        $fields['church_baptism'] = [
            'name'        => __( 'Baptism', 'disciple_tools' ),
            'description' => '',
            'type'        => 'key_select',
            'default'     => [ '0' => __( 'No', 'disciple_tools' ), '1' => __( 'Yes', 'disciple_tools' ) ],
            'section'     => 'church_hidden',
        ];
        $fields['church_bible'] = [
            'name'        => __( 'Bible Study', 'disciple_tools' ),
            'description' => '',
            'type'        => 'key_select',
            'default'     => [ '0' => __( 'No', 'disciple_tools' ), '1' => __( 'Yes', 'disciple_tools' ) ],
            'section'     => 'church_hidden',
        ];
        $fields['church_communion'] = [
            'name'        => __( 'Communion', 'disciple_tools' ),
            'description' => '',
            'type'        => 'key_select',
            'default'     => [ '0' => __( 'No', 'disciple_tools' ), '1' => __( 'Yes', 'disciple_tools' ) ],
            'section'     => 'church_hidden',
        ];
        $fields['church_fellowship'] = [
            'name'        => __( 'Fellowship', 'disciple_tools' ),
            'description' => '',
            'type'        => 'key_select',
            'default'     => [ '0' => __( 'No', 'disciple_tools' ), '1' => __( 'Yes', 'disciple_tools' ) ],
            'section'     => 'church_hidden',
        ];
        $fields['church_giving'] = [
            'name'        => __( 'Giving', 'disciple_tools' ),
            'description' => '',
            'type'        => 'key_select',
            'default'     => [ '0' => __( 'No', 'disciple_tools' ), '1' => __( 'Yes', 'disciple_tools' ) ],
            'section'     => 'church_hidden',
        ];
        $fields['church_prayer'] = [
            'name'        => __( 'Prayer', 'disciple_tools' ),
            'description' => '',
            'type'        => 'key_select',
            'default'     => [ '0' => __( 'No', 'disciple_tools' ), '1' => __( 'Yes', 'disciple_tools' ) ],
            'section'     => 'church_hidden',
        ];
        $fields['church_praise'] = [
            'name'        => __( 'Praise', 'disciple_tools' ),
            'description' => '',
            'type'        => 'key_select',
            'default'     => [ '0' => __( 'No', 'disciple_tools' ), '1' => __( 'Yes', 'disciple_tools' ) ],
            'section'     => 'church_hidden',
        ];
        $fields['church_sharing'] = [
            'name'        => __( 'Sharing the Gospel', 'disciple_tools' ),
            'description' => '',
            'type'        => 'key_select',
            'default'     => [ '0' => __( 'No', 'disciple_tools' ), '1' => __( 'Yes', 'disciple_tools' ) ],
            'section'     => 'church_hidden',
        ];
        $fields['church_leaders'] = [
            'name'        => __( 'Leaders', 'disciple_tools' ),
            'description' => '',
            'type'        => 'key_select',
            'default'     => [ '0' => __( 'No', 'disciple_tools' ), '1' => __( 'Yes', 'disciple_tools' ) ],
            'section'     => 'church_hidden',
        ];
        $fields['church_commitment'] = [
            'name'        => __( 'Church Commitment', 'disciple_tools' ),
            'description' => '',
            'type'        => 'key_select',
            'default'     => [ '0' => __( 'No', 'disciple_tools' ), '1' => __( 'Yes', 'disciple_tools' ) ],
            'section'     => 'church_hidden',
        ];

        $fields['start_date'] = [
            'name'        => __( 'Start Date', 'disciple_tools' ),
            'description' => '',
            'type'        => 'date',
            'default'     => date( 'Y-m-d' ),
            'section'     => 'info',
        ];
        $fields['end_date'] = [
            'name'        => __( 'End Date', 'disciple_tools' ),
            'description' => '',
            'type'        => 'date',
            'default'     => '',
            'section'     => 'info',
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

        return apply_filters( 'dt_custom_fields_settings', $fields );
    } // End get_custom_fields_settings()

    /**
     * Customise the "Enter title here" text.
     *
     * @access public
     * @since  0.1.0
     *
     * @param string $title
     *
     * @return string
     */
    public function enter_title_here( string $title )
    {
        if ( get_post_type() == $this->post_type ) {
            $title = __( 'Enter the group here', 'disciple_tools' );
        }

        return $title;
    } // End enter_title_here()

    /**
     * Run on activation.
     *
     * @access public
     * @since  0.1.0
     */
    public function activation()
    {
        $this->flush_rewrite_rules();
    } // End activation()

    /**
     * Flush the rewrite rules
     *
     * @access public
     * @since  0.1.0
     */
    private function flush_rewrite_rules()
    {
        $this->register_post_type();
        flush_rewrite_rules();
    } // End flush_rewrite_rules()

    /**
     * @param $post_link
     * @param $post
     *
     * @return string
     */
    public function groups_permalink( $post_link, $post )
    {
        if ( $post->post_type === "groups" ) {
            return home_url( "groups/" . $post->ID . '/' );
        } else {
            return $post_link;
        }
    }

    /**
     *
     */
    function groups_rewrites_init()
    {
        add_rewrite_rule( 'groups/([0-9]+)?$', 'index.php?post_type=groups&p=$matches[1]', 'top' );
    }

} // End Class
