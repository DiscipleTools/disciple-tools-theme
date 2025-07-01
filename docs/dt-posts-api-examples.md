# DT_Posts API Examples for AI

This document contains practical examples of using the DT_Posts API, derived from the unit tests, to help AI understand common patterns and how to interact with the API effectively.

## Basic CRUD Operations

### Creating a Contact

```php
// Create a simple contact
$contact = DT_Posts::create_post('contacts', [
    'title' => 'John Doe',
    'overall_status' => 'active'
]);

if (is_wp_error($contact)) {
    // Handle error
    echo $contact->get_error_message();
} else {
    // Success - $contact contains the newly created record
    $contact_id = $contact['ID'];
}

// Create a contact with more fields
$contact = DT_Posts::create_post('contacts', [
    'title' => 'Jane Smith',
    'contact_phone' => [
        'values' => [
            ['value' => '123456789']
        ]
    ],
    'contact_email' => [
        'values' => [
            ['value' => 'jane@example.com']
        ]
    ],
    'milestones' => [
        'values' => [
            ['value' => 'milestone_has_bible'],
            ['value' => 'milestone_reading_bible']
        ]
    ],
    'assigned_to' => $user_id,
    'type' => 'personal'
]);
```

### Retrieving a Contact

```php
// Get contact by ID
$contact = DT_Posts::get_post('contacts', $contact_id);

if (is_wp_error($contact)) {
    // Handle error
    echo $contact->get_error_message();
} else {
    // Success - $contact contains the retrieved record
    $name = $contact['title'];
    $status = $contact['overall_status'];
    
    // Access communication channels
    $phones = $contact['contact_phone'] ?? [];
    foreach ($phones as $phone) {
        echo $phone['value'];
    }
    
    // Access connections
    $groups = $contact['groups'] ?? [];
    foreach ($groups as $group) {
        echo $group['ID'] . ': ' . $group['post_title'];
    }
}
```

### Updating a Contact

```php
// Update basic fields
$updated = DT_Posts::update_post('contacts', $contact_id, [
    'overall_status' => 'active',
    'type' => 'media'
]);

// Update a multi-select field
$updated = DT_Posts::update_post('contacts', $contact_id, [
    'milestones' => [
        'values' => [
            ['value' => 'milestone_baptized'],
            ['value' => 'milestone_baptizing']
        ]
    ]
]);

// Update a communication channel
$updated = DT_Posts::update_post('contacts', $contact_id, [
    'contact_phone' => [
        'values' => [
            ['value' => '987654321', 'verified' => true]
        ]
    ]
]);

// Add a new communication channel
$updated = DT_Posts::update_post('contacts', $contact_id, [
    'contact_phone' => [
        'values' => [
            ['value' => '555123456']
        ]
    ]
]);

// Remove a specific value from a multi-select field
$updated = DT_Posts::update_post('contacts', $contact_id, [
    'milestones' => [
        'values' => [
            ['value' => 'milestone_baptized', 'delete' => true]
        ]
    ]
]);

// Force replace all values in a multi-select field
$updated = DT_Posts::update_post('contacts', $contact_id, [
    'milestones' => [
        'values' => [
            ['value' => 'milestone_has_bible'],
            ['value' => 'milestone_reading_bible']
        ],
        'force_values' => true
    ]
]);

// Clear all values from a field
$updated = DT_Posts::update_post('contacts', $contact_id, [
    'milestones' => [
        'values' => [],
        'force_values' => true
    ]
]);
```

## Working with Groups

### Creating a Group

```php
// Create a simple group
$group = DT_Posts::create_post('groups', [
    'title' => 'Bible Study Group',
    'group_type' => 'small_group',
    'status' => 'active'
]);

// Create a group with a leader and members
$group = DT_Posts::create_post('groups', [
    'title' => 'Discipleship Group',
    'group_type' => 'small_group',
    'status' => 'active',
    'leaders' => [
        'values' => [
            ['value' => $leader_contact_id]
        ]
    ],
    'members' => [
        'values' => [
            ['value' => $member1_contact_id],
            ['value' => $member2_contact_id]
        ]
    ]
]);
```

### Managing Group Membership

