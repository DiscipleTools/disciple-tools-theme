<?php

/**
 * Utility Class to wrap around WP register_rest_route
 */
class DT_Route {

    public static function get( string $namespace, string $route, array $callback, array $options = [] ) {
        self::endpoint( WP_REST_Server::READABLE, $namespace, $route, $callback, $options );
    }

    public static function post( string $namespace, string $route, array $callback, array $options = [] ) {
        self::endpoint( WP_REST_Server::CREATABLE, $namespace, $route, $callback, $options );
    }

    private static function endpoint( string $methods, string $namespace, string $route, array $callback, array $options ) {
        $default_options = [
            'permission_callback' => '__return_true'
        ];

        $rest_options = [
            'methods'  => $methods,
            'callback' => $callback,
        ];

        $merged_options = array_merge( $rest_options, $default_options, $options );

        register_rest_route(
            $namespace,
            $route,
            [ $merged_options ],
        );
    }
}
