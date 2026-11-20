<?php

/**
 * @testdox DT_Posts::create_post duplicate merge permissions
 */
class DT_Posts_DT_Posts_Duplicate_Merge_Permissions extends WP_UnitTestCase {

    private $multiplier_id;
    private $existing_id;

    public function set_up(): void {
        parent::set_up();
        $owner_id = wp_create_user( 'merge_dispatcher', 'test', 'merge_dispatcher@example.com' );
        get_user_by( 'id', $owner_id )->set_role( 'dispatcher' );
        wp_set_current_user( $owner_id );
        $existing = DT_Posts::create_post( 'contacts', [
            'title' => 'Existing duplicate',
            'contact_phone' => [ 'values' => [ [ 'value' => '5559990001' ] ] ],
        ], true, false );
        $this->existing_id = $existing['ID'];

        $this->multiplier_id = wp_create_user( 'merge_multiplier', 'test', 'merge_multiplier@example.com' );
        get_user_by( 'id', $this->multiplier_id )->set_role( 'multiplier' );
        wp_set_current_user( $this->multiplier_id );
    }

    private function create_with_duplicate_check( $title, $phone ){
        return DT_Posts::create_post( 'contacts', [
            'title' => $title,
            'contact_phone' => [ 'values' => [ [ 'value' => $phone ] ] ],
        ], true, true, [ 'check_for_duplicates' => [ 'contact_phone' ] ] );
    }

    public function test_viewable_but_not_updatable_duplicate_creates_a_new_record(){
        $user = wp_get_current_user();
        $user->add_cap( 'view_any_contacts' );
        $this->assertTrue( DT_Posts::can_view( 'contacts', $this->existing_id ) );
        $this->assertFalse( DT_Posts::can_update( 'contacts', $this->existing_id ) );

        $result = $this->create_with_duplicate_check( 'New contact', '5559990001' );
        $user->remove_cap( 'view_any_contacts' );

        $this->assertNotWPError( $result );
        $this->assertNotEquals( $this->existing_id, $result['ID'] );
    }

    public function test_unviewable_duplicate_is_not_merged(){
        $this->assertFalse( DT_Posts::can_view( 'contacts', $this->existing_id ) );

        $result = $this->create_with_duplicate_check( 'New contact', '5559990001' );

        $this->assertNotWPError( $result );
        $this->assertNotEquals( $this->existing_id, $result['ID'] );
    }

    public function test_updatable_duplicate_is_merged(){
        $own = DT_Posts::create_post( 'contacts', [
            'title' => 'Own contact',
            'contact_phone' => [ 'values' => [ [ 'value' => '5559990002' ] ] ],
        ], true );
        $this->assertNotWPError( $own );

        $result = $this->create_with_duplicate_check( 'Own contact again', '5559990002' );

        $this->assertNotWPError( $result );
        $this->assertEquals( $own['ID'], $result['ID'] );
    }
}
