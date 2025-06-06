# DT Import Frontend Assets

This directory contains the frontend assets for the DT Import feature.

## Files Overview

### JavaScript Files

#### `js/dt-import.js`
- **Main application logic** for the step-by-step import workflow
- Handles REST API communication with the backend
- Manages the 4-step import process:
  1. Post type selection
  2. CSV file upload with drag & drop
  3. Field mapping with intelligent suggestions
  4. Preview and import execution
- Features:
  - Real-time progress tracking
  - File validation and processing
  - Error handling and user feedback
  - Session-based workflow management

#### `js/dt-import-modals.js`
- **Modal dialog handling** for advanced features
- Custom field creation modal with field type support
- Value mapping modal for dropdown/multi-select fields
- Features:
  - Dynamic form generation
  - Field option management
  - Value mapping interface
  - Modal state management

### CSS Files

#### `css/dt-import.css`
- **Complete styling** for the import interface
- WordPress admin design consistency
- Responsive design for mobile compatibility
- Features:
  - Step-by-step progress indicator
  - Card-based layout for field mapping
  - Professional file upload interface
  - Data preview tables
  - Modal dialog styling
  - Loading states and animations

## Technical Implementation

### Architecture
- **Vanilla JavaScript ES6+** with jQuery for DOM manipulation
- **Class-based structure** for maintainability
- **Modular design** with separate modal handling
- **REST API integration** using native fetch()
- **WordPress admin styling** compatibility

### Key Features

#### File Upload
- Drag and drop support
- File type validation (CSV only)
- Size limit enforcement (10MB)
- Progress feedback during upload

#### Field Mapping
- Intelligent column detection with confidence scoring
- Support for all DT field types
- Real-time field creation capabilities
- Value mapping for dropdown fields
- Sample data preview

#### Import Processing
- Progress polling for long-running imports
- Real-time status updates
- Comprehensive error reporting
- Result statistics and summaries

#### User Experience
- Step-by-step guided workflow
- Visual progress indicators
- Contextual help and guidelines
- Responsive design for all devices
- Accessibility considerations

### Integration Points

#### WordPress Admin
- Integrates with DT Settings menu structure
- Uses WordPress admin CSS patterns
- Follows WordPress JavaScript standards
- Proper nonce handling for security

#### DT Framework
- Uses DT Web Components where available
- Integrates with DT field system
- Respects DT permissions model
- Follows DT REST API patterns

#### Browser Support
- Modern browsers (Chrome 80+, Firefox 75+, Safari 13+)
- Progressive enhancement approach
- Graceful degradation for older browsers

### Performance Considerations

#### Optimization
- Efficient DOM manipulation
- Minimal dependencies
- Lazy loading of complex components
- Chunked processing for large files

#### Memory Management
- Proper event listener cleanup
- Session-based data storage
- Automatic file cleanup after processing

## Usage

The frontend assets are automatically enqueued when accessing the DT Import tab in the WordPress admin. The JavaScript files initialize automatically and provide the complete import workflow interface.

### Dependencies
- jQuery (included with WordPress)
- DT Web Components (optional, loaded if available)
- WordPress REST API
- Modern browser with ES6 support

### Configuration
Configuration is provided via `wp_localize_script()` in the admin tab file:
- REST API endpoints
- Security nonces
- Available post types
- Translations
- File size limits

## Customization

The CSS can be customized to match specific branding requirements while maintaining the core functionality. The JavaScript is modular and can be extended for additional features.

For advanced customization, refer to the main implementation guide and project specification documents. 