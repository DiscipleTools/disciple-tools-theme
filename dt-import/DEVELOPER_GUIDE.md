# DT Import CSV - Developer Guide

## Overview

The DT Import system provides comprehensive CSV import functionality for Disciple.Tools post types. This guide covers the technical implementation details for developers working on or integrating with the import system.

## Core Architecture

### File Structure
```
wp-content/themes/disciple-tools-theme/dt-import/
├── dt-import.php                    # Main plugin file
├── admin/
│   ├── dt-import-admin-tab.php     # Admin tab integration
│   ├── dt-import-mapping.php       # Field mapping logic
│   ├── dt-import-processor.php     # Import processing
│   ├── rest-endpoints.php          # REST API endpoints
│   └── documentation-modal.php     # Documentation modal template
├── includes/
│   ├── dt-import-field-handlers.php # Field-specific processors
│   ├── dt-import-utilities.php     # Utility functions
│   ├── dt-import-validators.php    # Data validation
│   └── dt-import-geocoding.php     # Geocoding services
└── assets/
    ├── js/
    │   ├── dt-import.js            # Main frontend JavaScript
    │   └── dt-import-modals.js     # Modal handling
    ├── css/dt-import.css           # Styling
    └── example CSV files           # Example templates
```

### Main Classes
- `DT_Theme_CSV_Import`: Core plugin initialization
- `DT_CSV_Import_Admin_Tab`: WordPress admin integration
- `DT_CSV_Import_Mapping`: Field detection and mapping
- `DT_CSV_Import_Processor`: Data processing and import execution
- `DT_CSV_Import_Field_Handlers`: Field-specific processing logic

## Field Type Processing

### Field Handler Methods

Each field type has specific processing logic:

```php
// Text fields - direct assignment
handle_text_field($value, $field_key, $post_type)

// Date fields - format conversion to Y-m-d
handle_date_field($value, $field_key, $post_type)

// Boolean fields - convert various formats to boolean
handle_boolean_field($value, $field_key, $post_type)

// Key select - map CSV values to field options
handle_key_select_field($value, $field_key, $post_type, $value_mapping)

// Multi select - split semicolon-separated values
handle_multi_select_field($value, $field_key, $post_type, $value_mapping)

// Communication channels - validate and format
handle_communication_channel_field($value, $field_key, $post_type)

// Connections - lookup by ID or name, create if needed
handle_connection_field($value, $field_key, $post_type, $create_missing)

// User select - lookup by ID, username, or display name
handle_user_select_field($value, $field_key, $post_type)

// Location fields - geocoding and grid assignment
handle_location_field($value, $field_key, $post_type, $geocoding_service)
handle_location_grid_field($value, $field_key, $post_type)
handle_location_grid_meta_field($value, $field_key, $post_type, $geocoding_service)
```

### Value Mapping Structure

For key_select and multi_select fields:

```php
$field_mappings[column_index] = [
    'field_key' => 'field_name',
    'column_index' => 0,
    'value_mapping' => [
        'csv_value_1' => 'dt_option_key_1',
        'csv_value_2' => 'dt_option_key_2',
        // ... more mappings
    ]
];
```

## Automatic Field Detection

### Detection Logic Priority

1. **Predefined Field Headings** (100% confidence)
2. **Direct Field Matching** (100% confidence)
3. **Communication Channel Detection** (100% confidence)
4. **Partial Matching** (75% confidence)
5. **Extended Field Aliases** (≤75% confidence)

### Predefined Headings

```php
$predefined_headings = [
    'contact_phone' => ['phone', 'mobile', 'telephone', 'cell', 'phone_number'],
    'contact_email' => ['email', 'e-mail', 'email_address', 'mail'],
    'contact_address' => ['address', 'street_address', 'home_address'],
    'name' => ['title', 'name', 'contact_name', 'full_name', 'display_name'],
    // ... more mappings
];
```

### Auto-Mapping Threshold

- **≥75% confidence**: Automatically mapped
- **<75% confidence**: Shows "No match found", requires manual selection

## API Endpoints

### Get Field Options
```
GET /wp-json/dt-csv-import/v2/{post_type}/field-options?field_key={field_key}
```

Returns available options for key_select and multi_select fields.

### Get Column Data
```
GET /wp-json/dt-csv-import/v2/{session_id}/column-data?column_index={index}
```

Returns unique values and sample data from CSV column.

### Import Processing
```
POST /wp-json/dt-csv-import/v2/import
```

