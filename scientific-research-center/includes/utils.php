<?php
/**
 * Utility functions for Scientific Research Center
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get unique institutions from user metadata
 */
function get_utils_institutions() {
	global $wpdb;

	$results = $wpdb->get_col( $wpdb->prepare( "
		SELECT DISTINCT meta_value
		FROM {$wpdb->usermeta}
		WHERE meta_key = %s
		AND meta_value != ''
	", 'src_institution' ) );

	if ( empty( $results ) ) {
		return array( 'Healthedia Research Center', 'Global Science Institute' );
	}

	return $results;
}
