# CSV Import Documentation

## Overview

The CSV Import feature allows you to bulk import data into Disciple Tools from CSV (Comma-Separated Values) files. This tool supports importing both **Contacts** and **Groups** with comprehensive field mapping and validation.

## Getting Started

### Access the Import Tool

1. Navigate to **Admin** → **Settings** → **Import** tab
2. You must have `manage_dt` permissions to access the import functionality

**Note**: Only users with administrator or manager roles can access the import feature. If you don't see the Import tab, contact your system administrator to request the necessary permissions.

### Import Process

The import process consists of 4 main steps:

1. **Select Record Type** - Choose whether to import Contacts or Groups
2. **Upload CSV & Configure Options** - Upload your CSV file (max 10MB) and set import options
3. **Map Fields** - Map your CSV columns to Disciple Tools fields
4. **Preview & Import** - Review and execute the import

---

## File Requirements

### File Format
- **File Type**: CSV (.csv files only)
- **Maximum Size**: 10MB
- **Encoding**: UTF-8 recommended
- **Delimiters**: Comma (,), semicolon (;), tab, or pipe (|) - automatically detected

### CSV Structure
- **Headers Required**: First row must contain column headers
- **Consistent Columns**: All rows must have the same number of columns
- **No Duplicate Headers**: Column headers must be unique
- **Empty Values**: Leave cells empty if no data available

---

## Supported Field Types

The CSV import tool supports 14 different field types. Each field type has specific formatting requirements and validation rules.

### 1. Text Fields

**Field Type**: `text`

**Description**: Single-line text input

**Accepted Values**: Any string up to reasonable length

**Examples**:

| Name | Description |
|------|-------------|
| John Smith | A new contact from the outreach event |
| Mary Johnson | Referred by existing member |

---

### 2. Text Area Fields

**Field Type**: `textarea`

**Description**: Multi-line text input

**Accepted Values**: Any string including line breaks

**Examples**:

| Notes |
|-------|
| Met at coffee shop. Very interested in Bible study. Follow up next week. |
| Previous church member. Moving to the area next month. |

---

### 3. Number Fields

**Field Type**: `number`

**Description**: Numeric values (integers or decimals)

**Accepted Values**: 
- Integers: `1`, `42`, `-5`
- Decimals: `3.14`, `0.5`, `-2.7`

**Examples**:

| Age | Score |
|-----|-------|
| 25 | 8.5 |
| 42 | 10 |
| 18 | 7.2 |

---

### 4. Date Fields

**Field Type**: `date`

**Description**: Date values

**Accepted Formats**:
- ISO format: `2024-01-15` (recommended)
- US format: `01/15/2024`, `1/15/2024`
- European format: `15/01/2024`, `15.01.2024`
- Text dates: `January 15, 2024`, `15 Jan 2024`

**Examples**:

| Birth Date | Last Contact |
|------------|--------------|
| 2024-01-15 | 2024-03-20 |
| 01/15/1990 | March 20, 2024 |
| 1985-12-25 | 12/15/2023 |

---

### 5. Boolean Fields

**Field Type**: `boolean`

**Description**: True/false values

**Accepted Values for TRUE**:
- `true`, `True`, `TRUE`
- `yes`, `Yes`, `YES`
- `y`, `Y`
- `1`
- `on`, `On`, `ON`
- `enabled`, `Enabled`, `ENABLED`

**Accepted Values for FALSE**:
- `false`, `False`, `FALSE`
- `no`, `No`, `NO`
- `n`, `N`
- `0`
- `off`, `Off`, `OFF`
- `disabled`, `Disabled`, `DISABLED`

**Examples**:

| Baptized | Requires Follow Up |
|----------|-------------------|
| true | no |
| yes | 1 |
| false | disabled |
| Y | off |

---

### 6. Dropdown Fields (Key Select)

**Field Type**: `key_select`

**Description**: Single selection from predefined options

