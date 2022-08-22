<?php
require_once( get_template_directory() . '/tests/dt-posts/tests-setup.php' );

/**
 * @testdox DT_Posts::update_post
 */
class DT_Posts_DT_Posts_List_Posts extends WP_UnitTestCase {

    public static $sample_contact = [
        'title'              => 'Bob',
        'overall_status'     => 'active',
        'milestones'         => [
            "values" => [
                [ "value" => 'milestone_has_bible' ],
                [ "value" => "milestone_baptizing" ]
            ]
        ],
        'baptism_date'       => "2018-12-31",
        "location_grid"      => [ "values" => [ [ "value" => '100089589' ] ] ],
        "assigned_to"        => "1",
        "requires_update"    => true,
        "nickname"           => "Bob the builder",
        "contact_phone"      => [ "values" => [ [ "value" => "798456780" ] ] ],
        "contact_email"      => [ "values" => [ [ "value" => "bob@example.com" ] ] ],
        "tags"               => [ "values" => [ [ "value" => "tag1" ] ] ],
        'baptism_generation' => 4
    ];

    public static $sample_group = [
        'name'          => 'Bob\'s group',
        'group_type'    => 'church',
        "location_grid" => [ "values" => [ [ "value" => '100089589' ] ] ],
        "member_count"  => 5
    ];

    public static $contact = null;
    public static $group = null;

    public static function setupBeforeClass() {
        self::$contact = DT_Posts::create_post( "contacts", self::$sample_contact, true, false );
        self::$group   = DT_Posts::create_post( "groups", self::$sample_group, true, false );
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
                [ 'Bob' ],
                1,
                [
                    'idx'   => 0,
                    'key'   => 'name',
                    'value' => 'Bob'
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
            ]
        ];
    }
}
