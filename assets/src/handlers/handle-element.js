import renderImageIntoCanvas from '../render-image-into-canvas';

/**
 * Render placeholder for an image
 *
 * @param {HTMLImageElement} element Element to render placeholder for
 */
let handleElement = function ( element ) {
	if ( ! ( 'gaussholder' in element.dataset ) ) {
		return;
	}

	let canvas = document.createElement( 'canvas' );
	let final = element.dataset.gaussholderSize.split( ',' );

	// Set the dimensions...
	element.style.width = final[0] + 'px';
	element.style.height = final[1] + 'px';

	// ...then recalculate based on what it actually renders as
	let original = [ final[0], final[1] ];
	if ( element.width < final[0] ) {
		// Rescale, keeping the aspect ratio
		final[0] = element.width;
		final[1] = final[1] * ( final[0] / original[0] );
	} else if ( element.height < final[1] ) {
		// Rescale, keeping the aspect ratio
		final[1] = element.height;
		final[0] = final[0] * ( final[1] / original[1] );
	}

	// Set dimensions, _again_
	element.style.width = final[0] + 'px';
	element.style.height = final[1] + 'px';

	renderImageIntoCanvas( canvas, element.dataset.gaussholder.split( ',' ), final, function () {
		// Load in as our background image
		element.style.backgroundImage = 'url("' + canvas.toDataURL() + '")';
		element.style.backgroundRepeat = 'no-repeat';
	} );
};

export default handleElement;
