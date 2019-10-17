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
import ReactLinkify from "react-linkify";
import {Link} from "react-router-dom";

export default class Linkifier extends Component<any, any> {
	render(): React.ReactElement<any, string | React.JSXElementConstructor<any>> | string | number | {} | React.ReactNodeArray | React.ReactPortal | boolean | null | undefined {
		return <ReactLinkify>
			<ReactLinkify matchDecorator={(text: string) => {
				const matches = [];

				const match = text.match(/([@][\w_-]+)/g);

				console.log(text, match);

				if (match) {
					match.forEach((value, index) => {
						matches.push({
							schema: "",
							index,
							lastIndex: index + value.length,
							text: value,
							url: "/profile/" + value.substr(1)
						});
					});
				}

				return matches;
			}} componentDecorator={(decoratedHref: string, decoratedText: string, key: number) => {
				return <Link to={decoratedHref} key={key}>
					{decoratedText}
				</Link>;
			}}>
				{this.props.children}
			</ReactLinkify>
		</ReactLinkify>;
	}
}