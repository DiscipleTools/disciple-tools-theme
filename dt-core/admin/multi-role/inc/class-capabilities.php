<?php

/**
 * Role factory class.
 *
 * @since  0.1.0
 * @access public
 */
final class Disciple_Tools_Capabilities {
    /**
     * Returns the instance.
     *
     * @return object
     * @since  3.0.0
     * @access public
     */
    public static function get_instance() {

        static $instance = null;

        if (is_null( $instance )) {
            $instance = new Disciple_Tools_Capabilities(
                Disciple_Tools_Capability_Factory::get_instance()
            );
        }

        return $instance;
    }

    public function __construct( $factory ) {
        $this->factory = $factory;
    }

    public function all() {
        return $this->factory->get_capabilities();
    }

    public function from_source( $source ) {
        return array_filter( $this->all(), function ( $capability ) use ( $source ) {
            return $capability->source === $source;
        } );
    }

    public function sources() {
        return array_reduce($this->all(), function($sources, $capability) {
            if (!$capability->source) {
                return $sources;
            }

            if (!in_array($capability->source, $sources)) {
                $sources[] = $capability->source;
            }

            return $sources;
        }, []);
    }
}
