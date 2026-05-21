<?php
/**
 * Class DuplicateDetectionTest
 *
 * Tests for configurable duplicate detection fields (PR #2882).
 *
 * @package Disciple.Tools
 */

class DuplicateDetectionTest extends WP_UnitTestCase {

    private static $user_id;

    public static function setupBeforeClass(): void {
        $user_id = wp_create_user( 'dup_test_user', 'test', 'dup_test_user@example.com' );
        $user = get_user_by( 'id', $user_id );
        $user->set_role( 'dispatcher' );
        self::$user_id = $user_id;
        update_option( 'dt_base_user', $user_id );
    }

    public function setUp(): void {
        parent::setUp();
        wp_set_current_user( self::$user_id );
    }

    /**
     * @testdox dt_get_duplicate_fields_defaults returns name for any post type
     */
    public function test_defaults_include_name() {
        $defaults = dt_get_duplicate_fields_defaults( 'contacts' );
        $this->assertContains( 'name', $defaults );

        $defaults_groups = dt_get_duplicate_fields_defaults( 'groups' );
        $this->assertContains( 'name', $defaults_groups );
    }

    /**
     * @testdox dt_get_duplicate_fields_defaults includes communication channels for contacts
     */
    public function test_defaults_include_communication_channels_for_contacts() {
        $defaults = dt_get_duplicate_fields_defaults( 'contacts' );
        $this->assertContains( 'contact_phone', $defaults );
        $this->assertContains( 'contact_email', $defaults );
    }

    /**
     * @testdox dt_get_duplicate_fields_defaults does not include communication channels for non-contact post types
     */
    public function test_defaults_no_communication_channels_for_groups() {
        $defaults = dt_get_duplicate_fields_defaults( 'groups' );
        $this->assertNotContains( 'contact_phone', $defaults );
        $this->assertNotContains( 'contact_email', $defaults );
    }

    /**
     * @testdox Duplicate detection finds contacts with matching phone numbers using default config
     */
    public function test_duplicate_detection_by_phone_default_config() {
        $contact1 = DT_Posts::create_post( 'contacts', [
            'title' => 'Alice Phone',
            'overall_status' => 'active',
            'contact_phone' => [ 'values' => [ [ 'value' => '5551234567' ] ] ],
        ], true, false );
        $this->assertNotWPError( $contact1 );

        $contact2 = DT_Posts::create_post( 'contacts', [
            'title' => 'Alice Phone Copy',
            'overall_status' => 'active',
            'contact_phone' => [ 'values' => [ [ 'value' => '5551234567' ] ] ],
        ], true, false );
        $this->assertNotWPError( $contact2 );

        $duplicates = DT_Duplicate_Checker_And_Merging::ids_of_non_dismissed_duplicates( 'contacts', $contact1['ID'] );
        $this->assertNotWPError( $duplicates );
        $this->assertContains( (string) $contact2['ID'], $duplicates['ids'] );
    }

    /**
     * @testdox Duplicate detection finds contacts with matching names using default config
     */
    public function test_duplicate_detection_by_name_default_config() {
        $contact1 = DT_Posts::create_post( 'contacts', [
            'title' => 'UniqueNameForDupTest123',
            'overall_status' => 'active',
        ], true, false );
        $this->assertNotWPError( $contact1 );

        $contact2 = DT_Posts::create_post( 'contacts', [
            'title' => 'UniqueNameForDupTest123',
            'overall_status' => 'active',
        ], true, false );
        $this->assertNotWPError( $contact2 );

        $duplicates = DT_Duplicate_Checker_And_Merging::ids_of_non_dismissed_duplicates( 'contacts', $contact1['ID'] );
        $this->assertNotWPError( $duplicates );
        $this->assertContains( (string) $contact2['ID'], $duplicates['ids'] );
    }

    /**
     * @testdox Custom duplicate config restricts which fields are checked
     */
    public function test_custom_config_restricts_fields() {
        // Create two contacts that share a phone but have different names
        $contact1 = DT_Posts::create_post( 'contacts', [
            'title' => 'DupConfigTest Alpha',
            'overall_status' => 'active',
            'contact_phone' => [ 'values' => [ [ 'value' => '5559876543' ] ] ],
        ], true, false );
        $this->assertNotWPError( $contact1 );

        $contact2 = DT_Posts::create_post( 'contacts', [
            'title' => 'DupConfigTest Beta',
            'overall_status' => 'active',
            'contact_phone' => [ 'values' => [ [ 'value' => '5559876543' ] ] ],
        ], true, false );
        $this->assertNotWPError( $contact2 );

        // Configure duplicate detection to only check 'name' (not phone)
        $site_options = dt_get_option( 'dt_site_options' );
        $site_options['duplicates'] = [ 'contacts' => [ 'name' ] ];
        update_option( 'dt_site_options', $site_options );

        $duplicates = DT_Duplicate_Checker_And_Merging::ids_of_non_dismissed_duplicates( 'contacts', $contact1['ID'] );
        $this->assertNotWPError( $duplicates );
        // Names are different, so should NOT be detected as duplicates when only checking name
        $this->assertNotContains( (string) $contact2['ID'], $duplicates['ids'] );

        // Now configure to check phone
        $site_options['duplicates'] = [ 'contacts' => [ 'contact_phone' ] ];
        update_option( 'dt_site_options', $site_options );

        $duplicates = DT_Duplicate_Checker_And_Merging::ids_of_non_dismissed_duplicates( 'contacts', $contact1['ID'] );
        $this->assertNotWPError( $duplicates );
        // Phone matches, so SHOULD be detected
        $this->assertContains( (string) $contact2['ID'], $duplicates['ids'] );

        // Clean up
        $site_options['duplicates'] = [];
        update_option( 'dt_site_options', $site_options );
    }

