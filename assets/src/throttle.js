/**
 * See https://stackoverflow.com/questions/27078285/simple-throttle-in-js
 *
 * @param {Function} callback Function to throttle.
 * @param {number} limit Throttle time
 *
 * @returns {function(): void} throttleled callback.
 */
const throttle = function ( callback, limit ) {
	let waiting = false;
	return function () {
		if ( ! waiting ) {
			callback.apply( this, arguments );
			waiting = true;
			setTimeout( function () {
				waiting = false;
			}, limit );
		}
	};
};

export default throttle;
