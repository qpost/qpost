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
import User from "../Entity/Account/User";
import FollowStatus from "../Util/FollowStatus";
import Auth from "../Auth/Auth";
import {Redirect} from "react-router-dom";
import API from "../API/API";
import message from "antd/es/message";
import "antd/es/message/style";
import Spin from "antd/es/spin";
import "antd/es/spin/style";
import Button from "antd/es/button";
import "antd/es/button/style";
import {Method} from "axios";
import LoginSuggestionModal from "./LoginSuggestionModal";
import Storage from "../Util/Storage";

export default class FollowButton extends Component<{
	target: User,
	className?: string,
	followStatus?: number,
	size?: "small" | "large" | "default",
	block?: boolean
}, {
	redirectToEditPage: boolean,
	loading: boolean,
	followStatus: number | null,
	error: string | null
}> {
	public static INSTANCES: FollowButton[] = [];

	constructor(props) {
		super(props);

		this.state = {
			redirectToEditPage: false,
			loading: this.props.followStatus === undefined && !this.props.target.isBlocked(),
			followStatus: undefined,
			error: null
		};
	}

	click = (e) => {
		e.preventDefault();

		if (Auth.isLoggedIn()) {
			if (this.isCurrentUser()) {
				this.setState({
					redirectToEditPage: true
				});
			} else {
				if (!this.state.loading) {
					window.localStorage.removeItem(Storage.SESSION_SUGGESTED_USERS);

					const followStatus: number = this.followStatus();
					let method: Method = followStatus === FollowStatus.FOLLOWING || followStatus === FollowStatus.PENDING || followStatus === FollowStatus.BLOCKED ? "DELETE" : "POST";

					this.setState({
						loading: true
					});

					API.handleRequest(followStatus === FollowStatus.BLOCKED ? "/block" : "/follow", method, {[followStatus === FollowStatus.BLOCKED ? "target" : "to"]: this.props.target.getId()}, data => {
						if (followStatus === FollowStatus.BLOCKED) {
							this.setState({
								followStatus: FollowStatus.NOT_FOLLOWING,
								loading: false
							});
						} else {
							if (data.hasOwnProperty("status")) {
								this.setState({
									followStatus: data.status,
									loading: false
								});
							} else {
								this.setState({
									loading: false
								});

								message.error("An error occurred.");
							}
						}
					}, error => {
						message.error(error);

						this.setState({
							loading: false
						});
					});
				}
			}
		} else {
			LoginSuggestionModal.open();
		}
	};

	componentDidMount(): void {
		if (!FollowButton.INSTANCES.includes(this)) {
			FollowButton.INSTANCES.push(this);
		}

		if (this.props.target.isBlocked() && !this.isCurrentUser()) {
			this.setState({
				followStatus: FollowStatus.BLOCKED,
				loading: false
			});

			return;
		}

		if (this.props.followStatus === undefined) {
			// Fetch follow status if it was not passed

			if (Auth.isLoggedIn()) {
				if (!this.isCurrentUser()) {
					API.handleRequest("/follow", "GET", {
						from: Auth.getCurrentUser().getId(),
						to: this.props.target.getId()
					}, data => {
						if (data.status) {
							this.setState({
								followStatus: data.status,
								loading: false
							});
						} else {
							this.setState({
								followStatus: FollowStatus.FOLLOWING,
								loading: false
							});
						}
					}, error => {
						this.setState({
							followStatus: FollowStatus.NOT_FOLLOWING,
							loading: false
						});
					})
				}
			} else {
				// Default to not following if user is not logged in
				this.setState({
					followStatus: FollowStatus.NOT_FOLLOWING,
					loading: false
				});
			}
		}
	}

	componentWillUnmount(): void {
		if (FollowButton.INSTANCES.includes(this)) {
			const index = FollowButton.INSTANCES.indexOf(this);
			if (index > -1) {
				FollowButton.INSTANCES.splice(index, 1);
			}
		}
	}

	followStatus: () => number = () => {
		return this.state.followStatus || this.props.followStatus;
	};

	firstLoad: () => boolean = () => {
		return !this.props.followStatus && this.state.loading;
	};

	render(): React.ReactElement<any, string | React.JSXElementConstructor<any>> | string | number | {} | React.ReactNodeArray | React.ReactPortal | boolean | null | undefined {
		if (this.state.redirectToEditPage) {
			return <Redirect push to={"/edit"}/>
		}

		let text: string = "";

		if (this.isCurrentUser()) {
			text = "Edit profile";
		} else {
			switch (this.followStatus()) {
				case FollowStatus.FOLLOWING:
					text = "Unfollow";
					break;

				case FollowStatus.PENDING:
					text = "Pending";
					break;

				case FollowStatus.BLOCKED:
					text = "Blocked";
					break;

				default:
					text = "Follow";
					break;
			}
		}

		return <Button
			className={"followButton" + (this.props.className ? " " + this.props.className : "")}
			size={this.props.size || "default"}
			type={!this.state.loading && !this.isCurrentUser() && text === "Follow" ? "primary" : "default"}
			data-follow-status={!this.state.loading ? (this.props.followStatus ? this.props.followStatus : this.state.followStatus ? this.state.followStatus : -1) : -1}
			block={this.props.block}
			onClick={(e) => this.click(e)} shape={"round"}>
			{(!this.state.loading && !this.firstLoad()) || this.isCurrentUser() ? text : <Spin size={"small"}/>}
		</Button>;

		/*return <button type={"button"}
					   className={"btn btn-" + color + (this.props.className ? " " + this.props.className : "")}
					   onClick={(e) => this.click(e)}>{text}</button>*/
	}

	private isCurrentUser(): boolean {
		const currentUser = Auth.getCurrentUser();
		return currentUser && currentUser.getId() === this.props.target.getId();
	}
}