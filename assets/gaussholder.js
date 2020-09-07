window.Gaussholder = (function (header) {
	// Fade duration in ms when the image loads in.
	var fadeDuration = 800;

	var arrayBufferToBase64 = function( buffer ) {
		var binary = '';
		var bytes = new Uint8Array( buffer );
		var len = bytes.byteLength;
		for (var i = 0; i < len; i++) {
		    binary += String.fromCharCode( bytes[ i ] );
		}
		return window.btoa( binary );
	};

	var reconstituteImage = function (header, image) {
		var image_data = image[0],
			width = parseInt( image[1] ),
			height = parseInt( image[2] );

		var full = atob( header.header ) + atob( image_data );
		var bytes = new Uint8Array( full.length );
		for (var i = 0; i < full.length; i++) {
			bytes[i] = full.charCodeAt(i);
		}

		// Poke the bits.
		bytes[ header.height_offset ] = ( (height >> 8) & 0xFF);
		bytes[ header.height_offset + 1 ] = (height & 0xFF);
		bytes[ header.length_offset ] = ( (width >> 8) & 0xFF);
		bytes[ header.length_offset + 1] = (width & 0xFF);

		// Back to a full JPEG now.
		return arrayBufferToBase64( bytes );
	};

	/**
	 * Render an image into a Canvas
	 *
	 * @param {HTMLCanvasElement} canvas Canvas element to render into
	 * @param {list} image 3-tuple of base64-encoded image data, width, height
	 * @param {list} final Final width and height
	 */
	var render = function (canvas, image, final, cb) {
		var ctx = canvas.getContext('2d'),
			width = parseInt( final[0] ),
			height = parseInt( final[1] ),
			radius = parseInt( final[2] );

		// Ensure smoothing is off
		ctx.mozImageSmoothingEnabled = false;
		ctx.webkitImageSmoothingEnabled = false;
		ctx.msImageSmoothingEnabled = false;
		ctx.imageSmoothingEnabled = false;

		var img = new Image();
		img.src = 'data:image/jpg;base64,' + reconstituteImage(header, image);
		img.onload = function () {
			canvas.width = width;
			canvas.height = height;

			ctx.drawImage(img, 0, 0, width, height);
			StackBlur.canvasRGB( canvas, 0, 0, width, height, radius );
			cb();
		};
	};

	/**
	 * Render placeholder for an image
	 *
	 * @param {HTMLImageElement} element Element to render placeholder for
	 */
	var handleElement = function (element) {
		if ( element.complete ) {
			return;
		}

		if ( ! ( 'gaussholder' in element.dataset ) ) {
			return;
		}

		var canvas = document.createElement('canvas');
		var final = element.dataset.gaussholderSize.split(',');

		render(canvas, element.dataset.gaussholder.split(','), final, function () {
			// Load in as our background image
			element.style.backgroundRepeat = 'no-repeat';
			element.style.backgroundSize = 'cover';
			element.style.backgroundImage = 'url("' + canvas.toDataURL() + '")';
		});

		element.onload = function () {
			loadOriginal(element);
		};
	};

	var loadOriginal = function (element) {
		var data = element.dataset.gaussholderSize.split(','),
			radius = parseInt( data[2] );

		// Filter property to use
		var filterProp = ( 'webkitFilter' in element.style ) ? 'webkitFilter' : 'filter';
		element.style[ filterProp ] = 'blur(' + radius * 0.5 + 'px)';

		// Ensure blur doesn't bleed past image border
		element.style.clipPath = 'url(#gaussclip)'; // Current FF
		element.style.clipPath = 'inset(0)'; // Standard
		element.style.webkitClipPath = 'inset(0)'; // WebKit

		// Clear placeholder temporary image
		// (We do this after setting the source, as doing it before can
		// cause a tiny flicker)
		element.style.backgroundImage = '';
		element.style.backgroundRepeat = '';

		var start = 0;
		var anim = function (ts) {
			if ( ! start ) start = ts;
			var diff = ts - start;
			if ( diff > fadeDuration ) {
				element.style[ filterProp ] = '';
				element.style.clipPath = '';
				element.style.webkitClipPath = '';
				return;
			}

			var effectiveRadius = radius * ( 1 - ( diff / fadeDuration ) );

			element.style[ filterProp ] = 'blur(' + effectiveRadius * 0.5 + 'px)';
			window.requestAnimationFrame(anim);
		};
		window.requestAnimationFrame(anim);
	};

	/**
	 * Render all placeholders on the page
	 */
	var handleAll = function () {
		var images = document.getElementsByTagName('img');

		for (var i = images.length - 1; i >= 0; i--) {
			handleElement(images[i]);
		}
	};

	return handleAll;
})(window.GaussholderHeader);
