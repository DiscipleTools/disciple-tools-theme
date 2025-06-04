# DT Import Feature - Implementation Guide

## Overview

This guide provides detailed step-by-step instructions for implementing the DT Import feature using **vanilla JavaScript with DT Web Components** where available, and **WordPress admin styling** for consistency with the admin interface.

## Frontend Architecture Strategy

### Technology Stack
- **Core**: Vanilla JavaScript (ES6+)
- **Components**: DT Web Components where available
- **Styling**: WordPress admin CSS patterns + custom CSS
- **AJAX**: WordPress REST API with native fetch()
- **File Handling**: HTML5 File API with drag-and-drop

### Why This Approach
- ✅ **Performance**: Minimal bundle size, no framework overhead
- ✅ **Consistency**: Matches DT admin interface patterns
- ✅ **Maintainability**: Uses established DT component library
- ✅ **Progressive**: Enhances existing HTML with JavaScript
- ✅ **Future-proof**: Aligns with DT's web component direction

## Phase 1: Core Infrastructure Setup

### Step 1: Create Main Plugin File

Create `dt-import.php` as the main entry point:

```php
<?php
/**
 * DT Import Feature
 * Main plugin file for CSV import functionality
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Main DT Import Class
 */
class DT_Import {
    private static $_instance = null;
    
    public static function instance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    
    public function __construct() {
        add_action('init', [$this, 'init']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
        register_activation_hook(__FILE__, [$this, 'activate']);
        register_deactivation_hook(__FILE__, [$this, 'deactivate']);
    }
    
    public function init() {
        // Load required files
        $this->load_dependencies();
        
        // Initialize admin interface if in admin
        if (is_admin()) {
            $this->init_admin();
        }
    }
    
    private function load_dependencies() {
        require_once plugin_dir_path(__FILE__) . 'includes/dt-import-utilities.php';
        require_once plugin_dir_path(__FILE__) . 'includes/dt-import-validators.php';
        require_once plugin_dir_path(__FILE__) . 'includes/dt-import-field-handlers.php';
        
        if (is_admin()) {
            require_once plugin_dir_path(__FILE__) . 'admin/dt-import-admin-tab.php';
            require_once plugin_dir_path(__FILE__) . 'admin/dt-import-mapping.php';
            require_once plugin_dir_path(__FILE__) . 'admin/dt-import-processor.php';
            require_once plugin_dir_path(__FILE__) . 'ajax/dt-import-ajax.php';
        }
    }
    
    private function init_admin() {
        DT_Import_Admin_Tab::instance();
        DT_Import_Ajax::instance();
    }
    
    public function enqueue_scripts() {
        if (is_admin() && isset($_GET['page']) && $_GET['page'] === 'dt_options' && isset($_GET['tab']) && $_GET['tab'] === 'import') {
            wp_enqueue_script(
                'dt-import-js',
                plugin_dir_url(__FILE__) . 'assets/js/dt-import.js',
                ['jquery'],
                '1.0.0',
                true
            );
            
            wp_enqueue_style(
                'dt-import-css',
                plugin_dir_url(__FILE__) . 'assets/css/dt-import.css',
                [],
                '1.0.0'
            );
            
            // Localize script with necessary data
            wp_localize_script('dt-import-js', 'dtImport', [
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('dt_import_nonce'),
                'translations' => $this->get_translations(),
                'fieldTypes' => $this->get_field_types()
            ]);
        }
    }
    
    private function get_translations() {
        return [
            'map_values' => __('Map Values', 'disciple_tools'),
            'csv_value' => __('CSV Value', 'disciple_tools'),
            'dt_field_value' => __('DT Field Value', 'disciple_tools'),
            'skip_value' => __('Skip this value', 'disciple_tools'),
            'create_new_field' => __('Create New Field', 'disciple_tools'),
            'field_name' => __('Field Name', 'disciple_tools'),
            'field_type' => __('Field Type', 'disciple_tools'),
            'field_description' => __('Description', 'disciple_tools'),
            'create_field' => __('Create Field', 'disciple_tools'),
            'creating' => __('Creating...', 'disciple_tools'),
            'field_created_success' => __('Field created successfully!', 'disciple_tools'),
            'field_creation_error' => __('Error creating field', 'disciple_tools'),
            'ajax_error' => __('An error occurred. Please try again.', 'disciple_tools'),
            'fill_required_fields' => __('Please fill in all required fields.', 'disciple_tools')
        ];
    }
    
    private function get_field_types() {
        return [
            'text' => __('Text', 'disciple_tools'),
            'textarea' => __('Text Area', 'disciple_tools'),
            'number' => __('Number', 'disciple_tools'),
            'date' => __('Date', 'disciple_tools'),
            'boolean' => __('Boolean', 'disciple_tools'),
            'key_select' => __('Dropdown', 'disciple_tools'),
            'multi_select' => __('Multi Select', 'disciple_tools'),
            'tags' => __('Tags', 'disciple_tools'),
            'communication_channel' => __('Communication Channel', 'disciple_tools'),
            'connection' => __('Connection', 'disciple_tools'),
            'user_select' => __('User Select', 'disciple_tools'),
            'location' => __('Location', 'disciple_tools')
        ];
    }
    
    public function activate() {
        // Create upload directory for temporary CSV files
        $upload_dir = wp_upload_dir();
        $dt_import_dir = $upload_dir['basedir'] . '/dt-import-temp/';
        
        if (!file_exists($dt_import_dir)) {
            wp_mkdir_p($dt_import_dir);
            
            // Create .htaccess file to prevent direct access
            $htaccess_content = "Order deny,allow\nDeny from all\n";
            file_put_contents($dt_import_dir . '.htaccess', $htaccess_content);
        }
    }
    
    public function deactivate() {
        // Clean up temporary files
        $this->cleanup_temp_files();
    }
    
    private function cleanup_temp_files() {
        $upload_dir = wp_upload_dir();
        $dt_import_dir = $upload_dir['basedir'] . '/dt-import-temp/';
        
        if (file_exists($dt_import_dir)) {
            $files = glob($dt_import_dir . '*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
        }
    }
}

// Initialize the plugin
DT_Import::instance();
```

