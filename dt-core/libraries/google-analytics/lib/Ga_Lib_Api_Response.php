<?php

class Ga_Lib_Api_Response {

	public static $empty_response = array( '', '' );
	private $header;
	private $body;
	private $data;

	function __construct( $raw_response = null ) {
		if (!empty($raw_response)) {
		$this->setHeader( $raw_response[ 0 ] );
		$this->setBody( $raw_response[ 1 ] );
		$this->setData( json_decode( $raw_response[ 1 ], true ) );
	}
	}

	public function setHeader( $header ) {
		$this->header = $header;
	}

	public function getHeader() {
		return $this->header;
	}

	public function setBody( $body ) {
		$this->body = $body;
	}

	public function getBody() {
		return $this->body;
	}

	public function setData( $data ) {
		$this->data = $data;
	}

	public function getData() {
		return $this->data;
	}

}
