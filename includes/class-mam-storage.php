<?php

class MAM_Storage {

	/**
	 * Insert a Client Into the DB.
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
}