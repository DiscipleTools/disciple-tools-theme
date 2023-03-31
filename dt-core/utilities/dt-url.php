<?php

class DT_URL {

    private $parsed_url;
    public $query_params;
    public function __construct( string $url ) {
        $this->parsed_url = parse_url( $url );
        $this->query_params = $this->get_query_params();
    }

    private function get_query_params() {

        if ( !empty( $this->parsed_url['query'] ) ) {
            return new DT_Query_Params( $this->parsed_url['query'] );
        } else {
            return new DT_Query_Params( '' );
        }
    }
}
