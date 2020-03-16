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

const merge = require("webpack-merge");
const common = require("./webpack.common");
const {resolve} = require("path");
const MiniCssExtractPlugin = require("mini-css-extract-plugin");

module.exports = merge(common, {
	mode: "production",
	output: {
		filename: "bundle.[hash].js",
		path: resolve(__dirname, "public/build/")
	},
	module: {
		rules: [
			{
				test: /\.css$/,
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
					}
				]
			},
			{
				test: /\.scss$/,
				use: [
					{
						loader: MiniCssExtractPlugin.loader
					},

					{
						loader: "css-loader",
						options: {
							sourceMap: true,
							importLoaders: 1
						}
					},

					{
						loader: "sass-loader",
						options: {
							sourceMap: true,
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
								"primary-color": "#007bff",
								"screen-xs": "0",
								"screen-sm": "676px",
								"screen-md": "868px",
								"screen-lg": "1092px",
								"screen-xl": "1500px"
							}
						}
					}
				]
			}
		]
	},
	plugins: [
		new MiniCssExtractPlugin({
			filename: "[name].[hash].css",
			chunkFilename: "[id].[hash].css",
			ignoreOrder: false
		})
	]
});