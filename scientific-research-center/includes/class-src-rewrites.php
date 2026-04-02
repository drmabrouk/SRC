<?php
/**
 * Handle custom URL structures for the plugin.
 */
class SRC_Rewrites {

	/**
	 * Register rewrite rules.
	 */
	public static function add_rewrite_rules() {
		add_rewrite_rule(
			'^researcher/([^/]+)/?$',
			'index.php?pagename=user-profile&src_username=$matches[1]',
			'top'
		);
	}

	/**
	 * Register custom query variables.
	 */
	public static function add_query_vars( $vars ) {
		$vars[] = 'src_username';
		return $vars;
	}
}
