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
import {Button, Card} from "antd";
import {Link} from "react-router-dom";
import FollowButton from "../FollowButton";
import Biography from "../Biography";
import VerifiedBadge from "../VerifiedBadge";
import API from "../../API";
import FollowRequestNotification from "../../Pages/Notifications/FollowRequestNotification";
import User from "../../api/src/Entity/User";

const FollowerListItemColProps = {
	sm: 12
};

export {FollowerListItemColProps};

export default class FollowerListItem extends Component<{
	user: User,
	requestId?: number,
	notificationParent?: FollowRequestNotification
}, {
	clear: boolean
}> {
	constructor(props) {
		super(props);

		this.state = {
			clear: false
		};
	}

	respond(accept: boolean): void {
		if (this.props.notificationParent) {
			this.props.notificationParent.setState({
				clear: true
			});
		} else {
			this.setState({
				clear: true
			});
		}

		API.i.followRequest.delete(this.props.requestId, accept ? "accept" : "decline");
	}

	render(): React.ReactElement<any, string | React.JSXElementConstructor<any>> | string | number | {} | React.ReactNodeArray | React.ReactPortal | boolean | null | undefined {
		if (this.state.clear) {
			return "";
		}

		const user = this.props.user;

		return <Card size={"small"} className={"userBox"}>
			<div className={"clearfix"}>
				<div className={"float-left"}>
					<Link to={"/profile/" + user.getUsername()} className={"clearUnderline"}>
						<img alt={user.getUsername()} src={user.getAvatarURL()}
							 className={"rounded border border-primary"} width={48} height={48}/>
					</Link>
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

			{this.props.requestId ? <div className={"my-3 text-center"}>
				<Button type={"primary"} onClick={(e) => {
					e.preventDefault();
					this.respond(true);
				}}>Accept</Button>

				<Button type={"danger"} className={"customDangerButton ml-3"} onClick={(e) => {
					e.preventDefault();
					this.respond(false);
				}}>Decline</Button>
			</div> : ""}

			<Biography user={user}/>
		</Card>;
	}
}