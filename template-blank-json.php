<?php
/*
Template Name: JSON
*/

header( 'Content-type: application/json' );

/**
 * Filter to set access to the template
 */
if ( ! apply_filters( 'dt_json_access', false ) ){
    echo json_encode(['status' => 'FAIL'] );
    exit;
}

/**
 * Filter to return JSON payload.
 */
$json = apply_filters( 'dt_json_content', [] );
echo json_encode( $json );
