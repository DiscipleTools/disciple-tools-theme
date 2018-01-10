<?php
/**
 * Admin table for showing notification
 */

/**
 * Make sure wp-list-table is loaded
 */
if ( !class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/**
 * Class Disciple_Tools_Contact_Share_Table
 */
class Disciple_Tools_Notifications_Table extends WP_List_Table
{
    /**
     * Disciple_Tools_Notifications_Table constructor.
     */
    function __construct()
    {
        global $status, $page;

        //Set parent defaults
        parent::__construct( [
            'singular' => 'notification',     //singular name of the listed records
            'plural'   => 'notifications',    //plural name of the listed records
            'ajax'     => false        //does this table support ajax?
        ] );
    }

    /**
     * @param object $item
     * @param string $column_name
     *
     * @return mixed|string
     */
    function column_default( $item, $column_name )
    {
        switch ( $column_name ) {
            case 'notification_name':
            case 'notification_action':
            case 'date_notified':
            case 'notification_note':
                return $item[ $column_name ];
            case 'user_id':
//                return dt_get_user_display_name( $item[ $column_name ] );
                break;
            case 'is_new':
                return $item[ $column_name ] ? 'Yes' : 'No';
                break;
            case 'item_id':
                if ( $item['notification_name'] == 'mention' ) {
                    $comment = get_comment( $item[ $column_name ] );

                    return '<a href="' . home_url( '/contacts/' ) . $comment->comment_post_ID . '">' . $comment->comment_content . '</a>';
                } elseif ( $item['notification_name'] == 'field_update' ) {
                    return $item[ $column_name ];
                } elseif ( $item['notification_name'] == 'follow_activity' ) {
                    return $item[ $column_name ];
                }
                break;
            case 'secondary_item_id':
                if ( $item['notification_name'] == 'mention' ) {
                    $post_object = get_post( $item[ $column_name ] );

                    return '<a href="' . $post_object->guid . '">' . $post_object->post_title . '</a>';
                } elseif ( $item['notification_name'] == 'field_update' ) {
                    $post_object = get_post( $item[ $column_name ] );

                    return '<a href="' . $post_object->guid . '">' . $post_object->post_title . '</a>';
                } elseif ( $item['notification_name'] == 'follow_activity' ) {
                    $post_object = get_post( $item[ $column_name ] );

                    return '<a href="' . $post_object->guid . '">' . $post_object->post_title . '</a>';
                }
                break;
            default:
                return print_r( $item, true ); //Show the whole array for troubleshooting purposes
        }
    }

    /**
     * @param $item
     *
     * @return string
     */
    function column_title( $item )
    {

        //Build row actions
        if ( ! isset( $_REQUEST['page'] )) {
            throw new Exception( "Expected page to be set" );
        }
        $actions = [
            'edit'   => sprintf( '<a href="?page=%s&action=%s&notification=%s">Edit</a>', sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ), 'edit', $item['ID'] ),
            'delete' => sprintf( '<a href="?page=%s&action=%s&notification=%s">Delete</a>', sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ), 'delete', $item['ID'] ),
        ];

        //Return the title contents
        return sprintf( '%1$s <span style="color:silver">(id:%2$s)</span>%3$s',
            /*$1%s*/
            $item['notification_name'],
            /*$2%s*/
            $item['notification_action'],
            /*$3%s*/
            $this->row_actions( $actions )
        );
    }

    /**
     * @param object $item
     *
     * @return string
     */
    function column_cb( $item )
    {
        return sprintf(
            '<input type="checkbox" name="%1$s[]" value="%2$s" />',
            /*$1%s*/
            $this->_args['singular'],  //Let's simply repurpose the table's singular label ("notifications")
            /*$2%s*/
            $item['id']                //The value of the checkbox should be the record's id
        );
    }

    /**
     * @return array
     */
    function get_columns()
    {
        $columns = [
            'cb'                  => '<input type="checkbox" />', //Render a checkbox instead of text
            'user_id'             => 'User',
            'item_id'             => 'Item_ID',
            'secondary_item_id'   => 'Secondary',
            'notification_name'   => 'Name',
            'notification_action' => 'Action',
            'notification_note'   => 'Note',
            'date_notified'       => 'Date',
            'is_new'              => 'New',
        ];

        return $columns;
    }

    /**
     * @return array
     */
    function get_sortable_columns()
    {
        $sortable_columns = [
            'user_id'             => [ 'user_id', false ],     //true means it's already sorted
            'item_id'             => [ 'item_id', false ],
            'secondary_item_id'   => [ 'secondary_item_id', false ],
            'notification_name'   => [ 'notification_name', false ],
            'notification_action' => [ 'notification_action', false ],
            'date_notified'       => [ 'date_notified', false ],
            'notification_note'   => [ 'notification_note', false ],
            'is_new'              => [ 'is_new', false ],
        ];

        return $sortable_columns;
    }

    /**
     * @return array
     */
    function get_bulk_actions()
    {
        $actions = [
            'viewed' => 'Viewed',
        ];

        return $actions;
    }

    function process_bulk_action()
    {

        //Detect when a bulk action is being triggered...
        if ( 'viewed' === $this->current_action() && isset( $_GET['notification'] ) ) {
//            Disciple_Tools_Notifications::mark_notification_viewed( sanitize_text_field( wp_unslash( $_GET['notification'] ) ) );
        }
    }

    /**
     * @param null $search
     *
     */
    function prepare_items( $search = null )
    {
        global $wpdb; //This is used only if making any database queries

        $columns = $this->get_columns(); // prepare columns
        $hidden = [];
        $sortable = $this->get_sortable_columns();

        $this->_column_headers = [ $columns, $hidden, $sortable ]; // construct column headers to wp_table

        $this->process_bulk_action(); // construct bulk actions

        $total_items = $wpdb->get_var( "SELECT count(*) FROM $wpdb->dt_notifications" ); // get total items
        $current_page = $this->get_pagenum();// get current page
        $per_page = 20; // get items per page
        $page_start = (int) ( ( $current_page - 1 ) * $per_page ); // calculate starting item id

        $orderby = ( !empty( $_REQUEST['orderby'] ) ) ? sanitize_sql_orderby( wp_unslash( $_REQUEST['orderby'] ) ) : 'date_notified'; //If no sort, default to title
        $order = ( !empty( $_REQUEST['order'] ) ) ? sanitize_key( $_REQUEST['order'] ) : 'asc'; //If no order, default to asc

        if ( !preg_match( '/^[a-zA-Z_]+$/', $orderby ) ) {
            throw new Error( "To protect agains SQL injection attacks, only [a-zA-Z_]+ order arguments are accepted" );
        }

        if ( strtolower( $order ) != "asc" && strtolower( $order ) != "desc" ) {
            throw new Error( "order argument must be ASC or DESC" );
        }

        if ( empty( $search ) ) {

            $data = $wpdb->get_results(
                "SELECT
                    *
                FROM
                    `$wpdb->dt_notifications`
                ORDER BY "
                    // @codingStandardsIgnoreLine
                    . " `$orderby` $order
                LIMIT "
                    // @codingStandardsIgnoreLine
                    . " $page_start, $per_page",
                ARRAY_A
            );
        } else {
            // Trim Search Term
            $search = trim( $search );

            /* Notice how you can search multiple columns for your search term easily, and return one data set */
            $data = $wpdb->get_results(
                $wpdb->prepare( "
                    SELECT
                        *
                    FROM
                        `$wpdb->dt_notifications`
                    WHERE
                        `notification_name` LIKE %1\$s
                        OR `notification_action` LIKE %1\$s
                    ORDER BY "
                        // @codingStandardsIgnoreLine
                        . " `$orderby` $order
                    ",
                    '%' . $wpdb->esc_like( $search ) . '%'
                ),
                ARRAY_A
            );

            $total_items = count( $data );
            $per_page = $total_items;
        }

        $this->items = $data;

        $this->set_pagination_args( [
            'total_items' => $total_items,                  //WE have to calculate the total number of items
            'per_page'    => $per_page,                     //WE have to determine how many items to show on a page
            'total_pages' => $total_items > 0 ? $total_items / $per_page : '1', //WE have to calculate the total number of pages
        ] );
    }

}

/**
 * Display table function
 */
function dt_notifications_table()
{

    $list_table = new Disciple_Tools_Notifications_Table();
    //Fetch, prepare, sort, and filter our data...
    if ( isset( $_GET['s'] ) ) {
        $list_table->prepare_items( trim( sanitize_text_field( wp_unslash( $_GET['s'] ) ) ) );
    } else {
        $list_table->prepare_items();
    }

    ?>
    <div class="wrap">


        <div id="icon-users" class="icon32"><br/></div>
        <h2>Notifications System</h2>


        <!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->
        <form id="notifications" method="get">
            <?php if (isset( $_REQUEST['page'] )): ?>
            <input type="hidden" name="page" value="<?php echo esc_attr( sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) ); ?>"/>
            <?php endif; ?>
            <?php $list_table->search_box( 'Search Table', 'notifications' ); ?>
            <?php $list_table->display() ?>

        </form>

    </div>
    <?php
}
