<?php
define( 'ABSPATH', '/tmp' );
require_once '/workspace/repo/dt-workflows/workflows-execution-handler.php';

$reflection = new ReflectionClass( 'Disciple_Tools_Workflows_Execution_Handler' );
$method = $reflection->getMethod( 'action_update' );
$method->setAccessible( true );

// Test true
$result_true = $method->invoke( null, 'boolean', 'my_bool_field', 'true' );
if ( $result_true['my_bool_field'] !== true ) {
    echo "FAILED true\n";
    var_dump( $result_true );
    exit( 1 );
}

// Test false
$result_false = $method->invoke( null, 'boolean', 'my_bool_field', 'false' );
if ( $result_false['my_bool_field'] !== false ) {
    echo "FAILED false\n";
    var_dump( $result_false );
    exit( 1 );
}

echo 'PASSED';
