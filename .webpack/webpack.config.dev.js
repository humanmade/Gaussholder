const common = require( './webpack.config.common' );

module.exports = {
	...common,
	mode: 'development',
	devtool: 'cheap-module-source-map',
};
