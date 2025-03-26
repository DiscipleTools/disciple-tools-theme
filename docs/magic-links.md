# Magic Links Documentation

## Overview

Magic Links are a powerful feature in Disciple Tools that allows you to create secure, shareable URLs that provide access to specific functionality without requiring authentication. They are particularly useful for:

- Creating public-facing forms for contacts to update their information
- Building user portals for accessing assigned contacts
- Setting up landing pages or microsites
- Creating quick surveys or updates linked to specific records
- Sharing maps or statistics with the public

## Types of Magic Links

There are several types of Magic Links available:

1. **Post Type Magic Links**
   - Connected to specific post types (contacts, groups, etc.)
   - Useful for creating forms or views tied to specific records
   - Can be accessed from the record's details page

2. **User Magic Links**
   - Connected to WordPress users
   - Can be used for user-specific portals or applications
   - Often used for user login or profile management

3. **Non-Object Magic Links**
   - Not tied to any specific post type or user
   - Perfect for public pages, landing pages, or general applications
   - Can be used for registration pages, campaign landing pages, or public maps

4. **Map Magic Links**
   - Specialized for displaying maps
   - Includes necessary JavaScript and CSS resources for map functionality
   - Useful for sharing location-based data

## Key Features

### Security
- Magic Links are secure and can be configured to expire
- Access is controlled through unique hashes
- No authentication required for basic access

### Customization
- Customizable templates
- Configurable fields and forms
- Support for multiple languages
- Custom styling and JavaScript capabilities

## Implementation

### Basic Structure

A Magic Link class extends `DT_Magic_Url_Base` and typically includes:

```php
class Your_Magic_Link extends DT_Magic_Url_Base {
    public $page_title = 'Your Page Title';
    public $page_description = 'Your Page Description';
    public $root = 'your_root'; // URL root
    public $type = 'your_type'; // URL type
    public $post_type = 'your_post_type'; // Optional post type
    public $show_bulk_send = false;
    public $show_app_tile = false;
}
```

### Bulk Send Feature

The `show_bulk_send` property enables the ability to send magic links to multiple records at once from the list page. When set to `true`, it adds the magic link as an option in the bulk actions menu when viewing a list of records.

```php
public $show_bulk_send = true; // enables bulk send of magic links from list page
```

### App Tile Feature

The `show_app_tile` property enables the magic link to appear in the "Apps" tile on the record's details page. When set to `true`, the magic link will be available as a sharing option in the record's Apps section.

```php
public $show_app_tile = true; // enables addition to "app" tile sharing features
```


### Required Components

1. **Meta Configuration**
```php
$this->meta = [
    'app_type' => 'magic_link', // Required: Identifies this as a magic link application
    'post_type' => $this->post_type, // Required: The post type this magic link is associated with
    'contacts_only' => false, // Optional: If true, restricts the magic link to only work with contacts
    'supports_create' => true, // Optional: If true, allows creation of new records
    'fields' => [ // Optional: Defines the fields available in the magic link
        [
            'id' => 'name',
            'label' => 'Name'
        ]
    ],
    'fields_refresh' => [ // Optional: Configuration for field label updates
        'enabled' => true,
        'post_type' => 'contacts',
        'ignore_ids' => ['comments']
    ],
    'icon' => 'mdi mdi-stack-exchange', // Optional: Icon to display for the magic link
    'show_in_home_apps' => true // Optional: If true, adds the magic link to the user's home screen apps list
];
```

#### Meta Configuration Properties

- **app_type** (Required)
  - Type: string
  - Value: Should be 'magic_link'
  - Purpose: Identifies this as a magic link application in the system

- **post_type** (Required)
  - Type: string
  - Value: The post type this magic link is associated with
  - Purpose: Determines which type of records the magic link can be used with
  - Examples: 'contacts', 'groups', 'locations'

- **contacts_only** (Optional)
  - Type: boolean
  - Default: false
  - Purpose: Used in setting up Scheduling and auto sending Magic Link. Controls how user assignments are handled in the magic link plugin
  - When true: Provides a lookup field for contacts-only searching
  - When false: Provides a dropdown for user, team, or group selection
  - Use case: Determines the type of assignment interface shown to admin is setting up scheduling of magink link sending.

- **supports_create** (Optional)
  - Type: boolean
  - Default: false
  - Purpose: Enables creation of new records through the magic link
  - Use case: When you want users to be able to create new records using the magic link

