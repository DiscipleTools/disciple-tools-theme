<?php
/*
 * Disciple Tools - Short Codes
 *
 * @class Disciple_Tools_Function_Callback
 */

if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly


class Disciple_Tools_Function_Callback
{
    /**
     * Class derived from:
     * Plugin URI: https://marketplace.digitalpoint.com/shortcode-callback.3383/item
     * @version 1.0.0
     *
     * @uses [dtools function="someFunction"]
     * @uses [dtools include="custom/filetoinclude.php" function="someFunction"]
     * @uses [dtools function="someClass::someFunction" include="custom/filetoinclude.php" param="something"]
     * @uses [dtools function="someClass::someFunction" name="map" param="something"] ... can be map, chart
     * @uses [dtools function="someFunction" include="custom/filetoinclude.php" param="something"]
     */

    /**
     * Disciple_Tools_Function_Callback The single instance of Disciple_Tools_Function_Callback.
     * @var     object
     * @access  private
     * @since  0.1
     */
    private static $_instance = null;

    /**
     * Main Disciple_Tools_Function_Callback Instance
     *
     * Ensures only one instance of Disciple_Tools_Function_Callback is loaded or can be loaded.
     *
     * @since 0.1
     * @static
     * @see Disciple_Tools()
     * @return Disciple_Tools_Function_Callback instance
     */
    public static function instance () {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    } // End instance()

    /**
     * Constructor function.
     * @access  portal
     * @since   0.1
     */
    protected function __construct()
    {
        $this->_initHooks();
    }

    /**
     * Initializes WordPress hooks
     */
    protected function _initHooks()
    {
        add_shortcode( 'dtools', array($this, 'shortcode_callback') );
    }

    /**
     * Loads the shortcode
     *
     * @since 0.1
     */
    public function shortcode_callback( $atts )
    {


        $atts = shortcode_atts(
            array(
                'function' => null,
                'include' => null,
                'param' => null,
                'name'  => null
            ),
            $atts,
            'dtools'
        );

        if ($this->_isCallable( $atts['function'] ))
        {
            return call_user_func( $atts['function'], $atts['param'] );
        }
        elseif (!empty( $atts['name'] )) {

            // Select the file to include
            switch ($atts['name']) {
                case 'map';
                    require_once( 'shortcode-maps.php' );
                    break;
                case 'chart':
                    require_once( 'charts.php' );
                    break;

                default:
                    return sprintf( esc_html__( '[dtools] Not a "name" option: %s', 'shortcode-callback' ), $atts['name'] );
                    break;

            }

            // Check if callable function, then call it.
            if ($this->_isCallable( @$atts['function'] ))
            {
                return call_user_func( $atts['function'], $atts['param'] );
            }
            else
            {
                return sprintf( esc_html__( '[dtools] Function not callable: %s', 'shortcode-callback' ), $atts['function'] );
            }
        }
        elseif (!empty( $atts['include'] ))
        {
            if (file_exists( ABSPATH . $atts['include'] ))
            {
                require_once( ABSPATH . $atts['include'] );
                if ($this->_isCallable( @$atts['function'] ))
                {
                    return call_user_func( $atts['function'], $atts['param'] );
                }
                else
                {
                    return sprintf( esc_html__( '[dtools] Function not callable: %s', 'shortcode-callback' ), $atts['function'] );
                }
            }
            else
            {
                return sprintf( esc_html__( '[dtools] File not found: %s', 'shortcode-callback' ), ABSPATH . $atts['include'] );
            }
        }
        else
        {
            return sprintf( esc_html__( '[dtools] Function not callable: %s', 'shortcode-callback' ), $atts['function'] );
        }
    }

    /**
     * Checks if the function is callable
     *
     * @return boolean
     */
    protected function _isCallable( $function )
    {
        if (strpos( $function, '::' ))
        {
            $split = explode( '::', $function );

            if (class_exists( $split[0] ))
            {
                $class = new $split[0];
                return is_callable( array($class, $split[1]) );
            }
        }
        else
        {
            return is_callable( $function );
        }
    }
}
