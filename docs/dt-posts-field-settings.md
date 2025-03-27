# DT_Posts Field Settings Guide for AI

This guide provides detailed information about the field settings returned by `get_post_field_settings()` in the DT_Posts API. These field settings define the structure, behavior, and presentation of fields in the Disciple.Tools interface.

## Overview

The `DT_Posts::get_post_field_settings()` method returns a comprehensive array of field definitions for a specific post type in Disciple.Tools. Each field has settings that control how it appears, behaves, and interacts with other parts of the system.

## Method Signature

```php
DT_Posts::get_post_field_settings(
    string $post_type,                // The post type to get field settings for (e.g. 'contacts', 'groups')
    bool $load_from_cache = true,     // Whether to load from cache if available
    bool $with_deleted_options = false, // Whether to include deleted field options
    bool $load_tags = false           // Whether to load tag data
)
```

## Return Value Structure

The method returns an associative array where each key is a field ID and each value is another array containing the field's configuration:

```php
[
    'field_key_1' => [
        'name' => 'Display Name',
        'type' => 'text',
        // Additional field settings...
    ],
    'field_key_2' => [
        'name' => 'Status',
        'type' => 'key_select',
        // Additional field settings...
    ],
    // Other fields...
]
```

## Field Types Reference

The Disciple.Tools system supports the following field types, each with its own unique structure and properties:

### 1. Text (`text`)

A simple text field for single-line input.

```php
'name' => [
    'name' => 'Name',
    'type' => 'text',
    'tile' => 'details',
    'icon' => 'http://example.com/wp-content/themes/disciple-tools-theme/dt-assets/images/name.svg',
    'show_in_table' => 5,
    'required' => true
]
```

### 2. Text Area (`textarea`)

A multi-line text area for longer content.

```php
'notes' => [
    'name' => 'Notes',
    'type' => 'textarea',
    'tile' => 'details'
]
```

### 3. Key Select (`key_select`)

A dropdown select field with predefined options.

```php
'overall_status' => [
    'name' => 'Status',
    'type' => 'key_select',
    'default' => [
        'active' => [
            'label' => 'Active',
            'color' => '#4CAF50'
        ],
        'paused' => [
            'label' => 'Paused',
            'color' => '#FF9800'
        ]
    ],
    'tile' => 'status',
    'select_cannot_be_empty' => true
]
```

### 4. Multi-Select (`multi_select`)

A field allowing selection of multiple options from predefined choices.

```php
'milestones' => [
    'name' => 'Faith Milestones',
    'type' => 'multi_select',
    'default' => [
        'milestone_has_bible' => [
            'label' => 'Has Bible',
            'color' => '#4CAF50'
        ],
        'milestone_reading_bible' => [
            'label' => 'Reading Bible',
            'color' => '#2196F3'
        ]
    ],
    'tile' => 'faith'
]
```

### 5. Tags (`tags`)

Similar to multi-select, but allows users to create new options dynamically.

```php
'tags' => [
    'name' => 'Tags',
    'type' => 'tags',
    'default' => [],
    'tile' => 'other'
]
```

### 6. Communication Channel (`communication_channel`)

Fields for storing contact methods like phone, email, etc.

```php
'contact_phone' => [
    'name' => 'Phone',
    'type' => 'communication_channel',
    'tile' => 'contact_details',
    'icon' => 'http://example.com/wp-content/themes/disciple-tools-theme/dt-assets/images/phone.svg'
]
```

### 7. Connection (`connection`)

Fields for relationships between different post types.

```php
'groups' => [
    'name' => 'Groups',
    'type' => 'connection',
    'post_type' => 'groups',
    'p2p_key' => 'contacts_to_groups',
    'p2p_direction' => 'from',
    'tile' => 'connections'
]
```

### 8. User Select (`user_select`)

Fields for associating users with posts.

```php
'assigned_to' => [
    'name' => 'Assigned To',
    'type' => 'user_select',
    'tile' => 'status',
    'icon' => 'http://example.com/wp-content/themes/disciple-tools-theme/dt-assets/images/assigned-to.svg'
]
```

### 9. Date (`date`)

Fields for storing date values.

```php
'baptism_date' => [
    'name' => 'Baptism Date',
    'type' => 'date',
    'tile' => 'faith'
]
```

### 10. Number (`number`)

Fields for numeric values.

```php
'member_count' => [
    'name' => 'Member Count',
    'type' => 'number',
    'tile' => 'details'
]
```

### 11. Boolean (`boolean`)

Fields for true/false values.

```php
'requires_update' => [
    'name' => 'Requires Update',
    'type' => 'boolean',
    'tile' => 'status'
]
```

### 12. Location (`location`)

Fields for selecting predefined locations from the location grid.

