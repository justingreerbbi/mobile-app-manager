<?php
/**
 * Plugin Name: Mobile Application Manager
 *
 * @auhtor Justin Greer <justin@justin-greer.com>
 */

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

if ( ! defined( 'MAM_PLUGIN_FILE' ) ) {
	define( 'MAM_PLUGIN_FILE', __FILE__ );
}

if ( ! defined( 'MAM_PLUGIN_DIR' ) ) {
	define( 'MAM_PLUGIN_DIR', dirname( __FILE__ ) );
}

if ( ! defined( 'MAM_PLUGIN_VERSION' ) ) {
	define( 'MAM_PLUGIN_VERSION', '1.0.0' );
}

function mam_plugin_api_includes() {
	require_once( MAM_PLUGIN_DIR . '/includes/class-mam-response.php' );
	require_once( MAM_PLUGIN_DIR . '/includes/class-mam-rest-api.php' );
}

function mam_plugin_register_query_vars() {
	mam_plugin_register_rewrites();

	global $wp;
	$wp->add_query_var( 'mam' );
}

add_action( 'init', 'mam_plugin_register_query_vars' );


function mam_plugin_register_rewrites() {
	add_rewrite_rule( '^mam/(.+)', 'index.php?mam=$matches[1]', 'top' );
}

function mam_plugin_template_redirect_intercept( $template ) {
	global $wp_query;

	if ( $wp_query->get( 'mam' ) ) {

		/*
		 * @todo Change this function call to an action include for extendability
		 */
		mam_plugin_api_includes();
		define( 'DOING_MOBILE_APPLICATION_API', true );
		require_once dirname( __FILE__ ) . '/includes/class-wo-api.php';
		exit;
	}

	return $template;
}

add_filter( 'template_include', 'mam_plugin_template_redirect_intercept', 100 );