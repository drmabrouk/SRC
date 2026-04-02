<?php
/**
 * Security and validation logic for the plugin.
 */
class SRC_Security {

	/**
	 * Validate uploaded research files.
	 */
	public static function validate_upload( $file ) {
		$allowed_types = array(
			'application/pdf',
			'application/vnd.openxmlformats-officedocument.wordprocessingml.document', // docx
		);

		$max_size = 10 * 1024 * 1024; // 10MB

		if ( ! in_array( $file['type'], $allowed_types ) ) {
			return new WP_Error( 'invalid_file_type', __( 'Only PDF and DOCX files are allowed.', 'scientific-research-center' ) );
		}

		if ( $file['size'] > $max_size ) {
			return new WP_Error( 'file_too_large', __( 'File size exceeds the 10MB limit.', 'scientific-research-center' ) );
		}

		return true;
	}

	/**
	 * Sanitize file names for security.
	 */
	public static function sanitize_filename( $filename ) {
		return sanitize_file_name( $filename );
	}
}
