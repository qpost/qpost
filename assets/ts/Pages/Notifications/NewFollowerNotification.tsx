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
import Notification from "../../Entity/Feed/Notification";
import {Card} from "antd";

export default class NewFollowerNotification extends Component<{
	notification: Notification
}, any> {
	constructor(props) {
		super(props);
	}

	render(): React.ReactElement<any, string | React.JSXElementConstructor<any>> | string | number | {} | React.ReactNodeArray | React.ReactPortal | boolean | null | undefined {
		const notification = this.props.notification;
		const user = notification.getReferencedUser();

		return user ? <Card size={"small"} className={!notification.isSeen() ? "unseenNotification" : ""}>
			<div className={"clearfix"}>
				<div className={"float-left mr-2"}>
					<a href={"/profile/" + user.getUsername()} className={"clearUnderline"}>
						<img src={user.getAvatarURL()} width={48} height={48} className={"rounded"}/>
					</a>
				</div>

				<div className={"float-left"}>
					<p className={"mb-0 text-muted"}>
						New follower - @{user.getUsername()}
					</p>

					<p className={"mb-0"}>
						<a href={"/profile/" + user.getUsername()}
						   className={"font-weight-bold clearUnderline"}>{user.getDisplayName()}</a> is now following
						you.
					</p>
				</div>
			</div>
		</Card> : "";
	}
}