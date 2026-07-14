<?php
define( 'ABSPATH', '/tmp' );
require_once '/workspace/repo/dt-workflows/workflows-execution-handler.php';

// Test action_update for boolean
$reflection = new ReflectionClass( 'Disciple_Tools_Workflows_Execution_Handler' );
$method = $reflection->getMethod( 'action_update' );
$method->setAccessible( true );

$value = true; // Boolean true
$result = $method->invoke( null, 'boolean', 'my_bool_field', $value );

var_dump( $result );

$value = false; // Boolean false
$result = $method->invoke( null, 'boolean', 'my_bool_field', $value );

var_dump( $result );
