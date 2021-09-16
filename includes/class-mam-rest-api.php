<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

do_action( 'mam_plugin_before_api', array( $_REQUEST ) );

global $wp_query;
$method   = $wp_query->get( 'mam' );
$response = new MAM_API_Response();

/**
 * Dynamic Client Registration
 *
 * Request
 *
 * {
 * "redirect_uris": [
 * "https://client.example.org/callback",
 * "https://client.example.org/callback2"],
 * "client_name": "My Example Client",
 * "client_name#ja-Jpan-JP":
 * "\u30AF\u30E9\u30A4\u30A2\u30F3\u30C8\u540D",
 * "token_endpoint_auth_method": "client_secret_basic",
 * "logo_uri": "https://client.example.org/logo.png",
 * "jwks_uri": "https://client.example.org/my_public_keys.jwks",
 * "example_extension_parameter": "example_value"
 * }
 *
 * @link https://datatracker.ietf.org/doc/html/rfc7591#page-15
 */
if ( $method == 'auth/register' ) {

	require_once( MAM_PLUGIN_DIR . '/includes/class-mam-api-request.php' );

	$auth = new MAM_API_Request();
	$auth->handleRequest( $wp_query );

	$response->setResponse( array(
		'error'       => false,
		'description' => 'The device has been registered'
	) );
	$response->send();
	exit;
}


$response = new MAM_API_Response();
$error    = $response->setError( array(
	'error'       => 'unsupported',
	'description' => 'The call made is unsupported'
) );
$response->send();
exit;