**Accepted Values**: Must match exact option keys or be mapped during import

**Common Examples**:

**Contact Status**:
- `new`
- `unassigned`
- `assigned`
- `active`
- `paused`
- `closed`
- `unassignable`

**Contact Type**:
- `personal`
- `placeholder`
- `user`

**Seeker Path** (Contacts):
- `none`
- `attempted`
- `established`
- `scheduled`
- `met`
- `ongoing`
- `coaching`

**Examples**:

| Status | Type | Seeker Path |
|--------|------|-------------|
| new | personal | attempted |
| active | personal | ongoing |
| paused | personal | met |
| closed | placeholder | none |

---

### 7. Multi-Select Fields

**Field Type**: `multi_select`

**Description**: Multiple selections from predefined options

**Format**: Separate multiple values with semicolons (`;`)

**Common Examples**:

**Sources** (Contacts):
- `web`
- `phone`
- `facebook`
- `twitter`
- `instagram`
- `referral`
- `advertisement`
- `event`

**Milestones** (Contacts):
- `milestone_has_bible`
- `milestone_reading_bible`
- `milestone_belief`
- `milestone_can_share`
- `milestone_sharing`
- `milestone_baptized`
- `milestone_baptizing`
- `milestone_in_group`
- `milestone_planting`

**Health Metrics** (Groups):
- `church_giving`
- `church_fellowship`
- `church_communion`
- `church_baptism`
- `church_prayer`
- `church_leaders`
- `church_bible`
- `church_praise`
- `church_sharing`
- `church_commitment`

**Examples**:

| Sources | Milestones |
|---------|------------|
| web;referral | milestone_has_bible;milestone_reading_bible |
| facebook | milestone_belief;milestone_baptized;milestone_in_group |
| phone;event | milestone_has_bible;milestone_can_share |

---

### 8. Tags Fields

**Field Type**: `tags`

**Description**: Free-form tags/labels

**Format**: Separate multiple tags with semicolons (`;`)

**Examples**:

| Tags |
|------|
| VIP;follow-up-needed;speaks-spanish |
| new-believer;youth;musician |
| staff;volunteer;leader |

---

### 9. Communication Channel Fields

**Field Type**: `communication_channel`

**Description**: Contact methods like phone numbers and email addresses

**Format**: Separate multiple values with semicolons (`;`)

**Validation**:
- **Email fields**: Must be valid email format
- **Phone fields**: Must contain at least one digit

**Common Fields**:
- `contact_phone`
- `contact_email`
- `contact_address`
- `contact_facebook`
- `contact_telegram`
- `contact_whatsapp`

**Examples**:

| Email | Phone |
|-------|-------|
| john@example.com | +1-555-123-4567 |
| mary.smith@email.com;backup@email.com | 555-987-6543;555-111-2222 |
| contact@church.org | (555) 123-4567;555-987-6543 |

---

### 10. Connection Fields

**Field Type**: `connection`

**Description**: Relationships to other records

**Accepted Values**:
- **Numeric ID**: `123`, `456`
- **Record Title/Name**: `"John Smith"`, `"Downtown Group"`

**Format**: Separate multiple connections with semicolons (`;`)

**Connection Logic**:
- **Record ID (numeric)**: Links directly to that specific record
- **Record name (text)**: Searches for existing records by name
- **Single match found**: Connects to the existing record
- **No match found**: Creates a new record with that name
- **Multiple matches found**: That specific connection is skipped (other connections in the same field will still process)

**Common Examples**:

**For Contacts**:
- `baptized_by` (connects to other contacts)
- `baptized` (connects to other contacts)
- `coached_by` (connects to other contacts)
- `coaching` (connects to other contacts)
- `groups` (connects to groups)

**For Groups**:
- `parent_groups` (connects to other groups)
- `child_groups` (connects to other groups)
- `members` (connects to contacts)
- `leaders` (connects to contacts)

