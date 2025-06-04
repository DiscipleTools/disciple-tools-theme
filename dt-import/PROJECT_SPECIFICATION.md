Help me create the project specifications and implementation process for this feature:

I'm working in the disciple-tools-theme folder in a new folder called dt-import. In this feature that is located in the WordPress admin, we will be building an import interface for CSV files. 
The DT import feature is accessible through the settings DT WordPress admin menu item. 
The DT import will ask the user whether they are working with contacts, groups, or any of the other post types. It will get and list out the post types using the DT post types API. 
Then the user will be able to upload their CSV file. 
When the user has uploaded their CSV file, they will be able to map each column to an existing DT field for the post type 
The DT import will try to guess which field the column refers to by matching the column name to the existing field names. 
If a corresponding field cannot be identified, the user is given the option for each column to choose which corresponding DT field it corresponds to or if they do not want to import the column. 
If a field does not exist, they also have the option to create the field right there in the mapping section. Field options are decumented here @dt-posts-field-settings.md 
The mapping UI is set up horizontally with each column of the CSV being a column in the UI. 

Text, text area, date, numer, boolean fields are fairly straightforward. forward, the importing is one to one. 

Dates are converted to the year month day format. 

key_select Fields, the mapping gives the user the ability to match each value in the CSV to a corresponding value in the key select field. 

multi_select fields, the mapping gives the user the ability to match each value in the CSV to a corresponding value in the multi_select field. 

Fields with multiple values are semicolon separated. 

tags and communication_channels fields, the values are sererated and imported 

Connection fields, the values can either be the ID of the record it is connected to or the name of the field. If a name is provided, we need to do a search across the existing records of the selected selected connection field to make sure that that we can find the corresponding records or if we need to create corresponding records. 

User select fields can accept the user ID or the name of the user or the email address of the user. A search needs to be done to find the user. If a user doesn't exist, we do not create new users. 

If a, if the location field is selected, we need to make sure that the inputted value you is a location grid or a latitude and longitude point. or if it is an address. If it is a grid ID, that is the easiest and can be saved directly. correctly. If it is a latitude and latitude or a address, then we need to geocode the location.

## Uploading
Once all the fields are mapped we can upload the records.
Either the upoading happens by sending the csv to the server and the server runs a process on the csv file to import all of the files. the records. The downside of this is that if this is an error, the user isn't able to try again right away. 
The upload could also happen in the browser by using the API and creating each record one by one. 
Let's handle uploading in phase two of the development process. 

Helpful API documentation and Disciple.Tools structure can be found in the Disciple Tools theme docs folder. 




# DT Import Feature - Project Specification

## 1. Project Overview

### 1.1 Purpose
The DT Import feature is a comprehensive CSV import system that allows administrators to import contacts, groups, and other post types into Disciple.Tools through an intuitive WordPress admin interface.

### 1.2 Scope
- **Primary Goal**: Enable bulk data import from CSV files into any DT post type
- **Secondary Goals**: 
  - Intelligent field mapping with auto-detection
  - Support for creating new fields during import
  - Comprehensive data validation and error handling
  - User-friendly interface with step-by-step workflow

### 1.3 Key Features
- Multi-post type support (contacts, groups, custom post types)
- Intelligent column-to-field mapping with manual override
- Field creation capabilities during mapping process
- Advanced data transformation for various field types
- Real-time preview before import execution
- Comprehensive error reporting and validation

### 1.4 Access & Integration
- **Location**: WordPress Admin → Settings (D.T) → Import tab
- **Permission Required**: `manage_dt` capability
- **Integration Point**: Extends existing DT Settings menu structure

## 2. Technical Requirements

### 2.1 System Requirements
- WordPress 5.0+
- PHP 7.4+
- Disciple.Tools theme framework
- MySQL 5.7+ / MariaDB 10.2+

### 2.2 Dependencies
- DT_Posts API for post type management
- DT field settings system
- WordPress file upload system
- DT permission framework

### 2.3 Browser Support
- Chrome 80+
- Firefox 75+
- Safari 13+
- Edge 80+

## 3. Functional Requirements

### 3.1 Core Workflow
1. **Post Type Selection**: Admin selects target post type for import
2. **CSV Upload**: Upload CSV file with delimiter selection
3. **Field Mapping**: Map CSV columns to DT fields with intelligent suggestions
4. **Preview**: Review mapped data before import
5. **Import Execution**: Process import with progress tracking
6. **Results Summary**: Display success/failure statistics and error details

