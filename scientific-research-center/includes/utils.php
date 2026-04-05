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

/**
 * Keyword highlighting helper
 */
function src_highlight_keywords( $text, $query ) {
	if ( empty( $query ) ) return $text;
	$words = explode( ' ', $query );
	foreach ( $words as $word ) {
		$word = preg_quote( $word, '/' );
		$text = preg_replace( "/($word)/i", '<mark class="src-highlight">$1</mark>', $text );
	}
	return $text;
}

/**
 * Log activity in the custom log table
 */
function src_log_activity( $user_id, $event_type, $description ) {
	global $wpdb;
	$table_name = $wpdb->prefix . 'src_activity_log';

	$wpdb->insert(
		$table_name,
		array(
			'user_id'     => $user_id,
			'event_type'  => $event_type,
			'description' => $description,
			'event_date'  => current_time( 'mysql' ),
		),
		array( '%d', '%s', '%s', '%s' )
	);
}