- **fields** (Optional)
  - Type: array
  - Purpose: Defines the fields available in the magic link
  - Structure: Array of field objects with 'id' and 'label' properties
  - Use case: When you need to collect or display specific data in the magic link
  - Example:
    ```php
    'fields' => [
        [
            'id' => 'name',
            'label' => 'Name'
        ],
        [
            'id' => 'email',
            'label' => 'Email Address'
        ]
    ]
    ```

- **fields_refresh** (Optional)
  - Type: array
  - Purpose: Configuration for field label updates
  - Properties:
    - `enabled`: boolean - Whether field refresh is enabled
    - `post_type`: string - The post type to refresh fields from
    - `ignore_ids`: array - Field IDs to ignore during refresh
  - Use case: When you want field labels to be dynamically updated from the post type settings

- **icon** (Optional)
  - Type: string
  - Purpose: Defines the icon to display for the magic link
  - Format: CSS class names for the icon
  - Example: 'mdi mdi-stack-exchange'
  - https://pictogrammers.com/library/mdi/ for more options
  - Use case: When you want to provide a visual identifier for the magic link

- **show_in_home_apps** (Optional)
  - Type: boolean
  - Default: false
  - Purpose: Controls whether the magic link appears in the D.T Home Screen apps list
  - Use case: When you want users to have quick access to the magic link from their home screen

2. **REST API Endpoints**
```php
public function add_endpoints() {
    $namespace = $this->root . '/v1';
    register_rest_route(
        $namespace,
        '/' . $this->type,
        [
            [
                'methods' => 'GET',
                'callback' => [ $this, 'endpoint_get' ],
                'permission_callback' => function( WP_REST_Request $request ){
                    $magic = new DT_Magic_URL( $this->root );
                    return $magic->verify_rest_endpoint_permissions_on_post( $request );
                },
            ],
            [
                'methods' => 'POST',
                'callback' => [ $this, 'update_record' ],
                'permission_callback' => function( WP_REST_Request $request ){
                    $magic = new DT_Magic_URL( $this->root );
                    return $magic->verify_rest_endpoint_permissions_on_post( $request );
                },
            ]
        ]
    );
}
```

### REST API Security

The `verify_rest_endpoint_permissions_on_post` function is a critical security component that verifies the authenticity and validity of REST API requests for Magic Links. It's used as the permission callback for all REST endpoints.

It gets and verifies the root, type, and key from the url. It then checks that the key provided matches the saved key on the record (contact, group, etc) or on the user.


This function performs several security checks:

1. **Required Parameters Check**
   - Verifies that all required parameters are present in the request:
     - `meta_key`
     - `public_key`
     - `post_id`
     - `type`
     - `root`

2. **Return Values**
   - Returns `true` if all checks pass, indicating the request is valid and authorized
   - Returns `false` if any check fails, indicating the request is invalid or unauthorized

Use this function  in the `permission_callback` parameter when registering REST routes, ensuring that only valid, authorized requests can access the endpoints.

### Available Functions

