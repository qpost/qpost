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

import Webpush from "webpush-client";

export default class PushManager {
	public static WebPushClient = null;

	public static init(): void {
		Webpush.create({
			serviceWorkerPath: "/sw.js",
			serverKey: window["VAPID_SERVER_KEY"],
			subscribeUrl: "/webpush/"
		}).then(WebPushClient => {
			PushManager.WebPushClient = WebPushClient;
			console.log("WebPushClient initiated", WebPushClient);

			WebPushClient.subscribe().then(subscription => {
				console.log("Subscribed to push notifications.", subscription);
			}).catch(reason => {
				console.error("Failed to subscribe to push notifications.", reason);
			});

			/*WebPushClient.getSubscription().then(subscription => {
				if(subscription === null){
					WebPushClient.subscribe().then(subscription => {
						console.log("Subscribed to push notifications.", subscription);
					}).catch(reason => {
						console.error("Failed to subscribe to push notifications.", reason);
					});
				} else {
					console.log("Already subscribed to push notifications.");
				}
			}).catch(reason => {
				console.error("Failed to check push notifications status.", reason);
			});*/
		});
	}
}