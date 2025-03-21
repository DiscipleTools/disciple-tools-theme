# DT_Posts List Query Guide for AI

This guide provides detailed information about using the list functionality in DT_Posts API, focusing on the `list_posts()` method.

## Overview

The Disciple.Tools API provides a method for listing posts with filtering and pagination capabilities:

`DT_Posts::list_posts()` - Lists posts with filtering, sorting, and pagination options

## Using `list_posts()`

The `list_posts()` method is used for retrieving a paginated list of posts with filtering options.

### Syntax

```php
DT_Posts::list_posts(
    string $post_type,           // Type of posts to list (e.g., 'contacts', 'groups')
    array $search_and_filter_query, // Query parameters
    bool $check_permissions = true   // Whether to check user permissions
)
```

### Query Parameters

```php
$query = [
    'offset' => 0,       // Starting position (pagination)
    'limit' => 100,      // Number of records to return
    'sort' => 'name',    // Field to sort by
    // Additional filters:
    'overall_status' => ['active'], // Field-specific filters
    'assigned_to' => [1] // Another filter
];
```

### Examples

```php
// Get first 20 contacts, sorted by name
$contacts = DT_Posts::list_posts('contacts', [
    'offset' => 0,
    'limit' => 20,
    'sort' => 'name'
]);

// Get first 20 active groups, sorted by member count descending
$groups = DT_Posts::list_posts('groups', [
    'offset' => 0,
    'limit' => 20,
    'sort' => '-member_count', // Minus sign for descending sort
    'status' => 'active'
]);
```

## Field-Specific Filter Formats

Different field types have different filter formats:

### Text Fields

```php
// Search for posts where title contains 'John'
'text' => 'John'
```

### Key Select Fields (Single Select)

```php
// Find contacts with status 'active' OR 'paused'
'overall_status' => ['active', 'paused']

// Find contacts NOT with status 'closed'
'overall_status' => ['-closed']
```

### Multi-Select Fields

```php
// Find posts with any of these values
'milestones' => ['milestone_has_bible', 'milestone_baptizing']

// Find posts WITHOUT this value
'milestones' => ['-milestone_has_bible']

// Find posts with NO values set for this field
'milestones' => []

// Find posts with ANY value set for this field
'milestones' => ['*']
```

### Connection Fields

```php
// Find contacts connected to any of these groups
'groups' => [123, 456]

// Find contacts NOT connected to this group
'groups' => [-123]

// Find contacts with no group connections
'groups' => []

// Find contacts with at least one group connection
'groups' => ['*']
```

### Date Fields

```php
// Find posts with a date in a specific range
'baptism_date' => [
    'start' => '2020-01-01',
    'end' => '2020-12-31'
]

// Find posts modified since a specific date
'last_modified' => [
    'start' => '2020-01-01'
]

// Find posts with no date set
'baptism_date' => []
```

### Boolean Fields

```php
// Find posts with field set to true
'requires_update' => [true]

// Find posts with field set to false OR not set
'requires_update' => [false]

// Find posts with no value set for this field
'requires_update' => []
```

### Communication Channels

```php
// Find contacts with a specific phone (partial match)
'contact_phone' => ['123456']

// Find contacts with exact phone match
'contact_phone' => ['^123456789']

// Find contacts without this phone
'contact_phone' => ['-123456']

// Find contacts with no phone
'contact_phone' => []

// Find contacts with any phone
'contact_phone' => ['*']
```

### Number Fields

```php
// Find groups with exactly 5 members
'member_count' => 5

// Find groups with 5 or more members
'member_count' => [
    'number' => 5,
    'operator' => '>='  // >=, >, =, <, <=
]

// Find posts with no value set
'member_count' => []
```

### User Select Fields

```php
// Find posts assigned to specific users
'assigned_to' => [1, 5]

// Find posts NOT assigned to user 1
'assigned_to' => [-1]

// Find unassigned posts
'assigned_to' => []
```

## Combining Multiple Filters

Multiple filters can be combined to create complex queries:

