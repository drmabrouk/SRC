<?php
/**
 * SRC_Deactivator Class
 * Fired during plugin deactivation.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SRC_Deactivator {

	/**
	 * Main deactivation logic
	 */
	public static function deactivate() {
		// Flush rewrite rules
		flush_rewrite_rules();
	}
}
