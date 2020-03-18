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

import APIEndpoint from "./APIEndpoint";
import API from "../API";
import BaseObject from "../../Serialization/BaseObject";
import Notification from "../../Entity/Feed/Notification";

export default class NotificationsEndpoint extends APIEndpoint {
	private path: string = "/notifications";

	/**
	 * Gets Notification entities for the current user.
	 * @param max The maximum Notification ID to look for.
	 */
	public get(max?: number): Promise<Notification[]> {
		return new Promise<Notification[]>((resolve, reject) => {
			API.handleRequestWithPromise(this.path, "GET", max ? {max} : {}).then(value => {
				return resolve(BaseObject.convertArray(Notification, value));
			}).catch(reason => {
				return reject(reason);
			});
		});
	}
}