    /**
     * @testdox Custom duplicate config with email field works
     */
    public function test_custom_config_with_email() {
        $contact1 = DT_Posts::create_post( 'contacts', [
            'title' => 'Email Dup Test One',
            'overall_status' => 'active',
            'contact_email' => [ 'values' => [ [ 'value' => 'duptest@example.com' ] ] ],
        ], true, false );
        $this->assertNotWPError( $contact1 );

        $contact2 = DT_Posts::create_post( 'contacts', [
            'title' => 'Email Dup Test Two',
            'overall_status' => 'active',
            'contact_email' => [ 'values' => [ [ 'value' => 'duptest@example.com' ] ] ],
        ], true, false );
        $this->assertNotWPError( $contact2 );

        // Configure to only check email
        $site_options = dt_get_option( 'dt_site_options' );
        $site_options['duplicates'] = [ 'contacts' => [ 'contact_email' ] ];
        update_option( 'dt_site_options', $site_options );

        $duplicates = DT_Duplicate_Checker_And_Merging::ids_of_non_dismissed_duplicates( 'contacts', $contact1['ID'] );
        $this->assertNotWPError( $duplicates );
        $this->assertContains( (string) $contact2['ID'], $duplicates['ids'] );

        // Clean up
        $site_options['duplicates'] = [];
        update_option( 'dt_site_options', $site_options );
    }

    /**
     * @testdox Empty custom config falls back to defaults
     */
    public function test_empty_config_falls_back_to_defaults() {
        $site_options = dt_get_option( 'dt_site_options' );
        $site_options['duplicates'] = [];
        update_option( 'dt_site_options', $site_options );

        $contact1 = DT_Posts::create_post( 'contacts', [
            'title' => 'FallbackDupTest',
            'overall_status' => 'active',
            'contact_phone' => [ 'values' => [ [ 'value' => '5550001111' ] ] ],
        ], true, false );
        $this->assertNotWPError( $contact1 );

        $contact2 = DT_Posts::create_post( 'contacts', [
            'title' => 'FallbackDupTest',
            'overall_status' => 'active',
            'contact_phone' => [ 'values' => [ [ 'value' => '5550001111' ] ] ],
        ], true, false );
        $this->assertNotWPError( $contact2 );

        // With empty config, defaults should apply (name + communication channels)
        $duplicates = DT_Duplicate_Checker_And_Merging::ids_of_non_dismissed_duplicates( 'contacts', $contact1['ID'] );
        $this->assertNotWPError( $duplicates );
        $this->assertContains( (string) $contact2['ID'], $duplicates['ids'] );
    }

    /**
     * @testdox dt_get_duplicate_fields_defaults is filterable
     */
    public function test_defaults_are_filterable() {
        $filter = function ( $defaults, $post_type ) {
            if ( $post_type === 'contacts' ) {
                $defaults[] = 'nickname';
            }
            return $defaults;
        };
        add_filter( 'dt_duplicate_fields_defaults', $filter, 10, 2 );

        $defaults = dt_get_duplicate_fields_defaults( 'contacts' );
        $this->assertContains( 'nickname', $defaults );

        remove_filter( 'dt_duplicate_fields_defaults', $filter, 10 );
    }

    /**
     * @testdox Site options include duplicates key in defaults
     */
    public function test_site_options_defaults_include_duplicates_key() {
        $defaults = dt_get_site_options_defaults();
        $this->assertArrayHasKey( 'duplicates', $defaults );
        $this->assertIsArray( $defaults['duplicates'] );
        $this->assertEmpty( $defaults['duplicates'] );
    }

