<?php

/**
 * Disciple_Tools_Import_Export_Tab
 *
 * @class      Disciple_Tools_Import_Export_Tab
 * @since      0.1.3
 * @package    Disciple_Tools
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class Disciple_Tools_Import_Export_Tab
 */
class Disciple_Tools_Import_CSV_Tab extends Disciple_Tools_Abstract_Menu_Base
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

        $this->odelimiter = "dt_import_odelimiter";
        $this->onumberfields = "dt_import_onumberfields";
        $this->otype = "dt_import_otype";

        $this->step = 1;
        $this->error = '';
        $this->filename = dirname( __FILE__ ) . '/locations_temp.csv'; // create temp file
        $this->delimiter = ',';
        $this->column_count = 0;
        $this->mapped = [];
        $this->insertype = '';
        $this->results = [];
        $this->count_rows = 0;

        add_action( 'dt_submenu_import_tab_menu', [ $this, 'add_tab' ], 99, 1 );
        add_action( 'dt_submenu_import_tab_content', [ $this, 'content' ], 99, 1 );

        parent::__construct();
    }

    public function add_tab( $tab ) {
        echo '<a href="admin.php?page=dt_import_export&tab=import" class="nav-tab ';
        if ( $tab == 'import' ) {
            echo 'nav-tab-active';
        }
        echo '">'. esc_attr__( 'Import', 'disciple_tools' ) .'</a>';
    }

    /**
     * Primary page content
     */
    public function content()
    {

        // Routing
        if ( $this->check_is_post( '_csv_panel', 'post_for_step_2' ) ) {
            // page 2

            // verify nonce
            if ( ! isset( $_POST['_csv_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['_csv_nonce'] ) ), 'import_csv' ) ) {
                wp_die( 'nonce verification fail' );
            };

            if ( empty( $_FILES['csv_import']['tmp_name'] ) ) {
                $this->error = "No file uploaded";
                $this->step = 1;
            }
            else {
                move_uploaded_file( sanitize_file_name( wp_unslash( $_FILES['csv_import']['tmp_name'] ) ), $this->filename ); // locally store uploaded file

                if ( ! file_exists( $this->filename ) || ! is_readable( $this->filename ) ) { // validate file
                    $this->error = "Can not open or read uploaded file.";
                    $this->step = 1;
                } else {
                    if ( isset( $_POST['post_format'] ) ) {
                        $this->insertype = sanitize_key( wp_unslash( $_POST['post_format'] ) ); // capture selected post type
                        set_transient( $this->otype, $this->insertype, 1 * HOUR_IN_SECONDS );
                        $this->step = 2;

                        // check for file limit
                        $rows_count = $this->count_rows();
                        if ( $rows_count > 2000 ) {
                            $this->error = "Cannot load more than 2000 items at a time. Please split your csv into smaller chunks.";
                            $this->step = 1;
                        } elseif ( $rows_count > 500 ) {
                            $this->error = "Loading more than 500 records at a time is not recommended. Please, consider splitting your csv file.";
                        }
                    }
                }
            }
        } elseif ( $this->check_is_post( '_csv_panel', 'post_for_step_3' ) ) {
            // page 3

            // verify nonce
            if ( ! isset( $_POST['_csv_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['_csv_nonce'] ) ), 'import_csv' ) ) {
                wp_die( 'nonce verification fail' );
            };
            $this->step = 3;
            $process_form = $this->process_form();
        } else {
            // page 1
            set_transient( 'dt_import_finished_count', 0, 1 * HOUR_IN_SECONDS ); // reset the processor transient for the async process
            set_transient( 'dt_import_finished_with_errors', [], 1 * HOUR_IN_SECONDS );
            $this->step = 1;
        }

        // Wizard Pages
        switch ( $this->step ) {
            case '1':
                ?>
                <h2>
                    <?php esc_html_e( 'Import CSV Files', 'disciple_tools' ) ?>
                </h2>
                <div class="wrap">
                    <div id="poststuff">
                        <div id="post-body" class="metabox-holder columns-2">
                            <div id="post-body-content">


                                <?php if ( $this->error !== '' ) : ?>
                                    <div class="error">
                                        <?php echo esc_attr( $this->error ); ?>
                                    </div>
                                <?php endif; ?>

                                <form class="add:the-list: validate" method="post" enctype="multipart/form-data">
                                    <?php wp_nonce_field( 'import_csv', '_csv_nonce' ); ?>
                                    <input name="_csv_panel" type="hidden" value="post_for_step_2"/>

                                    <!-- File input -->
                                    <div>
                                        <div id="formatdiv" class="postbox">
                                            <h3 class="hndle"
                                                style="cursor:auto;padding:10px;"><?php esc_html_e( 'Select File', 'disciple_tools' ) ?></h3>
                                            <div class="inside">
                                                <div id="post-formats-select">
                                                    <div>
                                                        <label for="csv_import">
                                                            <?php esc_html_e( 'Select a CSV file:', 'disciple_tools' ) ?>
                                                        </label><br/>
                                                        <input type="hidden" name="MAX_FILE_SIZE" value="30000"/>
                                                        <input name="csv_import" id="csv_import" type="file" value=""/>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Type -->
                                    <div>
                                        <div id="formatdiv" class="postbox">
                                            <h3 class="hndle"
                                                style="cursor:auto;padding:10px;"><?php esc_html_e( 'Select Import Type', 'disciple_tools' ) ?></h3>
                                            <div class="inside">
                                                <div id="post-formats-select">

                                                    <input id="post-format-page" class="post-format" type="radio"
                                                           value="locations"
                                                           name="post_format" checked>
                                                    <label for="post-format-page">
                                                        &nbsp;&nbsp;<?php esc_html_e( 'Locations', 'disciple_tools' ) ?>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="submit">
                                        <button type="submit" class="button">Next ></button>
                                    </div>
                                </form>
                            </div>

                            <div id="postbox-container-1" class="postbox-container">
                                <div class="postbox" id="formatdiv">
                                    <h3 class="hndle"
                                        style="cursor:auto;padding:10px;">Templates</h3>
                                    <div class="inside">
                                        Locations Template<br>
                                        <a href="<?php echo esc_url( get_template_directory_uri() ) . '/dt-locations/utilities/locations-template.csv' ?>" target="_blank" rel="noopener noreferrer">Locations Import Template</a>
                                    </div>
                                </div>
                            </div>

                        </div><!--poststuff end -->
                    </div><!-- wrap end -->
                </div>
                <?php
                break;

            case '2':
                $csv = $this->get_header_and_rows();
                $headers = $csv['headers'];
                $rows = $csv['rows'];
                $number_of_fields = $csv['number_of_fields'];
                ?>

                <div class="wrap">
                    <h2><?php esc_html_e( 'Import CSV Files', 'disciple_tools' ) ?></h2>
                    <?php if ( $this->error !== '' ) : ?>
                        <div class="error">
                            <?php echo esc_attr( $this->error ); ?>
                        </div>
                    <?php endif; ?>
                    <h3><?php esc_html_e( 'Step 2 - Map Fields', 'disciple_tools' ) ?></h3>
                    <p><?php esc_html_e( 'Data preview fields', 'disciple_tools' ) ?></p>

                    <?php if ( $csv ) : ?>
                    <!-- CSV REVIEW SECTION -->
                    <div style="overflow:auto;">
                        <table class="widefat">
                            <!-- Header of table-->
                            <thead>
                            <tr>
                                <?php
                                // header
                                for ( $i = 0; $i < $number_of_fields; $i++ ) {
                                    $string = $headers[ $i ];

                                    if ( mb_strlen( $string ) > 30 ) {
                                        $string = substr( $string, 0, 30 );
                                    }
                                    $string = str_replace( " ", "&nbsp;", $string );
                                    echo "<th><b>" . esc_attr( $string ) . "</b></th>";
                                }
                                ?>
                            </tr>
                            </thead>
                            <tbody>
                            <?php
                            // rows
                            foreach ( $rows as $row ) {
                                $number_of_fields = count( $row );
                                echo '<tr>';

                                for ( $i = 0; $i < $number_of_fields; $i++ ) {
                                    $string = $row[ $i ];

                                    if ( strlen( $string ) > 30 ) {
                                        $string = substr( $string, 0, 30 );
                                    }
                                    $string = str_replace( " ", "&nbsp;", $string );
                                    echo "<td>" . esc_attr( $string ) . "</td>";
                                }

                                echo '</tr>';
                            }
                            ?>
                            </tbody>
                        </table>
                    </div>

                    <br/>

                    <!-- MAP FIELDS SECTION -->
                    <form class="add:the-list: validate" method="post" enctype="multipart/form-data">
                        <?php wp_nonce_field( 'import_csv', '_csv_nonce' ); ?>
                        <input name="_csv_panel" type="hidden" value="post_for_step_3"/>

                        <!-- Type -->
                        <div id="formatdiv" class="postbox" style="max-width:600px;">
                            <h3 class="hndle" style="cursor:auto;padding:10px;">
                        <span>
                            <?php esc_html_e( 'Map fields from the .csv file to post fields', 'disciple_tools' ) ?>
                        </span>
                            </h3>
                            <div class="inside">
                                <div id="post-formats-select">

                                    <?php
                                    for ( $i = 0; $i < $number_of_fields; $i++ ) {
                                        $string = $headers[ $i ];

                                        if ( strlen( $string ) > 30 ) {
                                            $string = mb_substr( $string, 0, 30 );
                                        }
                                        ?>

                                        <div>
                                            <div style="width:250px;float:left;"><b><?php echo esc_attr( $string ) ?></b>
                                            </div>
                                            <select name="field<?php echo esc_attr( $i ) ?>">
                                                <?php $this->get_mapping_drop_down( $headers[ $i ] ) ?>
                                            </select>
                                        </div>

                                    <?php } // end for statement
                                    ?>

                                </div>
                            </div>
                        </div>

                        <?php endif; ?>

                        <a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=import_export' ) ); ?>">Back</a>
                        <button type="submit" class="button"><?php esc_attr_e( 'Next', 'disciple_tools' ) ?> ></button>
                        <br>
                        <div style="/*float:right;*/background-color:#FFFFE0;border: 1px solid #E6DB55;padding:10px;"><?php esc_html_e( 'After clicking "Next", the import process may take some time to complete. Do not navigate to another page or hit Refresh!', 'disciple_tools' ) ?>
                        </div>

                    </form>
                </div>

                <?php
                break;

            case '3':
                if ( is_wp_error( $process_form ) ) {
                    echo esc_attr( $process_form->get_error_message() );
                } else {
                    ?>

                    <div class="wrap"><h1><?php echo esc_attr__( 'Report', 'disciple_tools' ) ?></h1><br/>
                        <div id="formatdiv" class="postbox" style="display: block;max-width:350px;">
                            <h3 class="hndle"
                                style="cursor:auto;padding:10px;"><?php esc_html_e( 'Status', 'disciple_tools' ) ?></h3>
                            <div class="inside">
                                <div id="post-formats-select">
                                    <p id="status"><strong>Completed <span id="success">0</span> out of <?php echo esc_attr( get_transient( 'dt_import_csv_rows_count' ) ) ?> so far!</strong></p><br>
                                    <div id="failed"></div> <br>
                                </div>
                            </div>
                        </div>
                    </div>

                    <script>
                        function check_import(){
                            let i = 0;
                            let expectedRows = <?php echo esc_attr( get_transient( 'dt_import_csv_rows_count' ) ) ?>;
                            let url = '<?php echo esc_url_raw( get_rest_url( null, '/dt/v1/locations/import_check' ) ) ?>';
                            let nonce = '<?php echo esc_attr( wp_create_nonce( 'wp_rest' ) ) ?>';

                            let loop = window.setInterval(function(){
                                <?php // this REST API point is loaded from locations-endpoints.php ?>
                                jQuery.ajax({
                                    url : url,
                                    type : 'GET',
                                    contentType: "application/json; charset=utf-8",
                                    data : {
                                        action : 'check_progress',
                                    },
                                    beforeSend: function(xhr) {
                                        xhr.setRequestHeader('X-WP-Nonce', nonce);
                                    },
                                })
                                    .done(function (data) {
                                        jQuery('#success').html( data.count );

                                        data.errors.forEach(function( item, index ) {
                                            jQuery('#failed').append( item + '<br>' )
                                        })

                                        // clear loop
                                        if( data.count >= expectedRows ) {
                                            clearInterval(loop);
                                            jQuery('#status').html('<strong>Done! ' + data.count + ' records installed!</strong>')
                                        }

                                    })
                                    .fail(function (err) {
                                        console.log("error")
                                        console.log(err)
                                        jQuery("#errors").append(err.responseText)
                                    })
                            }, 2000);

                        }
                        check_import()

                    </script>

                    <?php

                }
                break;

            default:
                wp_die( 'step not recognized' );
                break;
        }
    }

    /**
     * Process Form
     *
     * @return array|WP_Error
     */
    public function process_form()
    {
        $this->insertype = get_transient( $this->otype );

        // Check for duplicate mappings and build mappings variable
        $check_mappings = $this->check_mappings();
        if ( is_wp_error( $check_mappings ) ) {
            return new WP_Error( 'confirm_mappings', $check_mappings->get_error_message() );
        }

        // Check required fields
        $check_required_fields = $this->check_required_fields();
        if ( is_wp_error( $check_required_fields ) ) {
            return new WP_Error( 'check_required_fields', $check_required_fields->get_error_message() );
        }

        // Insert records
        $insert_records = $this->insert_records();
        if ( is_wp_error( $insert_records ) ) {
            return new WP_Error( 'insert_records_fail', $insert_records->get_error_message() );
        }

        // Clean up temp file
        $this->remove_temp_file();

        // Return processing results
        return $insert_records;
    }

    /**
     * @return bool|\WP_Error
     */
    public function insert_records() {

        // Initialize variables
        $i = 0;
        $length = 999999;
        $delimiter = $this->delimiter;

        // Open file
        ini_set( "auto_detect_line_endings", true ); // adds some fault protection for csv errors.
        $resource = $this->fopen_utf8( $this->filename );

        // Check file for error
        if ( $resource == 0 ) {
            $this->error = "Loading file error!";
            $this->step = 1;
            return new WP_Error( 'failed_to_get_file', 'Failed to get the csv file.' );
        }

        // Insert each line as new record
        while ( $keys = fgetcsv( $resource, $length, $delimiter ) ) {

            if ( $i == 0 ) {
                // skip first line
                $str = implode( "", $keys );
                trim( $str );
                if ( mb_strlen( $str ) === 0 ) { // if first line empty, skip back and process the next line as first line.
                    continue;
                }
            }
            else {

                // Parse mapped data
                $args = [];
                foreach ( $this->mapped as $item => $value ) {
                    $args[ $value ] = $keys[ $item ];
                }

                // Add post type
                $args['post_type'] = $this->insertype;

                // Load and launch async insert
                try {
                    $insert_location = new Disciple_Tools_Async_Insert_Location();
                    $insert_location->launch( $args );

                } catch ( Exception $e ) {
                    return new WP_Error( 'async_insert_error', 'Failed to launch async insert process' );
                }
            }
            $i++;
        }

        // Close and Clean up
        fclose( $resource );
        ini_set( "auto_detect_line_endings", false );

        // Return report
        return true;
    }

    /**
     * File opener
     *
     * @param $filename
     *
     * @return bool|int|resource
     */
    public function fopen_utf8( $filename )
    {
        if ( ! file_exists( $filename ) || ! is_readable( $filename ) ) {
            return 0;
        }
        $encoding = '';
        $handle = fopen( $filename, 'r' );
        $bom = fread( $handle, 2 );
        rewind( $handle );

        if ( $bom === chr( 0xff ) . chr( 0xfe ) || $bom === chr( 0xfe ) . chr( 0xff ) ) {
            // UTF16 Byte Order Mark present
            $encoding = 'UTF-16';
        }

        $bytes = fread( $handle, 3 );
        if ( $bytes != pack( 'CCC', 0xef, 0xbb, 0xbf ) ) {
            rewind( $handle );
        }
        if ( $encoding != '' ) {
            stream_filter_append( $handle, 'convert.iconv.' . $encoding . '/UTF-8' );
        }

        return ( $handle );
    }

    /**
     * @param $name
     */
    public function get_mapping_drop_down( $name ) {

        switch ( $this->insertype ) {
            case 'locations':
                $fields = [
                    [
                        'key' => 'post_title',
                        'label' => 'Title',
                    ],
                    [
                        'key' => 'address',
                        'label' => 'Address',
                    ],
                    [
                        'key' => 'post_parent',
                        'label' => 'Parent Location',
                    ],
                    [
                        'key' => 'country',
                        'label' => 'Country',
                    ],
                    [
                        'key' => 'reference_id',
                        'label' => 'Reference ID',
                    ],
                    [
                        'key' => 'ignore',
                        'label' => '--Ignore--',
                    ]
                ];

                echo '<option value="">Select...</option>';

                foreach ( $fields as $field ) {
                    echo '<option value="' . esc_attr( $field['key'] ) . '"';
                    if ( $field['key'] == $name ) {
                        echo ' selected';
                    }
                    echo '>'. esc_attr( $field['label'] ) .'</option>';
                }

                break;
            default:
                break;
        }

    }

    /**
     * Get post test
     *
     * @param $postvar
     * @param $postval
     *
     * @return bool
     */
    public function get_post( $postvar, &$postval )
    {
        if ( ! isset( $_POST[ $postvar ] ) || ! isset( $_POST['_csv_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['_csv_nonce'] ) ), 'import_csv' ) ) {
            return false;
        }
        $postval = sanitize_key( wp_unslash( $_POST[ $postvar ] ) );
        if ( $postval == '' ) {
            return false;
        }

        return true;
    }

    /**
     * Parse the header and rows
     *
     * @return array|bool
     */
    public function get_header_and_rows()
    {
        /**
         * Get CSV Headers
         * creates $header and $rows variables
         */
        $i = 0;
        $length = 9999999;
        $delimiter = $this->delimiter;
        $resource = $this->fopen_utf8( $this->filename );

        if ( $resource == 0 ) {
            return false;
        }

        $headers = [];
        $rows = [];

        while ( $keys = fgetcsv( $resource, $length, $delimiter ) ) {
            $str = implode( "", $keys );
            trim( $str );
            if ( mb_strlen( $str ) === 0 ) {
                continue;
            }
            if ( $i == 0 ) {
                $headers = $keys; // splits first row into header
            } else {
                array_push( $rows, $keys ); // loads 5 sample rows
            }
            if ( $i == 5 ) {
                break;
            }
            $i++;
        }

        // close header counter script
        fclose( $resource );

        // get number of columns
        $number_of_fields = count( $headers );

        set_transient( $this->onumberfields, $number_of_fields, 12 * HOUR_IN_SECONDS );

        return [
        'headers'          => $headers,
        'rows'             => $rows,
        'number_of_fields' => $number_of_fields,
        ];
    }

    /**
     * @return int
     */
    public function count_rows() {
        // get number of rows
        $csv = file( $this->filename, FILE_SKIP_EMPTY_LINES );
        $rows_count = count( $csv ) - 1;

        set_transient( 'dt_import_csv_rows_count', $rows_count, 1 * HOUR_IN_SECONDS );
        $this->count_rows = $rows_count;

        return $rows_count;
    }

    /**
     * Check if the post exists
     *
     * @param $postvar
     * @param $postval
     *
     * @return bool
     */
    public function check_is_post( $postvar, $postval )
    {
        if ( ! isset( $_POST[ $postvar ] ) || ! isset( $_POST['_csv_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['_csv_nonce'] ) ), 'import_csv' ) ) {
            return false;
        }
        if ( $_POST[ $postvar ] == $postval || ! isset( $_POST['_csv_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['_csv_nonce'] ) ), 'import_csv' ) ) {
            return true;
        }

        return false;
    }

    /**
     * Check for duplicate mappings and build mappings variable
     */
    public function check_mappings() {

        $columns = get_transient( $this->onumberfields );

        $alloptions = [];
        for ( $i = 0; $i < $columns; $i++ ) {
            $val = '';
            if ( $this->get_post( "field$i", $val ) ) {
                if ( ! in_array( $val, $alloptions ) ) {
                    $alloptions[] = $val;
                } else {
                    $this->error = "Post field(s) mapped more than once !";
                    $this->step = 2;

                    return new WP_Error( 'failed_post_fields', 'Post field(s) mapped more than once!' );
                }
                $this->mapped[ $i ] = $val;
            }
        }
        return true;
    }

    /**
     * Verify required fields
     *
     * @return bool|\WP_Error
     */
    public function check_required_fields() {

        // Check for presence of title and content
        switch ( $this->insertype ) {

            case 'locations':

                $title = false;
                $address = false;
                $parent = false;
                $country = false;

                foreach ( $this->mapped as $key => $value ) {
                    if ( $value == 'post_title' ) {
                        $title = true;
                    }
                    if ( $value == 'address' ) {
                        $address = true;
                    }
                    if ( $value == 'post_parent' ) {
                        $parent = true;
                    }
                    if ( $value == 'country' ) {
                        $country = true;
                    }
                }

                if ( ! $title || ! $address || ! $parent || ! $country ) {
                    $this->error = "Not all mandatory fields mapped!";
                    $this->step = 2;

                    return new WP_Error( 'not_all_mandatory_fields', 'Not all mandatory fields mapped!' );
                }
                return true;

                break;

            default:
                $this->error = "Post type not selected.";
                $this->step = 1;

                return new WP_Error( 'post_type_not_selected', 'Post-type not selected.' );
                break;
        }
    }

    public function remove_temp_file()
    {
        // Delete temporary file
        if ( file_exists( $this->filename ) ) {
            @unlink( $this->filename );
        }
    }

}
Disciple_Tools_Import_CSV_Tab::instance();
