<?php

/**
 * A helper class for working with capabilities
 *
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

    /**
     * Disciple_Tools_Capabilities constructor.
     * @param $factory
     */
    public function __construct( $factory ) {
        $this->factory = $factory;
    }

    /**
     * Get all the registered capabilities as an array
     * @return mixed
     */
    public function all() {
        return $this->factory->get_capabilities();
    }

    /**
     * Only get capabiliteis from a particular source
     * "Wordpress" or 'Disciple Tools" or any other source registered by a plugin
     * @param $source
     * @return mixed
     */
    public function from_source( $source ) {
        return array_filter( $this->all(), function ( $capability ) use ( $source ) {
            return $capability->source === $source;
        } );
    }

    /**
     * Get an array of all registered sources.
     * @return mixed
     */
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