    /**
     * @testdox Duplicate detection works with tags field type
     */
    public function test_duplicate_detection_by_tags() {
        $contact1 = DT_Posts::create_post( 'contacts', [
            'title' => 'Tags Dup Alpha',
            'overall_status' => 'active',
            'tags' => [ 'values' => [ [ 'value' => 'unique-dup-tag-xyz' ] ] ],
        ], true, false );
        $this->assertNotWPError( $contact1 );

        $contact2 = DT_Posts::create_post( 'contacts', [
            'title' => 'Tags Dup Beta',
            'overall_status' => 'active',
            'tags' => [ 'values' => [ [ 'value' => 'unique-dup-tag-xyz' ] ] ],
        ], true, false );
        $this->assertNotWPError( $contact2 );

        // Configure to check tags
        $site_options = dt_get_option( 'dt_site_options' );
        $site_options['duplicates'] = [ 'contacts' => [ 'tags' ] ];
        update_option( 'dt_site_options', $site_options );

        $duplicates = DT_Duplicate_Checker_And_Merging::ids_of_non_dismissed_duplicates( 'contacts', $contact1['ID'] );
        $this->assertNotWPError( $duplicates );
        $this->assertContains( (string) $contact2['ID'], $duplicates['ids'] );

        // Clean up
        $site_options['duplicates'] = [];
        update_option( 'dt_site_options', $site_options );
    }

    /**
     * @testdox Duplicate detection does not match contacts with different tags
     */
    public function test_no_duplicate_detection_with_different_tags() {
        $contact1 = DT_Posts::create_post( 'contacts', [
            'title' => 'Tags NoDup Alpha',
            'overall_status' => 'active',
            'tags' => [ 'values' => [ [ 'value' => 'tag-aaa-111' ] ] ],
        ], true, false );
        $this->assertNotWPError( $contact1 );

        $contact2 = DT_Posts::create_post( 'contacts', [
            'title' => 'Tags NoDup Beta',
            'overall_status' => 'active',
            'tags' => [ 'values' => [ [ 'value' => 'tag-bbb-222' ] ] ],
        ], true, false );
        $this->assertNotWPError( $contact2 );

        $site_options = dt_get_option( 'dt_site_options' );
        $site_options['duplicates'] = [ 'contacts' => [ 'tags' ] ];
        update_option( 'dt_site_options', $site_options );

        $duplicates = DT_Duplicate_Checker_And_Merging::ids_of_non_dismissed_duplicates( 'contacts', $contact1['ID'] );
        $this->assertNotWPError( $duplicates );
        $this->assertNotContains( (string) $contact2['ID'], $duplicates['ids'] );

        $site_options['duplicates'] = [];
        update_option( 'dt_site_options', $site_options );
    }

    /**
     * @testdox Duplicate detection works with multi_select field type
     */
    public function test_duplicate_detection_by_multi_select() {
        $contact1 = DT_Posts::create_post( 'contacts', [
            'title' => 'MultiSel Dup Alpha',
            'overall_status' => 'active',
            'milestones' => [ 'values' => [ [ 'value' => 'milestone_has_bible' ] ] ],
        ], true, false );
        $this->assertNotWPError( $contact1 );

        $contact2 = DT_Posts::create_post( 'contacts', [
            'title' => 'MultiSel Dup Beta',
            'overall_status' => 'active',
            'milestones' => [ 'values' => [ [ 'value' => 'milestone_has_bible' ] ] ],
        ], true, false );
        $this->assertNotWPError( $contact2 );

        // Configure to check milestones (a multi_select field on contacts)
        $site_options = dt_get_option( 'dt_site_options' );
        $site_options['duplicates'] = [ 'contacts' => [ 'milestones' ] ];
        update_option( 'dt_site_options', $site_options );

        $duplicates = DT_Duplicate_Checker_And_Merging::ids_of_non_dismissed_duplicates( 'contacts', $contact1['ID'] );
        $this->assertNotWPError( $duplicates );
        $this->assertContains( (string) $contact2['ID'], $duplicates['ids'] );

        $site_options['duplicates'] = [];
        update_option( 'dt_site_options', $site_options );
    }

    /**
     * @testdox Duplicate detection does not match contacts with different multi_select values
     */
    public function test_no_duplicate_detection_with_different_multi_select() {
        $contact1 = DT_Posts::create_post( 'contacts', [
            'title' => 'MultiSel NoDup Alpha',
            'overall_status' => 'active',
            'milestones' => [ 'values' => [ [ 'value' => 'milestone_has_bible' ] ] ],
        ], true, false );
        $this->assertNotWPError( $contact1 );

        $contact2 = DT_Posts::create_post( 'contacts', [
            'title' => 'MultiSel NoDup Beta',
            'overall_status' => 'active',
            'milestones' => [ 'values' => [ [ 'value' => 'milestone_baptizing' ] ] ],
        ], true, false );
        $this->assertNotWPError( $contact2 );

        $site_options = dt_get_option( 'dt_site_options' );
        $site_options['duplicates'] = [ 'contacts' => [ 'milestones' ] ];
        update_option( 'dt_site_options', $site_options );

        $duplicates = DT_Duplicate_Checker_And_Merging::ids_of_non_dismissed_duplicates( 'contacts', $contact1['ID'] );
        $this->assertNotWPError( $duplicates );
        $this->assertNotContains( (string) $contact2['ID'], $duplicates['ids'] );

        $site_options['duplicates'] = [];
        update_option( 'dt_site_options', $site_options );
    }

