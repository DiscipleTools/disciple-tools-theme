<?php

class DT_Query_Params {
    private $query_params;

    public function __construct( string $query_params ) {
        parse_str( $query_params, $this->query_params );
    }

    public function get( string $name ) {

        foreach ( $this->query_params as $key => $value ) {
            if ( $key === $name ) {
                return $value;
            }
        }

        return null;
    }
}
