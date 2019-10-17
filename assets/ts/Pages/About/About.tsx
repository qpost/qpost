/*
 * Copyright (C) 2018-2019 Gigadrive - All rights reserved.
 * https://gigadrivegroup.com
 * https://qpo.st
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

import React, {Component} from "react";
import {setPageTitle} from "../../Util/Page";
import {Card, Col, Row} from "antd";
import {Link} from "react-router-dom";

export default class About extends Component<any, any> {
	componentDidMount(): void {
		setPageTitle("About");
	}

	render(): React.ReactElement<any, string | React.JSXElementConstructor<any>> | string | number | {} | React.ReactNodeArray | React.ReactPortal | boolean | null | undefined {
		return <Row gutter={24}>
			<Col lg={{span: 14, offset: 5}}>
				<Card size={"small"}>
					<h1 className={"mb-3"}>About qpost</h1>

					<p>
						qpost is a social microblogging network, that was first released in Summer 2018 by
						Gigadrive.
					</p>

					<p>
						<b>Original author</b><br/>
						<Link to={"/profile/Zeryther"}>
							Mehdi Baaboura
						</Link>
					</p>

					<p>
						<b>Source code</b><br/>
						<a href={"https://gitlab.com/Gigadrive/qpost/qpost"}
						   target={"_blank"}>https://gitlab.com/Gigadrive/qpost/qpost</a>
					</p>

					<p>
						<b>License</b><br/>
						<a href={"https://gitlab.com/Gigadrive/qpost/qpost/blob/master/LICENSE"} target={"_blank"}>GNU
							GPL v3</a>
					</p>

					<p>
						<b>Issue Tracker</b><br/>
						<a href={"https://gitlab.com/Gigadrive/qpost/qpost/issues"}
						   target={"_blank"}>https://gitlab.com/Gigadrive/qpost/qpost/issues</a>
					</p>

					<h1 className={"my-3"}>Third-party software</h1>

					<p>
						qpost utilizes third-party software to run, you can find a list below (last update: October 17th
						2019).
					</p>

					{this.thirdPartySoftware().map((software, index) => {
						return <p key={index}>
							<b>{software.name}</b><br/>
							<a href={software.link} target={"_blank"}>{software.link}</a>
						</p>;
					})}
				</Card>
			</Col>
		</Row>;
	}

	private thirdPartySoftware(): { name: string, link: string }[] {
		return [
			{
				name: "Ant Design",
				link: "https://ant.design"
			},
			{
				name: "Symfony 4",
				link: "https://symfony.com"
			},
			{
				name: "Webpack",
				link: "https://webpack.js.org"
			},
			{
				name: "Doctrine",
				link: "https://doctrine-project.org"
			},
			{
				name: "php-image-resize",
				link: "https://github.com/gumlet/php-image-resize"
			},
			{
				name: "Guzzle",
				link: "https://github.com/guzzle/guzzle"
			},
			{
				name: "JMS Serializer",
				link: "https://jmsyst.com/libs/serializer"
			},
			{
				name: "Bootstrap 4",
				link: "https://getbootstrap.com"
			},
			{
				name: "Dropzone.js",
				link: "https://dropzonejs.com"
			},
			{
				name: "jQuery",
				link: "https://jquery.com"
			},
			{
				name: "jQuery UI",
				link: "https://jqueryui.com"
			},
			{
				name: "JavaScript Cookie",
				link: "https://github.com/js-cookie/js-cookie"
			},
			{
				name: "React",
				link: "https://reactjs.org"
			},
			{
				name: "react-infinite-scroller",
				link: "https://github.com/noopkat/react-infinite-scroller"
			},
			{
				name: "react-router",
				link: "https://github.com/ReactTraining/react-router"
			},
			{
				name: "reactstrap",
				link: "https://reactstrap.github.io"
			},
			{
				name: "axios",
				link: "https://github.com/axios/axios"
			},
			{
				name: "flag-icon-css",
				link: "https://github.com/lipis/flag-icon-css"
			},
			{
				name: "json2typescript",
				link: "https://github.com/dhlab-basel/json2typescript"
			},
			{
				name: "popper.js",
				link: "https://popper.js.org"
			},
			{
				name: "react-images-viewer",
				link: "https://github.com/guonanci/react-images-viewer"
			},
			{
				name: "react-scripts-ts",
				link: "https://github.com/jpavon/react-scripts-ts"
			},
			{
				name: "ts-loader",
				link: "https://github.com/TypeStrong/ts-loader"
			},
			{
				name: "TypeScript",
				link: "https://www.typescriptlang.org"
			},
			{
				name: "ua-parser-js",
				link: "https://github.com/faisalman/ua-parser-js"
			},
			{
				name: "Font Awesome",
				link: "https://fontawesome.com"
			},
			{
				name: "react-timeago",
				link: "https://github.com/nmn/react-timeago"
			},
			{
				name: "css-loader",
				link: "https://github.com/webpack-contrib/css-loader"
			},
			{
				name: "file-loader",
				link: "https://github.com/webpack-contrib/file-loader"
			},
			{
				name: "less",
				link: "http://lesscss.org"
			},
			{
				name: "less-loader",
				link: "https://github.com/webpack-contrib/less-loader"
			},
			{
				name: "mobx",
				link: "https://github.com/mobxjs/mobx"
			},
			{
				name: "node-sass",
				link: "https://github.com/sass/node-sass"
			},
			{
				name: "optimize-css-assets-webpack-plugin",
				link: "https://github.com/NMFR/optimize-css-assets-webpack-plugin"
			},
			{
				name: "react-window-size-listener",
				link: "https://github.com/kunokdev/react-window-size-listener"
			},
			{
				name: "run-sequence",
				link: "https://github.com/OverZealous/run-sequence"
			},
			{
				name: "sass-loader",
				link: "https://github.com/webpack-contrib/sass-loader"
			},
			{
				name: "style-loader",
				link: "https://github.com/webpack-contrib/style-loader"
			},
			{
				name: "uglifyjs-webpack-plugin",
				link: "https://github.com/webpack-contrib/uglifyjs-webpack-plugin"
			},
			{
				name: "url-loader",
				link: "https://github.com/webpack-contrib/url-loader"
			},
			{
				name: "webpack-merge",
				link: "https://github.com/survivejs/webpack-merge"
			},
			{
				name: "media-embed",
				link: "https://github.com/dereuromark/media-embed"
			},
			{
				name: "react-linkify",
				link: "https://github.com/tasti/react-linkify"
			}
		].sort((a, b) => {
			// sort by name, alphabetically
			return a.name.toUpperCase() < b.name.toUpperCase() ? -1 : a.name.toUpperCase() > b.name.toUpperCase() ? 1 : 0;
		});
	}
}