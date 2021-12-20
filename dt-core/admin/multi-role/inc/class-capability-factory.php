<?php

/**
 * WordPress' `WP_Roles` and the global `$wp_capabilities` array don't really cut it.  So, this is a
 * singleton factory class for storing capability objects and information that we need.
 *
 * @package    Members
 * @subpackage Includes
 * @author     Justin Tadlock <justin@justintadlock.com>
 * @copyright  Copyright (c) 2009 - 2016, Justin Tadlock
 * @link       http://themehybrid.com/plugins/members
 * @license    http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */

/**
 * Role factory class.
 *
 * @since  0.1.0
 * @access public
 */
final class Disciple_Tools_Capability_Factory {

    /**
     * Array of capabilities added.
     *
     * @since  0.1.0
     * @access public
     * @var    array
     */
    public $capabilities = [];

    /**
     * Private constructor method to prevent a new instance of the object.
     *
     * @since  0.1.0
     * @access public
     * @return void
     */
    private function __construct() {}

    /**
     * Adds a capability object.
     *
     * @since  0.1.0
     * @access public
     * @param  string  $slug
     * @param  array  $options
     */
    public function add_capability( $slug, $options ) {
        $source = $options['source'];
        $name = isset($options['name']) ? $options['name'] : $this->name_from_slug($slug);
        $description = isset($options['description']) ? $options['description'] : '';

        $capability = $this->get_capability($slug);

        if (!$this->get_capability($slug)) {
            //Handle the case that we've already registered this capability
            $capability = new Disciple_Tools_Capability(
                $slug,
                $source,
                $name,
                $description
            );
        } else {
            $capability->source = $source;
            $capability->slug = $slug;
            $capability->description = $description;
        }
        $this->capabilities[$slug] = $capability;
        ksort($this->capabilities);
    }

    /**
     * Returns a single capability object.
     *
     * @since  0.1.0
     * @access public
     * @param  string  $capability
     * @return object|bool
     */
    public function get_capability( $capability ) {
        return isset( $this->capabilities[ $capability ] ) ? $this->capabilities[ $capability ] : false;
    }


    /**
     * Removes a capability object (doesn't remove from DB).
     *
     * @since  1.1.0
     * @access public
     * @param  string  $capability
     * @return void
     */
    public function remove_capability( $capability ) {

        if ( isset( $this->capabilities[ $capability ] ) ) {
            unset( $this->capabilities[ $capability ] );
        }
    }

    /**
     * Returns an array of capability objects.
     *
     * @since  0.1.0
     * @access public
     * @return array
     */
    public function get_capabilities( $capabilities = [] ) {
        if (!count($capabilities)) {
            return $this->capabilities;
        }

        return array_filter($this->capabilities, function($capability) use ($capabilities) {
            return in_array($capability->slug, $capabilities);
        });
    }

    /**
     * Returns the instance.
     *
     * @since  3.0.0
     * @access public
     * @return object
     */
    public static function get_instance() {

        static $instance = null;

        if ( is_null( $instance ) ) {
            $instance = new Disciple_Tools_Capability_Factory();
            $instance->setup_capabilities();
        }

        return $instance;
    }


    private function name_from_slug( $capability ) {
        $string = str_replace("_", ' ', $capability);

        /* Words that should be entirely lower-case */
        $articles_conjunctions_prepositions = array(
            'a','an','the',
            'and','but','or','nor',
            'if','then','else','when',
            'at','by','from','for','in',
            'off','on','out','over','to','into','with'
        );
        /* Words that should be entirely upper-case (need to be lower-case in this list!) */
        $acronyms_and_such = array(
            'asap', 'unhcr', 'wpse', 'wtf'
        );
        /* split title string into array of words */
        $words = explode( ' ', mb_strtolower( $string ) );
        /* iterate over words */
        foreach ( $words as $position => $word ) {
            /* re-capitalize acronyms */
            if( in_array( $word, $acronyms_and_such ) ) {
                $words[$position] = mb_strtoupper( $word );
                /* capitalize first letter of all other words, if... */
            } elseif (
                /* ...first word of the title string... */
                0 === $position ||
                /* ...or not in above lower-case list*/
                ! in_array( $word, $articles_conjunctions_prepositions )
            ) {
                $words[$position] = ucwords( $word );
            }
        }
        /* re-combine word array */
        $string = implode( ' ', $words );
        /* return title string in title case */
        return $string;
    }

    public function setup_capabilities()
    {
        $capabilities = [];

        $wordpress_capabilities = dt_multi_role_get_wp_capabilities();
        foreach ($wordpress_capabilities as $capability) {
            $capabilities[$capability] = [
                'source' => __('WordPress', 'disciple_tools')
            ];
        }

        $dt_capabilities = array_merge(
            dt_multi_role_get_role_capabilities(),
            dt_multi_role_get_plugin_capabilities()
        );

        foreach ($dt_capabilities as $capability) {
            $capabilities[$capability] = [
                'source' => __('Disciple Tools', 'disciple_tools'),
            ];
        }

        $capabilities = apply_filters('dt_capabilities', $capabilities);

        foreach ($capabilities as $capability => $options) {
            $this->add_capability($capability, $options);
        }
    }
}
