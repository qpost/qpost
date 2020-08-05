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
import ReactTimeAgo from "react-timeago";
import buildFormatter from "react-timeago/lib/formatters/buildFormatter";

export default class TimeAgo extends Component<{
	time: string,
	short?: boolean
}, any> {
	public static formatter;

	constructor(props) {
		super(props);

		if (!TimeAgo.formatter) {
			TimeAgo.formatter = buildFormatter(window["TIMEAGO_STRINGS"]);
		}
	}

	render(): React.ReactElement<any, string | React.JSXElementConstructor<any>> | string | number | {} | React.ReactNodeArray | React.ReactPortal | boolean | null | undefined {
		return <ReactTimeAgo date={this.props.time}
							 formatter={this.props.short ? (value: number, unit: string, suffix: string, epochSeconds: number, nextFormatter) => {
								 return unit.toLowerCase().startsWith("month") ? value + "mo" : value + unit.substr(0, 1);
							 } : TimeAgo.formatter}/>;
	}
}