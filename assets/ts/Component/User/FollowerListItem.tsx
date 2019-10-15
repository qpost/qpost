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
import {Card, Col} from "antd";
import {Link} from "react-router-dom";
import FollowButton from "../FollowButton";
import Biography from "../Biography";
import VerifiedBadge from "../VerifiedBadge";

const FollowerListItemColProps = {
	sm: 12
};

export {FollowerListItemColProps};

export default class FollowerListItem extends Component<{
	user: User
}, any> {
	render(): React.ReactElement<any, string | React.JSXElementConstructor<any>> | string | number | {} | React.ReactNodeArray | React.ReactPortal | boolean | null | undefined {
		const user = this.props.user;

		return <Col {...FollowerListItemColProps}>
			<Card size={"small"} className={"userBox"}>
				<div className={"clearfix"}>
					<div className={"float-left"}>
						<img alt={user.getUsername()} src={user.getAvatarURL()}
							 className={"rounded border border-primary"} width={48} height={48}/>
					</div>

					<div className={"float-left ml-2 nameContainer"}>
						<div className={"displayName"}>
							<Link to={"/profile/" + user.getUsername()} className={"clearUnderline"}>
								{user.getDisplayName()}<VerifiedBadge target={user}/>
							</Link>
						</div>

						<div className={"username"}>
							<Link to={"/profile/" + user.getUsername()} className={"clearUnderline"}>
								{"@" + user.getUsername()}
							</Link>
						</div>
					</div>

					<div className={"float-right mt-2"}>
						<FollowButton target={user}/>
					</div>
				</div>

				<Biography user={user}/>
			</Card>
		</Col>;
	}
}