```php
// Find active contacts assigned to current user with specific milestones
$results = DT_Posts::list_posts('contacts', [
    'offset' => 0,
    'limit' => 50,
    'sort' => 'last_modified',
    'overall_status' => ['active'],
    'assigned_to' => [get_current_user_id()],
    'milestones' => ['milestone_has_bible', 'milestone_reading_bible'],
    'last_modified' => [
        'start' => date('Y-m-d', strtotime('-30 days'))
    ]
]);
```

## Response Format

The method returns a response in the following format:

```php
[
    'posts' => [
        // Array of post objects
    ],
    'total' => 123,  // Total number of posts matching the query
    'total_unfiltered' => 456  // Total posts available (without filters)
]
```

## Common Use Cases

### Pagination

```php
// First page (20 items per page)
$page1 = DT_Posts::list_posts('contacts', [
    'limit' => 20,
    'offset' => 0
]);

// Second page
$page2 = DT_Posts::list_posts('contacts', [
    'limit' => 20,
    'offset' => 20
]);
```

### Finding Unassigned Contacts

```php
$unassigned = DT_Posts::list_posts('contacts', [
    'assigned_to' => []
]);
```

### Finding Recently Modified Records

```php
$recent = DT_Posts::list_posts('contacts', [
    'last_modified' => [
        'start' => date('Y-m-d', strtotime('-7 days'))
    ]
]);
```

### Finding Contacts Connected to a Group

```php
$group_members = DT_Posts::list_posts('contacts', [
    'groups' => [$group_id]
]);
```

### Finding Groups with Specific Characteristics

```php
$large_active_groups = DT_Posts::list_posts('groups', [
    'status' => ['active'],
    'member_count' => [
        'number' => 10,
        'operator' => '>='
    ],
    'group_type' => ['church']
]);
```

## Error Handling

Always check for errors in the response:

```php
$result = DT_Posts::list_posts('contacts', $query);
if (is_wp_error($result)) {
    $error_code = $result->get_error_code();
    $error_message = $result->get_error_message();
    // Handle error
} else {
    $posts = $result['posts'];
    $total = $result['total'];
    // Process results
}
```

## Performance Considerations

- Always use pagination (`limit` and `offset`) for large datasets
- Limit the number of fields you request if possible
- More specific filters perform better than general text searches
- Consider caching results for frequently used queries
- For very large datasets, consider using more specific filters to reduce the result set

## How It Works Under the Hood

When you call `list_posts()`, it internally processes your filters and converts them to SQL queries. The method handles permissions checks to ensure users only see posts they have access to.

## Practical Examples

### Find Contacts Ready for Follow-up

```php
$follow_up_needed = DT_Posts::list_posts('contacts', [
    'sort' => 'last_modified',  // Oldest first
    'overall_status' => ['active'],
    'requires_update' => [true],
    'assigned_to' => [get_current_user_id()]
]);
```

### Find Potential Duplicate Contacts

```php
$potential_duplicates = DT_Posts::list_posts('contacts', [
    'limit' => 10,
    'text' => $contact_name,
    'contact_phone' => [$phone_number]
]);
```

### Track Baptism Progress

```php
$baptism_candidates = DT_Posts::list_posts('contacts', [
    'sort' => 'last_modified',
    'overall_status' => ['active'],
    'milestones' => ['milestone_has_bible', 'milestone_reading_bible'],
    'milestones' => ['-milestone_baptized'],  // Not yet baptized
    'type' => ['personal']
]);
```

### Monitor Church Health

```php
$churches = DT_Posts::list_posts('groups', [
    'sort' => '-last_modified',  // Most recently modified first
    'group_type' => ['church'],
    'status' => ['active'],
    'member_count' => [
        'number' => 1,
        'operator' => '>'
    ]
]);
```

### Find Incomplete Records

```php
$incomplete_contacts = DT_Posts::list_posts('contacts', [
    'overall_status' => ['active'],
    'contact_phone' => [],  // No phone number
    'contact_email' => [],  // No email
    'assigned_to' => [get_current_user_id()]
]);
``` 