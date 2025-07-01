# Disciple.Tools Posts API - AI Reference Documentation

This documentation provides a comprehensive guide to the DT_Posts API for AI usage when working with Disciple.Tools.

## Table of Contents

1. [Overview](#overview)
2. [Core Classes](#core-classes)
3. [Post Methods](#post-methods)
4. [Field Formats](#field-formats)
5. [Query Examples](#query-examples)
6. [Common Use Cases](#common-use-cases)
7. [Error Handling](#error-handling)

## Overview

The DT_Posts API is the primary interface for interacting with Disciple.Tools records (posts) like contacts, groups, and other custom post types. It extends Disciple_Tools_Posts which provides base functionality for permission checks and other shared methods.

## Core Classes

### `DT_Posts` 

Main class handling creation, retrieval, updating and listing of posts.

```php
class DT_Posts extends Disciple_Tools_Posts {
    // Methods for interacting with posts
}
```

### `Disciple_Tools_Posts`

Parent class containing permission checks and utility methods.

```php
class Disciple_Tools_Posts {
    // Permission and utility methods
}
```

## Post Methods

### Get Post Types

```php
DT_Posts::get_post_types()
```
Returns array of all registered post types.

### Get Post Settings

```php
DT_Posts::get_post_settings(string $post_type, $return_cache = true, $load_tags = false)
```
Returns settings for a post type, including fields, tiles, and other configuration.

### Create Post

```php
DT_Posts::create_post(string $post_type, array $fields, bool $silent = false, bool $check_permissions = true, $args = [])
```
Creates a new post of the specified type with the given fields.

**Example:**
```php
$contact = DT_Posts::create_post('contacts', [
    'title' => 'John Doe',
    'contact_phone' => [
        'values' => [
            ['value' => '123456789']
        ]
    ],
    'assigned_to' => $user_id
]);
```

### Update Post

```php
DT_Posts::update_post(string $post_type, int $post_id, array $fields, bool $silent = false, bool $check_permissions = true)
```
Updates an existing post with the provided fields.

**Example:**
```php
$updated_contact = DT_Posts::update_post('contacts', $contact_id, [
    'status' => 'active',
    'milestones' => [
        'values' => [
            ['value' => 'milestone_has_bible']
        ]
    ]
]);
```

### Get Post

```php
DT_Posts::get_post(string $post_type, int $post_id, bool $use_cache = true, bool $check_permissions = true, bool $silent = false)
```
Retrieves a post by ID.

**Example:**
```php
$contact = DT_Posts::get_post('contacts', $contact_id);
```

### List Posts

```php
DT_Posts::list_posts(string $post_type, array $search_and_filter_query, bool $check_permissions = true)
```
Retrieves a list of posts based on search and filter criteria.

**Example:**
```php
$contacts = DT_Posts::list_posts('contacts', [
    'offset' => 0,
    'limit' => 100,
    'sort' => 'name'
]);
```

### Post Comments

```php
DT_Posts::add_post_comment(string $post_type, int $post_id, string $comment_html, string $type = 'comment', array $args = [], bool $check_permissions = true, $silent = false)
```
Adds a comment to a post.

```php
DT_Posts::get_post_comments(string $post_type, int $post_id, bool $check_permissions = true, string $type = 'all', array $args = [])
```
Retrieves comments for a post.

### Post Activity

```php
DT_Posts::get_post_activity(string $post_type, int $post_id, array $args = [])
```
Retrieves activity history for a post.

### Sharing

```php
DT_Posts::get_shared_with(string $post_type, int $post_id, bool $check_permissions = true)
```
Gets users with whom a post is shared.

```php
DT_Posts::add_shared(string $post_type, int $post_id, int $user_id, $meta = null, bool $send_notifications = true, $check_permissions = true, bool $insert_activity = true)
```
Shares a post with a user.

```php
DT_Posts::remove_shared(string $post_type, int $post_id, int $user_id)
```
Removes sharing from a user.

## Field Formats

Different field types require specific formats when creating or updating posts.

### Text Field
```php
'title' => 'John Doe'
```

### Date Field
```php
'baptism_date' => '2021-01-15'
```

### Key Select (Dropdown)
```php
'status' => 'active'
```

### Multi Select
```php
'milestones' => [
    'values' => [
        ['value' => 'milestone_has_bible'],
        ['value' => 'milestone_baptizing']
    ],
    'force_values' => true  // Optional: replaces all existing values
]
```

### Connection Fields
```php
'groups' => [
    'values' => [
        ['value' => 123],  // Add connection to group with ID 123
        ['value' => 456, 'delete' => true]  // Remove connection to group 456
    ]
]
```

### Communication Channels (Phone, Email, etc.)
```php
'contact_phone' => [
    'values' => [
        ['value' => '123456789', 'verified' => true],
        ['value' => '987654321']
    ]
]
```

### Location Fields
```php
'location_grid' => [
    'values' => [
        ['value' => 100089589]  // Location grid ID
    ]
]
```

### User Select
```php
'assigned_to' => $user_id
```

### Boolean Fields
```php
'requires_update' => true
```

## Query Examples

### List Query Parameters

The `list_posts` method accepts these common parameters:

```php
$query = [
    'offset' => 0,       // Starting position
    'limit' => 100,      // Number of records to return
    'sort' => 'name',    // Field to sort by (prefix with '-' for descending)
    'overall_status' => ['active'], // Field-specific filter
    'assigned_to' => [5] // Another filter example
];
```

### Search Query Format

The `list_posts` method accepts these filter formats:

#### Text search
```php
'text' => 'search term'  // Free text search
```

#### Multi-select fields
```php
'milestones' => ['milestone_has_bible', 'milestone_baptizing']  // Match posts with any of these values
'milestones' => ['-milestone_has_bible']  // Match posts WITHOUT this value
'milestones' => []  // Match posts with no values for this field
'milestones' => ['*']  // Match posts with ANY value for this field
```

#### Connection fields
```php
'groups' => [123, 456]  // Match posts connected to any of these group IDs
'groups' => [-123]  // Match posts NOT connected to this group ID
```

#### Date fields
```php
'baptism_date' => [
    'start' => '2020-01-01',
    'end' => '2020-12-31'
]
```

#### Boolean fields
```php
'requires_update' => [true]  // Match posts with this field set to true
'requires_update' => [false]  // Match posts with this field set to false or not set
```

#### Number fields
```php
'member_count' => [
    'number' => 5,
    'operator' => '>='  // >=, >, =, <, <=
]
```

## Common Use Cases

### Creating and Assigning a Contact

```php
$contact = DT_Posts::create_post('contacts', [
    'title' => 'John Doe',
    'contact_phone' => ['values' => [['value' => '123456789']]],
    'assigned_to' => get_current_user_id(),
    'type' => 'personal'
]);
```

### Updating Contact Status and Faith Milestones

```php
$updated = DT_Posts::update_post('contacts', $contact_id, [
    'overall_status' => 'active',
    'seeker_path' => 'searching',
    'milestones' => [
        'values' => [
            ['value' => 'milestone_has_bible'],
            ['value' => 'milestone_reading_bible']
        ]
    ]
]);
```

### Creating a Group and Adding Members

```php
// Create group
$group = DT_Posts::create_post('groups', [
    'title' => 'Bible Study Group',
    'group_type' => 'bible_study',
    'status' => 'active'
]);

// Add members to group
if (!is_wp_error($group)) {
    foreach ($member_ids as $member_id) {
        DT_Posts::update_post('groups', $group['ID'], [
            'members' => [
                'values' => [
                    ['value' => $member_id]
                ]
            ]
        ]);
    }
}
```

### Finding Contacts with Specific Criteria

```php
$results = DT_Posts::list_posts('contacts', [
    'overall_status' => ['active'],
    'assigned_to' => [get_current_user_id()],
    'seeker_path' => ['searching', 'curious'],
    'last_modified' => [
        'start' => date('Y-m-d', strtotime('-30 days'))
    ]
]);
```

## Error Handling

Most API methods return a WP_Error object on failure. Always check for errors:

```php
$result = DT_Posts::update_post('contacts', $contact_id, $fields);
if (is_wp_error($result)) {
    // Handle error
    $error_message = $result->get_error_message();
    $error_code = $result->get_error_code();
} else {
    // Success
    $updated_contact = $result;
}
```

Common error codes:
- `__FUNCTION__` - Permission denied
- `missing_values` - Required fields are missing
- `invalid_values` - Field values are invalid
- `post_type_not_found` - The post type does not exist
- `post_not_found` - The post ID does not exist 