Executes the import with field mappings and configuration.

## Data Processing Flow

### Import Workflow

1. **File Upload**: CSV uploaded and parsed
2. **Field Detection**: Headers analyzed for field suggestions
3. **Field Mapping**: User maps columns to DT fields
4. **Value Mapping**: For key_select/multi_select, map CSV values to options
5. **Validation**: Data validated against field requirements
6. **Processing**: Records created via DT_Posts API
7. **Reporting**: Results and errors reported

### Multi-Value Field Processing

Fields supporting multiple values use semicolon separation:

```php
// Split semicolon-separated values
$values = array_map('trim', explode(';', $csv_value));

// Process each value
foreach ($values as $value) {
    // Handle individual value based on field type
}
```

## Location Field Handling

### Supported Location Formats

#### location_grid
- **Input**: Numeric grid ID only
- **Processing**: Direct validation and assignment
- **Example**: `12345`

#### location_grid_meta
- **Numeric grid ID**: `12345`
- **Decimal coordinates**: `40.7128, -74.0060`
- **DMS coordinates**: `35°50′40.9″N, 103°27′7.5″E`
- **Address strings**: `123 Main St, New York, NY`
- **Multiple locations**: `Paris; Berlin` (semicolon-separated)

#### Coordinate Format Support

**Decimal Degrees**:
```php
// Format: latitude,longitude
// Range: -90 to 90 (lat), -180 to 180 (lng)
preg_match('/^(-?\d+\.?\d*),\s*(-?\d+\.?\d*)$/', $value, $matches)
```

**DMS (Degrees, Minutes, Seconds)**:
```php
// Format: DD°MM′SS.S″N/S, DDD°MM′SS.S″E/W
// Supports various symbols: °′″ or d m s or regular quotes
$dms_pattern = '/(\d+)[°d]\s*(\d+)[\'′m]\s*([\d.]+)["″s]?\s*([NSEW])/i';
```

### Geocoding Integration

```php
// Geocoding availability check
$is_geocoding_available = DT_CSV_Import_Geocoding::is_geocoding_available();

// The system uses DT's built-in geocoding services:
// - Google Maps (if API key configured)
// - Mapbox (if API token configured)

// Geocoding during import is handled by DT core
// Import system just sets the geolocate flag
$location_data = [
    'value' => $address,
    'geolocate' => $is_geocoding_available
];
```

## Value Mapping for Select Fields

### Modal System

The value mapping modal provides:

- Real CSV data fetching
- Unique value detection
- Auto-mapping with fuzzy matching
- Batch operations (clear all, auto-map)
- Live mapping count updates

### JavaScript Integration

```javascript
// Key methods in DT Import JavaScript
// Main script: dt-import.js (2151 lines)
// Modal handling: dt-import-modals.js (511 lines)

// Core functionality:
getColumnCSVData(columnIndex)      // Fetch CSV column data
getFieldOptions(postType, fieldKey) // Fetch field options
autoMapValues()                    // Intelligent auto-mapping
clearAllMappings()                 // Clear all mappings
updateMappingCount()               // Update mapping statistics
```

### Auto-Mapping Algorithm

```php
// Fuzzy matching for auto-mapping
function auto_map_values($csv_values, $field_options) {
    foreach ($csv_values as $csv_value) {
        $best_match = find_best_match($csv_value, $field_options);
        if ($best_match['confidence'] >= 0.8) {
            $mappings[$csv_value] = $best_match['option_key'];
        }
    }
    return $mappings;
}
```

## Security Implementation

### Access Control
```php
// Capability check for all operations
if (!current_user_can('manage_dt')) {
    wp_die('Insufficient permissions');
}
```

### File Upload Security
```php
// File type validation
$allowed_types = ['text/csv', 'application/csv'];
if (!in_array($file['type'], $allowed_types)) {
    throw new Exception('Invalid file type');
}

// File size validation
if ($file['size'] > 10 * 1024 * 1024) { // 10MB
    throw new Exception('File too large');
}
```

### Data Sanitization
```php
// Sanitize all inputs
$sanitized_value = sanitize_text_field($raw_value);

// Use WordPress nonces
wp_verify_nonce($_POST['_wpnonce'], 'dt_import_action');
```

## Error Handling

### Validation Errors
```php
// Row-level error collection
$errors = [
    'row_2' => ['Invalid email format in column 3'],
    'row_5' => ['Required field "name" is empty'],
    'row_8' => ['Invalid option "maybe" for field "status"']
];
```

