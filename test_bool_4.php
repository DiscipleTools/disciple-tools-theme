<?php
$val = 'false';
$res = filter_var( $val, FILTER_VALIDATE_BOOLEAN );
var_dump( $res );
?>
