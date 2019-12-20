<?php
if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly.

/**
 * Disciple Tools Post Type Class
 * All functionality pertaining to post types in Disciple_Tools.
 *
 * @package    Disciple_Tools
 * @author     Chasm.Solutions & Kingdom.Training
 * @since      0.1.0
 */
class Disciple_Tools_People_Groups_Post_Type
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
     * Disciple_Tools_People_Groups_Post_Type The single instance of Disciple_Tools_People_Groups_Post_Type.
     *
     * @var    object
     * @access private
     * @since  0.1.0
     */
    private static $_instance = null;

    /**
     * Main Disciple_Tools_People_Groups_Post_Type Instance
     * Ensures only one instance of Disciple_Tools_People_Groups_Post_Type is loaded or can be loaded.
     *
     * @since  0.1.0
     * @static
     * @return Disciple_Tools_People_Groups_Post_Type instance
     */
    public static function instance() {
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
    public function __construct() {
        $this->post_type = 'peoplegroups';
        $this->singular = __( 'People Group', 'disciple_tools' );
        $this->plural = __( 'People Groups', 'disciple_tools' );
        $this->args = [ 'menu_icon' => 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIyNCIgaGVpZ2h0PSIyNCIgdmlld0JveD0iMCAwIDI0IDI0Ij48ZyBjbGFzcz0ibmMtaWNvbi13cmFwcGVyIiBmaWxsPSIjZmZmZmZmIj48cGF0aCBkYXRhLWNvbG9yPSJjb2xvci0yIiBmaWxsPSIjZmZmZmZmIiBkPSJNMTIsMEM5LjU0MiwwLDcsMS44MDIsNyw0LjgxN2MwLDIuNzE2LDMuODY5LDYuNDg2LDQuMzEsNi45MDdMMTIsMTIuMzgybDAuNjktMC42NTkgQzEzLjEzMSwxMS4zMDMsMTcsNy41MzMsMTcsNC44MTdDMTcsMS44MDIsMTQuNDU4LDAsMTIsMHogTTEyLDdjLTEuMTA1LDAtMi0wLjg5Ni0yLTJjMC0xLjEwNSwwLjg5NS0yLDItMnMyLDAuODk1LDIsMiBDMTQsNi4xMDQsMTMuMTA1LDcsMTIsN3oiPjwvcGF0aD4gPHBhdGggZmlsbD0iI2ZmZmZmZiIgZD0iTTkuODg0LDE5LjQ5OUM5LjAyMywxOC44MTUsNy41NjMsMTgsNS41LDE4cy0zLjUyMywwLjgxNS00LjM4MywxLjQ5OEMwLjQwNywyMC4wNjEsMCwyMC45MTMsMCwyMS44MzZWMjRoMTEgdi0yLjE2NEMxMSwyMC45MTMsMTAuNTkzLDIwLjA2MSw5Ljg4NCwxOS40OTl6Ij48L3BhdGg+IDxjaXJjbGUgZmlsbD0iI2ZmZmZmZiIgY3g9IjUuNSIgY3k9IjEzLjUiIHI9IjMuNSI+PC9jaXJjbGU+IDxwYXRoIGZpbGw9IiNmZmZmZmYiIGQ9Ik0yMi44ODQsMTkuNDk5QzIyLjAyMywxOC44MTUsMjAuNTYzLDE4LDE4LjUsMThzLTMuNTIzLDAuODE1LTQuMzgzLDEuNDk4IEMxMy40MDcsMjAuMDYxLDEzLDIwLjkxMywxMywyMS44MzZWMjRoMTF2LTIuMTY0QzI0LDIwLjkxMywyMy41OTMsMjAuMDYxLDIyLjg4NCwxOS40OTl6Ij48L3BhdGg+IDxjaXJjbGUgZmlsbD0iI2ZmZmZmZiIgY3g9IjE4LjUiIGN5PSIxMy41IiByPSIzLjUiPjwvY2lyY2xlPjwvZz48L3N2Zz4=' ];

        add_action( 'init', [ $this, 'register_post_type' ] );

        if ( is_admin() ) {
            global $pagenow;

            add_action( 'admin_menu', [ $this, 'meta_box_setup' ], 20 );
            add_action( 'save_post', [ $this, 'meta_box_save' ] );
            add_filter( 'enter_title_here', [ $this, 'enter_title_here' ] );
//            add_filter( 'post_updated_messages', [ $this, 'updated_messages' ] );

            if ( $pagenow == 'edit.php' && isset( $_GET['post_type'] ) ) {
                $pt = sanitize_text_field( wp_unslash( $_GET['post_type'] ) );
                if ( $pt == $this->post_type ) {
                    add_filter( 'manage_edit-' . $this->post_type . '_columns', [ $this, 'register_custom_column_headings' ], 10, 1 );
                    add_action( 'manage_posts_custom_column', [ $this, 'register_custom_columns' ], 10, 2 );
                }
            }

//            add_action( 'admin_init', [ $this, 'remove_add_new_submenu' ] );
        }
    }

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
            'search_items'          => sprintf( _x( "Search %s", "Search 'something'", 'disciple_tools' ), $this->plural ),
        ];

        $rewrite = [
            'slug'       => 'peoplegroups',
            'with_front' => true,
            'pages'      => true,
            'feeds'      => false,
        ];
        $capabilities = [
            'edit_post'           => 'edit_peoplegroup',
            'read_post'           => 'read_peoplegroup',
            'delete_post'         => 'delete_peoplegroup',
            'delete_others_posts' => 'delete_others_peoplegroups',
            'delete_posts'        => 'delete_peoplegroups',
            'edit_posts'          => 'edit_peoplegroups',
            'edit_others_posts'   => 'edit_others_peoplegroups',
            'publish_posts'       => 'publish_peoplegroups',
            'read_private_posts'  => 'read_private_peoplegroups',
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
            'hierarchical'          => false,
            'supports'              => [ 'title' ],
            'menu_position'         => 6,
            'menu_icon'             => 'dashicons-smiley',
            'show_in_rest'          => true,
            'rest_base'             => 'peoplegroups',
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
    public function register_custom_columns( $column_name ) {
        //        global $post;

        switch ( $column_name ) {
            case 'image':
                break;

            default:
                break;
        }
    }

    /**
     * Add custom column headings for the "manage" screen of this post type.
     *
     * @access public
     *
     * @param  array $defaults
     *
     * @since  0.1.0
     * @return array
     */
    public function register_custom_column_headings( $defaults ) {
        //      $new_columns = array( 'image' => __( 'Image', 'disciple_tools' ) );
        $new_columns = []; // TODO: restore above column once we know what columns we need to show.

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
//    public function updated_messages( $messages ) {
//        global $post;
//
//        $link = '<a target="_blank" href="' . esc_url( get_permalink( $post->ID ) ) .'">' .  __( 'View', 'disciple_tools' ) . '</a>';
//
//        $messages[ $this->post_type ] = [
//            0  => '', // Unused. Messages start at index 1.
//            1  => sprintf( __( '%s updated.', 'disciple_tools' ), $this->singular ) ,
//            2  => sprintf( __( '%s updated.', 'disciple_tools' ), $this->singular ),
//            3  => sprintf( __( '%s deleted.', 'disciple_tools' ), $this->singular ),
//            4  => sprintf( __( '%s updated.', 'disciple_tools' ), $this->singular ),
//            /* translators: %s: date and time of the revision */
//            5  => isset( $_GET['revision'] ) ? sprintf( __( '%1$s restored to revision from %2$s', 'disciple_tools' ), $this->singular, wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
//            6  => sprintf( __( '%s published.', 'disciple_tools' ), $this->singular ) ,
//            7  => sprintf( __( '%s saved.', 'disciple_tools' ), $this->singular ),
//            8  => sprintf( __( '%s submitted.', 'disciple_tools' ), $this->singular ) ,
//            9  => sprintf(
//                __( '%1$s scheduled for: %2$s.', 'disciple_tools' ),
//                $this->singular,
//                '<strong>' . date_i18n( _x( 'M j, Y @ G:i', 'Publish box date format, see http://php.net/date', 'disciple_tools' ), strtotime( $post->post_date ) ) . '</strong>'
//            ) ,
//            10  => sprintf( __( '%s draft updated.', 'disciple_tools' ), $this->singular ) ,
//        ];
//
//        return $messages;
//    } // End updated_messages()

    /**
     * Setup the meta box.
     *
     * @access public
     * @since  0.1.0
     * @return void
     */
    public function meta_box_setup() {
          add_meta_box( $this->post_type . '_update', __( 'Add/Update People Group', 'disciple_tools' ), [ $this, 'load_add_update_meta_box' ], $this->post_type, 'normal', 'high' );
          add_meta_box( $this->post_type . '_data', __( 'People Group Details', 'disciple_tools' ), [ $this, 'load_details_meta_box' ], $this->post_type, 'normal', 'high' );
          add_meta_box( $this->post_type . '_translate', __( 'Translations', 'disciple_tools' ), [ $this, 'load_translation_meta_box' ], $this->post_type, 'side', 'low' );
    } // End meta_box_setup()

    public function load_add_update_meta_box( $post ) {
        $names = Disciple_Tools_People_Groups::get_country_dropdown(); // @todo throwing error.
        ?>
        <input type="hidden" id="post_id" value="<?php echo esc_attr( $post->ID ) ?>" />

        Search by either country or ROP3 code:<br>
        <table class="widefat">
            <tr>
                <td width="33%"><label for="country">Country </label><br>
                    <select id="country">
                        <option></option>
                        <?php foreach ( $names as $name ) :
                            echo '<option value="'.esc_attr( $name ).'">'.esc_attr( $name ).'</option>';
                        endforeach; ?>
                    </select>
                </td>
                <td width="33%" style="text-align:center;"><br>or</td>
                <td width="33%">
                    <label for="rop3">ROP3 Code</label>
                    <br>
                    <input type="text" name="rop3" id="rop3" class="text-input" />
                </td>
            </tr>
            <tr>
                <td></td>
                <td style="text-align:center;"><button type="button" class="button" id="search_button" onclick="link_search()">Search</button></td>
                <td></td>
            </tr>
        </table>
        <br>
        <div id="results"></div>


        <?php
    }

    /**
     * Load activity metabox
     */
    public function load_details_meta_box() {
        global $wpdb, $post;
        $results = $wpdb->get_results( $wpdb->prepare( "
            SELECT meta_key, meta_value
            FROM $wpdb->postmeta
            WHERE post_id = %s
            AND (
            meta_key LIKE %s
            OR meta_key LIKE %s
            ) ",
            $post->ID,
            $wpdb->esc_like( 'jp_' ). '%',
            $wpdb->esc_like( 'imb_' ). '%'
        ), ARRAY_A );

        if ( ! empty( $results ) ) {
            $record = [];
            foreach ( $results as $item ) {
                $record[$item['meta_key']] = $item['meta_value'];
            }
            ksort( $record );

            if ( isset( $record['jp_ROP3'] ) ) {
                echo '<table class="widefat striped">';
                echo '<tr><td colspan="2"><h3>JOSHUA PROJECT - ('.esc_attr( $record['jp_PeopNameAcrossCountries'] ).')</h3></td><td></td></tr>';
                foreach ( $record as $key => $value ) {
                    if ( substr( $key, 0, 3 ) == 'jp_' ) {
                        echo '<tr><td>' . esc_attr( substr( $key, 3 ) ) . '</td><td>' . esc_attr( $value ) . '</td></tr>';
                    }
                }
                echo '</table>';
            }


            if ( isset( $record['imb_ROP3'] ) ) {
                echo "<br>";
                echo '<table class="widefat striped">';
                echo '<tr><td><h3>IMB - (' . esc_attr( $record['imb_People Name'] ) . ')</h3></td><td></td></tr>';
                foreach ( $record as $key => $value ) {
                    if ( substr( $key, 0, 4 ) == 'imb_' ) {
                        echo '<tr><td>' . esc_attr( substr( $key, 4 ) ) . '</td><td>' . esc_attr( $value ) . '</td></tr>';
                    }
                }
                echo '</table>';
            }
        }
    }

     /**
     * Load translation metabox
     */
    public function load_translation_meta_box() {
        global $post;
        $dt_available_languages = dt_get_available_languages();

        echo '<input type="hidden" name="dt_' . esc_attr( $this->post_type ) . '_noonce" id="dt_' . esc_attr( $this->post_type ) . '_noonce" value="' . esc_attr( wp_create_nonce( 'update_peoplegroup_info' ) ) . '" />';
        ?>
        <?php foreach ($dt_available_languages as $language) {
            echo '<label for="dt_translation_' . esc_attr( $language["language"] ) . '">' . esc_attr( $language["native_name"] ) . '</label><br/>';
            echo '<input type="text" name="dt_translation_' . esc_attr( $language["language"] ) . '" id="dt_translation_' . esc_attr( $language["language"] ) . '" class="text-input" value="' . esc_attr( get_post_meta( $post->ID, $language["language"], true ) ). '" /><br/>';
        }
    }
    /**
     * Load activity metabox
     */
    public function load_jp_meta_box() {
        global $wpdb, $post;

        echo '<table class="widefat striped"><tbody>';
        $jp_results = $wpdb->get_results( $wpdb->prepare(
            "SELECT
                meta_key, meta_value
            FROM
                `$wpdb->postmeta`
             WHERE
                post_id = %s
                AND meta_key LIKE %s ",
            $post->ID,
            $wpdb->esc_like( 'jp_' ) . '%'
        ) );
        foreach ( $jp_results as $value ) {
            echo '<tr><td style="max-width: 150px">' . esc_html( substr( $value->meta_key, 3 ) ) . '</td><td style="max-width: 150px">' . esc_html( $value->meta_value ) . '</td></tr>';
        }
        echo '</tbody></table>';
    }

    /**
     * The contents of our meta box.
     *
     * @param string $section
     */
    public function meta_box_content( $section = 'info' ) {
        global $post_id;
        $fields = get_post_custom( $post_id );
        $field_data = $this->get_custom_fields_settings();

        echo '<input type="hidden" name="dt_' . esc_attr( $this->post_type ) . '_noonce" id="dt_' . esc_attr( $this->post_type ) . '_noonce" value="' . esc_attr( wp_create_nonce( 'update_peoplegroup_info' ) ) . '" />';

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
                                echo '>' . esc_attr( $vv ) . '</option>';
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
    public function meta_box_save( $post_id ) {
        // Verify
        $key = 'dt_' . $this->post_type . '_noonce';
        if ( ( get_post_type() != $this->post_type ) || !isset( $_POST[ $key ] ) || !wp_verify_nonce( sanitize_key( $_POST[ $key ] ), 'update_peoplegroup_info' ) ) {
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
            $details = [
            'type' => $type,
            'verified' => false
            ];
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

        $dt_available_languages = Disciple_Tools_Core_Endpoints::get_settings();

        foreach ($dt_available_languages["available_translations"] as $language) {
            if ( isset( $_POST['dt_translation_' . $language["language"]] ) )
            {
                $translated_text_value = sanitize_text_field( wp_unslash( $_POST['dt_translation_' . $language["language"]] ) );
                update_post_meta( $post_id, $language["language"], $translated_text_value );
            }
        }

        return $post_id;
    } // End meta_box_save()

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
            $title = __( 'Enter the People Group title here', 'disciple_tools' );
        }

        return $title;
    } // End enter_title_here()

    /**
     * Get the settings for the custom fields.
     *
     * @access public
     * @since  0.1.0
     * @return array
     */
    public function get_custom_fields_settings() {
        //        global $post;
        $fields = [];

        //        /* Sample */
        //        $fields['overall_status'] = [
        //            'name' => __( 'Overall Status', 'disciple_tools' ),
        //            'description' => '',
        //            'type' => 'key_select',
        //            'default' => ['0' => __( 'Unassigned', 'disciple_tools' ), '1' => __( 'Accepted', 'disciple_tools' ), '2' => __( 'Paused', 'disciple_tools' ), '3' => __( 'Closed', 'disciple_tools' ), '4' => __( 'Unassignable', 'disciple_tools' ) ],
        //            'section' => 'status'
        //        ];

        return apply_filters( 'dt_custom_fields_settings', $fields, "people_groups" );
    }

    /**
     * Field: People Group Fields
     *
     * @return array
     */
    public function people_group_fields() {
        global $wpdb, $post;
        $fields = [];
        $current_fields = [];
        if ( isset( $post->ID ) ) {
            $current_fields = $wpdb->get_results( $wpdb->prepare(
                "SELECT
                    meta_key
                FROM
                    `$wpdb->postmeta`
                WHERE
                    post_id = %s
                    AND meta_key LIKE %s
                ORDER BY
                    meta_key DESC",
                $post->ID,
                $wpdb->esc_like( 'contact_' ) . '%'
            ), ARRAY_A );
        }
        foreach ( $current_fields as $value ) {
            $names = explode( '_', $value['meta_key'] );
            $tag = null;
            if ( $names[1] != $names[2] ) {
                $tag = ' (' . ucwords( $names[2] ) . ')';
            }
            $fields[ $value['meta_key'] ] = [
                'name' => ucwords( $names[1] ) . $tag,
                'tag'  => $names[1],
            ];
        }

        return $fields;
    }

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
     * Remove the add new submenu from the locaions menu
     */
    public function remove_add_new_submenu() {
        global $submenu;
        unset(
            $submenu['edit.php?post_type=peoplegroups'][10]
        );
    }

} // End Class
