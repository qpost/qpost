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
import User from "../Entity/Account/User";
import {message, Modal} from "antd";
import API from "../API/API";
import BaseObject from "../Serialization/BaseObject";
import Block from "../Entity/Account/Block";

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

				API.handleRequest("/block", "POST", {
					target: user.getId()
				}, data => {
					this.setState({
						open: false,
						loading: false
					});

					if (data.result) {
						const block = BaseObject.convertObject(Block, data.result);
						if (block) {
							message.success("You have successfully blocked @" + user.getUsername());
							// TODO: Update FollowButtons
						} else {
							message.error("An error occurred.");
						}
					} else {
						message.error("An error occurred.");
					}
				}, error => {
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
			block
		</Modal>;
	}
}