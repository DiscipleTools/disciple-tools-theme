<?php
require_once get_template_directory() . '/tests/dt-posts/tests-setup.php';

/**
 * @testdox DT_Posts::update_post
 */
class DT_Posts_DT_Posts_List_Posts extends WP_UnitTestCase {

    public static $sample_contact_bob = array(
        'title'              => 'Bob List',
        'overall_status'     => 'active',
        'milestones'         => array(
            'values' => array(
                array( 'value' => 'milestone_has_bible' ),
                array( 'value' => 'milestone_baptizing' ),
            ),
        ),
        'baptism_date'       => '2018-12-31',
        'location_grid'      => array( 'values' => array( array( 'value' => '100089589' ) ) ),
        'assigned_to'        => '1',
        'requires_update'    => true,
        'nickname'           => 'Bob the builder',
        'contact_phone'      => array( 'values' => array( array( 'value' => '798456780' ) ) ),
        'contact_email'      => array( 'values' => array( array( 'value' => 'bob@example.com' ) ) ),
        'tags'               => array( 'values' => array( array( 'value' => 'tag1' ) ) ),
        'baptism_generation' => 4,
    );
    public static $sample_contact_john_doe = array(
        'title'              => 'John Doe',
        'overall_status'     => 'active',
    );
    public static $sample_contact_john_bob_doe = array(
        'title'              => 'John bob Doe',
        'overall_status'     => 'active',
    );
    public static $sample_contact_john = array(
        'title'              => 'John',
        'overall_status'     => 'active',
    );
    public static $sample_contact_johndoe = array(
        'title'              => 'JohnDoe',
        'overall_status'     => 'active',
    );
    public static $sample_contact_doe_john = array(
        'title'              => 'Doe John',
        'overall_status'     => 'active',
    );

    public static $sample_group = array(
        'name'          => 'Bob\'s group',
        'group_type'    => 'church',
        'location_grid' => array( 'values' => array( array( 'value' => '100089589' ) ) ),
        'member_count'  => 5,
    );

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
        return array(
            'match on contact name'                  => array(
                'contacts',
                array( 'name' => array( 'Bob List' ) ),
                1,
                array(
                    'idx'   => 0,
                    'key'   => 'name',
                    'value' => 'Bob List',
                ),
            ),
            'match on contact milestone'             => array(
                'contacts',
                array( 'milestones' => array( 'milestone_has_bible' ) ),
                1,
                array(
                    'idx'   => 0,
                    'key'   => 'milestones',
                    'value' => array( 'milestone_has_bible', 'milestone_baptizing' ),
                ),
            ),
            'match on contact baptism generation'    => array(
                'contacts',
                array(
                    'baptism_generation' => array(
                        'operator' => '>=',
                        'number'   => '3',
                    ),
                ),
                1,
                array(
                    'idx'   => 0,
                    'key'   => 'baptism_generation',
                    'value' => 4,
                ),
            ),
            'no match on contact baptism generation' => array(
                'contacts',
                array(
                    'baptism_generation' => array(
                        'operator' => '>',
                        'number'   => '4',
                    ),
                ),
                0,
                array(),
            ),
            'match on group type'                    => array(
                'groups',
                array( 'group_type' => array( 'church' ) ),
                1,
                array(
                    'idx'   => 0,
                    'key'   => 'group_type',
                    'value' => array(
                        'key'   => 'church',
                        'label' => 'Church',
                    ),
                ),
            ),
            'no match on group name'                 => array(
                'groups',
                array( 'name' => array( '-Bob\'s group' ) ),
                0,
                array(),
            ),
            'search for John' => array(
                'contacts',
                array( 'text' => 'John' ),
                5,
                array(),
            ),
            'search for JohnD' => array(
                'contacts',
                array( 'text' => 'JohnD' ),
                1,
                array(),
            ),
            'search for John Doe' => array(
                'contacts',
                array( 'text' => 'John Doe' ),
                3,
                array(),
            ),
            'search for bob' => array(
                'contacts',
                array( 'text' => 'bob' ),
                2,
                array(),
            ),
            'search for doe' => array(
                'contacts',
                array( 'text' => 'doe' ),
                4,
                array(),
            ),
        );
    }
}
