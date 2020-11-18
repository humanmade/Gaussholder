import loadImageCallback from '../load-original';
import throttle from '../throttle';

let loadLazily = [];

const scrollHandler = function() {
	var threshold = 1200;
	var next = [];
	for (var i = loadLazily.length - 1; i >= 0; i--) {
		var img = loadLazily[i];
		var shouldShow = img.getBoundingClientRect().top <= ( window.innerHeight + threshold );
		if ( ! shouldShow ) {
			next.push(img);
			continue;
		}

		loadImageCallback(img);
	}
	loadLazily = next;
}

export default ( images ) => {
	loadLazily = images;

	const throttledHandler = throttle( scrollHandler, 40 );
	scrollHandler();
	window.addEventListener('scroll', throttledHandler );

	const finishedTimeoutCheck = window.setInterval( function() {
		if ( loadLazily.length < 1 ) {
			window.removeEventListener('scroll', throttledHandler );
			window.clearInterval( finishedTimeoutCheck );
		}
	}, 1000);
}
