<?php
$val1 = 'true';
$val2 = 'false';
$val3 = 'something';

$res1 = (bool) filter_var( $val1, FILTER_VALIDATE_BOOLEAN );
$res2 = (bool) filter_var( $val2, FILTER_VALIDATE_BOOLEAN );
$res3 = (bool) filter_var( $val3, FILTER_VALIDATE_BOOLEAN );

var_dump( $res1 );
var_dump( $res2 );
var_dump( $res3 );
?>
