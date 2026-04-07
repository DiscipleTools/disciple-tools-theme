<?php
/**
 * Class PostsTest
 *
 * @package Disciple.Tools
 */


class DT_Posts_DT_Posts_Search_Viewable_Posts extends WP_UnitTestCase {

    public static $sample_contact = [
        'title' => 'Bob',
        'overall_status' => 'active',
        'milestones' => [ 'values' => [ [ 'value' => 'milestone_has_bible' ], [ 'value' => 'milestone_baptizing' ] ] ],
        'baptism_date' => '2018-12-31',
        'location_grid' => [ 'values' => [ [ 'value' => '100089589' ] ] ],
        'assigned_to' => '1',
        'requires_update' => true,
        'nickname' => 'Bob the builder',
        'contact_phone' => [ 'values' => [ [ 'value' => '798456780' ] ] ],
        'contact_email' => [ 'values' => [ [ 'value' => 'bob@example.com' ] ] ],
        'tags' => [ 'values' => [ [ 'value' => 'tag1' ] ] ],
        'gender' => 'male',
    ];

    public $sample_group = [
        'name' => 'Bob\'s group',
        'group_type' => 'church',
        'location_grid' => [ 'values' => [ [ 'value' => '100089589' ] ] ],
        'member_count' => 5
    ];


    private function map_ids( $posts ){
        return array_map(  function ( $post ){
            return $post->ID;
        }, $posts );
    }

    /**
     * PHPUnit constraints often call Exporter on full values; with WP DB row objects that can surface as a bogus
     * "syntax error" / SERIALIZATION_FRAGMENT failure. These helpers only fail with scalars or JSON of ints.
     * On success they call addToAssertionCount( 1 ) so strict "risky if no assertions" mode stays satisfied.
     */
    private function dt_assert_count( $expected, $haystack, $msg = '' ) {
        if ( ! is_countable( $haystack ) ) {
            $this->fail( $msg . ' haystack not countable' );
        }
        $c     = count( $haystack );
        $exp   = (int) $expected;
        if ( $exp !== $c ) {
            $this->fail( trim( $msg . " expected count $exp, got $c" ) );
        }
        $this->addToAssertionCount( 1 );
    }

    /**
     * @param int   $needle_id Post ID to find.
     * @param int[] $id_list   Values from map_ids().
     */
    private function dt_assert_contains_id( $needle_id, array $id_list, $msg = '' ) {
        $n   = (int) $needle_id;
        $ids = array_map( 'intval', $id_list );
        if ( ! in_array( $n, $ids, true ) ) {
            $this->fail( trim( $msg . ' id ' . $n . ' not in ' . wp_json_encode( $ids ) ) );
        }
        $this->addToAssertionCount( 1 );
    }

    /**
     * @param int   $needle_id Post ID that must be absent.
     * @param int[] $id_list   Values from map_ids().
     */
    private function dt_assert_not_contains_id( $needle_id, array $id_list, $msg = '' ) {
        $n   = (int) $needle_id;
        $ids = array_map( 'intval', $id_list );
        if ( in_array( $n, $ids, true ) ) {
            $this->fail( trim( $msg . ' id ' . $n . ' must not be in ' . wp_json_encode( $ids ) ) );
        }
        $this->addToAssertionCount( 1 );
    }

    private function dt_assert_not_empty_posts( array $posts, $msg = '' ) {
        if ( [] === $posts ) {
            $this->fail( trim( $msg . ' expected non-empty posts' ) );
        }
        $this->addToAssertionCount( 1 );
    }

    private function dt_assert_same_string( $expected, $actual, $msg = '' ) {
        $e = (string) $expected;
        $a = (string) $actual;
        if ( $e !== $a ) {
            $this->fail( trim( $msg . ' expected ' . wp_json_encode( $e ) . ' got ' . wp_json_encode( $a ) ) );
        }
        $this->addToAssertionCount( 1 );
    }

    private function dt_assert_same_int( $expected, $actual, $msg = '' ) {
        if ( (int) $expected !== (int) $actual ) {
            $this->fail( trim( $msg . ' expected int ' . (int) $expected . ' got ' . (int) $actual ) );
        }
        $this->addToAssertionCount( 1 );
    }

    private function dt_assert_gt( int $min, $value, $msg = '' ) {
        if ( (int) $value <= $min ) {
            $this->fail( trim( $msg . ' expected value > ' . $min . ', got ' . (int) $value ) );
        }
        $this->addToAssertionCount( 1 );
    }

    /**
     * Compare search_viewable_post() payloads (totals + post ID order); no PHPUnit asserts on nested data.
     *
     * @param array $a
     * @param array $b
     */
    private function assert_search_responses_equal( array $a, array $b ) {
        $this->dt_assert_same_int( $a['total'] ?? 0, $b['total'] ?? 0, 'search total' );
        $ids_a = array_map( 'intval', $this->map_ids( $a['posts'] ?? [] ) );
        $ids_b = array_map( 'intval', $this->map_ids( $b['posts'] ?? [] ) );
        if ( $ids_a !== $ids_b ) {
            $this->fail( 'post IDs mismatch: ' . wp_json_encode( $ids_a ) . ' vs ' . wp_json_encode( $ids_b ) );
        }
        $this->addToAssertionCount( 1 );
    }

