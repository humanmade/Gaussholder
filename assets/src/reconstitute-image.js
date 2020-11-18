/**
 * @param {number} buffer Buffer Size.
 *
 * @returns {string} Base64 string.
 */
function arrayBufferToBase64( buffer ) {
	let binary = '';
	let bytes = new Uint8Array( buffer );
	let len = bytes.byteLength;
	for ( let i = 0; i < len; i++ ) {
		binary += String.fromCharCode( bytes[ i ] );
	}
	return window.btoa( binary );
}

/**
 * @param {*} header Gaussholder header.
 * @param {Array} image Image node.
 *
 * @returns {string} Base 64 string
 */
function reconstituteImage( header, image ) {
	let image_data = image[0],
		width = parseInt( image[1] ),
		height = parseInt( image[2] );

	let full = atob( header.header ) + atob( image_data );
	let bytes = new Uint8Array( full.length );
	for ( let i = 0; i < full.length; i++ ) {
		bytes[i] = full.charCodeAt( i );
	}

	// Poke the bits.
	bytes[ header.height_offset ] = ( ( height >> 8 ) & 0xFF );
	bytes[ header.height_offset + 1 ] = ( height & 0xFF );
	bytes[ header.length_offset ] = ( ( width >> 8 ) & 0xFF );
	bytes[ header.length_offset + 1] = ( width & 0xFF );

	// Back to a full JPEG now.
	return arrayBufferToBase64( bytes );
}

export default reconstituteImage;
