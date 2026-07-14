<?php
require_once __DIR__ . '/../tests/dt-workflows/unit-test-boolean-update.php';

$test = new Test_Workflows_Boolean_Update();
$test->test_action_update_boolean();
echo "Test passed!\n";
?>
