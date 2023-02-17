<?php
require_once( get_template_directory() . '/tests/dt-posts/tests-setup.php' );

/**
 * @testdox DT_Posts::update_post
 */
class DT_Posts_DT_Posts_List_Posts extends WP_UnitTestCase {

    public static $sample_contact_bob = [
        'title'              => 'Bob List',
        'overall_status'     => 'active',
        'milestones'         => [
            'values' => [
                [ 'value' => 'milestone_has_bible' ],
                [ 'value' => 'milestone_baptizing' ]
            ]
        ],
        'baptism_date'       => '2018-12-31',
        'location_grid'      => [ 'values' => [ [ 'value' => '100089589' ] ] ],
        'assigned_to'        => '1',
        'requires_update'    => true,
        'nickname'           => 'Bob the builder',
        'contact_phone'      => [ 'values' => [ [ 'value' => '798456780' ] ] ],
        'contact_email'      => [ 'values' => [ [ 'value' => 'bob@example.com' ] ] ],
        'tags'               => [ 'values' => [ [ 'value' => 'tag1' ] ] ],
        'baptism_generation' => 4
    ];
    public static $sample_contact_john_doe = [
        'title'              => 'John Doe',
        'overall_status'     => 'active'
    ];
    public static $sample_contact_john_bob_doe = [
        'title'              => 'John bob Doe',
        'overall_status'     => 'active'
    ];
    public static $sample_contact_john = [
        'title'              => 'John',
        'overall_status'     => 'active'
    ];
    public static $sample_contact_johndoe = [
        'title'              => 'JohnDoe',
        'overall_status'     => 'active'
    ];
    public static $sample_contact_doe_john = [
        'title'              => 'Doe John',
        'overall_status'     => 'active'
    ];

    public static $sample_group = [
        'name'          => 'Bob\'s group',
        'group_type'    => 'church',
        'location_grid' => [ 'values' => [ [ 'value' => '100089589' ] ] ],
        'member_count'  => 5
    ];

    public static $contact_bob = null;
    public static $contact_john_doe = null;
    public static $contact_john_bob_doe = null;
    public static $contact_john = null;
    public static $contact_johndoe = null;
    public static $contact_doe_john = null;
    public static $group = null;

    public static function setupBeforeClass(): void  {
        $user_id = wp_create_user( 'dispatcher4', 'test', 'testdisp4@example.com' );
        $user = get_user_by( 'id', $user_id );
        $user->set_role( 'dispatcher' );

        self::$sample_contact_bob['assigned_to'] = $user_id;
        self::$sample_contact_john_doe['assigned_to'] = $user_id;
        self::$sample_contact_john_bob_doe['assigned_to'] = $user_id;
        self::$sample_contact_john['assigned_to'] = $user_id;
        self::$sample_contact_johndoe['assigned_to'] = $user_id;
        self::$sample_contact_doe_john['assigned_to'] = $user_id;

        self::$contact_bob = DT_Posts::create_post( 'contacts', self::$sample_contact_bob, true, false );
        self::$contact_john_doe = DT_Posts::create_post( 'contacts', self::$sample_contact_john_doe, true, false );
        self::$contact_john_bob_doe = DT_Posts::create_post( 'contacts', self::$sample_contact_john_bob_doe, true, false );
        self::$contact_john = DT_Posts::create_post( 'contacts', self::$sample_contact_john, true, false );
        self::$contact_johndoe = DT_Posts::create_post( 'contacts', self::$sample_contact_johndoe, true, false );
        self::$contact_doe_john = DT_Posts::create_post( 'contacts', self::$sample_contact_doe_john, true, false );
        self::$group   = DT_Posts::create_post( 'groups', self::$sample_group, true, false );
    }

    /**
     * @dataProvider provide_filter_query_data
     */
    public function test_list_posts_by_filter_query( $post_type, $query, $total, $expected ) {
        $result = DT_Posts::list_posts( $post_type, $query, false );

        $this->assertNotWPError( $result );
        $this->assertSame( $result['total'], $total );

        if ( ! empty( $expected ) ) {
            $this->assertSame( $result['posts'][ $expected['idx'] ][ $expected['key'] ], $expected['value'] );
        }
    }

    public function provide_filter_query_data(): array {
        return [
            'match on contact name'                  => [
                'contacts',
                [ 'name' => [ 'Bob List' ] ],
                1,
                [
                    'idx'   => 0,
                    'key'   => 'name',
                    'value' => 'Bob List'
                ]
            ],
            'match on contact milestone'             => [
                'contacts',
                [ 'milestones' => [ 'milestone_has_bible' ] ],
                1,
                [
                    'idx'   => 0,
                    'key'   => 'milestones',
                    'value' => [ 'milestone_has_bible', 'milestone_baptizing' ]
                ]
            ],
            'match on contact baptism generation'    => [
                'contacts',
                [
                    'baptism_generation' => [
                        'operator' => '>=',
                        'number'   => '3'
                    ]
                ],
                1,
                [
                    'idx'   => 0,
                    'key'   => 'baptism_generation',
                    'value' => 4
                ]
            ],
            'no match on contact baptism generation' => [
                'contacts',
                [
                    'baptism_generation' => [
                        'operator' => '>',
                        'number'   => '4'
                    ]
                ],
                0,
                []
            ],
            'match on group type'                    => [
                'groups',
                [ 'group_type' => [ 'church' ] ],
                1,
                [
                    'idx'   => 0,
                    'key'   => 'group_type',
                    'value' => [
                        'key'   => 'church',
                        'label' => 'Church'
                    ]
                ]
            ],
            'no match on group name'                 => [
                'groups',
                [ 'name' => [ '-Bob\'s group' ] ],
                0,
                []
            ],
            'search for John' => [
                'contacts',
                [ 'text' => 'John' ],
                5,
                []
            ],
            'search for JohnD' => [
                'contacts',
                [ 'text' => 'JohnD' ],
                1,
                []
            ],
            'search for John Doe' => [
                'contacts',
                [ 'text' => 'John Doe' ],
                3,
                []
            ],
            'search for bob' => [
                'contacts',
                [ 'text' => 'bob' ],
                2,
                []
            ],
            'search for doe' => [
                'contacts',
                [ 'text' => 'doe' ],
                4,
                []
            ]
        ];
    }
}
