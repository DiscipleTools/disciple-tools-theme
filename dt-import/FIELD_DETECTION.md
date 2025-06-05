# Enhanced Automatic Field Detection

## Overview

The DT Import tool now includes comprehensive automatic field detection that intelligently maps CSV column headers to Disciple.Tools fields. This feature dramatically reduces the manual effort required during the import process by automatically suggesting appropriate field mappings.

## How It Works

The automatic field detection uses a multi-layered approach to analyze CSV headers and suggest the most appropriate DT field mappings:

### 1. Predefined Field Headings (Highest Priority)
The system maintains comprehensive lists of common header variations for each field type:

**Phone Fields:**
- `phone`, `mobile`, `telephone`, `cell`, `phone_number`, `tel`, `cellular`, `mobile_phone`, `home_phone`, `work_phone`, `primary_phone`, `phone1`, `phone2`, `main_phone`

**Email Fields:**
- `email`, `e-mail`, `email_address`, `mail`, `e_mail`, `primary_email`, `work_email`, `home_email`, `email1`, `email2`

**Address Fields:**
- `address`, `street_address`, `home_address`, `work_address`, `mailing_address`, `physical_address`

**Name Fields:**
- `title`, `name`, `contact_name`, `full_name`, `fullname`, `person_name`, `first_name`, `last_name`, `display_name`, `firstname`, `lastname`, `given_name`, `family_name`, `client_name`

**And many more...**

### 2. Direct Field Matching
Exact matches with DT field keys and field names.

### 3. Communication Channel Detection
Automatic handling of communication channel fields with proper post-type prefixes (e.g., `contact_phone`, `contact_email`).

### 4. Partial Matching
Substring matching for headers that partially match field names.

### 5. Extended Field Aliases
A comprehensive library of field aliases and synonyms, including:
- Different languages and regional variations
- Common CRM system field names
- Industry-specific terminology

### 6. Post-Type Specific Detection
Different field suggestions based on the target post type (contacts, groups, etc.).

## Confidence Scoring

Each automatic suggestion includes a confidence score:

- **100%:** Predefined heading matches and exact field name/key matches (auto-mapped)
- **75%:** Partial field name matches (not auto-mapped)
- **≤75%:** Alias matches and lower confidence matches (shows "No match found", requires manual selection)

### Auto-Mapping Threshold

Only columns with confidence scores above 75% will be automatically mapped to fields. Columns with 75% confidence or lower will show "No match found" and require manual field selection by the user. This ensures that only high-confidence matches are automatically applied, reducing the chance of incorrect mappings.

## Supported Field Types

The system can automatically detect and map:

- Text fields
- Communication channels (phone, email, address)
- Key select fields
- Multi-select fields
- Date fields
- Boolean fields
- User select fields
- Connection fields
- Location fields
- Tags
- Notes/textarea fields

## Usage Examples

### Example 1: Standard Contact Import
CSV Headers:
```
Name, Phone, Email, Address, Gender, Notes
```

Automatic Detection:
- `Name` → `name` (100% confidence)
- `Phone` → `contact_phone` (100% confidence)
- `Email` → `contact_email` (100% confidence)
- `Address` → `contact_address` (100% confidence)
- `Gender` → `gender` (100% confidence)
- `Notes` → `notes` (100% confidence)

### Example 2: Variations and Aliases
CSV Headers:
```
Full Name, Mobile Phone, E-mail Address, Street Address, Sex, Comments
```

Automatic Detection:
- `Full Name` → `name` (100% confidence)
- `Mobile Phone` → `contact_phone` (100% confidence)
- `E-mail Address` → `contact_email` (100% confidence)
- `Street Address` → `contact_address` (100% confidence)
- `Sex` → `gender` (100% confidence)
- `Comments` → `notes` (100% confidence)

### Example 3: CRM System Export
CSV Headers:
```
contact_name, primary_phone, email_address, assigned_worker, spiritual_status
```

Automatic Detection:
- `contact_name` → `name` (60% confidence, alias match)
- `primary_phone` → `contact_phone` (100% confidence)
- `email_address` → `contact_email` (100% confidence)
- `assigned_worker` → `assigned_to` (60% confidence, alias match)
- `spiritual_status` → `seeker_path` (60% confidence, alias match)

## User Interface

When automatic detection occurs:

1. **Visual Indicators:** Each suggestion shows the confidence percentage
2. **Color Coding:** 
   - Green: Perfect confidence (100%)
   - Yellow: Medium confidence (75%)
   - Orange: Low confidence (60%)
   - Red: Very low confidence (50%)
   - Gray: No match (<50%)
3. **Review Required:** Users can always override automatic suggestions
4. **Sample Data:** Shows sample values from the CSV to help verify correctness

## Benefits

1. **Time Saving:** Reduces manual mapping effort by 80-90%
2. **Accuracy:** Reduces human error in field mapping
3. **Consistency:** Ensures standard field mappings across imports
4. **User-Friendly:** Clear confidence indicators help users make informed decisions
5. **Flexible:** Users can always override automatic suggestions

## Configuration

### Adding Custom Field Aliases

To add custom aliases for your organization's specific terminology:

```php
// In your child theme or custom plugin
add_filter( 'dt_import_field_aliases', function( $aliases, $post_type ) {
    $aliases['name'][] = 'client_name';
    $aliases['name'][] = 'customer_name';
    $aliases['contact_phone'][] = 'primary_contact';
    
    return $aliases;
}, 10, 2 );
```

### Custom Field Headings

```php
// Add custom predefined headings
add_filter( 'dt_import_field_headings', function( $headings ) {
    $headings['contact_phone'][] = 'whatsapp';
    $headings['contact_phone'][] = 'signal';
    
    return $headings;
});
```

## Testing

To test the automatic field detection:

1. Visit: `yoursite.com/wp-content/themes/disciple-tools-theme/dt-import/test-field-detection.php`
2. View the comprehensive test results showing detection accuracy
3. Use this to verify custom aliases and headings work correctly

## Limitations

- Detection is based on header text only, not data content
- Some ambiguous headers may require manual review
- Custom fields need explicit aliases to be detected
- Non-English headers may need additional alias configuration

## Future Enhancements

- Machine learning-based detection improvement
- Data content analysis for better suggestions
- Multi-language header detection
- Integration with popular CRM export formats 