**Examples**:

| Coached By | Groups |
|------------|--------|
| John Smith | Downtown Bible Study |
| 142 | Small Group Alpha;Youth Group |
| Mary Johnson | Prayer Group;Bible Study |

**Important**: If multiple records exist with the same name (e.g., two contacts named "John Smith"), that connection will be skipped. To avoid this, use record IDs instead of names, or ensure all records have unique names before importing.

---

### 11. User Select Fields

**Field Type**: `user_select`

**Description**: Assignment to system users

**Accepted Values**:
- **User ID**: `1`, `25`
- **Username**: `admin`, `john_doe`
- **Display Name**: `"John Doe"`, `"Mary Smith"`

**Common Fields**:
- `assigned_to`
- `overall_status`

**Examples**:

| Assigned To |
|-------------|
| john_doe |
| 25 |
| John Doe |
| admin |

---

### 12. Location Fields

**Field Type**: `location`

**Description**: Geographic location information using coordinates or grid IDs only

**Accepted Values**: 
- **Grid ID**: `100364199` (numeric location grid ID)
- **Decimal coordinates**: `"40.7128,-74.0060"` (latitude,longitude)
- **DMS coordinates**: `"35°50′40.9″N, 103°27′7.5″E"` (degrees, minutes, seconds)

**Multiple Locations**: Separate multiple values with semicolons (`;`)

**Note**: Location fields do NOT accept address strings. For addresses, use `location_meta` fields instead.

**Coordinate Formats**:

**Decimal Degrees** (recommended):
- Format: `latitude,longitude`
- Range: -90 to 90 for latitude, -180 to 180 for longitude
- Examples: `40.7128,-74.0060`, `35.8447,103.4521`

**DMS (Degrees, Minutes, Seconds)**:
- Format: `DD°MM′SS.S″N/S, DDD°MM′SS.S″E/W`
- Direction indicators (N/S/E/W) are **required**
- Supports various symbols: `°′″` or `d m s` or regular quotes `'"`
- Examples: 
  - `35°50′40.9″N, 103°27′7.5″E`
  - `40°42′46″N, 74°0′21″W`
  - `51°30′26″N, 0°7′39″W`

**Examples**:

| Location |
|----------|
| 100364199 |
| 40.7589, -73.9851 |
| 35°50′40.9″N, 103°27′7.5″E |
| 100089589 |
| 100364199;100089589 |
| 40.7589, -73.9851;35°50′40.9″N, 103°27′7.5″E |

---

### 13. Location Grid Fields

**Field Type**: `location_grid`

**Description**: Specific location grid IDs in the system

**Accepted Values**: Numeric grid IDs only

**Examples**:

| Location Grid |
|---------------|
| 100364199 |
| 100089589 |
| 100254781 |

---

### 14. Location Meta Fields

**Field Type**: `location_meta`

**Description**: Enhanced location with geocoding support and address processing

**Accepted Values**:
- **Grid ID**: `100364199` (numeric location grid ID)
- **Decimal coordinates**: `"40.7128,-74.0060"` (latitude,longitude)
- **DMS coordinates**: `"35°50′40.9″N, 103°27′7.5″E"` (degrees, minutes, seconds)
- **Address strings**: `"123 Main St, Springfield, IL"` (requires geocoding service)
- **Location names**: `"Springfield, Illinois"` (requires geocoding service)
- **Multiple locations**: `"Paris, France; Berlin, Germany"` (semicolon-separated)

**Coordinate Formats**:

**Decimal Degrees**:
- Format: `latitude,longitude`
- Examples: `40.7128,-74.0060`, `35.8447,103.4521`

**DMS (Degrees, Minutes, Seconds)**:
- Format: `DD°MM′SS.S″N/S, DDD°MM′SS.S″E/W`
- Direction indicators (N/S/E/W) are **required**
- Examples: `35°50′40.9″N, 103°27′7.5″E`, `40°42′46″N, 74°0′21″W`

