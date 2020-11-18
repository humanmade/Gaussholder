const scrollHandler = function( images, loadImageCallback ) {
	var loadLazily = images;
	var threshold = 1200;
	var lastRun = 0,
		loopTimeout = null;
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

		loadImageCallback(img);
	}
	loadLazily = next;
	if (loadLazily.length < 1) {
		window.removeEventListener('scroll', scrollHandler);
	}
}

export default scrollHandler;
