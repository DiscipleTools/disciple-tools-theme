<?php
/**
 * Test file to demonstrate enhanced automatic field detection
 * This file can be accessed at: /wp-content/themes/disciple-tools-theme/dt-import/test-field-detection.php
 *
 * To run this test, visit: yoursite.com/wp-content/themes/disciple-tools-theme/dt-import/test-field-detection.php
 */

// Load WordPress
if ( !defined( 'ABSPATH' ) ) {
    $wp_root = dirname( dirname( dirname( dirname( __FILE__ ) ) ) );
    require_once( $wp_root . '/wp-config.php' );
}

// Load DT Import classes
require_once( __DIR__ . '/includes/dt-import-utilities.php' );
require_once( __DIR__ . '/admin/dt-import-mapping.php' );

// Test CSV headers with various formats
$test_headers = [
    // Standard headers that should be detected
    'Name',
    'Full Name',
    'Phone',
    'Mobile',
    'Telephone',
    'Email',
    'E-mail',
    'Address',
    'Gender',
    'Sex',
    'Notes',
    'Comment',

    // Headers with underscores and variations
    'contact_name',
    'phone_number',
    'email_address',
    'home_phone',
    'work_email',
    'street_address',

    // Headers with different cases
    'MOBILE PHONE',
    'Primary Email',
    'HOME ADDRESS',

    // Headers that might not be detected
    'Custom Field',
    'XYZ Data',
    'Random Column'
];

echo '<h1>DT Import - Enhanced Automatic Field Detection Test</h1>';
echo '<p>This test demonstrates the enhanced automatic field detection capabilities.</p>';

echo "<table border='1' cellpadding='5' cellspacing='0' style='border-collapse: collapse; width: 100%;'>";
echo '<thead>';
echo '<tr>';
echo '<th>CSV Header</th>';
echo '<th>Detected Field</th>';
echo '<th>Auto-Mapped</th>';
echo '<th>Match Type</th>';
echo '</tr>';
echo '</thead>';
echo '<tbody>';

foreach ( $test_headers as $index => $header ) {
    // Create fake CSV data for testing
    $fake_csv_data = [
        array_fill( 0, count( $test_headers ), 'Sample Data' )
    ];

    // Test the field detection
    $field_settings = [
        'name' => [ 'name' => 'Name', 'type' => 'text' ],
        'contact_phone' => [ 'name' => 'Phone', 'type' => 'communication_channel' ],
        'contact_email' => [ 'name' => 'Email', 'type' => 'communication_channel' ],
        'contact_address' => [ 'name' => 'Address', 'type' => 'communication_channel' ],
        'gender' => [ 'name' => 'Gender', 'type' => 'key_select' ],
        'notes' => [ 'name' => 'Notes', 'type' => 'textarea' ]
    ];

    $post_settings = [
        'channels' => [
            'phone' => [ 'label' => 'Phone' ],
            'email' => [ 'label' => 'Email' ],
            'address' => [ 'label' => 'Address' ]
        ]
    ];

    // Use reflection to access private method for testing
    $reflection = new ReflectionClass( 'DT_CSV_Import_Mapping' );
    $method = $reflection->getMethod( 'suggest_field_mapping' );
    $method->setAccessible( true );

    $suggested_field = $method->invokeArgs( null, [ $header, $field_settings, $post_settings, 'contacts' ] );

    // Determine match type and auto-mapping status
    $match_type = 'No Match';
    $auto_mapped = 'No';

    if ( $suggested_field ) {
        $auto_mapped = 'Yes';
        $normalized_header = strtolower( trim( preg_replace( '/[^a-zA-Z0-9]/', '', $header ) ) );

        // Check field headings
        $field_headings = [
            'contact_phone' => [ 'phone', 'mobile', 'telephone', 'cell' ],
            'contact_email' => [ 'email', 'mail' ],
            'contact_address' => [ 'address' ],
            'name' => [ 'name', 'title', 'fullname', 'contactname' ],
            'gender' => [ 'gender', 'sex' ],
            'notes' => [ 'notes', 'comment' ]
        ];

        foreach ( $field_headings as $field => $headings ) {
            if ( $suggested_field === $field || strpos( $suggested_field, str_replace( 'contact_', '', $field ) ) !== false ) {
                foreach ( $headings as $heading ) {
                    if ( $normalized_header === strtolower( preg_replace( '/[^a-zA-Z0-9]/', '', $heading ) ) ||
                         strpos( $normalized_header, strtolower( preg_replace( '/[^a-zA-Z0-9]/', '', $heading ) ) ) !== false ) {
                        $match_type = 'Predefined Heading';
                        break 2;
                    }
                }
            }
        }

        if ( $match_type === 'No Match' ) {
            if ( isset( $field_settings[$suggested_field] ) ) {
                $match_type = 'Direct Field Match';
            } else {
                $match_type = 'Partial/Alias Match';
            }
        }
    }

    echo '<tr>';
    echo '<td><strong>' . esc_html( $header ) . '</strong></td>';
    echo '<td>' . ( $suggested_field ? esc_html( $suggested_field ) : '<em style="color: #999;">No match found</em>' ) . '</td>';
    echo "<td style='color: " . ( $auto_mapped === 'Yes' ? '#46b450' : '#999' ) . "; font-weight: bold;'>" . $auto_mapped . '</td>';
    echo '<td>' . esc_html( $match_type ) . '</td>';
    echo '</tr>';
}

echo '</tbody>';
echo '</table>';

echo '<h2>Summary</h2>';
echo '<p>The enhanced automatic field detection uses the following strategies:</p>';
echo '<ul>';
echo '<li><strong>Predefined Field Headings:</strong> Comprehensive lists of common header variations for each field type</li>';
echo '<li><strong>Direct Field Matching:</strong> Exact matches with field keys and names</li>';
echo '<li><strong>Channel Field Detection:</strong> Automatic prefix handling for communication channels</li>';
echo '<li><strong>Partial Matching:</strong> Substring matching for partial header matches</li>';
echo '<li><strong>Extended Aliases:</strong> Large library of field aliases and synonyms</li>';
echo '</ul>';

echo '<h2>Auto-Mapping Behavior</h2>';
echo '<ul>';
echo "<li><span style='color: #46b450; font-weight: bold;'>Auto-Mapped:</span> Field was automatically detected and will be pre-selected in the mapping interface</li>";
echo "<li><span style='color: #999; font-weight: bold;'>No Match:</span> Field was not detected and will show 'No match found' in the interface, requiring manual selection</li>";
echo '</ul>';

echo '<p><em>This is a test file. In the actual import interface, users can review and modify these automatic suggestions.</em></p>';
?> 