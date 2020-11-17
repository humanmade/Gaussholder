const { CleanWebpackPlugin } = require('clean-webpack-plugin');

module.exports = {
	entry: [ './assets/gaussholder.js' ],
	output: {
		filename: 'gaussholder.min.js',
	},
	module: {
		rules: [
			{
				test: /\.js$/,
				exclude: /(node_modules)/,
				loader: 'babel-loader',
			},
		],
	},
	plugins: [
		new CleanWebpackPlugin(),
	],
};