### Step 2: Create Admin Tab Integration

Create `admin/dt-import-admin-tab.php`:

```php
<?php
/**
 * DT Import Admin Tab Integration
 */

if (!defined('ABSPATH')) {
    exit;
}

class DT_Import_Admin_Tab extends Disciple_Tools_Abstract_Menu_Base {
    private static $_instance = null;
    
    public static function instance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    
    public function __construct() {
        add_action('admin_menu', [$this, 'add_submenu'], 125);
        add_action('dt_settings_tab_menu', [$this, 'add_tab'], 125, 1);
        add_action('dt_settings_tab_content', [$this, 'content'], 125, 1);
        parent::__construct();
    }
    
    public function add_submenu() {
        add_submenu_page(
            'dt_options',
            __('Import', 'disciple_tools'),
            __('Import', 'disciple_tools'),
            'manage_dt',
            'dt_options&tab=import',
            ['Disciple_Tools_Settings_Menu', 'content']
        );
    }
    
    public function add_tab($tab) {
        ?>
        <a href="<?php echo esc_url(admin_url()) ?>admin.php?page=dt_options&tab=import"
           class="nav-tab <?php echo esc_html($tab == 'import' ? 'nav-tab-active' : '') ?>">
            <?php echo esc_html__('Import', 'disciple_tools') ?>
        </a>
        <?php
    }
    
    public function content($tab) {
        if ('import' !== $tab) {
            return;
        }
        
        // Check permissions
        if (!current_user_can('manage_dt')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'disciple_tools'));
        }
        
        // Process form submissions
        $this->process_form_submission();
        
        // Display appropriate step
        $this->display_import_interface();
    }
    
    private function process_form_submission() {
        if (!isset($_POST['dt_import_step'])) {
            return;
        }
        
        $step = sanitize_text_field($_POST['dt_import_step']);
        
        switch ($step) {
            case '1':
                $this->process_step_1();
                break;
            case '2':
                $this->process_step_2();
                break;
            case '3':
                $this->process_step_3();
                break;
            case '4':
                $this->process_step_4();
                break;
        }
    }
    
    private function display_import_interface() {
        $current_step = $this->get_current_step();
        
        echo '<div class="wrap dt-import-container">';
        echo '<h1>' . esc_html__('Import Data', 'disciple_tools') . '</h1>';
        
        // Display progress indicator
        $this->display_progress_indicator($current_step);
        
        // Display appropriate step content
        switch ($current_step) {
            case 1:
                $this->display_step_1();
                break;
            case 2:
                $this->display_step_2();
                break;
            case 3:
                $this->display_step_3();
                break;
            case 4:
                $this->display_step_4();
                break;
            default:
                $this->display_step_1();
        }
        
        echo '</div>';
    }
    
    private function get_current_step() {
        // Determine current step based on session data
        if (!isset($_SESSION['dt_import'])) {
            return 1;
        }
        
        $session_data = $_SESSION['dt_import'];
        
        if (!isset($session_data['post_type'])) {
            return 1;
        }
        
        if (!isset($session_data['csv_data'])) {
            return 2;
        }
        
        if (!isset($session_data['mapping'])) {
            return 3;
        }
        
        return 4;
    }
    
    private function display_progress_indicator($current_step) {
        $steps = [
            1 => __('Select Post Type', 'disciple_tools'),
            2 => __('Upload CSV', 'disciple_tools'),
            3 => __('Map Fields', 'disciple_tools'),
            4 => __('Preview & Import', 'disciple_tools')
        ];
        
        echo '<div class="dt-import-progress">';
        echo '<ul class="dt-import-steps">';
        
        foreach ($steps as $step_num => $step_name) {
            $class = '';
            if ($step_num < $current_step) {
                $class = 'completed';
            } elseif ($step_num == $current_step) {
                $class = 'active';
            }
            
            echo '<li class="' . esc_attr($class) . '">';
            echo '<span class="step-number">' . esc_html($step_num) . '</span>';
            echo '<span class="step-name">' . esc_html($step_name) . '</span>';
            echo '</li>';
        }
        
        echo '</ul>';
        echo '</div>';
    }
    
    // Step-specific methods will be implemented in subsequent phases
    private function display_step_1() {
        require_once plugin_dir_path(__FILE__) . '../templates/step-1-select-type.php';
    }
    
    private function display_step_2() {
        require_once plugin_dir_path(__FILE__) . '../templates/step-2-upload-csv.php';
    }
    
    private function display_step_3() {
        require_once plugin_dir_path(__FILE__) . '../templates/step-3-mapping.php';
    }
    
    private function display_step_4() {
        require_once plugin_dir_path(__FILE__) . '../templates/step-4-preview.php';
    }
}
```

