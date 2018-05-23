<?php

if ( !defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class Disciple_Tools_General_Tab
 */
class Disciple_Tools_Tab_Featured_Extensions extends Disciple_Tools_Abstract_Menu_Base
{
    private static $_instance = null;

    public static function instance()
    {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct()
    {
        add_action( 'dt_extensions_tab_menu', [ $this, 'add_tab' ], 10, 1 ); // use the priority setting to control load order
        add_action( 'dt_extensions_tab_content', [ $this, 'content' ], 99, 1 );
        parent::__construct();
    } // End __construct()

    public function add_tab( $tab )
    {
        ?>
        <script type="text/javascript">
            //alert("nice");
            function install(plug) {
                //alert("working install");
                //alert(plug);
                jQuery("#wpbody-content").replaceWith( "<p>installing...</p>" );
                jQuery.post("",
                    {
                        install: plug,
                    },
                    function(data, status){
                        location.reload();
                    });
            }
            function activate(plug) {
                jQuery("#wpbody-content").replaceWith( "<p>activating...</p>" );
                jQuery.post("",
                    {
                        activate: plug,
                    },
                    function(data, status){
                        location.reload();
                    });
            }
        </script>
        <?php
        echo '<a href="' . esc_url( admin_url() ) . 'admin.php?page=dt_extensions&tab=featured-extensions" class="nav-tab ';
        if ($tab == 'featured-extensions') {
            echo 'nav-tab-active';
        }
        echo '">' . esc_attr__( 'Featured Extensions', 'disciple_tools' ) . '</a>';
    }

    public function content( $tab )
    {
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

    //checks for a partial string in an array
    public function partial_array_search( $array, $find )
    {
        //check for null value
        if ($find == null) {
            return -1;
        }
        //with array keys
        foreach ( array_keys( $array ) as $key ) {
            $key = " " . $key; //for rare bug
            if (strpos( $key, $find ) !== false ) {
                return $key;
            }
        }
        //with array values
        foreach ($array as $key) {
            $key = " " . $key; //for rare bug
            if ( strpos( $key, $find ) !== false ) {
                return $key;
            }
        }
        //false
        return -1;
    }

    //main page
    public function box_message()
    {
        //nonce check
        //do we set the nonce because this fails always for me?
        $nonce = $_REQUEST['_wpnonce'];
        if ( !wp_verify_nonce( $nonce, 'my-nonce' ) ) {
            //die( 'Security Check' );

        }
        //check for actions
        if ( isset( $_POST["activate"] ) && is_admin() ) {
            //activate the plugin
            $result = activate_plugin( $_POST["activate"] );
            ?>
            <p> plugin installed </p>
            <p><a href=<?php echo esc_html( $_SERVER["HTTP_REFERER"] ); ?>> return to plugin page </a></p>
            <?php
            exit;
        }
        else if ( isset( $_POST["install"] ) && is_admin() ) {
            //install plugin
            $this->install_plugin( $_POST["install"] );
            exit;
        }
        $active_plugins = get_option( 'active_plugins' );
        $all_plugins = get_plugins();
        //get plugin data
        $plugins = $this->get_plugins();
        ?>
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
            foreach ($plugins as $plugin) {
                foreach ($plugin as $p) {
                    ?>
                    <tr>
                    <td><?php echo esc_html( $p->name ); ?></td>
                    <td>
                    <?php echo esc_html( $p->description ); ?></td>
                    <td><?php
                    $result_name = $this->partial_array_search( $all_plugins, $p->folder_name );
                    if ($result_name == -1) {
                        ?>
                        <button class="button" onclick="install('<?php echo esc_html( $p->url ); ?>')">Install</button>
                        </td>
                        </tr>
                        <?php
                    } else if ( $this->partial_array_search( $active_plugins, $p->folder_name ) == -1 && isset( $_POST["activate"] ) == false && strpos( $_POST["activate"], $p->folder_name ) === false ) {
                        ?>
                        <button class="button" onclick="activate('<?php echo esc_html( $result_name ); ?>')">Activate</button>
                        </td>
                        </tr>
                        <?php
                    } else {
                        ?>
                        <p>Installed</p>
                        </td>
                        </tr>
                        <?php
                    }
                }
            }
            ?></tbody>
        </table>
        <?php
    }

    //this function will install a plugin with a name
    public function install_plugin( $url )
    {
        set_time_limit( 0 );
        //download plugin json data
        $plugin_json_text = file_get_contents( $url );
        $plugin_json = json_decode( trim( $plugin_json_text ) );
        //get url for plugin
        $download_url = $plugin_json->download_url;
        $folder_name = explode( "/", $download_url );
        $folder_name = get_home_path() . "wp-content/plugins/" . $folder_name[ count( $folder_name ) - 1 ];
        ?> <p> got info </p>
        <?php
        if ( $folder_name != "" ) {
            //download the zip file to plugins
            //http://php.net/file_put_contents <- to download
            file_put_contents( $folder_name, file_get_contents( $download_url ) );
            ?> <p> downloaded file </p>
            <?php
            //unzip the file
            // get the absolute path to $file
            $zip = new ZipArchive();
            $res = $zip->open( $folder_name );
            if ( $res === true ) {
                // extract it to the path we determined above
                $zip->extractTo( get_home_path() . "wp-content/plugins/" );
                $zip->close();
                //remove the file
                unlink( $folder_name );
                ?> <p> opening zip </p> <?php
            }
        }
        ?>
        <p> click on the activate button (on the main plugin page) to activate the plugin </p>
        <p><a href=<?php echo esc_html( $_SERVER["HTTP_REFERER"] ); ?>> return to plugin page </a></p> <?php
    }

    //this function gets the plugin list data
    public function get_plugins()
    {
        return json_decode( trim( file_get_contents( 'https://raw.githubusercontent.com/DiscipleTools/disciple-tools-version-control/master/disciple-tools-plugin-url-list.json' ) ) );
    }
}

Disciple_Tools_Tab_Featured_Extensions::instance();
