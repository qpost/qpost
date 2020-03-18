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
import User from "../Entity/Account/User";
import API from "../API/API";
import VerifiedBadge from "./VerifiedBadge";
import Spin from "antd/es/spin";
import "antd/es/spin/style";
import {Card} from "antd";
import Auth from "../Auth/Auth";
import TimeAgo from "./TimeAgo";
import {placeZeroBelowTen} from "../Util/Format";
import Storage from "../Util/Storage";
import AppearanceSettings from "../Util/AppearanceSettings";
import BaseObject from "../Serialization/BaseObject";

export default class UpcomingBirthdays extends Component<any, { loading: boolean, results: User[] }> {
	constructor(props) {
		super(props);

		this.state = {
			loading: true,
			results: []
		}
	}

	componentDidMount(): void {
		if (Auth.isLoggedIn() && AppearanceSettings.showUpcomingBirthdays()) {
			const storedBirthdays = Storage.sessionGet(Storage.SESSION_UPCOMING_BIRTHDAYS);
			if (storedBirthdays) {
				this.load(BaseObject.convertArray(User, JSON.parse(storedBirthdays)));
				return;
			}

			const now = new Date();

			API.birthdays.get(now.getFullYear() + "-" + placeZeroBelowTen(now.getMonth() + 1) + "-" + placeZeroBelowTen(now.getDate())).then(users => {
				this.load(users);

				if (this.state.results) {
					Storage.sessionSet(Storage.SESSION_UPCOMING_BIRTHDAYS, JSON.stringify(this.state.results));
				}
			});
		}
	}

	load = (results) => {
		this.setState({results, loading: false});
	};

	render(): React.ReactElement<any, string | React.JSXElementConstructor<any>> | string | number | {} | React.ReactNodeArray | React.ReactPortal | boolean | null | undefined {
		if (!Auth.isLoggedIn() || !AppearanceSettings.showUpcomingBirthdays()) return <div/>;

		if (this.state.loading) {
			return <div className={"text-center my-3"}>
				<Spin size={"large"}/>
			</div>
		} else if (this.state.results.length > 0) {
			const now = new Date();

			return <Card title={"Upcoming birthdays"} size={"small"} className={"mb-3"}>
				<div className="tab-content" id="users-tablist-content">
					{this.state.results.map((user, i) => {
						const birthday = new Date(user.getBirthday());
						birthday.setHours(0);
						birthday.setMinutes(0);
						birthday.setSeconds(0);
						birthday.setFullYear(now.getFullYear());

						const today = birthday.getDate() === now.getDate() && birthday.getMonth() === now.getMonth();

						return <div className="my-1" style={{height: "70px"}} key={i}>
							<Link to={"/profile/" + user.getUsername()} className="clearUnderline float-left">
								<img src={user.getAvatarURL()} width="64" height="64" className="rounded"
									 alt={user.getUsername()}/>
							</Link>

							<div className="ml-2 float-left">
								<Link to={"/profile/" + user.getUsername()} className="clearUnderline">
									<div className="float-left mt-1"
										 style={{
											 maxWidth: "220px",
											 overflow: "hidden",
											 textOverflow: "ellipsis",
											 whiteSpace: "nowrap",
											 wordWrap: "normal"
										 }}>
										<span
											className={"font-weight-bold"}>{user.getDisplayName()}</span><VerifiedBadge
										target={user}/> <span
										className={"text-muted small"}>@{user.getUsername()}</span>
									</div>
									<br/>
								</Link>

								<div style={{
									fontSize: "16px",
									marginTop: "8px"
								}}>
									<i className={"far fa-clock"}/> {today ?
									<span className={"text-danger font-weight-bold"}>Today</span> :
									<TimeAgo time={birthday.toUTCString()}
											 short={true}/>}
								</div>
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