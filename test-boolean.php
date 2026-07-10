<?php
$val1 = 'true';
$val2 = 'false';
$val3 = 'foo';
$val4 = '1';
$val5 = '0';

echo '(bool) filter_var("true", FILTER_VALIDATE_BOOLEAN): ' . ( (bool) filter_var( $val1, FILTER_VALIDATE_BOOLEAN ) ? 'true' : 'false' ) . "\n";
echo '(bool) filter_var("false", FILTER_VALIDATE_BOOLEAN): ' . ( (bool) filter_var( $val2, FILTER_VALIDATE_BOOLEAN ) ? 'true' : 'false' ) . "\n";
echo '(bool) filter_var("foo", FILTER_VALIDATE_BOOLEAN): ' . ( (bool) filter_var( $val3, FILTER_VALIDATE_BOOLEAN ) ? 'true' : 'false' ) . "\n";
echo '(bool) filter_var("1", FILTER_VALIDATE_BOOLEAN): ' . ( (bool) filter_var( $val4, FILTER_VALIDATE_BOOLEAN ) ? 'true' : 'false' ) . "\n";
echo '(bool) filter_var("0", FILTER_VALIDATE_BOOLEAN): ' . ( (bool) filter_var( $val5, FILTER_VALIDATE_BOOLEAN ) ? 'true' : 'false' ) . "\n";