    /**
     * Compare an expected post ID to a DB row object; no assertSame on objects.
     *
     * @param int|string   $expected_id
     * @param object|array $post        Object with ->ID or array with ['ID'].
     * @param string       $message
     */
    private function assert_post_id( $expected_id, $post, $message = '' ) {
        $actual = is_array( $post ) ? ( $post['ID'] ?? null ) : ( $post->ID ?? null );
        if ( $actual === null || $actual === '' ) {
            $this->fail( trim( $message . ' post id missing' ) );
        }
        $this->dt_assert_same_int( $expected_id, $actual, $message );
    }

    /**
     * Replacement for WP core's assertWPError(): assertInstanceOf dumps $actual on failure; exporter then
     * chokes on large search responses (misreported as a PHP "syntax error" / serialization noise).
     *
     * @param mixed $actual
     */
    private function assert_dt_wp_error( $actual, $message = '' ) {
        if ( is_wp_error( $actual ) ) {
            $this->addToAssertionCount( 1 );

            return;
        }
        $this->fail( trim( $message . ' Expected WP_Error; got: ' . self::describe_for_assert( $actual ) ) );
    }

    /**
     * Replacement for assertNotWPError() for the same reason as assert_dt_wp_error().
     *
     * @param mixed $actual
     */
    private function assert_dt_not_wp_error( $actual, $message = '' ) {
        if ( ! is_wp_error( $actual ) ) {
            $this->addToAssertionCount( 1 );

            return;
        }
        $this->fail( trim( $message . ' ' . $actual->get_error_message() ) );
    }

    /**
     * Short description only — never pass live post/search payloads to PHPUnit's exporter.
     *
     * @param mixed $x
     */
    private static function describe_for_assert( $x ) {
        if ( is_array( $x ) ) {
            return 'array keys [ ' . implode( ', ', array_keys( $x ) ) . ' ]';
        }
        if ( is_object( $x ) ) {
            return get_class( $x );
        }
        return gettype( $x );
    }

    public static function setupBeforeClass(): void  {
        $user_id = wp_create_user( 'unittestsearch', 'test', 'unittestsearch@example.com' );
        $user = get_user_by( 'id', $user_id );
        $user->set_role( 'dispatcher' );
        self::$sample_contact['assigned_to'] = $user_id;
        update_option( 'dt_base_user', $user_id );
    }

