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
import API from "../API";
import FollowButton from "./FollowButton";
import FollowStatus from "../Util/FollowStatus";
import User from "../api/src/Entity/User";
import __ from "../i18n/i18n";

export default class BlockModal extends Component<any, {
	open: boolean,
	user: User,
	loading: boolean
}> {
	private static INSTANCE: BlockModal = null;

	constructor(props) {
		super(props);

		this.state = {
			open: false,
			user: null,
			loading: false
		};
	}

	public static open(user: User): void {
		if (this.INSTANCE !== null) {
			this.INSTANCE.setState({
				open: true,
				user
			});
		}
	}

	public static close(): void {
		if (this.INSTANCE !== null) {
			this.INSTANCE.setState({
				open: false,
				user: null,
				loading: false
			});
		}
	}

	componentDidMount(): void {
		if (BlockModal.INSTANCE === null) {
			BlockModal.INSTANCE = this;
		}
	}

	componentWillUnmount(): void {
		if (BlockModal.INSTANCE !== null) {
			BlockModal.INSTANCE = null;
		}
	}

	render(): React.ReactElement<any, string | React.JSXElementConstructor<any>> | string | number | {} | React.ReactNodeArray | React.ReactPortal | boolean | null | undefined {
		const user = this.state.user;
		if (!user) return "";

		return <Modal
			visible={this.state.open}
			title={"Block @" + user.getUsername() + "?"}
			onOk={() => {
				this.setState({
					loading: true
				});

				API.i.block.post(user).then(block => {
					const newUser = block.getTarget();

					message.success(__("profile.block.success", {
						"%user%": "@" + user.getUsername()
					}));

					FollowButton.INSTANCES.forEach(followButton => {
						if (followButton.props.target.getId() === newUser.getId()) {
							followButton.setState({
								followStatus: FollowStatus.BLOCKED
							});
						}
					});

					this.setState({
						open: false,
						loading: false
					});
				}).catch(error => {
					this.setState({
						open: false,
						loading: false
					});

					message.error(error);
				});
			}}
			onCancel={() => {
				this.setState({
					open: false
				});
			}}
			confirmLoading={this.state.loading}
		>
			{__("profile.block.description", {
				"%user%": "@" + user.getUsername()
			})}
		</Modal>;
	}
}