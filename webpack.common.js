/*
 * Copyright (C) 2018-2020 Gigadrive - All rights reserved.
 * https://gigadrivegroup.com
 * https://qpostapp.com
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://gnu.org/licenses/>
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
	/*optimization: {
		minimizer: [
			new UglifyJsPlugin({
				extractComments: "all"
			})
		]
	},*/
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