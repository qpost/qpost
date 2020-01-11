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
import FollowButton from "../../Component/FollowButton";
import VerifiedBadge from "../../Component/VerifiedBadge";
import SidebarStickyContent from "../../Component/Layout/SidebarStickyContent";
import SuggestedUsers from "../../Component/SuggestedUsers";
import SidebarFooter from "../../Parts/Footer/SidebarFooter";
import {Card, Menu} from "antd";
import "antd/es/card/style";
import FollowsYouBadge from "../../Component/FollowsYouBadge";
import Biography from "../../Component/Biography";
import {Redirect, Route, Switch} from "react-router-dom";
import Following from "./Following";
import Posts from "./Posts";
import Favorites from "./Favorites";
import Followers from "./Followers";
import {formatNumberShort} from "../../Util/Format";
import NightMode from "../../NightMode/NightMode";
import {setPageTitle} from "../../Util/Page";
import ProfileDropdown from "./ProfileDropdown";
import UserBlockedAlert from "../../Component/UserBlockedAlert";
import FollowersYouKnow from "../../Component/FollowersYouKnow";
import ProfileHeader from "./ProfileHeader";
import Replies from "./Replies";

export declare type ProfilePageProps = {
	user: User,
	parent: Profile
};

export default class Profile extends Component<any, {
	user: User,
	error: string | null,
	redirect: string | null,
	activeMenuPoint: string | null
}> {
	constructor(props) {
		super(props);

		this.state = {
			user: null,
			error: null,
			redirect: null,
			activeMenuPoint: null
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

					setPageTitle(user.getDisplayName() + " (@" + user.getUsername() + ")");
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
				{this.state.redirect ? <Redirect push to={this.state.redirect}/> : ""}

				<ContentBase>
					<ProfileHeader user={user}/>

					<LeftSidebar negativeOffset={!!user.getHeaderURL()}>
						<SidebarStickyContent>
							<div className={"text-center"}>
								<img className={"mainAvatar"} src={user.getAvatarURL()} alt={user.getUsername()}
									 style={{
										 backgroundColor: "#000"
									 }}/>
							</div>

							<h4 className={"mb-0"}>{user.getDisplayName()}<VerifiedBadge target={user}/><ProfileDropdown
								user={user} className={"float-right"}/></h4>
							<div className={"usernameDisplay"}>@{user.getUsername()}<FollowsYouBadge target={user}/>
							</div>

							<Biography user={user}/>

							<p className={"my-2 text-muted"}>
								<i className={"fas fa-globe"}/><span
								style={{marginLeft: "5px"}}>Joined {registerDate.toLocaleString("default", {
								month: "long",
								year: "numeric"
							})}</span>
								{birthDate ? <div>
									<i className={"fas fa-birthday-cake"}/><span
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
						<Card className={"mobilePart mb-3"} size={"small"}>
							<div className={"clearfix"}>
								<div className={"float-left"}>
									<img className={"mobileAvatar"} src={user.getAvatarURL()} alt={user.getUsername()}
										 style={{
											 backgroundColor: "#000"
										 }}/>
								</div>

								<div className={"float-left"}>
									<h4 className={"mb-0"}>{user.getDisplayName()}<VerifiedBadge target={user}/></h4>
									<div className={"usernameDisplay"}>@{user.getUsername()}<FollowsYouBadge
										target={user}/></div>
								</div>
							</div>

							<div className={"mt-2"}>
								<Biography user={user}/>

								<p className={"my-2 text-muted clearfix"}>
									<div className={"float-left"}>
										<i className={"fas fa-globe"}/><span
										style={{marginLeft: "5px"}}>Joined {registerDate.toLocaleString("default", {
										month: "long",
										year: "numeric"
									})}</span>
									</div>
									{birthDate ? <div className={"float-left ml-2"}>
										<i className={"fas fa-birthday-cake"}/><span
										style={{marginLeft: "7px"}}>{birthDate.toLocaleString("default", {
										month: "long",
										day: "numeric",
										year: "numeric"
									})}</span>
									</div> : ""}
								</p>

								<FollowButton target={user}/>

								<ProfileDropdown user={user} placement={"bottomLeft"}/>
							</div>
						</Card>

						{user.isBlocked() ? <UserBlockedAlert user={user}/> : ""}

						<Menu theme={NightMode.isActive() ? "dark" : "light"}
							  selectedKeys={[this.state.activeMenuPoint]} mode={"horizontal"} onClick={(e) => {
							if (e.key) {
								const key = e.key;

								if (key !== this.state.activeMenuPoint) {
									switch (key) {
										case "POSTS":
											this.setState({
												activeMenuPoint: key,
												redirect: "/" + user.getUsername()
											});
											break;
										case "REPLIES":
											this.setState({
												activeMenuPoint: key,
												redirect: "/" + user.getUsername() + "/replies"
											});
											break;
										case "FOLLOWING":
											this.setState({
												activeMenuPoint: key,
												redirect: "/" + user.getUsername() + "/following"
											});
											break;
										case "FOLLOWERS":
											this.setState({
												activeMenuPoint: key,
												redirect: "/" + user.getUsername() + "/followers"
											});
											break;
										case "FAVORITES":
											this.setState({
												activeMenuPoint: key,
												redirect: "/" + user.getUsername() + "/favorites"
											});
											break;
									}
								}
							}
						}}>
							<Menu.Item key={"POSTS"}>
								Posts ({formatNumberShort(user.getTotalPostCount())})
							</Menu.Item>

							<Menu.Item key={"REPLIES"}>
								Replies
							</Menu.Item>

							<Menu.Item key={"FOLLOWING"}>
								Following ({formatNumberShort(user.getFollowingCount())})
							</Menu.Item>

							<Menu.Item key={"FOLLOWERS"}>
								Followers ({formatNumberShort(user.getFollowerCount())})
							</Menu.Item>

							<Menu.Item key={"FAVORITES"}>
								Favorites ({formatNumberShort(user.getFavoritesCount())})
							</Menu.Item>
						</Menu>

						<Switch>
							<Route path={"/:username/following"}
								   render={(props) => <Following {...props} user={this.state.user} parent={this}/>}/>
							<Route path={"/:username/followers"}
								   render={(props) => <Followers {...props} user={this.state.user} parent={this}/>}/>
							<Route path={"/:username/favorites"}
								   render={(props) => <Favorites {...props} user={this.state.user} parent={this}/>}/>
							<Route path={"/:username/replies"}
								   render={(props) => <Replies {...props} user={this.state.user} parent={this}/>}/>
							<Route path={"/:username"}
								   render={(props) => <Posts {...props} user={this.state.user} parent={this}/>}/>
						</Switch>
					</PageContent>

					<RightSidebar>
						<SidebarStickyContent>
							<FollowersYouKnow user={this.state.user}/>

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