// Fade duration in ms when the image loads in.
const FADE_DURATION = 800;

/**
 * Load the original image. Triggered once the image is on the viewport.
 *
 * @param {Node} element Image element
 */
let loadOriginal = function ( element ) {
	if ( ! ( 'originalsrc' in element.dataset ) && ! ( 'originalsrcset' in element.dataset ) ) {
		return;
	}

	let data = element.dataset.gaussholderSize.split( ',' ),
		radius = parseInt( data[2] );

	// Load our image now
	let img = new Image();

	if ( element.dataset.originalsrc ) {
		img.src = element.dataset.originalsrc;
	}
	if ( element.dataset.originalsrcset ) {
		img.srcset = element.dataset.originalsrcset;
	}

	/**
	 *
	 */
	img.onload = function () {
		// Filter property to use
		let filterProp = ( 'webkitFilter' in element.style ) ? 'webkitFilter' : 'filter';
		element.style[ filterProp ] = 'blur(' + radius * 0.5 + 'px)';

		// Ensure blur doesn't bleed past image border
		element.style.clipPath = 'url(#gaussclip)'; // Current FF
		element.style.clipPath = 'inset(0)'; // Standard
		element.style.webkitClipPath = 'inset(0)'; // WebKit

		// Set the actual source
		element.src = img.src;
		element.srcset = img.srcset;

		// Cleaning source
		element.dataset.originalsrc = '';
		element.dataset.originalsrcset = '';

		// Clear placeholder temporary image
		// (We do this after setting the source, as doing it before can
		// cause a tiny flicker)
		element.style.backgroundImage = '';
		element.style.backgroundRepeat = '';

		let start = 0;

		/**
		 * @param {number} ts Timestamp.
		 */
		const anim = function ( ts ) {
			if ( ! start ) start = ts;
			let diff = ts - start;
			if ( diff > FADE_DURATION ) {
				element.style[ filterProp ] = '';
				element.style.clipPath = '';
				element.style.webkitClipPath = '';
				return;
			}

			let effectiveRadius = radius * ( 1 - ( diff / FADE_DURATION ) );

			element.style[ filterProp ] = 'blur(' + effectiveRadius * 0.5 + 'px)';
			window.requestAnimationFrame( anim );
		};
		window.requestAnimationFrame( anim );
	};
};

export default loadOriginal;