### Step 3: Create Utility Functions

Create `includes/dt-import-utilities.php`:

```php
<?php
/**
 * DT Import Utility Functions
 */

if (!defined('ABSPATH')) {
    exit;
}

class DT_Import_Utilities {
    
    /**
     * Parse CSV file and return data array
     */
    public static function parse_csv_file($file_path, $delimiter = ',') {
        if (!file_exists($file_path)) {
            return new WP_Error('file_not_found', __('CSV file not found.', 'disciple_tools'));
        }
        
        $data = [];
        $handle = fopen($file_path, 'r');
        
        if ($handle === false) {
            return new WP_Error('file_read_error', __('Unable to read CSV file.', 'disciple_tools'));
        }
        
        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            $data[] = $row;
        }
        
        fclose($handle);
        
        if (empty($data)) {
            return new WP_Error('empty_file', __('CSV file is empty.', 'disciple_tools'));
        }
        
        return $data;
    }
    
    /**
     * Detect CSV delimiter
     */
    public static function detect_delimiter($file_path) {
        $delimiters = [',', ';', "\t", '|'];
        $file_handle = fopen($file_path, 'r');
        $first_line = fgets($file_handle);
        fclose($file_handle);
        
        $delimiter_count = [];
        
        foreach ($delimiters as $delimiter) {
            $delimiter_count[$delimiter] = substr_count($first_line, $delimiter);
        }
        
        return array_search(max($delimiter_count), $delimiter_count);
    }
    
    /**
     * Sanitize CSV data
     */
    public static function sanitize_csv_data($data) {
        $sanitized = [];
        
        foreach ($data as $row) {
            $sanitized_row = [];
            foreach ($row as $cell) {
                $sanitized_row[] = sanitize_text_field($cell);
            }
            $sanitized[] = $sanitized_row;
        }
        
        return $sanitized;
    }
    
    /**
     * Get sample data from column
     */
    public static function get_sample_data($csv_data, $column_index, $count = 5) {
        $samples = [];
        $row_count = 0;
        
        foreach ($csv_data as $row) {
            if ($row_count >= $count) {
                break;
            }
            
            if (isset($row[$column_index]) && !empty(trim($row[$column_index]))) {
                $samples[] = trim($row[$column_index]);
                $row_count++;
            }
        }
        
        return $samples;
    }
    
    /**
     * Normalize string for comparison
     */
    public static function normalize_string($string) {
        return strtolower(trim(preg_replace('/[^a-zA-Z0-9]/', '', $string)));
    }
    
    /**
     * Store data in session
     */
    public static function store_session_data($key, $data) {
        if (!session_id()) {
            session_start();
        }
        
        if (!isset($_SESSION['dt_import'])) {
            $_SESSION['dt_import'] = [];
        }
        
        $_SESSION['dt_import'][$key] = $data;
    }
    
    /**
     * Get data from session
     */
    public static function get_session_data($key = null) {
        if (!session_id()) {
            session_start();
        }
        
        if ($key === null) {
            return $_SESSION['dt_import'] ?? [];
        }
        
        return $_SESSION['dt_import'][$key] ?? null;
    }
    
    /**
     * Clear session data
     */
    public static function clear_session_data() {
        if (!session_id()) {
            session_start();
        }
        
        unset($_SESSION['dt_import']);
    }
    
    /**
     * Save uploaded file to temporary directory
     */
    public static function save_uploaded_file($file_data) {
        $upload_dir = wp_upload_dir();
        $temp_dir = $upload_dir['basedir'] . '/dt-import-temp/';
        
        // Ensure directory exists
        if (!file_exists($temp_dir)) {
            wp_mkdir_p($temp_dir);
        }
        
        // Generate unique filename
        $filename = 'import_' . uniqid() . '_' . sanitize_file_name($file_data['name']);
        $filepath = $temp_dir . $filename;
        
        // Move uploaded file
        if (move_uploaded_file($file_data['tmp_name'], $filepath)) {
            return $filepath;
        }
        
        return false;
    }
    
    /**
     * Validate file upload
     */
    public static function validate_file_upload($file_data) {
        $errors = [];
        
        // Check for upload errors
        if ($file_data['error'] !== UPLOAD_ERR_OK) {
            $errors[] = __('File upload failed.', 'disciple_tools');
        }
        
        // Check file size (10MB limit)
        if ($file_data['size'] > 10 * 1024 * 1024) {
            $errors[] = __('File size exceeds 10MB limit.', 'disciple_tools');
        }
        
        // Check file type
        $file_info = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($file_info, $file_data['tmp_name']);
        finfo_close($file_info);
        
        $allowed_types = ['text/csv', 'text/plain', 'application/csv'];
        if (!in_array($mime_type, $allowed_types)) {
            $errors[] = __('Invalid file type. Please upload a CSV file.', 'disciple_tools');
        }
        
        // Check file extension
        $file_extension = strtolower(pathinfo($file_data['name'], PATHINFO_EXTENSION));
        if ($file_extension !== 'csv') {
            $errors[] = __('Invalid file extension. Please upload a .csv file.', 'disciple_tools');
        }
        
        return $errors;
    }
}
```

