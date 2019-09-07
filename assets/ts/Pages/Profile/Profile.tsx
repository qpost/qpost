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
import API from "../../API/API";
import BaseObject from "../../Serialization/BaseObject";
import ContentBase from "../../Component/Layout/ContentBase";
import PageContent from "../../Component/Layout/PageContent";
import RightSidebar from "../../Component/Layout/RightSidebar";
import LeftSidebar from "../../Component/Layout/LeftSidebar";
import Spin from "antd/es/spin";
import "antd/es/spin/style";
import Alert from "antd/es/alert";
import "antd/es/alert/style";
import FeedEntryList from "../../Component/FeedEntry/FeedEntryList";
import FollowButton from "../../Component/FollowButton";
import VerifiedBadge from "../../Component/VerifiedBadge";
import SidebarStickyContent from "../../Component/Layout/SidebarStickyContent";
import SuggestedUsers from "../../Component/SuggestedUsers";
import SidebarFooter from "../../Parts/Footer/SidebarFooter";

export default class Profile extends Component<any, {
	user: User,
	error: string | null
}> {
	constructor(props) {
		super(props);

		this.state = {
			user: null,
			error: null
		};
	}

	componentDidMount(): void {
		const username = this.props.match.params.username;

		if (username) {
			API.handleRequest("/user", "GET", {user: username}, (data) => {
				if (data.result) {
					const user = BaseObject.convertObject(User, data.result);

					this.setState({
						user
					});
				} else {
					this.setState({
						error: "An error occurred."
					});
				}
			}, (error) => {
				this.setState({
					error
				});
			});
		} else {
			this.setState({
				error: "An error occurred."
			});
		}
	}

	render(): React.ReactElement<any, string | React.JSXElementConstructor<any>> | string | number | {} | React.ReactNodeArray | React.ReactPortal | boolean | null | undefined {
		const user = this.state.user;
		const error = this.state.error;

		if (user !== null) {
			const registerDate: Date = new Date(user.getTime());
			const birthDate: Date | null = user.getBirthday() ? new Date(user.getBirthday()) : null;

			return <div className={"profile"}>
				<ContentBase>
					<LeftSidebar>
						<SidebarStickyContent>
							<div className={"text-center"}>
								<img className={"mainAvatar"} src={user.getAvatarURL()}/>
							</div>

							<h4 className={"mb-0"}>{user.getDisplayName()}<VerifiedBadge target={user}/></h4>
							<div className={"usernameDisplay"}>@{user.getUsername()}</div>

							<p className={"my-2 text-muted"}>
								<i className={"fas fa-globe"}/><span
								style={{marginLeft: "5px"}}>Joined {registerDate.toLocaleString("default", {
								month: "long",
								year: "numeric"
							})}</span>
								{birthDate ? <div>
									<br/><i className={"fas fa-birthday-cake"}/><span
									style={{marginLeft: "7px"}}>{birthDate.toLocaleString("default", {
									month: "long",
									day: "numeric",
									year: "numeric"
								})}</span>
								</div> : ""}
							</p>

							<FollowButton target={user} block/>
						</SidebarStickyContent>
					</LeftSidebar>

					<PageContent leftSidebar rightSidebar>
						<FeedEntryList user={user}/>
					</PageContent>

					<RightSidebar>
						<SidebarStickyContent>
							<SuggestedUsers/>

							<SidebarFooter/>
						</SidebarStickyContent>
					</RightSidebar>
				</ContentBase>
			</div>;
		} else if (error !== null) {
			return <ContentBase>
				<PageContent>
					<Alert message="This user could not be found." type="error"/>
				</PageContent>
			</ContentBase>;
		} else {
			return <ContentBase>
				<PageContent>
					<div className={"text-center my-3"}>
						<Spin size={"large"}/>
					</div>
				</PageContent>
			</ContentBase>;
		}
	}
}