    /**
     * @testdox Duplicate detection works with text field type (nickname)
     */
    public function test_duplicate_detection_by_text_field() {
        $contact1 = DT_Posts::create_post( 'contacts', [
            'title' => 'TextDup Alpha',
            'overall_status' => 'active',
            'nickname' => 'the-same-unique-nickname-789',
        ], true, false );
        $this->assertNotWPError( $contact1 );

        $contact2 = DT_Posts::create_post( 'contacts', [
            'title' => 'TextDup Beta',
            'overall_status' => 'active',
            'nickname' => 'the-same-unique-nickname-789',
        ], true, false );
        $this->assertNotWPError( $contact2 );

        // Configure to check nickname (a text field on contacts)
        $site_options = dt_get_option( 'dt_site_options' );
        $site_options['duplicates'] = [ 'contacts' => [ 'nickname' ] ];
        update_option( 'dt_site_options', $site_options );

        $duplicates = DT_Duplicate_Checker_And_Merging::ids_of_non_dismissed_duplicates( 'contacts', $contact1['ID'] );
        $this->assertNotWPError( $duplicates );
        $this->assertContains( (string) $contact2['ID'], $duplicates['ids'] );

        $site_options['duplicates'] = [];
        update_option( 'dt_site_options', $site_options );
    }

    /**
     * @testdox Duplicate detection does not match contacts with different text field values
     */
    public function test_no_duplicate_detection_with_different_text() {
        $contact1 = DT_Posts::create_post( 'contacts', [
            'title' => 'TextNoDup Alpha',
            'overall_status' => 'active',
            'nickname' => 'nickname-aaa-unique',
        ], true, false );
        $this->assertNotWPError( $contact1 );

        $contact2 = DT_Posts::create_post( 'contacts', [
            'title' => 'TextNoDup Beta',
            'overall_status' => 'active',
            'nickname' => 'nickname-bbb-unique',
        ], true, false );
        $this->assertNotWPError( $contact2 );

        $site_options = dt_get_option( 'dt_site_options' );
        $site_options['duplicates'] = [ 'contacts' => [ 'nickname' ] ];
        update_option( 'dt_site_options', $site_options );

        $duplicates = DT_Duplicate_Checker_And_Merging::ids_of_non_dismissed_duplicates( 'contacts', $contact1['ID'] );
        $this->assertNotWPError( $duplicates );
        $this->assertNotContains( (string) $contact2['ID'], $duplicates['ids'] );

        $site_options['duplicates'] = [];
        update_option( 'dt_site_options', $site_options );
    }

    /**
     * @testdox Duplicate detection with multiple configured field types finds match on any field
     */
    public function test_duplicate_detection_with_mixed_field_types() {
        $contact1 = DT_Posts::create_post( 'contacts', [
            'title' => 'MixedDup Alpha',
            'overall_status' => 'active',
            'nickname' => 'mixed-nick-unique-456',
            'tags' => [ 'values' => [ [ 'value' => 'mixed-tag-unique-456' ] ] ],
            'contact_phone' => [ 'values' => [ [ 'value' => '5550009999' ] ] ],
        ], true, false );
        $this->assertNotWPError( $contact1 );

        // Contact2 only shares tags, not nickname or phone
        $contact2 = DT_Posts::create_post( 'contacts', [
            'title' => 'MixedDup Beta',
            'overall_status' => 'active',
            'nickname' => 'different-nick',
            'tags' => [ 'values' => [ [ 'value' => 'mixed-tag-unique-456' ] ] ],
            'contact_phone' => [ 'values' => [ [ 'value' => '5550008888' ] ] ],
        ], true, false );
        $this->assertNotWPError( $contact2 );

        // Configure to check nickname, tags, and phone
        $site_options = dt_get_option( 'dt_site_options' );
        $site_options['duplicates'] = [ 'contacts' => [ 'nickname', 'tags', 'contact_phone' ] ];
        update_option( 'dt_site_options', $site_options );

        // Should match because tags overlap
        $duplicates = DT_Duplicate_Checker_And_Merging::ids_of_non_dismissed_duplicates( 'contacts', $contact1['ID'] );
        $this->assertNotWPError( $duplicates );
        $this->assertContains( (string) $contact2['ID'], $duplicates['ids'] );

        // Now configure to only check nickname — should NOT match
        $site_options['duplicates'] = [ 'contacts' => [ 'nickname' ] ];
        update_option( 'dt_site_options', $site_options );

        $duplicates = DT_Duplicate_Checker_And_Merging::ids_of_non_dismissed_duplicates( 'contacts', $contact1['ID'] );
        $this->assertNotWPError( $duplicates );
        $this->assertNotContains( (string) $contact2['ID'], $duplicates['ids'] );

        $site_options['duplicates'] = [];
        update_option( 'dt_site_options', $site_options );
    }

