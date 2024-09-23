/**
 * External dependencies
 */
const RemoveEmptyScriptsPlugin = require( 'webpack-remove-empty-scripts' );
const path = require( 'path' );
const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );

module.exports = {
	...defaultConfig,

	entry: {
		'cslice-video-in-modal' : path.resolve( process.cwd(), 'src/index.js' ),
		'cslice-video-in-modal-styles': path.resolve( process.cwd(), 'src/index.scss' ),
	},

	plugins: [
		...defaultConfig.plugins,
		new RemoveEmptyScriptsPlugin(),
	],
};
