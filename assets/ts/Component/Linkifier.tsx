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
import LinkifyIt from 'linkify-it';
import tlds from 'tlds';

export default class Linkifier extends Component<any, any> {
	render(): React.ReactElement<any, string | React.JSXElementConstructor<any>> | string | number | {} | React.ReactNodeArray | React.ReactPortal | boolean | null | undefined {
		return <ReactLinkify matchDecorator={(text: string) => {
			const matches = [];

			const linkify = new LinkifyIt();
			linkify.tlds(tlds);

			// https://github.com/tasti/react-linkify/issues/10
			linkify.add('@', {
				validate: function (text, pos, self) {
					var tail = text.slice(pos);

					if (!self.re.twitter) {
						self.re.twitter = new RegExp(
							'^([a-zA-Z0-9_]){1,15}(?!_)(?=$|' + self.re.src_ZPCc + ')'
						);
					}
					if (self.re.twitter.test(tail)) {
						// Linkifier allows punctuation chars before prefix,
						// but we additionally disable `@` ("@@mention" is invalid)
						if (pos >= 2 && tail[pos - 2] === '@') {
							return false;
						}
						return tail.match(self.re.twitter)[0].length;
					}
					return 0;
				},
				normalize: function (match) {
					match.url = '/profile/' + match.url.replace(/^@/, '');
				}
			});

			let match = linkify.match(text);

			if (match) {
				match.forEach(linkifyMatch => matches.push(linkifyMatch));
			}

			return matches;
		}} componentDecorator={(decoratedHref: string, decoratedText: string, key: number) => {
			return decoratedHref.startsWith("/") ? <Link to={decoratedHref} key={key}>
				{decoratedText}
			</Link> : <a href={decoratedHref} key={key} target={"_blank"} onClick={(e) => {
				e.stopPropagation();
			}}>
				{decoratedText}
			</a>;
		}}>
			{this.props.children}
		</ReactLinkify>;
	}
}