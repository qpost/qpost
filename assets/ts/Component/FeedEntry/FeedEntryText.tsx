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
import FeedEntry from "../../Entity/Feed/FeedEntry";
import Linkify from "react-linkify";

export default class FeedEntryText extends Component<{
	feedEntry: FeedEntry
}, any> {
	render(): React.ReactElement<any, string | React.JSXElementConstructor<any>> | string | number | {} | React.ReactNodeArray | React.ReactPortal | boolean | null | undefined {
		const text = this.props.feedEntry.getText();

		return text ? <div className={"specialLinkColor"}>
			<Linkify>
				{text.split('\n').map((item, i) => {
					item = item.trim();

					return item !== "" ? <p key={i} style={{
						marginBottom: 0,
						width: "100%",
						display: "block",
						wordWrap: "break-word"
					}}>{item}</p> : "";
				})}
			</Linkify>
		</div> : "";
	}
}