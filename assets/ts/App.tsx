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
import ReactDOM from "react-dom";
import NightMode from "./NightMode/NightMode";
import LoadingScreen from "./Component/LoadingScreen";
import API from "./API";
import {BrowserRouter as Router, Route, Switch} from "react-router-dom";
import Auth from "./Auth/Auth";
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
import BadgeStatus from "./Auth/BadgeStatus";
import Notifications from "./Pages/Notifications/Notifications";
import Messages from "./Pages/Messages/Messages";
import ImageViewer from "./Component/ImageViewer";
import LoginSuggestionModal from "./Component/LoginSuggestionModal";
import Goodbye from "./Pages/Goodbye/Goodbye";
import PostForm from "./Component/PostForm/PostForm";
import BlockModal from "./Component/BlockModal";
import StatusRedirect from "./Pages/Status/StatusRedirect";
import Search from "./Pages/Search/Search";
import Analytics from "react-router-ga";
import SearchRedirect from "./Pages/Search/SearchRedirect";
import PushNotificationsManager from "./PushNotificationsManager";
import BadgeUpdater from "./BadgeUpdater";
import Storage from "./Util/Storage";
import RelationshipList from "./Component/Settings/RelationshipList";
import Sessions from "./Component/Settings/Sessions";
import BirthdaySelector from "./Component/Settings/BirthdaySelector";
import HeaderSelector from "./Component/Settings/HeaderSelector";
import AvatarSelector from "./Component/Settings/AvatarSelector";
import TokenStorage from "./Auth/TokenStorage";
import AccountSwitcher from "./Component/AccountSwitcher";
import PhraseStorage from "./i18n/PhraseStorage";
import ChangelogModal from "./Component/ChangelogModal";

export default class App extends Component<any, {
	validatedLogin: boolean,
	error: string | null
}> {
	constructor(props) {
		super(props);

		this.state = {
			validatedLogin: !Auth.isLoggedIn(),
			error: null
		}
	}

	public static async init(): Promise<void> {
		if ($("#root").length) {
			NightMode.init();
		} else {
			$(".settingsNavMobileLine .collapseButton").on("click", function (e) {
				e.preventDefault();

				$(".settingsNav").toggleClass("navHidden");
			});

			await PhraseStorage.loadPhrases();
		}

		if ($("#relationshipListFollowing").length) {
			ReactDOM.render(<RelationshipList
				type={"FOLLOWING"}/>, document.getElementById("relationshipListFollowing"));
		}

		if ($("#relationshipListFollowers").length) {
			ReactDOM.render(<RelationshipList
				type={"FOLLOWERS"}/>, document.getElementById("relationshipListFollowers"));
		}

		if ($("#relationshipListBlocked").length) {
			ReactDOM.render(<RelationshipList type={"BLOCKED"}/>, document.getElementById("relationshipListBlocked"));
		}

		if ($("#sessionList").length) {
			ReactDOM.render(<Sessions/>, document.getElementById("sessionList"));
		}

		if ($("#birthdaySelector").length) {
			ReactDOM.render(<BirthdaySelector/>, document.getElementById("birthdaySelector"));
		}

		if ($("#avatarSelector").length) {
			ReactDOM.render(<AvatarSelector/>, document.getElementById("avatarSelector"));
		}

		if ($("#headerSelector").length) {
			ReactDOM.render(<HeaderSelector/>, document.getElementById("headerSelector"));
		}

		$("#settingsNavLogoutButton").on("click", (e) => {
			e.preventDefault();
			Auth.logout();
		});

		Storage.cleanTask();
	}

	componentDidMount(): void {
		if (Auth.isLoggedIn()) {
			(async () => {
				try {
					// load user info
					const user = await API.i.token.verify();
					Auth.setCurrentUser(user);

					// load TokenStorage
					await TokenStorage.loadTokens();

					// load translations
					await PhraseStorage.loadPhrases();

					// load changelog
					await ChangelogModal.loadChangelog();

					// load badge status
					BadgeStatus.update(() => {
						PushNotificationsManager.init();
						BadgeUpdater.init();

						this.setState({
							validatedLogin: true
						});
					});
				} catch (err) {
					this.setState({
						error: err
					});

					Auth.logout(false, true);
				}
			})();
		}
	}

	render(): React.ReactElement<any, string | React.JSXElementConstructor<any>> | string | number | {} | React.ReactNodeArray | React.ReactPortal | boolean | null | undefined {
		if (this.state.validatedLogin === true) {
			return (
				<Router>
					<Analytics id={"UA-57891578-9"} debug>
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
												<AccountSwitcher/>
												<ChangelogModal/>

												<Switch>
													{Auth.isLoggedIn() ?
														<Route path={"/"} exact component={HomeFeed}/> :
														<Route path={"/"} exact component={Home}/>}

													<PrivateRoute path={"/notifications"} exact
																  component={Notifications}/>
													<PrivateRoute path={"/messages"} exact component={Messages}/>
													<Route path={"/search"} exact component={Search}/>
													<Route path={"/hashtag/:query"} exact component={SearchRedirect}/>
													<Route path={"/goodbye"} exact component={Goodbye}/>
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
					</Analytics>
				</Router>
			);
		} else if (this.state.error !== null) {
			return <div>{this.state.error}</div>; // TODO
		} else {
			return <LoadingScreen/>
		}
	}
}