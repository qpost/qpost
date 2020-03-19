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
import User from "../../Entity/Account/User";
import BaseObject from "../../Serialization/BaseObject";

export default class UserEndpoint extends APIEndpoint {
	private path: string = "/user";

	/**
	 * Gets a user by their username.
	 * @param user The username to look for (case insensitive)
	 */
	public get(user: string): Promise<User> {
		return new Promise<User>((resolve, reject) => {
			return this.api.handleRequestWithPromise(this.path, "GET", {user}).then(value => {
				resolve(BaseObject.convertObject(User, value));
			}).catch(reason => {
				reject(reason);
			});
		});
	}
}