```php
// Add a member to a group
$updated_group = DT_Posts::update_post('groups', $group_id, [
    'members' => [
        'values' => [
            ['value' => $new_member_id]
        ]
    ]
]);

// Remove a member from a group
$updated_group = DT_Posts::update_post('groups', $group_id, [
    'members' => [
        'values' => [
            ['value' => $member_id, 'delete' => true]
        ]
    ]
]);

// Set a contact as a group leader
$updated_group = DT_Posts::update_post('groups', $group_id, [
    'leaders' => [
        'values' => [
            ['value' => $leader_id]
        ]
    ]
]);

// Check member count
$group = DT_Posts::get_post('groups', $group_id);
$member_count = $group['member_count'];
```

## Advanced Search and Filtering

### List Posts with Pagination

```php
// Get a list of contacts with pagination
$contacts = DT_Posts::list_posts('contacts', [
    'offset' => 0,   // Start at the first record
    'limit' => 20,   // Get 20 records per page
    'sort' => 'name' // Sort by name (ascending)
]);

// Get the next page
$next_page = DT_Posts::list_posts('contacts', [
    'offset' => 20,  // Skip the first 20 records
    'limit' => 20,   // Get 20 records per page
    'sort' => 'name'
]);

// Sort in descending order
$desc_order = DT_Posts::list_posts('contacts', [
    'offset' => 0,
    'limit' => 20,
    'sort' => '-name' // Descending order (note the minus sign)
]);
```

### Search for Specific Records

```php
// Basic text search
$results = DT_Posts::list_posts('contacts', [
    'text' => 'John'  // Search for "John" in searchable fields
]);

// Search by status
$results = DT_Posts::list_posts('contacts', [
    'overall_status' => ['active', 'paused']  // Find contacts with either status
]);

// Search by assigned user
$results = DT_Posts::list_posts('contacts', [
    'assigned_to' => [$user_id]  // Find contacts assigned to this user
]);

// Search by unassigned
$results = DT_Posts::list_posts('contacts', [
    'assigned_to' => []  // Find unassigned contacts
]);

// Search by NOT criteria
$results = DT_Posts::list_posts('contacts', [
    'overall_status' => ['-closed']  // Find contacts NOT closed
]);

// Search by connection
$results = DT_Posts::list_posts('contacts', [
    'groups' => [$group_id]  // Find contacts connected to this group
]);

// Search by multiple filters
$results = DT_Posts::list_posts('contacts', [
    'overall_status' => ['active'],
    'assigned_to' => [$user_id],
    'type' => ['personal'],
    'milestones' => ['milestone_has_bible', 'milestone_reading_bible']
]);

// Search by date range
$results = DT_Posts::list_posts('contacts', [
    'baptism_date' => [
        'start' => '2020-01-01',
        'end' => '2020-12-31'
    ]
]);

// Search by recent activity
$results = DT_Posts::list_posts('contacts', [
    'last_modified' => [
        'start' => date('Y-m-d', strtotime('-30 days'))
    ]
]);

// Search for contacts with no phone
$results = DT_Posts::list_posts('contacts', [
    'contact_phone' => []  // Find contacts with no phone
]);

// Search for contacts with any phone
$results = DT_Posts::list_posts('contacts', [
    'contact_phone' => ['*']  // Find contacts with any phone
]);

// Search by numeric field comparison
$results = DT_Posts::list_posts('groups', [
    'member_count' => [
        'number' => 5,
        'operator' => '>='  // Find groups with 5 or more members
    ]
]);
```

## Working with Comments

### Adding Comments

```php
// Add a simple comment
$comment = DT_Posts::add_post_comment(
    'contacts',
    $contact_id,
    'This is a comment on the contact record.'
);

// Add a comment with a specific type
$comment = DT_Posts::add_post_comment(
    'contacts',
    $contact_id,
    'Contact successfully completed training.',
    'activity'  // Different comment type
);

// Add a comment with additional metadata
$comment = DT_Posts::add_post_comment(
    'contacts',
    $contact_id,
    'Scheduled a follow-up meeting.',
    'comment',
    [
        'date' => '2021-06-15',
        'category' => 'follow_up'
    ]
);
```

### Retrieving Comments