```php
'location_grid' => [
    'name' => 'Location',
    'type' => 'location',
    'tile' => 'details',
    'icon' => 'http://example.com/wp-content/themes/disciple-tools-theme/dt-assets/images/location.svg'
]
```

### 13. Location Meta (`location_meta`)

Fields for geocoded location data.

```php
'location_grid_meta' => [
    'name' => 'Location Details',
    'type' => 'location_meta',
    'tile' => 'details',
    'hidden' => true
]
```

### 14. Link (`link`)

Fields for storing link data with categories.

```php
'social_links' => [
    'name' => 'Social Media',
    'type' => 'link',
    'default' => [
        'facebook' => 'Facebook',
        'twitter' => 'Twitter'
    ],
    'tile' => 'details'
]
```

### 15. Array (`array`)

Fields for storing array data (typically used internally).

```php
'metrics' => [
    'name' => 'Metrics',
    'type' => 'array',
    'hidden' => true
]
```

### 16. Tasks (`tasks`)

Fields for task-related data.

```php
'tasks' => [
    'name' => 'Tasks',
    'type' => 'tasks',
    'tile' => 'tasks'
]
```

## Common Field Properties

Most field types support these common properties:

| Property | Type | Description |
|----------|------|-------------|
| `name` | string | Display name for the field |
| `type` | string | Field type identifier |
| `description` | string | Explanatory text for the field |
| `tile` | string | Which tile to display the field in |
| `icon` | string | URL to the field icon |
| `font-icon` | string | Material Design icon reference (e.g., 'mdi mdi-robot-outline') |
| `show_in_table` | int | Priority for showing in list tables (lower = higher priority) |
| `customizable` | bool/string | Whether users can customize this field ('add_only' or boolean) |
| `hidden` | bool | Whether to hide field from UI |
| `in_create_form` | bool/array | Whether to show in creation form (true or array of types) |
| `custom_display` | bool | Whether field has custom display logic |
| `private` | bool | Whether field contains private data |
| `required` | bool | Whether field is required when creating/updating |
| `only_for_types` | array | Record types where field should be visible |

## Type-Specific Properties

Different field types have additional properties specific to their functionality:

### Key Select and Multi Select Fields

| Property | Type | Description |
|----------|------|-------------|
| `default` | array | Associative array of options with their configurations |
| `default_color` | string | Default color for options (enables color mode) |
| `select_cannot_be_empty` | bool | Whether field must always have a value |

### Connection Fields

| Property | Type | Description |
|----------|------|-------------|
| `post_type` | string | Connected post type |
| `p2p_key` | string | Post-to-post connection key |
| `p2p_direction` | string | Connection direction (from, to) |
| `create-icon` | string | URL for icon used in typeahead create button |

### Communication Channel Fields

| Property | Type | Description |
|----------|------|-------------|
| `hide_domain` | bool | Whether to hide URL domains in UI |

## Usage Examples

### Getting Field Settings

```php
// Get field settings for contacts
$contact_fields = DT_Posts::get_post_field_settings('contacts');

// Check field type
$status_field_type = $contact_fields['overall_status']['type'];  // 'key_select'

// Get available options for a dropdown field
$status_options = $contact_fields['overall_status']['default'];

// Check if a field is required
$is_name_required = $contact_fields['title']['required'] ?? false;
```

### Finding Fields of a Specific Type

```php
// Get all multi-select fields for contacts
$multi_select_fields = DT_Posts::get_field_settings_by_type('contacts', 'multi_select');
```

### Getting Default Field Values

```php
// Get default options for a field
$milestone_options = $contact_fields['milestones']['default'];

// Get a specific option's label
$has_bible_label = $contact_fields['milestones']['default']['milestone_has_bible']['label'];
```

### Field Customization Context

The field settings can be customized by both plugin developers and administrators:

1. **Theme/Plugin Developers**: Add or modify fields using the `dt_custom_fields_settings` filter
2. **Admin UI**: Customizations made through the admin interface are stored in the `dt_field_customizations` option

```php
// Example: Adding a custom field through code
add_filter('dt_custom_fields_settings', function($fields, $post_type) {
    if ($post_type === 'contacts') {
        $fields['language'] = [
            'name' => 'Spoken Language',
            'type' => 'key_select',
            'default' => [
                'english' => ['label' => 'English'],
                'french' => ['label' => 'French']
            ],
            'tile' => 'details'
        ];
    }
    return $fields;
}, 10, 2);
```

## Internal Operation

When `get_post_field_settings()` is called, it performs these operations:

1. Checks if field settings are cached and returns the cache if available
2. Loads base post type fields
3. Applies the `dt_custom_fields_settings` filter to allow plugins to add fields
4. Normalizes option values for key_select and multi_select fields
5. Merges in any admin customizations from the `dt_field_customizations` option
6. Caches the results for future use

This process ensures that all field customizations from both code and admin UI are properly applied. 