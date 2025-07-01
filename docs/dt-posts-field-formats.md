# DT_Posts Field Formats Guide for AI

This guide provides detailed information about the field formats used in the DT_Posts API for creating, updating, and managing posts in Disciple.Tools. It's based on the documentation at https://developers.disciple.tools/theme-core/api-posts/post-types-fields-format and the codebase.

## Overview

Disciple.Tools uses a custom field structure for different types of fields. Understanding these formats is essential for correctly creating and updating records. Each field type has a specific format for providing values.

## Basic Field Types

### Text Fields

Simple text fields are passed directly as strings.

```php
'title' => 'John Smith',
'notes' => 'Met at conference'
```

### Key Select Fields (Dropdowns)

Single-select dropdown fields use a string value from the available options.

```php
'overall_status' => 'active',
'type' => 'personal',
'seeker_path' => 'curious'
```

### Boolean Fields

Boolean fields accept true/false values.

```php
'requires_update' => true,
'baptized' => false
```

### Date Fields

Date fields use string dates in YYYY-MM-DD format.

```php
'baptism_date' => '2021-03-15',
'start_date' => '2020-01-01'
```

### Number Fields

Numeric fields accept integer or float values.

```php
'member_count' => 5,
'age' => 42
```

## Complex Field Types

### Multi-Select Fields

Multi-select fields use a structured format with arrays of values.

```php
'milestones' => [
    'values' => [
        ['value' => 'milestone_has_bible'],
        ['value' => 'milestone_reading_bible']
    ]
]
```

To remove a value:

```php
'milestones' => [
    'values' => [
        ['value' => 'milestone_has_bible', 'delete' => true]
    ]
]
```

To replace all values (instead of adding):

```php
'milestones' => [
    'values' => [
        ['value' => 'milestone_has_bible'],
        ['value' => 'milestone_reading_bible']
    ],
    'force_values' => true
]
```

To clear all values:

```php
'milestones' => [
    'values' => [],
    'force_values' => true
]
```

### Connection Fields

Connection fields link to other post types (contacts, groups, etc.) and use a similar structure to multi-select fields.

```php
'groups' => [
    'values' => [
        ['value' => 123],  // ID of the group to connect
        ['value' => 456]   // Another group ID
    ]
]
```

To remove a connection:

```php
'groups' => [
    'values' => [
        ['value' => 123, 'delete' => true]
    ]
]
```

To replace all connections:

```php
'groups' => [
    'values' => [
        ['value' => 123],
        ['value' => 789]
    ],
    'force_values' => true
]
```

### Communication Channels (Contact Methods)

Communication channels like phone, email, etc. use a special format with unique keys.

```php
'contact_phone' => [
    'values' => [
        ['value' => '123456789'],
        ['value' => '987654321', 'verified' => true]
    ]
]
```

To update an existing communication channel:

```php
'contact_phone' => [
    'values' => [
        ['key' => 'existing_key', 'value' => '555123456']
    ]
]
```

To delete a communication channel:

```php
'contact_phone' => [
    'values' => [
        ['key' => 'existing_key', 'delete' => true]
    ]
]
```

### Location Fields

Location fields use location grid IDs.

```php
'location_grid' => [
    'values' => [
        ['value' => 100089589]  // Location grid ID
    ]
]
```

### User Select Fields

User select fields reference user IDs.

```php
'assigned_to' => 5,  // User ID 5
'coached_by' => 10   // User ID 10
```

## Complete Example

Here's a complete example of creating a contact with various field types:

```php
$contact = DT_Posts::create_post('contacts', [
    // Basic fields
    'title' => 'John Smith',
    'overall_status' => 'active',
    'type' => 'personal',
    'requires_update' => false,
    
    // Date field
    'baptism_date' => '2021-03-15',
    
    // Multi-select field
    'milestones' => [
        'values' => [
            ['value' => 'milestone_has_bible'],
            ['value' => 'milestone_reading_bible']
        ]
    ],
    
    // Communication channels
    'contact_phone' => [
        'values' => [
            ['value' => '123456789', 'verified' => true]
        ]
    ],
    'contact_email' => [
        'values' => [
            ['value' => 'john@example.com']
        ]
    ],
    
    // Location
    'location_grid' => [
        'values' => [
            ['value' => 100089589]
        ]
    ],
    
    // Connection to a group
    'groups' => [
        'values' => [
            ['value' => 123]
        ]
    ],
    
    // User assignment
    'assigned_to' => get_current_user_id()
]);
```

## Field Formats by Post Type

Different post types have different available fields. Here's an overview of common fields for the main post types:

### Contacts

