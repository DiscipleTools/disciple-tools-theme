<?php
require_once dirname( __DIR__, 2 ) . '/dt-workflows/workflows-execution-handler.php';

class Unit_Test_Boolean_Update extends PHPUnit\Framework\TestCase {
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

    public function test_condition_equals_boolean() {
        $method = new ReflectionMethod( 'Disciple_Tools_Workflows_Execution_Handler', 'condition_equals' );
        $method->setAccessible( true );

        // Test "true" equals "true"
        $this->assertTrue( $method->invoke( null, 'boolean', 'true', 'true' ) );
        // Test "false" equals "false"
        $this->assertTrue( $method->invoke( null, 'boolean', 'false', 'false' ) );
        // Test "true" equals "false"
        $this->assertFalse( $method->invoke( null, 'boolean', 'true', 'false' ) );
    }

    public function test_condition_not_equals_boolean() {
        $method = new ReflectionMethod( 'Disciple_Tools_Workflows_Execution_Handler', 'condition_not_equals' );
        $method->setAccessible( true );

        // Test "true" not equals "false"
        $this->assertTrue( $method->invoke( null, 'boolean', 'true', 'false' ) );
        // Test "true" not equals "true"
        $this->assertFalse( $method->invoke( null, 'boolean', 'true', 'true' ) );
        // Test "false" not equals "false"
        $this->assertFalse( $method->invoke( null, 'boolean', 'false', 'false' ) );
    }
}
