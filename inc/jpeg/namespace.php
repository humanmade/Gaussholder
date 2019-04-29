<?php
/**
 * Preview images before loading full detail
 *
 * Thanks to Facebook for their fantastic article inspiring this:
 * https://code.facebook.com/posts/991252547593574/the-technology-behind-preview-photos/
 */

namespace Gaussholder\JPEG;

use Imagick;

/**
 * Build a standard JFIF header
 *
 * @return array `header` element contains the JFIF header, `height_offset` contains the byte offset for the 2 height bytes, `length_offset` contains the byte offset for the 2 length bytes
 */
function build_header() {
	// JFIF start of image
	$header = "\xFF\xD8";

	// Start JFIF-APP0 section
	$header .= "\xFF\xE0";

		// Header length (16 bytes)
		$header .= "\x00\x10";

		// Header ID: JFIF in ASCII with null terminator
		$header .= "JFIF\x00";

		// JFIF version (major minor)
		$header .= "\x01\x01";

		// Pixel density units:
		//   00 = pixel aspect
		//   01 = pixels per inch
		//   02 = pixels per centimetre
		$header .= "\x01";

		// X density (72dpi)
		$header .= "\x00\x48";

		// Y density (72dpi)
		$header .= "\x00\x48";

		// X thumbnail size
		$header .= "\x00";

		// Y thumbnail size
		$header .= "\x00";

	// Start quantization table (table K.1)
	$header .= "\xFF\xDB";
		$header .= "\x00\x43\x00\x03\x02\x02\x03\x02\x02\x03\x03\x03\x03\x04\x03\x03\x04\x05\x08\x05\x05\x04\x04\x05\x0A\x07\x07\x06\x08\x0C\x0A\x0C\x0C\x0B\x0A\x0B\x0B\x0D\x0E\x12\x10\x0D\x0E\x11\x0E\x0B\x0B\x10\x16\x10\x11\x13\x14\x15\x15\x15\x0C\x0F\x17\x18\x16\x14\x18\x12\x14\x15\x14";

	// Another quantization table again? (table K.2)
	$header .= "\xFF\xDB";
		$header .= "\x00\x43\x01\x03\x04\x04\x05\x04\x05\x09\x05\x05\x09\x14\x0D\x0B\x0D\x14\x14\x14\x14\x14\x14\x14\x14\x14\x14\x14\x14\x14\x14\x14\x14\x14\x14\x14\x14\x14\x14\x14\x14\x14\x14\x14\x14\x14\x14\x14\x14\x14\x14\x14\x14\x14\x14\x14\x14\x14\x14\x14\x14\x14\x14\x14\x14\x14\x14";

	// Start Of Frame
	$header .= "\xFF\xC0";

		// Frame length (17 bytes)
		$header .= "\x00\x11";

		// Sample precision
		$header .= "\x08";

		// Y size (in pixels, 2 bytes) *****CHANGE ME*****
		$height_offset = strlen( $header );
		$header .= "\xCA\xFE";

		// X size (in pixels, 2 bytes) *****CHANGE ME*****
		$length_offset = strlen( $header );
		$header .= "\xDE\xAD";

		// Nf - number of components (3 for coloured JPEG)
		$header .= "\x03";

		// For each component:

			// Component 1
				// Component ID
				$header .= "\x01";

				// H and V sampling (nibble for each)
				$header .= "\x11";

				// Quantization table number
				$header .= "\x00";

			// Component 2
				// Component ID
				$header .= "\x02";

				// H and V sampling (nibble for each)
				$header .= "\x11";

				// Quantization table number
				$header .= "\x01";

			// Component 3
				// Component ID
				$header .= "\x03";

				// H and V sampling (nibble for each)
				$header .= "\x11";

				// Quantization table number
				$header .= "\x01";

	// Huffman table from K.3.3.1 in JPEG specification: table K.3, luminance DC
	$header .= "\xFF\xC4";

		// Length (31 bytes)
		$header .= "\x00\x1F";

		// Information (0..3: number of tables, 4: type, 5..7: must be 0)
		// Type DC, table 0
		$header .= "\x00";

		// Number of symbols
		$header .= "\x00\x01\x05\x01\x01\x01\x01\x01\x01\x00\x00\x00\x00\x00\x00\x00";

		// Symbols!
		$header .= "\x00\x01\x02\x03\x04\x05\x06\x07\x08\x09\x0A\x0B";

	// Huffman table from K.3.3.2; table K.5, luminance AC
	$header .= "\xFF\xC4";

		// Length (181 bytes)
		$header .= "\x00\xB5";

		// Information (0..3: number, 4: type, 5..7: 0)
		// Type AC, table 0
		$header .= "\x10";

		// Number of symbols
		$header .= "\x00\x02\x01\x03\x03\x02\x04\x03\x05\x05\x04\x04\x00\x00\x01\x7D";

		// Symbols!
		$header .= "\x01\x02\x03\x00\x04\x11\x05\x12\x21\x31\x41\x06\x13\x51\x61\x07\x22\x71\x14\x32\x81\x91\xA1\x08\x23\x42\xB1\xC1\x15\x52\xD1\xF0\x24\x33\x62\x72\x82\x09\x0A\x16\x17\x18\x19\x1A\x25\x26\x27\x28\x29\x2A\x34\x35\x36\x37\x38\x39\x3A\x43\x44\x45\x46\x47\x48\x49\x4A\x53\x54\x55\x56\x57\x58\x59\x5A\x63\x64\x65\x66\x67\x68\x69\x6A\x73\x74\x75\x76\x77\x78\x79\x7A\x83\x84\x85\x86\x87\x88\x89\x8A\x92\x93\x94\x95\x96\x97\x98\x99\x9A\xA2\xA3\xA4\xA5\xA6\xA7\xA8\xA9\xAA\xB2\xB3\xB4\xB5\xB6\xB7\xB8\xB9\xBA\xC2\xC3\xC4\xC5\xC6\xC7\xC8\xC9\xCA\xD2\xD3\xD4\xD5\xD6\xD7\xD8\xD9\xDA\xE1\xE2\xE3\xE4\xE5\xE6\xE7\xE8\xE9\xEA\xF1\xF2\xF3\xF4\xF5\xF6\xF7\xF8\xF9\xFA";

	// Huffman table from K.3.3.1; table K.4, chrominance DC
	$header .= "\xFF\xC4";

		// Length (31 bytes)
		$header .= "\x00\x1F";

		// Information (0..3: number, 4: type, 5..7: 0)
		// Type DC, table 1
		$header .= "\x01";

		// Number of symbols
		$header .= "\x00\x03\x01\x01\x01\x01\x01\x01\x01\x01\x01\x00\x00\x00\x00\x00";

		// Symbols!
		$header .= "\x00\x01\x02\x03\x04\x05\x06\x07\x08\x09\x0A\x0B";

	// Huffman table from K.3.3.2; table K.6, chrominance AC
	$header .= "\xFF\xC4";

		// Length (181 bytes)
		$header .= "\x00\xB5";

		// Information
		// Type AC, table 1
		$header .= "\x11";

		// Number of symbols
		$header .= "\x00\x02\x01\x02\x04\x04\x03\x04\x07\x05\x04\x04\x00\x01\x02\x77";

		// Symbols!
		$header .= "\x00\x01\x02\x03\x11\x04\x05\x21\x31\x06\x12\x41\x51\x07\x61\x71\x13\x22\x32\x81\x08\x14\x42\x91\xA1\xB1\xC1\x09\x23\x33\x52\xF0\x15\x62\x72\xD1\x0A\x16\x24\x34\xE1\x25\xF1\x17\x18\x19\x1A\x26\x27\x28\x29\x2A\x35\x36\x37\x38\x39\x3A\x43\x44\x45\x46\x47\x48\x49\x4A\x53\x54\x55\x56\x57\x58\x59\x5A\x63\x64\x65\x66\x67\x68\x69\x6A\x73\x74\x75\x76\x77\x78\x79\x7A\x82\x83\x84\x85\x86\x87\x88\x89\x8A\x92\x93\x94\x95\x96\x97\x98\x99\x9A\xA2\xA3\xA4\xA5\xA6\xA7\xA8\xA9\xAA\xB2\xB3\xB4\xB5\xB6\xB7\xB8\xB9\xBA\xC2\xC3\xC4\xC5\xC6\xC7\xC8\xC9\xCA\xD2\xD3\xD4\xD5\xD6\xD7\xD8\xD9\xDA\xE2\xE3\xE4\xE5\xE6\xE7\xE8\xE9\xEA\xF2\xF3\xF4\xF5\xF6\xF7\xF8\xF9\xFA";

	// Start of Scan marker, get ready for an image!
	$header .= "\xFF\xDA";

	return compact( 'header', 'height_offset', 'length_offset' );
}

/**
 * Get data for a file
 *
 * @param string $file Image file path
 * @param int $radius Gaussian blur radius
 * @return array 3-tuple of binary image data (string), width (int), and height (int).
 */
function data_for_file( $file, $radius ) {
	if ( parse_url( $file, PHP_URL_SCHEME ) ) {
		$editor = new Imagick();
		$editor->readImageBlob( file_get_contents( $file ) );
	} else {
		$editor = new Imagick( $file );
	}

	$size = $editor->getImageGeometry();

	// Normalise the density to 72dpi
	$editor->setImageResolution( 72, 72 );

	// Set sampling factors to constant
	$editor->setSamplingFactors(array('1x1', '1x1', '1x1'));

	// Ensure we use default Huffman tables
	$editor->setOption('jpeg:optimize-coding', false);

	// Strip unnecessary header data
	$editor->stripImage();

	// Adjust by scaling factor
	$width = floor( $size['width'] / $radius );
	$height = floor( $size['height'] / $radius );
	$editor->scaleImage( $width, $height );

	$scaled = $editor->getImageBlob();

	// Strip the header
	$scaled_stripped = substr( $scaled, strpos( $scaled, "\xFF\xDA" ) + 2 );

	return array( $scaled_stripped, $width, $height );
}
