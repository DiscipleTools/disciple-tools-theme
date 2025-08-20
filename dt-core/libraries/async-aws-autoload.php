<?php
if ( !defined( 'ABSPATH' ) ) { exit; }

spl_autoload_register( function ( $class ) {
    $prefixes = [
        'AsyncAws\\S3\\' => __DIR__ . '/async-aws/s3/src/',
        'AsyncAws\\Core\\' => __DIR__ . '/async-aws/core/src/',
        'Symfony\\Component\\HttpClient\\' => __DIR__ . '/symfony/http-client/',
        'Symfony\\Contracts\\HttpClient\\' => __DIR__ . '/symfony/http-client-contracts/',
        'Symfony\\Contracts\\Service\\' => __DIR__ . '/symfony/service-contracts/',
        'Psr\\Log\\' => __DIR__ . '/psr/log/src/',
    ];
    foreach ( $prefixes as $prefix => $baseDir ) {
        if ( strpos( $class, $prefix ) === 0 ) {
            $rel = substr( $class, strlen( $prefix ) );
            $file = $baseDir . str_replace( '\\', '/', $rel ) . '.php';
            if ( is_file( $file ) ) {
                require $file;
            }
        }
    }
});

// Symfony deprecation helper function
if ( !function_exists( 'trigger_deprecation' ) ) {
    $dep = __DIR__ . '/symfony/deprecation-contracts/function.php';
    if ( is_file( $dep ) ) {
        require $dep;
    }
}


