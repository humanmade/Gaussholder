import loadImageCallback from '../load-original';

/**
 * Handles the images on screen by using the Intersection Observer API.
 *
 * @param {NodeList} images List of images in DOM to handle.
 */
const intersectionHandler = function ( images ) {
	const options = {
		rootMargin: '1200px', // Threshold that Intersection API uses to detect the intersection between the image  and the main element in the page.
	};

	const imagesObserver = new IntersectionObserver( entries => {
		const visibleImages = entries.filter( ( { isIntersecting } ) => isIntersecting === true );

		visibleImages.forEach( ( { target } ) => {
			loadImageCallback( target );
			imagesObserver.unobserve( target );
		} );
	}, options );

	Array.from( images ).forEach( img => {
		imagesObserver.observe( img );
	} );
};

export default intersectionHandler;
