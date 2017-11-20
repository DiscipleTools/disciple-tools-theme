<?php

/**
 * Route Front Page depending on login role
 */
function dt_route_front_page() {
    if (user_can( get_current_user_id(), 'access_contacts' )) {
        include( get_stylesheet_directory() . '/dashboard.php' );
    } else {
        wp_redirect( home_url() . '/settings/' );
    }
}
