<?php
/**
 * Initialization of the Post to Post library
 * This is the key configuration file for the post-to-post system in Disciple Tools.
 *
 * @see https://github.com/scribu/wp-posts-to-posts/wiki
 */

function dt_my_connection_types() {









    //    } // end options filter for people groups
}
add_action( 'p2p_init', 'dt_my_connection_types' );


/**
 * Sets the new connections to be published automatically.
 *
 * @param  $args
 * @return mixed
 */
function dt_p2p_published_by_default( $args ) {
    $args['post_status'] = 'publish';

    return $args;
}
add_filter( 'p2p_new_post_args', 'dt_p2p_published_by_default', 10, 1 );

//escape the connection title because p2p doesn't
function dt_p2p_title_escape( $title, $object = null, $type = null ){
    return esc_html( $title );
}
add_filter( "p2p_connected_title", "dt_p2p_title_escape" );

