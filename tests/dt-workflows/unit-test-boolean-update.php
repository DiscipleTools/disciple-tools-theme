<?php
require_once dirname( __DIR__, 2 ) . '/dt-workflows/workflows-execution-handler.php';

class Test_Workflows_Boolean_Update extends PHPUnit\Framework\TestCase {
    public function test_action_update_boolean() {
        $method = new ReflectionMethod( 'Disciple_Tools_Workflows_Execution_Handler', 'action_update' );
        $method->setAccessible( true );

        // Test "true"
        $result = $method->invoke( null, 'boolean', 'field_id', 'true' );
        $this->assertEquals( [ 'field_id' => true ], $result );

        // Test "false"
        $result = $method->invoke( null, 'boolean', 'field_id', 'false' );
        $this->assertEquals( [ 'field_id' => false ], $result );
    }
}
