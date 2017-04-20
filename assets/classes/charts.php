<?php
/**
 * Dtools Charts
 *
 * @uses Disciple_Tools_Function_Callback - Used in short codes
 *
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Dtools_Charts
{
    public static function first_chart($param)
    {
        return 'This is a chart from a class::staticfunction ' . $param;
    }

    public static function portal_charts_page()
    {
        return 'This is a chart from a class::staticfunction by portal_charts_page ';
    }
}