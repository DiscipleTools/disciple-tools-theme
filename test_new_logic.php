<?php
define( 'ABSPATH', '/tmp' );
require_once '/workspace/repo/dt-workflows/workflows-execution-handler.php';

function test_value( $value ) {
    if ( is_string( $value ) ) {
        return ( $value === 'true' );
    } else {
        return (bool) $value;
    }
}

var_dump( test_value( 'true' ) );
var_dump( test_value( 'false' ) );
var_dump( test_value( true ) );
var_dump( test_value( false ) );
