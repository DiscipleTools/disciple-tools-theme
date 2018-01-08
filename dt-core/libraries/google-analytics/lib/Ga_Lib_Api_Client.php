<?php

abstract class Ga_Lib_Api_Client {

	/**
	 * Keeps error messages.
	 * @var array
	 */
	protected $errors = array();

	/**
	 * Returns errors array.
	 * @return array
	 */
	public function get_errors() {
		return $this->errors;
	}

	/**
	 * Calls private API method from context client.
	 *
	 * @param $callback
	 * @param $args
	 *
	 * @return Ga_Lib_Api_Response
	 */
	abstract function call_api_method( $callback, $args );

	/**
	 * Calls api methods.
	 *
	 * @param string $callback
	 * @param mixed $args
	 *
	 * @return mixed
	 */
	public function call( $callback, $args = null ) {
		try {
			return $this->call_api_method( $callback, $args );
		} catch ( Ga_Lib_Api_Client_Exception $e ) {
			$this->add_error( $e );

			return new Ga_Lib_Api_Response( Ga_Lib_Api_Response::$empty_response );
		} catch ( Ga_Lib_Api_Request_Exception $e ) {
			$this->add_error( $e );

			return new Ga_Lib_Api_Response( Ga_Lib_Api_Response::$empty_response );
		} catch ( Exception $e ) {
			$this->add_error( $e );

			return new Ga_Lib_Api_Response( Ga_Lib_Api_Response::$empty_response );
		}
	}

	/**
	 * Prepares error data.
	 *
	 * @param Exception $e
	 *
	 */
	protected function add_error( Exception $e ) {
		$this->errors[ $e->getCode() ] = array( 'class' => get_class( $e ), 'message' => $e->getMessage() );
	}

	public function add_own_error( $code, $message, $class = '' ) {
		$this->errors[ $code ] = array( 'class' => $class, 'message' => $message );
	}

}

class Ga_Lib_Api_Client_Exception extends Exception {
	
}