    // =========================================================================
    // get_all_duplicates_on_post: exact + fuzzy matching with scoring
    // =========================================================================

    /**
     * Helper to find a specific duplicate ID in get_all_duplicates_on_post results.
     */
    private function find_dup_in_results( array $results, int $target_id ) {
        foreach ( $results as $dup ) {
            if ( (int) $dup['ID'] === $target_id ) {
                return $dup;
            }
        }
        return null;
    }

    /**
     * @testdox get_all_duplicates_on_post finds exact name match with high points
     */
    public function test_all_duplicates_exact_name_match() {
        $contact1 = DT_Posts::create_post( 'contacts', [
            'title' => 'ExactNameAllDups777',
            'overall_status' => 'active',
        ], true, false );
        $this->assertNotWPError( $contact1 );

        $contact2 = DT_Posts::create_post( 'contacts', [
            'title' => 'ExactNameAllDups777',
            'overall_status' => 'active',
        ], true, false );
        $this->assertNotWPError( $contact2 );

        $site_options = dt_get_option( 'dt_site_options' );
        $site_options['duplicates'] = [ 'contacts' => [ 'name' ] ];
        update_option( 'dt_site_options', $site_options );

        $results = DT_Duplicate_Checker_And_Merging::get_all_duplicates_on_post( 'contacts', $contact1['ID'] );
        $this->assertNotWPError( $results );
        $this->assertIsArray( $results );

        $match = $this->find_dup_in_results( $results, $contact2['ID'] );
        $this->assertNotNull( $match, 'Exact name match should be found' );
        // Exact text match = 4 points
        $this->assertGreaterThanOrEqual( 4, $match['points'] );

        // Verify match_on fields contain the name field
        $matched_fields = array_column( $match['fields'], 'field' );
        $this->assertContains( 'name', $matched_fields );

        $site_options['duplicates'] = [];
        update_option( 'dt_site_options', $site_options );
    }

    /**
     * @testdox get_all_duplicates_on_post finds fuzzy name match (substring) with lower points
     */
    public function test_all_duplicates_fuzzy_name_match() {
        // Contact with the longer name
        $contact_long = DT_Posts::create_post( 'contacts', [
            'title' => 'FuzzyTestJohnathan',
            'overall_status' => 'active',
        ], true, false );
        $this->assertNotWPError( $contact_long );

        // Contact with the shorter name (substring of the longer)
        $contact_short = DT_Posts::create_post( 'contacts', [
            'title' => 'FuzzyTestJohn',
            'overall_status' => 'active',
        ], true, false );
        $this->assertNotWPError( $contact_short );

        $site_options = dt_get_option( 'dt_site_options' );
        $site_options['duplicates'] = [ 'contacts' => [ 'name' ] ];
        update_option( 'dt_site_options', $site_options );

        // Search from the shorter-named contact: the DB LIKE '%FuzzyTestJohn%'
        // will match "FuzzyTestJohnathan", so it gets returned as a candidate.
        // Then the scoring logic detects the substring relationship.
        $results = DT_Duplicate_Checker_And_Merging::get_all_duplicates_on_post( 'contacts', $contact_short['ID'] );
        $this->assertNotWPError( $results );

        $match = $this->find_dup_in_results( $results, $contact_long['ID'] );
        $this->assertNotNull( $match, 'Fuzzy name match (substring) should be found' );
        // Fuzzy match = 1 point (not 4 for exact)
        $this->assertGreaterThanOrEqual( 1, $match['points'] );

        $site_options['duplicates'] = [];
        update_option( 'dt_site_options', $site_options );
    }

    /**
     * @testdox get_all_duplicates_on_post scores exact phone match with 4 points
     */
    public function test_all_duplicates_exact_phone_match_scoring() {
        $contact1 = DT_Posts::create_post( 'contacts', [
            'title' => 'PhoneScore Alpha',
            'overall_status' => 'active',
            'contact_phone' => [ 'values' => [ [ 'value' => '5557770001' ] ] ],
        ], true, false );
        $this->assertNotWPError( $contact1 );

        $contact2 = DT_Posts::create_post( 'contacts', [
            'title' => 'PhoneScore Beta',
            'overall_status' => 'active',
            'contact_phone' => [ 'values' => [ [ 'value' => '5557770001' ] ] ],
        ], true, false );
        $this->assertNotWPError( $contact2 );

        $site_options = dt_get_option( 'dt_site_options' );
        $site_options['duplicates'] = [ 'contacts' => [ 'contact_phone' ] ];
        update_option( 'dt_site_options', $site_options );

        $results = DT_Duplicate_Checker_And_Merging::get_all_duplicates_on_post( 'contacts', $contact1['ID'] );
        $this->assertNotWPError( $results );

        $match = $this->find_dup_in_results( $results, $contact2['ID'] );
        $this->assertNotNull( $match, 'Exact phone match should be found' );
        $this->assertGreaterThanOrEqual( 4, $match['points'] );

        $matched_fields = array_column( $match['fields'], 'field' );
        $this->assertContains( 'contact_phone', $matched_fields );

        $site_options['duplicates'] = [];
        update_option( 'dt_site_options', $site_options );
    }

