# Disciple Tools Frontend Admin Area Specification

## Overview
This specification outlines the development of a new frontend admin area for the Disciple Tools theme, accessible at `/admin`. The admin area will provide a modern, intuitive interface for managing various aspects of the Disciple Tools system.

## URL Structure
- **Primary URL**: `/admin`
- **Section URLs**: `/admin/{section}`
- **Subsection URLs**: `/admin/{section}/{subsection}`

## Layout Structure

### Main Layout
```
┌─────────────────────────────────────────────┐
│                Header                       │
├─────────────┬───────────────────────────────┤
│             │                               │
│   Left      │         Main Content          │
│   Sidebar   │           Area                │
│   Menu      │                               │
│             │                               │
│             │                               │
└─────────────┴───────────────────────────────┘
```

### Left Sidebar Menu Structure
The left sidebar will contain a collapsible navigation menu with the following sections:

#### 1. **Mapping** (`/admin/mapping`)
   - **Overview** (`/admin/mapping/overview`) - Dashboard with mapping statistics
   - **Location Grid** (`/admin/mapping/location-grid`) - Manage location grid data
   - **Geocoding** (`/admin/mapping/geocoding`) - Geocoding settings and tools
   - **Map Layers** (`/admin/mapping/layers`) - Configure map layers and styles

#### 2. **Settings & Configuration** (`/admin/settings`)
   - **General** (`/admin/settings/general`) - Site-wide general settings
   - **Custom Fields** (`/admin/settings/custom-fields`) - Manage custom fields
   - **Custom Lists** (`/admin/settings/custom-lists`) - Manage custom list options
   - **Custom Tiles** (`/admin/settings/custom-tiles`) - Configure custom tiles
   - **Roles & Permissions** (`/admin/settings/roles`) - User roles management
   - **Security** (`/admin/settings/security`) - Security configurations
   - **Site Links** (`/admin/settings/site-links`) - Inter-site connectivity
   - **Translations** (`/admin/settings/translations`) - Custom translations
   - **Workflows** (`/admin/settings/workflows`) - Automated workflows

#### 3. **Plugins** (`/admin/plugins`)
   - **Installed Plugins** (`/admin/plugins/installed`) - List of installed plugins
   - **Available Extensions** (`/admin/plugins/available`) - Browse available extensions
   - **Plugin Settings** (`/admin/plugins/settings`) - Configure plugin settings
   - **Updates** (`/admin/plugins/updates`) - Plugin update management

#### 4. **System Tools** (`/admin/tools`)
   - **Data Management** (`/admin/tools/data`) - Import/Export tools
   - **System Logs** (`/admin/tools/logs`) - View system logs
   - **Background Jobs** (`/admin/tools/jobs`) - Monitor background processes
   - **Database Utilities** (`/admin/tools/database`) - Database maintenance
   - **Scripts** (`/admin/tools/scripts`) - Administrative scripts

## File Structure

### Template Files
```
template-admin.php                    # Main admin template (HTML structure only)
dt-admin/
├── admin-functions.php              # Core admin functions & REST API registration
├── admin-assets/
│   ├── css/
│   │   ├── admin.css               # Main admin styles
│   │   └── admin.min.css
│   ├── js/
│   │   ├── admin.js                # Main admin JS + REST API client
│   │   ├── admin.min.js
│   │   ├── api-client.js           # REST API client utilities
│   │   └── sections/
│   │       ├── mapping.js          # Mapping section JS (API-driven)
│   │       ├── settings.js         # Settings section JS (API-driven)
│   │       ├── plugins.js          # Plugins section JS (API-driven)
│   │       └── tools.js            # Tools section JS (API-driven)
│   └── images/
│       ├── icons/                  # Section icons
│       └── admin-logo.svg
├── api/                             # REST API Controllers
│   ├── class-dt-admin-rest-base.php
│   ├── mapping/
│   │   ├── class-mapping-rest-controller.php
│   │   ├── class-location-grid-rest-controller.php
│   │   ├── class-geocoding-rest-controller.php
│   │   └── class-map-layers-rest-controller.php
│   ├── settings/
│   │   ├── class-settings-rest-controller.php
│   │   ├── class-custom-fields-rest-controller.php
│   │   ├── class-custom-lists-rest-controller.php
│   │   ├── class-roles-rest-controller.php
│   │   └── class-security-rest-controller.php
│   ├── plugins/
│   │   ├── class-plugins-rest-controller.php
│   │   └── class-extensions-rest-controller.php
│   └── tools/
│       ├── class-tools-rest-controller.php
│       ├── class-logs-rest-controller.php
│       └── class-jobs-rest-controller.php
└── partials/
    ├── header.php                   # Admin header (static HTML)
    ├── sidebar.php                  # Left sidebar menu (static HTML)
    ├── breadcrumbs.php             # Breadcrumb navigation (static HTML)
    └── footer.php                   # Admin footer (static HTML)
```

## Technical Implementation

### URL Routing
Following the existing DT pattern, add to `functions.php`:

```php
// In dt_url_loader() function
$template_for_url['admin'] = 'template-admin.php';
$template_for_url['admin/(.+)'] = 'template-admin.php'; // Catch all admin routes
```

### Main Template Structure
The `template-admin.php` will:
1. Check user permissions (`manage_dt` capability)
2. Parse the URL to determine active section/subsection
3. Render static HTML structure (header, sidebar, main content area)
4. Enqueue section-specific JavaScript for API data loading
5. **NO direct data processing** - all data loaded via REST API in JavaScript

