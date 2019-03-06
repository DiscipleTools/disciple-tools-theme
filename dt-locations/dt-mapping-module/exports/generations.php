<?php
if ( defined( 'ABSPATH' ) ) {
    return; // return unless accessed directly
}
// @codingStandardsIgnoreStart
if ( ! function_exists( 'dt_write_log' ) ) {
    /**
     * A function to assist development only.
     * This function allows you to post a string, array, or object to the WP_DEBUG log.
     *
     * @param $log
     */
    function dt_write_log( $log ) {
        if ( true === WP_DEBUG ) {
            if ( is_array( $log ) || is_object( $log ) ) {
                error_log( print_r( $log, true ) );
            } else {
                error_log( $log );
            }
        }
    }
}
// @codingStandardsIgnoreLine
require( $_SERVER[ 'DOCUMENT_ROOT' ] . '/wp-load.php' ); // loads the wp framework when called

?>
    <style>
        ul {
            list-style: none;
            /*padding-left: 0;*/

        }

        li {
            padding: 15px;
            border: 1px solid grey;
            margin-top: 10px;
            width: 20%;
            background: yellowgreen;
            border-radius:10px;

        }
    </style>
<?php

function buildMenu( $parent_id, $menuData, $gen) {
    $html = '';

    if (isset( $menuData['parents'][$parent_id] ))
    {
        $html = '<ul class="ul-gen-'.$gen.'">';
        $gen++;
        foreach ($menuData['parents'][$parent_id] as $itemId)
        {
            $html .= '<li class="li-gen-'.$gen.'">';
            $html .= '(gen: '.$gen.')<br> ';
            $html .= '<strong>'. $menuData['items'][$itemId]['name'] . '</strong><br>';

            $html .= '</li>';

            // find childitems recursively
            $html .= buildMenu( $itemId, $menuData, $gen );
        }
        $html .= '</ul>';

    }
    return $html;
}

$query = $wpdb->get_results("
            SELECT
              a.ID         as id,
              0            as parent_id,
              a.post_title as name
            FROM $wpdb->posts as a
            WHERE a.post_status = 'publish'
            AND a.post_type = 'groups'
            AND a.ID NOT IN (
            SELECT DISTINCT (p2p_from)
            FROM $wpdb->p2p
            WHERE p2p_type = 'groups_to_groups'
            GROUP BY p2p_from)
            UNION
            SELECT
              p.p2p_from                          as id,
              p.p2p_to                            as parent_id,
              (SELECT sub.post_title FROM $wpdb->posts as sub WHERE sub.ID = p.p2p_from ) as name
            FROM $wpdb->p2p as p
            WHERE p.p2p_type = 'groups_to_groups'
        ", ARRAY_A );


// prepare special array with parent-child relations
$menuData = array(
    'items' => array(),
    'parents' => array()
);

foreach ( $query as $menuItem )
{
    $menuData['items'][$menuItem['id']] = $menuItem;
    $menuData['parents'][$menuItem['parent_id']][] = $menuItem['id'];
}

// output the menu
//echo buildMenu( 0, $menuData, 0 );


$query = $wpdb->get_col("
            SELECT p2p_to FROM wp_p2p WHERE p2p_type = 'baptizer_to_baptized'
        ");

function get_number_disciples( $contact, $column ) {
    $i = 0;

    foreach ($column as $item) {
        if ($item == $contact && $item != 0) {
            $i++;
//            $item_id = array_search($item, $column );
//            $contact_id = array_search($contact, $column );
//            $sub = $column;
//            unset($sub[$item_id]);
//            unset($sub[$contact_id]);
//            $i = $i + get_number_disciples( $item, $sub );
//            break;
        }
    }
    return $i;
}


echo get_number_disciples( 25, $query );

$gen = new Disciple_Tools_Counter_Generations();
print '<pre>';
print_r( $gen->generation_status_list( 'baptizer_to_baptized' ) );
