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
import Avatar from "antd/es/avatar";
import "antd/es/avatar/style";
import Layout from "antd/es/layout";
import "antd/es/layout/style";
import Menu from "antd/es/menu";
import "antd/es/menu/style";
import NightMode from "../../../NightMode/NightMode";
import Auth from "../../../Auth/Auth";
import User from "../../../Entity/Account/User";
import {Link} from "react-router-dom";
import VerifiedBadge from "../../../Component/VerifiedBadge";
import ClickEvent = JQuery.ClickEvent;

export default class MobileSider extends Component<{
	mobile: boolean,
	key: any
}, {
	collapsed: boolean,
	mobileMenu: boolean
}> {
	constructor(props) {
		super(props);

		this.state = {
			collapsed: true,
			mobileMenu: this.props.mobile
		};
	}

	componentDidMount(): void {
		$("#mobileSiderTrigger").off("click").on("click", (e: ClickEvent) => {
			e.preventDefault();

			this.setState({
				collapsed: !this.state.collapsed
			});
		});
	}

	toggle = () => {
		this.setState({
			collapsed: !this.state.collapsed
		});
	};

	render(): React.ReactElement<any, string | React.JSXElementConstructor<any>> | string | number | {} | React.ReactNodeArray | React.ReactPortal | boolean | null | undefined {
		const currentUser: User = Auth.getCurrentUser();

		return [
			<Layout.Sider
				key={0}
				className={"mobileSider" + (!this.props.mobile ? " d-none" : "")}
				breakpoint="lg"
				collapsedWidth="0"
				trigger={null}
				collapsible
				collapsed={this.state.collapsed}
				style={{
					position: "fixed",
					zIndex: 2001,
					height: "100%"
				}}
				onBreakpoint={broken => {
					this.setState({
						mobileMenu: broken
					});
				}}>
				{currentUser ? <div className={"mx-2 my-2 clearfix"}>
					<Link to={"/profile/" + currentUser.getUsername()} className={"clearUnderline"}
						  onClick={(e) => this.toggle()}>
						<Avatar src={currentUser.getAvatarURL()} className={"mr-2"} shape={"square"} size={"large"}
								style={{
									float: "left"
								}}/>
					</Link>

					<div style={{float: "left", width: "calc(100% - 40px - 20px)"}}>
						<div className={"displayName"}>
							{currentUser.getDisplayName()}<VerifiedBadge target={currentUser}/>
						</div>

						<div className={"username"}>
							@{currentUser.getUsername()}
						</div>
					</div>
				</div> : ""}

				<Menu theme={NightMode.isActive() ? "dark" : "light"} mode="inline" selectable={false}>
					{currentUser ? [<Menu.Item key="1">
						<Link to={"/profile/" + currentUser.getUsername()} className={"clearUnderline"}
							  onClick={(e) => this.toggle()}>
							<i className={"far fa-user iconMargin-10"}/>
							<span className="nav-text">Profile</span>
						</Link>
					</Menu.Item>,
						<Menu.Item key="2">
							<a href={"/settings/profile/appearance"} className={"clearUnderline"}
							   onClick={(e) => this.toggle()}>
								<i className={"fas fa-cog iconMargin-10"}/>
								<span className="nav-text">Settings</span>
							</a>
						</Menu.Item>,
						<Menu.Item key="3">
							<Link to={"#"} onClick={(e) => {
								e.preventDefault();
								Auth.logout();
							}} className={"clearUnderline"}>
								<i className={"fas fa-sign-out-alt iconMargin-10"}/>
								<span className="nav-text">Logout</span>
							</Link>
						</Menu.Item>] : [
						<Menu.Item key="1">
							<a href={"/login"} className={"clearUnderline"}>
								<i className={"far fa-user iconMargin-10"}/>
								<span className="nav-text">Login</span>
							</a>
						</Menu.Item>,
						<Menu.Item key="2">
							<a href={"/"} className={"clearUnderline"}>
								<i className={"fas fa-sign-in-alt iconMargin-10"}/>
								<span className="nav-text">Sign up</span>
							</a>
						</Menu.Item>
					]}
				</Menu>
			</Layout.Sider>,
			<div key={1} className={"mobileSliderBackdrop" + (!this.state.collapsed ? " open" : "")} onClick={(e) => {
				e.preventDefault();

				if (!this.state.collapsed) {
					this.setState({
						collapsed: !this.state.collapsed
					});
				}
			}}/>
		];
	}
}