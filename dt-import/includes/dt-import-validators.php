<?php
/**
 * DT Import Validators
 */

if ( !defined( 'ABSPATH' ) ) {
    exit;
}

class DT_Import_Validators {

    /**
     * Validate CSV structure
     */
    public static function validate_csv_structure( $csv_data ) {
        $errors = [];

        if ( empty( $csv_data ) ) {
            $errors[] = __( 'CSV file is empty.', 'disciple_tools' );
            return $errors;
        }

        // Check if we have headers
        if ( count( $csv_data ) < 2 ) {
            $errors[] = __( 'CSV file must contain at least one header row and one data row.', 'disciple_tools' );
            return $errors;
        }

        $headers = $csv_data[0];
        $column_count = count( $headers );

        // Check for empty headers
        foreach ( $headers as $index => $header ) {
            if ( empty( trim( $header ) ) ) {
                $errors[] = sprintf( __( 'Column %d has an empty header.', 'disciple_tools' ), $index + 1 );
            }
        }

        // Check for duplicate headers
        $header_counts = array_count_values( $headers );
        foreach ( $header_counts as $header => $count ) {
            if ( $count > 1 ) {
                $errors[] = sprintf( __( 'Duplicate header found: "%s"', 'disciple_tools' ), $header );
            }
        }

        // Check row consistency
        $csv_data_count = count( $csv_data );
        for ( $i = 1; $i < $csv_data_count; $i++ ) {
            $row = $csv_data[$i];
            if ( count( $row ) !== $column_count ) {
                $errors[] = sprintf( __( 'Row %1$d has %2$d columns, expected %3$d columns.', 'disciple_tools' ), $i + 1, count( $row ), $column_count );
            }
        }

        return $errors;
    }

    /**
     * Validate field mapping data
     */
    public static function validate_field_mappings( $mappings, $post_type ) {
        $errors = [];
        $field_settings = DT_Posts::get_post_field_settings( $post_type );

        foreach ( $mappings as $column_index => $mapping ) {
            if ( empty( $mapping['field_key'] ) || $mapping['field_key'] === 'skip' ) {
                continue;
            }

            $field_key = $mapping['field_key'];

            // Check if field exists
            if ( !isset( $field_settings[$field_key] ) ) {
                $errors[] = sprintf( __( 'Field "%s" does not exist.', 'disciple_tools' ), $field_key );
                continue;
            }

            $field_config = $field_settings[$field_key];

            // Validate field-specific mappings
            if ( in_array( $field_config['type'], [ 'key_select', 'multi_select' ] ) && isset( $mapping['value_mapping'] ) ) {
                foreach ( $mapping['value_mapping'] as $csv_value => $dt_value ) {
                    if ( !empty( $dt_value ) && !isset( $field_config['default'][$dt_value] ) ) {
                        $errors[] = sprintf( __( 'Invalid option "%1$s" for field "%2$s".', 'disciple_tools' ), $dt_value, $field_config['name'] );
                    }
                }
            }
        }

        return $errors;
    }

    /**
     * Validate import session data
     */
    public static function validate_import_session( $session_data ) {
        $errors = [];

        $required_fields = [ 'csv_data', 'field_mappings', 'post_type' ];
        foreach ( $required_fields as $field ) {
            if ( !isset( $session_data[$field] ) ) {
                $errors[] = sprintf( __( 'Missing required session data: %s', 'disciple_tools' ), $field );
            }
        }

        return $errors;
    }
}
