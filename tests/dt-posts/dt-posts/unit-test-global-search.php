<?php
require_once get_template_directory() . '/tests/dt-posts/tests-setup.php';

/**
 * @testdox DT_Posts::advanced_search
 */
class DT_Posts_DT_Posts_Global_Search extends WP_UnitTestCase{

    public static $sample_contact = array(
        'title' => 'Bob List',
        'overall_status' => 'active',
        'milestones' => array(
            'values' => array(
                array( 'value' => 'milestone_has_bible' ),
                array( 'value' => 'milestone_baptizing' ),
            ),
        ),
        'baptism_date' => '2018-12-31',
        'location_grid' => array( 'values' => array( array( 'value' => '100089589' ) ) ),
        'requires_update' => true,
        'nickname' => 'Bob the builder',
        'contact_phone' => array( 'values' => array( array( 'value' => '798456780' ) ) ),
        'contact_email' => array( 'values' => array( array( 'value' => 'bob@example.com' ) ) ),
        'tags' => array( 'values' => array( array( 'value' => 'tag1' ) ) ),
        'baptism_generation' => 4,
        'notes' => array(
            'Test comment for bob!',
        ),
    );

    public static $sample_group = array(
        'name' => 'Bob\'s group',
        'group_type' => 'church',
        'location_grid' => array( 'values' => array( array( 'value' => '100089589' ) ) ),
        'member_count' => 5,
    );

    public static $user = null;
    public static $contact = null;
    public static $group = null;

    public static function setupBeforeClass(): void{
        $user_id = wp_create_user( 'dispatcher4', 'test', 'testdisp4@example.com' );
        self::$user = get_user_by( 'id', $user_id );
        self::$user->set_role( 'dispatcher' );

        self::$sample_contact['assigned_to'] = $user_id;
        self::$sample_group['assigned_to'] = $user_id;
        wp_set_current_user( $user_id );

        self::$contact = DT_Posts::create_post( 'contacts', self::$sample_contact, true, false );
        self::$group = DT_Posts::create_post( 'groups', self::$sample_group, true, false );
    }

    /**
     * @dataProvider provide_global_search_query_data
     */
    public function test_global_searches( $args, $expected ){
        wp_set_current_user( self::$user->ID );

        // fwrite( STDERR, print_r( wp_get_current_user(), true ) );
        $result = DT_Posts::advanced_search( $args['query'], $args['post_type'], $args['offset'], $args['filters'], false );

        $this->assertNotWPError( $result );
        $this->assertSame( $result['total_hits'], $expected['total'] );
    }

    public function provide_global_search_query_data(): array{
        return array(
            'match all bob references' => array(
                array(
                    'query' => 'Bob',
                    'post_type' => 'all',
                    'offset' => 0,
                    'filters' => array(
                        'post' => true,
                        'comment' => true,
                        'meta' => true,
                        'status' => 'all',
                    ),
                ),
                array(
                    'total' => 2,
                ),
            ),
            'match all bob references in contacts' => array(
                array(
                    'query' => 'bob',
                    'post_type' => 'contacts',
                    'offset' => 0,
                    'filters' => array(
                        'post' => true,
                        'comment' => true,
                        'meta' => true,
                        'status' => 'all',
                    ),
                ),
                array(
                    'total' => 1,
                ),
            ),
            'match all bob references in contacts comments' => array(
                array(
                    'query' => 'comment for bob',
                    'post_type' => 'contacts',
                    'offset' => 0,
                    'filters' => array(
                        'post' => true,
                        'comment' => true,
                        'meta' => true,
                        'status' => 'all',
                    ),
                ),
                array(
                    'total' => 1,
                ),
            ),
            'no match across all post types' => array(
                array(
                    'query' => 'Frank',
                    'post_type' => 'all',
                    'offset' => 0,
                    'filters' => array(
                        'post' => true,
                        'comment' => true,
                        'meta' => true,
                        'status' => 'all',
                    ),
                ),
                array(
                    'total' => 0,
                ),
            ),
            'match all bob references in groups' => array(
                array(
                    'query' => 'Bob',
                    'post_type' => 'groups',
                    'offset' => 0,
                    'filters' => array(
                        'post' => true,
                        'comment' => true,
                        'meta' => true,
                        'status' => 'all',
                    ),
                ),
                array(
                    'total' => 1,
                ),
            ),
            'find all records by phone number' => array(
                array(
                    'query' => '798456780',
                    'post_type' => 'all',
                    'offset' => 0,
                    'filters' => array(
                        'post' => true,
                        'comment' => true,
                        'meta' => true,
                        'status' => 'all',
                    ),
                ),
                array(
                    'total' => 1,
                ),
            ),
            'find all records by email address' => array(
                array(
                    'query' => 'bob@example.com',
                    'post_type' => 'all',
                    'offset' => 0,
                    'filters' => array(
                        'post' => true,
                        'comment' => true,
                        'meta' => true,
                        'status' => 'all',
                    ),
                ),
                array(
                    'total' => 1,
                ),
            ),
            'no email found if meta filter disabled' => array(
                array(
                    'query' => 'bob@example.com',
                    'post_type' => 'all',
                    'offset' => 0,
                    'filters' => array(
                        'post' => true,
                        'comment' => true,
                        'meta' => false,
                        'status' => 'all',
                    ),
                ),
                array(
                    'total' => 0,
                ),
            ),
            'no records found with incorrect offset' => array(
                array(
                    'query' => 'Bob',
                    'post_type' => 'all',
                    'offset' => 10,
                    'filters' => array(
                        'post' => true,
                        'comment' => true,
                        'meta' => true,
                        'status' => 'all',
                    ),
                ),
                array(
                    'total' => 0,
                ),
            ),
            'partial query search' => array(
                array(
                    'query' => 'b list',
                    'post_type' => 'all',
                    'offset' => 0,
                    'filters' => array(
                        'post' => true,
                        'comment' => true,
                        'meta' => true,
                        'status' => 'all',
                    ),
                ),
                array(
                    'total' => 1,
                ),
            ),
        );
    }
}
