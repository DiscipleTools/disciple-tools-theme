<?php

$params = [
    'Input CSV File',
    'Output CSV File',
    'Ignore First Row',
    'Latitude CSV File Index',
    'Longitude CSV File Index',
    'New Output CSV File Column Name',
    'DT Geo-Location API Endpoint',
    'DT Site-Link API Token'
];

function help( $expected_params ): void{
    echo PHP_EOL . 'Please ensure to specify correct parameters in the following order:' . PHP_EOL . PHP_EOL;
    $example = 'E.g. php ' . basename( __FILE__ );

    foreach ( $expected_params as $param ){
        $example .= ' [' . $param . ']';
    }

    $example .= PHP_EOL . PHP_EOL;
    // @phpcs:ignore
    echo ( $example );
}

function get_csv_content( $csv_file ): array{
    $csv = [];
    $handle = fopen( __DIR__ . $csv_file, 'r' );
    if ( $handle !== false ){
        while ( ( $data = fgetcsv( $handle, 0, ',' ) ) !== false ){
            $csv[] = $data;
        }
        fclose( $handle );
    }

    return $csv;
}

function save_csv_content( $csv_file, $content ): bool{
    if ( !empty( $content ) ){
        try {
            $handle = fopen( __DIR__ . $csv_file, 'w' );
            if ( $handle !== false ){
                foreach ( $content as $row ){
                    fputcsv( $handle, $row, ',' );
                }
                fclose( $handle );
                return true;
            }
        } catch ( Exception $e ) {
            // @phpcs:ignore
            echo 'Caught exception: ', ( $e->getMessage() ), PHP_EOL;
            return false;
        }
    }

    return false;
}

/**
 * Process script execution, based on specified arguments.
 */


if ( ( count( $argv ) - 1 ) === count( $params ) ){

    // Extract/Set required parameters.
    $input_csv_file = $argv[1];
    $output_csv_file = $argv[2];
    $ignore_first_row = $argv[3];
    $latitude_idx = $argv[4];
    $longitude_idx = $argv[5];
    $output_csv_file_col_name = $argv[6];
    $dt_url_endpoint = $argv[7];
    $dt_api_token = $argv[8];

    $url_context = stream_context_create( [
        'http' => [
            'method' => 'GET',
            'header' => 'Authorization: Bearer ' . $dt_api_token
        ]
    ] );

    // Fetch input csv file contents and initial headings.
    // @phpcs:ignore
    echo PHP_EOL . 'Loading contents of file: ' . ( $input_csv_file ) . PHP_EOL;
    $input_csv_file_content = get_csv_content( $input_csv_file );
    $input_csv_file_headings = $input_csv_file_content[0];

    // Set output csv file headings.
    $output_csv_file_content[0] = $input_csv_file_headings;
    $output_csv_file_content[0][] = $output_csv_file_col_name;

    // If required, remove input csv file headings.
    if ( $ignore_first_row ){
        unset( $input_csv_file_content[0] );
    }

    // Iterate over input content, fetching location grid based on available lat/lng values.
    $input_csv_file_row_total = count( $input_csv_file_content );
    $input_csv_file_row_count = 0;
    // @phpcs:ignore
    echo PHP_EOL . 'Processing loaded contents of file: ' . ( $input_csv_file ) . '; which contains ' . ( $input_csv_file_row_total ) . ' records.' . PHP_EOL . PHP_EOL;
    foreach ( $input_csv_file_content as $csv ){
        // @phpcs:ignore
        echo 'Processing ' . ( ++$input_csv_file_row_count ) . ' of ' . ( $input_csv_file_row_total ) . PHP_EOL;

        $grid = [];
        if ( isset( $csv[$latitude_idx], $csv[$longitude_idx] ) ){
            $latitude = $csv[$latitude_idx];
            $longitude = $csv[$longitude_idx];

            // Build dt url endpoint and fetch content.
            $url = $dt_url_endpoint . '?lng=' . $longitude . '&lat=' . $latitude;
            $grid = json_decode( file_get_contents( $url, false, $url_context ), true );
        }

        // Repackage into new output csv record.
        $csv[] = $grid['grid_id'] ?? '';
        $output_csv_file_content[] = $csv;
    }

    // Save updated csv records into specified output file and report outcome.
    // @phpcs:ignore
    echo PHP_EOL . 'Saving updated contents of file: ' . ( $input_csv_file ) . ' to ' . ( $output_csv_file ) . PHP_EOL;
    // @phpcs:ignore
    echo PHP_EOL . ( $output_csv_file ) . ( save_csv_content( $output_csv_file, $output_csv_file_content ) ? ' successfully created.' : ' failed to create.' ) . PHP_EOL . PHP_EOL;

} else {
    help( $params );
}

