<?php
$val = 'somejunk';
$res1 = (bool) filter_var( $val, FILTER_VALIDATE_BOOLEAN );
$res2 = (bool) filter_var( $val, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE );
var_dump( $res1 );
var_dump( $res2 );
?>
