<?php
/**
 * Initialization of the Post to Post library
 * This is the key configuration file for the post-to-post system in Disciple Tools.
 *
 * @see https://github.com/scribu/wp-posts-to-posts/wiki
 */

function dt_my_connection_types()
{

    p2p_register_connection_type(
        [
            'name'        => 'contacts_to_contacts',
            'from'        => 'contacts',
            'to'          => 'contacts',
            'title'       => [
                'from' => __( 'Coached by', 'disciple_tools' ),
                'to'   => __( 'Coaching', 'disciple_tools' ),
            ],
            'from_labels' => [
                'singular_name' => __( 'Contact', 'disciple_tools' ),
                'search_items'  => __( 'Search contacts', 'disciple_tools' ),
                'not_found'     => __( 'No contacts found.', 'disciple_tools' ),
                'create'        => __( 'Connect Disciple ', 'disciple_tools' ),
            ],
            'to_labels'   => [
                'singular_name' => __( 'Contact', 'disciple_tools' ),
                'search_items'  => __( 'Search contacts', 'disciple_tools' ),
                'not_found'     => __( 'No contacts found.', 'disciple_tools' ),
                'create'        => __( 'Connect Coach', 'disciple_tools' ),
            ],

        ]
    );

    p2p_register_connection_type(
        [
            'name'        => 'baptizer_to_baptized',
            'from'        => 'contacts',
            'to'          => 'contacts',
            'title'       => [
                'from' => __( 'Baptized by', 'disciple_tools' ),
                'to'   => __( 'Baptized', 'disciple_tools' ),
            ],
            'from_labels' => [
                'singular_name' => __( 'Contact', 'disciple_tools' ),
                'search_items'  => __( 'Search contacts', 'disciple_tools' ),
                'not_found'     => __( 'No contacts found.', 'disciple_tools' ),
                'create'        => __( 'Add Baptism', 'disciple_tools' ),
            ],
            'to_labels'   => [
                'singular_name' => __( 'Contact', 'disciple_tools' ),
                'search_items'  => __( 'Search contacts', 'disciple_tools' ),
                'not_found'     => __( 'No contacts found.', 'disciple_tools' ),
                'create'        => __( 'Add Baptizer', 'disciple_tools' ),
            ],
            'fields'      => [
                'month' => [
                    'title'   => __( 'Month', 'disciple_tools' ),
                    'type'    => 'select',
                    'values'  => [ '01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12' ],
                    'default' => date( 'm' ),
                ],
                'day'   => [
                    'title'   => __( 'Day', 'disciple_tools' ),
                    'type'    => 'select',
                    'values'  => [ '01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '22', '23', '24', '25', '26', '27', '28', '29', '30', '31' ],
                    'default' => date( 'd' ),
                ],
                'year'  => [
                    'title'   => __( 'Year', 'disciple_tools' ),
                    'type'    => 'text',
                    'default' => date( 'Y' ),
                ],
            ],

        ]
    );

    p2p_register_connection_type(
        [
            'name'           => 'contacts_to_groups',
            'from'           => 'contacts',
            'to'             => 'groups',
            'admin_column'   => 'any',
            'admin_dropdown' => 'from',
            'title'          => [
                'from' => __( 'Groups', 'disciple_tools' ),
                'to'   => __( 'Members', 'disciple_tools' ),
            ],
            'to_labels'      => [
                'singular_name' => __( 'Groups', 'disciple_tools' ),
                'search_items'  => __( 'Search groups', 'disciple_tools' ),
                'not_found'     => __( 'No groups found.', 'disciple_tools' ),
                'create'        => __( 'Connect Group ', 'disciple_tools' ),
            ],
            'from_labels'    => [
                'singular_name' => __( 'Member', 'disciple_tools' ),
                'search_items'  => __( 'Search members', 'disciple_tools' ),
                'not_found'     => __( 'No members found.', 'disciple_tools' ),
                'create'        => __( 'Connect Member', 'disciple_tools' ),
            ],
            'fields'         => [
                'leader' => [
                    'title' => __( 'Leader', 'disciple_tools' ),
                    'type'  => 'checkbox',
                ],
            ],
        ]
    );

    p2p_register_connection_type(
        [
            'name'        => 'contacts_to_locations',
            'from'        => 'contacts',
            'to'          => 'locations',
            //            'cardinality' => 'many-to-one',
            'title'       => [
                'from' => __( 'Location', 'disciple_tools' ),
                'to'   => __( 'Contacts', 'disciple_tools' ),
            ],
            'to_labels'   => [
                'singular_name' => __( 'Locations', 'disciple_tools' ),
                'search_items'  => __( 'Search locations', 'disciple_tools' ),
                'not_found'     => __( 'No locations found.', 'disciple_tools' ),
                'create'        => __( 'Connect Location', 'disciple_tools' ),
            ],
            'from_labels' => [
                'singular_name' => __( 'Contacts', 'disciple_tools' ),
                'search_items'  => __( 'Search contacts', 'disciple_tools' ),
                'not_found'     => __( 'No contacts found.', 'disciple_tools' ),
                'create'        => __( 'Create Contact', 'disciple_tools' ),
            ],
            'fields'      => [
                'primary' => [
                    'title' => __( 'Primary', 'disciple_tools' ),
                    'type'  => 'checkbox',
                ],
            ],
        ]
    );

    p2p_register_connection_type(
        [
            'name'        => 'groups_to_locations',
            'from'        => 'groups',
            'to'          => 'locations',
            'title'       => [
                'from' => __( 'Location', 'disciple_tools' ),
                'to'   => __( 'Groups', 'disciple_tools' ),
            ],
            'to_labels'   => [
                'singular_name' => __( 'Locations', 'disciple_tools' ),
                'search_items'  => __( 'Search locations', 'disciple_tools' ),
                'not_found'     => __( 'No locations found.', 'disciple_tools' ),
                'create'        => __( 'Connect Location', 'disciple_tools' ),
            ],
            'from_labels' => [
                'singular_name' => __( 'Groups', 'disciple_tools' ),
                'search_items'  => __( 'Search groups', 'disciple_tools' ),
                'not_found'     => __( 'No groups found.', 'disciple_tools' ),
                'create'        => __( 'Create Group', 'disciple_tools' ),
            ],
        ]
    );

    p2p_register_connection_type(
        [
            'name'         => 'groups_to_groups',
            'from'         => 'groups',
            'to'           => 'groups',
            'admin_column' => 'any',
            'title'        => [
                'from' => __( 'Planted by', 'disciple_tools' ),
                'to'   => __( 'Planting', 'disciple_tools' ),
            ],
            'from_labels'  => [
                'singular_name' => __( 'Group', 'disciple_tools' ),
                'search_items'  => __( 'Search groups', 'disciple_tools' ),
                'not_found'     => __( 'No groups found.', 'disciple_tools' ),
                'create'        => __( 'Connect Child Group', 'disciple_tools' ),
            ],
            'to_labels'    => [
                'singular_name' => __( 'Group', 'disciple_tools' ),
                'search_items'  => __( 'Search groups', 'disciple_tools' ),
                'not_found'     => __( 'No groups found.', 'disciple_tools' ),
                'create'        => __( 'Connect Parent Group', 'disciple_tools' ),
            ],
        ]
    );

    p2p_register_connection_type(
        [
            'name'        => 'assetmapping_to_locations',
            'from'        => 'assetmapping',
            'to'          => 'locations',
            'cardinality' => 'many-to-one',
            'title'       => [
                'from' => __( 'Location', 'disciple_tools' ),
                'to'   => __( 'Assets', 'disciple_tools' ),
            ],
            'from_labels' => [
                'singular_name' => __( 'Assets', 'disciple_tools' ),
                'search_items'  => __( 'Search assets', 'disciple_tools' ),
                'not_found'     => __( 'No assets found.', 'disciple_tools' ),
                'create'        => __( 'Connect Assets', 'disciple_tools' ),
            ],
            'to_labels'   => [
                'singular_name' => __( 'Locations', 'disciple_tools' ),
                'search_items'  => __( 'Search locations', 'disciple_tools' ),
                'not_found'     => __( 'No locations found.', 'disciple_tools' ),
                'create'        => __( 'Connect Location', 'disciple_tools' ),
            ],
        ]
    );

    /**
     * This creates the link between members and locations for assignment purposes.
     */
    p2p_register_connection_type(
        [
            'name'  => 'team_member_locations',
            'from'  => 'locations',
            'to'    => 'user',
            'title' => [
                'from' => __( 'Team Members', 'disciple_tools' ),
                'to'   => __( 'Locations', 'disciple_tools' ),
            ],
        ]
    );

    /**
     * People Groups addon
     *
     * @see disciple-tools.php for the people groups registration
     */
    //    if(isset( get_option( disciple_tools()->token.'-general', false )['add_people_groups'] )) { // TODO need to create the options filter for people groups
    p2p_register_connection_type(
        [
            'name'  => 'team_member_peoplegroups',
            'from'  => 'peoplegroups',
            'to'    => 'user',
            'title' => [
                'from' => __( 'Team Members', 'disciple_tools' ),
                'to'   => __( 'People Groups', 'disciple_tools' ),
            ],
        ]
    );
    p2p_register_connection_type(
        [
            'name'        => 'contacts_to_peoplegroups',
            'from'        => 'contacts',
            'to'          => 'peoplegroups',
            'title'       => [
                'from' => __( 'People Groups', 'disciple_tools' ),
                'to'   => __( 'Contacts', 'disciple_tools' ),
            ],
            'to_labels'   => [
                'singular_name' => __( 'People Group', 'disciple_tools' ),
                'search_items'  => __( 'Search People Groups', 'disciple_tools' ),
                'not_found'     => __( 'No people groups found.', 'disciple_tools' ),
                'create'        => __( 'Connect People Groups', 'disciple_tools' ),
            ],
            'from_labels' => [
                'singular_name' => __( 'Contacts', 'disciple_tools' ),
                'search_items'  => __( 'Search contacts', 'disciple_tools' ),
                'not_found'     => __( 'No contacts found.', 'disciple_tools' ),
                'create'        => __( 'Create Contact', 'disciple_tools' ),
            ],
            'fields'      => [
                'primary' => [
                    'title' => __( 'Primary', 'disciple_tools' ),
                    'type'  => 'checkbox',
                ],
            ],
        ]
    );
    p2p_register_connection_type(
        [
            'name'        => 'groups_to_peoplegroups',
            'from'        => 'groups',
            'to'          => 'peoplegroups',
            'title'       => [
                'from' => __( 'People Groups', 'disciple_tools' ),
                'to'   => __( 'Groups', 'disciple_tools' ),
            ],
            'to_labels'   => [
                'singular_name' => __( 'People Groups', 'disciple_tools' ),
                'search_items'  => __( 'Search people groups', 'disciple_tools' ),
                'not_found'     => __( 'No people groups found.', 'disciple_tools' ),
                'create'        => __( 'Connect People Groups', 'disciple_tools' ),
            ],
            'from_labels' => [
                'singular_name' => __( 'Groups', 'disciple_tools' ),
                'search_items'  => __( 'Search groups', 'disciple_tools' ),
                'not_found'     => __( 'No groups found.', 'disciple_tools' ),
                'create'        => __( 'Create Group', 'disciple_tools' ),
            ],
        ]
    );
    p2p_register_connection_type(
        [
            'name'        => 'peoplegroups_to_locations',
            'from'        => 'peoplegroups',
            'to'          => 'locations',
            'title'       => [
                'from' => __( 'Locations', 'disciple_tools' ),
                'to'   => __( 'People Groups', 'disciple_tools' ),
            ],
            'to_labels'   => [
                'singular_name' => __( 'Locations', 'disciple_tools' ),
                'search_items'  => __( 'Search locations', 'disciple_tools' ),
                'not_found'     => __( 'No locations found.', 'disciple_tools' ),
                'create'        => __( 'Connect Location', 'disciple_tools' ),
            ],
            'from_labels' => [
                'singular_name' => __( 'People Groups', 'disciple_tools' ),
                'search_items'  => __( 'Search People Groups', 'disciple_tools' ),
                'not_found'     => __( 'No people groups found.', 'disciple_tools' ),
                'create'        => __( 'Create People Groups', 'disciple_tools' ),
            ],
            'fields'      => [
                'primary' => [
                    'title' => __( 'Primary', 'disciple_tools' ),
                    'type'  => 'checkbox',
                ],
            ],
        ]
    );
    p2p_register_connection_type(
        [
            'name'        => 'assetmapping_to_peoplegroups',
            'from'        => 'assetmapping',
            'to'          => 'peoplegroups',
            'title'       => [
                'from' => __( 'People Groups', 'disciple_tools' ),
                'to'   => __( 'Assets', 'disciple_tools' ),
            ],
            'from_labels' => [
                'singular_name' => __( 'Assets', 'disciple_tools' ),
                'search_items'  => __( 'Search assets', 'disciple_tools' ),
                'not_found'     => __( 'No assets found.', 'disciple_tools' ),
                'create'        => __( 'Connect Assets', 'disciple_tools' ),
            ],
            'to_labels'   => [
                'singular_name' => __( 'People Group', 'disciple_tools' ),
                'search_items'  => __( 'Search People Groups', 'disciple_tools' ),
                'not_found'     => __( 'No People Groups found.', 'disciple_tools' ),
                'create'        => __( 'Connect People Group', 'disciple_tools' ),
            ],
        ]
    );
    //    } // end options filter for people groups

}
add_action( 'p2p_init', 'dt_my_connection_types' );

/**
 * Sets the new connections to be published automatically.
 *
 * @param  $args
 *
 * @return mixed
 */
function dt_p2p_published_by_default( $args )
{
    $args['post_status'] = 'publish';

    return $args;
}
add_filter( 'p2p_new_post_args', 'dt_p2p_published_by_default', 10, 1 );

/**
 * Adding the connection box to the user profile
 *
 * @param $user
 */
function dt_user_location_connections( $user )
{

    // Find connected posts
    $args = [
        'connected_type'   => 'team_member_locations',
        'connected_items'  => $user,
        'suppress_filters' => false,
        'nopaging'         => true,
        'post_status'      => 'publish',
    ];
    $connected = get_posts( $args );

    // Display connected posts
    if ( count( $connected ) ) {
        ?>
        <h3>User Locations</h3>
        <table class="form-table">
            <tbody>
            <tr>
                <th>
                    <label for="user-group">Locations</label>
                </th>
                <td>
                    <table class="wp-list-table widefat fixed striped user-groups">
                        <thead>
                        <tr>
                            <th scope="col" class="manage-column column-name column-primary">Name</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        foreach ( $connected as $next ) { ?>
                            <tr class="inactive">
                                <td class="column-primary">
                                    <strong><?php echo esc_html( $next->post_title ); ?></strong>
                                    <div class="row-actions">
                                        <a href="<?php echo esc_url( get_permalink( $next->ID ) ); ?>">View in Portal</a> | <a
                                            href="<?php echo esc_url( home_url( '/' ) ); ?>wp-admin/post.php?post=<?php echo esc_url( $next->ID ); ?>&action=edit">View
                                            in Admin</a>
                                    </div>
                                </td>
                            </tr>
                            <?php
                        } ?>
                        </tbody>
                        <tfoot>
                        <tr>
                            <th scope="col" class="manage-column column-name column-primary">Name</th>
                        </tr>
                        </tfoot>
                    </table>

                </td>
            </tr>
            </tbody>
        </table>
        <ul>

        </ul>

        <?php
        // Prevent weirdness
        wp_reset_postdata();
    }
}
add_action( 'show_user_profile', 'dt_user_location_connections', 999 );
add_action( 'edit_user_profile', 'dt_user_location_connections', 999 );

/**
 * Adding the connection box to the user profile
 *
 * @param $user
 */
function dt_user_peoplegroup_connections( $user )
{

    // Find connected posts
    $args = [
        'connected_type'   => 'team_member_peoplegroups',
        'connected_items'  => $user,
        'suppress_filters' => false,
        'nopaging'         => true,
        'post_status'      => 'publish',
    ];
    $connected = get_posts( $args );

    // Display connected posts
    if ( count( $connected ) ) {
        ?>
        <h3>User People Groups</h3>
        <table class="form-table">
            <tbody>
            <tr>
                <th>
                    <label for="user-group">People Groups</label>
                </th>
                <td>
                    <table class="wp-list-table widefat fixed striped user-groups">
                        <thead>
                        <tr>
                            <th scope="col" class="manage-column column-name column-primary">Name</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        foreach ( $connected as $next ) { ?>
                            <tr class="inactive">
                                <td class="column-primary">
                                    <strong><?php echo esc_html( $next->post_title ); ?></strong>
                                    <div class="row-actions">
                                        <a href="<?php echo esc_url( get_permalink( $next->ID ) ); ?>">View in Portal</a> | <a
                                            href="<?php echo esc_url( home_url( '/' ) ); ?>wp-admin/post.php?post=<?php echo esc_url( $next->ID ); ?>&action=edit">View
                                            in Admin</a>
                                    </div>
                                </td>
                            </tr>
                            <?php
                        } ?>
                        </tbody>
                        <tfoot>
                        <tr>
                            <th scope="col" class="manage-column column-name column-primary">Name</th>
                        </tr>
                        </tfoot>
                    </table>

                </td>
            </tr>
            </tbody>
        </table>
        <ul>

        </ul>

        <?php
        // Prevent weirdness
        wp_reset_postdata();
    }
}
add_action( 'show_user_profile', 'dt_user_peoplegroup_connections', 999 );
add_action( 'edit_user_profile', 'dt_user_peoplegroup_connections', 999 );
