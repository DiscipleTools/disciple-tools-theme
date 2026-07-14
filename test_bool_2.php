<?php
$values = [ 'true', 'false', '1', '0', 1, 0, 'on', 'off', 'yes', 'no' ];

foreach ( $values as $value ) {
    $res = (bool) filter_var( $value, FILTER_VALIDATE_BOOLEAN );
    echo 'Value: ' . var_export( $value, true ) . ' -> ' . var_export( $res, true ) . "\n";
}
?>