```php
// Get all comments for a post
$comments = DT_Posts::get_post_comments(
    'contacts',
    $contact_id
);

// Get only comments of a specific type
$activity_comments = DT_Posts::get_post_comments(
    'contacts',
    $contact_id,
    true,
    'activity'  // Only get activity type comments
);

// Get comments with additional filters
$filtered_comments = DT_Posts::get_post_comments(
    'contacts',
    $contact_id,
    true,
    'all',
    [
        'since' => date('Y-m-d', strtotime('-7 days')),  // Only from last 7 days
        'limit' => 10  // Limit to 10 comments
    ]
);
```

## Working with Post Activity

### Getting Activity History

```php
// Get all activity for a post
$activity = DT_Posts::get_post_activity(
    'contacts',
    $contact_id
);

// Get activity with filters
$filtered_activity = DT_Posts::get_post_activity(
    'contacts',
    $contact_id,
    [
        'limit' => 20,  // Limit to 20 activity records
        'offset' => 0,  // Start from the first record
        'since' => date('Y-m-d', strtotime('-30 days'))  // Only from last 30 days
    ]
);

// Get activity for a specific field
$field_activity = DT_Posts::get_post_activity(
    'contacts',
    $contact_id,
    [
        'field_filter' => 'overall_status'  // Only status changes
    ]
);
```

## Working with Shared Records

### Sharing Records

```php
// Share a record with a user
$shared = DT_Posts::add_shared(
    'contacts',
    $contact_id,
    $user_id
);

// Share with notification metadata
$shared = DT_Posts::add_shared(
    'contacts',
    $contact_id,
    $user_id,
    [
        'message' => 'Please review this contact',
        'priority' => 'high'
    ]
);

// Share without notification
$shared = DT_Posts::add_shared(
    'contacts',
    $contact_id,
    $user_id,
    null,
    false  // Don't send notification
);
```

### Getting Shared Records

```php
// Get all users a record is shared with
$shared_with = DT_Posts::get_shared_with(
    'contacts',
    $contact_id
);

// Remove sharing from a user
$removed = DT_Posts::remove_shared(
    'contacts',
    $contact_id,
    $user_id
);
```

## Common Patterns from Test Files

Here are some common patterns observed in the unit tests:

### Force Values vs. Add/Delete Values

The test demonstrates two different ways to update multi-value fields:

```php
// Test from unit-test-contacts-groups.php
public function test_force_values() {
    // Create contact with multiple values
    $contact1 = DT_Posts::create_post('contacts', [
        'title' => 'bob',
        'milestones' => [ 'values' => [ [ 'value' => 'milestone_has_bible' ], [ 'value' => 'milestone_baptizing' ] ] ],
        'groups' => [ 'values' => [ [ 'value' => $group1['ID'] ], [ 'value' => $group2['ID'] ] ] ],
        'location_grid' => [ 'values' => [ [ 'value' => 100089589 ], [ 'value' => 100056133 ] ] ],
        'contact_phone' => [ 'values' => [ [ 'value' => '123', 'verified' => true ], [ 'value' => '321' ] ] ]
    ], true, false );
    
    // Update using force_values (replaces all existing values)
    $contact1 = DT_Posts::update_post('contacts', $contact1['ID'], [
        'milestones' => [ "values" => [ [ "value" => 'milestone_has_bible' ], [ "value" => "milestone_sharing" ] ], "force_values" => true ],
        'groups' => [ 'values' => [ [ 'value' => $group1['ID'] ], [ 'value' => $group3['ID'] ] ], 'force_values' => true ],
        'location_grid' => [ 'values' => [ [ 'value' => 100089589 ] ], 'force_values' => true ],
        'contact_phone' => [ 'values' => [ [ 'key' => $phone_key, 'value' => '456' ] ], 'force_values' => true ],
    ], true, false );
    
    // Remove all values with force_values + empty array
    $contact1 = DT_Posts::update_post('contacts', $contact1['ID'], [
        'milestones' => [ 'values' => [], 'force_values' => true ],
        'groups' => [ 'values' => [], 'force_values' => true ],
        'location_grid' => [ 'values' => [], 'force_values' => true ],
        'contact_phone' => [ 'values' => [], 'force_values' => true ],
    ], true, false );
}
```

### Testing Member Count on Groups

The tests show how to manage members and track the count:

