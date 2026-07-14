<?php
require_once __DIR__ . '/../dt-workflows/workflows-execution-handler.php';

class Test_Boolean_Fix extends PHPUnit\Framework\TestCase {
    public function test_action_update_boolean_fix() {
        $method = new ReflectionMethod( 'Disciple_Tools_Workflows_Execution_Handler', 'action_update' );
        $method->setAccessible( true );

        // Test "true"
        $result = $method->invoke( null, 'boolean', 'field_id', 'true' );
        $this->assertEquals( [ 'field_id' => true ], $result, 'Failed to update boolean to true' );

        // Test "false"
        $result = $method->invoke( null, 'boolean', 'field_id', 'false' );
        $this->assertEquals( [ 'field_id' => false ], $result, 'Failed to update boolean to false' );
    }
}
