<?php
/**
 * DT CSV Import Mapping
 */

if ( !defined( 'ABSPATH' ) ) {
    exit;
}

class DT_CSV_Import_Mapping {

    /**
     * Common field headings based on the plugin's comprehensive detection
     */
    private static $field_headings = [
        'contact_phone' => [
            'contact_phone',
            'phone',
            'mobile',
            'telephone',
            'cell',
            'phone_number',
            'tel',
            'cellular',
            'mobile_phone',
            'home_phone',
            'work_phone',
            'primary_phone'
        ],
        'contact_email' => [
            'contact_email',
            'email',
            'e-mail',
            'email_address',
            'mail',
            'e_mail',
            'primary_email',
            'work_email',
            'home_email'
        ],
        'contact_address' => [
            'contact_address',
            'address',
            'street_address',
            'home_address',
            'work_address',
            'mailing_address',
            'physical_address'
        ],
        'name' => [
            'title',
            'name',
            'contact_name',
            'full_name',
            'fullname',
            'person_name',
            'first_name',
            'last_name',
            'display_name',
            'firstname',
            'lastname',
            'given_name',
            'family_name',
            'client_name'
        ],
        'gender' => [
            'gender',
            'sex'
        ],
        'notes' => [
            'notes',
            'note',
            'comments',
            'comment',
            'cf_notes',
            'description',
            'remarks',
            'additional_info'
        ]
    ];

    /**
     * Analyze CSV columns and suggest field mappings
     */
    public static function analyze_csv_columns( $csv_data, $post_type ) {
        if ( empty( $csv_data ) ) {
            return [];
        }

        $headers = array_shift( $csv_data );
        $field_settings = DT_Posts::get_post_field_settings( $post_type );
        $post_settings = DT_Posts::get_post_settings( $post_type );

        $mapping_suggestions = [];

        foreach ( $headers as $index => $column_name ) {
            $suggestion = self::suggest_field_mapping( $column_name, $field_settings, $post_settings, $post_type );
            $sample_data = DT_CSV_Import_Utilities::get_sample_data( $csv_data, $index, 5 );

            // Validate that the suggested field actually exists in field settings
            if ( $suggestion && !isset( $field_settings[$suggestion] ) ) {
                $suggestion = null;
            }

            $mapping_suggestions[$index] = [
                'column_name' => $column_name,
                'suggested_field' => $suggestion,
                'sample_data' => $sample_data,
                'has_match' => !is_null( $suggestion )
            ];
        }

        return $mapping_suggestions;
    }

    /**
     * Enhanced field mapping suggestion using the plugin's comprehensive approach
     */
    private static function suggest_field_mapping( $column_name, $field_settings, $post_settings, $post_type ) {
        $column_normalized = self::normalize_string_for_matching( $column_name );

        // Get post type prefix for channel fields
        $post_type_object = get_post_type_object( $post_type );
        $post_type_labels = get_post_type_labels( $post_type_object );
        $post_label_singular = $post_type_labels->singular_name ?? $post_type;
        $prefix = sprintf( '%s_', strtolower( $post_label_singular ) );

        // Step 1: Check predefined field headings (highest priority)
        foreach ( self::$field_headings as $field_key => $headings ) {
            foreach ( $headings as $heading ) {
                if ( $column_normalized === self::normalize_string_for_matching( $heading ) ) {
                    // For communication channels, add prefix if needed
                    if ( in_array( $field_key, [ 'contact_phone', 'contact_email' ] ) ) {
                        $base_field = str_replace( 'contact_', '', $field_key );
                        $channels = $post_settings['channels'] ?? [];
                        if ( isset( $channels[$base_field] ) ) {
                            return $field_key;
                        }
                    }

                    return $field_key;
                }
            }
        }

        // Step 2: Direct field key match
        if ( isset( $field_settings[$column_normalized] ) ) {
            return $column_normalized;
        }

        // Step 3: Channel field match (with and without prefix)
        $channels = $post_settings['channels'] ?? [];

        // Try without prefix first
        if ( isset( $channels[$column_normalized] ) ) {
            return $prefix . $column_normalized;
        }

        // Try with prefix removed
        $column_without_prefix = str_replace( $prefix, '', $column_normalized );
        if ( isset( $channels[$column_without_prefix] ) ) {
            return $prefix . $column_without_prefix;
        }

        // Step 4: Field name/label matching
        foreach ( $field_settings as $field_key => $field_config ) {
            $field_name_normalized = self::normalize_string_for_matching( $field_config['name'] ?? '' );

            // Exact match
            if ( $column_normalized === $field_name_normalized ) {
                return $field_key;
            }

            // Field key normalized match
            if ( $column_normalized === self::normalize_string_for_matching( $field_key ) ) {
                return $field_key;
            }
        }

        // Step 5: Channel name/label matching
        foreach ( $channels as $channel_key => $channel_config ) {
            $channel_name_normalized = self::normalize_string_for_matching( $channel_config['label'] ?? $channel_config['name'] ?? '' );

            if ( $column_normalized === $channel_name_normalized ) {
                return $prefix . $channel_key;
            }
        }

        // Step 6: Extended field aliases (moved before partial matching to handle ambiguity)
        $aliases = self::get_field_aliases( $post_type );
        $potential_matches = [];

        foreach ( $aliases as $field_key => $field_aliases ) {
            foreach ( $field_aliases as $alias ) {
                if ( $column_normalized === self::normalize_string_for_matching( $alias ) ) {
                    $potential_matches[] = $field_key;
                }
            }
        }

        // If we have exactly one match, return it
        if ( count( $potential_matches ) === 1 ) {
            return $potential_matches[0];
        }

        // If we have multiple matches, it's ambiguous - don't auto-map
        if ( count( $potential_matches ) > 1 ) {
            return null;
        }

        // Step 7: Partial matches for field names (more restrictive - only if column is a significant portion)
        foreach ( $field_settings as $field_key => $field_config ) {
            $field_name_normalized = self::normalize_string_for_matching( $field_config['name'] ?? '' );

            if ( !empty( $field_name_normalized ) && !empty( $column_normalized ) ) {
                // Only match if the column name is at least 50% of the field name
                // and the field name is not too much longer than the column name
                $column_len = strlen( $column_normalized );
                $field_len = strlen( $field_name_normalized );

                if ( $column_len >= 3 && // minimum meaningful length
                     $column_len >= ( $field_len * 0.5 ) && // column is at least 50% of field length
                     $field_len <= ( $column_len * 2 ) && // field is not more than 2x column length
                     ( strpos( $field_name_normalized, $column_normalized ) !== false ||
                       strpos( $column_normalized, $field_name_normalized ) !== false ) ) {
                    return $field_key;
                }
            }
        }

        // If we have no alias matches, return null
        return null;
    }