**Multiple Locations**:
- Separate with semicolons: `"France; Germany"`, `"40.7128,-74.0060; 35°50′40.9″N, 103°27′7.5″E"`

**Geocoding**: Can automatically convert addresses to coordinates if geocoding service is configured

**Examples**:

| Location Meta |
|---------------|
| 100364199 |
| 40.7589, -73.9851 |
| 35°50′40.9″N, 103°27′7.5″E |
| 123 Main Street, Springfield, IL 62701 |
| Times Square, New York, NY |
| Paris, France; Berlin, Germany |
| Central Park, Manhattan |
| 100364199;40.7589, -73.9851 |
| 123 Main St, Springfield; Times Square, NYC |

---

## Special Formatting Rules

### Multi-Value Fields
Use semicolons (`;`) to separate multiple values:

| Phone Numbers | Sources | Groups |
|---------------|---------|--------|
| 555-123-4567;555-987-6543 | web;referral | Group A;Group B |
| 555-111-2222 | facebook;event | Youth Group |

### Empty Values
Leave cells empty for no data:

| Name | Phone | Email |
|------|-------|-------|
| John Smith |  | john@example.com |
| Jane Doe | 555-123-4567 |  |
| Bob Wilson | 555-987-6543 | bob@example.com |

### Text with Commas
Use quotes around text containing commas:

| Address | Notes |
|---------|-------|
| 123 Main St, Springfield, IL | Met at coffee shop, very interested |
| 456 Oak Ave, Chicago, IL | Referred by John, wants to join small group |

---

## Import Options

### Default Values
Set default values that apply to all imported records:
- **Source**: Default source for tracking (e.g., 'csv_import', 'data_migration')
- **Assigned To**: Default user assignment for all imported records

### Duplicate Checking
Configure how the system handles potential duplicate records:
- **Enable Duplicate Checking**: Check for existing records with matching values
- **Merge Fields**: Choose which fields to use for duplicate detection (typically phone or email)
- **Behavior**: When duplicates are found, the system updates existing records instead of creating new ones

### Geocoding Services
Configure automatic address geocoding for location fields:
- **Google Maps**: Requires Google Maps API key configuration
- **Mapbox**: Requires Mapbox API token configuration  
- **None**: Import addresses without automatic geocoding

**Note**: Geocoding services must be configured in Disciple Tools settings before they become available for import.

---

## Common Field Examples

### Basic Contact Import

| Name | Email | Phone | Status | Source |
|------|-------|-------|--------|--------|
| John Smith | john@example.com | 555-123-4567 | new | web |
| Mary Johnson | mary@example.com | 555-987-6543 | active | referral |
| Bob Wilson | bob@example.com | 555-111-2222 | new | facebook |

### Comprehensive Contact Import

| Name | Email | Phone | Status | Type | Sources | Milestones | Assigned To | Tags | Location |
|------|-------|-------|--------|------|---------|------------|-------------|------|----------|
| John Smith | john@example.com | 555-123-4567 | active | personal | web;referral | milestone_has_bible;milestone_reading_bible | john_doe | VIP;follow-up | 123 Main St, Springfield |
| Mary Johnson | mary@example.com | 555-987-6543 | new | personal | facebook | milestone_belief | mary_admin | new-believer;youth | Downtown Community Center |

### Basic Group Import

| Name | Status | Group Type | Start Date | Location |
|------|--------|------------|------------|----------|
| Downtown Bible Study | active | group | 2024-01-15 | Community Center |
| Youth Group | active | church | 2024-02-01 | First Baptist Church |
| Prayer Circle | active | group | 2024-03-10 | Methodist Church |

### Comprehensive Group Import

