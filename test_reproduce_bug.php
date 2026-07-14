<?php
define( 'ABSPATH', '/tmp' );
require_once '/workspace/repo/dt-workflows/workflows-execution-handler.php';

$reflection = new ReflectionClass( 'Disciple_Tools_Workflows_Execution_Handler' );
$method = $reflection->getMethod( 'action_update' );
$method->setAccessible( true );

$value = 'false';
$result = $method->invoke( null, 'boolean', 'my_bool_field', $value );

var_dump( $result );
