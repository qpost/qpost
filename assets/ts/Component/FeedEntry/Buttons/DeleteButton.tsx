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
import FeedEntry from "../../../Entity/Feed/FeedEntry";
import Auth from "../../../Auth/Auth";
import FeedEntryActionButtons from "./FeedEntryActionButtons";
import Modal from "antd/es/modal";
import "antd/es/modal/style";
import {Button, message} from "antd";
import "antd/es/button/style";
import API from "../../../API/API";
import {Redirect} from "react-router";
import FeedEntryList from "../FeedEntryList";

export default class DeleteButton extends Component<{
	entry: FeedEntry,
	parent?: FeedEntryActionButtons
}, {
	modalVisible: boolean,
	loading: boolean,
	redirect: boolean
}> {
	constructor(props) {
		super(props);

		this.state = {
			modalVisible: false,
			loading: false,
			redirect: false
		};
	}

	click = (e) => {
		e.preventDefault();
		e.stopPropagation();

		this.setState({
			modalVisible: true
		});
	};

	closeModal = () => {
		this.setState({
			modalVisible: false
		});
	};

	render(): React.ReactElement<any, string | React.JSXElementConstructor<any>> | string | number | {} | React.ReactNodeArray | React.ReactPortal | boolean | null | undefined {
		if (this.state.redirect) {
			return <Redirect to={"/"}/>;
		}

		const currentUser = Auth.getCurrentUser();
		if (!currentUser || currentUser.getId() !== this.props.entry.getUser().getId()) {
			return "";
		}

		return [
			<div key={0} className={"d-inline-block deleteButton"}
					onClick={(e) => this.click(e)}>
			<i className={"fas fa-trash-alt"}/>
			</div>,
			<Modal
				key={1}
				title={"Are you sure you want to delete this post?"}
				footer={[
					<Button onClick={(e) => {
						e.preventDefault();
						e.stopPropagation();

						this.closeModal();
					}}>
						Cancel
					</Button>,

					<Button type={"danger"} className={"customDangerButton"} loading={this.state.loading}
							onClick={(e) => {
								e.preventDefault();
								e.stopPropagation();

								if (this.state.loading) return;

								const buttons = this.props.parent;
								if (buttons) {
									const feedEntryItem = buttons.props.parent;

									if (feedEntryItem) {
										const entry = feedEntryItem.props.entry;
										const feedEntryList = feedEntryItem.props.parent;

										if (feedEntryList && feedEntryList instanceof FeedEntryList) {
											const entries = feedEntryList.state.entries;

											if (entries) {
												if (entry) {
													const index = entries.indexOf(entry, 0);
													if (index > -1) {
														this.setState({
															loading: true
														});

														API.handleRequest("/status", "DELETE", {
															id: entry.getId()
														}, data => {
															message.success("The post has been deleted.");

															entries.splice(index, 1);

															feedEntryList.setState({
																entries
															});

															this.setState({
																loading: false,
																modalVisible: false
															});
														}, error => {
															message.error(error);

															this.setState({
																loading: false,
																modalVisible: false
															});
														});

														return;
													}
												}
											}
										}
									} else if (this.props.entry) {
										this.setState({
											loading: true
										});

										API.handleRequest("/status", "DELETE", {
											id: this.props.entry.getId()
										}, data => {
											message.success("The post has been deleted.");

											this.setState({
												loading: false,
												modalVisible: false,
												redirect: true
											});
										}, error => {
											message.error(error);

											this.setState({
												loading: false,
												modalVisible: false
											});
										});

										return;
									}
								}

								this.closeModal();
							}}>
						Delete
					</Button>
				]}
				visible={this.state.modalVisible}
				closable={false}
				onCancel={this.closeModal}>
				Please remember that deleting this post can <b>NOT</b> be undone!
			</Modal>];
	}
}