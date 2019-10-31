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
import NightMode from "./NightMode/NightMode";
import LoadingScreen from "./Component/LoadingScreen";
import API from "./API/API";
import {BrowserRouter as Router, Route, Switch} from "react-router-dom";
import BaseObject from "./Serialization/BaseObject";
import Auth from "./Auth/Auth";
import User from "./Entity/Account/User";
import Header from "./Parts/Header";
import Home from "./Pages/Home/Home";
import HomeFeed from "./Pages/Home/HomeFeed";
import Layout from "antd/es/layout";
import "antd/es/layout/style";
import MobileChecker from "./Util/Mobile/MobileChecker";
import Status from "./Pages/Status/Status";
import Profile from "./Pages/Profile/Profile";
import ProfileRedirect from "./Pages/Profile/ProfileRedirect";
import PrivateRoute from "./Auth/PrivateRoute";
import EditProfile from "./Pages/EditProfile/EditProfile";
import BadgeStatus from "./Auth/BadgeStatus";
import Notifications from "./Pages/Notifications/Notifications";
import Messages from "./Pages/Messages/Messages";
import Account from "./Pages/Account/Account";
import Sessions from "./Pages/Account/Sessions";
import ChangeUsername from "./Pages/Account/ChangeUsername";
import ImageViewer from "./Component/ImageViewer";
import LoginSuggestionModal from "./Component/LoginSuggestionModal";
import About from "./Pages/About/About";
import DeleteAccount from "./Pages/Account/DeleteAccount";
import Goodbye from "./Pages/Goodbye/Goodbye";
import PostForm from "./Component/PostForm/PostForm";
import BlockModal from "./Component/BlockModal";
import Privacy from "./Pages/Account/Privacy";
import Blocked from "./Pages/Account/Blocked";
import DesktopNotifications from "./DesktopNotifications";
import StatusRedirect from "./Pages/Status/StatusRedirect";
import Search from "./Pages/Search/Search";
import PrivacyLevel from "./Pages/Account/PrivacyLevel";
import FollowRequests from "./Pages/Account/FollowRequests";
import ChangePassword from "./Pages/Account/ChangePassword";

export default class App extends Component<any, any> {
	constructor(props) {
		super(props);

		this.state = {
			validatedLogin: !Auth.isLoggedIn(),
			error: null
		}
	}

	public static init(): void {
		if ($("#root").length) {
			NightMode.init();
		}
	}

	componentDidMount(): void {
		if (Auth.isLoggedIn()) {
			// TODO: Pre-load home page data and pass it to the HomeFeed component
			API.handleRequest("/token/verify", "POST", {}, (data => {
				if (data.status && data.status === "Token valid" && data.user) {
					Auth.setCurrentUser(BaseObject.convertObject(User, data.user));

					BadgeStatus.update(() => {
						this.setState({
							validatedLogin: true
						});
					});
				} else {
					this.setState({
						error: "Authentication failed."
					})
				}
			}), (error => {
				this.setState({
					error
				});

				Auth.logout(false, true);
			}));
		}
	}

	render(): React.ReactElement<any, string | React.JSXElementConstructor<any>> | string | number | {} | React.ReactNodeArray | React.ReactPortal | boolean | null | undefined {
		if (this.state.validatedLogin === true) {
			return (
				<Router>
					<div className={"h-100"}>
						<MobileChecker/>

						{/* SCROLL TO TOP ON ROUTE CHANGE */}
						<Route component={() => {
							window.scrollTo(0, 0);
							return null;
						}}/>

						<Layout>
							<Header/>

							<Layout.Content className="navMargin mainContent">
								<div className="legacyCard">
									<div className="wrapper">
										<div className="legacyCardBody">
											<ImageViewer/>
											<LoginSuggestionModal/>
											<PostForm/>
											<BlockModal/>
											<DesktopNotifications/>

											<Switch>
												{Auth.isLoggedIn() ? <Route path={"/"} exact component={HomeFeed}/> :
													<Route path={"/"} exact component={Home}/>}

												<PrivateRoute path={"/edit"} exact component={EditProfile}/>
												<PrivateRoute path={"/notifications"} exact component={Notifications}/>
												<PrivateRoute path={"/messages"} exact component={Messages}/>
												<PrivateRoute path={"/account/sessions"} exact component={Sessions}/>
												<PrivateRoute path={"/account/delete"} exact component={DeleteAccount}/>
												<PrivateRoute path={"/account/privacy"} exact component={Privacy}/>
												<PrivateRoute path={"/account/privacy/blocked"} exact
															  component={Blocked}/>
												<PrivateRoute path={"/account/privacy/level"} exact
															  component={PrivacyLevel}/>
												<PrivateRoute path={"/account/privacy/requests"} exact
															  component={FollowRequests}/>
												<PrivateRoute path={"/account/username"} exact
															  component={ChangeUsername}/>
												<PrivateRoute path={"/account/password"} exact
															  component={ChangePassword}/>
												<PrivateRoute path={"/account"} exact component={Account}/>
												<Route path={"/search"} exact component={Search}/>
												<Route path={"/goodbye"} exact component={Goodbye}/>
												<Route path={"/about"} exact component={About}/>
												<Route path={"/r/status/:id"} exact component={StatusRedirect}/>
												<Route path={"/status/:id"} exact component={Status}/>
												<Route path={"/profile/:username"} component={ProfileRedirect}/>
												<Route path={"/:username"} component={Profile}/>
											</Switch>
										</div>
									</div>
								</div>
							</Layout.Content>
						</Layout>
					</div>
				</Router>
			);
		} else if (this.state.error !== null) {
			return <div>{this.state.error}</div>; // TODO
		} else {
			return <LoadingScreen/>
		}
	}
}