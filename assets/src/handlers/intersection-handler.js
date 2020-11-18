const intersectionHandler = function( images, loadImageCallback ) {
	const options = {
		rootMargin: '1200px'
	};

	const imagesObserver = new IntersectionObserver( (entries) => {
		const visibleImages = entries.filter( ( { isIntersecting } ) => isIntersecting === true );

		visibleImages.forEach( ( { target } ) => {
			loadImageCallback( target );
			imagesObserver.unobserve( target );
		} );
	}, options);

	Array.from( images ).forEach( ( img ) => {
		imagesObserver.observe( img );
	})
}

export default intersectionHandler;
