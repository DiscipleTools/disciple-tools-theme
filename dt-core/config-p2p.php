<?php
/**
 * Initialization of the Post to Post library
 * This is the key configuration file for the post-to-post system in Disciple Tools.
 *
 * @see https://github.com/scribu/wp-posts-to-posts/wiki
 */

function dt_my_connection_types() {

    p2p_register_connection_type(
        [
            'name'        => 'contacts_to_contacts',
            'from'        => 'contacts',
            'to'          => 'contacts',
            'admin_box' => [
                'show' => false,
            ],
            'title'       => [
                'from' => __( 'Coached by', 'disciple_tools' ),
                'to'   => __( 'Coaching', 'disciple_tools' ),
            ],
            'from_labels' => [
                'singular_name' => __( 'Contact', 'disciple_tools' ),
                'search_items'  => __( 'Search contacts', 'disciple_tools' ),
                'not_found'     => __( 'No contacts found.', 'disciple_tools' ),
                'create'        => __( 'Connect Disciple', 'disciple_tools' ),
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
            'name'        => 'contacts_to_relation',
            'from'        => 'contacts',
            'to'          => 'contacts',
            'admin_box' => [
                'show' => false,
            ],
        ]
    );

    p2p_register_connection_type(
        [
            'name'        => 'contacts_to_subassigned',
            'from'        => 'contacts',
            'to'          => 'contacts',
            'admin_box' => [
                'show' => false,
            ],
            'title'       => [
                'from' => __( 'Sub-assigned by', 'disciple_tools' ),
                'to'   => __( 'Sub-assigned', 'disciple_tools' ),
            ],
            'from_labels' => [
                'singular_name' => __( 'Contact', 'disciple_tools' ),
                'search_items'  => __( 'Search contacts', 'disciple_tools' ),
                'not_found'     => __( 'No contacts found.', 'disciple_tools' ),
                'create'        => __( 'Connect Disciple', 'disciple_tools' ),
            ],
            'to_labels'   => [
                'singular_name' => __( 'Contact', 'disciple_tools' ),
                'search_items'  => __( 'Search contacts', 'disciple_tools' ),
                'not_found'     => __( 'No contacts found.', 'disciple_tools' ),
                'create'        => __( 'Connect Sub-assigned', 'disciple_tools' ),
            ],

        ]
    );

    p2p_register_connection_type(
        [
            'name'        => 'baptizer_to_baptized',
            'from'        => 'contacts',
            'to'          => 'contacts',
            'admin_box' => [
                'show' => false,
            ],
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
            'admin_box' => [
                'show' => false,
            ],
            'title'          => [
                'from' => __( 'Contacts', 'disciple_tools' ),
                'to'   => __( 'Members', 'disciple_tools' ),
            ],
            'to_labels'      => [
                'singular_name' => __( 'Group', 'disciple_tools' ),
                'plural_name' => __( 'Groups', 'disciple_tools' ),
                'search_items'  => __( 'Search groups', 'disciple_tools' ),
                'not_found'     => __( 'No groups found.', 'disciple_tools' ),
                'create'        => __( 'Connect Group', 'disciple_tools' ),
            ],
            'from_labels'    => [
                'singular_name' => __( 'Member', 'disciple_tools' ),
                'plural_name' => __( 'Members', 'disciple_tools' ),
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
            'name'           => 'groups_to_leaders',
            'from'           => 'groups',
            'to'             => 'contacts',
            'admin_box' => [
                'show' => false,
            ],
            'title'          => [
                'from' => __( 'Groups', 'disciple_tools' ),
                'to'   => __( 'Leaders', 'disciple_tools' ),
            ],
            'from_labels'      => [
                'singular_name' => __( 'Group', 'disciple_tools' ),
                'plural_name' => __( 'Groups', 'disciple_tools' ),
                'search_items'  => __( 'Search groups', 'disciple_tools' ),
                'not_found'     => __( 'No groups found.', 'disciple_tools' ),
                'create'        => __( 'Connect Group', 'disciple_tools' ),
            ],
            'to_labels'    => [
                'singular_name' => __( 'Leader', 'disciple_tools' ),
                'plural_name' => __( 'Leaders', 'disciple_tools' ),
                'search_items'  => __( 'Search leaders', 'disciple_tools' ),
                'not_found'     => __( 'No leaders found.', 'disciple_tools' ),
                'create'        => __( 'Connect Leader', 'disciple_tools' ),
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
            'name'           => 'groups_to_coaches',
            'from'           => 'groups',
            'to'             => 'contacts',
            'admin_box' => [
                'show' => false,
            ],
            'title'          => [
                'from' => __( 'Groups', 'disciple_tools' ),
                'to'   => __( 'Coaches', 'disciple_tools' ),
            ],
            'from_labels'      => [
                'singular_name' => __( 'Group', 'disciple_tools' ),
                'plural_name' => __( 'Groups', 'disciple_tools' ),
                'search_items'  => __( 'Search groups', 'disciple_tools' ),
                'not_found'     => __( 'No groups found.', 'disciple_tools' ),
                'create'        => __( 'Added coach:', 'disciple_tools' ),
            ],
            'to_labels'    => [
                'singular_name' => __( 'Coach', 'disciple_tools' ),
                'plural_name' => __( 'Coaches', 'disciple_tools' ),
                'search_items'  => __( 'Search coaches', 'disciple_tools' ),
                'not_found'     => __( 'No coaches found.', 'disciple_tools' ),
                'create'        => __( 'Connect Coach', 'disciple_tools' ),
            ],
            'fields'         => [
                'coach' => [
                    'title' => __( 'Coach', 'disciple_tools' ),
                    'type'  => 'checkbox',
                ],
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
                'singular_name' => __( 'Parent Group', 'disciple_tools' ),
                'plural_name' => __( 'Parent Groups', 'disciple_tools' ),
                'search_items'  => __( 'Search groups', 'disciple_tools' ),
                'not_found'     => __( 'No groups found.', 'disciple_tools' ),
                'create'        => __( 'Connect Child Group', 'disciple_tools' ),
            ],
            'to_labels'    => [
                'singular_name' => __( 'Child Group', 'disciple_tools' ),
                'plural_name' => __( 'Child Groups', 'disciple_tools' ),
                'search_items'  => __( 'Search groups', 'disciple_tools' ),
                'not_found'     => __( 'No groups found.', 'disciple_tools' ),
                'create'        => __( 'Connect Parent Group', 'disciple_tools' ),
            ],
        ]
    );

    /**
     * Peer groups
     */
    p2p_register_connection_type( [
        'name'         => 'groups_to_peers',
        'from'         => 'groups',
        'to'           => 'groups',
        'admin_column' => 'any'
    ] );



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
                'singular_name' => __( 'People Group', 'disciple_tools' ),
                'plural_name' => __( 'People Groups', 'disciple_tools' ),
                'search_items'  => __( 'Search people groups', 'disciple_tools' ),
                'not_found'     => __( 'No people groups found.', 'disciple_tools' ),
                'create'        => __( 'Connect People Groups', 'disciple_tools' ),
            ],
            'from_labels' => [
                'singular_name' => __( 'Group', 'disciple_tools' ),
                'plural_name' => __( 'Groups', 'disciple_tools' ),
                'search_items'  => __( 'Search groups', 'disciple_tools' ),
                'not_found'     => __( 'No groups found.', 'disciple_tools' ),
                'create'        => __( 'Create Group', 'disciple_tools' ),
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