### Menu System
Extend the existing menu system by adding admin navigation:

```php
// Add to dt_default_menu_array() filter
'admin_area' => [
    'label' => __( 'Admin', 'disciple_tools' ),
    'link' => site_url( '/admin/' ),
    'icon' => get_template_directory_uri() . '/dt-admin/admin-assets/images/admin-icon.svg',
    'hidden' => !current_user_can( 'manage_dt' ),
]
```

### REST API Controllers
Each section will have REST API controller classes (backend only):

```php
abstract class DT_Admin_REST_Controller extends WP_REST_Controller {
    protected $namespace = 'dt-admin/v1';
    protected $rest_base;
    
    abstract public function register_routes();
    abstract public function get_items($request);
    abstract public function create_item($request);
    abstract public function update_item($request);
    abstract public function delete_item($request);
    abstract public function get_item_permissions_check($request);
}
```

### Frontend Section Classes
JavaScript classes for handling each section:

```javascript
class AdminSectionBase {
    constructor(sectionSlug) {
        this.sectionSlug = sectionSlug;
        this.apiClient = new DTAdminAPIClient();
    }
    
    async loadData() {
        // Load section data via REST API
    }
    
    render() {
        // Render UI with loaded data
    }
    
    handleEvents() {
        // Handle user interactions
    }
}
```

### Security & Permissions
- All admin pages require `manage_dt` capability
- Additional subsection-specific permission checks
- CSRF protection for all forms
- Input sanitization and validation

### Responsive Design
- Mobile-first approach
- Collapsible sidebar on smaller screens
- Touch-friendly interface elements
- Adaptive layouts for different screen sizes

### JavaScript Architecture
- **API-Driven Frontend**: All data operations through REST API calls
- Modular JavaScript structure with dedicated API client classes
- Section-specific JS files loaded as needed
- Common admin utilities and REST API client in main admin.js
- Promise-based API calls with proper error handling
- Client-side state management for UI interactions
- Loading states and skeleton screens during API calls

### Styling Guidelines
- Consistent with existing DT design system
- CSS Grid and Flexbox for layouts
- CSS Custom Properties for theming
- Accessible color schemes and typography

## Data Handling

### REST API First Architecture
**CRITICAL**: All data retrieval and display operations must go through the REST API. No direct database queries or PHP data processing should occur in the frontend templates.

### REST API Endpoints
- **Primary Admin API**: `/wp-json/dt-admin/v1/{section}/{action}`
- **Settings API**: `/wp-json/dt-admin/v1/settings/{subsection}`
- **Mapping API**: `/wp-json/dt-admin/v1/mapping/{subsection}`
- **Plugins API**: `/wp-json/dt-admin/v1/plugins/{action}`
- **Tools API**: `/wp-json/dt-admin/v1/tools/{action}`

#### API Response Format
```json
{
    "success": true,
    "data": {
        // Response data
    },
    "message": "Operation completed successfully",
    "errors": []
}
```

#### API Security
- JWT authentication for all endpoints
- Proper nonce verification
- Role-based access control per endpoint
- Rate limiting on sensitive operations

### Frontend Data Flow
1. **Template Loading**: PHP templates only render HTML structure and enqueue scripts
2. **JavaScript Initialization**: Section-specific JS loads and requests data via REST API
3. **Dynamic Rendering**: All content populated via JavaScript from API responses
4. **Real-time Updates**: WebSocket or polling for live data updates

### Settings Storage
- All settings stored via REST API endpoints
- WordPress options API used on backend only
- Group related settings logically
- Provide default values through API
- Client-side caching of frequently accessed settings

### Error Handling
- Graceful error messages
- Debug logging for development
- User-friendly error displays
- Rollback capabilities for critical changes

## Accessibility Features
- ARIA labels and landmarks
- Keyboard navigation support
- Screen reader compatibility
- High contrast mode support
- Focus management

## Performance Considerations
- Lazy loading of section content
- Minimal initial page load
- Asset optimization and minification
- Caching strategies for settings
- Database query optimization

## Integration Points

### Existing DT Systems
- Leverage existing post type management
- Use DT notification system
- Integrate with DT logging
- Utilize DT user management

### Plugin Compatibility
- Hook system for plugin extensions
- Standardized section registration
- Plugin setting management
- Update notification integration

## Development Phases

### Phase 1: Core Infrastructure
1. Create main template file (static HTML structure only)
2. Implement URL routing  
3. Build REST API base controller class
4. Develop API client JavaScript utilities
5. Create sidebar navigation (static HTML)
6. Create basic styling framework

### Phase 2: Settings & Configuration
1. General settings section
2. Custom fields management
3. User roles and permissions
4. Security settings

### Phase 3: Mapping Features
1. Mapping overview dashboard
2. Location grid management
3. Geocoding tools
4. Map layer configuration

### Phase 4: Plugins & Tools
1. Plugin management interface
2. System tools and utilities
3. Data import/export
4. System monitoring

### Phase 5: Polish & Optimization
1. Performance optimization
2. Accessibility improvements
3. Mobile responsiveness
4. Documentation and help system

## Testing Strategy
- Unit tests for controller classes
- Integration tests for AJAX endpoints
- User acceptance testing
- Cross-browser compatibility testing
- Accessibility testing
- Performance testing

## Documentation Requirements
- Developer documentation for extending sections
- User documentation for admin features
- API documentation for AJAX endpoints
- Code commenting and inline documentation 