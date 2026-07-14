<?php
$v1 = 'true';
$v2 = 'false';
$r1 = filter_var( $v1, FILTER_VALIDATE_BOOLEAN );
$r2 = filter_var( $v2, FILTER_VALIDATE_BOOLEAN );

var_dump( $r1 );
var_dump( $r2 );

$r1_bool = (bool) $r1;
$r2_bool = (bool) $r2;

var_dump( $r1_bool );
var_dump( $r2_bool );