## Phase 2: Template Creation

### Step 4: Create Step Templates

Create `templates/step-1-select-type.php`:

```php
<?php
/**
 * Step 1: Post Type Selection Template
 */

if (!defined('ABSPATH')) {
    exit;
}

$post_types = DT_Posts::get_post_types();
?>

<div class="dt-import-step" id="step-1">
    <div class="dt-import-box">
        <h2><?php esc_html_e('Step 1: Select Post Type', 'disciple_tools') ?></h2>
        <p><?php esc_html_e('Choose the type of records you want to import from your CSV file.', 'disciple_tools') ?></p>
        
        <form method="post" id="dt-import-post-type-form">
            <?php wp_nonce_field('dt_import_step_1', 'dt_import_nonce'); ?>
            <input type="hidden" name="dt_import_step" value="1">
            
            <table class="widefat striped">
                <thead>
                    <tr>
                        <th style="width: 50px;"><?php esc_html_e('Select', 'disciple_tools') ?></th>
                        <th><?php esc_html_e('Post Type', 'disciple_tools') ?></th>
                        <th><?php esc_html_e('Description', 'disciple_tools') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($post_types as $post_type): ?>
                        <?php 
                        $post_settings = DT_Posts::get_post_settings($post_type);
                        $label_singular = $post_settings['label_singular'] ?? ucfirst($post_type);
                        $label_plural = $post_settings['label_plural'] ?? ucfirst($post_type) . 's';
                        ?>
                        <tr>
                            <td>
                                <input type="radio" 
                                       name="selected_post_type" 
                                       value="<?php echo esc_attr($post_type) ?>" 
                                       id="post_type_<?php echo esc_attr($post_type) ?>"
                                       required>
                            </td>
                            <td>
                                <label for="post_type_<?php echo esc_attr($post_type) ?>">
                                    <strong><?php echo esc_html($label_plural) ?></strong>
                                    <br>
                                    <small class="description"><?php echo esc_html($label_singular) ?></small>
                                </label>
                            </td>
                            <td>
                                <?php 
                                $descriptions = [
                                    'contacts' => __('Individual people you are reaching or discipling', 'disciple_tools'),
                                    'groups' => __('Groups, churches, or gatherings of people', 'disciple_tools'),
                                ];
                                echo esc_html($descriptions[$post_type] ?? '');
                                ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <p class="submit">
                <button type="submit" class="button button-primary">
                    <?php esc_html_e('Next: Upload CSV File', 'disciple_tools') ?>
                </button>
            </p>
        </form>
    </div>
</div>
```

Create `templates/step-2-upload-csv.php`:

