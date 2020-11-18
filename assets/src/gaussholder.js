import renderImageIntoCanvas from './render-image-into-canvas';
import loadOriginal from './load-original';
import handleElement from './handlers/handle-element';
import intersectionHandler from './handlers/intersection-handler';
import scrollHandler from './handlers/scroll-handler';

export default function () {
	const images = document.getElementsByTagName( 'img' );

	if ( typeof IntersectionObserver === 'undefined' ) {
		// Old browser. Handle events based on scrolling.
		scrollHandler( images, loadOriginal );
	}
	else {
		// Use the Intersection Observer API.
		intersectionHandler( images, loadOriginal );
	}

	// Initialize all images.
	Array.prototype.slice.call( images ).forEach( handleElement );
}
