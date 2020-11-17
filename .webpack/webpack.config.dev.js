const common = require( './webpack.config.common' );

module.exports = {
	...common,
	mode: 'development',
	devtool: 'inline-source-map',
};
