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
class Disciple_Tools_Import_Export_Tab
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

        // persistent variables for the processing timer
        $this->time_start = 0;

        dt_write_log( $_POST );
    }

    /**
     * Primary page content
     */
    public function content()
    {

        $this->handle_pages(); // process page data

        switch ( $this->step ) {
            case '1':
                dt_write_log( 'made it to case 1');
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
                dt_write_log('made it to case 2');
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
                            <!-- Body of table -->
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
                                $i++;
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
                                                <?php
                                                if ( $this->insertype == 'locations' ) {
                                                    echo "<option value=\"\">Select...</option>
                                                          <option value=\"post_title\">Title </option>
                                                          <option value=\"post_content\">Post Content </option>
                                                          <option value=\"address\">Address </option>
                                                          <option value=\"parent_title\">Parent Title </option>
                                                          <option value=\"country\">Country </option>";
                                                } // end if statement
                                                ?>
                                            </select>
                                        </div>

                                    <?php } // end for statement
                                    ?>

                                </div>
                            </div>
                        </div>

                        <!-- Type @todo remove unnecissary type form -->

                        <div id="formatdiv" class="postbox" style="max-width:600px; display: none;">
                            <h3 class="hndle"
                                style="cursor:auto;padding:10px;"><?php _e( 'Use these settings if "not found\mapped" in the .csv file', 'disciple_tools' ) ?></h3>
                            <div class="inside">
                                <div>
                                    <?php _e( 'Post Status:', 'disciple_tools' ) ?>
                                    <select name="post_status_user">
                                        <option value="draft">Draft</option>
                                        <option value="publish" selected>Publish</option>
                                        <option value="private">Private</option>
                                        <option value="pending">Pending</option>
                                    </select>&nbsp;&nbsp;&nbsp;&nbsp;
                                </div>
                            </div>
                        </div>


                        <input type="submit" class="button" name="submitback" value="Back"/>&nbsp;&nbsp;&nbsp;
                        <input type="submit" class="button" name="submit" value="Next >"/>
                        <div style="/*float:right;*/background-color:#FFFFE0;border: 1px solid #E6DB55;padding:10px;">After
                            clicking <b>Next</b>, the import process may take some time to complete. Do not navigate to
                            another page or hit Refresh!
                        </div>

                    </form>
                </div>


                <?php
                break;

            case '3':
                dt_write_log('made it to case 3');
                $results = $this->insert_csv();
                if ( is_wp_error( $results ) ) {
                    print $results->get_error_message();
                } else {
                    /** Print Report to Screen */
                    echo '<div class="wrap"><h1>Report</h1><br/>';
                    echo '<div class="updated fade">';
                    echo sprintf( " Posts <b>imported</b> - <b>%d</b><br><br>", esc_attr( $results['imported'] ) );
                    echo sprintf( " Posts <b>skipped</b> - <b>%d</b><br><br>", esc_attr( $results['skipped'] ) );
                    echo sprintf( "Finished in <b>%.2f</b> seconds.", esc_attr( $results['execution_time'] ) );
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
     * Handle Pages is the file and page processor
     */
    public function handle_pages()
    {
        dt_write_log( 'HANDLE PAGES' );
        dt_write_log( '2 - ' . $this->check_is_post( '_csv_panel', 'post_for_step_2' ) );
        dt_write_log( '3 - ' . $this->check_is_post( '_csv_panel', 'post_for_step_3' ) );

        // page 2
//        if ( $this->check_is_post( '_csv_panel', 'post_for_step_2' ) ) { // if page 2
        if ( $this->check_is_post( '_csv_panel', 'post_for_step_2' ) ) { // if page 2

            dt_write_log( 'handle_pages - page 2' );

            if ( empty( $_FILES['csv_import']['tmp_name'] ) ) {
                dt_write_log( 'No file uploaded' );
                $this->error = "No file uploaded";
                $this->step = 1;

            }
            else {

                move_uploaded_file( $_FILES['csv_import']['tmp_name'], $this->filename ); // locally store uploaded file
                dt_write_log( 'handle_pages - moved file' );
                if ( ! file_exists( $this->filename ) || ! is_readable( $this->filename ) ) { // validate file

                    $this->error = "Can not open/read uploaded file.";
                    $this->step = 1;

                } else {

                    $this->insertype = $_POST['post_format']; // capture selected post type
                    set_transient( $this->otype, $this->insertype, 12 * HOUR_IN_SECONDS );
                    $this->step = 2;
                    dt_write_log( 'handle_pages - set posttype and transient' );
                }
            }
        // back button called from page 2
        } elseif ( $this->check_is_post( '_csv_panel', 'post_for_step_3' ) && $this->check_is_post( 'submitback', 'Back' ) ) {
            dt_write_log( 'handle_pages - back button on page 2' );
            $this->step = 1;

        // page 3
        } elseif ( $this->check_is_post( '_csv_panel', 'post_for_step_3' ) ) { // if page 3
            dt_write_log( 'handle_pages - page 3' );
            $this->step = 3;

        } else { // if neither condition above, then return page 1
            dt_write_log( 'handle_pages - default to 1' );
            $this->step = 1;
        }
    }

    /**
     * @return array|WP_Error
     */
    public function insert_csv()
    {
        dt_write_log( 'START CSV' );
        $this->insertype = get_transient( $this->otype );

        $columns = get_transient( $this->onumberfields );

        $alloptions = [];

        for ( $i = 0; $i < $columns; $i++ ) {
            $val = '';
            if ( $this->get_post( "field$i", $var ) ) {
                if ( ! in_array( $val, $alloptions ) ) {
                    $alloptions[] = $val;
                } else {
                    $this->error = "Post field(s) mapped more than once !";
                    $this->step = 2;

                    return;
                }
                $this->mapped[ $i ] = $val;
            }
        }

        // Check for title and content
        $hast = false; // has title
        $hasc = false; // has content

        foreach ( $this->mapped as $key => $value ) {
            if ( $value == 'post_title' ) {
                $hast = true;
            }
            if ( $value == 'post_content' ) {
                $hasc = true;
            }
        }

        if ( ! $hasc || ! $hast ) {
            $this->error = "Mandatory fields Post Title and\or Post Content not mapped!";
            $this->step = 2;

            return;
        }

        $this->timer( 'start' ); //starts processing timer
        $i = 0;
        $skipped = 0;
        $imported = 0;
        $length = 999999;
        $delimiter = $this->delimiter;

        ini_set( "auto_detect_line_endings", true ); // adds some fault protection for csv errors.
        $resource = $this->fopen_utf8( $this->filename );

        if ( $resource == 0 ) {
            return new WP_Error( 'failed_to_get_file', 'Failed to get the csv file.' );
        }

        while ( $keys = fgetcsv( $resource, $length, $delimiter ) ) {

            if ( $i == 0 ) { // skip first line
                $str = implode( "", $keys );
                trim( $str );
                if ( mb_strlen( $str ) === 0 ) {
                    continue;
                }
            }
            else {
                $data = [];
                foreach ( $this->mapped as $item => $value ) {
                    $data[ $value ] = $keys[ $item ];
                }

                // Build and Insert new post
                $new_post = [];
                $this->get_values_from_array( $data, $new_post );

                $id = wp_insert_post( $new_post ); // wp insert statement

                unset( $new_post );
                unset( $data );
                if ( $id ) {
                    $imported++;
                } else {
                    $skipped++;
                }
            }
            $i++;
        }

        fclose( $resource );
        ini_set( "auto_detect_line_endings", false );
        $exec_time = $this->timer( 'stop' );

        $this->remove_temp_file(); // clear out the temp file

        return [
        'imported'       => $imported,
        'skipped'        => $skipped,
        'execution_time' => $exec_time,
        ];
    }

    public function remove_temp_file()
    {
        // Delete temporary file
        if ( file_exists( $this->filename ) ) {
            @unlink( $this->filename );
        }
    }

    /**
     * Get values from array
     *
     * @param $arr_source
     * @param $arr_dest
     */
    public function get_values_from_array( &$arr_source, &$arr_dest )
    {
        if ( ! isset( $arr_source['post_status'] ) ) {
            $post_status = '';
            $this->get_post( 'post_status_user', $post_status );

            $arr_dest['post_status'] = $post_status;
        }
        else {
            $from_file = $arr_source['post_status'];
            strtolower( $from_file );
            trim( $from_file );
            if ( $from_file != 'publish' || $from_file != 'draft' || $from_file != 'pending' || $from_file != 'private' ) {
                $post_status = '';
                $this->get_post( 'post_status_user', $post_status );
                $arr_dest['post_status'] = $post_status;
            } else {
                $arr_dest['post_status'] = $from_file;
            }
        }

        if ( isset( $arr_source['post_tags'] ) ) {
            $arr_dest['tags_input'] = $arr_source['post_tags'];
        }

        $arr_dest['post_title'] = wp_strip_all_tags( $arr_source['post_title'] );
        $arr_dest['post_content'] = convert_chars( $arr_source['post_content'] );
        $arr_dest['post_type'] = $this->insertype;

        if ( isset( $arr_source['post_excerpt'] ) ) {
            $arr_dest['post_excerpt'] = $arr_source['post_excerpt'];
        }

        if ( isset( $arr_source['post_slug'] ) ) {
            $arr_dest['post_name'] = $arr_source['post_slug'];
        }

        if ( isset( $arr_source['post_date'] ) ) {
            $timestamp = strtotime( $arr_source['post_date'] );
            if ( $timestamp !== false ) {
                $arr_dest['post_date'] = date( 'Y-m-d H:i:s', $timestamp );
            }
        }
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
        if ( ! $_POST[ $postvar ] == $postval ) {
            return false;
        }

        return true;
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
        if ( ! isset( $_POST[ $postvar ] ) ) {
            return false;
        }
        if ( '' == $_POST[ $postvar ] ) {
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

        $number_of_fields = count( $headers );

        set_transient( $this->onumberfields, $number_of_fields, 12 * HOUR_IN_SECONDS );

        return [
        'headers'          => $headers,
        'rows'             => $rows,
        'number_of_fields' => $number_of_fields,
        ];
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