### Field-Specific Validation
```php
// Email validation
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    throw new DT_Import_Field_Exception('Invalid email format');
}

// Date validation
$date = DateTime::createFromFormat('Y-m-d', $value);
if (!$date || $date->format('Y-m-d') !== $value) {
    throw new DT_Import_Field_Exception('Invalid date format');
}
```

## Performance Considerations

### Memory Management
```php
// Process large files in chunks
$chunk_size = 1000;
$offset = 0;

while ($records = get_csv_chunk($file, $offset, $chunk_size)) {
    process_chunk($records);
    $offset += $chunk_size;
    
    // Clear memory
    unset($records);
    if (function_exists('gc_collect_cycles')) {
        gc_collect_cycles();
    }
}
```

### Database Optimization
```php
// Batch database operations
$batch_size = 100;
$batch_data = [];

foreach ($records as $record) {
    $batch_data[] = prepare_record_data($record);
    
    if (count($batch_data) >= $batch_size) {
        process_batch($batch_data);
        $batch_data = [];
    }
}
```

## Extending the System

### Current Limitations

The current CSV import system has limited extensibility options. To modify behavior, you would need to:

1. **Modify Core Files**: Direct edits to the import classes (not recommended)
2. **Custom Post Types**: Add new post types through DT's existing systems
3. **Field Types**: Use DT's field type system rather than import-specific handlers

### Extending Field Detection

Field detection can be customized by modifying the `$field_headings` array in `DT_CSV_Import_Mapping`:

```php
// In dt-import-mapping.php
private static $field_headings = [
    'your_custom_field' => [
        'custom_header',
        'alternative_name',
        'legacy_field'
    ],
    // ... existing mappings
];
```

### Custom Field Handlers

Field processing is handled in `DT_CSV_Import_Field_Handlers`. New field type support would require:

1. Adding handler method in the class
2. Updating the field type mapping logic
3. Ensuring DT core supports the field type

### Integration Points

- **DT Posts API**: All imports go through `DT_Posts::create_post()`
- **DT Field Settings**: Field definitions come from `DT_Posts::get_post_field_settings()`
- **DT Geocoding**: Location processing uses DT's geocoding system

## Testing

### Testing

The import system can be tested through the WordPress admin interface:

1. **Manual Testing**: Use the CSV Import admin page under Utilities
2. **Field Detection**: Test with various CSV column headers
3. **Value Mapping**: Test dropdown field value mapping with sample data
4. **Error Handling**: Test with invalid data to verify error reporting

### Test CSV Files

The system includes example CSV files for testing:
- `assets/example_contacts.csv` - Basic contact import
- `assets/example_contacts_comprehensive.csv` - All contact fields
- `assets/example_groups.csv` - Basic group import
- `assets/example_groups_comprehensive.csv` - All group fields

## Configuration Hooks

### Current Implementation

The current CSV import system does not expose custom hooks or filters for extensibility. Configuration is handled through:

1. **File Size Limits**: Controlled by WordPress `wp_max_upload_size()`
2. **Field Detection**: Built into `DT_CSV_Import_Mapping` class methods
3. **Geocoding**: Uses DT core geocoding services automatically
4. **Validation**: Integrated with DT Posts API validation

### Potential Extension Points

If hooks were added in future versions, they might include:

```php
// Example hooks that could be implemented:
// apply_filters('dt_csv_import_field_mappings', $mappings, $post_type)
// apply_filters('dt_csv_import_supported_field_types', $field_types)
// do_action('dt_csv_import_before_process', $import_data)
// do_action('dt_csv_import_after_process', $results)
```

## Common Integration Patterns

### Custom Post Type Support

The import system automatically supports any post type available through `DT_Posts::get_post_types()`. To add import support for a custom post type:

1. **Register with DT**: Ensure your post type is registered with DT's post type system
2. **Field Settings**: Provide field settings via `DT_Posts::get_post_field_settings()`
3. **Automatic Detection**: The import system will automatically include it

### Working with Import Data

```php
// Get available post types for import
$post_types = DT_Posts::get_post_types();

// Get field settings for mapping
$field_settings = DT_Posts::get_post_field_settings($post_type);

// Access import session data
$session_data = DT_CSV_Import_Utilities::get_session_data($session_id);
```

This developer guide provides comprehensive technical reference for working with the DT Import CSV system, covering all major components, APIs, and extension points. 