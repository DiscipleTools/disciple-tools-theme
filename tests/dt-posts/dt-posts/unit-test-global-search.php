<?php
require_once( get_template_directory() . '/tests/dt-posts/tests-setup.php' );

/**
 * @testdox DT_Posts::advanced_search
 */
class DT_Posts_DT_Posts_Global_Search extends WP_UnitTestCase{

    public static $sample_contact = [
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
        'baptism_generation' => 4,
        'notes' => [
            'Test comment for bob!'
        ]
    ];

    public static $sample_group = [
        'name' => 'Bob\'s group',
        'group_type' => 'church',
        'location_grid' => [ 'values' => [ [ 'value' => '100089589' ] ] ],
        'member_count' => 5
    ];

    public static $contact = null;
    public static $group = null;

    public static function setupBeforeClass(): void{
        self::$contact = DT_Posts::create_post( 'contacts', self::$sample_contact, true, false );
        self::$group = DT_Posts::create_post( 'groups', self::$sample_group, true, false );
    }

    /**
     * @dataProvider provide_global_search_query_data
     */
    public function test_global_searches( $args, $expected ){
        wp_set_current_user( 1 ); // Default to admin user for total coverage

        // fwrite( STDERR, print_r( wp_get_current_user(), true ) );
        $result = DT_Posts::advanced_search( $args['query'], $args['post_type'], $args['offset'], $args['filters'], false );

        $this->assertNotWPError( $result );
        $this->assertSame( $result['total_hits'], $expected['total'] );
    }

    public function provide_global_search_query_data(): array{
        return [
            'match all bob references' => [
                [
                    'query' => 'Bob',
                    'post_type' => 'all',
                    'offset' => 0,
                    'filters' => [
                        'post' => true,
                        'comment' => true,
                        'meta' => true,
                        'status' => 'all'
                    ]
                ],
                [
                    'total' => 2
                ]
            ],
            'match all bob references in contacts' => [
                [
                    'query' => 'bob',
                    'post_type' => 'contacts',
                    'offset' => 0,
                    'filters' => [
                        'post' => true,
                        'comment' => true,
                        'meta' => true,
                        'status' => 'all'
                    ]
                ],
                [
                    'total' => 1
                ]
            ],
            'match all bob references in contacts comments' => [
                [
                    'query' => 'comment for bob',
                    'post_type' => 'contacts',
                    'offset' => 0,
                    'filters' => [
                        'post' => true,
                        'comment' => true,
                        'meta' => true,
                        'status' => 'all'
                    ]
                ],
                [
                    'total' => 1
                ]
            ],
            'no match across all post types' => [
                [
                    'query' => 'Frank',
                    'post_type' => 'all',
                    'offset' => 0,
                    'filters' => [
                        'post' => true,
                        'comment' => true,
                        'meta' => true,
                        'status' => 'all'
                    ]
                ],
                [
                    'total' => 0
                ]
            ],
            'match all bob references in groups' => [
                [
                    'query' => 'Bob',
                    'post_type' => 'groups',
                    'offset' => 0,
                    'filters' => [
                        'post' => true,
                        'comment' => true,
                        'meta' => true,
                        'status' => 'all'
                    ]
                ],
                [
                    'total' => 1
                ]
            ],
            'find all records by phone number' => [
                [
                    'query' => '798456780',
                    'post_type' => 'all',
                    'offset' => 0,
                    'filters' => [
                        'post' => true,
                        'comment' => true,
                        'meta' => true,
                        'status' => 'all'
                    ]
                ],
                [
                    'total' => 1
                ]
            ],
            'find all records by email address' => [
                [
                    'query' => 'bob@example.com',
                    'post_type' => 'all',
                    'offset' => 0,
                    'filters' => [
                        'post' => true,
                        'comment' => true,
                        'meta' => true,
                        'status' => 'all'
                    ]
                ],
                [
                    'total' => 1
                ]
            ],
            'no email found if meta filter disabled' => [
                [
                    'query' => 'bob@example.com',
                    'post_type' => 'all',
                    'offset' => 0,
                    'filters' => [
                        'post' => true,
                        'comment' => true,
                        'meta' => false,
                        'status' => 'all'
                    ]
                ],
                [
                    'total' => 0
                ]
            ],
            'no records found with incorrect offset' => [
                [
                    'query' => 'Bob',
                    'post_type' => 'all',
                    'offset' => 10,
                    'filters' => [
                        'post' => true,
                        'comment' => true,
                        'meta' => true,
                        'status' => 'all'
                    ]
                ],
                [
                    'total' => 0
                ]
            ],
            'partial query search' => [
                [
                    'query' => 'b list',
                    'post_type' => 'all',
                    'offset' => 0,
                    'filters' => [
                        'post' => true,
                        'comment' => true,
                        'meta' => true,
                        'status' => 'all'
                    ]
                ],
                [
                    'total' => 1
                ]
            ]
        ];
    }

}
