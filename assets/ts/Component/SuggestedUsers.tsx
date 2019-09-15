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
import {Link} from "react-router-dom";
import User from "../Entity/Account/User";
import API from "../API/API";
import BaseObject from "../Serialization/BaseObject";
import FollowButton from "./FollowButton";
import VerifiedBadge from "./VerifiedBadge";
import Spin from "antd/es/spin";
import "antd/es/spin/style";
import FollowStatus from "../Util/FollowStatus";
import {Card} from "antd";

export default class SuggestedUsers extends Component<any, { loading: boolean, results: User[] }> {
	constructor(props) {
		super(props);

		this.state = {
			loading: true,
			results: []
		}
	}

	componentDidMount(): void {
		API.handleRequest("/user/suggested", "GET", {}, (data => {
			if (data["results"]) {
				const results: User[] = [];

				data["results"].forEach(userData => {
					results.push(BaseObject.convertObject(User, userData));
				});

				this.setState({results, loading: false});
			}
		}));
	}

	render(): React.ReactElement<any, string | React.JSXElementConstructor<any>> | string | number | {} | React.ReactNodeArray | React.ReactPortal | boolean | null | undefined {
		if (this.state.loading) {
			return <div className={"text-center my-3"}>
				<Spin size={"large"}/>
			</div>
		} else if (this.state.results.length > 0) {
			return <Card title={"Suggested"} size={"small"}>
				<div className="tab-content" id="users-tablist-content">
					{this.state.results.map((suggestedUser, i) => {
						return <div className="my-1" style={{height: "70px"}} key={i}>
							<Link to={"/" + suggestedUser.getUsername()} className="clearUnderline float-left">
								<img src={suggestedUser.getAvatarURL()} width="64" height="64" className="rounded"
									 alt={suggestedUser.getUsername()}/>
							</Link>

							<div className="ml-2 float-left">
								<Link to={"/" + suggestedUser.getUsername()} className="clearUnderline"
									  data-user-id="{{ suggestedUser.id }}">
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