```php
<?php
/**
 * Step 2: CSV Upload Template
 */

if (!defined('ABSPATH')) {
    exit;
}

$session_data = DT_Import_Utilities::get_session_data();
$selected_post_type = $session_data['post_type'] ?? '';
$post_settings = DT_Posts::get_post_settings($selected_post_type);
?>

<div class="dt-import-step" id="step-2">
    <div class="dt-import-box">
        <h2><?php esc_html_e('Step 2: Upload CSV File', 'disciple_tools') ?></h2>
        <p>
            <?php 
            printf(
                esc_html__('Upload a CSV file containing %s data.', 'disciple_tools'),
                esc_html($post_settings['label_plural'])
            );
            ?>
        </p>
        
        <form method="post" enctype="multipart/form-data" id="dt-import-csv-form">
            <?php wp_nonce_field('dt_import_step_2', 'dt_import_nonce'); ?>
            <input type="hidden" name="dt_import_step" value="2">
            <input type="hidden" name="selected_post_type" value="<?php echo esc_attr($selected_post_type) ?>">
            
            <table class="form-table">
                <tbody>
                    <tr>
                        <th scope="row">
                            <label for="csv_file"><?php esc_html_e('CSV File', 'disciple_tools') ?></label>
                        </th>
                        <td>
                            <input type="file" name="csv_file" id="csv_file" accept=".csv" required>
                            <p class="description">
                                <?php esc_html_e('Maximum file size: 10MB. First row should contain column headers.', 'disciple_tools') ?>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="csv_delimiter"><?php esc_html_e('Delimiter', 'disciple_tools') ?></label>
                        </th>
                        <td>
                            <select name="csv_delimiter" id="csv_delimiter">
                                <option value=","><?php esc_html_e('Comma (,)', 'disciple_tools') ?></option>
                                <option value=";"><?php esc_html_e('Semicolon (;)', 'disciple_tools') ?></option>
                                <option value="<?php echo "\t"; ?>"><?php esc_html_e('Tab', 'disciple_tools') ?></option>
                                <option value="|"><?php esc_html_e('Pipe (|)', 'disciple_tools') ?></option>
                            </select>
                            <p class="description">
                                <?php esc_html_e('Choose the character that separates columns in your CSV file.', 'disciple_tools') ?>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="csv_encoding"><?php esc_html_e('Encoding', 'disciple_tools') ?></label>
                        </th>
                        <td>
                            <select name="csv_encoding" id="csv_encoding">
                                <option value="UTF-8"><?php esc_html_e('UTF-8', 'disciple_tools') ?></option>
                                <option value="ISO-8859-1"><?php esc_html_e('ISO-8859-1 (Latin-1)', 'disciple_tools') ?></option>
                                <option value="Windows-1252"><?php esc_html_e('Windows-1252', 'disciple_tools') ?></option>
                            </select>
                            <p class="description">
                                <?php esc_html_e('Character encoding of your CSV file. UTF-8 is recommended.', 'disciple_tools') ?>
                            </p>
                        </td>
                    </tr>
                </tbody>
            </table>
            
            <div class="dt-import-actions">
                <a href="<?php echo esc_url(admin_url('admin.php?page=dt_options&tab=import')) ?>" 
                   class="button">
                    <?php esc_html_e('← Back', 'disciple_tools') ?>
                </a>
                <button type="submit" class="button button-primary">
                    <?php esc_html_e('Next: Map Fields →', 'disciple_tools') ?>
                </button>
            </div>
        </form>
        
        <div class="dt-import-help">
            <h3><?php esc_html_e('CSV Format Guidelines', 'disciple_tools') ?></h3>
            <ul>
                <li><?php esc_html_e('Include column headers in the first row', 'disciple_tools') ?></li>
                <li><?php esc_html_e('Use semicolon (;) to separate multiple values in a single field', 'disciple_tools') ?></li>
                <li><?php esc_html_e('Date format should be YYYY-MM-DD or MM/DD/YYYY', 'disciple_tools') ?></li>
                <li><?php esc_html_e('Boolean fields should use true/false, yes/no, or 1/0', 'disciple_tools') ?></li>
            </ul>
        </div>
    </div>
</div>
```

## Phase 3: Field Mapping Implementation

### Step 5: Create Field Mapping Logic

Create `admin/dt-import-mapping.php`:

```php
<?php
/**
 * DT Import Field Mapping Logic
 */

if (!defined('ABSPATH')) {
    exit;
}

class DT_Import_Mapping {
    
    /**
     * Analyze CSV columns and suggest field mappings
     */
    public static function analyze_csv_columns($csv_data, $post_type) {
        if (empty($csv_data)) {
            return [];
        }
        
        $headers = array_shift($csv_data);
        $field_settings = DT_Posts::get_post_field_settings($post_type);
        
        $mapping_suggestions = [];
        
        foreach ($headers as $index => $column_name) {
            $suggestion = self::suggest_field_mapping($column_name, $field_settings);
            $sample_data = DT_Import_Utilities::get_sample_data($csv_data, $index, 5);
            
            $mapping_suggestions[$index] = [
                'column_name' => $column_name,
                'suggested_field' => $suggestion,
                'sample_data' => $sample_data,
                'confidence' => $suggestion ? self::calculate_confidence($column_name, $suggestion, $field_settings) : 0
            ];
        }
        
        return $mapping_suggestions;
    }
    
    /**
     * Suggest field mapping for a column
     */
    private static function suggest_field_mapping($column_name, $field_settings) {
        $column_normalized = DT_Import_Utilities::normalize_string($column_name);
        
        // Direct field name matches
        foreach ($field_settings as $field_key => $field_config) {
            $field_normalized = DT_Import_Utilities::normalize_string($field_config['name']);
            
            // Exact match
            if ($column_normalized === $field_normalized) {
                return $field_key;
            }
            
            // Field key match
            if ($column_normalized === DT_Import_Utilities::normalize_string($field_key)) {
                return $field_key;
            }
        }
        
        // Partial matches
        foreach ($field_settings as $field_key => $field_config) {
            $field_normalized = DT_Import_Utilities::normalize_string($field_config['name']);
            
            if (strpos($field_normalized, $column_normalized) !== false || 
                strpos($column_normalized, $field_normalized) !== false) {
                return $field_key;
            }
        }
        
        // Common aliases
        $aliases = self::get_field_aliases();
        foreach ($aliases as $field_key => $field_aliases) {
            foreach ($field_aliases as $alias) {
                if ($column_normalized === DT_Import_Utilities::normalize_string($alias)) {
                    return $field_key;
                }
            }
        }
        
        return null;
    }
    
    /**
     * Calculate confidence score for field mapping
     */
    private static function calculate_confidence($column_name, $field_key, $field_settings) {
        $column_normalized = DT_Import_Utilities::normalize_string($column_name);
        $field_config = $field_settings[$field_key];
        $field_normalized = DT_Import_Utilities::normalize_string($field_config['name']);
        $field_key_normalized = DT_Import_Utilities::normalize_string($field_key);
        
        // Exact matches get highest confidence
        if ($column_normalized === $field_normalized || $column_normalized === $field_key_normalized) {
            return 100;
        }
        
        // Partial matches get medium confidence
        if (strpos($field_normalized, $column_normalized) !== false || 
            strpos($column_normalized, $field_normalized) !== false) {
            return 75;
        }
        
        // Alias matches get lower confidence
        $aliases = self::get_field_aliases();
        if (isset($aliases[$field_key])) {
            foreach ($aliases[$field_key] as $alias) {
                if ($column_normalized === DT_Import_Utilities::normalize_string($alias)) {
                    return 60;
                }
            }
        }
        
        return 0;
    }
    
    /**
     * Get field aliases for common mappings
     */
    private static function get_field_aliases() {
        return [
            'title' => ['name', 'full_name', 'contact_name', 'fullname', 'person_name'],
            'contact_phone' => ['phone', 'telephone', 'mobile', 'cell', 'phone_number'],
            'contact_email' => ['email', 'e-mail', 'email_address', 'mail'],
            'assigned_to' => ['assigned', 'worker', 'assigned_worker', 'owner'],
            'overall_status' => ['status', 'contact_status'],
            'seeker_path' => ['seeker', 'spiritual_status', 'faith_status'],
            'baptism_date' => ['baptized', 'baptism', 'baptized_date'],
            'location_grid' => ['location', 'address', 'city', 'country'],
            'contact_address' => ['address', 'street_address', 'home_address'],
            'age' => ['years_old', 'years'],
            'gender' => ['sex'],
            'reason_paused' => ['paused_reason', 'pause_reason'],
            'reason_unassignable' => ['unassignable_reason'],
            'tags' => ['tag', 'labels', 'categories']
        ];
    }
    
    /**
     * Get available options for a field
     */
    public static function get_field_options($field_key, $field_config) {
        if (!in_array($field_config['type'], ['key_select', 'multi_select'])) {
            return [];
        }
        
        return $field_config['default'] ?? [];
    }
    
    /**
     * Validate field mapping configuration
     */
    public static function validate_mapping($mapping_data, $post_type) {
        $errors = [];
        $field_settings = DT_Posts::get_post_field_settings($post_type);
        
        foreach ($mapping_data as $column_index => $mapping) {
            if (empty($mapping['field_key']) || $mapping['field_key'] === 'skip') {
                continue;
            }
            
            $field_key = $mapping['field_key'];
            
            // Check if field exists
            if (!isset($field_settings[$field_key])) {
                $errors[] = sprintf(
                    __('Field "%s" does not exist for post type "%s"', 'disciple_tools'),
                    $field_key,
                    $post_type
                );
                continue;
            }
            
            $field_config = $field_settings[$field_key];
            
            // Validate field-specific configuration
            if (in_array($field_config['type'], ['key_select', 'multi_select'])) {
                if (isset($mapping['value_mapping'])) {
                    foreach ($mapping['value_mapping'] as $csv_value => $dt_value) {
                        if (!empty($dt_value) && !isset($field_config['default'][$dt_value])) {
                            $errors[] = sprintf(
                                __('Invalid option "%s" for field "%s"', 'disciple_tools'),
                                $dt_value,
                                $field_config['name']
                            );
                        }
                    }
                }
            }
        }
        
        return $errors;
    }
}
```

### Step 6: Create Field Mapping Template

Create `templates/step-3-mapping.php`:

```php
<?php
/**
 * Step 3: Field Mapping Template
 */

if (!defined('ABSPATH')) {
    exit;
}

$session_data = DT_Import_Utilities::get_session_data();
$post_type = $session_data['post_type'] ?? '';
$csv_data = $session_data['csv_data'] ?? [];
$mapping_suggestions = $session_data['mapping_suggestions'] ?? [];
$field_settings = DT_Posts::get_post_field_settings($post_type);
?>

<div class="dt-import-step" id="step-3">
    <div class="dt-import-box">
        <h2><?php esc_html_e('Step 3: Map CSV Columns to Fields', 'disciple_tools') ?></h2>
        <p><?php esc_html_e('Map each column from your CSV file to the appropriate field in Disciple.Tools.', 'disciple_tools') ?></p>
        
        <?php if (!empty($mapping_suggestions)): ?>
            <div class="dt-import-mapping-container">
                <div class="dt-import-columns-horizontal">
                    <?php foreach ($mapping_suggestions as $column_index => $mapping): ?>
                        <div class="dt-import-column-card" data-column-index="<?php echo esc_attr($column_index) ?>">
                            <div class="column-header">
                                <h4><?php echo esc_html($mapping['column_name']) ?></h4>
                                <?php if ($mapping['confidence'] > 0): ?>
                                    <div class="confidence-indicator confidence-<?php echo esc_attr($mapping['confidence'] >= 80 ? 'high' : ($mapping['confidence'] >= 60 ? 'medium' : 'low')) ?>">
                                        <?php printf(__('Confidence: %d%%', 'disciple_tools'), $mapping['confidence']) ?>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="sample-data">
                                    <strong><?php esc_html_e('Sample data:', 'disciple_tools') ?></strong>
                                    <ul>
                                        <?php foreach (array_slice($mapping['sample_data'], 0, 3) as $sample): ?>
                                            <li><?php echo esc_html($sample) ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            </div>
                            
                            <div class="mapping-controls">
                                <label><?php esc_html_e('Map to field:', 'disciple_tools') ?></label>
                                <select name="field_mapping[<?php echo esc_attr($column_index) ?>]" 
                                        class="field-mapping-select" 
                                        data-column-index="<?php echo esc_attr($column_index) ?>">
                                    <option value=""><?php esc_html_e('-- Do not import --', 'disciple_tools') ?></option>
                                    
                                    <?php foreach ($field_settings as $field_key => $field_config): ?>
                                        <?php if (empty($field_config['hidden'])): ?>
                                            <option value="<?php echo esc_attr($field_key) ?>" 
                                                    <?php selected($mapping['suggested_field'], $field_key) ?>
                                                    data-field-type="<?php echo esc_attr($field_config['type']) ?>"
                                                    data-field-name="<?php echo esc_attr($field_config['name']) ?>">
                                                <?php echo esc_html($field_config['name']) ?>
                                                (<?php echo esc_html($field_config['type']) ?>)
                                            </option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                    
                                    <option value="create_new"><?php esc_html_e('+ Create New Field', 'disciple_tools') ?></option>
                                </select>
                                
                                <!-- Field-specific options will be inserted here by JavaScript -->
                                <div class="field-specific-options" style="display: none;"></div>
                                
                                <!-- New field creation form -->
                                <div class="create-field-form" style="display: none;"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <form method="post" id="dt-import-mapping-form">
                <?php wp_nonce_field('dt_import_step_3', 'dt_import_nonce'); ?>
                <input type="hidden" name="dt_import_step" value="3">
                <input type="hidden" name="post_type" value="<?php echo esc_attr($post_type) ?>">
                <!-- Mapping data will be populated by JavaScript -->
                <input type="hidden" name="mapping_data" id="mapping-data-input">
                
                <div class="dt-import-actions">
                    <a href="<?php echo esc_url(admin_url('admin.php?page=dt_options&tab=import&step=2')) ?>" 
                       class="button">
                        <?php esc_html_e('← Back', 'disciple_tools') ?>
                    </a>
                    <button type="submit" class="button button-primary">
                        <?php esc_html_e('Next: Preview Import →', 'disciple_tools') ?>
                    </button>
                </div>
            </form>
            
        <?php else: ?>
            <div class="notice notice-error">
                <p><?php esc_html_e('No CSV data found. Please go back and upload a CSV file.', 'disciple_tools') ?></p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Field Options Data for JavaScript -->
<script type="text/javascript">
    window.dtImportFieldSettings = <?php echo json_encode($field_settings) ?>;
    window.dtImportPostType = <?php echo json_encode($post_type) ?>;
</script>
```

## Updated Import Strategy Using dt_reports Table

### Overview

The DT Import system has been updated to use the existing `dt_reports` table instead of creating a separate `dt_import_sessions` table. This approach provides several benefits:

1. **Reuses existing infrastructure** - Leverages DT's built-in reporting system
2. **Follows DT patterns** - Uses established table structures and APIs
3. **Reduces database overhead** - No additional tables needed
4. **Maintains data integrity** - Benefits from existing cleanup and maintenance routines

### Data Mapping Strategy

The import session data is mapped to the `dt_reports` table as follows:

#### Core Fields Mapping