    /**
     * @testdox get_all_duplicates_on_post finds exact tags match with scoring
     */
    public function test_all_duplicates_exact_tags_match() {
        $contact1 = DT_Posts::create_post( 'contacts', [
            'title' => 'TagsAllDup Alpha',
            'overall_status' => 'active',
            'tags' => [ 'values' => [ [ 'value' => 'all-dup-tag-exact-999' ] ] ],
        ], true, false );
        $this->assertNotWPError( $contact1 );

        $contact2 = DT_Posts::create_post( 'contacts', [
            'title' => 'TagsAllDup Beta',
            'overall_status' => 'active',
            'tags' => [ 'values' => [ [ 'value' => 'all-dup-tag-exact-999' ] ] ],
        ], true, false );
        $this->assertNotWPError( $contact2 );

        $site_options = dt_get_option( 'dt_site_options' );
        $site_options['duplicates'] = [ 'contacts' => [ 'tags' ] ];
        update_option( 'dt_site_options', $site_options );

        $results = DT_Duplicate_Checker_And_Merging::get_all_duplicates_on_post( 'contacts', $contact1['ID'] );
        $this->assertNotWPError( $results );

        $match = $this->find_dup_in_results( $results, $contact2['ID'] );
        $this->assertNotNull( $match, 'Exact tags match should be found' );
        $this->assertGreaterThanOrEqual( 4, $match['points'] );

        $matched_fields = array_column( $match['fields'], 'field' );
        $this->assertContains( 'tags', $matched_fields );

        $site_options['duplicates'] = [];
        update_option( 'dt_site_options', $site_options );
    }

    /**
     * @testdox get_all_duplicates_on_post finds exact multi_select match with scoring
     */
    public function test_all_duplicates_exact_multi_select_match() {
        $contact1 = DT_Posts::create_post( 'contacts', [
            'title' => 'MSAllDup Alpha',
            'overall_status' => 'active',
            'milestones' => [ 'values' => [ [ 'value' => 'milestone_has_bible' ] ] ],
        ], true, false );
        $this->assertNotWPError( $contact1 );

        $contact2 = DT_Posts::create_post( 'contacts', [
            'title' => 'MSAllDup Beta',
            'overall_status' => 'active',
            'milestones' => [ 'values' => [ [ 'value' => 'milestone_has_bible' ] ] ],
        ], true, false );
        $this->assertNotWPError( $contact2 );

        $site_options = dt_get_option( 'dt_site_options' );
        $site_options['duplicates'] = [ 'contacts' => [ 'milestones' ] ];
        update_option( 'dt_site_options', $site_options );

        $results = DT_Duplicate_Checker_And_Merging::get_all_duplicates_on_post( 'contacts', $contact1['ID'] );
        $this->assertNotWPError( $results );

        $match = $this->find_dup_in_results( $results, $contact2['ID'] );
        $this->assertNotNull( $match, 'Exact multi_select match should be found' );
        $this->assertGreaterThanOrEqual( 4, $match['points'] );

        $matched_fields = array_column( $match['fields'], 'field' );
        $this->assertContains( 'milestones', $matched_fields );

        $site_options['duplicates'] = [];
        update_option( 'dt_site_options', $site_options );
    }

