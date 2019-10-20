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

export default class DesktopNotifications {
	private static active: boolean = false;

	public static start(): void {
		this.active = true;
	}

	public static stop(): void {
		this.active = false;
	}

	public static run(): void {
		if (!Auth.isLoggedIn()) return;

		setTimeout(() => {
			if (this.active) {
				API.handleRequest("/desktopNotifications", "GET", {}, data => {
					BadgeStatus.notifications = data.notifications;
					BadgeStatus.messages = data.messages;
					Header.update();

					const results: PostNotification[] = [];
					data.results.forEach(result => results.push(BaseObject.convertObject(PostNotification, result)));

					if (Notification.permission === "granted") {
						// TODO
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