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
import BadgeStatus from "../../Entity/BadgeStatus";
import API from "../API";
import BaseObject from "../../Serialization/BaseObject";

export default class BadgeStatusEndpoint extends APIEndpoint {
	private path: string = "/badgestatus";

	/**
	 * Gets the current badge status information
	 */
	public get(): Promise<BadgeStatus> {
		return new Promise<BadgeStatus>((resolve, reject) => {
			API.handleRequestWithPromise(this.path).then(value => {
				return resolve(BaseObject.convertObject(BadgeStatus, value));
			}).catch(reason => {
				return reject(reason);
			});
		})
	}

	/**
	 * Clears the badge status.
	 * @param type What badge to clear.
	 */
	public delete(type: "notifications" | "messages"): Promise<void> {
		return new Promise<void>((resolve, reject) => {
			API.handleRequestWithPromise(this.path, "DELETE", {type}).then(() => {
				return resolve();
			}).catch(reason => {
				return reject(reason);
			})
		})
	}
}