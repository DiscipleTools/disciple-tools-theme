<?php
require_once __DIR__ . '/../../dt-workflows/workflows-execution-handler.php';

class Test_Workflows_Boolean_Update_Fix extends PHPUnit\Framework\TestCase {
    public function test_action_update_boolean_fix() {
        $method = new ReflectionMethod( 'Disciple_Tools_Workflows_Execution_Handler', 'action_update' );
        $method->setAccessible( true );

        // Test "true"
        $result = $method->invoke( null, 'boolean', 'test_field', 'true' );
        $this->assertEquals( [ 'test_field' => true ], $result );

        // Test "false"
        $result = $method->invoke( null, 'boolean', 'test_field', 'false' );
        $this->assertEquals( [ 'test_field' => false ], $result );
    }
}
