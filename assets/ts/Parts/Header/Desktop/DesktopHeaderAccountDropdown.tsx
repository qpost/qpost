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
import DesktopHeader from "./DesktopHeader";
import {Link} from "react-router-dom";
import Auth from "../../../Auth/Auth";
import AccountSwitcher from "../../../Component/AccountSwitcher";
import __ from "../../../i18n/i18n";

export default class DesktopHeaderAccountDropdown extends Component<{
	open: boolean,
	parent: DesktopHeader
}, any> {
	private static close(parent): void {
		parent.setState({
			accountDropdownOpen: false
		});
	}

	render() {
		const user = Auth.getCurrentUser();

		return <div className={(!this.props.open ? "d-none " : "") + "desktopHeaderAccountDropdown"}>
			<div className={"profileInfo"}>
				<div className={"profileInfoLeft"}>
					<img src={user.getAvatarURL()} alt={user.getUsername()} title={user.getUsername()}/>
				</div>

				<div className={"profileInfoRight"}>
					<div className={"profileInfoDisplayName"}>{user.getDisplayName()}</div>
					<div className={"profileInfoUserName"}>{"@" + user.getUsername()}</div>
				</div>
			</div>

			<hr/>

			<Link to={"/profile/" + Auth.getCurrentUser().getUsername()}
				  onClick={() => DesktopHeaderAccountDropdown.close(this.props.parent)}>
				<i className="fas fa-user iconMargin-10"/>{__("navigation.account.myprofile")}
			</Link>

			<Link to={"/notifications"} onClick={() => DesktopHeaderAccountDropdown.close(this.props.parent)}>
				<i className="fas fa-bell iconMargin-10"/>{__("navigation.account.notifications")}
			</Link>

			<Link to={"/messages"} onClick={() => DesktopHeaderAccountDropdown.close(this.props.parent)}>
				<i className="fas fa-envelope iconMargin-10"/>{__("navigation.account.messages")}
			</Link>

			<hr/>

			<a href={"/settings/preferences/appearance"}
			   onClick={() => DesktopHeaderAccountDropdown.close(this.props.parent)}>
				<i className="fas fa-wrench iconMargin-10"/>{__("navigation.account.settings")}
			</a>

			<Link to={"#"} onClick={(e) => {
				e.preventDefault();

				DesktopHeaderAccountDropdown.close(this.props.parent);
				AccountSwitcher.open();
			}}>
				<i className={"fas fa-user-friends iconMargin-10"}/>{__("navigation.account.switch")}
			</Link>

			<Link to={"#"} onClick={(e) => {
				e.preventDefault();

				DesktopHeaderAccountDropdown.close(this.props.parent);
				Auth.logout();
			}}>
				<i className="fas fa-sign-out-alt iconMargin-10"/>{__("navigation.account.logout")}
			</Link>
		</div>;
	}
}