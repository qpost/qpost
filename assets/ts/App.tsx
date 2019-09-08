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

export default class App extends Component<any, any> {
	constructor(props) {
		super(props);

		this.state = {
			validatedLogin: false,
			error: null
		}
	}

	public static init(): void {
		NightMode.init();
	}

	componentDidMount(): void {
		// TODO: Pre-load home page data and pass it to the HomeFeed component
		API.handleRequest("/token/verify", "POST", {}, (data => {
			if (data.status && data.status === "Token valid" && data.user) {
				Auth.setCurrentUser(BaseObject.convertObject(User, data.user));

				this.setState({
					validatedLogin: true
				})
			} else {
				this.setState({
					error: "Authentication failed."
				})
			}
		}), (error => {
			this.setState({
				error
			})
		}));
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
								<Switch>
									<div className="legacyCard">
										<div className="wrapper">
											<div className="legacyCardBody">
												{Auth.isLoggedIn() ? <Route path={"/"} exact component={HomeFeed}/> :
													<Route path={"/"} exact component={Home}/>}

												<Route path={"/status/:id"} exact component={Status}/>
												<Route path={"/:username"} exact component={Profile}/>
											</div>
										</div>
									</div>
								</Switch>
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