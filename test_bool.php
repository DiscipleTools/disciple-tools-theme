<?php
$val1 = 'true';
$val2 = 'false';
echo 'val1: ' . ( filter_var( $val1, FILTER_VALIDATE_BOOLEAN ) ? 'true' : 'false' ) . "\n";
echo 'val2: ' . ( filter_var( $val2, FILTER_VALIDATE_BOOLEAN ) ? 'true' : 'false' ) . "\n";
echo 'bool cast val1: ' . ( (bool) $val1 ? 'true' : 'false' ) . "\n";
echo 'bool cast val2: ' . ( (bool) $val2 ? 'true' : 'false' ) . "\n";
?>