```php
// Test from unit-test-contacts-groups.php
public function test_member_count(){
    $contact1 = DT_Posts::create_post('contacts', self::$sample_contact);
    
    // Create group with contact1 as member
    $group1 = DT_Posts::create_post('groups', [
        'title'   => 'group1',
        'members' => [ 'values' => [ [ 'value' => $contact1['ID'] ] ] ]
    ]);
    
    // Verify member count is 1
    $this->assertSame($group1['member_count'], 1);
    
    // Create contact2 with group1 in groups
    $contact2 = DT_Posts::create_post('contacts', [
        'title' => 'contact 2',
        'groups' => [ 'values' => [ [ 'value' => $group1['ID'] ] ] ]
    ]);
    
    // Refresh group and verify member count is 2
    $group1 = DT_Posts::get_post('groups', $group1['ID'], false);
    $this->assertSame($group1['member_count'], 2);
    
    // Remove the connection from contact2 to group1
    $contact2 = DT_Posts::update_post('contacts', $contact2['ID'], [
        'groups' => [
            'values' => [
                [
                    'value' => $group1['ID'],
                    'delete' => true
                ]
            ]
        ]
    ]);
    
    // Refresh group and verify member count is 1
    $group1 = DT_Posts::get_post('groups', $group1['ID'], false);
    $this->assertSame(1, $group1['member_count']);
}
```

### Search Query Testing

The tests demonstrate how to use various search criteria:

```php
// Test from unit-test-search-viewable-post.php
public function test_search_fields_structure(){
    // Multi-select field search
    $res = DT_Posts::list_posts('contacts', [
        'milestones' => ['milestone_has_bible', 'milestone_making_disciples']
    ], false);
    
    // Negative search (NOT)
    $res = DT_Posts::list_posts('contacts', [
        'milestones' => ['-milestone_has_bible']
    ], false);
    
    // Empty field search
    $res = DT_Posts::list_posts('contacts', [
        'milestones' => []
    ], false);
    
    // Date range search
    $range = DT_Posts::list_posts('contacts', [
        'baptism_date' => [
            'start' => '1980-01-02',
            'end' => '1980-01-04'
        ]
    ], false);
    
    // Boolean field search
    $bool1 = DT_Posts::list_posts('contacts', [
        'requires_update' => [true],
        'groups' => [$group['ID']]
    ], false);
    
    // Communication channel search
    $phone = DT_Posts::list_posts('contacts', [
        'contact_phone' => ['798456780']
    ], false);
    
    // Number field with operator
    $res = DT_Posts::list_posts('groups', [
        'member_count' => [
            'number' => '5',
            'operator' => '>='
        ]
    ], false);
}
```

## Error Handling Examples

```php
// Create/Update with error handling
$result = DT_Posts::create_post('contacts', $fields);
if (is_wp_error($result)) {
    // Error occurred
    $error_code = $result->get_error_code();
    $error_message = $result->get_error_message();
    $error_data = $result->get_error_data();
    
    // Handle specific errors
    if ($error_code === 'duplicate_contact_error') {
        // Handle duplicate contact case
        $duplicate_ids = $error_data['duplicate_ids'] ?? [];
        // Maybe merge with existing contact
    } 
    elseif ($error_code === '__FUNCTION__') {
        // Permission error
        // Maybe notify user they don't have access
    }
    else {
        // Generic error handling
        // Log or display error
    }
} else {
    // Success - proceed with the created/updated record
    $post_id = $result['ID'];
}
```

## Using Permissions Functions

```php
// Check if user can access post type
if (DT_Posts::can_access('contacts')) {
    // User has access to contacts
}

// Check if user can view a specific post
if (DT_Posts::can_view('contacts', $contact_id)) {
    // User can view this contact
}

// Check if user can update a specific post
if (DT_Posts::can_update('contacts', $contact_id)) {
    // User can update this contact
}

// Check if user can create posts of this type
if (DT_Posts::can_create('contacts')) {
    // User can create contacts
}

// Check if user can view all posts of this type
if (DT_Posts::can_view_all('contacts')) {
    // User can view all contacts
}

// Check if user can list all posts of this type
if (DT_Posts::can_list_all('contacts')) {
    // User can list all contacts
}
```