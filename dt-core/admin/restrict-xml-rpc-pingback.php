<?php
/**
 * Removes a few common methods for DDoS attacks on the site.
 */

/**
 * @param $methods
 *
 * @return mixed
 */
function dt_block_xmlrpc_attacks( $methods )
{
    unset( $methods['pingback.ping'] );
    unset( $methods['pingback.extensions.getPingbacks'] );

    return $methods;
}
add_filter( 'xmlrpc_methods', 'dt_block_xmlrpc_attacks' );

/**
 * @param $headers
 *
 * @return mixed
 */
function dt_remove_x_pingback_header( $headers )
{
    unset( $headers['X-Pingback'] );

    return $headers;
}
add_filter( 'wp_headers', 'dt_remove_x_pingback_header' );
