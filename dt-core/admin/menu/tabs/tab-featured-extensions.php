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
        //tools tab
        add_action( 'dt_extensions_tools_tab_content', [ $this, 'content' ], 100, 1 );
        parent::__construct();
    } // End __construct()

    public function add_tab( $tab )
    {
        $nonce = wp_create_nonce( 'portal-nonce' );
        ?>
        <script type="text/javascript">
            function install(plug) {
                jQuery("#wpbody-content").replaceWith( "<p>installing...</p>" );
                jQuery.post("",
                    {
                        install: plug,
                        _ajax_nonce: "<?php echo esc_attr( $nonce ); ?>"

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
                        _ajax_nonce: "<?php echo esc_attr( $nonce ); ?>"
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
        echo '<a href="' . esc_url( admin_url() ) . 'admin.php?page=dt_extensions&tab=tools" class="nav-tab ';
        if ($tab == 'tools') {
            echo 'nav-tab-active';
        }
        echo '">' . esc_attr__( 'Tools', 'disciple_tools' ) . '</a>';
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
        else if ( 'tools' == $tab ) {
            // begin columns template
            $this->template( 'begin' );

            $this->tools_box_message();

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
        if ($find == null || count( $array ) == 0) {
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
            if ( gettype( $key ) != 'array' ) {
                $key = " " . $key;
                if ( strpos( $key, $find ) !== false) {
                    return $key;
                }
            }
        }
        //false
        return -1;
    }

    //tools page
    public function tools_box_message()
    {
        //check if it can run commands
        $run = true;
        //check for admin or multisite super admin
        if ( ( is_multisite() && !is_super_admin() ) || ( !is_multisite() && !is_admin() ) ) {
            $run = false;
        }
        //check for action of csv import
        if ( isset( $_POST['csv_import_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['csv_import_nonce'] ) ), 'csv_import' ) && $run ) {
            //@codingStandardsIgnoreLine
            if ( isset( $_FILES["csv_file"] ) ) {
                //@codingStandardsIgnoreLine
                $file_parts = explode( ".", sanitize_text_field( wp_unslash( $_FILES["csv_file"]["name"] ) ) )[count( explode( ".", sanitize_text_field( wp_unslash( $_FILES["csv_file"]["name"] ) ) ) ) -1];
                if ( $file_parts != 'csv') {
                    esc_html_e( "NOT CSV", 'disciple_tools' );
                    exit;
                }
                if ($_FILES["csv_file"]["error"] > 0) {
                    esc_html_e( "ERROR UPLOADING FILE", 'disciple_tools' );
                    exit;
                }
                //@codingStandardsIgnoreLine
                $this->import_csv( $_FILES['csv_file'], sanitize_text_field( wp_unslash( $_POST['csv_del'] ) ), sanitize_text_field( wp_unslash( $_POST['csv_source'] ) ), sanitize_text_field( wp_unslash( $_POST['csv_assign'] ) ), sanitize_text_field( wp_unslash( $_POST['csv_header'] ) ) );
            }
            exit;
        }
        //check for varrification of data
        if ( isset( $_POST['csv_correct_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['csv_correct_nonce'] ) ), 'csv_correct' ) && $run ) {
            //@codingStandardsIgnoreLine
            if ( isset( $_POST["csv_contacts"] ) ) {
                //@codingStandardsIgnoreLine
                $this->insert_contacts( unserialize( base64_decode( $_POST["csv_contacts"] ) ) );
            }
            exit;
        }
        ?>
        <h3><?php esc_html_e( "CSV IMPORT", 'disciple_tools' ) ?></h3>
        <p><?php esc_html_e( "Format", 'disciple_tools' ) ?></p>
        <p><?php esc_html_e( "name, phone, email, address, gender, initial_comment", 'disciple_tools' ) ?></p>
        <form method="post" enctype="multipart/form-data">
            <input type="file" name="csv_file" id="csv_file"> <br>
            <input type="text" name="csv_del" value=',' size=2> <?php esc_html_e( "The CSV Delimiter", 'disciple_tools' ) ?> <br>
            <select name="csv_header">
                <option value=yes ><?php esc_html_e( "yes", 'disciple_tools' ) ?></option>
                <option value=no ><?php esc_html_e( "no", 'disciple_tools' ) ?></option>
            </select>
            <?php esc_html_e( "Include header?", 'disciple_tools' ) ?> <br>
            <select name="csv_source">
                <?php
                $site_custom_lists = dt_get_option( 'dt_site_custom_lists' );
                foreach ( $site_custom_lists['sources'] as $key => $value ) {
                    if ( $value['enabled'] ) {
                        ?>  <option value=<?php echo esc_html( $key ); ?>><?php echo esc_html( $value['label'] ); ?></option> <?php
                    }
                }
                ?>
            </select>
            <?php esc_html_e( "The Source of the Contacts", 'disciple_tools' ) ?> <br>
            <select name="csv_assign">
                <option value""></option>
                <?php
                $args = [
                    'role__not_in' => [ 'registered' ],
                    'fields' => [ 'ID', 'display_name' ],
                    'order' => 'ASC'
                ];
                $users = get_users( $args );
                foreach ( $users as $user) { ?>
                    <option value=<?php echo esc_html( $user->ID ); ?>><?php echo esc_html( $user->display_name ); ?></option>
                <?php } ?>
            </select>
            <?php esc_html_e( "The User to Assign to", 'disciple_tools' ) ?>
            <?php wp_nonce_field( 'csv_import', 'csv_import_nonce' ); ?>
            <p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value=<?php esc_html_e( "Submit", 'disciple_tools' ) ?>></p>
        </form>
        <?php
    }

    public function import_csv( $file, $del = ';', $source = 'web', $assign = '', $header = "no" ) {
        $people = [];
        //open file
        $file_data = fopen( $file['tmp_name'], "r" );
        //loop over array
        while ( $row = fgetcsv( $file_data, 10000, $del ) ) {
            foreach ($row as $index => $i) {
                //$info = explode( $del, $data );
                //chcek for data type
                if ($assign != '') {
                    $fields["assigned_to"] = (int) $assign;
                }
                $fields["sources"] = [ "values" => array( [ "value" => $source ] ) ];
                //foreach ($info as $index => $i) {
                    $i = str_replace( "\"", "", $i );
                //checks for name
                if ( $index == 0 ){
                    $fields['title'] = $i;
                }
                //checks for phone
                else if ( $index == 1) {//preg_match('/^[0-9|(|)|\-|#|" "|+]*]*$/', $i) ) {
                    $fields['contact_phone'][] = [ "value" => $i ];
                }
                //checks for email
                else if ( $index == 2) {//filter_var($i, FILTER_VALIDATE_EMAIL) ) {
                    $fields['contact_email'][] = [ "value" => $i ];
                }
                //checks for address
                else if ( $index == 3) {
                    $fields['contact_address'][] = [ "value" => $i ];
                }
                //checks for gender
                else if ( $index == 4) {
                    $i = strtolower( $i );
                    $i = substr( $i, 0, 1 );
                    $gender = "not-set";
                    if ($i == "m" ){
                        $gender = "male";
                    }
                    else if ($i == "f" ){
                        $gender = "female";
                    }
                    $fields['gender'] = $gender;
                }
                //checks for comments
                else { //$index == count($info)-1 ) {
                    if ( $i != '' ) {
                        $fields["notes"][] = $i;
                    }
                }
            }
                //add person
            if ( $fields['title'] != '' && $fields['title'] != ' ' && $fields['title'] !== false ) {
                $people[] = array( $fields );
                unset( $fields['contact_email'] );
                unset( $fields['contact_phone'] );
                unset( $fields['contact_address'] );
                unset( $fields['gender'] );
                unset( $fields['sources'] );
                unset( $fields['notes'] );
            }
        }
        //close the file
        fclose( $file_data );
        //check for correct data
        $pos = 0;
        if ( $header == "no" ) {
            unset( $people[0] );
            $pos = 1;
        }
        ?>
        <h3> <?php echo esc_html_e( "Is This Data In The Correct Fields?", 'disciple_tools' ); ?> </h3>
        <p><?php esc_html_e( "Name", 'disciple_tools' ) ?>: <?php echo esc_html( $people[$pos][0]['title'] ) ?></p>
        <p><?php esc_html_e( "Source", 'disciple_tools' ) ?>: <?php echo esc_html( $people[$pos][0]['sources']["values"][0]["value"] ) ?></p>
        <p><?php esc_html_e( "Assigned To", 'disciple_tools' ) ?>: <?php echo ( isset( $people[$pos][0]['assigned_to'] ) && $people[$pos][0]['assigned_to'] != '' ) ? esc_html( get_user_by( 'id', $people[$pos][0]['assigned_to'] )->data->display_name ) : "Not Set" ?></p>
        <p><?php esc_html_e( "Contact Phone", 'disciple_tools' ) ?>: <?php echo isset( $people[$pos][0]['contact_phone'][0]["value"] ) ? esc_html( $people[$pos][0]['contact_phone'][0]["value"] ) : "None" ?></p>
        <p><?php esc_html_e( "Contact Email", 'disciple_tools' ) ?>: <?php echo isset( $people[$pos][0]['contact_email'][0]["value"] ) ? esc_html( $people[$pos][0]['contact_email'][0]["value"] ) : "None" ?></p>
        <p><?php esc_html_e( "Contact Address", 'disciple_tools' ) ?>: <?php echo isset( $people[$pos][0]['contact_address'][0]["value"] ) ? esc_html( $people[$pos][0]['contact_address'][0]["value"] ) : "None" ?></p>
        <p><?php esc_html_e( "Gender", 'disciple_tools' ) ?>: <?php echo isset( $people[$pos][0]['gender'] ) ? esc_html( $people[$pos][0]['gender'] ) : "None" ?></p>
        <p><?php esc_html_e( "Notes", 'disciple_tools' ) ?>: <?php echo isset( $people[$pos][0]['notes'][0] ) ? esc_html( $people[$pos][0]['notes'][0] ) : "None" ?></p>
        </p>
        <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="csv_contacts" value="<?php echo esc_html( base64_encode( serialize( $people ) ) ); ?>">
            <?php wp_nonce_field( 'csv_correct', 'csv_correct_nonce' ); ?>
            <a href="/dt3/wp-admin/admin.php?page=dt_extensions&tab=tools" class="button button-primary"> <?php esc_html_e( "No", 'disciple_tools' ) ?> </a>
            <input type="submit" name="submit" id="submit" style="background-color:#4CAF50; color:white" class="button" value=<?php esc_html_e( "Yes", 'disciple_tools' ) ?>>
        </form>
        <?php
    }

    private function insert_contacts( $contacts) {
        ?>
         <script type="text/javascript">
        jQuery(document).ajaxStop(function () {
            jQuery("#back").show();
        });
        </script>
        <?php
        set_time_limit( 0 );
        global $wpdb;
        foreach ( $contacts as $num => $f ) {
            $js_array = json_encode( $f[0] );
            $ret = 0;
            ?>
            <script type="text/javascript">
            jQuery.ajax({
                type: "POST",
                data: JSON.stringify(<?php echo esc_js( $js_array ); ?>),
                contentType: "application/json; charset=utf-8",
                dataType: "json",
                url: "<?php echo esc_url_raw( rest_url() ); ?>" + `dt/v1/contact/create`,
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', "<?php echo esc_js( wp_create_nonce( 'wp_rest' ) ); ?>");
                    }
            });
            </script>
            <?php
            $wpdb->queries = [];
            if ( !is_numeric( $ret ) ) {
                break;
                echo esc_html_e( "ERROR CREATING CONTACT", 'disciple_tools' );
            }
        }
        $num = count( $contacts );
        echo esc_html( sprintf( __( "Creating %s Contacts", 'disciple_tools' ), $num ) );
        ?>
        <form id="back" method="post" enctype="multipart/form-data" hidden>
            <a href="/dt3/wp-admin/admin.php?page=dt_extensions&tab=tools" class="button button-primary"> <?php esc_html_e( "Back", 'disciple_tools' ) ?> </a>
        </form>
        <?php
        exit;
    }
    //main page
    public function box_message()
    {
        //check if it can run commands
        $run = true;
        //check for admin or multisite super admin
        if ( ( is_multisite() && !is_super_admin() ) || ( !is_multisite() && !is_admin() ) ) {
            $run = false;
        }
        //check for actions
        if ( isset( $_POST["activate"] ) && is_admin() && isset( $_POST["_ajax_nonce"] ) && check_ajax_referer( 'portal-nonce', sanitize_key( $_POST["_ajax_nonce"] ) ) && $run ) {
            //activate the plugin
            activate_plugin( sanitize_text_field( wp_unslash( $_POST["activate"] ) ) );
            exit;
        }
        else if ( isset( $_POST["install"] ) && is_admin() && isset( $_POST["_ajax_nonce"] ) && check_ajax_referer( 'portal-nonce', sanitize_key( $_POST["_ajax_nonce"] ) ) && $run ) {
            //install plugin
            $this->install_plugin( sanitize_text_field( wp_unslash( $_POST["install"] ) ) );
            exit;
        }
        $active_plugins = get_option( 'active_plugins' );
        $all_plugins = get_plugins();
        //get plugin data
        $plugins = $this->get_plugins();
        ?>
        <h3><?php esc_html_e( "Official DT Plugins", 'disciple_tools' ) ?></h3>
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
                        <td>
                            <?php echo esc_html( $p->name ); ?>
                        </td>
                        <td>
                            <?php echo esc_html( $p->description ); ?>
                        </td>
                    <td>
                    <?php
                    $result_name = $this->partial_array_search( $all_plugins, $p->folder_name );
                    if ($result_name == -1) {
                        ?>
                                <button class="button" onclick="install('<?php echo esc_html( $p->url ); ?>')"><?php echo esc_html__( 'Install', 'disciple_tools' ) ?></button>
                            </td>
                        </tr>
                        <?php
                    } else if ( $this->partial_array_search( $active_plugins, $p->folder_name ) == -1 && isset( $_POST["activate"] ) == false ) {
                        ?>
                                <button class="button" onclick="activate('<?php echo esc_html( $result_name ); ?>')"><?php echo esc_html__( 'Activate', 'disciple_tools' ) ?></button>
                            </td>
                        </tr>
                        <?php
                    } else {
                        ?>
                                <p><?php echo esc_html__( 'Installed', 'disciple_tools' ) ?></p>
                            </td>
                        </tr>
                        <?php
                    }
                }
            }
            ?>
            </tbody>
        </table>
        <h3><?php esc_html_e( "Recommended Plugins", 'disciple_tools' ) ?></h3>
        <p><?php echo esc_html__( 'look for the "Install" button on the bottom right of your screen after clicking the install button link', 'disciple_tools' ) ?></p>
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
                        <?php echo esc_html( "UpdraftPlus - Backup/Restore", 'disciple_tools' ); ?>
                    </td>
                    <td>
                            <?php echo esc_html( "Backup and restore: take backups locally, or backup to Amazon S3, Dropbox, Google Drive, Rackspace, (S)FTP, WebDAV & email, on automatic schedules.", 'disciple_tools' ); ?>
                    </td>
                    <td>
                    <?php
                    $result_name = $this->partial_array_search( $all_plugins, "updraftplus" );
                    if ($result_name == -1) {
                        ?>
                                <a class="button" href="./plugin-install.php?tab=plugin-information&plugin=updraftplus"><?php echo esc_html__( 'Install', 'disciple_tools' ) ?></a>
                        <?php
                    } else if ( $this->partial_array_search( $active_plugins, "updraftplus" ) == -1 && isset( $_POST["activate"] ) == false ) {
                        ?>
                                <button class="button" onclick="activate('<?php echo esc_html( "updraftplus/updraftplus.php" ); ?>')"><?php echo esc_html__( 'Activate', 'disciple_tools' ) ?></button>
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
                        <?php echo esc_html( "Two Factor Authentication", 'disciple_tools' ); ?>
                    </td>
                    <td>
                            <?php echo esc_html( "Secure your WordPress login forms with two factor authentication - including WooCommerce login forms", 'disciple_tools' ); ?>
                    </td>
                    <td>
                    <?php
                    $result_name = $this->partial_array_search( $all_plugins, "two-factor-authentication" );
                    if ($result_name == -1) {
                        ?>
                                <a class="button" href="./plugin-install.php?tab=plugin-information&plugin=two-factor-authentication"><?php echo esc_html__( 'Install', 'disciple_tools' ) ?></a>
                        <?php
                    } else if ( $this->partial_array_search( $active_plugins, "two-factor-authentication" ) == -1 && isset( $_POST["activate"] ) == false ) {
                        ?>
                                <button class="button" onclick="activate('<?php echo esc_html( "two-factor-authentication/two-factor-login.php" ); ?>')"><?php echo esc_html__( 'Activate', 'disciple_tools' ) ?></button>
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
                        <?php echo esc_html( "Inactive Logout", 'disciple_tools' ); ?>
                    </td>
                    <td>
                            <?php echo esc_html( "Inactive logout provides functionality to log out any idle users defined specified time showing a message. Works for frontend as well.", 'disciple_tools' ); ?>
                    </td>
                    <td>
                    <?php
                    $result_name = $this->partial_array_search( $all_plugins, " inactive-logout" );
                    if ($result_name == -1) {
                        ?>
                                <a class="button" href="./plugin-install.php?tab=plugin-information&plugin= inactive-logout"><?php echo esc_html__( 'Install', 'disciple_tools' ) ?></a>
                        <?php
                    } else if ( $this->partial_array_search( $active_plugins, " inactive-logout" ) == -1 && isset( $_POST["activate"] ) == false ) {
                        ?>
                                <button class="button" onclick="activate('<?php echo esc_html( " inactive-logout/inactive-logout.php" ); ?>')"><?php echo esc_html__( 'Activate', 'disciple_tools' ) ?></button>
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
    public function get_plugins()
    {
        return json_decode( trim( file_get_contents( 'https://raw.githubusercontent.com/DiscipleTools/disciple-tools-version-control/master/disciple-tools-plugin-url-list.json' ) ) );
    }
}

Disciple_Tools_Tab_Featured_Extensions::instance();