1. **Script and Style Management**
   ```php
   public function dt_magic_url_base_allowed_js( $allowed_js ) {
       // Add your custom JavaScript files here
       $allowed_js[] = 'your-custom-script';
       return $allowed_js;
   }

   public function dt_magic_url_base_allowed_css( $allowed_css ) {
       // Add your custom CSS files here
       $allowed_css[] = 'your-custom-style';
       return $allowed_css;
   }
   ```
   These functions control which JavaScript and CSS files are loaded in your Magic Link page. By default, WordPress loads many scripts and styles that may not be needed for your specific Magic Link. These functions allow you to:

   - Limit loaded scripts to only those needed for your Magic Link
   - Remove unnecessary WordPress default scripts and styles
   - Have precise control over which resources are loaded
   - Improve page load performance by reducing HTTP requests

   Default allowed scripts include:
   - jquery
   - jquery-ui
   - lodash
   - lodash-core
   - site-js
   - shared-functions
   - moment
   - datepicker

   Default allowed styles include:
   - jquery-ui-site-css
   - foundation-css
   - site-css
   - datepicker-css

   Common use cases:
   - Adding custom form validation scripts
   - Including third-party libraries (e.g., maps, charts)
   - Loading custom styling for your Magic Link interface
   - Adding interactive features

   Example of adding a custom script:
   ```php
   public function dt_magic_url_base_allowed_js( $allowed_js ) {
       // Add your custom script
       $allowed_js[] = 'magic-link-custom';
       
       // Add any dependencies your script needs
       $allowed_js[] = 'lodash';
       $allowed_js[] = 'jquery';
       
       return $allowed_js;
   }
   ```

   ### Enqueueing Scripts and Styles

   To add new scripts and styles to your Magic Link, you need to:

   1. Hook the enqueue function in the constructor
   2. Enqueue the script/style using WordPress's enqueue functions
   3. Add it to the allowed list using `dt_magic_url_base_allowed_js` or `dt_magic_url_base_allowed_css`

   Example of setting up script enqueuing:
   ```php
   public function __construct() {
       add_action( 'wp_enqueue_scripts', [ $this, 'wp_enqueue_scripts' ] );
   }

   public function wp_enqueue_scripts() {
       // Enqueue local script
       wp_enqueue_script(
           'magic-link-custom', // Handle
           plugin_dir_url( __FILE__ ) . 'js/magic-link-custom.js', // URL
           ['jquery', 'lodash'], // Dependencies
           filemtime( plugin_dir_path( __FILE__ ) . 'js/magic-link-custom.js' ), // Version
           true // In footer
       );

       // Enqueue local style
       wp_enqueue_style(
           'magic-link-custom', // Handle
           plugin_dir_url( __FILE__ ) . 'css/magic-link-custom.css', // URL
           [], // Dependencies
           filemtime( plugin_dir_path( __FILE__ ) . 'css/magic-link-custom.css' ) // Version
       );
   }
   ```

   Example of enqueueing a CDN script:
   ```php
   public function __construct() {
       parent::__construct();
       add_action( 'wp_enqueue_scripts', [ $this, 'wp_enqueue_scripts' ] );
   }

   public function wp_enqueue_scripts() {
       // Enqueue CDN script
       wp_enqueue_script(
           'chart-js', // Handle
           'https://cdn.jsdelivr.net/npm/chart.js@3.7.0/dist/chart.min.js', // CDN URL
           [], // Dependencies
           '3.7.0', // Version
           true // In footer
       );

       // Enqueue CDN style
       wp_enqueue_style(
           'toastify', // Handle
           'https://cdn.jsdelivr.net/npm/toastify-js@1.12.0/src/toastify.min.css', // CDN URL
           [], // Dependencies
           '1.12.0' // Version
       );
   }
   ```

   Then add the handles to the allowed lists:
   ```php
   public function dt_magic_url_base_allowed_js( $allowed_js ) {
       $allowed_js[] = 'magic-link-custom';
       $allowed_js[] = 'chart-js';
       return $allowed_js;
   }

   public function dt_magic_url_base_allowed_css( $allowed_css ) {
       $allowed_css[] = 'magic-link-custom';
       $allowed_css[] = 'toastify';
       return $allowed_css;
   }
   ```

   Important notes:
   - Always use WordPress's enqueue functions instead of directly adding script/style tags
   - For local files, use `plugin_dir_url()` and `plugin_dir_path()` to get correct paths
   - Use `filemtime()` for local files to bust cache when files change
   - Specify dependencies to ensure proper loading order
   - Add scripts to footer when possible for better performance
   - Remember to add the handles to the allowed lists or they won't be loaded

2. **Template Functions**
   ```php
   public function header_style() {
       // Add inline styles to header
   }

   public function header_javascript() {
       // Add inline JavaScript to header
   }

   public function footer_javascript() {
       // Add inline JavaScript to footer
   }
   ```
   These functions allow you to add inline styles and scripts to your Magic Link page. They are called automatically by the base class.


4. **Page Content**
   ```php
   public function body() {
       // Implement your page content here
   }
   ```
   This is where you implement the main content of your Magic Link page.

5. **Language and Localization**
   ```php
   public $translatable = [ 'query', 'user', 'contact' ];
   ```
   Define which parts of your Magic Link should be translatable. The order determines the priority of translation sources.



## Usage Examples

### Creating a Contact Update Form

1. Create a new Magic Link class extending `DT_Magic_Url_Base`
2. Configure the meta data for the form fields
3. Implement the form display in the `body()` method
4. Handle form submissions through REST API endpoints

### Setting Up a User Portal

1. Create a user-specific Magic Link
2. Implement authentication checks
3. Display user-specific data and functionality
4. Handle user interactions through REST endpoints

### Creating a Public Map

1. Use the Map Magic Link template
2. Configure map settings and data sources
3. Implement map display and interactions
4. Handle any necessary data updates

## Best Practices

1. **Security**
   - Always validate input data
   - Use nonces for forms
   - Implement proper permission checks
   - Set appropriate expiration times

2. **Performance**
   - Use WordPress enqueue functions properly for scripts and styles
   - Implement proper script dependencies

3. **User Experience**
   - Provide clear feedback for actions
   - Implement proper error handling
   - Use responsive design
   - Support multiple languages when needed