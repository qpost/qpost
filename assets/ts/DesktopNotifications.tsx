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

import API from "./API/API";
import {message} from "antd";
import BadgeStatus from "./Auth/BadgeStatus";
import BaseObject from "./Serialization/BaseObject";
import PostNotification from "./Entity/Feed/Notification";
import Auth from "./Auth/Auth";
import Header from "./Parts/Header";
import NotificationType from "./Entity/Feed/NotificationType";
import React, {Component} from "react";
import {Redirect} from "react-router";

export default class DesktopNotifications extends Component<any, {
	active: boolean,
	redirect: boolean
}> {
	private static INSTANCE: DesktopNotifications = null;

	constructor(props) {
		super(props);

		this.state = {
			active: true,
			redirect: false
		};
	}

	public static start(): void {
		if (this.INSTANCE !== null) {
			this.INSTANCE.setState({
				active: true
			});
		}
	}

	public static stop(): void {
		if (this.INSTANCE !== null) {
			this.INSTANCE.setState({
				active: false
			});
		}
	}

	componentDidMount(): void {
		if (DesktopNotifications.INSTANCE === null) {
			DesktopNotifications.INSTANCE = this;
		}

		this.run();
	}

	componentWillUnmount(): void {
		if (DesktopNotifications.INSTANCE === this) {
			DesktopNotifications.INSTANCE = null;
		}
	}

	render(): React.ReactElement<any, string | React.JSXElementConstructor<any>> | string | number | {} | React.ReactNodeArray | React.ReactPortal | boolean | null | undefined {
		return this.state.redirect ? <Redirect to={"/notifications"}/> : "";
	}

	private run(): void {
		if (!Auth.isLoggedIn()) return;
		const _this = this;

		setTimeout(() => {
			if (this.state.active) {
				API.handleRequest("/desktopNotifications", "GET", {}, data => {
					BadgeStatus.notifications = data.notifications;
					BadgeStatus.messages = data.messages;
					Header.update();

					const results: PostNotification[] = [];
					data.results.forEach(result => results.push(BaseObject.convertObject(PostNotification, result)));

					if (Notification.permission === "granted") {
						results.forEach(notification => {
							let title = "";
							let text = "";
							let icon = "";

							const referencedUser = notification.getReferencedUser();
							const referencedFeedEntry = notification.getReferencedFeedEntry();

							switch (notification.getType()) {
								case NotificationType.NEW_FOLLOWER:
									title = referencedUser.getDisplayName() + " (@" + referencedUser.getUsername() + ") is now following you.";
									icon = referencedUser.getAvatarURL();
									text = referencedUser.getBio();
									break;
								case NotificationType.MENTION:
									title = referencedUser.getDisplayName() + " (@" + referencedUser.getUsername() + ") mentioned you.";
									icon = referencedUser.getAvatarURL();
									text = referencedFeedEntry.getText();
									break;
								case NotificationType.FAVORITE:
									title = referencedUser.getDisplayName() + " (@" + referencedUser.getUsername() + ") favorited your post.";
									icon = referencedUser.getAvatarURL();
									text = referencedFeedEntry.getText();
									break;
								case NotificationType.SHARE:
									title = referencedUser.getDisplayName() + " (@" + referencedUser.getUsername() + ") shared your post.";
									icon = referencedUser.getAvatarURL();
									text = referencedFeedEntry.getText();
									break;
								case NotificationType.REPLY:
									title = referencedUser.getDisplayName() + " (@" + referencedUser.getUsername() + ") replied to your post.";
									icon = referencedUser.getAvatarURL();
									text = referencedFeedEntry.getText();
									break;
								default:
									return;
							}

							const desktopNotification = new Notification(title, {
								body: text,
								icon,
								timestamp: new Date(notification.getTime()).getTime() / 1000,
								requireInteraction: false,
								badge: "https://qpo.st/assets/img/favicon.png"
							});

							desktopNotification.onclick = (e) => {
								e.preventDefault();

								_this.setState({
									redirect: true
								});

								setTimeout(() => {
									_this.setState({
										redirect: false
									});
								}, 500);
							};
						});
					}

					this.run();
				}, error => {
					message.error("Failed to update notifications (" + error + ")");
					this.run();
				});
			} else {
				this.run();
			}
		}, 3000);
	}
}