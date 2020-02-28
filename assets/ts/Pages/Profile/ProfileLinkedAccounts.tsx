/*
 * Copyright (C) 2018-2020 Gigadrive - All rights reserved.
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
import User from "../../Entity/Account/User";
import LinkedAccount from "../../Entity/Account/LinkedAccount";
import LinkedAccountService from "../../Entity/Account/LinkedAccountService";
import {message, Tooltip} from "antd";
import {Clipboard} from "ts-clipboard";

export default class ProfileLinkedAccounts extends Component<{
	user: User
}, any> {
	render(): React.ReactElement<any, string | React.JSXElementConstructor<any>> | string | number | {} | React.ReactNodeArray | React.ReactPortal | boolean | null | undefined {
		const identities: LinkedAccount[] | null = this.props.user.getIdentities();
		if (identities === null) return "";

		return <div className={"profileIdentities"}>
			{identities.map((account: LinkedAccount) => {
				const icon = <i
					className={"service-" + account.getService().toLowerCase() + "-color fab fa-" + account.getService().toLowerCase()}/>;

				let link = "";
				switch (account.getService()) {
					case LinkedAccountService.TWITCH:
						link = "https://twitch.tv/" + account.getLinkedUserName();
						break;
					case LinkedAccountService.TWITTER:
						link = "https://twitter.com/" + account.getLinkedUserName();
						break;
					case LinkedAccountService.MASTODON:
						const usernameSplit = account.getLinkedUserName().split("@");

						link = "http://" + usernameSplit[1] + "/@" + usernameSplit[0];
						break;
				}

				return <div className={"profileIdentity"} key={"profileIdentity-" + account.getId()}
							style={account.getService() === LinkedAccountService.DISCORD ? {
								cursor: "pointer"
							} : {}} onClick={(e) => {
					if (account.getService() === LinkedAccountService.DISCORD) {
						e.preventDefault();

						Clipboard.copy(account.getLinkedUserName());
						message.success("The Discord tag has been copied.");
					}
				}}>
					{account.getService() === LinkedAccountService.DISCORD ?
						<Tooltip title={account.getLinkedUserName()}>
							{icon}
						</Tooltip> : <a href={link} target={"_blank"}>
							{icon}
						</a>}
				</div>;
			})}
		</div>;
	}
}