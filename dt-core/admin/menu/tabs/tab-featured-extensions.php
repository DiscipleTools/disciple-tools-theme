<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class Disciple_Tools_General_Tab
 */
class Disciple_Tools_Tab_Featured_Extensions extends Disciple_Tools_Abstract_Menu_Base
{
    private static $_instance = null;

    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct() {
        add_action( 'admin_enqueue_scripts', [ $this, 'admin_enqueue_scripts' ], 9, 1 );
        add_action( 'dt_extensions_tab_content', [ $this, 'content' ], 99, 1 );
        parent::__construct();
    }

    public function admin_enqueue_scripts() {
        dt_theme_enqueue_script( 'dt-extensions', 'dt-core/admin/js/dt-extensions.js', [], true );
        dt_theme_enqueue_script( 'typeahead-jquery', 'dt-core/dependencies/typeahead/dist/jquery.typeahead.min.js', array( 'jquery' ), true );
        wp_localize_script(
            'dt-extensions', 'plugins', array(
                'all_plugins' => self::get_dt_plugins(),
            )
        );
    }

    public function content( $tab ) {
        ?>
        <style>
            #the-list {
                display: flex;
                flex-wrap: wrap;
            }
            .extension-buttons {
                display: content;
            }
            .plugin-author-img {
                height: 28px;
                width: 28px;
                border: 1px solid #d0d7de;
                border-radius: 100px;
                vertical-align: bottom;
                margin-left: 6px;
            }
            .warning-pill{
                background-color: #ffae00;
                color: black;
                font-size: .9em;
                pointer-events: none;
                border-radius: 3px;
                text-decoration: none;
                margin: 1rem 0 0 1rem;
                padding: .5em .5em;
                text-align: center;
            }
            .flip-card {
                transform: rotateY(180deg);
            }
            .filter-links {
                display: flex;
                justify-content: space-evenly;
                align-items: baseline;
                flex-wrap: wrap;
                margin: -1px;
            }
            .card-front, .card-back {
                -webkit-backface-visibility: hidden;
                backface-visibility: hidden;
            }
            .card-back {
                transform: rotateY(180deg);
            }
            .plugin-card-content-back {
                margin-top: 58%;
            }
            .plugin-card {
                display: flex;
                height: 275px;
                flex-wrap: wrap;
                justify-content: center;
                align-items: flex-start;
                position: relative;
                transition: transform 0.5s;
                transform-style: preserve-3d;
            }
            .plugin-card-top {
                position: absolute;
            }
            .loading {
                background: url('images/spinner.gif') no-repeat;
                background-size: 20px 20px;
                opacity: .7;
                width: 20px;
                height: 20px;
                display: block;
                margin: 10px auto;
            }
            .dt-typeahead-extensions {
                width: 100%;
                padding: 0.5rem 0.75rem;
                border: 1.5px solid #ccc;
                border-radius: 2px 0 0 2px;
                appearance: none;
                box-sizing: border-box;
                overflow: visible;
                padding-right: 32px;
                margin-bottom: 12px;
            }
            #no-typeahead-results {
                width:100%;
                text-align: center;
                display: none;
            }
            .typeahead__result {
                display: none;
            }
        </style>
        <?php
        // begin columns template
        $this->template( 'begin' );

        $this->box_message( $tab );

        // begin right column template
        $this->template( 'right_column' );

        // end columns template
        $this->template( 'end' );
    }

    //main page
    public function box_message( $tab ) {
        // //check for actions
        // if ( isset( $_POST['activate'] ) && is_admin() && isset( $_POST['_ajax_nonce'] ) && check_ajax_referer( 'portal-nonce', sanitize_key( $_POST['_ajax_nonce'] ) ) && current_user_can( 'manage_dt' ) ) {
        //     //activate the plugin
        //     activate_plugin( sanitize_text_field( wp_unslash( $_POST['activate'] ) ) );
        //     exit;
        // }
        // elseif ( isset( $_POST['install'] ) && is_admin() && isset( $_POST['_ajax_nonce'] )
        //     && check_ajax_referer( 'portal-nonce', sanitize_key( $_POST['_ajax_nonce'] ) )
        //     && ( ( is_multisite() && is_super_admin() ) || ( ! is_multisite() && current_user_can( 'manage_dt' ) ) ) ) {
        //     //check for admin or multisite super admin
        //     //install plugin
        //     $this->install_plugin( sanitize_text_field( wp_unslash( $_POST['install'] ) ) );
        //     exit;
        // }

        // elseif ( isset( $_POST['uninstall'] ) && is_admin() && isset( $_POST['_ajax_nonce'] )
        //     && check_ajax_referer( 'portal-nonce', sanitize_key( $_POST['_ajax_nonce'] ) )
        //     && ( ( is_multisite() && is_super_admin() ) || ( ! is_multisite() && current_user_can( 'manage_dt' ) ) ) ) {
        //     //check for admin or multisite super admin
        //     //uninstall plugin
        //     delete_plugins( [ sanitize_text_field( wp_unslash( $_POST['uninstall'] ) ) ] );
        //     exit;
        // }

        // elseif ( isset( $_POST['deactivate'] ) && is_admin() && isset( $_POST['_ajax_nonce'] ) && check_ajax_referer( 'portal-nonce', sanitize_key( $_POST['_ajax_nonce'] ) ) && current_user_can( 'manage_dt' ) ) {
        //     //deactivate the plugin
        //     deactivate_plugins( sanitize_text_field( wp_unslash( $_POST['deactivate'] ) ), true );
        //     exit;
        // }

        $network_active_plugins = get_site_option( 'active_sitewide_plugins', [] );
        $active_plugins = get_option( 'active_plugins', [] );
        foreach ( $network_active_plugins as $plugin => $time ){
            $active_plugins[] = $plugin;
        }


        //get plugin data
        $plugins = $this->get_dt_plugins();
        // Page content goes here

        // Assign the 'current' class to the selected tab
            $class_current_tab = '';
        ?>
        <div>
            <p>Plugins are ways of extending the Disciple.Tools system to meet the unique needs of your project, ministry, or movement.</p>
        </div>
        <div class="wp-filter">
            <ul class="filter-links">
                <?php
                $all_plugin_categories = $this->get_all_plugin_categories();
                foreach ( $all_plugin_categories as $plugin_category ) : ?>
                    <li class="plugin-install">
                        <a href="javascript:void(0);" data-category="<?php echo esc_attr( str_replace( ' ', '-', $plugin_category ) ); ?>"><?php echo esc_html( ucwords( $plugin_category ) ); ?></a>
                    </li>
                <?php endforeach; ?>
                <li>
                    <div class="typeahead-div">
                        <form id="form-field_settings_search" name="form-field_settings_search">
                            <div class="typeahead__container">
                                <div class="typeahead__field">
                                    <div class="typeahead__query">
                                        <span class="typeahead__query">
                                            <input id="settings[query]" name="settings[query]" class="js-typeahead-extensions dt-typeahead-extensions" autocomplete="off" placeholder="<?php esc_attr_e( 'Search plugins', 'disciple_tools' ); ?>">
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </li>
            </ul>
        </div>
        <div id="the-list">
            <div id="no-typeahead-results">
                <em>
                    No plugins found.
                    <br>
                    Please try another search term.
                </em>
            </div>
        </div>
        <?php
    }

    //checks for a partial string in an array
    public function partial_array_search( $array, $find ) {
        //check for null value
        if ( $find == null || !$array || sizeof( $array ) == 0 ) {
            return -1;
        }
        //with array keys
        foreach ( array_keys( $array ) as $key ) {
            $key = ' ' . $key; //for rare bug
            if ( strpos( $key, $find ) !== false ) {
                return $key;
            }
        }
        //with array values
        foreach ( $array as $key ) {
            if ( gettype( $key ) != 'array' ) {
                $key = ' ' . $key;
                if ( strpos( $key, $find . '/' ) !== false ) {
                    return $key;
                } elseif ( strpos( $key, $find . '.php' ) !== false ){
                    return $key;
                }
            }
        }
        //false
        return -1;
    }

    public function install_plugin( $download_url ) {
        set_time_limit( 0 );
        $folder_name = explode( '/', $download_url );
        $folder_name = get_home_path() . 'wp-content/plugins/' . $folder_name[4] . '.zip';
        if ( $folder_name != '' ) {
            //download the zip file to plugins
            file_put_contents( $folder_name, file_get_contents( $download_url ) );
            // get the absolute path to $file
            $folder_name = realpath( $folder_name );
            //unzip
            WP_Filesystem();
            $unzip = unzip_file( $folder_name, realpath( get_home_path() . 'wp-content/plugins/' ) );
            //remove the file
            unlink( $folder_name );
        }
    }

    public function get_all_plugin_categories() {
        $plugins = json_decode( trim( file_get_contents( 'https://disciple.tools/wp-content/themes/disciple-tools-public-site/plugin-feed.php' ) ) );
        $distinct_categories = [ 'featured', 'all plugins' ];
        foreach ( $plugins as $plugin ) {
            $plugin_categories = explode( ',', $plugin->categories ?? false );
            foreach ( $plugin_categories as $plug_cat ) {
                if ( ! in_array( str_replace( '-', ' ', $plug_cat ), $distinct_categories ) ) {
                    $distinct_categories[] = str_replace( '-', ' ', $plug_cat );
                }
            }
        }
        return $distinct_categories;
    }

    public function get_dt_plugins() {
        $all_plugins = get_plugins();
        $plugins = get_transient( 'dt_plugins_feed' );
        if ( empty( $plugins ) ){
            $plugins = json_decode( trim( file_get_contents( 'https://disciple.tools/wp-content/themes/disciple-tools-public-site/plugin-feed.php' ) ) );
            set_transient( 'dt_plugins_feed', $plugins, HOUR_IN_SECONDS );
        }

        $network_active_plugins = get_site_option( 'active_sitewide_plugins', [] );
        $active_plugins = get_option( 'active_plugins', [] );

        foreach ( $network_active_plugins as $plugin => $time ){
            $active_plugins[] = $plugin;
        }

        foreach ( $plugins as $plugin ) {
            $plugin->slug = explode( '/', $plugin->homepage )[4];
            $plugin->blog_url = 'https://disciple.tools/plugins/' . $plugin->slug;
            $plugin->folder_name = get_home_path() . 'wp-content/plugins/' . $plugin->slug;
            $plugin->author_github_username = explode( '/', $plugin->homepage )[3];
            $plugin->description = count_chars( $plugin->description ) > 128 ? trim( substr( $plugin->description, 0, 128 ) ) . '...' : $plugin->description; // Shorten descriptions to 88 chars
            $plugin->icon = ! isset( $plugin->icon ) ? 'https://s.w.org/plugins/geopattern-icon/' . $plugin->slug . '.svg' : $plugin->icon;
            $plugin->name = str_replace( 'Disciple Tools - ', '', $plugin->name );
            $plugin->name = str_replace( 'Disciple.Tools - ', '', $plugin->name );
            $plugin->installed = false;
            $plugin->active = false;
            $plugin->activation_path = '';

            if ( $this->partial_array_search( $all_plugins, $plugin->slug ) !== -1 ) {
                $plugin->installed = true;
            }

            if ( $this->partial_array_search( $active_plugins, $plugin->slug ) !== -1 ) {
                $plugin->active = true;
                $plugin->activation_path = $this->partial_array_search( $active_plugins, $plugin->slug );
            }
        }
        return $plugins;
    }
}

Disciple_Tools_Tab_Featured_Extensions::instance();
