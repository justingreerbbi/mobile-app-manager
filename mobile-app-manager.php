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

if ( ! defined( 'MAM_PLUGIN_DB_VERSION' ) ) {
	define( 'MAM_PLUGIN_DB_VERSION', '1.0' );
}

function mam_plugin_api_includes() {
	require_once( MAM_PLUGIN_DIR . '/includes/class-mam-storage.php' );
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


/**
 * Install Script
 * DO NOT MODIFY
 *
 * @todo Add DB script with DB check etc.
 */
register_activation_hook( __FILE__, 'mam_plugin_install' );
function mam_plugin_install() {

	global $wpdb;
	$charset_collate = '';

	if ( ! empty( $wpdb->charset ) ) {
		$charset_collate = "DEFAULT CHARACTER SET {$wpdb->charset}";
	}

	if ( ! empty( $wpdb->collate ) ) {
		$charset_collate .= " COLLATE {$wpdb->collate}";
	}

	/*
	 * Set the plugin DB version is it is not already set.
	 */
	$mamp_plugin_db_version = get_option( 'mam_plugin_db_version', false );
	if ( false == $mamp_plugin_db_version ) {
		update_option( 'mam_plugin_db_version', MAM_PLUGIN_DB_VERSION );
	}
	update_option( 'mam_plugin_version', MAM_PLUGIN_VERSION );

	$sql1 = "
			CREATE TABLE IF NOT EXISTS {$wpdb->prefix}mam_clients (
			id 					  INT 			UNSIGNED NOT NULL AUTO_INCREMENT,
	        client_id             VARCHAR(191)	NOT NULL UNIQUE,
	        client_secret         VARCHAR(255)  NOT NULL,
	        redirect_uri          VARCHAR(2000),
	        grant_types           VARCHAR(80),
	        scope                 VARCHAR(4000),
	        user_id               VARCHAR(80),
	        name                  VARCHAR(80),
	        description           LONGTEXT,
	        PRIMARY KEY (id)
	      	);
		";

	$sql2 = "
			CREATE TABLE IF NOT EXISTS {$wpdb->prefix}mam_access_tokens (
			id					 INT 			UNSIGNED NOT NULL AUTO_INCREMENT,
			access_token         VARCHAR(1000) 	NOT NULL,
		    client_id            VARCHAR(255)	NOT NULL,
		    user_id              VARCHAR(80),
		    expires              TIMESTAMP      NOT NULL,
		    scope                VARCHAR(4000),
		    ap_generated       VARCHAR(32),
		    PRIMARY KEY (id)
      		);
		";

	$sql3 = "
			CREATE TABLE IF NOT EXISTS {$wpdb->prefix}mam_refresh_tokens (
			refresh_token       VARCHAR(191)    NOT NULL UNIQUE,
		    client_id           VARCHAR(255)    NOT NULL,
		    user_id             VARCHAR(80),
		    expires             TIMESTAMP      	NOT NULL,
		    scope               VARCHAR(4000),
		    PRIMARY KEY (refresh_token)
      		);
		";

	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	dbDelta( $sql1 );
	dbDelta( $sql2 );
	dbDelta( $sql3 );
}