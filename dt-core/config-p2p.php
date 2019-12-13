<?php
/**
 * Initialization of the Post to Post library
 * This is the key configuration file for the post-to-post system in Disciple Tools.
 *
 * @see https://github.com/scribu/wp-posts-to-posts/wiki
 */

function dt_my_connection_types() {

    /**
     * Contact Coaching field
     */
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
            ]
        ]
    );

    /**
     * Contact Connection or Relation
     */
    p2p_register_connection_type(
        [
            'name'        => 'contacts_to_relation',
            'from'        => 'contacts',
            'to'          => 'contacts'
        ]
    );

    /**
     * Contact Sub-assigned to
     */
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
            ]
        ]
    );

    /**
     * Contact Baptism Field
     */
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
            ]
        ]
    );

    /**
     * Contact People Groups
     */
    p2p_register_connection_type(
        [
            'name'        => 'contacts_to_peoplegroups',
            'from'        => 'contacts',
            'to'          => 'peoplegroups',
            'title'       => [
                'from' => __( 'People Groups', 'disciple_tools' ),
                'to'   => __( 'Contacts', 'disciple_tools' ),
            ]
        ]
    );


    /**
     * Group members field
     */
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
            ]
        ]
    );
    /**
     * Group leaders field
     */
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
            ]
        ]
    );
    /**
     * Group coaches field
     */
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
            ]
        ]
    );


    /**
     * Parent and child groups
     */
    p2p_register_connection_type(
        [
            'name'         => 'groups_to_groups',
            'from'         => 'groups',
            'to'           => 'groups',
            'title'        => [
                'from' => __( 'Planted by', 'disciple_tools' ),
                'to'   => __( 'Planting', 'disciple_tools' ),
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
    ] );



    /**
     * Group People Groups field
     */
    p2p_register_connection_type(
        [
            'name'        => 'groups_to_peoplegroups',
            'from'        => 'groups',
            'to'          => 'peoplegroups',
            'title'       => [
                'from' => __( 'People Groups', 'disciple_tools' ),
                'to'   => __( 'Groups', 'disciple_tools' ),
            ]
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

