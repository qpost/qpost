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
import {Link} from "react-router-dom";
import API from "../API";
import FollowButton from "./FollowButton";
import VerifiedBadge from "./VerifiedBadge";
import Spin from "antd/es/spin";
import "antd/es/spin/style";
import FollowStatus from "../Util/FollowStatus";
import {Card} from "antd";
import Auth from "../Auth/Auth";
import Storage from "../Util/Storage";
import AppearanceSettings from "../Util/AppearanceSettings";
import BaseObject from "../api/src/BaseObject";
import User from "../api/src/Entity/User";

export default class SuggestedUsers extends Component<any, { loading: boolean, results: User[] }> {
	constructor(props) {
		super(props);

		this.state = {
			loading: true,
			results: []
		}
	}

	componentDidMount(): void {
		if (Auth.isLoggedIn() && AppearanceSettings.showSuggestedUsers()) {
			const storedUsers = Storage.sessionGet(Storage.SESSION_SUGGESTED_USERS);
			if (storedUsers) {
				this.load(BaseObject.convertArray(User, JSON.parse(storedUsers)));
				return;
			}

			API.i.suggestedUsers.get().then(users => {
				this.load(users);

				if (this.state.results) {
					Storage.sessionSet(Storage.SESSION_SUGGESTED_USERS, JSON.stringify(this.state.results));
				}
			});
		}
	}

	load = (results) => {
		this.setState({results, loading: false});
	};

	render(): React.ReactElement<any, string | React.JSXElementConstructor<any>> | string | number | {} | React.ReactNodeArray | React.ReactPortal | boolean | null | undefined {
		if (!Auth.isLoggedIn() || !AppearanceSettings.showSuggestedUsers()) return <div/>;

		if (this.state.loading) {
			return <div className={"text-center my-3"}>
				<Spin size={"large"}/>
			</div>
		} else if (this.state.results.length > 0) {
			return <Card title={"Suggested"} size={"small"} className={"mb-3"}>
				<div className="tab-content" id="users-tablist-content">
					{this.state.results.map((suggestedUser, i) => {
						return <div className="my-1" style={{height: "70px"}} key={i}>
							<Link to={"/profile/" + suggestedUser.getUsername()} className="clearUnderline float-left">
								<img src={suggestedUser.getAvatarURL()} width="64" height="64" className="rounded"
									 alt={suggestedUser.getUsername()}/>
							</Link>

							<div className="ml-2 float-left">
								<Link to={"/profile/" + suggestedUser.getUsername()} className="clearUnderline">
									<div className="float-left mt-1"
										 style={{
											 maxWidth: "220px",
											 overflow: "hidden",
											 textOverflow: "ellipsis",
											 whiteSpace: "nowrap",
											 wordWrap: "normal"
										 }}>
										<span
											className={"font-weight-bold"}>{suggestedUser.getDisplayName()}</span><VerifiedBadge
										target={suggestedUser}/> <span
										className={"text-muted small"}>@{suggestedUser.getUsername()}</span>
									</div>
									<br/>
								</Link>

								<FollowButton target={suggestedUser} className={"mt-0 btn-sm"}
											  followStatus={FollowStatus.NOT_FOLLOWING}/>
							</div>
						</div>
					})}
				</div>
			</Card>;
		} else {
			return "";
		}
	}
}