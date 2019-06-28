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
        add_action( 'dt_extensions_tab_menu', [ $this, 'add_tab' ], 10, 1 ); // use the priority setting to control load order
        add_action( 'dt_extensions_tab_content', [ $this, 'content' ], 99, 1 );

        parent::__construct();
    } // End __construct()

    public function add_tab( $tab ) {
        $nonce = wp_create_nonce( 'portal-nonce' );
        ?>
        <script type="text/javascript">
            function install(plug) {
                jQuery("#wpbody-content").replaceWith("<p><?php esc_html_e( 'installing', 'disciple_tools' ) ?>...</p>");
                jQuery.post("",
                    {
                        install: plug,
                        _ajax_nonce: "<?php echo esc_attr( $nonce ); ?>"

                    },
                    function (data, status) {
                        location.reload();
                    });
            }

            function activate(plug) {
                jQuery("#wpbody-content").replaceWith("<p><?php esc_html_e( 'activating', 'disciple_tools' ) ?>...</p>");
                jQuery.post("",
                    {
                        activate: plug,
                        _ajax_nonce: "<?php echo esc_attr( $nonce ); ?>"
                    },
                    function (data, status) {
                        location.reload();
                    });
            }
        </script>
        <?php
        echo '<a href="' . esc_url( admin_url() ) . 'admin.php?page=dt_extensions&tab=featured-extensions" class="nav-tab ';
        if ( $tab == 'featured-extensions' ) {
            echo 'nav-tab-active';
        }
        echo '">' . esc_attr__( 'Featured Extensions', 'disciple_tools' ) . '</a>';
    }

    public function content( $tab ) {
        if ( 'featured-extensions' == $tab ) {
            // begin columns template
            $this->template( 'begin' );

            $this->box_message();

            // begin right column template
            $this->template( 'right_column' );

            // end columns template
            $this->template( 'end' );
        }
    }

    //main page
    public function box_message() {
        //check for actions
        if ( isset( $_POST["activate"] ) && is_admin() && isset( $_POST["_ajax_nonce"] ) && check_ajax_referer( 'portal-nonce', sanitize_key( $_POST["_ajax_nonce"] ) ) && current_user_can( "manage_dt" ) ) {
            //activate the plugin
            activate_plugin( sanitize_text_field( wp_unslash( $_POST["activate"] ) ) );
            exit;
        } elseif ( isset( $_POST ) && isset( $_POST["install"] ) && is_admin() && isset( $_POST["_ajax_nonce"] )
            && check_ajax_referer( 'portal-nonce', sanitize_key( $_POST["_ajax_nonce"] ) )
            && ( ( is_multisite() && is_super_admin() ) || ( ! is_multisite() && current_user_can( "manage_dt" ) ) ) ) {
            //check for admin or multisite super admin
            //install plugin
            $this->install_plugin( sanitize_text_field( wp_unslash( $_POST["install"] ) ) );
            exit;
        }
        $active_plugins = get_option( 'active_plugins' );
        $all_plugins = get_plugins();
        //get plugin data
        $plugins = $this->get_plugins();
        ?>
        <h3><?php esc_html_e( "Official Disciple.Tools Plugins", 'disciple_tools' ) ?></h3>
        <table class="widefat striped">
            <thead>
            <tr>
                <td>
                    <?php echo esc_html__( 'Name', 'disciple_tools' ) ?>
                </td>
                <td>
                    <?php echo esc_html__( 'Description', 'disciple_tools' ) ?>
                <td>
                    <?php echo esc_html__( 'Actions', 'disciple_tools' ) ?>
                </td>
            </tr>
            </thead>
            <tbody>
            <?php
            foreach ( $plugins as $plugin ) {
                foreach ( $plugin as $p ) {
                    ?>
                    <tr>
                        <td>
                            <?php echo esc_html( $p->name ); ?>
                        </td>
                        <td>
                            <?php echo esc_html( $p->description ); ?>
                        </td>
                        <td>
                            <?php
                            $result_name = $this->partial_array_search( $all_plugins, $p->folder_name );
                            if ( $result_name == -1 ) {
                                ?>
                                <button class="button"
                                        onclick="install('<?php echo esc_html( $p->url ); ?>')"><?php echo esc_html__( 'Install', 'disciple_tools' ) ?></button>

                                <?php
                            } elseif ( $this->partial_array_search( $active_plugins, $p->folder_name ) == -1 && isset( $_POST["activate"] ) == false ) {
                                ?>
                                <button class="button"
                                        onclick="activate('<?php echo esc_html( $result_name ); ?>')"><?php echo esc_html__( 'Activate', 'disciple_tools' ) ?></button>
                                <?php
                            } else {
                                ?>
                                <p><?php echo esc_html__( 'Activated', 'disciple_tools' ) ?></p>
                                <?php
                            }
                            ?>
                        </td>
                    </tr>
                    <?php
                }
            }
            ?>
            </tbody>
        </table>
        <h3><?php esc_html_e( "Recommended Plugins", 'disciple_tools' ) ?></h3>
        <p><?php echo esc_html__( 'Look for the "Install" button on the bottom right of your screen after clicking the install button link', 'disciple_tools' ) ?></p>
        <table class="widefat striped">
            <thead>
            <tr>
                <td>
                    <?php echo esc_html__( 'Name', 'disciple_tools' ) ?>
                </td>
                <td>
                    <?php echo esc_html__( 'Description', 'disciple_tools' ) ?>
                <td>
                    <?php echo esc_html__( 'Action', 'disciple_tools' ) ?>
                </td>
            </tr>
            </thead>
            <tbody>
            <!--Updraft-->
            <tr>
                <td>
                    <?php echo esc_html__( "UpdraftPlus - Backup/Restore", 'disciple_tools' ); ?>
                </td>
                <td>
                    <?php echo esc_html__( "Setup backups to a remote location. Note: Only the Premium version works with Disciple.Tools. See our help page:", 'disciple_tools' ); ?>
                    <a target="_blank" href="https://disciple-tools.readthedocs.io/en/latest/Disciple_Tools_Theme/development/self_hosting.html#backups">Backups</a>
                </td>
                <td>
                    <?php
                    $result_name = $this->partial_array_search( $all_plugins, "updraftplus" );
                    if ( $result_name == -1 ) {
                        ?>
                        <a class="button"
                           href="./plugin-install.php?tab=plugin-information&plugin=updraftplus"><?php echo esc_html__( 'Install', 'disciple_tools' ) ?></a>
                        <?php
                    } else if ( $this->partial_array_search( $active_plugins, "updraftplus" ) == -1 && isset( $_POST["activate"] ) == false ) {
                        ?>
                    <button class="button"
                            onclick="activate('<?php echo esc_html( "updraftplus/updraftplus.php" ); ?>')"><?php echo esc_html__( 'Activate', 'disciple_tools' ) ?></button>
                </td>
            </tr>
                        <?php
                    }
                    else {
                        ?>
                <p><?php echo esc_html__( 'Installed', 'disciple_tools' ) ?></p>
                        <?php
                    }
                    ?>
            </td>
            </tr>
            <!--Two Factor Authentication-->
            <tr>
                <td>
                    <?php echo esc_html__( "Two Factor Authentication", 'disciple_tools' ); ?>
                </td>
                <td>
                    <?php echo esc_html__( "Secure your WordPress login forms with two factor authentication - including WooCommerce login forms", 'disciple_tools' ); ?>
                </td>
                <td>
                    <?php
                    $result_name = $this->partial_array_search( $all_plugins, "two-factor-authentication" );
                    if ( $result_name == -1 ) {
                        ?>
                        <a class="button"
                           href="./plugin-install.php?tab=plugin-information&plugin=two-factor-authentication"><?php echo esc_html__( 'Install', 'disciple_tools' ) ?></a>
                        <?php
                    } else if ( $this->partial_array_search( $active_plugins, "two-factor-authentication" ) == -1 && isset( $_POST["activate"] ) == false ) {
                        ?>
                    <button class="button"
                            onclick="activate('<?php echo esc_html( "two-factor-authentication/two-factor-login.php" ); ?>')"><?php echo esc_html__( 'Activate', 'disciple_tools' ) ?></button>
                </td>
            </tr>
                        <?php
                    }
                    else {
                        ?>
                <p><?php echo esc_html__( 'Installed', 'disciple_tools' ) ?></p>
                        <?php
                    }
                    ?>
            </td>
            </tr>
            <!--Inactive Logout-->
            <tr>
                <td>
                    <?php echo esc_html__( "Inactive Logout", 'disciple_tools' ); ?>
                </td>
                <td>
                    <?php echo esc_html__( "Inactive logout provides functionality to automatically log out any idle users after a defined period.", 'disciple_tools' ); ?>
                </td>
                <td>
                    <?php
                    $result_name = $this->partial_array_search( $all_plugins, " inactive-logout" );
                    if ( $result_name == -1 ) {
                        ?>
                        <a class="button"
                           href="./plugin-install.php?tab=plugin-information&plugin= inactive-logout"><?php echo esc_html__( 'Install', 'disciple_tools' ) ?></a>
                        <?php
                    } else if ( $this->partial_array_search( $active_plugins, " inactive-logout" ) == -1 && isset( $_POST["activate"] ) == false ) {
                        ?>
                    <button class="button"
                            onclick="activate('<?php echo esc_html( " inactive-logout/inactive-logout.php" ); ?>')"><?php echo esc_html__( 'Activate', 'disciple_tools' ) ?></button>
                </td>
            </tr>
                        <?php
                    }
                    else {
                        ?>
                <p><?php echo esc_html__( 'Installed', 'disciple_tools' ) ?></p>
                        <?php
                    }
                    ?>
            </td>
            </tr>
            </tbody>
        </table>
        <?php
    }

    //checks for a partial string in an array
    public function partial_array_search( $array, $find ) {
        //check for null value
        if ( $find == null || count( $array ) == 0 ) {
            return -1;
        }
        //with array keys
        foreach ( array_keys( $array ) as $key ) {
            $key = " " . $key; //for rare bug
            if ( strpos( $key, $find ) !== false ) {
                return $key;
            }
        }
        //with array values
        foreach ( $array as $key ) {
            if ( gettype( $key ) != 'array' ) {
                $key = " " . $key;
                if ( strpos( $key, $find ) !== false ) {
                    return $key;
                }
            }
        }

        //false
        return -1;
    }

    //this function will install a plugin with a name
    public function install_plugin( $url ) {
        set_time_limit( 0 );
        //download plugin json data
        $plugin_json_text = file_get_contents( $url );
        $plugin_json = json_decode( trim( $plugin_json_text ) );
        //get url for plugin
        $download_url = $plugin_json->download_url;
        $folder_name = explode( "/", $download_url );
        $folder_name = get_home_path() . "wp-content/plugins/" . $folder_name[ count( $folder_name ) - 1 ];
        if ( $folder_name != "" ) {
            //download the zip file to plugins
            //http://php.net/file_put_contents <- to download
            file_put_contents( $folder_name, file_get_contents( $download_url ) );
            // get the absolute path to $file
            $folder_name = realpath( $folder_name );
            //unzip
            WP_Filesystem();
            $unzip = unzip_file( $folder_name, realpath( get_home_path() . "wp-content/plugins/" ) );
            //remove the file
            unlink( $folder_name );
        }
    }

    //this function gets the plugin list data
    public function get_plugins() {
        return json_decode( trim( file_get_contents( 'https://raw.githubusercontent.com/DiscipleTools/disciple-tools-version-control/master/disciple-tools-plugin-url-list.json' ) ) );
    }
}

Disciple_Tools_Tab_Featured_Extensions::instance();
