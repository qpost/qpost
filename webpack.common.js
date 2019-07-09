/*
 * Copyright (c) 2019 Gigadrive Group - All rights reserved.
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 * Proprietary and confidential.
 * https://gigadrivegroup.com/dev/technologies
 */

const {resolve} = require("path");
const UglifyJsPlugin = require("uglifyjs-webpack-plugin");
const optimizeCss = require("optimize-css-assets-webpack-plugin");

module.exports = {
	entry: "./assets/ts/index.tsx",
	context: resolve("./"),
	devtool: false,
	module: {
		rules: [
			{
				test: /\.tsx?$/,
				use: "ts-loader",
				exclude: /node_modules/
			},
			{
				test: /\.css$/,
				use: [
					{loader: "style-loader"},
					{loader: "css-loader"}
				]
			},
			{
				test: /\.scss$/,
				use: [
					{loader: "style-loader"},
					{loader: "css-loader"},
					{
						loader: "sass-loader", options: {
							includePaths: [
								resolve(__dirname, "node_modules")
							]
						}
					}
				]
			},
			{
				test: /\.png$/,
				use: "url-loader"
			},
			{
				test: /\.jpg$/,
				use: "file-loader"
			},
			{
				test: /\.(woff|woff2)(\?v=\d+\.\d+\.\d+)?$/,
				use: [{loader: "url-loader?limit=10000&mimetype=application/font-woff"}]
			},
			{
				test: /\.ttf(\?v=\d+\.\d+\.\d+)?$/,
				use: [{loader: "url-loader?limit=10000&mimetype=application/octet-stream"}]
			},
			{
				test: /\.eot(\?v=\d+\.\d+\.\d+)?$/,
				use: [{loader: "file-loader"}]
			},
			{
				test: /\.svg(\?v=\d+\.\d+\.\d+)?$/,
				use: [{loader: "url-loader?limit=10000&mimetype=image/svg+xml"}]
			}
		]
	},
	optimization: {
		minimizer: [
			new UglifyJsPlugin({
				extractComments: "all"
			})
		]
	},
	plugins: [
		new optimizeCss({
			cssProcessorOptions: {
				safe: true,
				discardComments: {
					removeAll: true,
				},
			}
		})
	],
	resolve: {
		extensions: [".tsx", ".ts", ".js", ".jsx"]
	},
	output: {
		filename: "bundle.js",
		path: resolve(__dirname, "public/build/")
	},
	node: {
		console: true,
		fs: "empty",
		net: "empty",
		tls: "empty"
	},
	performance: {
		hints: false,
		maxEntrypointSize: 512000,
		maxAssetSize: 512000
	}
};