    /**
     * @testdox get_all_duplicates_on_post exact match scores higher than fuzzy
     */
    public function test_all_duplicates_exact_scores_higher_than_fuzzy() {
        // The "source" contact uses the shorter nickname so the fuzzy DB search
        // (LIKE '%ScoreCompare%') can find the longer "ScoreCompareNickLonger" too.
        $contact1 = DT_Posts::create_post( 'contacts', [
            'title' => 'ScoreCompare Alpha',
            'overall_status' => 'active',
            'nickname' => 'ScoreCompare',
        ], true, false );
        $this->assertNotWPError( $contact1 );

        // Exact nickname match
        $contact_exact = DT_Posts::create_post( 'contacts', [
            'title' => 'ScoreCompare Exact',
            'overall_status' => 'active',
            'nickname' => 'ScoreCompare',
        ], true, false );
        $this->assertNotWPError( $contact_exact );

        // Fuzzy nickname match (contact1's nickname is a substring of this one)
        $contact_fuzzy = DT_Posts::create_post( 'contacts', [
            'title' => 'ScoreCompare Fuzzy',
            'overall_status' => 'active',
            'nickname' => 'ScoreCompareNickLonger',
        ], true, false );
        $this->assertNotWPError( $contact_fuzzy );

        $site_options = dt_get_option( 'dt_site_options' );
        $site_options['duplicates'] = [ 'contacts' => [ 'nickname' ] ];
        update_option( 'dt_site_options', $site_options );

        $results = DT_Duplicate_Checker_And_Merging::get_all_duplicates_on_post( 'contacts', $contact1['ID'] );
        $this->assertNotWPError( $results );

        $exact_match = $this->find_dup_in_results( $results, $contact_exact['ID'] );
        $fuzzy_match = $this->find_dup_in_results( $results, $contact_fuzzy['ID'] );

        $this->assertNotNull( $exact_match, 'Exact match should be found' );
        $this->assertNotNull( $fuzzy_match, 'Fuzzy match should be found' );
        $this->assertGreaterThan( $fuzzy_match['points'], $exact_match['points'], 'Exact match should score higher than fuzzy' );

        $site_options['duplicates'] = [];
        update_option( 'dt_site_options', $site_options );
    }

    // =========================================================================
    // Create-time duplicate check (check_for_duplicates arg) — unchanged path
    // =========================================================================

    /**
     * @testdox Create-time check_for_duplicates on phone still works and updates existing record
     */
    public function test_create_time_duplicate_check_by_phone() {
        $contact1 = DT_Posts::create_post( 'contacts', [
            'title' => 'CreateDup Phone Original',
            'overall_status' => 'active',
            'contact_phone' => [ 'values' => [ [ 'value' => '5551112222' ] ] ],
        ], true, false );
        $this->assertNotWPError( $contact1 );

        // Create with duplicate check — should update existing instead of creating new
        $contact2 = DT_Posts::create_post( 'contacts', [
            'title' => 'CreateDup Phone Duplicate',
            'contact_phone' => [ 'values' => [ [ 'value' => '5551112222' ] ] ],
            ], true, false, [
            'check_for_duplicates' => [ 'contact_phone' ],
        ] );
        $this->assertNotWPError( $contact2 );

        // Should return the original contact's ID (updated, not new)
        $this->assertSame( $contact1['ID'], $contact2['ID'] );
    }

    /**
     * @testdox Create-time check_for_duplicates on email still works and updates existing record
     */
    public function test_create_time_duplicate_check_by_email() {
        $contact1 = DT_Posts::create_post( 'contacts', [
            'title' => 'CreateDup Email Original',
            'overall_status' => 'active',
            'contact_email' => [ 'values' => [ [ 'value' => 'create-dup-test@example.com' ] ] ],
        ], true, false );
        $this->assertNotWPError( $contact1 );

        // Create with duplicate check on email — should update existing
        $contact2 = DT_Posts::create_post( 'contacts', [
            'title' => 'CreateDup Email Duplicate',
            'contact_email' => [ 'values' => [ [ 'value' => 'create-dup-test@example.com' ] ] ],
            'contact_phone' => [ 'values' => [ [ 'value' => '5553334444' ] ] ],
            ], true, false, [
            'check_for_duplicates' => [ 'contact_email' ],
        ] );
        $this->assertNotWPError( $contact2 );

        // Should return the original contact's ID (updated, not new)
        $this->assertSame( $contact1['ID'], $contact2['ID'] );

        // Verify the phone was added to the existing contact
        $updated = DT_Posts::get_post( 'contacts', $contact1['ID'] );
        $phones = array_column( $updated['contact_phone'], 'value' );
        $this->assertContains( '5553334444', $phones );
    }

    /**
     * @testdox Create-time check_for_duplicates creates new record when no match
     */
    public function test_create_time_duplicate_check_no_match_creates_new() {
        $contact1 = DT_Posts::create_post( 'contacts', [
            'title' => 'CreateNoDup Original',
            'overall_status' => 'active',
            'contact_phone' => [ 'values' => [ [ 'value' => '5556667777' ] ] ],
        ], true, false );
        $this->assertNotWPError( $contact1 );

        // Create with different phone — should create new record
        $contact2 = DT_Posts::create_post( 'contacts', [
            'title' => 'CreateNoDup Different',
            'contact_phone' => [ 'values' => [ [ 'value' => '5558889999' ] ] ],
            ], true, false, [
            'check_for_duplicates' => [ 'contact_phone' ],
        ] );
        $this->assertNotWPError( $contact2 );

        $this->assertNotSame( $contact1['ID'], $contact2['ID'] );
    }

    // =========================================================================
    // Groups post type — duplicate detection works across post types
    // =========================================================================