    /**
     * Normalize string for matching (more aggressive than the existing normalize_string)
     */
    private static function normalize_string_for_matching( $string ) {
        return strtolower( trim( preg_replace( '/[^a-zA-Z0-9]/', '', $string ) ) );
    }

    /**
     * Enhanced field aliases with more comprehensive mappings
     */
    private static function get_field_aliases( $post_type = 'contacts' ) {
        $base_aliases = [
            // Contact fields
            'name' => [
                'title',
        'full_name',
        'contact_name',
        'fullname',
        'person_name',
                'display_name',
        'first_name',
        'last_name',
        'firstname',
        'lastname',
                'given_name',
        'family_name',
        'client_name'
            ],
            'contact_phone' => [
                'phone',
            'telephone',
            'mobile',
            'cell',
            'phone_number',
            'tel',
                'cellular',
            'mobile_phone',
            'home_phone',
            'work_phone',
            'primary_phone',
                'phone1',
            'phone2',
            'main_phone'
            ],
            'contact_email' => [
                'email',
            'e-mail',
            'email_address',
            'mail',
            'e_mail',
                'primary_email',
            'work_email',
            'home_email',
            'email1',
            'email2'
            ],
            'contact_address' => [
                'address',
            'street_address',
            'home_address',
            'work_address',
                'mailing_address',
            'physical_address',
            'location',
            'addr'
            ],
            'assigned_to' => [
                'assigned',
            'worker',
            'assigned_worker',
            'owner',
            'responsible',
                'coach',
            'leader',
            'assigned_user',
            'staff'
            ],
            'overall_status' => [
                'status',
            'contact_status',
            'stage',
            'phase'
            ],
            'seeker_path' => [
                'seeker',
            'spiritual_status',
            'faith_status',
            'seeker_status',
                'spiritual_stage',
            'faith_journey'
            ],
            'baptism_date' => [
                'baptized',
            'baptism',
            'baptized_date',
            'baptism_date',
                'date_baptized',
            'water_baptism'
            ],
            'location_grid' => [
                'location',
            'city',
            'country',
            'state',
            'province',
            'region'
            ],
            'age' => [
                'years_old',
            'years',
            'age_range',
            'age_group'
            ],
            'gender' => [
                'sex',
            'male_female',
            'gender_identity'
            ],
            'reason_paused' => [
                'paused_reason',
            'pause_reason',
            'why_paused'
            ],
            'reason_unassignable' => [
                'unassignable_reason',
            'unassign_reason'
            ],
            'tags' => [
                'tag',
            'labels',
            'categories',
            'keywords',
            'tags'
            ],
            'notes' => [
                'note',
            'comments',
            'comment',
            'cf_notes',
            'description',
                'remarks',
            'additional_info',
            'notes_field',
            'memo'
            ],
            'sources' => [
                'source',
            'lead_source',
            'referral_source',
            'how_found'
            ],
            'milestones' => [
                'milestone',
            'achievements',
            'progress'
            ]
        ];

        // Add post-type specific aliases
        if ( $post_type === 'groups' ) {
            $base_aliases = array_merge( $base_aliases, [
                'group_type' => [ 'type', 'category', 'kind' ],
                'group_status' => [ 'status', 'state', 'phase' ],
                'start_date' => [ 'started', 'began', 'launch_date' ],
                'end_date' => [ 'ended', 'finished', 'completion_date' ],
                'members' => [ 'participants', 'attendees', 'people' ]
            ]);
        }

        return $base_aliases;
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
                $split_values = DT_CSV_Import_Utilities::split_multi_value( $value );
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
     * Enhanced value mapping suggestions with fuzzy matching
     */
    public static function suggest_value_mappings( $csv_values, $field_options ) {
        $mappings = [];

        foreach ( $csv_values as $csv_value ) {
            $csv_normalized = self::normalize_string_for_matching( $csv_value );
            $best_match = null;
            $best_score = 0;

            foreach ( $field_options as $option_key => $option_config ) {
                $option_label = $option_config['label'] ?? $option_key;
                $option_normalized = self::normalize_string_for_matching( $option_label );
                $option_key_normalized = self::normalize_string_for_matching( $option_key );

                // Exact match with label
                if ( $csv_normalized === $option_normalized ) {
                    $best_match = $option_key;
                    $best_score = 100;
                    break;
                }

                // Exact match with key
                if ( $csv_normalized === $option_key_normalized ) {
                    $best_match = $option_key;
                    $best_score = 95;
                    break;
                }

                // Partial match with label
                if ( !empty( $option_normalized ) &&
                     ( strpos( $option_normalized, $csv_normalized ) !== false ||
                       strpos( $csv_normalized, $option_normalized ) !== false ) ) {
                    if ( $best_score < 80 ) {
                        $best_match = $option_key;
                        $best_score = 80;
                    }
                }

                // Partial match with key
                if ( !empty( $option_key_normalized ) &&
                     ( strpos( $option_key_normalized, $csv_normalized ) !== false ||
                       strpos( $csv_normalized, $option_key_normalized ) !== false ) ) {
                    if ( $best_score < 75 ) {
                        $best_match = $option_key;
                        $best_score = 75;
                    }
                }
            }

            $mappings[$csv_value] = [
                'suggested_value' => $best_match
            ];
        }

        return $mappings;
    }

    /**
     * Validate connection values and check if they exist
     */
    public static function validate_connection_values( $csv_values, $connection_post_type ) {
        $validation_results = [];

        foreach ( $csv_values as $csv_value ) {
            $result = [
                'value' => $csv_value,
                'exists' => false,
                'id' => null,
                'suggestions' => []
            ];

            // Try to find by ID first
            if ( is_numeric( $csv_value ) ) {
                $post = DT_Posts::get_post( $connection_post_type, intval( $csv_value ), true, false );
                if ( !is_wp_error( $post ) ) {
                    $result['exists'] = true;
                    $result['id'] = intval( $csv_value );
                    $validation_results[] = $result;
                    continue;
                }
            }

            // Try to find by title/name
            $posts = DT_Posts::list_posts( $connection_post_type, [
                'name' => $csv_value,
                'limit' => 5
            ]);

            if ( !is_wp_error( $posts ) && !empty( $posts['posts'] ) ) {
                if ( count( $posts['posts'] ) === 1 ) {
                    $result['exists'] = true;
                    $result['id'] = $posts['posts'][0]['ID'];
                } else {
                    // Multiple matches - provide suggestions
                    foreach ( $posts['posts'] as $post ) {
                        $result['suggestions'][] = [
                            'id' => $post['ID'],
                            'name' => $post['title'] ?? $post['name'] ?? "Record #{$post['ID']}"
                        ];
                    }
                }
            }

            $validation_results[] = $result;
        }

        return $validation_results;
    }

    /**
     * Validate user values and check if they exist
     */
    public static function validate_user_values( $csv_values ) {
        $validation_results = [];

        foreach ( $csv_values as $csv_value ) {
            $result = [
                'value' => $csv_value,
                'exists' => false,
                'user_id' => null,
                'display_name' => null
            ];

            $user = null;

            // Try to find by ID
            if ( is_numeric( $csv_value ) ) {
                $user = get_user_by( 'id', intval( $csv_value ) );
            }

            // Try to find by email
            if ( !$user && filter_var( $csv_value, FILTER_VALIDATE_EMAIL ) ) {
                $user = get_user_by( 'email', $csv_value );
            }

            // Try to find by username
            if ( !$user ) {
                $user = get_user_by( 'login', $csv_value );
            }

            // Try to find by display name
            if ( !$user ) {
                $users = get_users( [ 'search' => $csv_value, 'search_columns' => [ 'display_name' ] ] );
                if ( !empty( $users ) ) {
                    $user = $users[0];
                }
            }

            if ( $user ) {
                $result['exists'] = true;
                $result['user_id'] = $user->ID;
                $result['display_name'] = $user->display_name;
            }

            $validation_results[] = $result;
        }

        return $validation_results;
    }
}
