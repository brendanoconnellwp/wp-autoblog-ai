const path = require( 'path' );
const MiniCssExtractPlugin = require( 'mini-css-extract-plugin' );
const CssMinimizerPlugin = require( 'css-minimizer-webpack-plugin' );

module.exports = {
	entry: {
		admin: [
			'./assets/js/admin.js',
			'./assets/css/admin.css',
		],
	},
	output: {
		path: path.resolve( __dirname, 'assets/dist' ),
		filename: 'js/[name].min.js',
		clean: true,
	},
	module: {
		rules: [
			{
				test: /\.css$/,
				use: [ MiniCssExtractPlugin.loader, 'css-loader' ],
			},
		],
	},
	plugins: [
		new MiniCssExtractPlugin( {
			filename: 'css/[name].min.css',
		} ),
	],
	optimization: {
		minimizer: [ '...', new CssMinimizerPlugin() ],
	},
	externals: {
		jquery: 'jQuery',
	},
};
