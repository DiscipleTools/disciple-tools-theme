# CSV Import Documentation

## Overview

The CSV Import feature allows you to bulk import data into Disciple Tools from CSV (Comma-Separated Values) files. This tool supports importing both **Contacts** and **Groups** with comprehensive field mapping and validation.

## Getting Started

### Access the Import Tool

1. Navigate to **Admin** → **Settings** → **Import** tab
2. You must have `manage_dt` permissions to access the import functionality

### Import Process

The import process consists of 4 steps:

1. **Select Record Type** - Choose whether to import Contacts or Groups
2. **Upload CSV** - Upload your CSV file (max 10MB)
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

**Description**: Geographic location information

**Accepted Values**: 
- **Address strings**: `"123 Main St, Springfield, IL"`
- **Decimal coordinates**: `"40.7128,-74.0060"` (latitude,longitude)
- **DMS coordinates**: `"35°50′40.9″N, 103°27′7.5″E"` (degrees, minutes, seconds)
- **Location names**: `"Springfield, Illinois"`

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
| 123 Main Street, Springfield, IL 62701 |
| Downtown Community Center |
| 40.7589, -73.9851 |
| 35°50′40.9″N, 103°27′7.5″E |
| First Baptist Church |

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

**Field Type**: `location_grid_meta`

**Description**: Enhanced location with geocoding support

**Accepted Values**:
- **Grid ID**: `100364199`
- **Decimal coordinates**: `"40.7128,-74.0060"`
- **DMS coordinates**: `"35°50′40.9″N, 103°27′7.5″E"`
- **Address**: `"123 Main St, Springfield, IL"`
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
| Times Square, New York, NY |
| Paris, France; Berlin, Germany |
| Central Park, Manhattan |

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
- **Source**: Default source for tracking
- **Assigned To**: Default user assignment
- **Status**: Default status for new records

### Geocoding Services
If enabled, addresses can be automatically geocoded:
- **Google Maps**: Requires API key
- **Mapbox**: Requires API token
- **None**: No automatic geocoding

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

### Best Practices

1. **Start Small**: Test with a few records first
2. **Use Examples**: Download and modify the provided example CSV files
3. **Check Field Options**: Review available options for dropdown fields during mapping
4. **Validate Data**: Clean your data before import
5. **Backup First**: Always backup your data before large imports

---

## Field Mapping

During the import process, you'll map your CSV columns to Disciple Tools fields:

1. **Automatic Detection**: The system attempts to match column headers to field names
2. **Manual Mapping**: You can override automatic suggestions
3. **Value Mapping**: For dropdown fields, map your CSV values to valid options
4. **Skip Columns**: Choose to skip columns you don't want to import

---

## Example CSV Files

The import tool provides downloadable example files:

- **Basic Contacts CSV**: Simple contact import template
- **Basic Groups CSV**: Simple group import template  
- **Comprehensive Contacts CSV**: All contact fields with examples
- **Comprehensive Groups CSV**: All group fields with examples

Use these as starting points for your own import files. 