<?php

/**
 * @testdox DT_Posts connection field permissions
 */
class DT_Posts_DT_Posts_Connection_Permissions extends WP_UnitTestCase {

    private $user_id;
    private $hidden_group_id;
    private $shared_group_id;
    private $contact_id;

    public function set_up(): void {
        parent::set_up();
        $this->user_id = wp_create_user( 'connection_multiplier', 'test', 'connection_multiplier@example.com' );
        $user = get_user_by( 'id', $this->user_id );
        $user->set_role( 'multiplier' );

        $hidden_group = DT_Posts::create_post( 'groups', [ 'title' => 'Hidden group' ], true, false );
        $this->hidden_group_id = $hidden_group['ID'];

        wp_set_current_user( $this->user_id );
        $shared_group = DT_Posts::create_post( 'groups', [ 'title' => 'Shared group' ], true );
        $this->shared_group_id = $shared_group['ID'];
        $contact = DT_Posts::create_post( 'contacts', [ 'title' => 'Connection contact' ], true );
        $this->contact_id = $contact['ID'];
    }

    private function get_group_ids( $post_id ){
        $post = DT_Posts::get_post( 'contacts', $post_id, false, false );
        return array_map( function ( $group ){
            return (int) $group['ID'];
        }, $post['groups'] ?? [] );
    }

    public function test_update_rejects_unviewable_connection_before_any_write(){
        $this->assertFalse( DT_Posts::can_view( 'groups', $this->hidden_group_id ) );

        $result = DT_Posts::update_post( 'contacts', $this->contact_id, [
            'title' => 'Renamed contact',
            'contact_phone' => [ 'values' => [ [ 'value' => '798456780' ] ] ],
            'groups' => [ 'values' => [ [ 'value' => $this->shared_group_id ], [ 'value' => $this->hidden_group_id ] ] ],
        ], true );
        $this->assertWPError( $result );
        $this->assertSame( 403, $result->get_error_data()['status'] );

        $contact = DT_Posts::get_post( 'contacts', $this->contact_id, false, false );
        $this->assertSame( 'Connection contact', $contact['title'] );
        $this->assertEmpty( $contact['contact_phone'] ?? [] );
        $this->assertSame( [], $this->get_group_ids( $this->contact_id ) );
    }

    public function test_create_rejects_unviewable_connection_without_creating_the_record(){
        $result = DT_Posts::create_post( 'contacts', [
            'title' => 'Orphan check contact',
            'groups' => [ 'values' => [ [ 'value' => $this->hidden_group_id ] ] ],
        ], true );
        $this->assertWPError( $result );
        $this->assertSame( 403, $result->get_error_data()['status'] );

        $created = get_posts( [
            'post_type' => 'contacts',
            'title' => 'Orphan check contact',
            'post_status' => 'any',
            'fields' => 'ids',
        ] );
        $this->assertEmpty( $created );
    }

    public function test_connection_by_title_is_checked(){
        $result = DT_Posts::update_post( 'contacts', $this->contact_id, [
            'groups' => [ 'values' => [ [ 'value' => 'Hidden group' ] ] ],
        ], true );
        $this->assertWPError( $result );
        $this->assertSame( [], $this->get_group_ids( $this->contact_id ) );
    }

    public function test_viewable_connection_is_added(){
        $result = DT_Posts::update_post( 'contacts', $this->contact_id, [
            'groups' => [ 'values' => [ [ 'value' => $this->shared_group_id ] ] ],
        ], true );
        $this->assertNotWPError( $result );
        $this->assertSame( [ (int) $this->shared_group_id ], $this->get_group_ids( $this->contact_id ) );
    }

    public function test_list_all_allows_connection(){
        $user = wp_get_current_user();
        $user->add_cap( 'list_all_groups' );
        $result = DT_Posts::update_post( 'contacts', $this->contact_id, [
            'groups' => [ 'values' => [ [ 'value' => $this->hidden_group_id ] ] ],
        ], true );
        $user->remove_cap( 'list_all_groups' );
        $this->assertNotWPError( $result );
        $this->assertSame( [ (int) $this->hidden_group_id ], $this->get_group_ids( $this->contact_id ) );
    }

    public function test_existing_unviewable_connection_can_be_kept_and_removed(){
        $result = DT_Posts::update_post( 'contacts', $this->contact_id, [
            'groups' => [ 'values' => [ [ 'value' => $this->hidden_group_id ] ] ],
        ], true, false );
        $this->assertNotWPError( $result );

        $result = DT_Posts::update_post( 'contacts', $this->contact_id, [
            'groups' => [ 'values' => [ [ 'value' => $this->hidden_group_id ], [ 'value' => $this->shared_group_id ] ] ],
        ], true );
        $this->assertNotWPError( $result );

        $result = DT_Posts::update_post( 'contacts', $this->contact_id, [
            'groups' => [ 'values' => [ [ 'value' => $this->hidden_group_id, 'delete' => true ] ] ],
        ], true );
        $this->assertNotWPError( $result );
        $this->assertSame( [ (int) $this->shared_group_id ], $this->get_group_ids( $this->contact_id ) );
    }

    public function test_check_permissions_false_skips_the_check(){
        $result = DT_Posts::update_post( 'contacts', $this->contact_id, [
            'groups' => [ 'values' => [ [ 'value' => $this->hidden_group_id ] ] ],
        ], true, false );
        $this->assertNotWPError( $result );
        $this->assertSame( [ (int) $this->hidden_group_id ], $this->get_group_ids( $this->contact_id ) );
    }
}
