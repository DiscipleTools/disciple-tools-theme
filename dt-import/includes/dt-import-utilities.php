<?php
/**
 * DT CSV Import Utilities
 */

if ( !defined( 'ABSPATH' ) ) {
    exit;
}

class DT_CSV_Import_Utilities {

    /**
     * Parse CSV file and return data array
     */
    public static function parse_csv_file( $file_path, $delimiter = ',' ) {
        if ( !file_exists( $file_path ) ) {
            return new WP_Error( 'file_not_found', __( 'CSV file not found.', 'disciple_tools' ) );
        }

        $data = [];
        $handle = fopen( $file_path, 'r' );

        if ( $handle === false ) {
            return new WP_Error( 'file_read_error', __( 'Unable to read CSV file.', 'disciple_tools' ) );
        }

        while ( ( $row = fgetcsv( $handle, 0, $delimiter ) ) !== false ) {
            $data[] = $row;
        }

        fclose( $handle );

        if ( empty( $data ) ) {
            return new WP_Error( 'empty_file', __( 'CSV file is empty.', 'disciple_tools' ) );
        }

        return $data;
    }

    /**
     * Detect CSV delimiter
     */
    public static function detect_delimiter( $file_path ) {
        $delimiters = [ ',', ';', "\t", '|' ];
        $file_handle = fopen( $file_path, 'r' );
        $first_line = fgets( $file_handle );
        fclose( $file_handle );

        $delimiter_count = [];

        foreach ( $delimiters as $delimiter ) {
            $delimiter_count[$delimiter] = substr_count( $first_line, $delimiter );
        }

        return array_search( max( $delimiter_count ), $delimiter_count );
    }

    /**
     * Sanitize CSV data
     */
    public static function sanitize_csv_data( $data ) {
        $sanitized = [];

        foreach ( $data as $row ) {
            $sanitized_row = [];
            foreach ( $row as $cell ) {
                $sanitized_row[] = sanitize_text_field( $cell );
            }
            $sanitized[] = $sanitized_row;
        }

        return $sanitized;
    }

    /**
     * Get sample data from column
     */
    public static function get_sample_data( $csv_data, $column_index, $count = 5 ) {
        $samples = [];
        $row_count = 0;

        foreach ( $csv_data as $row ) {
            if ( $row_count >= $count ) {
                break;
            }

            if ( isset( $row[$column_index] ) && !empty( trim( $row[$column_index] ) ) ) {
                $samples[] = trim( $row[$column_index] );
                $row_count++;
            }
        }

        return $samples;
    }

    /**
     * Normalize string for comparison
     */
    public static function normalize_string( $string ) {
        return strtolower( trim( preg_replace( '/[^a-zA-Z0-9]/', '', $string ) ) );
    }

    /**
     * Save uploaded file to temporary directory
     */
    public static function save_uploaded_file( $file_data ) {
        $upload_dir = wp_upload_dir();
        $temp_dir = $upload_dir['basedir'] . '/dt-import-temp/';

        // Ensure directory exists
        if ( !file_exists( $temp_dir ) ) {
            wp_mkdir_p( $temp_dir );
        }

        // Generate unique filename
        $filename = 'import_' . uniqid() . '_' . sanitize_file_name( $file_data['name'] );
        $filepath = $temp_dir . $filename;

        // Move uploaded file
        if ( move_uploaded_file( $file_data['tmp_name'], $filepath ) ) {
            return $filepath;
        }

        return false;
    }

    /**
     * Validate file upload
     */
    public static function validate_file_upload( $file_data ) {
        $errors = [];

        // Check for upload errors
        if ( $file_data['error'] !== UPLOAD_ERR_OK ) {
            $errors[] = __( 'File upload failed.', 'disciple_tools' );
        }

        // Check file size (10MB limit)
        if ( $file_data['size'] > 10 * 1024 * 1024 ) {
            $errors[] = __( 'File size exceeds 10MB limit.', 'disciple_tools' );
        }

        // Check file type
        $file_info = finfo_open( FILEINFO_MIME_TYPE );
        $mime_type = finfo_file( $file_info, $file_data['tmp_name'] );
        finfo_close( $file_info );

        $allowed_types = [ 'text/csv', 'text/plain', 'application/csv' ];
        if ( !in_array( $mime_type, $allowed_types ) ) {
            $errors[] = __( 'Invalid file type. Please upload a CSV file.', 'disciple_tools' );
        }

        // Check file extension
        $file_extension = strtolower( pathinfo( $file_data['name'], PATHINFO_EXTENSION ) );
        if ( $file_extension !== 'csv' ) {
            $errors[] = __( 'Invalid file extension. Please upload a .csv file.', 'disciple_tools' );
        }

        return $errors;
    }

