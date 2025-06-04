<?php
/**
 * DT CSV Import Mapping
 */

if ( !defined( 'ABSPATH' ) ) {
    exit;
}

class DT_CSV_Import_Mapping {

    /**
     * Analyze CSV columns and suggest field mappings
     */
    public static function analyze_csv_columns( $csv_data, $post_type ) {
        if ( empty( $csv_data ) ) {
            return [];
        }

        $headers = array_shift( $csv_data );
        $field_settings = DT_Posts::get_post_field_settings( $post_type );

        $mapping_suggestions = [];

        foreach ( $headers as $index => $column_name ) {
            $suggestion = self::suggest_field_mapping( $column_name, $field_settings );
            $sample_data = DT_Import_Utilities::get_sample_data( $csv_data, $index, 5 );

            $mapping_suggestions[$index] = [
                'column_name' => $column_name,
                'suggested_field' => $suggestion,
                'sample_data' => $sample_data,
                'confidence' => $suggestion ? self::calculate_confidence( $column_name, $suggestion, $field_settings ) : 0
            ];
        }

        return $mapping_suggestions;
    }

    /**
     * Suggest field mapping for a column
     */
    private static function suggest_field_mapping( $column_name, $field_settings ) {
        $column_normalized = DT_Import_Utilities::normalize_string( $column_name );

        // Direct field name matches
        foreach ( $field_settings as $field_key => $field_config ) {
            $field_normalized = DT_Import_Utilities::normalize_string( $field_config['name'] );

            // Exact match
            if ( $column_normalized === $field_normalized ) {
                return $field_key;
            }

            // Field key match
            if ( $column_normalized === DT_Import_Utilities::normalize_string( $field_key ) ) {
                return $field_key;
            }
        }

        // Partial matches
        foreach ( $field_settings as $field_key => $field_config ) {
            $field_normalized = DT_Import_Utilities::normalize_string( $field_config['name'] );

            if ( strpos( $field_normalized, $column_normalized ) !== false ||
                strpos( $column_normalized, $field_normalized ) !== false ) {
                return $field_key;
            }
        }

        // Common aliases
        $aliases = self::get_field_aliases();
        foreach ( $aliases as $field_key => $field_aliases ) {
            foreach ( $field_aliases as $alias ) {
                if ( $column_normalized === DT_Import_Utilities::normalize_string( $alias ) ) {
                    return $field_key;
                }
            }
        }

        return null;
    }

