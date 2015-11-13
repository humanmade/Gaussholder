<?php

namespace Gaussholder\Frontend;

use Gaussholder;
use Gaussholder\JPEG;

/**
 * Set up hooked callbacks on plugins_loaded
 */
function bootstrap() {
	add_action( 'wp_footer', __NAMESPACE__ . '\\output_script' );

	add_filter( 'the_content', __NAMESPACE__ . '\\mangle_images', 30 );
	add_filter( 'wp_get_attachment_image_attributes', __NAMESPACE__ .  '\\add_placeholder_to_attributes', 10, 3 );
}

/**
 * Output the Gaussholder script onto the page.
 */
function output_script() {
	// Inline for performance
	if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) {
		$files = array(
			Gaussholder\PLUGIN_DIR . '/assets/stackblur.js',
			Gaussholder\PLUGIN_DIR . '/assets/gaussholder.js',
		);
	} else {
		$files = array(
			Gaussholder\PLUGIN_DIR . '/assets/gaussholder.min.js',
		);
	}

	echo '<script>';

	// Output header onto the page
	echo 'var GaussholderRadius = ' . Gaussholder\get_blur_radius() . ";\n";
	$header = JPEG\build_header();
	$header['header'] = base64_encode( $header['header'] );
	echo 'var GaussholderHeader = ' . json_encode( $header ) . ";\n";

	foreach ( $files as $file ) {
		echo file_get_contents( $file ) . "\n";
	}

	echo 'Gaussholder();</script>';
}

/**
 * Mangle <img> tags in the post content.
 *
 * Replaces the <img> tag src to stop browsers loading the source early, as well
 * as adding the Gaussholder data.
 * @param [type] $content [description]
 * @return [type] [description]
 */
function mangle_images( $content ) {
	// Find images
	$searcher = '#<img[^>]+(?:class=[\'"]([^\'"]+wp-image-(\d+)[^\'"]*)|data-gaussholder-id="(\d+)")[^>]+>#x';
	if ( ! preg_match_all( $searcher, $content, $images, PREG_SET_ORDER ) ) {
		return $content;
	}

	$blank = file_get_contents( Gaussholder\PLUGIN_DIR . '/assets/blank.gif' );
	$blank_url = 'data:image/gif;base64,' . base64_encode( $blank );

	foreach ( $images as $image ) {
		$tag = $image[0];
		if ( ! empty( $image[2] ) ) {
			// Singular image, using `class="wp-image-<id>"`
			$id = $image[2];
			$class = $image[1];
			if ( ! preg_match( '#\bsize-([\w-]+)\b#', $class, $size_match ) ) {
				continue;
			}
			$size = $size_match[1];
		} else {
			// Gallery, using `data-gaussholder-id="<id>"`
			$id = $image[3];
			if ( ! preg_match( '# class=[\'"][^\'"]*\battachment-([\w-]+)\b#', $tag, $size_match ) ) {
				continue;
			}
			$size = $size_match[1];
		}

		if ( ! Gaussholder\is_enabled_size( $size ) ) {
			continue;
		}

		$new_attrs = array();

		// Replace src with our blank GIF
		$new_attrs[] = 'src="' . esc_attr( $blank_url ) . '"';

		// Add the actual placeholder
		$placeholder = Gaussholder\get_placeholder( $id, $size );
		$new_attrs[] = 'data-gaussholder="' . esc_attr( $placeholder ) . '"';

		// Add final size
		$size_data = Gaussholder\get_size_data( $size );

		// Has the size been overridden?
		if ( preg_match( '#height=[\'"](\d+)[\'"]#i', $tag, $matches ) ) {
			$size_data['height'] = absint( $matches[1] );
		}
		if ( preg_match( '#width=[\'"](\d+)[\'"]#i', $tag, $matches ) ) {
			$size_data['width'] = absint( $matches[1] );
		}
		$new_attrs[] = 'data-gaussholder-size="' . $size_data['width'] . ',' . $size_data['height'] . '"';

		$mangled_tag = str_replace( ' src="', ' ' . implode( ' ', $new_attrs ) . ' data-original="', $tag );
		$content = str_replace( $tag, $mangled_tag, $content );
	}

	return $content;
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
function add_placeholder_to_attributes( $attr, $attachment, $size ) {
	// Are placeholders enabled for this size?
	if ( ! Gaussholder\is_enabled_size( $size ) ) {
		return $attr;
	}

	$attr['data-gaussholder-id'] = $attachment->ID;

	return $attr;
}
