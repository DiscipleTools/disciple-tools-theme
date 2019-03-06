<?php

if ( defined( 'ABSPATH' ) ) {
    return; // return unless accessed directly
}
if ( ! function_exists( 'dt_write_log' ) ) {
    /**
     * A function to assist development only.
     * This function allows you to post a string, array, or object to the WP_DEBUG log.
     *
     * @param $log
     */
    function dt_write_log( $log ) {
        if ( true === WP_DEBUG ) {
            if ( is_array( $log ) || is_object( $log ) ) {
                error_log( print_r( $log, true ) );
            } else {
                error_log( $log );
            }
        }
    }
}
// @codingStandardsIgnoreLine
require( $_SERVER[ 'DOCUMENT_ROOT' ] . '/wp-load.php' ); // loads the wp framework when called
require '../vendor/autoload.php';

$places = [];
$results = $wpdb->get_results("
        SELECT 
	ID as id, 
	post_title, 
	(SELECT post_title FROM wp_3_posts WHERE ID IN (SELECT p2p_to FROM wp_3_p2p WHERE p2p_from = p.ID )) as location_name,
	(SELECT meta_value FROM wp_3_postmeta WHERE post_id IN (SELECT p2p_to FROM wp_3_p2p WHERE p2p_from = p.ID ) AND meta_key = 'latitude') as latitude,
	(SELECT meta_value FROM wp_3_postmeta WHERE post_id IN (SELECT p2p_to FROM wp_3_p2p WHERE p2p_from = p.ID ) AND meta_key = 'longitude') as longitude,
	(SELECT meta_value FROM wp_3_postmeta WHERE post_id IN (SELECT p2p_to FROM wp_3_p2p WHERE p2p_from = p.ID ) AND meta_key = 'country_short_name') as country_code,
	(SELECT meta_value FROM wp_3_postmeta WHERE post_id IN (SELECT p2p_to FROM wp_3_p2p WHERE p2p_from = p.ID ) AND meta_key = 'admin1_short_name') as admin1_short_name,
	'none' as members,
	'none' as status,
	'none' as privacy
FROM wp_3_posts as p
WHERE post_type = 'groups' 
	AND post_status = 'publish'
      ",
ARRAY_A);


use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// build spreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setCellValue( 'A1', 'ID' );
$sheet->setCellValue( 'B1', 'Name' );
$sheet->setCellValue( 'C1', 'Location' );
$sheet->setCellValue( 'D1', 'Latitude' );
$sheet->setCellValue( 'E1', 'Longitude' );
$sheet->setCellValue( 'F1', 'Country' );
$sheet->setCellValue( 'G1', 'State' );
$sheet->setCellValue( 'H1', 'Members' );
$sheet->setCellValue( 'I1', 'Status' );
$sheet->setCellValue( 'J1', 'Privacy' );

$row_number = 2;
foreach ( $results as $result ) {
    $sheet->setCellValue( 'A'.$row_number, $result['id'] );
    $sheet->setCellValue( 'B'.$row_number, $result['post_title'] );
    $sheet->setCellValue( 'C'.$row_number, $result['location_name'] );
    $sheet->setCellValue( 'D'.$row_number, $result['latitude'] );
    $sheet->setCellValue( 'E'.$row_number, $result['longitude'] );
    $sheet->setCellValue( 'F'.$row_number, $result['country_code'] );
    $sheet->setCellValue( 'G'.$row_number, $result['admin1_code'] );
    $sheet->setCellValue( 'H'.$row_number, $result['members'] );
    $sheet->setCellValue( 'I'.$row_number, $result['status'] );
    $sheet->setCellValue( 'J'.$row_number, $result['privacy'] );
    $row_number++;
}

// create temporary file
$writer = new Xlsx( $spreadsheet );
$writer->save( 'ishare-report.xlsx' );

// download file
$file = "ishare-report.xlsx";
header( 'Content-Description: File Transfer' );
header( 'Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' );
header( 'Content-Disposition: attachment; filename='.basename( $file ) );
header( 'Content-Transfer-Encoding: binary' );
header( 'Expires: 0' );
header( 'Cache-Control: must-revalidate' );
header( 'Pragma: public' );
header( 'Content-Length: ' . filesize( $file ) );
ob_clean();
flush();
readfile( $file );

// delete temporary file
unlink( $file );


