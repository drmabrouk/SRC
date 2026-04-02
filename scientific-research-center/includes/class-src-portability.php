<?php
/**
 * Handle data portability and migration.
 */
class SRC_Portability {

	/**
	 * Initialize hooks for import/export optimization.
	 */
	public static function init() {
		add_filter( 'wxr_export_skip_postmeta', array( __CLASS__, 'ensure_meta_is_exported' ), 10, 3 );
		add_action( 'import_post_meta', array( __CLASS__, 'remap_reviewer_ids_on_import' ), 10, 3 );
	}

	/**
	 * Ensure custom metadata is included in WXR exports.
	 */
	public static function ensure_meta_is_exported( $skip, $meta_key, $meta ) {
		$src_meta_keys = array(
			'src_assigned_reviewer',
			'src_paper_status',
			'src_doi',
			'src_paper_file',
		);

		if ( in_array( $meta_key, $src_meta_keys ) ) {
			return false; // Do not skip
		}

		return $skip;
	}

	/**
	 * Remap reviewer User IDs during import to maintain structural integrity.
	 */
	public static function remap_reviewer_ids_on_import( $post_id, $key, $value ) {
		if ( 'src_assigned_reviewer' === $key ) {
			// This is a simplified placeholder for the logic that would
			// map old IDs to new IDs based on email or username if the
			// import tool provides that context.
		}
	}
}
