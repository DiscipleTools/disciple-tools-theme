<?php

class DT_Query_Params {
    private $query_params;

    public function __construct( string $query_params ) {
        parse_str( $query_params, $this->query_params );
    }

    /**
     * Add a query param to the list of params clobbering any previous instance of
     *
     * @param string $name
     * @param string $value
     */
    public function append( $name, $value ) : void {
        $this->query_params[$name] = $value;
    }

    /**
     * Get a query param by name
     *
     * @param string $name
     * @return string|null
     */
    public function get( string $name ) {

        foreach ( $this->query_params as $key => $value ) {
            if ( $key === $name ) {
                return $value;
            }
        }

        return null;
    }

    /**
     * Check if a query param exists
     *
     * @param string $name
     * @return bool
     */
    public function has( string $name ) {
        return key_exists( $name, $this->query_params );
    }

    /**
     * Removes a query param from the array
     *
     * @param string $name
     */
    public function delete( string $name ) {

        if ( $this->has( $name ) ) {
            unset( $this->query_params[ $name ] );
        }
    }

    /**
     * Get the query params as an array
     *
     * @return array
     */
    public function to_array() {
        return $this->query_params ? $this->query_params : [];
    }
}
