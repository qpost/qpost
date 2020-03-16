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

import User from "../Entity/Account/User";
import React, {Component} from "react";
import {Tooltip} from "antd";

export default class VerifiedBadge extends Component<{ target: User }, any> {
	render(): React.ReactElement<any, string | React.JSXElementConstructor<any>> | string | number | {} | React.ReactNodeArray | React.ReactPortal | boolean | null | undefined {
		if (this.props.target.isVerified()) {
			return <Tooltip
				title={"This account has has been confirmed as an authentic page for this public figure, media company or brand."}
				placement={"right"}><span className="ml-1 small"><i
				className="fas fa-check-circle text-mainColor"/></span></Tooltip>;
		}

		return "";
	}
}