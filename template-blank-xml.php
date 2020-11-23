<?php
/*
Template Name: XML
*/

header( 'Content-Type: application/xml; charset=utf-8' );

/**
 * Filter to set access to the template
 */
if ( ! apply_filters( 'dt_xml_access', false ) ){
    echo "<?xml version='1.0' encoding='UTF-8'?>";
    echo "<empty>";
    echo "<message>No Access</message>";
    echo "</empty>";
    exit;
}

/**
 * Filter to return JSON payload.
 */
$json = apply_filters( 'dt_xml_content', [] );
echo json_encode( $json );