### 3.2 Field Mapping Requirements

#### 3.2.1 Automatic Field Detection
- Match column names to existing field names (case-insensitive)
- Support common field aliases (e.g., "phone" → "contact_phone")
- Calculate confidence scores for mapping suggestions
- Handle partial name matches

#### 3.2.2 Manual Mapping Override
- Allow users to override automatic suggestions
- Provide dropdown of all available fields for each column
- Option to skip columns (do not import)
- Real-time preview of sample data

#### 3.2.3 Field Creation
- Create new fields directly from mapping interface
- Support all DT field types
- Immediate availability of newly created fields
- Proper field validation and configuration

### 3.3 Data Processing Requirements

#### 3.3.1 Field Type Support

| Field Type | Processing Logic | Special Requirements |
|------------|------------------|---------------------|
| `text` | Direct assignment | Sanitization, trim whitespace |
| `textarea` | Direct assignment | Preserve line breaks |
| `date` | Format conversion | Support multiple input formats, convert to Y-m-d |
| `number` | Numeric conversion | Validate numeric input, handle decimals |
| `boolean` | Boolean conversion | Handle true/false, 1/0, yes/no variations |
| `key_select` | Option mapping | Map CSV values to field options |
| `multi_select` | Split and map | Semicolon-separated values by default |
| `tags` | Split and create | Auto-create new tags as needed |
| `communication_channel` | Split and validate | Format validation for emails/phones |
| `connection` | ID or name lookup | Search existing records, optional creation |
| `user_select` | User lookup | Search by ID, username, or display name |
| `location` | Geocoding | Support grid IDs, addresses, lat/lng |

#### 3.3.2 Data Validation
- Pre-import validation of all data
- Required field validation
- Format validation for specific field types
- Foreign key existence checks
- Data type validation

#### 3.3.3 Error Handling
- Row-level error collection
- Detailed error messages with line numbers
- Graceful failure handling (continue processing other rows)
- Comprehensive error reporting

### 3.4 User Interface Requirements

#### 3.4.1 Step-by-Step Interface
- Clear navigation between steps
- Progress indication
- Ability to go back and modify previous steps
- Responsive design for various screen sizes

#### 3.4.2 Field Mapping Interface
- Horizontal column layout showing CSV columns
- Sample data display for each column
- Dropdown field selection with search
- Field-specific configuration options
- Visual confidence indicators for automatic suggestions

#### 3.4.3 Preview Interface
- Tabular display of mapped data
- Show original and processed values
- Highlight potential issues
- Summary statistics before import

## 4. Technical Architecture

### 4.1 File Structure
```
wp-content/themes/disciple-tools-theme/dt-import/
├── dt-import.php                    # Main plugin file
├── admin/
│   ├── dt-import-admin-tab.php     # Admin tab integration
│   ├── dt-import-mapping.php       # Field mapping logic
│   └── dt-import-processor.php     # Import processing
├── includes/
│   ├── dt-import-field-handlers.php # Field-specific processors
│   ├── dt-import-utilities.php     # Utility functions
│   └── dt-import-validators.php    # Data validation
├── assets/
│   ├── js/
│   │   └── dt-import.js           # Frontend JavaScript
│   └── css/
│       └── dt-import.css          # Styling
├── templates/
│   ├── step-1-select-type.php     # Post type selection
│   ├── step-2-upload-csv.php      # File upload
│   ├── step-3-mapping.php         # Field mapping
│   └── step-4-preview.php         # Import preview
└── ajax/
    └── dt-import-ajax.php          # AJAX handlers
```

### 4.2 Class Architecture

#### 4.2.1 Core Classes
- `DT_Import`: Main plugin class and initialization
- `DT_Import_Admin_Tab`: Admin interface integration
- `DT_Import_Mapping`: Field mapping logic and suggestions
- `DT_Import_Processor`: Import execution and data processing
- `DT_Import_Field_Handlers`: Field-specific processing logic
- `DT_Import_Utilities`: Shared utility functions
- `DT_Import_Validators`: Data validation functions

#### 4.2.2 Integration Points
- Extends `Disciple_Tools_Abstract_Menu_Base` for admin integration
- Uses `DT_Posts` API for all post operations
- Integrates with DT field customization system
- Follows DT permission and security patterns

