<?php
define( 'ABSPATH', __DIR__ );
require_once __DIR__ . '/dt-workflows/workflows-execution-handler.php';

$method = new ReflectionMethod( 'Disciple_Tools_Workflows_Execution_Handler', 'action_update' );
$method->setAccessible( true );

$result1 = $method->invoke( null, 'boolean', 'field_id', 'true' );
var_dump( $result1 );
if ( isset( $result1['field_id'] ) && $result1['field_id'] === true ) {
    echo "Passed true\n";
} else {
    echo "Failed true\n";
}

$result2 = $method->invoke( null, 'boolean', 'field_id', 'false' );
var_dump( $result2 );
if ( isset( $result2['field_id'] ) && $result2['field_id'] === false ) {
    echo "Passed false\n";
} else {
    echo "Failed false\n";
}
?>
