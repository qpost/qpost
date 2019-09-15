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
import Logo from "../../../../img/navlogo.png";
import Menu from "antd/es/menu";
import "antd/es/mention/style";
import Badge from "antd/es/badge";
import "antd/es/badge/style";
import Layout from "antd/es/layout";
import "antd/es/layout/style";
import NightMode from "../../../NightMode/NightMode";
import {Link} from "react-router-dom";
import Auth from "../../../Auth/Auth";
import SubMenu from "antd/es/menu/SubMenu";
import BadgeStatus from "../../../Auth/BadgeStatus";

export default class DesktopHeader extends Component<{
	mobile: boolean,
	key: any
}, any> {
	render(): React.ReactElement<any, string | React.JSXElementConstructor<any>> | string | number | {} | React.ReactNodeArray | React.ReactPortal | boolean | null | undefined {
		const currentUser = Auth.getCurrentUser();

		return <Layout.Header
			hasSider={true}
			className={"mainNav"}
			style={{
				position: "fixed",
				zIndex: 1,
				width: "100%",
				top: 0,
				display: this.props.mobile ? "none" : "block"
			}}>

			<Link to={"/"} className={"clearUnderline"}>
				<img src={Logo} style={{height: "30px"}} alt={"qpost Logo"}/>
			</Link>

			<Menu
				theme={NightMode.isActive() ? "dark" : "light"}
				mode={"horizontal"}
				selectable={false}
				style={{
					lineHeight: "64px",
					float: "right"
				}}>
				<Menu.Item key={0}>
					<Link to={"/"} className={"clearUnderline"}>
						home
					</Link>
				</Menu.Item>

				<Menu.Item key={1}>
					<Link to={"/profile/" + currentUser.getUsername()} className={"clearUnderline"}>
						my profile
					</Link>
				</Menu.Item>

				<Menu.Item key={2}>
					<Link to={"/notifications"} className={"clearUnderline"}>
						notifications <Badge count={BadgeStatus.getNotifications()} className={"ml-2"}/>
					</Link>
				</Menu.Item>

				<Menu.Item key={3}>
					<Link to={"/messages"} className={"clearUnderline"}>
						messages <Badge count={BadgeStatus.getMessages()} className={"ml-2"}/>
					</Link>
				</Menu.Item>

				<SubMenu title={<Link to={"/profile/" + currentUser.getUsername()} className={"clearUnderline"}>
					<img src={currentUser.getAvatarURL()} width={24} height={24} alt={currentUser.getUsername()}
						 className={"rounded"}/>
				</Link>}>
					<Menu.Item>
						<Link to={"/edit"} className={"clearUnderline"}>
							Edit profile
						</Link>
					</Menu.Item>

					<Menu.Item>
						<Link to={"/account"} className={"clearUnderline"}>
							Settings and privacy
						</Link>
					</Menu.Item>

					<Menu.Item>
						<a href={"/logout"} className={"clearUnderline"} onClick={(e) => {
							e.preventDefault();
							Auth.logout();
						}}>
							Log out
						</a>
					</Menu.Item>

					<Menu.Item>
						<a href={"/nightmode"} className={"clearUnderline"} onClick={(e) => {
							e.preventDefault();
							NightMode.toggle();
						}}>
							Toggle night mode
						</a>
					</Menu.Item>
				</SubMenu>
			</Menu>
		</Layout.Header>;
	}
}