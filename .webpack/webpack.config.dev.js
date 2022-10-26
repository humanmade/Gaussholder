const common = require( './webpack.config.common' );

module.exports = {
	...common,
	mode: 'development',
	devtool: 'inline-source-map',
	module: {
		...common.module,
		rules: [
			...common.module.rules,
			{
				test: /\.js?$/,
				exclude: /(node_modules|bower_components)/,
				enforce: 'pre',
				loader: require.resolve( 'eslint-loader' ),
				options: {},
			},
		]

	}
};
