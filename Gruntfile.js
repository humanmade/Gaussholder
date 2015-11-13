module.exports = function(grunt) {

	// Project configuration.
	grunt.initConfig({
		// pkg: grunt.file.readJSON('package.json'),
		uglify: {
			options: {
				compress: {
					dead_code: true,
					sequences: true,
				},
				// beautify: true,
				// mangleProperties: true,
				// mangle: {
					// except: ['Gaussholder'],
				// },
				// wrap: 'G'
			},
			build: {
				src: [ 'assets/stackblur.js', 'assets/gaussholder.js' ],
				dest: 'assets/gaussholder.min.js'
			}
		}
	});

	// Load the plugin that provides the "uglify" task.
	grunt.loadNpmTasks('grunt-contrib-uglify');

	// Default task(s).
	grunt.registerTask('default', ['uglify']);

};