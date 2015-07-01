<?php namespace HMImagePlaceholders;

namespace HM_Image_Placeholder;

use League\ColorExtractor\Client as ColorExtractor;

/**
 * Class Gradient_Placeholder
 */
class Plugin {

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
		if ( ! ( self::$instance instanceof Plugin ) ) {
			self::$instance = new Plugin();
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

		$colors_hex = $this->get_colors_for_attachment( $id );

		if ( ! $colors_hex ) {
			return $attr;
		}

		$display_type = get_site_option( 'hmip_placeholder_type' );

		if ( 'gradient' === $display_type ) {
			$attr['style'] = $this->get_gradient_style( $colors_hex );
		} else {
			$attr['style'] = $this->get_solid_style( $colors_hex );
		}

		return $attr;
	}

	public function get_gradient_style( $hex_colors ) {

		foreach ( $hex_colors as $hex ) {
			$colors[] = implode( ',', $this->hex2rgb( $hex ) ) . ',0';
			$colors[] = implode( ',', $this->hex2rgb( $hex ) ) . ',1';
		}

		$gradients = array();
		$gradient_angles = array( '90', '0', '-90', '-180' );

		foreach ( $gradient_angles as $key => $gradient_angle ) {
			$gradients[] = sprintf( "linear-gradient(%sdeg, rgba(%s) 0%%, rgba(%s) 100%%, rgba(%s))", $gradient_angle, $colors[ $key ], $colors[ $key + 1 ], $colors[ $key + 1 ] );
		}

		$style = 'background:' . implode( $gradients, ', ' ) . ';';

		return $style;
	}

	public function get_solid_style( $colors ) {

		return 'background:' . reset( $colors ) . ';';
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

		$colors = $this->calculate_colors_for_attachment( $attachment_id );
		$this->save_colors_for_attachment( $attachment_id, $colors );

		return $metadata;
	}

	/**
	 * Get the stored colors for the image
	 *
	 * @param $id
	 * @param array $hex_colors
	 */
	public function get_colors_for_attachment( $id ) {

		return get_post_meta( $attachment->ID, 'hmgp_image_colors', true );

	}

	/**
	 * Extract the colors from the image
	 *
	 * @param $id
	 * @param $image_path
	 */
	public function save_colors_for_attachment( $id, $colors = array() ) {
		update_post_meta( $id, 'hmgp_image_colors', $colors );
	}

	/**
	 * Calculate the colors from the image
	 *
	 * @param $id
	 * @param $image_path
	 */
	public function calculate_colors_for_attachment( $id ) {

		$img     = wp_get_attachment_image_src( $id, 'thumbnail' );
		$uploads = wp_upload_dir();
		$path    = str_replace( $uploads['baseurl'], $uploads['basedir'], $img[0] );

		if ( get_post_meta( $id, 'hmgp_image_colors', true ) ) {
			return;
		}

		return $this->extract_colors( $path );

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

		return $rgb; // returns an array with the rgb values
	}

}
