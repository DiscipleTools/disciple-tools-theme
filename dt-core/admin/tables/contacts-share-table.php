<?php
/**
 * Admin table for showing share
 */

/**
 * Display table function
 */
function dt_contact_share_table()
{

    $list_table = new MM_Table();
    //Fetch, prepare, sort, and filter our data...
    if ( isset( $_GET['s'] ) ) {
        $list_table->prepare_items( trim( sanitize_text_field( wp_unslash( $_GET['s'] ) ) ) );
    } else {
        $list_table->prepare_items();
    }

    ?>
    <div class="wrap">

        <div id="icon-users" class="icon32"><br/></div>
        <h2>Movement Mapping Table</h2>

        <!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->
        <form id="movement-mapping" method="get">
            <?php if (isset( $_REQUEST['page'] ) ): ?>
            <input type="hidden" name="page" value="<?php echo esc_attr( sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) ); ?>"/>
            <?php endif; ?>
            <?php $list_table->search_box( 'Search Table', 'movement-mapping' ); ?>
            <?php $list_table->display() ?>

        </form>

    </div>
    <?php
}

/**
 * Make sure wp-list-table is loaded
 */
if ( !class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/**
 * Class Disciple_Tools_Contact_Share_Table
 */
class Disciple_Tools_Contact_Share_Table extends WP_List_Table
{
    /**
     * Disciple_Tools_Contact_Share_Table constructor.
     */
    function __construct()
    {
        global $status, $page;

        //Set parent defaults
        parent::__construct( [
            'singular' => 'location',     //singular name of the listed records
            'plural'   => 'locations',    //plural name of the listed records
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
            case 'WorldID':
            case 'Zone_Name':
            case 'CntyID':
            case 'Cnty_Name':
            case 'Adm1ID':
            case 'Adm1_Name':
            case 'Adm2ID':
            case 'Adm2_Name':
            case 'Adm3ID':
            case 'Adm3_Name':
            case 'Adm4ID':
            case 'Adm4_Name':
            case 'World':
            case 'Region':
            case 'Field':
            case 'Notes':
            case 'Last_Sync':
            case 'Sync_Source':
                return $item[ $column_name ];
            case 'Center':
                return !empty( $item['Cen_x'] ) ? '<a href="https://www.google.com/maps/@' . $item['Cen_y'] . ',' . $item['Cen_x'] . ',10z" target="_blank">' . $item['Cen_x'] . ', ' . $item['Cen_y'] . '</a>' : '';
            case 'geometry':
                return empty( $item[ $column_name ] ) ? 'No' : 'Yes';
            case 'Source_Key':
                return !empty( $item[ $column_name ] ) && ( $item['Sync_Source'] == '4KArcGIS' ) ? $item[ $column_name ] . ' (<a href="https://services1.arcgis.com/DnZ5orhsUGGdUZ3h/ArcGIS/rest/services/OmegaZones082016/FeatureServer/0/query?outFields=*&returnGeometry=true&resultRecordCount=1&f=html&where=WorldID=\'' . $item['WorldID'] . '\'">html</a>, <a href="https://services1.arcgis.com/DnZ5orhsUGGdUZ3h/ArcGIS/rest/services/OmegaZones082016/FeatureServer/0/query?outFields=*&returnGeometry=true&resultRecordCount=1&f=pgeojson&where=WorldID=\'' . $item['WorldID'] . '\'">json</a>)' : $item[ $column_name ];
            case 'Population':
                return !empty( $item[ $column_name ] ) ? number_format_i18n( $item[ $column_name ] ) : '';
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
        $actions = [
            //            'edit'      => sprintf('<a href="?page=%s&action=%s&location=%s">Edit</a>',$_REQUEST['page'],'edit',$item['ID']),
            //            'delete'    => sprintf('<a href="?page=%s&action=%s&location=%s">Delete</a>',$_REQUEST['page'],'delete',$item['ID']),
        ];

        //Return the title contents
        return sprintf( '%1$s <span style="color:silver">(id:%2$s)</span>%3$s',
            /*$1%s*/
            $item['Zone_name'],
            /*$2%s*/
            $item['WorldID'],
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
            $this->_args['singular'],  //Let's simply repurpose the table's singular label ("locations")
            /*$2%s*/
            $item['WorldID']                //The value of the checkbox should be the record's id
        );
    }

    /**
     * @return array
     */
    function get_columns()
    {
        $columns = [
            'cb'          => '<input type="checkbox" />', //Render a checkbox instead of text
            'WorldID'     => 'WorldID',
            'Zone_Name'   => 'Zone Name',
            'CntyID'      => 'CntyID',
            'Cnty_Name'   => 'Cnty_Name',
            //            'Adm1ID'        => 'Adm1ID',
            //            'Adm1_Name'     => 'Adm1_Name',
            //            'Adm2ID'        => 'Adm2ID',
            //            'Adm2_Name'     => 'Adm2_Name',
            //            'Adm3ID'        => 'Adm3ID',
            //            'Adm3_Name'     => 'Adm3_Name',
            //            'Adm4ID'        => 'Adm4ID',
            //            'Adm4_Name'     => 'Adm4_Name',
            //            'World'         => 'World',
            'Population'  => 'Population',
            'Center'      => 'Center',
            'Region'      => 'Region',
            'Field'       => 'Field',
            'geometry'    => 'geometry',
            //            'Notes'         => 'Notes',
            'Last_Sync'   => 'Last_Sync',
            'Sync_Source' => 'Sync_Source',
            'Source_Key'  => 'Source_Key',
        ];

        return $columns;
    }

    /**
     * @return array
     */
    function get_sortable_columns()
    {
        $sortable_columns = [
            'WorldID'    => [ 'WorldID', false ],     //true means it's already sorted
            'Zone_Name'  => [ 'Zone_Name', false ],
            'Population' => [ 'Population', false ],
            'CntyID'     => [ 'CntyID', false ],
            'Cnty_Name'  => [ 'Cnty_Name', false ],
            'geometry'   => [ 'geometry', false ],
            'Region'     => [ 'Region', false ],
            'Field'      => [ 'Field', false ],
            'Last_Sync'  => [ 'last_sync', false ],
            'Source_Key' => [ 'Source_Key', false ],
        ];

        return $sortable_columns;
    }

    /**
     * @return array
     */
    function get_bulk_actions()
    {
        $actions = [
            'sync' => 'Sync',
        ];

        return $actions;
    }

    function process_bulk_action()
    {

        //Detect when a bulk action is being triggered...
        if ( 'sync' === $this->current_action() && isset( $_GET['location'] ) ) {
            throw new Exception( "Unimplemented, what is mm_sync_by_oz_objectid?" );
            /* foreach( $_GET[ 'location' ] as $location ) { */
            /*     mm_sync_by_oz_objectid( $location ); */
            /* } */
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

        $total_items = $wpdb->get_var( "SELECT count(*) FROM $wpdb->mm" ); // get total items
        $current_page = $this->get_pagenum();// get current page
        $per_page = 20; // get items per page
        $page_start = (int) ( ( $current_page - 1 ) * $per_page ); // calculate starting item id

        $orderby = ( !empty( $_REQUEST['orderby'] ) ) ? sanitize_sql_orderby( wp_unslash( $_REQUEST['orderby'] ) ) : 'WorldID'; //If no sort, default to title
        $order = ( !empty( $_REQUEST['order'] ) ) ? sanitize_key( $_REQUEST['order'] ) : 'asc'; //If no order, default to asc

        if ( !preg_match( '/^[a-zA-Z_]+$/', $orderby ) ) {
            throw new Error( "To protect agains SQL injection attacks, only [a-zA-Z_]+ order arguments are accepted" );
        }

        if ( strtolower( $order ) != "asc" && strtolower( $order ) != "desc" ) {
            throw new Error( "order argument must be ASC or DESC" );
        }

        if ( empty( $search ) ) {

            $data = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT
                        *
                    FROM
                        `$wpdb->mm`
                    WHERE
                        1=1 "
                    // @codingStandardsIgnoreLine
                    . ( !empty( $_GET[ 'cnty-filter' ] ) ? 'AND CntyID = %1$s ' : '' )
                    . "ORDER BY "
                    // @codingStandardsIgnoreLine
                        . " `$orderby` $order
                    LIMIT "
                    // @codingStandardsIgnoreLine
                        . " $page_start, $per_page",
                    sanitize_text_field( wp_unslash( $_GET['cnty-filter'] ) )
                ),
                ARRAY_A
            );
        } else {
            // Trim Search Term
            $search = trim( $search );

            $where = '';
            if ( !empty( $_GET['cnty-filter'] ) ) {
                $where = sprintf( ' AND CntyID=%d', intval( $_GET['cnty-filter'] ) );
            }

            /* Notice how you can search multiple columns for your search term easily, and return one data set */
            $data = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT
                         *
                    FROM
                        `$wpdb->mm`
                    WHERE
                        `WorldID` LIKE %1\$s
                        OR `Zone_Name` LIKE %1\$s "
                    // @codingStandardsIgnoreLine
                    . ( !empty( $_GET[ 'cnty-filter' ] ) ? ' AND CntyID = %2$s ' : '' )
                    . "ORDER BY "
                    // @codingStandardsIgnoreLine
                        . " $orderby $order
                    ",
                    '%' . $wpdb->esc_like( $search ) . '%',
                    sanitize_text_field( wp_unslash( $_GET['cnty-filter'] ) )
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

    /**
     * @param string $which
     */
    function extra_tablenav( $which )
    {
        global $wpdb;

        if ( $which == "top" ) {
            ?>
            <div class="alignleft actions bulkactions">
                <?php
                $cnty = $wpdb->get_results( 'SELECT CntyID, Cnty_Name FROM ' . $wpdb->mm . ' GROUP BY CntyID, Cnty_Name ORDER BY Cnty_Name ASC', ARRAY_A );

                if ( $cnty ) {
                    ?>

                    <select name="cnty-filter" id="cnty-filter">

                        <option value="">Filter by Country</option>
                        <?php
                        foreach ( $cnty as $cat ) {
                            $selected = '';
                            if ( isset( $_GET['cnty-filter'] ) && sanitize_text_field( wp_unslash( $_GET['cnty-filter'] ) ) == $cat['CntyID'] ) {
                                $selected = ' selected = "selected"';
                            }
                            ?>
                            <option
                                <?php // @codingStandardsIgnoreLine ?>
                                value="<?php echo esc_attr( $cat[ 'CntyID' ] ); ?>" <?php echo $selected; ?>><?php echo esc_html( $cat[ 'Cnty_Name' ] ); ?></option>
                            <?php
                        }
                        ?>
                    </select>
                    <button class="button" type="submit">Filter</button>

                    <?php
                }
                ?>
            </div>
            <?php
        }
        if ( $which == "bottom" ) {
            //The code that goes after the table is there

        }
    }

}
