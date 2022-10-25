import intersectionHandler from './intersection-handler';

/**
 * Create an event listener to load images when the content has been changed after a page load.
 */
const eventListenerLoadImages = function () {
	window.addEventListener( 'gaussholder.loadImages', function ( event ) {
		const images = document.getElementsByTagName( 'img' );
		intersectionHandler( images );
	} );
};

export default eventListenerLoadImages;
