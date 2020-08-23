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
const glob = require("glob");
const UglifyJsPlugin = require("uglifyjs-webpack-plugin");
const optimizeCss = require("optimize-css-assets-webpack-plugin");
const MiniCssExtractPlugin = require("mini-css-extract-plugin");

module.exports = {
	entry: "./assets/ts/index.tsx",
	devtool: false,
	module: {
		rules: [
			{
				test: /\.tsx?$/,
				loader: "ts-loader",
				exclude: [
					/node_modules/
				],
				options: {
					configFile: 'tsconfig.webpack.json'
				}
			},
			{
				test: /\.css$/,
				use: [
					{
						loader: MiniCssExtractPlugin.loader,
						options: {
							hmr: process.env.NODE_ENV === "development"
						}
					},
					{loader: "css-loader"}
				]
			},
			{
				test: /\.scss$/,
				use: [
					{
						loader: MiniCssExtractPlugin.loader
					},
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
				test: /\.less$/,
				use: [
					{
						loader: MiniCssExtractPlugin.loader,
						options: {
							hmr: process.env.NODE_ENV === "development"
						}
					},
					{
						loader: "css-loader",
						options: {
							sourceMap: true,
							importLoaders: 1
						}
					},
					{
						loader: "less-loader",
						options: {
							javascriptEnabled: true,
							modifyVars: {
								"primary-color": "#007BFF",
								"screen-xs": "0",
								"screen-sm": "676px",
								"screen-md": "868px",
								"screen-lg": "1092px",
								"screen-xl": "1370px"
							}
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
				extractComments: "all",
				test: [/\.tsx?$/, /\.jsx?$/]
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
		}),
		new MiniCssExtractPlugin({
			filename: "main.css",
			chunkFilename: "[id].[hash].css",
			ignoreOrder: false
		})
	],
	resolve: {
		extensions: [".tsx", ".ts", ".js", ".jsx"]
	},
	output: {
		path: resolve(__dirname, "public/build/"),
		filename: "bundle.js"
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