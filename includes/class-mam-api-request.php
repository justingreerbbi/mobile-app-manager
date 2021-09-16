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

			$response->setResponse($creation, '201 Created');
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