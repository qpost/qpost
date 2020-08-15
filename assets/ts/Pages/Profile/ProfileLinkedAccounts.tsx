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
import User from "../../api/src/Entity/User";
import LinkedAccount from "../../api/src/Entity/LinkedAccount";
import LinkedAccountService from "../../api/src/Entity/LinkedAccountService";
import {message, Tooltip} from "antd";
import {copyToClipboard} from "../../Util/Clipboard";
import __ from "../../i18n/i18n";

export default class ProfileLinkedAccounts extends Component<{
	user: User
}, any> {
	render(): React.ReactElement<any, string | React.JSXElementConstructor<any>> | string | number | {} | React.ReactNodeArray | React.ReactPortal | boolean | null | undefined {
		const identities: LinkedAccount[] | null = this.props.user.getIdentities();
		if (identities === null) return "";

		return <div className={"profileIdentities"}>
			{identities.map((account: LinkedAccount) => {
				let icon = undefined;
				switch (account.getService()) {
					case LinkedAccountService.DISCORD:
					case LinkedAccountService.TWITCH:
					case LinkedAccountService.TWITTER:
					case LinkedAccountService.LASTFM:
					case LinkedAccountService.MASTODON:
					case LinkedAccountService.SPOTIFY:
						icon =
							<i className={"service-" + account.getService().toLowerCase() + "-color fab fa-" + account.getService().toLowerCase()}/>;
						break;
				}

				let link = "";
				switch (account.getService()) {
					case LinkedAccountService.TWITCH:
						link = "https://twitch.tv/" + account.getLinkedUserName();
						break;
					case LinkedAccountService.TWITTER:
						link = "https://twitter.com/" + account.getLinkedUserName();
						break;
					case LinkedAccountService.LASTFM:
						link = "https://www.last.fm/user/" + account.getLinkedUserName();
						break;
					case LinkedAccountService.SPOTIFY:
						link = "https://open.spotify.com/user/" + account.getLinkedUserId();
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

						copyToClipboard(account.getLinkedUserName());
						message.success(__("profile.discordTagCopied"));
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