import eventListenerLoadImages from './handlers/event-listener';
import handleElement from './handlers/handle-element';
import intersectionHandler from './handlers/intersection-handler';
import scrollHandler from './handlers/scroll-handler';

/**
 * Initializes Gaussholder.
 */
export default function () {

	const images = document.getElementsByTagName( 'img' );

	if ( typeof IntersectionObserver === 'undefined' ) {
		// Old browser. Handle events based on scrolling.
		scrollHandler( images );
	} else {
		// Use the Intersection Observer API.
		intersectionHandler( images );
	}

	// Add the event listener to load images.
	eventListenerLoadImages();

	// Initialize all images.
	Array.prototype.slice.call( images ).forEach( handleElement );
}
