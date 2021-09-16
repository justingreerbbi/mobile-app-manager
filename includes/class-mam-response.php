<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

class MAM_API_Response {

	public $lastestResponse;

	public function __construct() {
		header( 'Content-Type: application/json; charset=utf-8' );
	}

	public function setResponse( $response, $code = '200 OK' ) {
		header( $_SERVER["SERVER_PROTOCOL"] . ' ' . $code );
		$this->lastestResponse = json_encode( $response );
	}

	public function setError( $error, $code = '400 BAD REQUEST' ) {
		header( $_SERVER["SERVER_PROTOCOL"] . ' ' . $code );
		$this->lastestResponse = json_encode( $error );
	}

	public function send() {
		print $this->lastestResponse;
		exit;
	}
}