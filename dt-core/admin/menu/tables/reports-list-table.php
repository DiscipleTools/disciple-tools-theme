<?php
/**
 * Reports List Table
 */

// WP_List_Table is not loaded automatically so we need to load it in our application
if ( !class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/**
 * Create a new table class that will extend the WP_List_Table
 */
class Disciple_Tools_Reports_List_Table extends WP_List_Table
{
    /**
     * Prepare the items for the table to process
     *
     */
    public function prepare_items()
    {
        $columns = $this->get_columns();
        $hidden = $this->get_hidden_columns();
        $sortable = $this->get_sortable_columns();

        $data = $this->get_activity_data();
        usort( $data, [ &$this, 'sort_data' ] );

        $per_page = 15;
        $current_page = $this->get_pagenum();
        $total_items = count( $data );

        $this->set_pagination_args(
            [
                'total_items' => $total_items,
                'per_page'    => $per_page,
            ]
        );

        $data = array_slice( $data, ( ( $current_page - 1 ) * $per_page ), $per_page );

        $this->_column_headers = [ $columns, $hidden, $sortable ];
        $this->items = $data;
    }

    /**
     * Override the parent columns method. Defines the columns to use in your listing table
     *
     * @return array
     */
    public function get_columns()
    {
        $columns = [
            'date'       => __( 'Date', 'disciple-tools' ),
            'source'     => __( 'Source', 'disciple-tools' ),
            'subsource'  => __( 'SubSource', 'disciple-tools' ),
            'meta_input' => __( 'Records', 'disciple-tools' ),
        ];

        return $columns;
    }

    /**
     * Define which columns are hidden
     *
     * @return array
     */
    public function get_hidden_columns()
    {
        return [];
    }

    /**
     * Define the sortable columns
     *
     * @return array
     */
    public function get_sortable_columns()
    {
        return [ 'date' => [ 'date', false ], 'source' => [ 'source', false ] ];
    }

    /**
     * Get the table data
     *
     * @return array
     */
    private function get_activity_data()
    {
        global $wpdb;

        $data = [];

        // Get all report detals
        $results = $wpdb->get_results(
            "SELECT
                *
            FROM
                `$wpdb->dt_reports`
            ORDER BY
                `report_date` desc
            LIMIT
                300",
            ARRAY_A
        );

        foreach ( $results as $result ) {
            $mapped_array = [
                'date'      => $result['report_date'],
                'source'    => $result['report_source'],
                'subsource' => $result['report_subsource'],
            ];

            // Get all report detals
            $meta_input_raw = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT
                        meta_key, meta_value
                    FROM
                        `$wpdb->dt_reportmeta`
                    WHERE
                        `report_id` = %s",
                    $result['id']
                ),
                ARRAY_A
            );

            $mapped_array['meta_input'] = $meta_input_raw;

            $data[] = $mapped_array;
        }

        return $data;
    }

    /**
     * Define what data to show on each column of the table
     *
     * @param array  $item        Data
     * @param string $column_name - Current column name
     *
     * @return Mixed
     */
    public function column_default( $item, $column_name )
    {
        switch ( $column_name ) {
            case 'date':
            case 'source':
            case 'subsource':
                return $item[ $column_name ];
            case 'meta_input':
                return print_r( $this->build_meta_input_list( $item['meta_input'] ), true );
            default:
                return print_r( $item, true );
        }
    }

    /**
     * @param $meta_input
     *
     * @return string
     */
    public function build_meta_input_list( $meta_input )
    {
        $html = '';
        foreach ( $meta_input as $value ) {
            $html .= $value['meta_key'] . ': ' . $value['meta_value'] . '<br>';
        }

        return $html;
    }

    /**
     * Allows you to sort the data by the variables set in the $_GET
     *
     * @param $a
     * @param $b
     *
     * @return int
     * @throws \Exception Expected order variable to be asc or desc.
     */
    private function sort_data( $a, $b )
    {
        // Set defaults
        $orderby = 'date';
        $order = 'desc';

        // If orderby is set, use this as the sort column
        if ( !empty( $_GET['orderby'] ) ) {
            $orderby = sanitize_sql_orderby( wp_unslash( $_GET['orderby'] ) );
            if ( !preg_match( $orderby, '/^[a-zA-Z][a-zA-Z0-9]*$/' ) ) {
                throw new Exception( "orderby variable contains weird input" );
            }
        }

        // If order is set use this as the order
        if ( !empty( $_GET['order'] ) ) {
            $order = sanitize_key( $_GET['order'] );
            if ( strtolower( $order ) != "asc" && strtolower( $order ) != "desc" ) {
                throw new Exception( "expected order variable to be asc or desc" );
            }
        }

        $result = strcmp( $a[ $orderby ], $b[ $orderby ] );

        if ( $order === 'asc' ) {
            return $result;
        }

        return -$result;
    }
}