```php
$contact_fields = [
    'title' => 'Contact Name',
    'contact_phone' => ['values' => [['value' => '123456789']]],
    'contact_email' => ['values' => [['value' => 'contact@example.com']]],
    'contact_address' => ['values' => [['value' => '123 Main St']]],
    'contact_facebook' => ['values' => [['value' => 'facebook_id']]],
    'contact_whatsapp' => ['values' => [['value' => 'whatsapp_id']]],
    'location_grid' => ['values' => [['value' => 100089589]]],
    'overall_status' => 'active',
    'seeker_path' => 'curious',
    'milestones' => ['values' => [['value' => 'milestone_has_bible']]],
    'baptism_date' => '2021-03-15',
    'baptized_by' => ['values' => [['value' => 456]]],  // Contact ID
    'groups' => ['values' => [['value' => 123]]],       // Group ID
    'coaches' => ['values' => [['value' => 789]]],      // Contact ID
    'assigned_to' => 5,  // User ID
    'requires_update' => false,
    'reason_closed' => 'moved',
    'sources' => ['values' => [['value' => 'web']]],
    'type' => 'personal',
    'age' => 30,
    'gender' => 'male',
    'faith_status' => 'growing',
    'notes' => 'Some notes about the contact'
];
```

### Groups

```php
$group_fields = [
    'title' => 'Group Name',
    'group_type' => 'small_group',
    'status' => 'active',
    'member_count' => 5,
    'location_grid' => ['values' => [['value' => 100089589]]],
    'start_date' => '2020-01-01',
    'end_date' => '2022-12-31',
    'members' => ['values' => [['value' => 123]]],  // Contact ID
    'leaders' => ['values' => [['value' => 456]]],  // Contact ID
    'coaches' => ['values' => [['value' => 789]]],  // Contact ID
    'parent_groups' => ['values' => [['value' => 101]]],  // Group ID
    'peer_groups' => ['values' => [['value' => 102]]],    // Group ID
    'child_groups' => ['values' => [['value' => 103]]],   // Group ID
    'meeting_time' => 'Tuesdays at 7pm',
    'meeting_location' => 'Community Center',
    'assigned_to' => 5,  // User ID
    'health_metrics' => [
        'values' => [
            ['value' => 'church_bible'],
            ['value' => 'church_praise']
        ]
    ]
];
```

## Special Considerations

### Keys for Communication Channels

When updating existing communication channels, you need the `key` of the channel, which is returned when you get the post. Example:

```php
$contact = DT_Posts::get_post('contacts', $contact_id);
$phone_key = $contact['contact_phone'][0]['key'];

$updated = DT_Posts::update_post('contacts', $contact_id, [
    'contact_phone' => [
        'values' => [
            ['key' => $phone_key, 'value' => '555123456']
        ]
    ]
]);
```

### Force Values vs. Incremental Updates

By default, multi-value fields are updated incrementally (adding/removing values). Use `force_values` when you want to replace all values at once:

```php
// Incremental (adds new value, keeps existing)
$updated = DT_Posts::update_post('contacts', $contact_id, [
    'milestones' => [
        'values' => [
            ['value' => 'milestone_baptized']
        ]
    ]
]);

// Force values (replaces ALL values)
$updated = DT_Posts::update_post('contacts', $contact_id, [
    'milestones' => [
        'values' => [
            ['value' => 'milestone_baptized']
        ],
        'force_values' => true
    ]
]);
```

### Adding Meta to Connections

Some connection fields can include additional metadata:

```php
'groups' => [
    'values' => [
        [
            'value' => 123,
            'meta' => [
                'role' => 'leader',
                'since' => '2021-01-01'
            ]
        ]
    ]
]
```

### Field Validation

The API performs validation on fields:

1. Field type must exist for the post type
2. Field values must match expected formats 
3. Selected values must be valid for enumerated fields (key_select, multi-select)
4. Dates must use proper format
5. User permissions are verified for operations

## Getting Field Definitions

To understand what fields are available for a post type:

```php
$post_settings = DT_Posts::get_post_settings('contacts');
$fields = $post_settings['fields'];

// Example: check field type
$field_type = $fields['overall_status']['type'];  // 'key_select'

// Example: get available options for a field
$status_options = $fields['overall_status']['default'];
```

## Error Handling for Field Format Issues

If fields are formatted incorrectly, the API will return WP_Error objects:

```php
$result = DT_Posts::update_post('contacts', $contact_id, $fields);
if (is_wp_error($result)) {
    if ($result->get_error_code() === 'missing_values') {
        // Required fields are missing
    }
    if ($result->get_error_code() === 'invalid_values') {
        // Field values are invalid
    }
    // Handle error
}
``` 