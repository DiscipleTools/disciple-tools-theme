# DT_Posts::get_post() - Comprehensive Documentation

This document provides detailed information about the `get_post()` method in the DT_Posts API, including its functionality, parameters, return structure, and examples for each field type.

## Method Signature

```php
DT_Posts::get_post(
    string $post_type,           // The post type to retrieve (e.g., 'contacts', 'groups')
    int $post_id,                // The ID of the post to retrieve
    bool $use_cache = true,      // Whether to use cached data if available
    bool $check_permissions = true, // Whether to check if the current user has permission to view the post
    bool $silent = false         // Whether to create an activity log entry for viewing the post
)
```

## Description

The `get_post()` method retrieves a single post of the specified type by its ID. It returns a comprehensive array containing all the post's fields, connections, and metadata. The method handles permission checks, caching, and activity logging.

## Return Value

The method returns an associative array containing all the post's data, or a WP_Error object if the post doesn't exist or the user doesn't have permission to view it.

### Common Fields in All Post Types

All post types include these standard fields:

```php
[
    'ID' => 123,                 // The post ID
    'post_type' => 'contacts',   // The post type
    'post_date' => [             // Creation date information
        'timestamp' => 1609459200,
        'formatted' => '2023-08-10'
    ],
    'permalink' => 'https://example.com/contacts/123/', // URL to the post
    'post_author' => 1,          // ID of the user who created the post
    'post_author_display_name' => 'Admin User', // Display name of the post author
    'name' => 'John Doe',        // The post title (decoded)
    'title' => 'John Doe'        // The post title (decoded)
]
```

## Field Type Examples

Below are examples of how different field types appear in the response from `get_post()`:

### 1. Text (`text`)

```php
'name' => 'John Doe'
```

### 2. Text Area (`textarea`)

```php
'notes' => 'This is a multi-line note about the contact.\nSecond line of notes.'
```

### 3. Key Select (`key_select`)

```php
'overall_status' => [
    'key' => 'active',
    'label' => 'Active',
]
```

### 4. Multi-Select (`multi_select`)

```php
'milestones' => [
    'milestone_has_bible',
    'milestone_reading_bible'
]
```

### 5. Tags (`tags`)

```php
'tags' => [
    'tag1',
    'tag2',
    'tag3'
]
```

### 6. Communication Channel (`communication_channel`)

```php
'contact_phone' => [
    ['value' => '123-456-7890', 'key' => 'contact_phone_123'],
    ['value' => '098-765-4321', 'key' => 'contact_phone_456']
]
```

### 7. Connection (`connection`)

```php
'groups' => [
    '456' => [
        'ID' => 456,
        'post_type' => 'groups',
        'post_date' => '2024-10-04 12:57:50'
        'post_date_gmt' => '2024-10-04 11:57:50',
        'post_title' => 'Small Group Alpha',
        'status' => [
            'key' => 'active',
            'label' => 'Active',
            'color' => '#4CAF50'
        ]
    ],
    '789' => [
        'ID' => 789,
        'post_type' => 'groups',
        'post_date' => '2024-10-05 14:20:30',
        'post_date_gmt' => '2024-10-05 13:20:30',
        'post_title' => 'Prayer Group Beta',
        'status' => [
            'key' => 'inactive',
            'label' => 'Inactive',
            'color' => '#F44336'
        ]
    ]
]
```

### 8. User Select (`user_select`)

```php
'assigned_to' => [
    'id' => 5,
    'type' => 'user',
    'display' => 'John Smith',
    'assigned-to' => 'user-5',
]
```

### 9. Date (`date`)

```php
'baptism_date' => [
    'timestamp' => 1609459200,
    'formatted' => '2025-07-12'
]
```

### 10. Number (`number`)

```php
'member_count' => 12
```

### 11. Boolean (`boolean`)

```php
'requires_update' => true
```

### 12. Location (`location`)

```php
'location_grid' => [
    '123' => [
        'id' => 100089597,
        'label' => 'New York City',
        'matched_search' => 'New York City, New York, USA',
    ]
]
```

### 13. Location Meta (`location_meta`)

```php
'location_grid_meta' => [
    [
        'grid_id' => '100089597',
        'grid_meta_id' => '1303',
        'lng' => '-74.0060',
        'lat' => '40.7128',
        'level' => 'admin3',
        'post_id' => '123',
        'post_type' => 'contacts',
        'pastmeta_id_location_grid' => '661728',
        'source' => 'user'
    ]
]
```

### 14. Link (`link`)

```php
'social_links' => [
    [
        'meta_id' => 1234,
        'value' => 'https://facebook.com/johndoe',
        'type' => 'facebook'
    ],
    [
        'meta_id' => 1235,
        'value' => 'https://twitter.com/johndoe',
        'type' => 'twitter'
    ]
]
```

### 15. Array (`array`)

```php
'metrics' => [
    'key1' => 'value1',
    'key2' => 'value2'
]
```

### 16. Tasks (`tasks`)

```php
'tasks' => [
    [
        'id' => 1,
        'value' => [
            'note' => 'Call John',
        ],
        'date' => '2021-01-01',
        'category' => 'follow_up'
    ],
    [
        'id' => 2,
        'value' => [
            'note' => 'Send follow-up email',
            'task_type' => 'follow_up',
            'subtype' => 'email',
            'status' => 'complete'
        ],
        'date' => '2021-01-02',
        'category' => 'follow_up'
    ]
]
```

## Error Handling

The method may return a WP_Error object in the following cases:

1. User doesn't have permission to view the post:
```php
new WP_Error(
    'get_post',
    "No permissions to read $post_type with ID $post_id",
    ['status' => 403]
)
```

2. Post type is invalid or not yet loaded:
```php
new WP_Error(
    'get_post',
    "$post_type in not a valid post type or hasn't been declared yet. Please use the disciple_tools_loaded hook",
    ['status' => 400]
)
```

3. Post doesn't exist:
```php
new WP_Error(
    'get_post',
    'post does not exist',
    ['status' => 400]
)
```

## Usage Examples

### Basic Usage

```php
// Get a contact
$contact = DT_Posts::get_post('contacts', 123);

// Get a group without checking permissions
$group = DT_Posts::get_post('groups', 456, true, false);

// Get a contact without using cache and without creating an activity log
$fresh_contact = DT_Posts::get_post('contacts', 123, false, true, true);
```

### Accessing Fields

```php
// Get a contact
$contact = DT_Posts::get_post('contacts', 123);

// Access basic information
$name = $contact['name'];
$status = $contact['overall_status']['key'];

// Access a multi-select field
$milestones = array_keys($contact['milestones'] ?? []);

// Access a connection field
$groups = array_keys($contact['groups'] ?? []);

// Access a communication channel
$phone_numbers = array_column($contact['contact_phone'] ?? [], 'value');
```

## Notes

- The method caches results for performance. Use `$use_cache = false` to get fresh data.
- Activity logs are created when viewing posts unless `$silent = true`.
- The method applies the `dt_after_get_post_fields_filter` filter to the fields before returning.
- Private fields are only returned to the user who created them.
