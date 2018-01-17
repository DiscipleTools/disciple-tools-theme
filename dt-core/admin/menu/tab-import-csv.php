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
class Disciple_Tools_Import_CSV
{

    /**
     * Disciple_Tools_Import_Export_Tab constructor.
     */
    public function __construct()
    {


        $this->odelimiter = "dt_import_odelimiter";
        $this->onumberfields = "dt_import_onumberfields";
        $this->otype = "dt_import_otype";

        $this->step = 1;
        $this->error = '';
        $this->filename = dirname( __FILE__ ) . '/myfile.csv'; // create temp file
        $this->delimiter = ',';
        $this->column_count = 0;
        $this->mapped = [];
        $this->insertype = '';
        $this->results = [];

        // persistent variables for the processing timer
        $this->time_start = 0;

        dt_write_log( $_POST );
    }

    /**
     * Primary page content
     */
    public function wizard()
    {

        // Routing
        if ( $this->check_is_post( '_csv_panel', 'post_for_step_2' ) ) {
            // page 2
            if ( empty( $_FILES['csv_import']['tmp_name'] ) ) {
                $this->error = "No file uploaded";
                $this->step = 1;
            }
            else {
                move_uploaded_file( $_FILES['csv_import']['tmp_name'], $this->filename ); // locally store uploaded file

                if ( ! file_exists( $this->filename ) || ! is_readable( $this->filename ) ) { // validate file
                    $this->error = "Can not open/read uploaded file.";
                    $this->step = 1;
                } else {
                    $this->insertype = $_POST['post_format']; // capture selected post type
                    set_transient( $this->otype, $this->insertype, 12 * HOUR_IN_SECONDS );
                    $this->step = 2;
                }
            }
        } elseif ( $this->check_is_post( '_csv_panel', 'post_for_step_3' ) && $this->check_is_post( 'submitback', 'Back' ) ) {
            // back button
            $this->step = 1;
        } elseif ( $this->check_is_post( '_csv_panel', 'post_for_step_3' ) ) {
            // page 3
            $this->step = 3;
            $process_form = $this->process_form();
        } else {
            // page 1
            $this->step = 1;
        }

        // Wizard Pages
        switch ( $this->step ) {
            case '1':
                ?>
                <div class="wrap">
                    <h2>
                        <?php _e( 'Import CSV Files', 'disciple_tools' ) ?>
                    </h2>
                    <br/>
                    <?php if ( $this->error !== '' ) : ?>
                        <div class="error">
                            <?php esc_attr_e( $this->error ); ?>
                        </div>
                    <?php endif; ?>

                    <form class="add:the-list: validate" method="post" enctype="multipart/form-data">
                        <input name="_csv_panel" type="hidden" value="post_for_step_2"/>

                        <!-- File input -->
                        <div>
                            <label for="csv_import">
                                <?php _e( 'Select a CSV file:', 'disciple_tools' ) ?>
                            </label><br/>
                            <input name="csv_import" id="csv_import" type="file" value=""/>
                        </div>
                        <!-- Type -->
                        <div>
                            <div id="formatdiv" class="postbox" style="display: block;max-width:350px;">
                                <h3 class="hndle"
                                    style="cursor:auto;padding:10px;"><?php _e( 'Select Import Type', 'disciple_tools' ) ?></h3>
                                <div class="inside">
                                    <div id="post-formats-select">

                                        <input id="post-format-page" class="post-format" type="radio" value="locations"
                                               name="post_format" checked>
                                        <label for="post-format-page">
                                            &nbsp;&nbsp;<?php _e( 'Locations', 'disciple_tools' ) ?>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="submit">
                            <button type="submit" class="button" >Next ></button>
                        </div>
                    </form>
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
                    <h2><?php _e( 'Import CSV Files', 'disciple_tools' ) ?></h2>
                    <?php if ( $this->error !== '' ) : ?>
                        <div class="error">
                            <?php esc_attr_e( $this->error ); ?>
                        </div>
                    <?php endif; ?>
                    <h3><?php _e( 'Step 2 - Map Fields', 'disciple_tools' ) ?></h3>
                    <p><?php _e( 'Data preview fields', 'disciple_tools' ) ?></p>


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
                        <input name="_csv_panel" type="hidden" value="post_for_step_3"/>

                        <!-- Type -->
                        <div id="formatdiv" class="postbox" style="max-width:600px;">
                            <h3 class="hndle" style="cursor:auto;padding:10px;">
                        <span>
                            <?php _e( 'Map fields from the .csv file to post fields', 'disciple_tools' ) ?>
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
                                            <select name="field<?php echo $i ?>">
                                                <?php $this->get_mapping_drop_down( $headers[ $i ] ) ?>
                                            </select>
                                        </div>

                                    <?php } // end for statement
                                    ?>

                                </div>
                            </div>
                        </div>

                        <button type="submit" class="button" name="submitback" value="Back">Back</button>
                        <button type="submit" class="button">Next ></button>
                        <br>
                        <div style="/*float:right;*/background-color:#FFFFE0;border: 1px solid #E6DB55;padding:10px;">After
                            clicking <b>Next</b>, the import process may take some time to complete. Do not navigate to
                            another page or hit Refresh!
                        </div>

                    </form>
                </div>


                <?php
                break;

            case '3':
               

                if ( is_wp_error( $process_form ) ) {
                    print $process_form->get_error_message();
                } else {
                    /** Print Report to Screen */
                    echo '<div class="wrap"><h1>Report</h1><br/>';
                    echo '<div class="updated fade">';
                    echo sprintf( " Posts <b>imported</b> - <b>%d</b><br><br>", esc_attr( $process_form['imported'] ) );
                    echo sprintf( " Posts <b>skipped</b> - <b>%d</b><br><br>", esc_attr( $process_form['skipped'] ) );
                    echo sprintf( "Finished in <b>%.2f</b> seconds.", esc_attr( $process_form['execution_time'] ) );
                    echo '</div>';
                    echo '</div>'; // end div wrapper
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
        if( is_wp_error( $check_mappings ) ) {
            return new WP_Error('confirm_mappings', $check_mappings->get_error_message() );
        }

        // Check required fields
        $check_required_fields = $this->check_required_fields();
        if( is_wp_error( $check_required_fields ) ) {
            return new WP_Error('check_required_fields', $check_required_fields->get_error_message() );
        }

        // Insert records
        $insert_records = $this->insert_records();
        if( is_wp_error( $insert_records ) ) {
            return new WP_Error('insert_records_fail', $insert_records->get_error_message() );
        }

        // Clean up temp file
        $this->remove_temp_file();

        // Return processing results
        return $insert_records;
    }

    /**
     * @return array|\WP_Error
     */
    public function insert_records() {

        // Initialize variables
        $this->timer( 'start' ); //starts processing timer
        $i = 0;
        $skipped = 0;
        $imported = 0;
        $length = 999999;
        $delimiter = $this->delimiter;

        // Open file
        ini_set( "auto_detect_line_endings", true ); // adds some fault protection for csv errors.
        $resource = $this->fopen_utf8( $this->filename );

        // Check file for error
        if ( $resource == 0 ) {
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

                $args = $this->prepare_new_post_array( $keys ); // build post args
                $id = wp_insert_post( $args ); // wp insert statement

                if ( $id ) {
                    $imported++;
                } else {
                    $skipped++;
                }
            }
            $i++;
        }

        // Close and Clean up
        fclose( $resource );
        ini_set( "auto_detect_line_endings", false );
        $exec_time = $this->timer( 'stop' );

        // Return report
        return [
            'imported'       => $imported,
            'skipped'        => $skipped,
            'execution_time' => $exec_time,
        ];
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
     * Get values from array
     *
     * @param $keys
     * @return array
     */
    public function prepare_new_post_array( $keys )
    {
        // Parse mapped data
        $mapped_from_form = [];
        foreach ( $this->mapped as $item => $value ) {
            $mapped_from_form[ $value ] = $keys[ $item ];
        }

        // prepare standard fields
        $args['post_title'] = wp_strip_all_tags( $mapped_from_form['post_title'] );
        $args['post_content'] = '';
        $args['post_type'] = $this->insertype;
        $args['post_status'] = 'publish';

        if ( isset( $mapped_from_form['post_excerpt'] ) ) {
            $args['post_excerpt'] = $mapped_from_form['post_excerpt'];
        }

        if ( isset( $mapped_from_form['post_slug'] ) ) {
            $args['post_name'] = $mapped_from_form['post_slug'];
        }

        if ( isset( $mapped_from_form['post_date'] ) ) {
            $timestamp = strtotime( $mapped_from_form['post_date'] );
            if ( $timestamp !== false ) {
                $args['post_date'] = date( 'Y-m-d H:i:s', $timestamp );
            }
        }

        switch( $this->insertype ) {
            case 'locations':
                // lookup post parent id
                if ( isset( $mapped_from_form['post_parent'] ) ) {
                    $parent_id = get_page_by_title( $mapped_from_form['post_parent'], OBJECT, 'locations');
                    if( ! is_null( $parent_id ) ) {
                        $args['post_parent'] = $parent_id->ID;
                    }
                }

                // geocode address
                if ( isset( $mapped_from_form['address'] ) ) {

                    if ( isset( $mapped_from_form['country'] ) ) {
                        $mapped_from_form['address'] .= ', ' . $mapped_from_form['country'];
                    }

                    $results = Disciple_Tools_Google_Geocode_API::query_google_api( $mapped_from_form['address'], 'all_points' );
                    if( $results ) {
                        $args['meta_input'] = [
                            'lat' => $results['lat'],
                            'lng' => $results['lng'],
                            'northeast_lat' => $results['northeast_lat'],
                            'northeast_lng' => $results['northeast_lng'],
                            'southwest_lat' => $results['southwest_lat'],
                            'southwest_lng' => $results['southwest_lng'],
                            'location_address' => $results['formatted_address'],
                            'location' => $results,
                        ];
                    }
                }

                break;
            default:
                break;
        }

        return $args;
    }

    /**
     * @param $name
     */
    public function get_mapping_drop_down( $name ) {

        switch( $this->insertype ) {
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
                    ]
                ];

                echo '<option value="">Select...</option>';

                foreach( $fields as $field ) {
                    echo '<option value="'. $field['key'].'"';
                    if( $field['key'] == $name ) {
                        echo ' selected';
                    }
                    echo '>'.$field['label'].'</option>';
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
        if ( ! isset( $_POST[ $postvar ] ) )
            return false;
        $postval = $_POST[ $postvar ];
        if ( $postval == '' )
            return false;

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

        $number_of_fields = count( $headers );

        set_transient( $this->onumberfields, $number_of_fields, 12 * HOUR_IN_SECONDS );

        return [
        'headers'          => $headers,
        'rows'             => $rows,
        'number_of_fields' => $number_of_fields,
        ];
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
        if ( ! isset( $_POST[ $postvar ] ) ) {
            return false;
        }
        if ( $_POST[ $postvar ] == $postval ) {
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

                    return new WP_Error('failed_post_fields', 'Post field(s) mapped more than once!');
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
        switch( $this->insertype ) {

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

                    return new WP_Error('not_all_mandatory_fields', 'Not all mandatory fields mapped!' );
                }
                return true;

                break;

            default:
                $this->error = "Post type not selected.";
                $this->step = 1;

                return new WP_Error('post_type_not_selected', 'Post-type not selected.' );
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

    /**
     * Timer
     *
     * @param $switch `use 'start' to start timer, use 'stop' to stop timer, and use no value to reset timer.`
     *
     * @return int|mixed
     */
    public function timer( $switch )
    {
        switch ( $switch ) {
            case 'start':
                return $this->time_start = microtime( true );
                break;

            case 'stop':
                return microtime( true ) - $this->time_start;
                break;

            default:
                return $this->time_start = 0;
                break;
        }
    }

}