    /**
     * Create custom field for post type
     */
    public static function create_custom_field( $post_type, $field_key, $field_config ) {
        // Check if field already exists
        $existing_fields = DT_Posts::get_post_field_settings( $post_type );
        if ( isset( $existing_fields[$field_key] ) ) {
            return new WP_Error( 'field_exists', __( 'Field already exists.', 'disciple_tools' ) );
        }

        // Validate field type
        $allowed_types = [ 'text', 'textarea', 'number', 'date', 'boolean', 'key_select', 'multi_select', 'tags', 'communication_channel', 'connection', 'user_select', 'location' ];
        if ( !in_array( $field_config['type'], $allowed_types ) ) {
            return new WP_Error( 'invalid_field_type', __( 'Invalid field type.', 'disciple_tools' ) );
        }

        // Create field using DT's field customization API
        $field_settings = [
            'name' => $field_config['name'],
            'description' => $field_config['description'] ?? '',
            'type' => $field_config['type'],
            'default' => $field_config['default'] ?? [],
            'tile' => 'other'
        ];

        // Add field using DT's field customization hooks
        add_filter('dt_custom_fields_settings', function( $fields, $post_type_filter ) use ( $post_type, $field_key, $field_settings ) {
            if ( $post_type_filter === $post_type ) {
                $fields[$field_key] = $field_settings;
            }
            return $fields;
        }, 10, 2);

        // Force refresh of field settings cache
        wp_cache_delete( $post_type . '_field_settings', 'dt_post_fields' );

        return true;
    }

    /**
     * Clean old temporary files
     */
    public static function cleanup_old_files( $hours = 24 ) {
        $upload_dir = wp_upload_dir();
        $temp_dir = $upload_dir['basedir'] . '/dt-import-temp/';

        if ( !file_exists( $temp_dir ) ) {
            return;
        }

        $files = glob( $temp_dir . '*' );
        $cutoff_time = time() - ( $hours * 3600 );

        foreach ( $files as $file ) {
            if ( is_file( $file ) && filemtime( $file ) < $cutoff_time ) {
                unlink( $file );
            }
        }
    }

    /**
     * Format file size for display
     */
    public static function format_file_size( $bytes ) {
        $units = [ 'B', 'KB', 'MB', 'GB' ];
        $factor = 1024;
        $units_count = count( $units );

        for ( $i = 0; $bytes >= $factor && $i < $units_count - 1; $i++ ) {
            $bytes /= $factor;
        }

        return round( $bytes, 2 ) . ' ' . $units[$i];
    }

    /**
     * Convert various date formats to Y-m-d
     */
    public static function normalize_date( $date_string, $format = 'auto' ) {
        if ( empty( $date_string ) ) {
            return '';
        }

        // If format is specified and not 'auto', try to parse with that format
        if ( $format !== 'auto' ) {
            $date = DateTime::createFromFormat( $format, $date_string );
            if ( $date !== false ) {
                return $date->format( 'Y-m-d' );
            }
            // If specified format fails, fall through to auto-detection
        }

        // Auto-detection: Try multiple common formats
        $formats_to_try = [
            'Y-m-d',           // 2024-01-15
            'Y-m-d H:i:s',     // 2024-01-15 14:30:00
            'm/d/Y',           // 01/15/2024
            'd/m/Y',           // 15/01/2024
            'F j, Y',          // January 15, 2024
            'j M Y',           // 15 Jan 2024
            'M j, Y',          // Jan 15, 2024
            'j F Y',           // 15 January 2024
            'd-m-Y',           // 15-01-2024
            'm-d-Y',           // 01-15-2024
        ];

        foreach ( $formats_to_try as $try_format ) {
            $date = DateTime::createFromFormat( $try_format, $date_string );
            if ( $date !== false ) {
                return $date->format( 'Y-m-d' );
            }
        }

        // Fallback to strtotime for other formats
        $timestamp = strtotime( $date_string );
        if ( $timestamp !== false ) {
            return gmdate( 'Y-m-d', $timestamp );
        }

        return '';
    }

    /**
     * Convert boolean-like values to boolean
     */
    public static function normalize_boolean( $value ) {
        $value = strtolower( trim( $value ) );

        $true_values = [ 'true', 'yes', 'y', '1', 'on', 'enabled' ];
        $false_values = [ 'false', 'no', 'n', '0', 'off', 'disabled' ];

        if ( in_array( $value, $true_values ) ) {
            return true;
        } elseif ( in_array( $value, $false_values ) ) {
            return false;
        }

        return null;
    }

    /**
     * Split multi-value string (semicolon separated by default)
     */
    public static function split_multi_value( $value, $separator = ';' ) {
        if ( empty( $value ) ) {
            return [];
        }

        $values = explode( $separator, $value );
        return array_map( 'trim', $values );
    }

    /**
     * Log import activity
     */
    public static function log_import_activity( $session_id, $message, $level = 'info' ) {
        dt_write_log([
            'component' => 'dt_import',
            'session_id' => $session_id,
            'level' => $level,
            'message' => $message,
            'timestamp' => current_time( 'mysql' )
        ]);
    }
}
