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
import Menu from "antd/es/menu";
import "antd/es/menu/style";
import Badge from "antd/es/badge";
import "antd/es/badge/style";
import Layout from "antd/es/layout";
import "antd/es/layout/style";
import NightMode from "../../../NightMode/NightMode";
import {Link} from "react-router-dom";
import BadgeStatus from "../../../Auth/BadgeStatus";
import Auth from "../../../Auth/Auth";

export default class MobileNavigation extends Component<{
	mobile: boolean,
	key: any
}, any> {
	render(): React.ReactElement<any, string | React.JSXElementConstructor<any>> | string | number | {} | React.ReactNodeArray | React.ReactPortal | boolean | null | undefined {
		const notifications = BadgeStatus.getNotifications();
		const messages = BadgeStatus.getMessages();

		if (!Auth.isLoggedIn()) return "";

		return <Layout.Header
			className={"mobileNav"}
			style={{
				position: "fixed",
				zIndex: 1,
				width: "100%",
				bottom: 0,
				display: !this.props.mobile ? "none" : "block"
			}}>
			<div style={{
				textAlign: "center"
			}}>
				<Menu
					theme={NightMode.isActive() ? "dark" : "light"}
					mode={"horizontal"}
					selectable={false}
					inlineCollapsed={false}
					style={{
						lineHeight: "64px"
					}}>
					<Menu.Item key={0}>
						<Link to={"/"}>
							<i className={"fas fa-home"}/>
						</Link>
					</Menu.Item>

					<Menu.Item key={1}>
						<Link to={"/search"}>
							<i className={"fas fa-search"}/>
						</Link>
					</Menu.Item>

					<Menu.Item key={2}>
						<Link to={"/notifications"}>
							{notifications > 0 ? <Badge count={notifications}>
								<i className={"fas fa-bell"}/>
							</Badge> : <i className={"fas fa-bell"}/>}
						</Link>
					</Menu.Item>

					<Menu.Item key={3}>
						<Link to={"/messages"}>
							{notifications > 0 ? <Badge count={messages}>
								<i className={"fas fa-envelope"}/>
							</Badge> : <i className={"fas fa-envelope"}/>}
						</Link>
					</Menu.Item>
				</Menu>
			</div>
		</Layout.Header>;
	}
}