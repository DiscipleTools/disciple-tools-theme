<?php

class DT_URL {

    private $parsed_url;
    public function __construct( string $url ) {
        $this->parsed_url = parse_url( $url );
    }

    public function query_params() {

        if ( !empty( $this->parsed_url['query'] ) ) {
            return new DT_Query_Params( $this->parsed_url['query'] );
        } else {
            return new DT_Query_Params( '' );
        }
    }
}