    /**
     * Calculate confidence score for field mapping
     */
    private static function calculate_confidence( $column_name, $field_key, $field_settings ) {
        $column_normalized = DT_Import_Utilities::normalize_string( $column_name );
        $field_config = $field_settings[$field_key];
        $field_normalized = DT_Import_Utilities::normalize_string( $field_config['name'] );
        $field_key_normalized = DT_Import_Utilities::normalize_string( $field_key );

        // Exact matches get highest confidence
        if ( $column_normalized === $field_normalized || $column_normalized === $field_key_normalized ) {
            return 100;
        }

        // Partial matches get medium confidence
        if ( strpos( $field_normalized, $column_normalized ) !== false ||
            strpos( $column_normalized, $field_normalized ) !== false ) {
            return 75;
        }

        // Alias matches get lower confidence
        $aliases = self::get_field_aliases();
        if ( isset( $aliases[$field_key] ) ) {
            foreach ( $aliases[$field_key] as $alias ) {
                if ( $column_normalized === DT_Import_Utilities::normalize_string( $alias ) ) {
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
            'title' => [ 'name', 'full_name', 'contact_name', 'fullname', 'person_name' ],
            'contact_phone' => [ 'phone', 'telephone', 'mobile', 'cell', 'phone_number' ],
            'contact_email' => [ 'email', 'e-mail', 'email_address', 'mail' ],
            'assigned_to' => [ 'assigned', 'worker', 'assigned_worker', 'owner' ],
            'overall_status' => [ 'status', 'contact_status' ],
            'seeker_path' => [ 'seeker', 'spiritual_status', 'faith_status' ],
            'baptism_date' => [ 'baptized', 'baptism', 'baptized_date' ],
            'location_grid' => [ 'location', 'address', 'city', 'country' ],
            'contact_address' => [ 'address', 'street_address', 'home_address' ],
            'age' => [ 'years_old', 'years' ],
            'gender' => [ 'sex' ],
            'reason_paused' => [ 'paused_reason', 'pause_reason' ],
            'reason_unassignable' => [ 'unassignable_reason' ],
            'tags' => [ 'tag', 'labels', 'categories' ]
        ];
    }

    /**
     * Get available options for a field
     */
    public static function get_field_options( $field_key, $field_config ) {
        if ( !in_array( $field_config['type'], [ 'key_select', 'multi_select' ] ) ) {
            return [];
        }

        return $field_config['default'] ?? [];
    }

    /**
     * Validate field mapping configuration
     */
    public static function validate_mapping( $mapping_data, $post_type ) {
        $errors = [];
        $field_settings = DT_Posts::get_post_field_settings( $post_type );

        foreach ( $mapping_data as $column_index => $mapping ) {
            if ( empty( $mapping['field_key'] ) || $mapping['field_key'] === 'skip' ) {
                continue;
            }

            $field_key = $mapping['field_key'];

            // Check if field exists
            if ( !isset( $field_settings[$field_key] ) ) {
                $errors[] = sprintf(
                    __( 'Field "%1$s" does not exist for post type "%2$s"', 'disciple_tools' ),
                    $field_key,
                    $post_type
                );
                continue;
            }

            $field_config = $field_settings[$field_key];

            // Validate field-specific configuration
            if ( in_array( $field_config['type'], [ 'key_select', 'multi_select' ] ) ) {
                if ( isset( $mapping['value_mapping'] ) ) {
                    foreach ( $mapping['value_mapping'] as $csv_value => $dt_value ) {
                        if ( !empty( $dt_value ) && !isset( $field_config['default'][$dt_value] ) ) {
                            $errors[] = sprintf(
                                __( 'Invalid option "%1$s" for field "%2$s"', 'disciple_tools' ),
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

    /**
     * Generate unique values from CSV column for mapping
     */
    public static function get_unique_column_values( $csv_data, $column_index ) {
        $values = [];

        foreach ( $csv_data as $row ) {
            if ( isset( $row[$column_index] ) && !empty( trim( $row[$column_index] ) ) ) {
                $value = trim( $row[$column_index] );

                // For multi-value fields, split by semicolon
                $split_values = DT_Import_Utilities::split_multi_value( $value );
                foreach ( $split_values as $split_value ) {
                    if ( !empty( $split_value ) ) {
                        $values[$split_value] = $split_value;
                    }
                }
            }
        }

        return array_values( $values );
    }

    /**
     * Suggest value mappings for key_select and multi_select fields
     */
    public static function suggest_value_mappings( $csv_values, $field_options ) {
        $mappings = [];

        foreach ( $csv_values as $csv_value ) {
            $csv_normalized = DT_Import_Utilities::normalize_string( $csv_value );
            $best_match = null;
            $best_score = 0;

            foreach ( $field_options as $option_key => $option_config ) {
                $option_label = $option_config['label'] ?? $option_key;
                $option_normalized = DT_Import_Utilities::normalize_string( $option_label );

                // Exact match
                if ( $csv_normalized === $option_normalized ) {
                    $best_match = $option_key;
                    $best_score = 100;
                    break;
                }

                // Partial match
                if ( strpos( $option_normalized, $csv_normalized ) !== false ||
                    strpos( $csv_normalized, $option_normalized ) !== false ) {
                    if ( $best_score < 75 ) {
                        $best_match = $option_key;
                        $best_score = 75;
                    }
                }
            }

            $mappings[$csv_value] = [
                'suggested_option' => $best_match,
                'confidence' => $best_score
            ];
        }

        return $mappings;
    }

    /**
     * Validate connection field values
     */
    public static function validate_connection_values( $csv_values, $connection_post_type ) {
        $valid_connections = [];
        $invalid_connections = [];

        foreach ( $csv_values as $csv_value ) {
            // Try to find by ID first
            if ( is_numeric( $csv_value ) ) {
                $post = DT_Posts::get_post( $connection_post_type, intval( $csv_value ), true, false );
                if ( !is_wp_error( $post ) ) {
                    $valid_connections[$csv_value] = $post['ID'];
                    continue;
                }
            }

            // Try to find by title/name
            $posts = DT_Posts::list_posts($connection_post_type, [
                'name' => $csv_value,
                'limit' => 1
            ]);

            if ( !is_wp_error( $posts ) && !empty( $posts['posts'] ) ) {
                $valid_connections[$csv_value] = $posts['posts'][0]['ID'];
            } else {
                $invalid_connections[] = $csv_value;
            }
        }

        return [
            'valid' => $valid_connections,
            'invalid' => $invalid_connections
        ];
    }

    /**
     * Validate user field values
     */
    public static function validate_user_values( $csv_values ) {
        $valid_users = [];
        $invalid_users = [];

        foreach ( $csv_values as $csv_value ) {
            $user = null;

            // Try to find by ID first
            if ( is_numeric( $csv_value ) ) {
                $user = get_user_by( 'id', intval( $csv_value ) );
            }

            // Try to find by username
            if ( !$user ) {
                $user = get_user_by( 'login', $csv_value );
            }

            // Try to find by display name
            if ( !$user ) {
                $user = get_user_by( 'display_name', $csv_value );
            }

            if ( $user ) {
                $valid_users[$csv_value] = $user->ID;
            } else {
                $invalid_users[] = $csv_value;
            }
        }

        return [
            'valid' => $valid_users,
            'invalid' => $invalid_users
        ];
    }
}
