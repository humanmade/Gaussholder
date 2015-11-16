window.Gaussholder = (function (header, radius) {
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
	 * @param {int} radius Gaussian blur radius
	 * @param {list} final Final width and height
	 */
	var render = function (canvas, image, radius, final, cb) {
		var ctx = canvas.getContext('2d'),
			width = parseInt( final[0] ),
			height = parseInt( final[1] );

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
		if ( ! ( 'gaussholder' in element.dataset ) ) {
			return;
		}

		var canvas = document.createElement('canvas');
		var final = element.dataset.gaussholderSize.split(',');

		// Set the dimensions...
		element.style.width = final[0] + 'px';
		element.style.height = final[1] + 'px';

		// ...then recalculate based on what it actually renders as
		var original = [ final[0], final[1] ];
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

		render(canvas, element.dataset.gaussholder.split(','), radius, final, function () {
			// Load in as our background image
			element.style.backgroundImage = 'url("' + canvas.toDataURL() + '")';
			element.style.backgroundRepeat = 'no-repeat';
		});
	};

	var loadOriginal = function (element) {
		if ( ! ( 'original' in element.dataset ) ) {
			return;
		}

		// Load our image now
		var img = new Image();
		img.src = element.dataset.original;
		// img.onload = function () {
			element.src = img.src;
		// };
	};

	/**
	 * Render all placeholders on the page
	 */
	var handleAll = function () {
		var images = document.getElementsByTagName('img');
		for (var i = images.length - 1; i >= 0; i--) {
			handleElement(images[i]);
		}
		for (var i = images.length - 1; i >= 0; i--) {
			loadOriginal(images[i]);
		}
	};

	return handleAll;
})(window.GaussholderHeader, window.GaussholderRadius);