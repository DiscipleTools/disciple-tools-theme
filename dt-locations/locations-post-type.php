<?php
if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly.

/**
 * Disciple Tools Post Type Class
 *
 * @author     Chasm.Solutions & Kingdom.Training
 * @since      0.1.0
 */
class Disciple_Tools_Location_Post_Type
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
     * Disciple_Tools_Location_Post_Type The single instance of Disciple_Tools_Location_Post_Type.
     *
     * @var    object
     * @access private
     * @since  0.1.0
     */
    private static $_instance = null;

    /**
     * Main Disciple_Tools_Location_Post_Type Instance
     * Ensures only one instance of Disciple_Tools_Location_Post_Type is loaded or can be loaded.
     *
     * @since  0.1.0
     * @static
     * @return Disciple_Tools_Location_Post_Type instance
     */
    public static function instance()
    {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }

        return self::$_instance;
    } // End instance()

    /**
     * Constructor function.
     *
     * @access public
     * @since  0.1.0
     */
    public function __construct()
    {
        $this->post_type = 'locations';
        $this->singular = __( 'Location', 'disciple_tools' );
        $this->plural = __( 'Locations', 'disciple_tools' );
        $this->args = [ 'menu_icon' => dt_svg_icon() ];

        add_action( 'init', [ $this, 'register_post_type' ] );

        if ( is_admin() ) {
            global $pagenow;

            add_action( 'admin_menu', [ $this, 'meta_box_setup' ], 20 );
            add_action( 'save_post', [ $this, 'meta_box_save' ] );
            add_filter( 'enter_title_here', [ $this, 'enter_title_here' ] );
            add_filter( 'post_updated_messages', [ $this, 'updated_messages' ] );

            if ( isset( $_GET['post_type'] ) ) {
                $post_type = sanitize_text_field( wp_unslash( $_GET['post_type'] ) );
                if ( $pagenow == 'edit.php' && $this->post_type === $post_type ) {
                    add_filter( 'manage_edit-' . $this->post_type . '_columns', [ $this, 'register_custom_column_headings' ], 10, 1 );
                    add_action( 'manage_posts_custom_column', [ $this, 'register_custom_columns' ], 10, 2 );
                }
            }

            add_action( 'admin_init', [ $this, 'remove_add_new_submenu' ] );
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
            'name'                  => _x( 'Locations', 'post type general name', 'disciple_tools' ),
            'singular_name'         => _x( 'Location', 'post type singular name', 'disciple_tools' ),
            'add_new'               => _x( 'Add New', 'Locations', 'disciple_tools' ),
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

        $rewrite = [
            'slug'       => 'locations',
            'with_front' => true,
            'pages'      => true,
            'feeds'      => false,
        ];
        $capabilities = [
            'edit_post'           => 'edit_location',
            'read_post'           => 'read_location',
            'delete_post'         => 'delete_location',
            'delete_others_posts' => 'delete_others_locations',
            'delete_posts'        => 'delete_locations',
            'edit_posts'          => 'edit_locations',
            'edit_others_posts'   => 'edit_others_locations',
            'publish_posts'       => 'publish_locations',
            'read_private_posts'  => 'read_private_locations',
        ];
        $defaults = [
            'labels'                => $labels,
            'public'                => true,
            'publicly_queryable'    => true,
            'show_ui'               => true,
            'show_in_menu'          => true,
            'query_var'             => true,
            'rewrite'               => $rewrite,
            'capabilities'          => $capabilities,
            'has_archive'           => true,
            'hierarchical'          => true,
            'supports'              => [ 'title', 'comments', 'page-attributes' ],
            'menu_position'         => 6,
            'menu_icon'             => 'dashicons-smiley',
            'show_in_rest'          => true,
            'rest_base'             => 'locations',
            'rest_controller_class' => 'WP_REST_Posts_Controller',
        ];

        $args = wp_parse_args( $this->args, $defaults );

        register_post_type( $this->post_type, $args );
    } // End register_post_type()

    /**
     * Add custom columns for the "manage" screen of this post type.
     *
     * @param $column_name
     */
    public function register_custom_columns( $column_name, $post_id )
    {

        switch ( $column_name ) {
            case 'location_address':
                dt_write_log( 'location_address' );
                echo esc_attr( get_post_meta( $post_id, 'location_address', true ) );
                break;
            case 'location_parent':
                dt_write_log( 'location_parent' );
                echo esc_attr( get_post_meta( $post_id, 'location_parent', true ) );
                break;
            case 'level':
                dt_write_log( 'level' );
                echo esc_attr( get_post_meta( $post_id, 'level', true ) );
                break;
            case 'map':
                dt_write_log( 'map' );
                echo esc_attr( get_post_meta( $post_id, 'lat', true ) ) . ', ' . esc_attr( get_post_meta( $post_id, 'lng', true ) );
                break;

            default:
                break;
        }
    } // End register_custom_columns()

    /**
     * Add custom column headings for the "manage" screen of this post type.
     *
     * @param $defaults
     *
     * @return array
     */
    public function register_custom_column_headings( $defaults )
    {

        $new_columns =
        [
            'location_address' => __( 'Address', 'disciple_tools' ),
            'location_parent'  => __( 'Parent', 'disciple_tools' ),
            'level'            => __( 'Level', 'disciple_tools' ),
            'map'              => __( 'Map', 'disciple_tools' ),
        ];

        $last_item = [];

        if ( isset( $defaults['date'] ) ) {
            unset( $defaults['date'] );
        }

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
            1  => sprintf( __( '%1$s updated. %2$s View %3$s %4$s', 'disciple_tools' ), $this->singular, '<a href="' . esc_url( get_permalink( $post->ID ) ) . '">', strtolower( $this->singular ), '</a>' ),
            2  => __( 'Custom field updated.', 'disciple_tools' ),
            3  => __( 'Custom field deleted.', 'disciple_tools' ),
            4  => sprintf( __( '%s updated.', 'disciple_tools' ), $this->singular ),
            /* translators: %s: date and time of the revision */
            5  => isset( $_GET['revision'] ) ? sprintf( __( '%1$s restored to revision from %2$s', 'disciple_tools' ), $this->singular, wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
            6  => sprintf( __( '%1$s published. %3$sView %2$s%4$s', 'disciple_tools' ), $this->singular, strtolower( $this->singular ), '<a href="' . esc_url( get_permalink( $post->ID ) ) . '">', '</a>' ),
            7  => sprintf( __( '%s saved.', 'disciple_tools' ), $this->singular ),
            8  => sprintf( __( '%1$s submitted. %2$sPreview %3$s%4$s', 'disciple_tools' ), $this->singular, strtolower( $this->singular ), '<a target="_blank" href="' . esc_url( add_query_arg( 'preview', 'true', get_permalink( $post->ID ) ) ) . '">', '</a>' ),
            9  => sprintf(
                __( '%1$s scheduled for: %2$s. %3$sPreview %4$s %5$s', 'disciple_tools' ),
                $this->singular,
                // translators: Publish box date format, see http://php.net/date
                '<strong>' . date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ) . '</strong>',
                '<a target="_blank" href="' . esc_url( get_permalink( $post->ID ) ) . '">',
                strtolower( $this->singular ),
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
        add_meta_box( $this->post_type . '_geocode', __( 'Geo-Code', 'disciple_tools' ), [ $this, 'geocode_metabox' ], $this->post_type, 'normal', 'high' );
        add_meta_box( $this->post_type . '_map', __( 'Map', 'disciple_tools' ), [ $this, 'load_map_meta_box' ], $this->post_type, 'advanced', 'high' );
        add_meta_box( $this->post_type . '_activity', __( 'Activity', 'disciple_tools' ), [ $this, 'load_activity_meta_box' ], $this->post_type, 'advanced', 'low' );
    } // End meta_box_setup()

    /**
     * Load activity metabox
     */
    public function load_activity_meta_box()
    {
        dt_activity_metabox()->activity_meta_box( get_the_ID() );
    }

    /**
     * Load activity metabox
     */
    public function load_map_meta_box()
    {
        $this->display_location_map();
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

        echo '<input type="hidden" name="dt_' . esc_attr( $this->post_type ) . '_noonce" id="dt_' . esc_attr( $this->post_type ) . '_noonce" value="' . esc_attr( wp_create_nonce( 'update_location_info' ) ) . '" />';

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
                            echo '<p class="description">' . esc_attr( $v['description'] ) . '</p>' . "\n";
                            echo '</td><tr/>' . "\n";
                            break;
                        case 'select':
                            echo '<tr valign="top"><th scope="row">
							<label for="' . esc_attr( $k ) . '">' . esc_attr( $v['name'] ) . '</label></th>
							<td><select name="' . esc_attr( $k ) . '" id="' . esc_attr( $k ) . '" class="regular-text">';
                            // Iterate the options
                            foreach ( $v['default'] as $vv ) {
                                echo '<option value="' . esc_attr( $vv ) . '" ';
                                if ( $vv == $data ) {
                                    echo 'selected';
                                }
                                echo '>' . esc_html( $vv ) . '</option>';
                            }
                            echo '</select>' . "\n";
                            echo '<p class="description">' . esc_attr( $v['description'] ) . '</p>' . "\n";
                            echo '</td><tr/>' . "\n";
                            break;
                        case 'key_select':
                            echo '<tr valign="top"><th scope="row">
                                <label for="' . esc_attr( $k ) . '">' . esc_attr( $v['name'] ) . '</label></th>
                                <td>
                                <select name="' . esc_attr( $k ) . '" id="' . esc_attr( $k ) . '" class="regular-text">';
                            // Iterate the options
                            foreach ( $v['default'] as $kk => $vv ) {
                                echo '<option value="' . esc_attr( $kk ) . '" ';
                                if ( $kk == $data ) {
                                    echo 'selected';
                                }
                                echo '>' . esc_html( $vv ) . '</option>';
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
                                <input class="dt-radio" type="radio" name="' . esc_attr( $k ) . '" id="' . esc_attr( $k . '-' . $increment_the_radio_button ) . '" value="' . esc_attr( $vv ) . '" ';
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
     * @param $post_id
     *
     * @return mixed
     */
    public function meta_box_save( $post_id )
    {
        // Verify
        $key = 'dt_' . $this->post_type . '_noonce';
        if ( ( get_post_type() != $this->post_type ) || !isset( $_POST[ $key ] ) || !wp_verify_nonce( sanitize_key( $_POST[ $key ] ), 'update_location_info' ) ) {
            return $post_id;
        }

        if ( isset( $_POST['post_type'] ) && 'page' == sanitize_text_field( wp_unslash( $_POST['post_type'] ) ) ) {
            if ( !current_user_can( 'edit_page', $post_id ) ) {
                return $post_id;
            }
        } else {
            if ( !current_user_can( 'edit_post', $post_id ) ) {
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
            if ( !isset( $_POST[ $f ] ) ) {
                continue;
            }

            ${$f} = strip_tags( trim( sanitize_text_field( wp_unslash( $_POST[ $f ] ) ) ) );

            if ( get_post_meta( $post_id, $f ) == '' ) {
                add_post_meta( $post_id, $f, ${$f}, true );
            } elseif ( ${$f} == '' ) {
                delete_post_meta( $post_id, $f, get_post_meta( $post_id, $f, true ) );
            } elseif ( ${$f} != get_post_meta( $post_id, $f, true ) ) {
                update_post_meta( $post_id, $f, ${$f} );
            }
        }
        return $post_id;
    }

    /**
     * Customise the "Enter title here" text.
     *
     * @param $title
     *
     * @return string
     */
    public function enter_title_here( $title )
    {
        if ( get_post_type() == $this->post_type ) {
            $title = __( 'Enter the location title here', 'disciple_tools' );
        }

        return $title;
    }

    /**
     * Get the settings for the custom fields.
     *
     * @access public
     * @since  0.1.0
     * @return array
     */
    public function get_custom_fields_settings()
    {
        global $post;
        $fields = [];

        if ( isset( $post->ID ) && $post->post_status != 'auto-draft' ) { // if being called for a specific record or new record.
            // Address
            $addresses = dt_address_metabox()->address_fields( $post->ID );
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

        $fields['location_address'] = [
            'name'        => 'Location Address ',
            'description' => '',
            'type'        => 'text',
            'default'     => '',
            'section'     => 'map',
        ];




        return apply_filters( 'dt_custom_fields_settings', $fields );
    }

    /**
     * Run on activation.
     *
     * @access public
     * @since  0.1.0
     */
    public function activation()
    {
        $this->flush_rewrite_rules();
    }

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
    }

    /**
     * Remove the add new submenu from the locaions menu
     */
    public function remove_add_new_submenu()
    {
        global $submenu;
        unset(
            $submenu['edit.php?post_type=locations'][10]
        );
    }

    /**
     * @param $post
     * @param $location_address
     *
     * @return mixed
     */
    public function install_google_coordinates( $post, $location_address )
    {

        global $post;

        $query_object = Disciple_Tools_Google_Geolocation::query_google_api( $location_address );

        $location = [];
        $location['lat'] = $query_object->results[0]->geometry->location->lat;
        $location['lng'] = $query_object->results[0]->geometry->location->lng;
        $location['northeast_lat'] = $query_object->results[0]->geometry->bounds->northeast->lat;
        $location['northeast_lng'] = $query_object->results[0]->geometry->bounds->northeast->lng;
        $location['southwest_lat'] = $query_object->results[0]->geometry->bounds->southwest->lat;
        $location['southwest_lng'] = $query_object->results[0]->geometry->bounds->southwest->lng;

        update_post_meta( $post->ID, 'location', $location );
        update_post_meta( $post->ID, 'lat', $location['lat'] );
        update_post_meta( $post->ID, 'lng', $location['lng'] );
        update_post_meta( $post->ID, 'northeast_lat', $location['northeast_lat'] );
        update_post_meta( $post->ID, 'northeast_lng', $location['northeast_lng'] );
        update_post_meta( $post->ID, 'southwest_lat', $location['southwest_lat'] );
        update_post_meta( $post->ID, 'southwest_lng', $location['southwest_lng'] );

        return get_post_meta( $post->ID );
    }

    /**
     * Load map metabox
     */
    public function display_location_map()
    {
        global $post, $pagenow;
        $post_meta = get_post_meta( $post->ID );

        if ( ! ( 'post-new.php' == $pagenow ) ) { // don't run on the post-new.php page

            // check for post submission
            if ( ( get_post_type() == 'locations' && isset( $_POST['dt_locations_noonce'] ) && wp_verify_nonce( sanitize_key( $_POST['dt_locations_noonce'] ), 'update_location_info' ) ) ||
                ( ! ( isset( $post_meta['lat'][0] ) && isset( $post_meta['lng'][0] ) ) ) )
            {
                $location_address = '';
                if ( isset( $post_meta['location_address'][0] ) ) {
                    $location_address = $post_meta['location_address'][0];
                } elseif ( isset( $_POST['location_address'] ) ) {
                    $location_address = sanitize_key( $_POST['location_address'] );
                } else {
                    return new WP_Error( 'no_meta_field_found', 'Did not find address in the location_addres meta-field.' );
                }

                $post_meta = $this->install_google_coordinates( $post, $location_address );
            }


            $lat = (float) $post_meta['lat'][0];
            $lng = (float) $post_meta['lng'][0];
            $northeast_lat = (float) $post_meta['northeast_lat'][0];
            $northeast_lng = (float) $post_meta['northeast_lng'][0];
            $southwest_lat = (float) $post_meta['southwest_lat'][0];
            $southwest_lng = (float) $post_meta['southwest_lng'][0];

            ?>

            <style>
                /* Always set the map height explicitly to define the size of the div
            * element that contains the map. */
                #map {
                    height: 550px;
                    width: 100%;
                    /*max-width:1000px;*/
                }

                /* Optional: Makes the sample page fill the window. */
                html, body {
                    height: 100%;
                    margin: 0;
                    padding: 0;
                }

            </style>
            <div id="map"></div>
            <script type="text/javascript">
                jQuery(document).ready(function () {

                    let $mapDiv = jQuery('#map');

                    let centerLat = <?php echo esc_attr( $lat ); ?>;
                    let centerLng = <?php echo esc_attr( $lng ); ?>;
                    let center = new google.maps.LatLng(centerLat, centerLng);

                    let sw = new google.maps.LatLng(<?php echo esc_attr( $southwest_lat ); ?>, <?php echo esc_attr( $southwest_lng ); ?>);
                    let ne = new google.maps.LatLng(<?php echo esc_attr( $northeast_lat ); ?>, <?php echo esc_attr( $northeast_lng ); ?>);
                    let bounds = new google.maps.LatLngBounds(sw, ne);

                    let mapDim = {height: $mapDiv.height(), width: $mapDiv.width()};

                    let zoom = getBoundsZoomLevel(bounds, mapDim);

                    let map = new google.maps.Map(document.getElementById('map'), {
                        zoom: zoom - 3,
                        center: center,
                        mapTypeId: 'terrain'
                    });


                    let marker = new google.maps.Marker({
                        position: center,
                        map: map,
                    });

                    /**
                     * @see https://stackoverflow.com/questions/6048975/google-maps-v3-how-to-calculate-the-zoom-level-for-a-given-bounds
                     * @param bounds
                     * @param mapDim
                     * @returns {number}
                     */
                    function getBoundsZoomLevel(bounds, mapDim) {
                        let WORLD_DIM = {height: 256, width: 256};
                        let ZOOM_MAX = 21;

                        function latRad(lat) {
                            let sin = Math.sin(lat * Math.PI / 180);
                            let radX2 = Math.log((1 + sin) / (1 - sin)) / 2;
                            return Math.max(Math.min(radX2, Math.PI), -Math.PI) / 2;
                        }

                        function zoom(mapPx, worldPx, fraction) {
                            return Math.floor(Math.log(mapPx / worldPx / fraction) / Math.LN2);
                        }

                        let ne = bounds.getNorthEast();
                        let sw = bounds.getSouthWest();

                        let latFraction = (latRad(ne.lat()) - latRad(sw.lat())) / Math.PI;

                        let lngDiff = ne.lng() - sw.lng();
                        let lngFraction = ((lngDiff < 0) ? (lngDiff + 360) : lngDiff) / 360;

                        let latZoom = zoom(mapDim.height, WORLD_DIM.height, latFraction);
                        let lngZoom = zoom(mapDim.width, WORLD_DIM.width, lngFraction);

                        return Math.min(latZoom, lngZoom, ZOOM_MAX);
                    }
                });

            </script>
            <script
                src="https://maps.googleapis.com/maps/api/js?key=<?php echo esc_attr( dt_get_option( 'map_key' ) ); ?>">
            </script>

            <?php
        } // endif $pagenow match
    }

    public function geocode_metabox() {
        global $post;
        $post_meta = get_post_meta( $post->ID );

        echo '<input type="hidden" name="dt_locations_noonce" id="dt_locations_noonce" value="' . esc_attr( wp_create_nonce( 'update_location_info' ) ) . '" />';
        ?>
        <table class="widefat striped">
            <tr>
                <td><label for="location_address">Address: </label></td>
                <td><input type="text" id="location_address" name="location_address"
                           value="<?php isset( $post_meta['location_address'][0] ) ? print esc_attr( $post_meta['location_address'][0] ) : print esc_attr( '' ); ?>"
                           required/>
                </td>
            </tr>
<!--            <tr>-->
<!--                <td><label for="parent_location">Parent Location: </label></td>-->
<!--                <td><select name="parent_location" id="parent_location">-->
<!--                        <option>Select</option>-->
<!--                        --><?php
//                        $results = new WP_Query( [ 'post_type' => 'locations', 'orderby' => 'post_title', 'order' => 'ASC' ] );
//                        foreach ( $results->posts as $result ) {
//                            echo '<option value="' . $result->ID . '">' . $result->post_title . '</option>';
//                        }
//                        ?>
<!--                    </select>-->
<!--                </td>-->
<!--            </tr>-->
            <tr>
                <td></td>
                <td><button type="submit" class="button small right">Update</button></td>
            </tr>

        </table>

        <?php
    }

}
