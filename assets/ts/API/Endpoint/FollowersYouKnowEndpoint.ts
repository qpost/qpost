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

export default class FollowersYouKnowEndpoint extends APIEndpoint {
	private path: string = "/followersyouknow";

	/**
	 * Gets the followers of the specified target, that are followed by the current user.
	 * @param target The target to look for.
	 * @param limit The limit of results to return.
	 */
	public get(target: User | number, limit: number): Promise<User[]> {
		return new Promise<User[]>((resolve, reject) => {
			this.api.handleRequestWithPromise(this.path, "GET", {
				target: (target instanceof User) ? target.getId() : target,
				limit
			}).then(value => {
				return resolve(BaseObject.convertArray(User, value));
			}).catch(reason => {
				return reject(reason);
			});
		});
	}
}