    /**
     * @testdox Duplicate detection finds groups with matching names using default config
     */
    public function test_groups_duplicate_detection_by_name_default_config() {
        $group1 = DT_Posts::create_post( 'groups', [
            'title' => 'UniqueGroupDupTest789',
        ], true, false );
        $this->assertNotWPError( $group1 );

        $group2 = DT_Posts::create_post( 'groups', [
            'title' => 'UniqueGroupDupTest789',
        ], true, false );
        $this->assertNotWPError( $group2 );

        // Default config for groups is just ['name']
        $site_options = dt_get_option( 'dt_site_options' );
        $site_options['duplicates'] = [];
        update_option( 'dt_site_options', $site_options );

        $duplicates = DT_Duplicate_Checker_And_Merging::ids_of_non_dismissed_duplicates( 'groups', $group1['ID'] );
        $this->assertNotWPError( $duplicates );
        $this->assertContains( (string) $group2['ID'], $duplicates['ids'] );
    }

    /**
     * @testdox Duplicate detection on groups does not match different names
     */
    public function test_groups_no_duplicate_with_different_names() {
        $group1 = DT_Posts::create_post( 'groups', [
            'title' => 'GroupNoDup Alpha',
        ], true, false );
        $this->assertNotWPError( $group1 );

        $group2 = DT_Posts::create_post( 'groups', [
            'title' => 'GroupNoDup Beta',
        ], true, false );
        $this->assertNotWPError( $group2 );

        $site_options = dt_get_option( 'dt_site_options' );
        $site_options['duplicates'] = [];
        update_option( 'dt_site_options', $site_options );

        $duplicates = DT_Duplicate_Checker_And_Merging::ids_of_non_dismissed_duplicates( 'groups', $group1['ID'] );
        $this->assertNotWPError( $duplicates );
        $this->assertNotContains( (string) $group2['ID'], $duplicates['ids'] );
    }

    /**
     * @testdox Custom duplicate config works for groups post type
     */
    public function test_groups_custom_config_with_tags() {
        $group1 = DT_Posts::create_post( 'groups', [
            'title' => 'GroupTagDup Alpha',
            'tags' => [ 'values' => [ [ 'value' => 'group-dup-tag-unique-321' ] ] ],
        ], true, false );
        $this->assertNotWPError( $group1 );

        $group2 = DT_Posts::create_post( 'groups', [
            'title' => 'GroupTagDup Beta',
            'tags' => [ 'values' => [ [ 'value' => 'group-dup-tag-unique-321' ] ] ],
        ], true, false );
        $this->assertNotWPError( $group2 );

        // Configure groups to check tags
        $site_options = dt_get_option( 'dt_site_options' );
        $site_options['duplicates'] = [ 'groups' => [ 'tags' ] ];
        update_option( 'dt_site_options', $site_options );

        $duplicates = DT_Duplicate_Checker_And_Merging::ids_of_non_dismissed_duplicates( 'groups', $group1['ID'] );
        $this->assertNotWPError( $duplicates );
        $this->assertContains( (string) $group2['ID'], $duplicates['ids'] );

        $site_options['duplicates'] = [];
        update_option( 'dt_site_options', $site_options );
    }

    /**
     * @testdox get_all_duplicates_on_post works for groups with exact and fuzzy name matching
     */
    public function test_groups_all_duplicates_exact_and_fuzzy_name() {
        $group1 = DT_Posts::create_post( 'groups', [
            'title' => 'FuzzyGroup',
        ], true, false );
        $this->assertNotWPError( $group1 );

        // Exact match
        $group_exact = DT_Posts::create_post( 'groups', [
            'title' => 'FuzzyGroup',
        ], true, false );
        $this->assertNotWPError( $group_exact );

        // Fuzzy match (group1's name is a substring)
        $group_fuzzy = DT_Posts::create_post( 'groups', [
            'title' => 'FuzzyGroupExtended',
        ], true, false );
        $this->assertNotWPError( $group_fuzzy );

        $site_options = dt_get_option( 'dt_site_options' );
        $site_options['duplicates'] = [ 'groups' => [ 'name' ] ];
        update_option( 'dt_site_options', $site_options );

        $results = DT_Duplicate_Checker_And_Merging::get_all_duplicates_on_post( 'groups', $group1['ID'] );
        $this->assertNotWPError( $results );

        $exact_match = $this->find_dup_in_results( $results, $group_exact['ID'] );
        $fuzzy_match = $this->find_dup_in_results( $results, $group_fuzzy['ID'] );

        $this->assertNotNull( $exact_match, 'Exact group name match should be found' );
        $this->assertNotNull( $fuzzy_match, 'Fuzzy group name match should be found' );
        $this->assertGreaterThan( $fuzzy_match['points'], $exact_match['points'] );

        $site_options['duplicates'] = [];
        update_option( 'dt_site_options', $site_options );
    }
}