    public function test_search_fields_structure(){
        $group1 = DT_Posts::create_post( 'groups', $this->sample_group, true, false );
        $this->assert_dt_not_wp_error( $group1 );
        $group2 = DT_Posts::create_post( 'groups', $this->sample_group, true, false );
        $this->assert_dt_not_wp_error( $group2 );
        $sample_contact = DT_Posts::create_post( 'contacts', self::$sample_contact, true, false );
        $this->assert_dt_not_wp_error( $sample_contact );
        $contact1 = DT_Posts::create_post( 'contacts', [ 'name' => 'a', 'groups' => [ 'values' => [ [ 'value' => $group1['ID'] ] ] ] ], true, false );
        $this->assert_dt_not_wp_error( $contact1 );
        $contact2 = DT_Posts::create_post( 'contacts', [ 'name' => 'b', 'groups' => [ 'values' => [ [ 'value' => $group2['ID'] ] ] ] ], true, false );
        $this->assert_dt_not_wp_error( $contact2 );
        $empty_contact = DT_Posts::create_post( 'contacts', [ 'name' => 'x', 'type' => 'placeholder' ], true, false );
        $this->assert_dt_not_wp_error( $empty_contact );
        $empty_group = DT_Posts::create_post( 'groups', [ 'name' => 'x' ], true, false );
        $this->assert_dt_not_wp_error( $empty_group );
        $user_id = wp_create_user( 'test_search_fields_structure', 'test', 'test_search@example.com' );
        $user = get_user_by( 'ID', $user_id );
        $user->set_role( 'multiplier' );



        /**
         * connections
         */
        $res = DT_Posts::search_viewable_post( 'contacts', [ 'groups' => [ $group1['ID'], $group2['ID'] ] ], false );
        $this->dt_assert_count( 2, $res['posts'] );
        $res = DT_Posts::search_viewable_post( 'contacts', [ 'groups' => [ $group1['ID'] ] ], false );
        $this->dt_assert_count( 1, $res['posts'] );
        $this->dt_assert_same_string( 'a', $res['posts'][0]->post_title );
        $res = DT_Posts::search_viewable_post( 'contacts', [ 'groups' => [ '-' . $group1['ID'] ] ], false );
        $this->dt_assert_not_contains_id( $contact1['ID'], $this->map_ids( $res['posts'] ) );
        $this->dt_assert_contains_id( $contact2['ID'], $this->map_ids( $res['posts'] ) );
        $this->dt_assert_contains_id( $sample_contact['ID'], $this->map_ids( $res['posts'] ) );
        //search for posts with no values set for field x
        $res = DT_Posts::search_viewable_post( 'contacts', [ 'groups' => [] ], false );
        $this->dt_assert_contains_id( $empty_contact['ID'], $this->map_ids( $res['posts'] ) );
        $this->dt_assert_not_contains_id( $contact1['ID'], $this->map_ids( $res['posts'] ) );
        // search for all posts with a value set for field x
        $res = DT_Posts::search_viewable_post( 'contacts', [ 'groups' => [ '*' ] ], false );
        $this->dt_assert_not_contains_id( $sample_contact['ID'], $this->map_ids( $res['posts'] ) );
        $this->dt_assert_contains_id( $contact1['ID'], $this->map_ids( $res['posts'] ) );
        $this->dt_assert_contains_id( $contact2['ID'], $this->map_ids( $res['posts'] ) );
        //bad request
        $res = DT_Posts::search_viewable_post( 'contacts', [ 'groups' => $group1['ID'] ], false );
        $this->assert_dt_wp_error( $res );


        /**
         * locations_grid
         */
        $location_contact = DT_Posts::create_post( 'contacts', [ 'name' => 'a', 'location_grid' => [ 'values' => [ [ 'value' => 100089652 ] ] ] ], true, false );
        $this->assert_dt_not_wp_error( $location_contact );
        $location_contact_2 = DT_Posts::create_post( 'contacts', [ 'name' => 'b', 'location_grid' => [ 'values' => [ [ 'value' => 100089652 ] ] ] ], true, false );
        $this->assert_dt_not_wp_error( $location_contact_2 );
        $res = DT_Posts::search_viewable_post( 'contacts', [ 'location_grid' => [ '100089652' ] ], false );
        $this->dt_assert_count( 2, $res['posts'] );
        $all = DT_Posts::search_viewable_post( 'contacts', [], false );
        $res = DT_Posts::search_viewable_post( 'contacts', [ 'location_grid' => [ -100089652 ] ], false );
        $this->dt_assert_same_int( (int) $all['total'] - 2, $res['total'], 'location_grid excluded total' );
        //search for posts with no values set for field x
        $res = DT_Posts::search_viewable_post( 'contacts', [ 'location_grid' => [] ], false );
        $this->dt_assert_contains_id( $empty_contact['ID'], $this->map_ids( $res['posts'] ) );
        $this->dt_assert_not_contains_id( $sample_contact['ID'], $this->map_ids( $res['posts'] ) );
        //bad request
        $res = DT_Posts::search_viewable_post( 'contacts', [ 'location_grid' => 100089652 ], false );
        $this->assert_dt_wp_error( $res );

        /**
         * user_select
         */
        $user_contact = DT_Posts::create_post( 'contacts', [ 'name' => 'user contact', 'assigned_to' => $user_id ], true, false );
        $this->assert_dt_not_wp_error( $user_contact );
        $res = DT_Posts::search_viewable_post( 'contacts', [ 'assigned_to' => [ $user_id ] ], false );
        $this->dt_assert_count( 1, $res['posts'] );
        $all = DT_Posts::search_viewable_post( 'contacts', [], false );
        //search for the contact not assigned to the users with ids $user_id and 493
        $res = DT_Posts::search_viewable_post( 'contacts', [ 'assigned_to' => [ -$user_id, '-493' ] ], false );
        $this->dt_assert_same_int( (int) $all['total'] - 1, $res['total'], 'assigned_to excluded total' );
        //create contact with no assigned to
        $personal_contact = DT_Posts::create_post( 'contacts', [ 'name' => 'x', 'type' => 'placeholder' ], true, false );
        //search for posts with no values set for field x
        $res = DT_Posts::search_viewable_post( 'contacts', [ 'assigned_to' => [] ], false );
        $this->dt_assert_contains_id( $personal_contact['ID'], $this->map_ids( $res['posts'] ) );
        $this->dt_assert_not_contains_id( $sample_contact['ID'], $this->map_ids( $res['posts'] ) );
        //bad request
        $res = DT_Posts::search_viewable_post( 'contacts', [ 'assigned_to' => 1 ], false );
        $this->assert_dt_wp_error( $res );

        /**
         * Date fields
         */
        $baptism = DT_Posts::create_post( 'contacts', [ 'name' => 'x', 'baptism_date' => '1980-01-03' ], true, false );
        $this->assert_dt_not_wp_error( $baptism );
        $range = DT_Posts::search_viewable_post( 'contacts', [ 'baptism_date' => [ 'start' => '1980-01-02', 'end' => '1980-01-04' ] ], false );
        $exact = DT_Posts::search_viewable_post( 'contacts', [ 'baptism_date' => [ 'start' => '1980-01-03', 'end' => '1980-01-03' ] ], false );
        $start = DT_Posts::search_viewable_post( 'contacts', [ 'baptism_date' => [ 'start' => '1980-01-03' ] ], false );
        $end = DT_Posts::search_viewable_post( 'contacts', [ 'baptism_date' => [ 'end' => '1980-01-03' ] ], false );
        $this->assert_post_id( $baptism['ID'], $range['posts'][0] );
        $this->assert_post_id( $baptism['ID'], $exact['posts'][0] );
        $this->dt_assert_gt( 1, $start['total'], 'baptism date open start' );
        $this->assert_post_id( $baptism['ID'], $end['posts'][0] );

        $contact = DT_Posts::create_post( 'contacts', [ 'name' => 'x', 'post_date' => '2003-01-02' ], true, false );
        $this->assert_dt_not_wp_error( $contact );
        $res = DT_Posts::search_viewable_post( 'contacts', [ 'post_date' => [ 'start' => '2003-01-02', 'end' => '2003-01-02' ] ], false );
        $this->dt_assert_count( 1, $res['posts'] );
        $this->assert_post_id( $contact['ID'], $res['posts'][0] );
        $contact = DT_Posts::create_post( 'contacts', [ 'name' => 'x', 'post_date' => '2002-01-02' ], true, false );
        $this->assert_dt_not_wp_error( $contact );
        $res = DT_Posts::search_viewable_post( 'contacts', [ 'name' => 'x', [ 'post_date' => [ 'start' => '2002-01-02', 'end' => '2002-01-02' ] ] ], false );
        $this->dt_assert_count( 1, $res['posts'] );
        $this->assert_post_id( $contact['ID'], $res['posts'][0] );
        $contact = DT_Posts::create_post( 'contacts', [ 'name' => 'x', 'baptism_date' => '2003-01-02' ], true, false );
        $this->assert_dt_not_wp_error( $contact );
        $res = DT_Posts::search_viewable_post( 'contacts', [ 'baptism_date' => [ 'start' => '2003-01-02', 'end' => '2003-01-02' ] ], false );
        $this->dt_assert_count( 1, $res['posts'] );
        $this->assert_post_id( $contact['ID'], $res['posts'][0] );
        //search for posts with no values set for field x
        $res = DT_Posts::search_viewable_post( 'contacts', [ 'baptism_date' => [] ], false );
        $this->dt_assert_contains_id( $empty_contact['ID'], $this->map_ids( $res['posts'] ) );
        $this->dt_assert_not_contains_id( $sample_contact['ID'], $this->map_ids( $res['posts'] ) );
        //bad request
        $res = DT_Posts::search_viewable_post( 'contacts', [ 'baptism_date' => '1980-01-03' ], false );
        $this->assert_dt_wp_error( $res );


        /**
         * Boolean Fields
         */
        $group = DT_Posts::create_post( 'groups', $this->sample_group, true, false );
        $this->assert_dt_not_wp_error( $group );
        $update_needed = DT_Posts::create_post( 'contacts', [ 'name' => 'x', 'requires_update' => true, 'groups' => [ 'values' => [ [ 'value' => $group['ID'] ] ] ] ], true, false );
        $update_not_needed = DT_Posts::create_post( 'contacts', [ 'name' => 'x', 'requires_update' => false, 'groups' => [ 'values' => [ [ 'value' => $group['ID'] ] ] ] ], true, false );
        $bool1 = DT_Posts::search_viewable_post( 'contacts', [ 'requires_update' => [ true ] , 'groups' => [ $group['ID'] ] ], false );
        $bool2 = DT_Posts::search_viewable_post( 'contacts', [ 'requires_update' => [ '1' ] , 'groups' => [ $group['ID'] ] ], false );
        $bool3 = DT_Posts::search_viewable_post( 'contacts', [ 'requires_update' => [ false ] , 'groups' => [ $group['ID'] ] ], false );
        $bool4 = DT_Posts::search_viewable_post( 'contacts', [ 'requires_update' => [ '0' ] , 'groups' => [ $group['ID'] ] ], false );
        $this->assert_post_id( $update_needed['ID'], $bool1['posts'][0] );
        $this->assert_post_id( $update_needed['ID'], $bool2['posts'][0] );
        $this->assert_post_id( $update_not_needed['ID'], $bool3['posts'][0] );
        $this->assert_post_id( $update_not_needed['ID'], $bool4['posts'][0] );
        //search for posts with no values set for field x
        $res = DT_Posts::search_viewable_post( 'contacts', [ 'requires_update' => [] ], false );
        $this->dt_assert_contains_id( $empty_contact['ID'], $this->map_ids( $res['posts'] ) );
        $this->dt_assert_not_contains_id( $sample_contact['ID'], $this->map_ids( $res['posts'] ) );
        //false also includes contacts with the field no set.
        $res = DT_Posts::search_viewable_post( 'contacts', [ 'requires_update' => [ false ] ], false );
        $this->dt_assert_contains_id( $contact1['ID'], $this->map_ids( $res['posts'] ) );
        //bad request
        $res = DT_Posts::search_viewable_post( 'contacts', [ 'requires_update' => true ], false );
        $this->assert_dt_wp_error( $res );


        /**
         * communication_channels
         */
        $phone_contact = DT_Posts::create_post( 'contacts', [ 'name' => 'x', 'contact_phone' => [ 'values' => [ [ 'value' => '798456781' ] ] ] ], true, false );
        $this->assert_dt_not_wp_error( $phone_contact );
        $phone = DT_Posts::search_viewable_post( 'contacts', [ 'contact_phone' => [ '798456780' ] ], false );
        $this->dt_assert_contains_id( $sample_contact['ID'], $this->map_ids( $phone['posts'] ) );
        $phone = DT_Posts::search_viewable_post( 'contacts', [ 'contact_phone' => [ '79845678' ] ], false );
        $this->dt_assert_contains_id( $phone_contact['ID'], $this->map_ids( $phone['posts'] ) );
        $phone = DT_Posts::search_viewable_post( 'contacts', [ 'contact_phone' => [ '-798456780' ] ], false );
        $this->dt_assert_not_contains_id( $sample_contact['ID'], $this->map_ids( $phone['posts'] ) );
        $phone = DT_Posts::search_viewable_post( 'contacts', [ 'contact_phone' => [ '^798456780' ] ], false );
        $this->dt_assert_contains_id( $sample_contact['ID'], $this->map_ids( $phone['posts'] ) );
        $phone = DT_Posts::search_viewable_post( 'contacts', [ 'contact_phone' => [ '^79845678' ] ], false );
        $this->dt_assert_not_contains_id( $sample_contact['ID'], $this->map_ids( $phone['posts'] ) );
        //search for posts with no values set for field x
        $res = DT_Posts::search_viewable_post( 'contacts', [ 'contact_phone' => [] ], false );
        $this->dt_assert_contains_id( $empty_contact['ID'], $this->map_ids( $res['posts'] ) );
        $this->dt_assert_not_contains_id( $sample_contact['ID'], $this->map_ids( $res['posts'] ) );
        //search for posts with any values set for field x
        $res = DT_Posts::search_viewable_post( 'contacts', [ 'contact_phone' => [ '*' ] ], false );
        $this->dt_assert_contains_id( $sample_contact['ID'], $this->map_ids( $res['posts'] ) );
        $this->dt_assert_not_contains_id( $empty_contact['ID'], $this->map_ids( $res['posts'] ) );
        //bad request
        $res = DT_Posts::search_viewable_post( 'contacts', [ 'contact_phone' => '79845678' ], false );
        $this->assert_dt_wp_error( $res );


        /**
         * numbers
         */
        $res = DT_Posts::search_viewable_post( 'groups', [ 'member_count' => [ 'number' => '5' ] ], false );
        $this->dt_assert_contains_id( $group1['ID'], $this->map_ids( $res['posts'] ) );
        $res = DT_Posts::search_viewable_post( 'groups', [ 'member_count' => [ 'number' => '5', 'operator' => '>=' ] ], false );
        $this->dt_assert_contains_id( $group1['ID'], $this->map_ids( $res['posts'] ) );
        $res = DT_Posts::search_viewable_post( 'groups', [ 'member_count' => [ 'number' => '5', 'operator' => '<' ] ], false );
        $this->dt_assert_not_contains_id( $group1['ID'], $this->map_ids( $res['posts'] ) );
        $res = DT_Posts::search_viewable_post( 'groups', [ 'member_count' => 5 ], false );
        $this->dt_assert_contains_id( $group1['ID'], $this->map_ids( $res['posts'] ) );
        // search for posts with no values set for field x
        $res = DT_Posts::search_viewable_post( 'groups', [ 'member_count' => [] ], false );
        $this->dt_assert_contains_id( $empty_group['ID'], $this->map_ids( $res['posts'] ) );
        $this->dt_assert_not_contains_id( $group1['ID'], $this->map_ids( $res['posts'] ) );

        /**
         * text
         */
        $nick = DT_Posts::create_post( 'contacts', [ 'name' => 'a', 'nickname' => 'Bob the teacher' ], true, false );
        $this->assert_dt_not_wp_error( $nick );
        $res = DT_Posts::search_viewable_post( 'contacts', [ 'nickname' => [ 'Bob the builder' ] ], false );
        $this->dt_assert_contains_id( $sample_contact['ID'], $this->map_ids( $res['posts'] ) );
        $res = DT_Posts::search_viewable_post( 'contacts', [ 'nickname' => [ 'build' ] ], false );
        $this->dt_assert_contains_id( $sample_contact['ID'], $this->map_ids( $res['posts'] ) );
        $res = DT_Posts::search_viewable_post( 'contacts', [ 'nickname' => [ 'something', 'build' ] ], false );
        $this->dt_assert_contains_id( $sample_contact['ID'], $this->map_ids( $res['posts'] ) );
        $res = DT_Posts::search_viewable_post( 'contacts', [ 'nickname' => [ 'something' ] ], false );
        $this->dt_assert_not_contains_id( $sample_contact['ID'], $this->map_ids( $res['posts'] ) );
        $res = DT_Posts::search_viewable_post( 'contacts', [ 'nickname' => [ '-build' ] ], false );
        $this->dt_assert_not_contains_id( $sample_contact['ID'], $this->map_ids( $res['posts'] ) );
        $this->dt_assert_contains_id( $contact1['ID'], $this->map_ids( $res['posts'] ) );
        $res = DT_Posts::search_viewable_post( 'contacts', [ 'nickname' => [ '-this name does not exist' ] ], false );
        $this->dt_assert_contains_id( $sample_contact['ID'], $this->map_ids( $res['posts'] ) );
        $res = DT_Posts::search_viewable_post( 'contacts', [ 'nickname' => [ '-build', 'bob' ] ], false );
        $this->dt_assert_contains_id( $nick['ID'], $this->map_ids( $res['posts'] ) );
        $this->dt_assert_count( 1, $res['posts'] );
        $res = DT_Posts::search_viewable_post( 'contacts', [ 'nickname' => [ '^build' ] ], false );
        $this->dt_assert_count( 0, $res['posts'] );
        $res = DT_Posts::search_viewable_post( 'contacts', [ 'nickname' => [ '^Bob the teacher' ] ], false );
        $this->dt_assert_count( 1, $res['posts'] );
        $this->dt_assert_contains_id( $nick['ID'], $this->map_ids( $res['posts'] ) );
        //search for posts with no values set for field x
        $res = DT_Posts::search_viewable_post( 'contacts', [ 'nickname' => [] ], false );
        $this->dt_assert_contains_id( $empty_contact['ID'], $this->map_ids( $res['posts'] ) );
        $this->dt_assert_not_contains_id( $sample_contact['ID'], $this->map_ids( $res['posts'] ) );
        //search for posts with any values set for field x
        $res = DT_Posts::search_viewable_post( 'contacts', [ 'nickname' => [ '*' ] ], false );
        $this->dt_assert_contains_id( $sample_contact['ID'], $this->map_ids( $res['posts'] ) );
        $this->dt_assert_not_contains_id( $empty_contact['ID'], $this->map_ids( $res['posts'] ) );
        //bad request
        $res = DT_Posts::search_viewable_post( 'contacts', [ 'nickname' => 'Bob' ], false );
        $this->assert_dt_wp_error( $res );
        //name
        $res = DT_Posts::search_viewable_post( 'contacts', [ 'name' => [ 'Bob' ] ], false );
        $this->dt_assert_contains_id( $sample_contact['ID'], $this->map_ids( $res['posts'] ) );
        $res = DT_Posts::search_viewable_post( 'contacts', [ 'name' => 'Bob' ], false );
        $this->dt_assert_contains_id( $sample_contact['ID'], $this->map_ids( $res['posts'] ) );
        $res = DT_Posts::search_viewable_post( 'contacts', [ [ 'name' => 'Bob' ] ], false );
        $this->dt_assert_contains_id( $sample_contact['ID'], $this->map_ids( $res['posts'] ) );
        $res = DT_Posts::search_viewable_post( 'contacts', [ [ 'name' => [ 'Bob' ] ] ], false );
        $this->dt_assert_contains_id( $sample_contact['ID'], $this->map_ids( $res['posts'] ) );
        $res = DT_Posts::search_viewable_post( 'contacts', [ 'name' => [ '^Bo' ] ], false );
        $this->dt_assert_count( 0, $res['posts'] );
        $res = DT_Posts::search_viewable_post( 'contacts', [ 'name' => [ '^Bob' ] ], false );
        $this->dt_assert_contains_id( $sample_contact['ID'], $this->map_ids( $res['posts'] ) );


        /**
         * key_select
         */
        $paused = DT_Posts::create_post( 'contacts', [ 'name' => 'x', 'overall_status' => 'paused', 'gender' => 'male' ], true, false );
        $this->assert_dt_not_wp_error( $paused );
        $res = DT_Posts::search_viewable_post( 'contacts', [ 'overall_status' => [ 'paused', 'active' ] ], false );
        $this->dt_assert_contains_id( $sample_contact['ID'], $this->map_ids( $res['posts'] ) );
        $this->dt_assert_contains_id( $paused['ID'], $this->map_ids( $res['posts'] ) );
        //negative values
        $res = DT_Posts::search_viewable_post( 'contacts', [ 'overall_status' => [ '-active', 'paused' ] ], false );
        $this->dt_assert_not_contains_id( $sample_contact['ID'], $this->map_ids( $res['posts'] ) );
        $this->dt_assert_contains_id( $paused['ID'], $this->map_ids( $res['posts'] ) );
        $res = DT_Posts::search_viewable_post( 'contacts', [ 'overall_status' => [ '-closed' ] ], false );
        $this->dt_assert_contains_id( $sample_contact['ID'], $this->map_ids( $res['posts'] ) );
        //empty search = with none of the field
        $res = DT_Posts::search_viewable_post( 'contacts', [ 'gender' => [] ], false );
        $this->dt_assert_not_empty_posts( $res['posts'] );
        $this->dt_assert_not_contains_id( $paused['ID'], $this->map_ids( $res['posts'] ) );
        $this->dt_assert_not_contains_id( $sample_contact['ID'], $this->map_ids( $res['posts'] ) );
        $this->dt_assert_contains_id( $empty_contact['ID'], $this->map_ids( $res['posts'] ) );
        //bad request
        $res = DT_Posts::search_viewable_post( 'contacts', [ 'overall_status' => 'active' ], false );
        $this->assert_dt_wp_error( $res );

        //check that the paused contact doesn't show up in the "none" search
        $res = DT_Posts::search_viewable_post( 'contacts', [ 'overall_status' => [ 'none' ] ], false );
        $this->dt_assert_not_contains_id( $paused['ID'], $this->map_ids( $res['posts'] ) );
        delete_post_meta( $paused['ID'], 'overall_status' );
        //check that the contact does show up in the "none" search not that the meta is removed
        $res = DT_Posts::search_viewable_post( 'contacts', [ 'overall_status' => [ 'none' ] ], false );
        $this->dt_assert_contains_id( $paused['ID'], $this->map_ids( $res['posts'] ) );

        /*
         * multi_select
         */
        $in_group = DT_Posts::create_post( 'contacts', [ 'name' => 'x', 'milestones' => [ 'values' => [ [ 'value' =>'milestone_in_group' ] ] ] ], true, false );
        $this->assert_dt_not_wp_error( $in_group );
        $res = DT_Posts::search_viewable_post( 'contacts', [ 'milestones' => [ 'milestone_has_bible' ] ], false );
        $this->dt_assert_contains_id( $sample_contact['ID'], $this->map_ids( $res['posts'] ) );
        $this->dt_assert_not_contains_id( $in_group['ID'], $this->map_ids( $res['posts'] ) );
        //negative filter
        $res = DT_Posts::search_viewable_post( 'contacts', [ 'milestones' => [ '-milestone_has_bible' ] ], false );
        $this->dt_assert_not_contains_id( $sample_contact['ID'], $this->map_ids( $res['posts'] ) );
        $this->dt_assert_contains_id( $in_group['ID'], $this->map_ids( $res['posts'] ) );
        $res = DT_Posts::search_viewable_post( 'contacts', [ 'milestones' => [ '-milestone_planting' ] ], false );
        $this->dt_assert_contains_id( $sample_contact['ID'], $this->map_ids( $res['posts'] ) );
        //empty search = with none of the field
        $res = DT_Posts::search_viewable_post( 'contacts', [ 'milestones' => [] ], false );
        $this->dt_assert_not_empty_posts( $res['posts'] );
        $this->dt_assert_not_contains_id( $in_group['ID'], $this->map_ids( $res['posts'] ) );
        $this->dt_assert_not_contains_id( $sample_contact['ID'], $this->map_ids( $res['posts'] ) );
        $this->dt_assert_contains_id( $empty_contact['ID'], $this->map_ids( $res['posts'] ) );
        //bad request
        $res = DT_Posts::search_viewable_post( 'contacts', [ 'milestones' => 'active' ], false );
        $this->assert_dt_wp_error( $res );

        /*
         * tags
         */
        $in_group = DT_Posts::create_post( 'contacts', [ 'name' => 'x', 'tags' => [ 'values' => [ [ 'value' =>'in_group1' ] ] ] ], true, false );
        $this->assert_dt_not_wp_error( $in_group );
        $res = DT_Posts::search_viewable_post( 'contacts', [ 'tags' => [ 'tag1' ] ], false );
        $this->dt_assert_contains_id( $sample_contact['ID'], $this->map_ids( $res['posts'] ) );
        $this->dt_assert_not_contains_id( $in_group['ID'], $this->map_ids( $res['posts'] ) );
        //negative filter
        $res = DT_Posts::search_viewable_post( 'contacts', [ 'tags' => [ '-tag1' ] ], false );
        $this->dt_assert_not_contains_id( $sample_contact['ID'], $this->map_ids( $res['posts'] ) );
        $this->dt_assert_contains_id( $in_group['ID'], $this->map_ids( $res['posts'] ) );
        $res = DT_Posts::search_viewable_post( 'contacts', [ 'tags' => [ '-in_group1' ] ], false );
        $this->dt_assert_contains_id( $sample_contact['ID'], $this->map_ids( $res['posts'] ) );
        //empty search = with none of the field
        $res = DT_Posts::search_viewable_post( 'contacts', [ 'tags' => [] ], false );
        $this->dt_assert_not_empty_posts( $res['posts'] );
        $this->dt_assert_not_contains_id( $in_group['ID'], $this->map_ids( $res['posts'] ) );
        $this->dt_assert_not_contains_id( $sample_contact['ID'], $this->map_ids( $res['posts'] ) );
        $this->dt_assert_contains_id( $empty_contact['ID'], $this->map_ids( $res['posts'] ) );
        //bad request
        $res = DT_Posts::search_viewable_post( 'contacts', [ 'tags' => 'in_group1' ], false );
        $this->assert_dt_wp_error( $res );


        /**
         * Default fields
         */
        $contact = DT_Posts::create_post( 'contacts', [ 'name' => 'dh39ent' ], true, false );
        $this->assert_dt_not_wp_error( $contact );
        $res = DT_Posts::search_viewable_post( 'contacts', [ 'name' => [ 'dh39ent' ] ], false );
        $this->dt_assert_count( 1, $res['posts'] );
        $this->assert_post_id( $contact['ID'], $res['posts'][0] );

        /**
         * weird
         */
        $res = DT_Posts::search_viewable_post( 'contacts', [ 'some_random_key' => [ $group1['ID'], $group2['ID'] ] ], false );
        $this->assert_dt_wp_error( $res );
        $res = DT_Posts::search_viewable_post( 'contacts_bad_type', [ 'groups' => [ $group1['ID'], $group2['ID'] ] ], false );
        $this->assert_dt_wp_error( $res );
        $res = DT_Posts::search_viewable_post( 'contacts', [ 'member_count' => [] ], false );
        $this->assert_dt_wp_error( $res );


        /**
         * Search input
         */
        $res = DT_Posts::search_viewable_post( 'contacts', [ 'text' => 'Bob' ], false );
        $this->dt_assert_contains_id( $sample_contact['ID'], $this->map_ids( $res['posts'] ) );
        $res = DT_Posts::search_viewable_post( 'contacts', [ 'text' => 'ob' ], false );
        $this->dt_assert_contains_id( $sample_contact['ID'], $this->map_ids( $res['posts'] ) );
        $res = DT_Posts::search_viewable_post( 'contacts', [ 'text' => '798456780' ], false );
        $this->dt_assert_contains_id( $sample_contact['ID'], $this->map_ids( $res['posts'] ) );
        $res = DT_Posts::search_viewable_post( 'contacts', [ 'text' => '6780' ], false );
        $this->dt_assert_contains_id( $sample_contact['ID'], $this->map_ids( $res['posts'] ) );
        $res = DT_Posts::search_viewable_post( 'contacts', [ 'text' => 'example.com' ], false );
        $this->dt_assert_contains_id( $sample_contact['ID'], $this->map_ids( $res['posts'] ) );
        $res = DT_Posts::search_viewable_post( 'contacts', [ 'text' => 'bob@example.com' ], false );
        $this->dt_assert_contains_id( $sample_contact['ID'], $this->map_ids( $res['posts'] ) );


        /**
         * structure
         * AND / OR layers
         */
        $group = DT_Posts::create_post( 'groups', [ 'name' => 'this_is_a_group1' ], true, false );
        $this->assert_dt_not_wp_error( $group );
        $c1 = DT_Posts::create_post( 'contacts', [ 'name' => 'this_is_a_test1', 'assigned_to' => self::$sample_contact['assigned_to'], 'gender' => 'male', 'groups' => [ 'values' => [ [ 'value' => $group['ID'] ] ] ] ], true, false );
        $this->assert_dt_not_wp_error( $c1 );
        $c2 = DT_Posts::create_post( 'contacts', [ 'name' => 'this_is_a_test2', 'assigned_to' => self::$sample_contact['assigned_to'], 'gender' => 'male', 'groups' => [ 'values' => [ [ 'value' => $group['ID'] ] ] ] ], true, false );
        $this->assert_dt_not_wp_error( $c2 );
        //name1 and name 2
        $res1 = DT_Posts::search_viewable_post( 'contacts', [ [ 'name' => [ 'this_is_a_test1' ] ], [ 'name' => [ 'this_is_a_test2' ] ] ], false );
        //with fields key
        $res2 = DT_Posts::search_viewable_post( 'contacts', [ 'fields' => [ [ 'name' => [ 'this_is_a_test1' ] ], [ 'name' => [ 'this_is_a_test2' ] ] ] ], false );
        $this->dt_assert_count( 0, $res1['posts'] );
        $this->dt_assert_count( 0, $res2['posts'] );
        $this->assert_search_responses_equal( $res1, $res2 );
        //name1 or name 2
        $res1 = DT_Posts::search_viewable_post( 'contacts', [ [ [ 'name' => [ 'this_is_a_test1' ] ], [ 'name' => [ 'this_is_a_test2' ] ] ] ], false );
        //with fields key
        $res2 = DT_Posts::search_viewable_post( 'contacts', [ 'fields' => [ [ [ 'name' => [ 'this_is_a_test1' ] ], [ 'name' => [ 'this_is_a_test2' ] ] ] ] ], false );
        $this->dt_assert_count( 2, $res1['posts'] );
        $this->dt_assert_count( 2, $res2['posts'] );
        $this->assert_search_responses_equal( $res1, $res2 );

        //more ANDs
        $res1 = DT_Posts::search_viewable_post( 'contacts', [ 'name' => [ 'this_is_a_test1' ], 'gender' => [ 'male' ], 'groups' => [ $group['ID'] ] ], false );
        $res2 = DT_Posts::search_viewable_post( 'contacts', [ 'fields' => [ 'name' => [ 'this_is_a_test1' ], 'gender' => [ 'male' ], 'groups' => [ $group['ID'] ] ] ], false );
        $this->dt_assert_count( 1, $res2['posts'] );
        $this->assert_search_responses_equal( $res1, $res2 );

        //mixing ANDs and ORs
        $res1 = DT_Posts::search_viewable_post( 'contacts', [ [ 'name' => [ 'this_is_a_test1' ], 'gender' => [ 'male' ], ], 'groups' => [ $group['ID'] ] ], false );
        $res2 = DT_Posts::search_viewable_post( 'contacts', [ 'fields' => [ [ 'name' => [ 'this_is_a_test1' ], 'gender' => [ 'male' ] ], [ 'groups' => [ $group['ID'] ] ] ] ], false );
        $this->dt_assert_count( 2, $res2['posts'] );
        $this->assert_search_responses_equal( $res1, $res2 );
    }
}
