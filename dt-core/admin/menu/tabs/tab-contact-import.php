<?php

if ( !defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class Disciple_Tools_Contact_Import_Tab
 */
class Disciple_Tools_Contact_Import_Tab extends Disciple_Tools_Abstract_Menu_Base
{
    private static $_instance = null;
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    } // End instance()

    /**
     * Constructor function.
     *
     * @access  public
     * @since   0.1.0
     */
    public function __construct() {
        add_action( 'admin_menu', [ $this, 'add_submenu' ], 99 );
        add_action( 'dt_utilities_tab_menu', [ $this, 'add_tab' ], 50, 1 ); // use the priority setting to control load order
        add_action( 'dt_utilities_tab_content', [ $this, 'content' ], 99, 1 );


        parent::__construct();
    } // End __construct()


    public function add_submenu() {
        add_submenu_page( 'dt_utilities', __( 'Import Contacts', 'disciple_tools' ), __( 'Import Contacts', 'disciple_tools' ), 'manage_dt', 'dt_utilities&tab=contact-import', [ 'Disciple_Tools_Utilities_Menu', 'content' ] );
    }

    public function add_tab( $tab ) {
        echo '<a href="'. esc_url( admin_url() ).'admin.php?page=dt_utilities&tab=contact-import" class="nav-tab ';
        if ( $tab == 'contact-import' ) {
            echo 'nav-tab-active';
        }
        echo '">'. esc_attr__( 'Import Contacts', 'disciple_tools' ) .'</a>';
    }

    public function content( $tab ) {
        if ( 'contact-import' == $tab ) {

            self::template( 'begin' );

            $this->tools_box_message();

            self::template( 'right_column' );

            $this->instructions();

            self::template( 'end' );
        }
    }

    public function instructions() {
        $this->box( 'top', 'Required Format' );
        ?>

        <p><?php esc_html_e( "Your csv file needs to have the following columns:", 'disciple_tools' ) ?></p>
        <p><?php esc_html_e( "name, phone, email, address, gender, initial_comment", 'disciple_tools' ) ?></p>
        <p><?php esc_html_e( "use utf-8 file format", 'disciple_tools' ) ?></p>

        <?php
        $this->box( 'bottom' );
    }


    //tools page
    public function tools_box_message() {
        $this->box( 'top', 'CSV Import Contacts' );
        //check if it can run commands
        $run = true;
        //check for admin
        if ( ! is_admin() ) {
            $run = false;
        }
        //check for action of csv import
        if ( isset( $_POST['csv_import_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['csv_import_nonce'] ) ), 'csv_import' ) && $run ) {
            //@codingStandardsIgnoreLine
            if ( isset( $_FILES[ "csv_file" ] ) ) {
                //@codingStandardsIgnoreLine
                $file_parts = explode( ".", sanitize_text_field( wp_unslash( $_FILES[ "csv_file" ][ "name" ] ) ) )[ count( explode( ".", sanitize_text_field( wp_unslash( $_FILES[ "csv_file" ][ "name" ] ) ) ) ) - 1 ];
                if ( $_FILES["csv_file"]["error"] > 0 ) {
                    esc_html_e( "ERROR UPLOADING FILE", 'disciple_tools' );
                    ?>
                    <form id="back" method="post" enctype="multipart/form-data">
                        <a href="" class="button button-primary"> <?php esc_html_e( "Back", 'disciple_tools' ) ?> </a>
                    </form>
                    <?php
                    exit;
                }
                if ( $file_parts != 'csv' ) {
                    esc_html_e( "NOT CSV", 'disciple_tools' );
                    ?>
                    <form id="back" method="post" enctype="multipart/form-data">
                        <a href="" class="button button-primary"> <?php esc_html_e( "Back", 'disciple_tools' ) ?> </a>
                    </form>
                    <?php
                    exit;
                }
                //@codingStandardsIgnoreLine
                if ( mb_detect_encoding( file_get_contents( $_FILES[ "csv_file" ][ 'tmp_name' ], false, null, 0, 100 ), 'UTF-8', true ) === false ) {
                    esc_html_e( "FILE IS NOT UTF-8", 'disciple_tools' );
                    ?>
                    <form id="back" method="post" enctype="multipart/form-data">
                        <a href="" class="button button-primary"> <?php esc_html_e( "Back", 'disciple_tools' ) ?> </a>
                    </form>
                    <?php
                    exit;
                }
                //@codingStandardsIgnoreLine
                $this->import_csv( $_FILES[ 'csv_file' ], sanitize_text_field( wp_unslash( $_POST[ 'csv_del' ] ) ), sanitize_text_field( wp_unslash( $_POST[ 'csv_source' ] ) ), sanitize_text_field( wp_unslash( $_POST[ 'csv_assign' ] ) ), sanitize_text_field( wp_unslash( $_POST[ 'csv_header' ] ) ) );
            }
            exit;
        }
        //check for verification of data
        if ( isset( $_POST['csv_correct_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['csv_correct_nonce'] ) ), 'csv_correct' ) && $run ) {
            //@codingStandardsIgnoreLine
            if ( isset( $_POST[ "csv_contacts" ] ) ) {
                //@codingStandardsIgnoreLine
                $this->insert_contacts( unserialize( base64_decode( $_POST[ "csv_contacts" ] ) ) );
            }
            exit;
        }
        ?>

        <form method="post" enctype="multipart/form-data">
            <?php wp_nonce_field( 'csv_import', 'csv_import_nonce' ); ?>
            <table class="widefat">
                <tr>
                    <td>
                        <label for="csv_file"><?php esc_html_e( 'Select your csv file' ) ?></label><br>
                        <input class="button" type="file" name="csv_file" id="csv_file" />
                    </td>
                </tr>
                <tr>
                    <td>
                        <label for="csv_del"><?php esc_html_e( "Add csv delimiter (default is fine)", 'disciple_tools' ) ?></label><br>
                        <input type="text" name="csv_del" id="csv_del" value=',' size=2 />
                    </td>
                </tr>
                <tr>
                    <td>
                        <label for="csv_header"><?php esc_html_e( "Does the file have a header? (i.e. a first row with the names of the columns?)", 'disciple_tools' ) ?></label><br>
                        <select name="csv_header" id="csv_header">
                            <option value=yes><?php esc_html_e( "yes", 'disciple_tools' ) ?></option>
                            <option value=no><?php esc_html_e( "no", 'disciple_tools' ) ?></option>
                        </select>

                    </td>
                </tr>
                <tr>
                    <td>
                        <label for="csv_source">
                            <?php esc_html_e( "Where did these contacts come from? Add a source.", 'disciple_tools' ) ?>
                        </label><br>
                        <select name="csv_source" id="csv_source">
                            <?php
                            $site_custom_lists = dt_get_option( 'dt_site_custom_lists' );
                            foreach ( $site_custom_lists['sources'] as $key => $value ) {
                                if ( $value['enabled'] ) {
                                    ?>
                                    <option value=<?php echo esc_html( $key ); ?>><?php echo esc_html( $value['label'] ); ?></option>
                                    <?php
                                }
                            }
                            ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>
                        <label for="csv_assign">
                            <?php esc_html_e( "Which user do you want these assigned to?", 'disciple_tools' ) ?>
                        </label><br>
                        <select name="csv_assign" id="csv_assign">
                            <option value=""></option>
                            <?php
                            $args = [
                                'role__not_in' => [ 'registered' ],
                                'fields'       => [ 'ID', 'display_name' ],
                                'order'        => 'ASC',
                            ];
                            $users = get_users( $args );
                            foreach ( $users as $user ) { ?>
                                <option
                                    value=<?php echo esc_html( $user->ID ); ?>><?php echo esc_html( $user->display_name ); ?></option>
                            <?php } ?>
                        </select>

                    </td>
                </tr>
                <tr>
                    <td>
                        <p class="submit">
                            <input type="submit" name="submit" id="submit" class="button"
                                                 value=<?php esc_html_e( "Upload", 'disciple_tools' ) ?>>
                        </p>
                    </td>
                </tr>
            </table>
        </form>


        <?php
        $this->box( 'bottom' );
    }

    public function import_csv( $file, $del = ';', $source = 'web', $assign = '', $header = "yes" ) {
        $people = [];
        //open file
        ini_set( 'auto_detect_line_endings', true );
        $file_data = fopen( $file['tmp_name'], "r" );
        //loop over array
        while ( $row = fgetcsv( $file_data, 0, $del ) ) {
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
        if ( $header == "yes" ) {
            unset( $people[0] );
            $pos = 1;
        }
        ?>
        <h3><?php echo esc_html_e( "Is This Data In The Correct Fields?", 'disciple_tools' ); ?> </h3>
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
        set_time_limit( 0 );
        global $wpdb;
        ?>
        <script type="text/javascript">
            function process( q, num, fn, done ) {
                // remove a batch of items from the queue
                var items = q.splice(0, num),
                    count = items.length;

                // no more items?
                if ( !count ) {
                    // exec done callback if specified
                    done && done();
                    // quit
                    return;
                }

                // loop over each item
                for ( var i = 0; i < count; i++ ) {
                    // call callback, passing item and
                    // a "done" callback
                    fn(items[i], function() {
                        // when done, decrement counter and
                        // if counter is 0, process next batch
                        --count || process(q, num, fn, done);
                    });
                }
            }

            // a per-item action
            function doEach( item, done ) {
                console.log('starting ...' );
                jQuery.ajax({
                    type: "POST",
                    data: item,
                    contentType: "application/json; charset=utf-8",
                    dataType: "json",
                    url: "<?php echo esc_url_raw( rest_url() ); ?>" + `dt/v1/contact/create?silent=true`,
                    beforeSend: function(xhr) {
                        xhr.setRequestHeader('X-WP-Nonce', "<?php /*@codingStandardsIgnoreLine*/ echo sanitize_text_field( wp_unslash( wp_create_nonce( 'wp_rest' ) ) ); ?>");
                    },
                    success: function(data) {
                        console.log('done');
                        done();
                    },
                    error: function(xhr) { // if error occured
                        alert("Error occured.please try again");
                        console.log("%o",xhr);
                    }
                });
            }

            // an all-done action
            function doDone() {
                console.log('all done!');
                jQuery("#back").show();
            }
        </script>
        <?php
        global $wpdb;
        $js_contacts = [];
        foreach ( $contacts as $num => $f ) {
            $js_array = wp_json_encode( $f[0] );
            $js_contacts[] = $js_array;
            $wpdb->queries = [];
        }
        ?>
        <script type="text/javascript">
            // start processing queue!
            queue = <?php echo wp_json_encode( $js_contacts ); ?>

                process(queue, 5, doEach, doDone);
        </script>
        <?php
        $num = count( $contacts );
        echo esc_html( sprintf( __( "Creating %s Contacts DO NOT LEAVE THE PAGE UNTIL THE BACK BUTTON APPEARS", 'disciple_tools' ), $num ) );
        ?>
        <form id="back" method="post" enctype="multipart/form-data" hidden>
            <a href="" class="button button-primary"> <?php esc_html_e( "Back", 'disciple_tools' ) ?> </a>
        </form>
        <?php
        exit;
    }
}
Disciple_Tools_Contact_Import_Tab::instance();
