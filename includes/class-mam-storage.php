<?php

class MAM_Storage {

	/**
	 * Insert a Client Into the DB.
	 *
	 * @param $client_data
	 *
	 * @return false|mixed
	 *
	 * @todo Check if the device ID is already registered so we can simply just send back the data for it instead of creating a new one
	 */
	public function insertClient( $client_data ) {
		global $wpdb;
		$insert = $wpdb->insert( $wpdb->prefix . 'mam_clients', $client_data );
		if ( $insert ) {
			return $client_data;
		} else {
			return false;
		}
	}

	/**
	 * Get a client from the DB
	 *
	 * @param $client_id
	 *
	 * @return false
	 */
	public function getClient( $client_id ) {
		global $wpdb;
		$result = $wpdb->get_row( $wpdb->prepare( "SELECT client_id FROM {$wpdb->prefix}mam_clients WHERE client_id = %s", array( $client_id ) ) );
		if ( $result ) {
			return $result->client_id;
		} else {
			return false;
		}
	}

	/**
	 * Insert an access token into the DB
	 * @param $client_id
	 * @param $access_token
	 *
	 * @return array|false
	 */
	public function insertAccessToken( $client_id, $access_token ) {
		global $wpdb;
		$expires = date( "Y-m-d H:i:s", strtotime( '+1 hour' ) );
		$insert = $wpdb->insert( $wpdb->prefix . 'mam_access_tokens', array(
			'access_token' => $access_token,
			'client_id'    => $client_id,
			'user_id'      => 0,
			'expires'      => $expires,
			'scope'        => 'basic',
			'ap_generated' => 1
		) );
		if ( $insert ) {
			return array(
				'access_token' => $access_token,
				'expires'      => $expires
			);
		} else {
			return false;
		}
	}
}