### 4.3 Data Flow

1. **File Upload**: CSV uploaded and temporarily stored
2. **Parsing**: CSV parsed into arrays with delimiter detection
3. **Analysis**: Column headers analyzed for field suggestions
4. **Mapping**: User maps columns to fields with configuration
5. **Validation**: Data validated against field requirements
6. **Processing**: Records created using DT_Posts API
7. **Reporting**: Results compiled and displayed to user

## 5. Security Requirements

### 5.1 Access Control
- Enforce `manage_dt` capability for all operations
- Validate user permissions for specific post types
- Secure session handling for multi-step process

### 5.2 File Upload Security
- Restrict uploads to CSV files only
- Validate file size and structure
- Sanitize all file contents
- Store uploads in secure, temporary location
- Clean up uploaded files after processing

### 5.3 Data Security
- Sanitize all user inputs
- Use WordPress nonces for CSRF protection
- Validate all database operations
- Respect DT field-level permissions
- Audit trail for import operations

### 5.4 Input Validation
- Server-side validation of all form data
- SQL injection prevention
- XSS protection for all outputs
- File type verification beyond extension

## 6. Performance Requirements

### 6.1 File Size Limits
- Support CSV files up to 10MB
- Handle up to 10,000 records per import
- Implement chunked processing for large files
- Memory-efficient parsing algorithms

### 6.2 Processing Performance
- Import processing under 30 seconds for 1,000 records
- Progress indicators for long-running imports
- Optimized database operations
- Efficient field lookup mechanisms

### 6.3 User Experience
- Page load times under 3 seconds
- Responsive interface during processing
- Real-time feedback for user actions
- Graceful handling of timeouts

## 7. Quality Assurance

### 7.1 Testing Requirements

#### 7.1.1 Unit Testing
- Field mapping algorithm accuracy
- Data transformation correctness
- Validation logic completeness
- Error handling robustness

#### 7.1.2 Integration Testing
- End-to-end import workflows
- Various CSV formats and encodings
- Field creation and customization
- Multi-post type scenarios
- Large file processing

#### 7.1.3 User Acceptance Testing
- Admin user workflow validation
- Error scenario handling
- Cross-browser compatibility
- Mobile responsiveness

### 7.2 Code Quality Standards
- Follow WordPress coding standards
- PSR-4 autoloading compliance
- Comprehensive inline documentation
- Security best practices adherence

## 8. Deployment Requirements

### 8.1 Installation
- Automatic activation with DT theme
- No additional database modifications required
- Backward compatibility with existing DT installations

### 8.2 Configuration
- No initial configuration required
- Inherits DT permission settings
- Uses existing field customization system

### 8.3 Maintenance
- Automatic cleanup of temporary files
- Error log integration
- Performance monitoring capabilities

## 9. Documentation Requirements

### 9.1 Technical Documentation
- Complete API documentation
- Code architecture overview
- Security implementation details
- Performance optimization guide

### 9.2 User Documentation
- Step-by-step import guide
- Field mapping examples
- Troubleshooting guide
- Best practices for CSV preparation

### 9.3 Administrative Documentation
- Installation and configuration guide
- Security considerations
- Performance tuning recommendations
- Backup and recovery procedures

## 10. Success Criteria

### 10.1 Functional Success
- Successfully import 95% of well-formatted CSV data
- Support all standard DT field types
- Handle edge cases gracefully
- Provide clear error messages for failures

### 10.2 Performance Success
- Process 1,000 records in under 30 seconds
- Memory usage under 256MB for typical imports
- No significant impact on site performance during import

### 10.3 User Experience Success
- Intuitive workflow requiring minimal training
- Clear progress indication throughout process
- Comprehensive error reporting and resolution guidance
- Successful completion by non-technical administrators

## 11. Future Enhancements

### 11.1 Phase 2 Features
- Excel file support (.xlsx)
- Automated duplicate detection and merging
- Scheduled/recurring imports
- Import templates for common data sources

### 11.2 Advanced Features
- REST API for programmatic imports
- Webhook integration for real-time data sync
- Advanced field transformation rules
- Bulk update capabilities for existing records

This specification serves as the comprehensive blueprint for the DT Import feature development, ensuring all requirements, constraints, and success criteria are clearly defined and achievable. 