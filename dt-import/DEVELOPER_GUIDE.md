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

### Supported Field Types

Each field type has specific processing logic in `DT_CSV_Import_Field_Handlers`:

- **Text fields**: Direct assignment with sanitization
- **Date fields**: Format conversion and validation
- **Boolean fields**: Multiple format recognition (true/false, yes/no, 1/0, etc.)
- **Key select**: CSV value mapping to field options
- **Multi select**: Semicolon-separated value processing
- **Communication channels**: Email/phone validation and formatting
- **Connections**: ID/name lookup with duplicate handling
- **User select**: User lookup by ID, username, or display name
- **Location fields**: Address geocoding and coordinate processing

### Connection Field Processing

Connection fields have special handling for duplicate names:

1. **Numeric ID**: Direct lookup by post ID
2. **Text name**: Search by title/name with duplicate detection
3. **Single match**: Connect to existing record
4. **No match**: Create new record (non-preview mode)
5. **Multiple matches**: Skip that connection (continue processing other connections)

### Value Mapping Structure

For dropdown fields, mappings are stored as:
- `field_key`: Target DT field
- `column_index`: CSV column number
- `value_mapping`: CSV value → DT option key mappings

## Automatic Field Detection

### Detection Priority

1. **Predefined Field Headings** (100% confidence)
2. **Direct Field Matching** (100% confidence) 
3. **Communication Channel Detection** (100% confidence)
4. **Partial Matching** (75% confidence)
5. **Extended Field Aliases** (≤75% confidence)

### Auto-Mapping Threshold

- **≥75% confidence**: Automatically mapped
- **<75% confidence**: Manual selection required

## API Endpoints

### REST API Structure

Base URL: `/wp-json/dt-csv-import/v2/`

- **Field Options**: `/{post_type}/field-options?field_key={field_key}`
- **Column Data**: `/{session_id}/column-data?column_index={index}`
- **Import Processing**: `/import` (POST)

## Data Processing Flow

### Import Workflow

1. **File Upload**: CSV parsing and validation
2. **Field Detection**: Header analysis for field suggestions
3. **Field Mapping**: User column-to-field mapping
4. **Value Mapping**: Dropdown value mapping for select fields
5. **Validation**: Data validation against field requirements
6. **Processing**: Record creation via DT_Posts API
7. **Reporting**: Results and error reporting

### Multi-Value Field Processing

Fields supporting multiple values use semicolon (`;`) separation for processing multiple entries in a single CSV cell.

## Location Field Handling

### Supported Formats

- **location_grid**: Numeric grid ID only
- **location_grid_meta**: Multiple formats supported
  - Numeric grid IDs
  - Decimal coordinates (lat,lng)
  - DMS coordinates with direction indicators
  - Address strings for geocoding
  - Semicolon-separated multiple locations

### Coordinate Format Support

- **Decimal Degrees**: Standard lat,lng format with range validation
- **DMS**: Degrees/minutes/seconds with required direction indicators (N/S/E/W)

### Geocoding Integration

Uses DT's built-in geocoding services (Google Maps/Mapbox) when configured. The import system sets the geolocate flag for address processing.

## Value Mapping System

### Modal Features

- Real-time CSV data fetching
- Unique value detection from actual CSV data
- Auto-mapping with fuzzy string matching
- Batch operations (clear all, auto-map)
- Live mapping count updates

### Auto-Mapping Algorithm

Fuzzy matching compares CSV values to field options with confidence scoring. Mappings above 80% confidence are auto-applied.

## Security Implementation

### Access Control
- Requires `manage_dt` capability for all operations
- WordPress nonce verification for all actions

### File Upload Security
- File type validation (CSV only)
- Size limits (10MB maximum)
- Server-side content validation

### Data Sanitization
- All inputs sanitized before processing
- Field-specific validation rules applied

## Error Handling

### Validation Levels
- **Row-level**: Collect all errors per row for batch reporting
- **Field-specific**: Type-appropriate validation (email format, date parsing, etc.)
- **Connection lookup**: Graceful handling of missing/duplicate records

### Performance Considerations

- **Memory Management**: Large file processing in chunks
- **Database Optimization**: Batch operations where possible
- **Progress Tracking**: Session-based progress for long imports

## Current Limitations

### Extensibility
The system has limited hook/filter support. Customization requires:
- Direct file modification (not recommended)
- Custom post type registration through DT's existing systems
- Field type extension through DT's field system

### Extension Points
- Field detection can be modified via `$field_headings` array
- Field handlers can be added to `DT_CSV_Import_Field_Handlers`
- All imports integrate through `DT_Posts::create_post()`

## Testing

### Available Methods
1. **Manual Testing**: Admin interface under Utilities
2. **Field Detection**: Test various CSV column headers
3. **Value Mapping**: Dropdown field mapping with sample data
4. **Error Handling**: Invalid data testing

### Test Resources
- Example CSV files in assets directory
- Basic and comprehensive templates for contacts/groups
- Various format examples for testing edge cases

## Integration Patterns

### Custom Post Type Support
Automatic support for any post type registered with DT's system via:
- `DT_Posts::get_post_types()` for available types
- `DT_Posts::get_post_field_settings()` for field definitions

### Data Access Patterns
- Session data: `DT_CSV_Import_Utilities::get_session_data()`
- Field settings: `DT_Posts::get_post_field_settings($post_type)`
- Import processing: All through DT_Posts API

This developer guide provides the essential technical reference for understanding and working with the DT Import CSV system architecture and implementation patterns. 