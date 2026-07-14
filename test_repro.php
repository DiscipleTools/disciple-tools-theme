<?php
define( 'ABSPATH', '/tmp/' );
require_once '/workspace/repo/dt-workflows/workflows-execution-handler.php';

// Mocking required dependencies if any, or just testing the function if it's static
class Mock_Handler extends Disciple_Tools_Workflows_Execution_Handler {
    public static function call_action_update( $field_type, $field_id, $value ) {
        $method = new ReflectionMethod( 'Disciple_Tools_Workflows_Execution_Handler', 'action_update' );
        $method->setAccessible( true );
        return $method->invoke( null, $field_type, $field_id, $value );
    }
}

function assert_bool( $type, $id, $val, $expected ) {
    $result = Mock_Handler::call_action_update( $type, $id, $val );
    echo "Testing type: $type, id: $id, val: $val. Expected: " . ( $expected ? 'true' : 'false' ) . '. Result: ' . ( $result[$id] ? 'true' : 'false' ) . "\n";
    if ( $result[$id] === $expected ) {
        echo "PASS\n";
    } else {
        echo "FAIL\n";
    }
}

assert_bool( 'boolean', 'test_bool', 'true', true );
assert_bool( 'boolean', 'test_bool', 'false', false );
assert_bool( 'boolean', 'test_bool', 1, true );
assert_bool( 'boolean', 'test_bool', 0, false );
?>
