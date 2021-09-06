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

            function deactivate(plug) {
                jQuery("#wpbody-content").replaceWith("<p><?php esc_html_e( 'deactivating', 'disciple_tools' ) ?>...</p>");
                jQuery.post("",
                    {
                        deactivate: plug,
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

        $this->modify_css();
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
} elseif ( isset( $_POST["deactivate"] ) && is_admin() && isset( $_POST["_ajax_nonce"] ) && check_ajax_referer( 'portal-nonce', sanitize_key( $_POST["_ajax_nonce"] ) ) && current_user_can( "manage_dt" ) ) {
    //deactivate the plugin
    deactivate_plugins( sanitize_text_field( wp_unslash( $_POST["deactivate"] ) ), true );
    exit;
}
        $active_plugins = get_option( 'active_plugins' );
        $all_plugins = get_plugins();
        //get plugin data
        $plugins = $this->get_plugins();

        // Page content goes here
?>
        <div class="wp-filter">
        <ul class="filter-links">
            <li class="plugin-install"><a href="https://dt-clean-fork.local/wp-admin/plugin-install.php?tab=featured" class="current" aria-current="page">All Plugins</a> </li>
        </ul>
        </div>
        <p>Plugins are ways of extending the Disciple.Tools system to meet the unique needs of your project, ministry, or movement.</p>
        <div id="the-list">
            <?php foreach ( $plugins as $plugin ) {
                $plugin->slug = explode( '/', $plugin->homepage );
                $plugin->slug = $plugin->slug[ array_key_last( $plugin->slug ) ];
                $plugin->blog_url = 'https://disciple.tools/plugins/' . $plugin->slug;
                $plugin->folder_name = get_home_path() . "wp-content/plugins/" . $plugin->slug;
                ?>
            <!-- Plugin Card: START -->
            <div class="plugin-card plugin-card-classic-editor">
                            <div class="plugin-card-top">
                    <div class="name column-name">
                        <h3>
                            <a href="https://dt-clean-fork.local/wp-admin/plugin-install.php?tab=plugin-information&amp;plugin=classic-editor&amp;TB_iframe=true&amp;width=772&amp;height=890" class="thickbox open-plugin-details-modal">
                            <?php echo esc_html( $plugin->name ); ?>
                            <img src="https://s.w.org/plugins/geopattern-icon/<?php echo esc_attr( $plugin->slug ); ?>.svg" class="plugin-icon" alt="<?php echo esc_attr( $plugin->name ); ?>">
                            </a>
                        </h3>
                    </div>
                    <div class="action-links">
                        <ul class="plugin-action-buttons">
                            <li>
                            <?php
                            $result_name = $this->partial_array_search( $all_plugins, $plugin->slug );
                            if ( $result_name == -1 ) {
                                if ( isset( $plugin->download_url ) && current_user_can( "install_plugins" ) ) : ?>
                                <button class="button"
                                        onclick="install('<?php echo esc_html( $plugin->download_url ); ?>')"><?php echo esc_html__( 'Install', 'disciple_tools' ) ?></button>
                                <?php else : ?>
                                    <span>To install this plugin ask your network administrator</span>
                                <?php endif;

                            } elseif ( $this->partial_array_search( $active_plugins, $plugin->slug ) == -1 && isset( $_POST["activate"] ) == false ) {
                                ?>
                                <button class="button"
                                        onclick="activate('<?php echo esc_html( $result_name ); ?>')"><?php echo esc_html__( 'Activate', 'disciple_tools' ) ?></button>
                                <?php
                            } else {
                                ?>
                                <button class="button"
                                        onclick="deactivate('<?php echo esc_html( $result_name ); ?>')"><?php echo esc_html__( 'Deactivate', 'disciple_tools' ) ?></button>
                                <?php
                            }
                            ?> 
                            </li>
                        </ul>
                    </div>
                    <div class="desc column-description">
                        <p><?php echo esc_html( $plugin->description ); ?></p>
                        <p class="authors"> <cite>By <a href="<?php echo esc_attr( $plugin->author_homepage ); ?>"><?php echo esc_html( $plugin->author ); ?></a></cite></p>
                    </div>
                </div>
                <div class="plugin-card-bottom">
                    <!--
                    <div class="vers column-rating">
                        <div class="star-rating">
                            <span class="screen-reader-text">5.0 rating based on 1,000 ratings</span>
                            <div class="star star-full" aria-hidden="true"></div>
                            <div class="star star-full" aria-hidden="true"></div>
                            <div class="star star-full" aria-hidden="true"></div>
                            <div class="star star-full" aria-hidden="true"></div>
                            <div class="star star-full" aria-hidden="true"></div>
                        </div>
                        <span class="num-ratings" aria-hidden="true">(1,000)</span>
                    </div>
                    -->
                    <div class="column-updated">
                        <strong>Last Updated:</strong>
                        <?php echo esc_html( $plugin->last_updated ); ?>
                    </div>
                    <div class="column-downloaded">Active Installations data not available</div>
                    <div class="column-compatibility">
                        <span class="compatibility-compatible"><strong>Compatible</strong> with your version of WordPress</span>
                    </div>
                </div>
            </div>
            <!-- Plugin Card: END -->
                <?php
            }
            ?>
        </div>
        <?
    }

    //checks for a partial string in an array
    public function partial_array_search( $array, $find ) {
        //check for null value
        if ( $find == null || !$array || sizeof( $array ) == 0 ) {
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
    public function install_plugin( $download_url ) {
        set_time_limit( 0 );
        $folder_name = explode( "/", $download_url );
        $folder_name = get_home_path() . "wp-content/plugins/" . $folder_name[4] . '.zip';
        if ( $folder_name != "" ) {
            //download the zip file to plugins
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
        return json_decode( trim( file_get_contents( 'https://disciple.tools/wp-content/themes/disciple-tools-public-site/plugin-feed.php' ) ) );
    }

    /**
     * Remove the 'columns-2' class from #post-body
     * in order to make the most of the screen's width
     */
    private function modify_css() {
        ?>
        <script>jQuery('#post-body').attr('class', 'metabox-holder');</script>
        <?
    }
}

Disciple_Tools_Tab_Featured_Extensions::instance();
