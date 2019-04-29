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

		render(canvas, element.dataset.gaussholder.split(','), final, function () {
			// Load in as our background image
			element.style.backgroundImage = 'url("' + canvas.toDataURL() + '")';
			element.style.backgroundRepeat = 'no-repeat';
		});
	};

	var loadOriginal = function (element) {
		if ( ! ( 'originalsrc' in element.dataset ) && ! ( 'originalsrcset' in element.dataset ) ) {
			return;
		}

		var data = element.dataset.gaussholderSize.split(','),
			radius = parseInt( data[2] );

		// Load our image now
		var img = new Image();

		if ( element.dataset.originalsrc ) {
			img.src = element.dataset.originalsrc;
		}
		if ( element.dataset.originalsrcset ) {
			img.srcset = element.dataset.originalsrcset;
		}

		img.onload = function () {
			// Filter property to use
			var filterProp = ( 'webkitFilter' in element.style ) ? 'webkitFilter' : 'filter';
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
	};

	var loadLazily = [];
	var threshold = 1200;
	var lastRun = 0,
		loopTimeout = null;

	var scrollHandler = function () {
		var now = Date.now();
		if ( ( lastRun + 40 ) > now ) {
			if ( loopTimeout ) {
				return;
			}
			loopTimeout = window.setTimeout(scrollHandler, 40);
			return;
		}
		lastRun = now;
		loopTimeout && (loopTimeout = null);

		var next = [];
		for (var i = loadLazily.length - 1; i >= 0; i--) {
			var img = loadLazily[i];
			var shouldShow = img.getBoundingClientRect().top <= ( window.innerHeight + threshold );
			if ( ! shouldShow ) {
				next.push(img);
				continue;
			}

			loadOriginal(img);
		}
		loadLazily = next;
		if (loadLazily.length < 1) {
			window.removeEventListener('scroll', scrollHandler);
		}
	};

	/**
	 * Render all placeholders on the page
	 */
	var handleAll = function () {
		var images = document.getElementsByTagName('img');

		for (var i = images.length - 1; i >= 0; i--) {
			var img = images[i];

			// Ensure the blank GIF has loaded first
			if ( img.complete ) {
				handleElement(img);
			} else {
				img.onload = function () {
					handleElement(this);
				}
			}
		}

		loadLazily = images;
		scrollHandler();

		if (loadLazily.length > 0) {
			window.addEventListener('scroll', scrollHandler);
		}
	};

	return handleAll;
})(window.GaussholderHeader);
