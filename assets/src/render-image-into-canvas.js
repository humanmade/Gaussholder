import reconstituteImage from './reconstitute-image';
import StackBlur from './stackblur';

const { GaussholderHeader } = window;

/**
 * Render an image into a Canvas
 *
 * @param {HTMLCanvasElement} canvas Canvas element to render into
 * @param {list} image 3-tuple of base64-encoded image data, width, height
 * @param {list} final Final width and height
 */
function renderImageIntoCanvas( canvas, image, final, cb ) {
	let ctx = canvas.getContext( '2d' ),
		width = parseInt( final[0] ),
		height = parseInt( final[1] ),
		radius = parseInt( final[2] );

	// Ensure smoothing is off
	ctx.mozImageSmoothingEnabled = false;
	ctx.webkitImageSmoothingEnabled = false;
	ctx.msImageSmoothingEnabled = false;
	ctx.imageSmoothingEnabled = false;

	let img = new Image();
	img.src = 'data:image/jpg;base64,' + reconstituteImage( GaussholderHeader, image );
	img.onload = function () {
		canvas.width = width;
		canvas.height = height;

		ctx.drawImage( img, 0, 0, width, height );
		StackBlur.canvasRGB( canvas, 0, 0, width, height, radius );
		cb();
	};
}

export default renderImageIntoCanvas;
