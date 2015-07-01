<?php
/*
Plugin Name: HM Image Placeholders
Plugin URI: http://hmn.md
Description: Automatically generate gradient or solid color image placeholders.
Version: 0.1.0
Author: Human Made Limited
Author URI: http://hmn.md/
License: GPL-2.0+
License URI: http://www.gnu.org/licenses/gpl-2.0.txt
Text Domain: hm-gradient-placeholders
Domain Path: /languages
Network: true
 */

require_once __DIR__ . '/vendor/autoload.php';

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
	 * @return Gradient_Placeholder
	 */
	public static function get_instance() {
		if ( ! ( self::$instance instanceof HM_Image_Placeholders ) ) {
			self::$instance = new HM_Image_Placeholders();
		}

		return self::$instance;
	}

	/**
	 *
	 */
	private function __construct() {
		add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ) );
	}

	/**
	 *
	 */
	public function plugins_loaded() {

		add_filter( 'wp_generate_attachment_metadata', array( $this, 'filter_wp_generate_attachment_metadata' ), 10, 2 );

		//add_filter( 'image_send_to_editor', array( $this, 'filter_image_send_to_editor' ), 10, 8 );

		add_filter( 'wp_get_attachment_image_attributes', array( $this, 'filter_wp_get_attachment_image_attributes' ), 10, 3 );
	}

	function filter_wp_get_attachment_image_attributes( $attr, $attachment, $size ) {

		$colors_hex = get_post_meta( $attachment->ID, 'hmgp_image_colors', true );

		if ( ! $colors_hex ) {
			return;
		}

		foreach ( $colors_hex as $hex ) {
			$colors[] = implode( ',', $this->hex2rgb( $hex ) ) . ',0';
			$colors[] = implode( ',', $this->hex2rgb( $hex ) ) . ',1';
		}

		$style = ' style="background:linear-gradient(90deg, rgba(' . $colors[0] . ') 0%, rgba(' . $colors[1] . ') 100%, rgba(' . $colors[1] . ') 100%) , linear-gradient(0deg, rgba(' . $colors[2] . ') 0%, rgba(' . $colors[3] . ') 100%, rgba(' . $colors[3] . ') 100%) , linear-gradient(-90deg, rgba(' . $colors[4] . ') 0%, rgba(' . $colors[5] . ') 100%, rgba(' . $colors[5] . ') 100%) , linear-gradient(-180deg, rgba(' . $colors[6] . ') 0%, rgba(' . $colors[7] . ') 100%, rgba(' . $colors[7] . ') 100%);" ';

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
	 * @param $metadata
	 * @param $attachment_id
	 *
	 * @return mixed
	 */
	public function filter_wp_generate_attachment_metadata( $metadata, $attachment_id ) {

		$upload_path = wp_upload_dir();

		$thumbnail_path = $upload_path['basedir']  . '/' . $metadata['file'];

		$this->save_colors_for_attachment( $attachment_id, $thumbnail_path );

		return $metadata;
	}

	/**
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

	/**
	 * @param $html
	 * @param $id
	 *
	 * @return string
	 */
	public function filter_image_send_to_editor( $html, $id, $caption, $title, $align, $url, $size, $alt ) {

		$new_html = $this->generate_image_markup( $id );

		if ( ! empty( $new_html ) ) {
			return $new_html;
		}
		return $html;
	}

	public function generate_image_markup( $attachment_id ) {

		//fetching attachment by post $id
		$attachment = get_post( $attachment_id );
		$mime_type  = $attachment->post_mime_type;

		//get an valid array of images types
		$image_exts = array( 'image/jpg', 'image/jpeg', 'image/jpe', 'image/gif', 'image/png' );

		//checking the above mime-type
		if ( in_array( $mime_type, $image_exts ) ) {

			// the image link would be great
			$src = wp_get_attachment_url( $attachment_id );

			$colors_hex = get_post_meta( $attachment_id, 'hmgp_image_colors', true );

			if ( ! $colors_hex ) {
				return;
			}

			foreach ( $colors_hex as $hex ) {
				$colors[] = implode( ',', $this->hex2rgb( $hex ) ) . ',0';
				$colors[] = implode( ',', $this->hex2rgb( $hex ) ) . ',1';
			}

			// enter you custom output here, you will want to probably change this
			$html = '<a href="' . $src . '" class="your-class" data-src="/img/image1.jpg" rel="your-rel"><img style="background:linear-gradient(90deg, rgba(' . $colors[0] . ') 0%, rgba(' . $colors[1] . ') 100%, rgba(' . $colors[1] . ') 100%) , linear-gradient(0deg, rgba(' . $colors[2] . ') 0%, rgba(' . $colors[3] . ') 100%, rgba(' . $colors[3] . ') 100%) , linear-gradient(-90deg, rgba(' . $colors[4] . ') 0%, rgba(' . $colors[5] . ') 100%, rgba(' . $colors[5] . ') 100%) , linear-gradient(-180deg, rgba(' . $colors[6] . ') 0%, rgba(' . $colors[7] . ') 100%, rgba(' . $colors[7] . ') 100%);" src="' . $src . '" /></a>';

			return $html; // return new $html
		}
	}
}

HM_Image_Placeholders::get_instance();
