<?php

/**
 * Utility Class to wrap around WP register_rest_route
 */
class DT_Route {

    public static function get( string $namespace, string $route, array $callback ) {
        self::endpoint( WP_REST_Server::READABLE, $namespace, $route, $callback );
    }

    public static function post( string $namespace, string $route, array $callback ) {
        self::endpoint( WP_REST_Server::CREATABLE, $namespace, $route, $callback );
    }

    private static function endpoint( string $methods, string $namespace, string $route, array $callback ) {
        register_rest_route(
            $namespace,
            $route,
            [
                [
                    'methods'  => $methods,
                    'callback' => $callback,
                    'permission_callback' => '__return_true'
                ],
            ]
        );
    }
}
