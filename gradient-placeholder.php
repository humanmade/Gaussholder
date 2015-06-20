<?php
/*
Plugin Name: Gradient Placeholder
Version: 0.1-alpha
Description: Image placeholders gradients
Author: pauldewouters
Text Domain: gradient-placeholder
Domain Path: /languages
 */

require_once __DIR__ . '/vendor/autoload.php';

use League\ColorExtractor\Client as ColorExtractor;

class Gradient_Placeholder {

	private static $instance;

	public static function get_instance() {
		if ( ! ( self::$instance instanceof Gradient_Placeholder ) ) {
			self::$instance = new Gradient_Placeholder();
		}

		return self::$instance;
	}

	private function __construct() {
		add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ) );
	}

	public function plugins_loaded() {

		add_filter( 'wp_generate_attachment_metadata', array( $this, 'filter_wp_generate_attachment_metadata' ), 10, 2 );

		add_filter( 'get_image_tag_class', array( $this, 'filter_get_image_tag_class' ), 10, 4 );

		add_action( 'wp_head', array( $this, 'action_wp_head' ) );
	}

	public function extract_colors( $image_path ) {

		$client = new ColorExtractor;

		$image = $client->loadJpeg( $image_path );

		// Get three most used color hex code
		return $image->extract( 4 );

	}

	public function filter_get_image_tag_class( $class, $id, $align, $size ) {
		return $class . ' hmgp-id-' . $id;
	}


	public function filter_wp_generate_attachment_metadata( $metadata, $attachment_id ) {

		$uploads_dir = wp_upload_dir();

		$thumbnail_path = trailingslashit( $uploads_dir['path'] ) . $metadata['sizes']['thumbnail']['file'];

		$this->save_colors_for_attachment( $attachment_id, $thumbnail_path );

		return $metadata;
	}

	public function action_wp_head() {

		global $post;

		$images = $this->get_all_images();

		$this->generate_style( $images );
	 }

	protected function generate_style( $images ) {

		// generate a <style> tag
	}

	protected function save_colors_for_attachment( $id, $image_path ) {

		if ( get_post_meta( $id, 'image_colors', true ) ) {
			return;
		}
		$colors = $this->extract_colors( $image_path );
		update_post_meta( $id, 'image_colors', $colors );
	}

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

Gradient_Placeholder::get_instance();
