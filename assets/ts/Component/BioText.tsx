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

import React, {Component} from "react";
import Linkifier from "./Linkifier";

export default class BioText extends Component<{
	text: string
}, any> {
	render(): React.ReactElement<any, string | React.JSXElementConstructor<any>> | string | number | {} | React.ReactNodeArray | React.ReactPortal | boolean | null | undefined {
		const text = this.props.text;

		if (text) {
			return text ? <div className={"specialLinkColor my-2"}>
				<Linkifier>
					{text.split('\n').map((item, i) => {
						item = item.trim();

						return item !== "" ? <p key={i} style={{
							marginBottom: 0,
							width: "100%",
							display: "block",
							wordWrap: "break-word"
						}}>{item}</p> : "";
					})}
				</Linkifier>
			</div> : "";
		}

		return "";
	}
}