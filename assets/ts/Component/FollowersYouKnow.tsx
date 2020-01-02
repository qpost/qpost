/*
 * Copyright (C) 2018-2020 Gigadrive - All rights reserved.
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
import User from "../Entity/Account/User";
import Auth from "../Auth/Auth";
import Spin from "antd/es/spin";
import {Alert, Card, Tooltip} from "antd";
import {Link} from "react-router-dom";
import API from "../API/API";
import BaseObject from "../Serialization/BaseObject";

export default class FollowersYouKnow extends Component<{
	user: User
}, {
	users: User[] | null,
	error: string | null
}> {
	constructor(props) {
		super(props);

		this.state = {
			users: null,
			error: null
		};
	}

	componentDidMount(): void {
		API.handleRequest("/followersyouknow", "GET", {
			target: this.props.user.getId(),
			limit: 10
		}, data => {
			if (data.results) {
				const users: User[] = this.state.users || [];

				data.results.forEach(result => users.push(BaseObject.convertObject(User, result)));

				this.setState({users});
			} else {
				this.setState({
					error: "An error occurred."
				});
			}
		}, error => {
			this.setState({error});
		});
	}

	render(): React.ReactElement<any, string | React.JSXElementConstructor<any>> | string | number | {} | React.ReactNodeArray | React.ReactPortal | boolean | null | undefined {
		if (Auth.isLoggedIn() && Auth.getCurrentUser().getId() !== this.props.user.getId()) {
			if (this.state.users !== null) {
				if (this.state.users.length > 0) {
					return <Card title={"Followers you know"} size={"small"} className={"mb-3"}>
						<div className="tab-content" id="users-tablist-content">
							{this.state.users.map((user, i) => {
								return <Tooltip placement={"top"}
												title={user.getDisplayName() + " (@" + user.getUsername() + ")"}>
									<Link key={user.getId()} to={"/profile/" + user.getUsername()}
										  className="clearUnderline float-left">
										<img src={user.getAvatarURL()} width="64" height="64"
											 className="rounded mr-1 mb-1"
											 alt={user.getUsername()}/>
									</Link>
								</Tooltip>;
							})}
						</div>
					</Card>;
				}
			} else if (this.state.error != null) {
				return <Alert message={this.state.error} className={"my-3"} type={"error"}/>;
			} else {
				return <div className={"text-center my-3"}>
					<Spin size={"large"}/>
				</div>;
			}
		}

		return <div/>;
	}
}