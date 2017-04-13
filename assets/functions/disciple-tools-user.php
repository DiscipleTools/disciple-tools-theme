<?php


/**
 * Prepares the keys of user connections for WP_Query
 * This function builds the array for the meta_query used in WP_Query to retrieve only records associated with
 * the user or the teams the user is connected to.
 *
 * @return array
 */
function dt_get_user_scope () {

    // get current user ID


    // build meta_key for current user


    // get groups current user is part of


    // build array for current groups


    // compile all associations

    $user_connections = array(
        'relation' => 'OR',
        array(
            'key'     => 'assigned_to',
            'value'   => 'user-4', // multiplier id
        ),
        array(
            'key'     => 'assigned_to',
            'value'   => 'group-8', // multiplier group
        ),

    );

    // return array to the meta_query
    return $user_connections;
}
