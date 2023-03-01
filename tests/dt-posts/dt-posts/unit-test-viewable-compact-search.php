<?php
require_once( get_template_directory() . '/tests/dt-posts/tests-setup.php' );

class DT_Posts_DT_Posts_Viewable_Compact_Search extends WP_UnitTestCase{

    public static $sample_contact_bob = [
        'title' => 'Bob List',
        'overall_status' => 'active',
        'milestones' => [
            'values' => [
                [ 'value' => 'milestone_has_bible' ],
                [ 'value' => 'milestone_baptizing' ]
            ]
        ],
        'baptism_date' => '2018-12-31',
        'location_grid' => [ 'values' => [ [ 'value' => '100089589' ] ] ],
        'assigned_to' => '1',
        'requires_update' => true,
        'nickname' => 'Bob the builder',
        'contact_phone' => [ 'values' => [ [ 'value' => '798456780' ] ] ],
        'contact_email' => [ 'values' => [ [ 'value' => 'bob@example.com' ] ] ],
        'tags' => [ 'values' => [ [ 'value' => 'tag1' ] ] ],
        'baptism_generation' => 4
    ];
    public static $sample_contact_john_doe = [
        'title' => 'John Doe',
        'overall_status' => 'active'
    ];
    public static $sample_contact_john_bob_doe = [
        'title' => 'John bob Doe',
        'overall_status' => 'active'
    ];
    public static $sample_contact_john = [
        'title' => 'John',
        'overall_status' => 'active'
    ];
    public static $sample_contact_johndoe = [
        'title' => 'JohnDoe',
        'overall_status' => 'active'
    ];
    public static $sample_contact_doe_john = [
        'title' => 'Doe John',
        'overall_status' => 'active'
    ];

    public static $sample_group = [
        'name' => 'Bob\'s group',
        'group_type' => 'church',
        'location_grid' => [ 'values' => [ [ 'value' => '100089589' ] ] ],
        'member_count' => 5
    ];

    public static $contact_bob = null;
    public static $contact_john_doe = null;
    public static $contact_john_bob_doe = null;
    public static $contact_john = null;
    public static $contact_johndoe = null;
    public static $contact_doe_john = null;
    public static $group = null;
    public static $user = null;

    public static $ASSERT_TYPE_EQUAL = 0;
    public static $ASSERT_TYPE_BETWEEN = 1;

    public static function setupBeforeClass(): void{
        $user_id = wp_create_user( 'dispatcher4', 'test', 'testdisp4@example.com' );
        self::$user = get_user_by( 'id', $user_id );
        self::$user->set_role( 'dispatcher' );
        self::$user->add_cap( 'access_contacts' );
        self::$user->add_cap( 'list_all_contacts' );
        self::$user->add_cap( 'access_groups' );
        self::$user->add_cap( 'list_all_groups' );

        self::$sample_contact_bob['assigned_to'] = $user_id;
        self::$sample_contact_john_doe['assigned_to'] = $user_id;
        self::$sample_contact_john_bob_doe['assigned_to'] = $user_id;
        self::$sample_contact_john['assigned_to'] = $user_id;
        self::$sample_contact_johndoe['assigned_to'] = $user_id;
        self::$sample_contact_doe_john['assigned_to'] = $user_id;
        self::$sample_group['assigned_to'] = $user_id;

        self::$contact_bob = DT_Posts::create_post( 'contacts', self::$sample_contact_bob, true, false );
        self::$contact_john_doe = DT_Posts::create_post( 'contacts', self::$sample_contact_john_doe, true, false );
        self::$contact_john_bob_doe = DT_Posts::create_post( 'contacts', self::$sample_contact_john_bob_doe, true, false );
        self::$contact_john = DT_Posts::create_post( 'contacts', self::$sample_contact_john, true, false );
        self::$contact_johndoe = DT_Posts::create_post( 'contacts', self::$sample_contact_johndoe, true, false );
        self::$contact_doe_john = DT_Posts::create_post( 'contacts', self::$sample_contact_doe_john, true, false );
        self::$group = DT_Posts::create_post( 'groups', self::$sample_group, true, false );
    }

    /**
     * @dataProvider provide_filter_query_data
     */
    public function test_viewable_compact_search_by_filter_query( $post_type, $search, $args, $expected ){
        wp_set_current_user( self::$user->ID );

        $result = DT_Posts::get_viewable_compact( $post_type, $search, $args );
        // fwrite( STDERR, print_r( wp_get_current_user(), TRUE ) );
        // fwrite( STDERR, print_r( $result, TRUE ) );

        $this->assertNotWPError( $result );

        // Assert Returned Totals.
        if ( !empty( $expected['totals'] ) ){
            switch ($expected['totals']['assert_type']){
                case self::$ASSERT_TYPE_EQUAL:
                    $this->assertSame( $result['total'], $expected['totals']['value'] );
                    break;
                case self::$ASSERT_TYPE_BETWEEN:
                    $this->assertThat(
                        $result['total'],
                        $this->logicalAnd(
                            $this->greaterThanOrEqual( $expected['totals']['min'] ),
                            $this->lessThanOrEqual( $expected['totals']['max'] )
                        )
                    );
                    break;
            }
        }

        // Assert Returned Posts.
        if ( !empty( $expected['posts'] ) ){
            $this->assertSame( $result['posts'][$expected['posts']['idx']][$expected['posts']['key']], $expected['posts']['value'] );
        }
    }

    public function provide_filter_query_data(): array{
        return [
            'groups field search' => [
                'groups',
                '',
                [
                    'field_key' => 'groups'
                ],
                [
                    'totals' => [
                        'assert_type' => self::$ASSERT_TYPE_EQUAL,
                        'value' => 1
                    ],
                    'posts' => []
                ]
            ],
            'groups search by name' => [
                'groups',
                self::$sample_group['name'],
                [],
                [
                    'totals' => [
                        'assert_type' => self::$ASSERT_TYPE_EQUAL,
                        'value' => 1
                    ],
                    'posts' => [
                        'idx' => 0,
                        'key' => 'name',
                        'value' => self::$sample_group['name']
                    ]
                ]
            ],
            'subassigned field search by all' => [
                'contacts',
                '',
                [
                    'field_key' => 'subassigned'
                ],
                [
                    'totals' => [
                        'assert_type' => self::$ASSERT_TYPE_BETWEEN,
                        'min' => 6,
                        'max' => 7
                    ],
                    'posts' => []
                ]
            ]
        ];
    }
}
