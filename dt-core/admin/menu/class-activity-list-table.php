<?php
// WP_List_Table is not loaded automatically so we need to load it in our application
if ( !class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/**
 * Create a new table class that will extend the WP_List_Table
 *
 * @since 0.1.0
 */
class Disciple_Tools_Activity_List_Table extends WP_List_Table
{
    /**
     * Prepare the items for the table to process
     *
     * @since 0.1.0
     * @return void
     */
    public function prepare_items()
    {
        $columns = $this->get_columns();
        $hidden = $this->get_hidden_columns();
        $sortable = $this->get_sortable_columns();

        $data = $this->get_activity_data();
        usort( $data, [ &$this, 'sort_data' ] );

        $per_page = 20;
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
     * @since 0.1.0
     * @return array
     */
    public function get_columns()
    {
        $columns = [
            'date'        => __( 'Date', 'disciple-tools' ),
            'ID'          => __( 'ID', 'disciple-tools' ),
            //            'ip'          => __( 'IP', 'disciple-tools' ),
            'type'        => __( 'Type', 'disciple-tools' ),
            'label'       => __( 'SubType', 'disciple-tools' ),
            'action'      => __( 'Action', 'disciple-tools' ),
            'description' => __( 'Description', 'disciple-tools' ),
            'object_note' => __( 'Note', 'disciple-tools' ),
            'meta_id'     => __( 'Meta ID', 'disciple-tools' ),
            'meta_key'    => __( 'Meta Key', 'disciple-tools' ),
            'meta_value'  => __( 'Meta Value', 'disciple-tools' ),
            'meta_parent' => __( 'Meta Parent', 'disciple-tools' ),
            'author'      => __( 'Author', 'disciple-tools' ),
        ];

        return $columns;
    }

    /**
     * Define which columns are hidden
     *
     * @since 0.1.0
     * @return array
     */
    public function get_hidden_columns()
    {
        return [];
    }

    /**
     * Define the sortable columns
     *
     * @since 0.1.0
     * @return array
     */
    public function get_sortable_columns()
    {
        return [ 'type' => [ 'type', false ], 'date' => [ 'date', false ] ];
    }

    /**
     * Get the table data
     *
     * @since 0.1.0
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
                `$wpdb->dt_activity_log`
            ORDER BY
                `hist_time` desc
            ;",
            ARRAY_A
        );

        foreach ( $results as $result ) {
            $mapped_array = [
                'date'        => date( 'm/d/Y h:i:s', $result['hist_time'] ),
                'ID'          => $result['object_id'],
                'author'      => $result['user_id'],//dt_get_user_display_name($result['user_id']),
                'ip'          => $result['hist_ip'],
                'type'        => $result['object_type'],
                'label'       => $result['object_subtype'],
                'action'      => $result['action'],
                'description' => $result['object_name'],
                'meta_id'     => $result['meta_id'],
                'meta_key'    => $result['meta_key'],
                'meta_value'  => $result['meta_value'],
                'meta_parent' => $result['meta_parent'],
                'object_note' => $result['object_note'],
            ];

            $data[] = $mapped_array;
        }

        return $data;
    }

    /**
     * Define what data to show on each column of the table
     *
     * @since 0.1.0
     *
     * @param array  $item        Data
     * @param string $column_name - Current column name
     *
     * @return mixed
     */
    public function column_default( $item, $column_name )
    {
        switch ( $column_name ) {
            case 'date':
            case 'ID':
            case 'author':
            case 'ip':
            case 'type':
            case 'label':
            case 'action':
            case 'description':
            case 'meta_id':
            case 'meta_key':
            case 'meta_value':
            case 'meta_parent':
            case 'object_note':
                return $item[ $column_name ];
            default:
                return print_r( $item, true );
        }
    }

    /**
     * Allows you to sort the data by the variables set in the $_GET
     *
     * @since 0.1.0
     *
     * @param $a
     * @param $b
     *
     * @return int
     * @throws \Exception Orderby variable contains weird input.
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
                throw new Exception( "Orderby variable contains weird input." );
            }
        }

        // If order is set use this as the order
        if ( !empty( $_GET['order'] ) ) {
            $order = sanitize_key( $_GET['order'] );
            if ( strtolower( $order ) != "asc" && strtolower( $order ) != "desc" ) {
                throw new Exception( "expected order variable to be asc or desc." );
            }
        }

        $result = strcmp( $a[ $orderby ], $b[ $orderby ] );

        if ( $order === 'asc' ) {
            return $result;
        }

        return -$result;
    }
}
