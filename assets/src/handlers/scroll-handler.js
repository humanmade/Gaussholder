import loadImageCallback from '../load-original';
import throttle from '../throttle';

let loadLazily = [];

/**
 * Handle images when scrolling. Suitable for older browsers.
 */
const scrollHandler = function () {
	let threshold = 1200;
	let next = [];
	for ( let i = loadLazily.length - 1; i >= 0; i-- ) {
		let img = loadLazily[i];
		let shouldShow = img.getBoundingClientRect().top <= ( window.innerHeight + threshold );
		if ( ! shouldShow ) {
			next.push( img );
			continue;
		}

		loadImageCallback( img );
	}
	loadLazily = next;
};

/**
 * Scroll handle initialization.
 *
 * @param {NodeList} images List of images on screen.
 */
const init = function ( images ) {
	loadLazily = images;

	const throttledHandler = throttle( scrollHandler, 40 );
	scrollHandler();
	window.addEventListener( 'scroll', throttledHandler );

	const finishedTimeoutCheck = window.setInterval( function () {
		if ( loadLazily.length < 1 ) {
			window.removeEventListener( 'scroll', throttledHandler );
			window.clearInterval( finishedTimeoutCheck );
		}
	}, 1000 );
};

export default init;
