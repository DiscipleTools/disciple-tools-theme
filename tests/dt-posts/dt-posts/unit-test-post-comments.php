<?php
require_once( get_template_directory() . '/tests/dt-posts/tests-setup.php' );

class DT_Posts_DT_Posts_Post_Comments extends WP_UnitTestCase {

    public static $sample_contact = [
        'title'           => 'Bob',
        'overall_status'  => 'active',
        'milestones'      => [
            'values' => [
                [ 'value' => 'milestone_has_bible' ],
                [ 'value' => 'milestone_baptizing' ]
            ]
        ],
        'baptism_date'    => '2022-01-13',
        'location_grid'   => [ 'values' => [ [ 'value' => '100089589' ] ] ],
        'assigned_to'     => '1',
        'requires_update' => true,
        'nickname'        => 'Bob the builder',
        'contact_phone'   => [ 'values' => [ [ 'value' => '798456780' ] ] ],
        'contact_email'   => [ 'values' => [ [ 'value' => 'bob@example.com' ] ] ],
        'tags'            => [ 'values' => [ [ 'value' => 'tag1' ], [ 'value' => 'tagToDelete' ] ] ],
    ];
    public static $contact = null;

    public static function setupBeforeClass(): void {
        //setup custom fields for each field type and custom tile.
        $user_id = wp_create_user( 'dispatcher1', 'test', 'test2@example.com' );
        wp_set_current_user( $user_id );
        $current_user = wp_get_current_user();
        $current_user->set_role( 'dispatcher' );

        self::$contact = DT_Posts::create_post( 'contacts', self::$sample_contact, true, false );
    }

    /**
     * @dataProvider valid_dates_data_provider
     */
    public function test_comment_creation_with_valid_data( $comment_date ) {

        $comment_args = [
            'comment_date' => $comment_date
        ];
        $comment_html = 'Valid data comments test';
        $comment_id   = DT_Posts::add_post_comment( self::$contact['post_type'], self::$contact['ID'], $comment_html, 'comment', $comment_args, false, true );
        $comments     = DT_Posts::get_post_comments( self::$contact['post_type'], self::$contact['ID'], false );

        $this->assertNotWPError( $comment_id );
        $this->assertSame( 1, (int) $comments['total'] );
        $this->assertSame( $comment_html, $comments['comments'][0]['comment_content'] );
    }

    public function valid_dates_data_provider(): array {
        return [
            [ '' ],
            [ '2022-01-13 12:00:00' ],
            [ '2019-12-25T15:54:55+0000' ],
        ];
    }

    /**
     * @dataProvider invalid_dates_data_provider
     */
    public function test_comment_creation_with_invalid_data( $comment_date ) {

        $comment_args  = [
            'comment_date' => $comment_date
        ];
        $comment_html  = 'Invalid data comments test';
        $comment_error = DT_Posts::add_post_comment( self::$contact['post_type'], self::$contact['ID'], $comment_html, 'comment', $comment_args, false, true );

        $error_msg = 'Invalid date! Correct format should be: Y-m-d H:i:s';
        $this->assertWPError( $comment_error );
        $this->assertSame( $error_msg, $comment_error->get_error_message() );
    }

    public function invalid_dates_data_provider(): array {
        return [
            [ 'null' ],
            [ '7/6/2009 14:16' ],
            [ 'Y-m-d H:i:s' ],
            [ '0000-00-00 00:00:00' ]
        ];
    }

    public function test_comment_updates() {

        $comment_args         = [
            'comment_date' => '2022-01-13 13:00:00'
        ];
        $comment_html         = 'Initial comment....';
        $comment_id           = DT_Posts::add_post_comment( self::$contact['post_type'], self::$contact['ID'], $comment_html, 'comment', $comment_args, false, true );
        $comments             = DT_Posts::get_post_comments( self::$contact['post_type'], self::$contact['ID'], false );
        $comment_content_orig = $comments['comments'][0]['comment_content'];

        $comment_html_updated = 'Updated comment....';
        DT_Posts::update_post_comment( $comment_id, $comment_html_updated, false );
        $comments                = DT_Posts::get_post_comments( self::$contact['post_type'], self::$contact['ID'], false );
        $comment_content_updated = $comments['comments'][0]['comment_content'];

        $this->assertSame( $comment_html, $comment_content_orig );
        $this->assertSame( $comment_html_updated, $comment_content_updated );
    }

    public function test_comment_deletes() {

        $comment_args   = [
            'comment_date' => '2022-01-13 14:00:00'
        ];
        $comment_html   = 'Deleted comment test....';
        $comment_id     = DT_Posts::add_post_comment( self::$contact['post_type'], self::$contact['ID'], $comment_html, 'comment', $comment_args, false, true );
        $comments       = DT_Posts::get_post_comments( self::$contact['post_type'], self::$contact['ID'], false );
        $comments_total = $comments['total'];

        DT_Posts::delete_post_comment( $comment_id, false );
        $comments                    = DT_Posts::get_post_comments( self::$contact['post_type'], self::$contact['ID'], false );
        $comments_total_after_delete = $comments['total'];

        $this->assertSame( 1, (int) $comments_total );
        $this->assertSame( 0, (int) $comments_total_after_delete );
    }
}
