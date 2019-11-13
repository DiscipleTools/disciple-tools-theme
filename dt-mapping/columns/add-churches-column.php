<?php
if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

function dt_mm_add_churches_column( $data ) {


    if ( isset( $_SERVER["SERVER_NAME"] ) ) {
        $url  = ( !isset( $_SERVER["HTTPS"] ) || @( $_SERVER["HTTPS"] != 'on' ) )
            ? 'http://'. sanitize_text_field( wp_unslash( $_SERVER["SERVER_NAME"] ) )
            : 'https://'. sanitize_text_field( wp_unslash( $_SERVER["SERVER_NAME"] ) );
        if ( isset( $_SERVER["REQUEST_URI"] ) ) {
            $url .= sanitize_text_field( wp_unslash( $_SERVER["REQUEST_URI"] ) );
        }
    }
    $url_path = trim( str_replace( get_site_url(), "", $url ), '/' );
    if ( strpos( $url_path, 'metrics/mapping' ) !== false ) {

        /**
         * Step 1
         * Extract the labels and data from the data section of the filter
         *
         * @note        No modification to this section needed.
         */
        $column_labels = $data['custom_column_labels'] ?? [];
        $column_data   = $data['custom_column_data'] ?? [];

        /**
         * Step 2
         * Get the next index/column to add
         * This will add this new column of data to the end of the list.
         *
         * @note        No modification to this section needed.
         * @note        To modify the order of the columns use the filter order
         *              found in the add_filters() function.
         * @example     Current load level 10
         *              add_filter( 'dt_mapping_module_data', 'dt_mm_add_contacts_column', 10, 1 );
         *              Change to load level 50 and thus move it down the column list. Which means it
         *              will load after 0-49, and in front of 51-1000+
         *              add_filter( 'dt_mapping_module_data', 'dt_mm_add_contacts_column', 50, 1 );
         */
        if ( empty( $column_labels ) ) {
            $next_column_number = 0;
        } else if ( count( $column_labels ) === 1 ) {
            $next_column_number = 1;
        } else {
            $next_column_number = count( $column_labels );
        }

        /**
         * Step 3
         * Add new label
         *
         * @note     Modify this! Add your column name and key.
         */
        $column_labels[ $next_column_number ] = [
            'key'   => 'churches',
            'label' => __( 'Churches', 'disciple_tools' )
        ];

        /**
         * Step 4
         * Add new column to existing data
         *
         * @note     No modification to this section needed.
         */
        if ( ! empty( $column_data ) ) {
            foreach ( $column_data as $key => $value ) {
                $column_data[$key][$next_column_number] = 0;
            }
        }

        /**
         * Step 5
         * Add new label and data column
         * This is the section you can loop through any content type
         * and add a new column of data for it. You want to only add grid_ids
         * that have a positive count value.
         *
         * @note    Modify this section!
         * @note    Don't add 0 values, or you might create unnecessary array and
         *          transfer weight to the mapping javascript file.
         */
        $results = Disciple_Tools_Mapping_Queries::get_location_grid_totals();
        if ( ! empty( $results ) ) {
            foreach ( $results as $result ) {
                if ( $result['type'] === 'churches' && $result['count'] > 0 ) { // filter for only contact and positive counts
                    $grid_id = $result['grid_id'];

                    // test if grid_id exists, else prepare it with 0 values
                    if ( ! isset( $column_data[ $grid_id ] ) ) {
                        $column_data[$grid_id] = [];
                        $i                         = 0;
                        while ( $i <= $next_column_number ) {
                            $column_data[$grid_id][$i] = 0;
                            $i ++;
                        }
                    }

                    // add new record to column
                    $column_data[$grid_id][$next_column_number] = (int) $result['count'] ?? 0; // must be string
                }
            }
        }

        /**
         * Step 6
         * Put back the modified labels and column data and return everything to the filter.
         *
         * @note    No modification to this section needed.
         */
        $data['custom_column_labels'] = $column_labels;
        $data['custom_column_data']   = $column_data;

    }
    return $data;
}
add_filter( 'dt_mapping_module_data', 'dt_mm_add_churches_column', 52, 1 );
