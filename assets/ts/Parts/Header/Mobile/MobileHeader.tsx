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
import Layout from "antd/es/layout";
import "antd/es/layout/style";
import Button from "antd/es/button";
import "antd/es/button/style";
import Logo from "../../../../img/navlogo.png";
import {Link} from "react-router-dom";
import Auth from "../../../Auth/Auth";

export default class MobileHeader extends Component<{
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
				zIndex: 99999,
				width: "100%",
				top: 0,
				display: !this.props.mobile ? "none" : "block"
			}}>
			<div style={{
				textAlign: "center"
			}}>
				<Button id={"mobileSiderTrigger"} ghost={true} type={"link"} style={{
					float: "left",
					lineHeight: "64px",
					marginTop: "-2px"
				}}>
					<i className="fas fa-bars"/>
				</Button>

				{currentUser ? <Link to={"/"} className={"clearUnderline"}>
					<img src={Logo} style={{height: "30px"}} alt={"qpost Logo"}/>
				</Link> : <a href={"/"} className={"clearUnderline"}>
					<img src={Logo} style={{height: "30px"}} alt={"qpost Logo"}/>
				</a>}

				{currentUser ?
					<Link to={"/profile/" + currentUser.getUsername()} className={"clearUnderline float-right"}>
						<img src={currentUser.getAvatarURL()} alt={currentUser.getUsername()} width={24} height={24}
							 className={"rounded border border-primary"} style={{
							marginTop: "-3px"
						}}/>
					</Link> : <a href={"/login"} className={"clearUnderline float-right"}>
						<img src={"https://cdn.gigadrivegroup.com/defaultAvatar.png"} alt={"Log in"} width={24}
							 height={24} className={"rounded border border-primary"} style={{
							marginTop: "-3px"
						}}/>
					</a>}
			</div>
		</Layout.Header>;
	}
}