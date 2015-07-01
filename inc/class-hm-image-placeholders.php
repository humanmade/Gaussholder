<?php

use League\ColorExtractor\Client as ColorExtractor;

/**
 * Class Gradient_Placeholder
 */
class HM_Image_Placeholders {

	/**
	 * @var
	 */
	private static $instance;

	/**
	 * Creates and returns a singleton instance of the class.
	 *
	 * @return Gradient_Placeholder
	 */
	public static function get_instance() {
		if ( ! ( self::$instance instanceof HM_Image_Placeholders ) ) {
			self::$instance = new HM_Image_Placeholders();
			self::$instance->init();
		}

		return self::$instance;
	}

	/**
	 * Initialize the plugin.
	 */
	public function init() {
		add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ) );
	}

	/**
	 * Set up hooked callbacks on plugins_loaded
	 */
	public function plugins_loaded() {

		add_filter( 'wp_generate_attachment_metadata', array( $this, 'filter_wp_generate_attachment_metadata' ), 10, 2 );

		add_filter( 'wp_get_attachment_image_attributes', array( $this, 'filter_wp_get_attachment_image_attributes' ), 10, 3 );
	}

	/**
	 * Adds a style attribute to image HTML.
	 *
	 * @param $attr
	 * @param $attachment
	 * @param $size
	 *
	 * @return mixed
	 */
	function filter_wp_get_attachment_image_attributes( $attr, $attachment, $size ) {

		$colors_hex = get_post_meta( $attachment->ID, 'hmgp_image_colors', true );

		if ( ! $colors_hex ) {
			return $attr;
		}

		foreach ( $colors_hex as $hex ) {
			$colors[] = implode( ',', $this->hex2rgb( $hex ) ) . ',0';
			$colors[] = implode( ',', $this->hex2rgb( $hex ) ) . ',1';
		}

		$gradients = array();
		$gradient_angles = array( '90', '0', '-90', '-180' );

		foreach ( $gradient_angles as $key => $gradient_angle ) {
			$gradients[] = sprintf( "linear-gradient(%sdeg, rgba(%s) 0%%, rgba(%s) 100%%, rgba(%s))", $gradient_angle, $colors[ $key ], $colors[ $key + 1 ], $colors[ $key + 1 ] );
		}

		$style = 'background:' . implode( $gradients, ', ' ) . ';';

		$attr['style'] = $style;

		return $attr;
	}

	/**
	 * Get the image's dominant colors
	 *
	 * @param $image_path
	 *
	 * @return array
	 */
	public function extract_colors( $image_path ) {

		$client = new ColorExtractor;

		$image = $client->loadJpeg( $image_path );

		// Get three most used color hex code
		return $image->extract( 4 );

	}

	/**
	 * Save extracted colors to image metadata
	 *
	 * @param $metadata
	 * @param $attachment_id
	 *
	 * @return mixed
	 */
	public function filter_wp_generate_attachment_metadata( $metadata, $attachment_id ) {

		$upload_path = wp_upload_dir();

		$thumbnail_path = $upload_path['basedir'] . '/' . $metadata['file'];

		$this->save_colors_for_attachment( $attachment_id, $thumbnail_path );

		return $metadata;
	}

	/**
	 * Extract the colors from the image
	 *
	 * @param $id
	 * @param $image_path
	 */
	protected function save_colors_for_attachment( $id, $image_path ) {

		if ( get_post_meta( $id, 'hmgp_image_colors', true ) ) {
			return;
		}
		$colors = $this->extract_colors( $image_path );
		update_post_meta( $id, 'hmgp_image_colors', $colors );
	}

	/**
	 * Converts a hex color to RGB.
	 * 
	 * @param $hex
	 *
	 * @return array
	 */
	function hex2rgb( $hex ) {
		$hex = str_replace( "#", "", $hex );

		if ( strlen( $hex ) == 3 ) {
			$r = hexdec( substr( $hex, 0, 1 ) . substr( $hex, 0, 1 ) );
			$g = hexdec( substr( $hex, 1, 1 ) . substr( $hex, 1, 1 ) );
			$b = hexdec( substr( $hex, 2, 1 ) . substr( $hex, 2, 1 ) );
		} else {
			$r = hexdec( substr( $hex, 0, 2 ) );
			$g = hexdec( substr( $hex, 2, 2 ) );
			$b = hexdec( substr( $hex, 4, 2 ) );
		}
		$rgb = array( $r, $g, $b );

		//return implode(",", $rgb); // returns the rgb values separated by commas
		return $rgb; // returns an array with the rgb values
	}

}
