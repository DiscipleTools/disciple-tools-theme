<?php
/**
 * Disciple Tools Author Metabox Extension
 * Description: This is a modified version of the Better Author Metabox Plugin. Customized to specifically support
 * the Disciple Tools post-types and configurations.
 *
 * @original BetterAuthorMetabox Plugin
 * @original Author: Joe Chellman
 * @original Author URI: http://www.shooflydesign.org/
 * @original Version: 1.0.2
 * @original Plugin URI: https://wordpress.org/plugins-wp/better-author-metabox/
 * @original GNUv2, or later
 * @package  Disciple Tools
 * @class    Disciple_Tools_BetterAuthorMetabox
 * TODO: Clean un-necessary settings panel and define authors for contacts and groups. And determine if securing pages by author, or assigned_to is better.
 */

if( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

/**
 * Class Disciple_Tools_BetterAuthorMetabox
 */
class Disciple_Tools_BetterAuthorMetabox
{

    private $options;

    protected $config = 'ba-metabox-config';

    /**
     * Disciple_Tools_BetterAuthorMetabox The single instance of Disciple_Tools_BetterAuthorMetabox.
     *
     * @var    object
     * @access private
     * @since  0.1.0
     */
    private static $_instance = null;

    /**
     * Main Disciple_Tools_BetterAuthorMetabox Instance
     * Ensures only one instance of Disciple_Tools_BetterAuthorMetabox is loaded or can be loaded.
     *
     * @since  0.1.0
     * @static
     * @return Disciple_Tools_BetterAuthorMetabox
     */
    public static function instance()
    {
        if( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }

        return self::$_instance;
    } // End instance()

    /**
     * Disciple_Tools_BetterAuthorMetabox constructor.
     */
    public function __construct()
    {
        $this->plugin_dir = plugin_dir_path( __FILE__ );

        // call all filters and actions
        // add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
        add_action( 'admin_menu', [ $this, 'reset_author_metabox' ] );
        add_action( 'admin_init', [ $this, 'init_options' ] );
    }

    /**
     * Adds the settings page
     */
    public function add_settings_page()
    {
        add_options_page(
            __( 'Author Metabox Settings', 'disciple_tools' ),
            __( 'Author Metabox', 'disciple_tools' ),
            'manage_options',
            $this->config,
            [ $this, 'display_options_page' ]
        );
    }

    /**
     * Changes the Author metabox so it display all users for the post types where we want this.
     */
    public function reset_author_metabox()
    {
        // don't do anything if the user can't edit others posts.
        if( !current_user_can( 'edit_others_posts' ) ) {
            return;
        }

        $options = get_option( 'BAM_config' );

        // no options (yet) - forget it!
        if( !$options || !$options[ 'enabled_post_types' ] ) {
            return;
        }

        foreach( $options[ 'enabled_post_types' ] as $post_type => $val ) {
            if( $val == 1 ) {
                remove_meta_box( 'authordiv', $post_type, 'normal' );
                add_meta_box( 'authordiv', __( 'Record Owner' ), [ $this, 'post_author_meta_box' ], $post_type );
            }
        }
    }

    /**
     * Very similar to core post_author_meta_box function, except this version always returns all users
     *
     * @param $post - the post being edited
     */
    public function post_author_meta_box( $post )
    {
        global $user_ID;
        ?>
        <label class="screen-reader-text" for="post_author_override"><?php _e( 'Author' ); ?></label>
        <?php
        $this->wp_dropdown_users(
            [
                'name'             => 'post_author_override',
                'selected'         => empty( $post->ID ) ? $user_ID : $post->post_author,
                'include_selected' => true,
            ]
        );
    }

    /**
     * Custom version of wp_dropdown_users that will query users by multiple roles
     *
     * @param  string $args
     *
     * @return mixed|string
     */
    protected function wp_dropdown_users( $args = '' )
    {
        $defaults = [
            'show_option_all'   => '', 'show_option_none' => '', 'hide_if_only_one_author' => '',
            'orderby'           => 'display_name', 'order' => 'ASC',
            'include'           => '', 'exclude' => '', 'multi' => 0,
            'show'              => 'display_name', 'echo' => 1,
            'selected'          => 0, 'name' => 'user', 'class' => '', 'id' => '',
            'blog_id'           => $GLOBALS[ 'blog_id' ], 'include_selected' => false,
            'option_none_value' => -1,
        ];

        $defaults[ 'selected' ] = is_author() ? get_query_var( 'author' ) : 0;

        $r = wp_parse_args( $args, $defaults );
        $show = $r[ 'show' ];
        $show_option_all = $r[ 'show_option_all' ];
        $show_option_none = $r[ 'show_option_none' ];
        $option_none_value = $r[ 'option_none_value' ];

        $query_args = wp_array_slice_assoc( $r, [ 'blog_id', 'include', 'exclude', 'orderby', 'order' ] );
        $query_args[ 'fields' ] = [ 'ID', 'user_login', $show ];

        $users = [];

        $options = get_option( 'BAM_config' );

        // if roles have been selected, use them
        if( count( $options[ 'enabled_roles' ] ) ) {
            foreach( $options[ 'enabled_roles' ] as $role => $enabled ) {
                if( $enabled == 1 ) {
                    $query_args[ 'role' ] = $role;
                    $role_users = get_users( $query_args );
                    $users = array_merge( $role_users, $users );
                }
            }
            // if no roles have been selected, use the default of authors
        } else {
            $query_args[ 'who' ] = 'authors';
            $users = get_users( $query_args );
        }

        $output = '';
        if( !empty( $users ) && ( empty( $r[ 'hide_if_only_one_author' ] ) || count( $users ) > 1 ) ) {
            $name = esc_attr( $r[ 'name' ] );
            if( $r[ 'multi' ] && !$r[ 'id' ] ) {
                $id = '';
            } else {
                $id = $r[ 'id' ] ? " id='" . esc_attr( $r[ 'id' ] ) . "'" : " id='$name'";
            }
            $output = "<select name='{$name}'{$id} class='" . $r[ 'class' ] . "'>\n";

            if( $show_option_all ) {
                $output .= "\t<option value='0'>$show_option_all</option>\n";
            }

            if( $show_option_none ) {
                $_selected = selected( $option_none_value, $r[ 'selected' ], false );
                $output .= "\t<option value='" . esc_attr( $option_none_value ) . "'$_selected>$show_option_none</option>\n";
            }

            $found_selected = false;
            foreach( (array) $users as $user ) {
                $user->ID = (int) $user->ID;
                $_selected = selected( $user->ID, $r[ 'selected' ], false );
                if( $_selected ) {
                    $found_selected = true;
                }
                $display = !empty( $user->$show ) ? $user->$show : '(' . $user->user_login . ')';
                $output .= "\t<option value='$user->ID'$_selected>" . esc_html( $display ) . "</option>\n";
            }

            if( $r[ 'include_selected' ] && !$found_selected && ( $r[ 'selected' ] > 0 ) ) {
                $user = get_userdata( $r[ 'selected' ] );
                $_selected = selected( $user->ID, $r[ 'selected' ], false );
                $display = !empty( $user->$show ) ? $user->$show : '(' . $user->user_login . ')';
                $output .= "\t<option value='$user->ID'$_selected>" . esc_html( $display ) . "</option>\n";
            }

            $output .= "</select>";
        }

        // different filter for the output of this version, so we don't mess up core
        $html = apply_filters( 'bam_wp_dropdown_users', $output );

        if( $r[ 'echo' ] ) {
            echo $html;
        }

        return $html;
    }

    /**
     * Initializes plugin options
     */
    public function init_options()
    {
        register_setting(
            $this->config,
            'BAM_config',
            [ $this, 'sanitize_options' ]
        );

        add_settings_section(
            'BAM_config_main',
            '',
            function() {
                print '';
            },
            $this->config
        );

        add_settings_field(
            'enabled_post_types',
            __( 'Enabled Post Types', 'disciple_tools' ),
            [ $this, 'setting_post_types' ], // Callback
            $this->config, // Page
            'BAM_config_main' // Section
        );

        add_settings_field(
            'enabled_roles',
            __( 'Enabled Roles', 'disciple_tools' ),
            [ $this, 'setting_enabled_roles' ], // Callback
            $this->config, // Page
            'BAM_config_main' // Section
        );
    }

    /**
     * Sanitizes the data from the options page
     *
     * @param  $input - the incoming option data
     *
     * @return array
     */
    public function sanitize_options( $input )
    {
        $safe_input = [ 'enabled_post_types' => [ 'contacts' => 1, 'groups' => 1, 'post' => 1, 'attachment' => 1 ], 'enabled_roles' => [ 'Marketer' => 1, 'Dispatcher' => 1, 'Administrator' => 1 ] ];

        return $safe_input;
    }

    /**
     * Callback to display the plugin options page.
     */
    public function display_options_page()
    {
        // collect defaults
        $this->options = get_option( 'BAM_config' );

        // include($this->plugin_dir . '/options-page.php');
        $html = '<div class="wrap">
        <h2>Author Metabox</h2>
        <form method="post" action="options.php">';

        echo $html;

        settings_fields( $this->config );
        do_settings_sections( $this->config );
        submit_button();

        $html = '</form></div>';
        echo $html;
    }

    /**
     * Callback for display_options_page() and init_options()
     * to display the post types where the box will be overridden
     */
    public function setting_post_types()
    {

        $post_types = get_post_types(
            [
                'show_ui' => true,
            ], 'objects'
        );

        foreach( $post_types as $p ) {
            $slug = $p->name;
            $label = $p->labels->singular_name . ' <small>(' . $slug . ')</small>';
            $item_id = "post_type_$slug";

            $comp = isset( $this->options[ 'enabled_post_types' ][ $slug ] ) ? $this->options[ 'enabled_post_types' ][ $slug ] : '';
            $checked = checked( 1, $comp, false );

            printf(
                '<label for="%s"><input type="checkbox" id="%s" name="BAM_config[enabled_post_types][%s]" value="1" %s /> %s</label><br />',
                $item_id, $item_id, $slug, $checked, $label
            );
        }

        echo '<p class="description">Enable the expanded Author metabox for these post types.</p>';
    }

    /**
     * Callback for display_options_page() and init_options()
     * to display the roles for users that should be displayed
     */
    public function setting_enabled_roles()
    {
        global $wp_roles;

        foreach( $wp_roles->role_names as $slug => $label ) {
            $item_id = "role_$slug";

            $comp = isset( $this->options[ 'enabled_roles' ][ $slug ] ) ? $this->options[ 'enabled_roles' ][ $slug ] : '';
            $checked = checked( 1, $comp, false );

            printf(
                '<label for="%s"><input type="checkbox" id="%s" name="BAM_config[enabled_roles][%s]" value="1" %s /> %s</label><br />',
                $item_id, $item_id, $slug, $checked, $label
            );
        }

        echo '<p class="description">Show users from these roles in the author metabox.</p>';
    }
}

// Instantiate the plugin
//add_action( 'init', array( 'BetterAuthorMetabox', 'init' ) );
