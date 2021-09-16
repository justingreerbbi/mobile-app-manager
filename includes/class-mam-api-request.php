<?php
/**
 * Request Handler for Mobile Application Manager
 *
 * @author Justin Greer <justin@justin-greer.com>
 */

class MAM_API_Request {

	/**
	 * Handle the Initial Request
	 */
	public function handleRequest( $request ) {

		$request = apply_filters( 'mam_handle_request', $request );
		$this->checkRequestMethod( $request );

		if ( $request->get( 'mam' ) == 'auth/register' ) {
			$this->handleDynamicClientRegistration( $request );
		}

		if ( $request->get( 'mam' ) == 'auth/token' ) {
			$this->handleTokenRequest( $request );
		}

		if ( $request->get( 'mam' ) == 'user/register' ) {
			$this->handleTokenRequest( $request );
		}

	}

	/**
	 * Handle Token Request. This method is only used as a way to issue tokens to device and not user
	 *
	 * @param $request
	 */
	public function handleTokenRequest( $request ) {
		$this->checkRequestMethod( $request );

		if ( empty( $_POST['grant_type'] ) ) {
			$response = new MAM_API_Response();
			$response->setError( array(
				'error'             => 'missing_parameter',
				'error_description' => 'The valid value for the parameter "grant type" MUST be provided'
			), 400 );
			$response->send();
			exit;
		}

		if ( empty( $_POST['client_id'] ) ) {
			$response = new MAM_API_Response();
			$response->setError( array(
				'error'             => 'missing_parameter',
				'error_description' => 'The valid value for the parameter "client id" MUST be provided'
			), 400 );
			$response->send();
			exit;
		}

		if ( $_POST['grant_type'] == 'client_credentials' ) {
			$this->handleClientCredentialRequest( $_POST['client_id'] );
		}

		if ( $_POST['grant_type'] == 'user_password' ) {
			exit( 'check the required username and password' );
		}

		exit;
	}

	public function handleClientCredentialRequest( $client_id ) {
		$storage = new MAM_Storage();

		$client_id_check = $storage->getClient( $client_id );
		if ( $client_id_check == false ) {
			$response = new MAM_API_Response();
			$response->setError( array(
				'error'             => 'invalid_client',
				'error_description' => 'The client id provided is invalid'
			), 400 );
			$response->send();
			exit;
		}

		// Issue an access token for the client
		$access_token_data = $this->setAccessToken( $client_id );
		$response = new MAM_API_Response();
		$response->setResponse( $access_token_data, '201 Created' );
		$response->send();

		exit;
	}

	public function setAccessToken( $client_id ) {
		$access_token = wp_generate_password( 60, false, false );

		$storage = new MAM_Storage();
		$token_data   = $storage->insertAccessToken( $client_id, $access_token );

		return $token_data;
	}

	/**
	 * Check the request and ensure all the checks pass for the given requirements based on the OAuth Draft.
	 *
	 * @param $requestCheck
	 */
	public function checkRequestMethod( $request ) {

		/*
		 * Check dynamic registration client method.
		 * MUST BE POST https://datatracker.ietf.org/doc/html/rfc7591#page-15
		 */
		if ( $request->get( 'mam' ) == 'auth/register' && $_SERVER['REQUEST_METHOD'] != 'POST' ) {
			$response = new MAM_API_Response();
			$response->setError( array(
				'error'             => 'unsupported_method',
				'error_description' => 'POST method MUST be used when registering a dynamic client. https://datatracker.ietf.org/doc/html/rfc7591#page-15'
			), 400 );
			$response->send();
			exit;
		}

		if ( $request->get( 'mam' ) == 'auth/token' && $_SERVER['REQUEST_METHOD'] != 'POST' ) {
			$response = new MAM_API_Response();
			$response->setError( array(
				'error'             => 'unsupported_method',
				'error_description' => 'POST method MUST be used when registering a dynamic client. https://datatracker.ietf.org/doc/html/rfc7591#page-15'
			), 400 );
			$response->send();
			exit;
		}
	}

	/**
	 * Handle the Dynamic Client Registration Process
	 *
	 * Required Fields:
	 * - client_name
	 * - device_id
	 */
	public function handleDynamicClientRegistration( $request ) {
		$response = new MAM_API_Response();

		$inputJSON = file_get_contents( 'php://input' );
		$input     = json_decode( $inputJSON );
		if ( json_last_error() === JSON_ERROR_NONE ) {

			if ( empty( $input->client_name ) ) {
				$response->setError( array(
					'error'             => 'missing_required_fields',
					'error_description' => 'The request is missing the required "client_name" parameter.'
				) );
				$response->send();
			}

			if ( empty( $input->device_id ) ) {
				$response->setError( array(
					'error'             => 'missing_required_fields',
					'error_description' => 'The request is missing the required "device_id" parameter.'
				) );
				$response->send();
			}

			// Get the client name and device id. These are unique to a device
			$client_name = sanitize_text_field( $input->client_name );
			$device_id   = sanitize_text_field( $input->device_id );

			// Generate a Custom Client ID
			$generated_client_id = wp_generate_password( 32, false, false );

			// Attempt to create the client in the DB
			$storage  = new MAM_Storage();
			$creation = $storage->insertClient( array(
				'client_id'   => $generated_client_id,
				'name'        => $client_name,
				'description' => $device_id
			) );

			if ( $creation == false ) {
				$response->setError( array(
					'error'             => 'error_on_creation',
					'error_description' => 'There was an issue creating the client. Check with the the developer.'
				) );
				$response->send();
			}

			$response->setResponse( $creation, '201 Created' );
			$response->send();

		} else {
			$response->setError( array(
				'error'             => 'malformed_input',
				'error_descriptiom' => 'The request is malformed. It must be in vlaid JSON format'
			) );
			$response->send();
		}
	}


}