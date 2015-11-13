<?php

namespace Gaussholder;

const META_PREFIX = 'gaussholder_';

/**
 * Get sizes to use placeholders on.
 *
 * @return string[] List of enabled sizes.
 */
function get_enabled_sizes() {
	/**
	 * Filter the sizes Gaussholder images will be generated for.
	 *
	 * By default, Gaussholder won't generate any placeholders, and you need to
	 * opt-in to using it. Simply filter here, and add the size names for what
	 * you want generated.
	 *
	 * Be aware that for every size you add, a placeholder will be generated and
	 * stored in the database. If you have a lot of sizes, this will be a _lot_
	 * of data.
	 *
	 * @param string[] $enabled Enabled sizes.
	 */
	return apply_filters( 'gaussholder.image_sizes', array() );
}

function get_blur_radius() {
	/**
	 * Filter the blur radius.
	 *
	 * The blur radius controls how much blur we use. The image is pre-scaled
	 * down by this factor, and this is really the key to how the placeholders
	 * work. Increasing radius decreases the required data quadratically: a
	 * radius of 2 uses a quarter as much data as the full image; a radius of
	 * 8 uses 1/64 the amount of data. (Due to compression, the final result
	 * will _not_ follow this scaling.)
	 *
	 * Be careful tuning this, as decreasing the radius too much will cause a
	 * huge amount of data in the body; increasing it will end up with not
	 * enough data to be an effective placeholder.
	 *
	 * (Also note: changing this requires regenerating the placeholder data.)
	 *
	 * @param int $radius Blur radius in pixels.
	 */
	return apply_filters( 'gaussholder.blur_radius', 16 );
}

/**
 * Is the size enabled for placeholders?
 *
 * @param string $size Image size to check.
 * @return boolean True if enabled, false if not. Simple.
 */
function is_enabled_size( $size ) {
	return in_array( $size, get_enabled_sizes() );
}

/**
 * Get a placeholder for an image.
 *
 * @param int $id Attachment ID.
 * @param string $size Image size.
 * @return string
 */
function get_placeholder( $id, $size ) {
	if ( ! is_enabled_size( $size ) ) {
		return null;
	}

	$meta = get_post_meta( $id, META_PREFIX . $size, true );
	if ( empty( $meta ) ) {
		return null;
	}

	return $meta;
}

/**
 * Save extracted colors to image metadata
 *
 * @param $metadata
 * @param $attachment_id
 *
 * @return mixed
 */
function generate_placeholders_on_save( $metadata, $attachment_id ) {
	// Is this a JPEG?
	$mime_type = get_post_mime_type( $attachment_id );
	if ( ! in_array( $mime_type, array( 'image/jpg', 'image/jpeg' ) ) ) {
		return $metadata;
	}

	$sizes = get_enabled_sizes();

	foreach ( $sizes as $size ) {
		$data = generate_placeholder( $attachment_id, $size );
		if ( empty( $data ) ) {
			continue;
		}

		// Comma-separated data, width, and height
		$for_database = sprintf( '%s,%d,%d', base64_encode( $data[0] ), $data[1], $data[2] );
		update_post_meta( $attachment_id, META_PREFIX . $size, $for_database );
	}

	return $metadata;
}

/**
 * Get data for a given image size.
 *
 * @param string $size Image size.
 * @return array|null Image size data (with `width`, `height`, `crop` keys) on success, null if image size is invalid.
 */
function get_size_data( $size ) {
	global $_wp_additional_image_sizes;

	switch ( $size ) {
		case 'thumbnail':
		case 'medium':
		case 'large':
			$size_data = array(
				'width'  => get_option( "{$size}_size_w" ),
				'height' => get_option( "{$size}_size_h" ),
				'crop'   => get_option( "{$size}_crop" ),
			);
			break;

		default:
			if ( ! isset( $_wp_additional_image_sizes[ $size ] ) ) {
				return null;
			}

			$size_data = $_wp_additional_image_sizes[ $size ];
			break;
	}

	return $size_data;
}

/**
 * Generate a placeholder at a given size.
 *
 * @param int $id Attachment ID.
 * @param string $size Image size.
 * @return array|null 3-tuple of binary image data (string), width (int), height (int) on success; null on error.
 */
function generate_placeholder( $id, $size ) {
	$size_data = get_size_data( $size );
	if ( empty( $size_data ) ) {
		_doing_it_wrong( __FUNCTION__, __( 'Invalid image size enabled for placeholders', 'gaussholder' ) );
		return null;
	}

	$img       = wp_get_attachment_image_src( $id, $size );
	$uploads   = wp_upload_dir();
	$path      = str_replace( $uploads['baseurl'], $uploads['basedir'], $img[0] );

	return JPEG\data_for_file( $path, get_blur_radius() );
}
