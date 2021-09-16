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

	public function getClient( $client_id ) {
		global $wpdb;
		$result = $wpdb->get_row( $wpdb->prepare( "SELECT client_id FROM {$wpdb->prefix}mam_clients WHERE client_id = %s", array( $client_id ) ) );
		if ( $result ) {
			return $result->client_id;
		} else {
			return false;
		}
	}
}