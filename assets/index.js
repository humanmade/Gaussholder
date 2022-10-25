import Gaussholder from './src/gaussholder';

document.addEventListener( 'DOMContentLoaded', Gaussholder );

window.addEventListener( 'gaussholder.loadImages', function () {
	Gaussholder();
} );
