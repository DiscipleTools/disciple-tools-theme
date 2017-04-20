<?php
/**
 * Dtools Maps
 *
 * @uses Disciple_Tools_Function_Callback
 *
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Dtools_Maps
{
    public static function first_map($param)
    {
        return 'This is a map from a class::staticfunction ' . $param;
    }

    public static function portal_maps_page()
    {
        return '<iframe width="1000" height="800" scrolling="no" frameborder="no" src="https://fusiontables.google.com/embedviz?q=select+col11+from+1TDZuA_zZh1v9my293-LMAq9jyRY_5Ggv_YU_3Hg9&amp;viz=MAP&amp;h=false&amp;lat=39.231610171660826&amp;lng=-104.79270425488284&amp;t=1&amp;z=6&amp;l=col11&amp;y=2&amp;tmplt=3&amp;hml=KML"></iframe>';
    }
}