| Import Session Field | dt_reports Column | Purpose |
|---------------------|-------------------|---------|
| `session_id` | `id` | Primary key, auto-generated |
| `user_id` | `user_id` | Session owner for security isolation |
| `post_type` | `post_type` | Target DT post type for import |
| `status` | `subtype` | Current workflow stage (mapped) |
| `records_imported` | `value` | Count of successfully imported records |
| `file_name` | `label` | Original CSV filename |
| `created_at` | `timestamp` | Session creation time |

#### Status to Subtype Mapping

| Import Status | dt_reports Subtype | Description |
|---------------|-------------------|-------------|
| `uploaded` | `csv_upload` | CSV file uploaded and parsed |
| `analyzed` | `field_analysis` | Column analysis completed |
| `mapped` | `field_mapping` | Field mappings configured |
| `processing` | `import_processing` | Import in progress |
| `completed` | `import_completed` | Import completed successfully |
| `completed_with_errors` | `import_completed_with_errors` | Import completed with some errors |
| `failed` | `import_failed` | Import failed completely |

#### Payload Field Usage

All complex session data is serialized into the `payload` field:

```php
$session_data = [
    'csv_data' => $csv_array,           // Full CSV data array
    'headers' => $header_row,           // CSV column headers
    'row_count' => $total_rows,         // Total data rows (excluding header)
    'file_path' => $temp_file_path,     // Physical file location
    'field_mappings' => $mappings,      // User's field mapping configuration
    'mapping_suggestions' => $auto_map, // AI-generated mapping suggestions
    'progress' => $percentage,          // Import progress (0-100)
    'records_imported' => $count,       // Successfully imported records
    'error_count' => $error_count,      // Total errors encountered
    'errors' => $error_array            // Detailed error messages
];
```

### API Changes

#### Session Creation
```php
// OLD: Custom table insert
$wpdb->insert($wpdb->prefix . 'dt_import_sessions', $data);

// NEW: Use dt_report_insert() function
$report_id = dt_report_insert([
    'user_id' => $user_id,
    'post_type' => $post_type,
    'type' => 'import_session',
    'subtype' => 'csv_upload',
    'payload' => $session_data,
    'value' => $row_count,
    'label' => basename($file_path)
]);
```

#### Session Retrieval
```php
// OLD: Query custom table
$session = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}dt_import_sessions WHERE id = %d");

// NEW: Query dt_reports with type filter
$session = $wpdb->get_row("SELECT * FROM $wpdb->dt_reports WHERE id = %d AND type = 'import_session'");
$payload = maybe_unserialize($session['payload']);
$session = array_merge($session, $payload);
```

#### Session Updates
```php
// OLD: Update session_data column
$wpdb->update($table, ['session_data' => json_encode($data)], ['id' => $id]);

// NEW: Update payload and other relevant fields
$wpdb->update($wpdb->dt_reports, [
    'payload' => maybe_serialize($updated_payload),
    'subtype' => $status_subtype,
    'value' => $records_imported,
    'timestamp' => time()
], ['id' => $session_id]);
```

### Security Considerations

- **User Isolation**: All queries include `user_id` filter to ensure users can only access their own sessions
- **Type Filtering**: All queries include `type = 'import_session'` to isolate import data from other reports
- **File Cleanup**: Associated CSV files are properly cleaned up when sessions are deleted

### Cleanup Strategy

The system automatically cleans up old import sessions:

1. **File Cleanup**: Before deleting records, extract file paths from payload and delete physical files
2. **Record Cleanup**: Delete import session records older than 24 hours
3. **Batch Processing**: Handle cleanup in batches to avoid performance issues

```php
// Get sessions with file paths before deletion
$old_sessions = $wpdb->get_results("SELECT payload FROM $wpdb->dt_reports WHERE type = 'import_session' AND timestamp < %d");

// Clean up files
foreach ($old_sessions as $session) {
    $payload = maybe_unserialize($session['payload']);
    if (isset($payload['file_path']) && file_exists($payload['file_path'])) {
        unlink($payload['file_path']);
    }
}

// Delete old records
$wpdb->query("DELETE FROM $wpdb->dt_reports WHERE type = 'import_session' AND timestamp < %d");
```

### Benefits of This Approach

1. **Infrastructure Reuse**: Leverages existing `dt_reports` table and related APIs
2. **Consistency**: Follows established DT patterns for data storage and retrieval
3. **Scalability**: Benefits from existing table optimization and indexing
4. **Maintenance**: Integrates with existing cleanup and maintenance routines
5. **Flexibility**: `payload` field allows for complex data structures without schema changes
6. **Security**: Inherits existing security patterns from DT reports system

### Migration Considerations

If upgrading from a system that used a custom `dt_import_sessions` table:

1. Export existing session data before migration
2. Convert session data to new payload format
3. Insert converted data using `dt_report_insert()`
4. Drop the old custom table after verification
5. Update any external integrations to use new session retrieval methods

This approach ensures the import system integrates seamlessly with DT's existing infrastructure while maintaining all required functionality. 