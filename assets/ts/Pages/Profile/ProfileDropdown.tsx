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
import {Button, Dropdown, Icon, Menu, message} from "antd";
import "antd/es/dropdown/style";
import User from "../../Entity/Account/User";
import BlockModal from "../../Component/BlockModal";
import {Clipboard} from "ts-clipboard";
import Auth from "../../Auth/Auth";

export default class ProfileDropdown extends Component<{
	className?: string,
	user: User,
	placement?: "topLeft" | "topCenter" | "topRight" | "bottomLeft" | "bottomCenter" | "bottomRight"
}, any> {
	render(): React.ReactElement<any, string | React.JSXElementConstructor<any>> | string | number | {} | React.ReactNodeArray | React.ReactPortal | boolean | null | undefined {
		const placement = this.props.placement || "bottomRight";

		return <Dropdown className={this.props.className} placement={placement} overlay={() => {
			return <Menu onClick={(e) => {
				const key = e.key;

				switch (key) {
					case "1":
						BlockModal.open(this.props.user);
						break;
					case "2":
						Clipboard.copy("https://qpo.st/" + this.props.user.getUsername());
						message.success("The link has been copied.");
						break;
				}
			}}>
				{Auth.isLoggedIn() && this.props.user.getId() !== Auth.getCurrentUser().getId() && !this.props.user.isBlocked() ?
					<Menu.Item key={"1"}>
					<i className="fas fa-ban iconMargin-10"/>Block {"@" + this.props.user.getUsername()}
				</Menu.Item> : ""}

				<Menu.Item key={"2"}>
					<i className="fas fa-link iconMargin-10"/>Copy profile link
				</Menu.Item>
			</Menu>;
		}}>
			<Button shape={"round"}>
				<Icon type="ellipsis"/>
			</Button>
		</Dropdown>;
	}
}