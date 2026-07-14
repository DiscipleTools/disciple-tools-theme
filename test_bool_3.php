<?php
$val = [ 'key' => 'value' ];
$res = (bool) filter_var( $val, FILTER_VALIDATE_BOOLEAN );
var_dump( $res );
?>
