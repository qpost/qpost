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
import FeedEntry from "../../Entity/Feed/FeedEntry";
import API from "../API";
import BaseObject from "../../Serialization/BaseObject";
import User from "../../Entity/Account/User";

export default class FeedEndpoint extends APIEndpoint {
	private path: string = "/feed";

	/**
	 * Get the contents for a FeedEntryList with the specified parameters.
	 * @param type The content to look for (only applies in combination with the user parameter).
	 * @param user The user who's posts to look for.
	 * @param max The maximum ID for FeedEntry entities to allow.
	 * @param min The minimum ID for FeedEntry entities to allow.
	 */
	public get(type: "posts" | "replies", user?: User | number, max?: number, min?: number): Promise<FeedEntry[]> {
		let data = {};

		if (type) data["type"] = type;
		if (user) data["user"] = (user instanceof User) ? user.getId() : user;
		if (max) data["max"] = max;
		if (min) data["min"] = min;

		return new Promise<FeedEntry[]>((resolve, reject) => {
			return API.handleRequestWithPromise(this.path, "GET", data).then(value => {
				return resolve(BaseObject.convertArray(FeedEntry, value));
			}).catch(reason => {
				return reject(reason);
			});
		});
	}
}