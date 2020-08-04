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
import {message, Modal} from "antd";
import VerifiedBadge from "./VerifiedBadge";
import TokenStorage from "../Auth/TokenStorage";
import StoredToken from "../Auth/StoredToken";
import __ from "../i18n/i18n";

export default class AccountSwitcher extends Component<any, {
	open: boolean
}> {
	public static i: AccountSwitcher | undefined = undefined;

	constructor(props) {
		super(props);

		this.state = {
			open: false
		};
	}

	public static open(): void {
		if (this.i !== null) {
			this.i.setState({
				open: true
			});
		}
	}

	componentDidMount() {
		AccountSwitcher.i = this;
	}

	componentWillUnmount() {
		AccountSwitcher.i = undefined;
	}

	render() {
		const currentTokenAmount: number = TokenStorage.tokens.length;

		return <Modal
			title={__("accountSwitcher.headline") + " (" + currentTokenAmount + "/" + TokenStorage.LIMIT + ")"}
			visible={this.state.open}
			okButtonProps={{className: "d-none"}}
			onCancel={() => {
				this.setState({
					open: false
				})
			}}
		>
			<div className={"accountSwitcher"}>
				{TokenStorage.tokens.map((token: StoredToken, index: number) => {
					const user = token.getUser();
					console.log(token);

					return <div className={"account" + (index === 0 ? " active" : "")} key={"storedAccount-" + index}
								onClick={event => {
									event.preventDefault();

									if (index === 0) {
										message.error(__("accountSwitcher.alreadyLoggedIn", {
											"%user%": "@" + user.getUsername()
										}));
									} else {
										TokenStorage.switchUser(token);
									}
								}}>
						<div className={"avatar"}>
							<img src={user.getAvatarURL()} alt={user.getUsername()} title={user.getUsername()}/>
						</div>

						<div className={"info"}>
							<div className={"displayName"}>{user.getDisplayName()}<VerifiedBadge target={user}/></div>
							<div className={"userName"}>{"@" + user.getUsername()}</div>
						</div>
					</div>;
				})}

				{currentTokenAmount < TokenStorage.LIMIT ? <div className={"account addNew"} onClick={event => {
					event.preventDefault();

					message.info(__("accountSwitcher.redirecting"));
					window.location.href = "/login?addToken=true";
				}}>
					{__("accountSwitcher.addAccount")}
				</div> : ""}

				<div className={"account logout"} onClick={event => {
					event.preventDefault();

					message.info("Logging out...");

					TokenStorage.killAll().then(() => {
						window.location.href = "/";
					});
				}}>
					{__("accountSwitcher.logout")}
				</div>
			</div>
		</Modal>;
	}
}