<?php
$v = 'invalid';
$r = filter_var( $v, FILTER_VALIDATE_BOOLEAN );
var_dump( $r ); // bool(false)

$r2 = filter_var( $v, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE );
var_dump( $r2 ); // NULL