| Name | Status | Group Type | Health Metrics | Members | Leaders | Start Date | End Date | Location |
|------|--------|------------|---------------|---------|---------|------------|----------|----------|
| Downtown Bible Study | active | group | church_bible;church_prayer;church_fellowship | John Smith;Mary Johnson | Pastor Mike | 2024-01-15 |  | Community Center |
| Youth Group | active | church | church_giving;church_baptism;church_leaders | Youth Member 1;Youth Member 2 | Youth Pastor | 2024-02-01 |  | First Baptist Church |

---

## Troubleshooting

### Common Errors

**"Invalid number"**: Ensure numeric fields contain only numbers
**"Invalid date format"**: Use YYYY-MM-DD format or clear date formats
**"Invalid email address"**: Check email format (must contain @ and valid domain)
**"Invalid option for field"**: Check that dropdown values match available options
**"Field does not exist"**: Verify field exists for the selected post type
**"Connection not found"**: Ensure connected records exist or use valid IDs
**"Connection skipped due to duplicate names"**: Multiple records exist with the same name - use record IDs instead

### Connection Field Behavior

When importing connection fields, the system processes each connection value as follows:

1. **Numeric values** (e.g., `123`): Treated as record IDs - will link directly to the record with that ID
2. **Text values** (e.g., `"John Smith"`): Searched by record name/title
   - **Single match found**: Links to that existing record
   - **No match found**: Creates a new record with that name
   - **Multiple matches found**: **Skips that specific connection** (does not fail the entire row)

**Example**: If your CSV has `"John Smith;Mary Johnson;456"` in a connection field:
- `John Smith`: If 1 record found → connects to it; if 0 found → creates new; if 2+ found → skips
- `Mary Johnson`: Processed independently with same logic
- `456`: Links directly to record ID 456 (if it exists)

The import will continue processing other connections and other fields even if some connections are skipped.

### Best Practices

1. **Start Small**: Test with a few records first
2. **Use Examples**: Download and modify the provided example CSV files
3. **Check Field Options**: Review available options for dropdown fields during mapping
4. **Validate Data**: Clean your data before import
5. **Backup First**: Always backup your data before large imports
6. **Review Mapping**: Carefully review automatic field mappings before proceeding
7. **Check Duplicates**: Enable duplicate checking for communication fields to avoid duplicate records
8. **Test Geocoding**: If using location fields, test geocoding with a few addresses first
9. **Use Record IDs for Connections**: When connecting to existing records, use numeric IDs instead of names to avoid issues with duplicate names

### System Limitations

- **File Size**: Maximum 10MB per CSV file
- **Memory**: Large imports may require adequate server memory
- **Timeout**: Very large imports may be processed in batches to avoid timeouts
- **Permissions**: Requires `manage_dt` capability to access import functionality
- **Geocoding**: Requires API keys for Google Maps or Mapbox services
- **Records**: No hard limit on number of records, but performance depends on server resources

---

## Field Mapping

During the import process, you'll map your CSV columns to Disciple Tools fields:

1. **Automatic Detection**: The system attempts to match column headers to field names
2. **Manual Mapping**: You can override automatic suggestions
3. **Value Mapping**: For dropdown fields, map your CSV values to valid options
4. **Skip Columns**: Choose to skip columns you don't want to import

---

## Example CSV Files

The import tool provides four downloadable example files:

### Basic Templates
- **`example_contacts.csv`**: Simple contact import template with essential fields
- **`example_groups.csv`**: Simple group import template with basic fields

### Comprehensive Templates  
- **`example_contacts_comprehensive.csv`**: Complete contact template with all available field types, milestone options, and relationship examples
- **`example_groups_comprehensive.csv`**: Complete group template with all field types, health metrics, and member/leader relationships

**Access**: Download these files from the Import interface sidebar or from the admin settings page.

**Usage Tips**:
- Start with basic templates for simple imports
- Use comprehensive templates to see all available field options
- Modify the templates by removing unnecessary columns for your specific needs
- Keep the header row and follow the same formatting patterns 