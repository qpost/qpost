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
import User from "../../Entity/Account/User";
import {stillGIFURL} from "../../Util/Format";

export default class ProfileHeader extends Component<{
	user: User
}, any> {
	render(): React.ReactElement<any, string | React.JSXElementConstructor<any>> | string | number | {} | React.ReactNodeArray | React.ReactPortal | boolean | null | undefined {
		const user = this.props.user;
		if (!user || !user.getHeaderURL()) return "";

		const header: string = stillGIFURL(user.getHeaderURL());

		return <div style={{
			paddingLeft: "10px",
			paddingRight: "10px"
		}}>
			<div className={"profileHeader"} style={{
				backgroundImage: "url('" + header + "')"
			}}/>
		</div>;
	}
}