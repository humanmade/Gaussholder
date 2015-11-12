<?php

namespace Gaussholder;

/**
 * Set up hooked callbacks on plugins_loaded
 */
function plugins_loaded() {

	add_filter( 'wp_generate_attachment_metadata', __NAMESPACE__ .  '\\filter_wp_generate_attachment_metadata', 10, 2 );

	add_filter( 'wp_get_attachment_image_attributes', __NAMESPACE__ .  '\\filter_wp_get_attachment_image_attributes', 10, 3 );

	add_filter( 'image_send_to_editor', __NAMESPACE__ .  '\\filter_image_send_to_editor', 10, 8 );
}

/**
 * Adds the style attribute to the image HTML.
 *
 * @param $html
 * @param $id
 * @param $caption
 * @param $title
 * @param $align
 * @param $url
 * @param $size
 * @param $alt
 *
 * @return mixed
 */
function filter_image_send_to_editor( $html, $id, $caption, $title, $align, $url, $size, $alt ) {

	$colors_hex = get_colors_for_attachment( $id );

	if ( ! $colors_hex ) {
		return $html;
	}

	$display_type = get_site_option( 'hmip_placeholder_type' );

	if ( 'gradient' === $display_type ) {
		$style = get_gradient_style( $colors_hex );
	} else {
		$style = get_solid_style( $colors_hex );
	}

	$html = preg_replace( '/<img/', '<img style="' . $style . '" ', $html );

	return $html;
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

	$colors_hex = get_colors_for_attachment( $attachment->ID );

	if ( ! $colors_hex ) {
		return $attr;
	}

	$display_type = get_site_option( 'hmip_placeholder_type' );

	if ( 'gradient' === $display_type ) {
		$attr['style'] = get_gradient_style( $colors_hex );
	} else {
		$attr['style'] = get_solid_style( $colors_hex );
	}

	return $attr;
}

/**
 * Style attribute for gradient background.
 *
 * @param $hex_colors
 *
 * @return string
 */
function get_gradient_style( $hex_colors ) {

	foreach ( $hex_colors as $hex ) {
		$colors[] = implode( ',', hex2rgb( $hex ) ) . ',0';
		$colors[] = implode( ',', hex2rgb( $hex ) ) . ',1';
	}

	$gradients = array();
	$gradient_angles = array( '90', '0', '-90', '-180' );

	foreach ( $gradient_angles as $key => $gradient_angle ) {
		$gradients[] = sprintf( "linear-gradient(%sdeg, rgba(%s) 0%%, rgba(%s) 100%%, rgba(%s) 100%%)", $gradient_angle, $colors[ $key ], $colors[ $key + 1 ], $colors[ $key + 1 ] );
	}

	$style = 'background:' . implode( $gradients, ', ' ) . ';';

	return $style;
}

/**
 * Style attribute for solid backgrounds.
 *
 * @param $colors
 *
 * @return string
 */
function get_solid_style( $colors ) {

	return 'background:' . reset( $colors ) . ';';
}

/**
 * Get the image's dominant colors
 *
 * @param string $image_path
 * @param string $mime_type
 *
 * @return array|WP_Error
 */
function extract_colors( $image_path, $mime_type ) {

	$client = new ColorExtractor;

	$image = '';

	switch ( $mime_type ) {

		case 'image/gif':
			$image = $client->loadGif( $image_path );
			break;

		case 'image/png':
			$image = $client->loadPng( $image_path );
			break;

		case 'image/jpg':
		case 'image/jpeg':
			$image = $client->loadJpeg( $image_path );
	}

	if ( empty( $image ) ) {
		return new \WP_Error( 'hmip_wrong_mime_type', __( 'Could not extract colors from this file.', 'hmip' ) );
	}

	// Get 2 most used color hex code
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

	$colors = calculate_colors_for_attachment( $attachment_id );

	if ( ! is_wp_error( $colors ) ) {
		save_colors_for_attachment( $attachment_id, $colors );
	}

	return $metadata;
}

/**
 * Get the stored colors for the image
 * @param $id
 *
 * @return mixed
 */
function get_colors_for_attachment( $id ) {

	return get_post_meta( $id, 'hmgp_image_colors', true );

}

/**
 * Extract the colors from the image
 *
 * @param $id
 * @param array $colors
 */
function save_colors_for_attachment( $id, Array $colors = array() ) {

	// Validate hex codes.
	$colors = array_filter( $colors, function( $color ) {
		return preg_match( '/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $color );
	} );

	// Restrict to 2 items.
	$colors = array_slice( $colors, 0, 4 );

	update_post_meta( $id, 'hmgp_image_colors', $colors );

}

/**
 * Calculate the colors from the image
 *
 * @param $id
 *
 * @return array|void
 */
function calculate_colors_for_attachment( $id ) {

	$img       = wp_get_attachment_image_src( $id, 'thumbnail' );
	$uploads   = wp_upload_dir();
	$path      = str_replace( $uploads['baseurl'], $uploads['basedir'], $img[0] );
	$mime_type = get_post_mime_type( $id );

	return extract_colors( $path, $mime_type );

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
