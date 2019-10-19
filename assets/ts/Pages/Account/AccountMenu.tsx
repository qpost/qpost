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
import {Menu} from "antd";
import WindowSizeListener from "react-window-size-listener";
import {Redirect} from "react-router";
import Auth from "../../Auth/Auth";
import NightMode from "../../NightMode/NightMode";

export declare type AccountMenuKey = "HOME" | "PRIVACY" | "SESSIONS" | "LOGOUT";

export default class AccountMenu extends Component<{
	activePoint?: AccountMenuKey
}, {
	mobileMenu: boolean,
	redirect: string | null
}> {
	constructor(props) {
		super(props);

		this.state = {
			mobileMenu: window.innerWidth <= 867,
			redirect: null
		};
	}

	setIsMobileMenu = (windowWidth: number) => {
		const mobileMenuOpen = windowWidth <= 867;

		if (this.state.mobileMenu !== mobileMenuOpen) {
			this.setState({
				mobileMenu: mobileMenuOpen
			});
		}
	};

	render(): React.ReactElement<any, string | React.JSXElementConstructor<any>> | string | number | {} | React.ReactNodeArray | React.ReactPortal | boolean | null | undefined {
		this.setIsMobileMenu(window.innerWidth);

		const redirect = this.state.redirect;
		if (redirect) {
			return <Redirect push to={redirect}/>;
		}

		return [
			<Menu theme={NightMode.isActive() ? "dark" : "light"} onClick={(e) => {
				if (e.key) {
					const key = e.key;

					if (key !== this.props.activePoint) {
						switch (key) {
							case "HOME":
								this.setState({
									redirect: "/account"
								});
								break;
							case "PRIVACY":
								this.setState({
									redirect: "/account/privacy"
								});
								break;
							case "SESSIONS":
								this.setState({
									redirect: "/account/sessions"
								});
								break;
							case "LOGOUT":
								Auth.logout();
								break;
						}
					}
				}
			}} selectedKeys={[this.props.activePoint || "HOME"]} mode={this.state.mobileMenu ? "horizontal" : "inline"}>
				<Menu.Item key={"HOME"}>
					<i className="fas fa-user iconMargin-5"/>
					Account
				</Menu.Item>

				<Menu.Item key={"PRIVACY"}>
					<i className="fas fa-lock iconMargin-5"/>
					Privacy
				</Menu.Item>

				<Menu.Item key={"SESSIONS"}>
					<i className="fas fa-globe iconMargin-5"/>
					Sessions
				</Menu.Item>

				<Menu.Item key={"LOGOUT"}>
					<i className="fas fa-sign-out-alt iconMargin-5"/>
					Logout
				</Menu.Item>
			</Menu>,
			<WindowSizeListener onResize={windowSize => {
				this.setIsMobileMenu(windowSize.windowWidth);
			}}/>];
	}
}