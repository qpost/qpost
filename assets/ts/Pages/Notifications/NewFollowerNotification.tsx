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
import Notification from "../../api/src/Entity/Notification";
import {Card} from "antd";
import {Link} from "react-router-dom";
import TimeAgo from "../../Component/TimeAgo";

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
					<Link to={"/profile/" + user.getUsername()} className={"clearUnderline"}>
						<img src={user.getAvatarURL()} width={48} height={48} className={"rounded"}/>
					</Link>
				</div>

				<div className={"float-left"}>
					<p className={"mb-0 text-muted"}>
						New follower - @{user.getUsername()}
					</p>

					<p className={"mb-0"}>
						<Link to={"/profile/" + user.getUsername()}
							  className={"font-weight-bold clearUnderline"}>{user.getDisplayName()}</Link> is now
						following
						you.<span className={"text-muted ml-2"}><i className="far fa-clock"/> <TimeAgo
						time={notification.getTime()} short={true}/></span>
					</p>
				</div>
			</div>
		</Card> : "";
	}
}