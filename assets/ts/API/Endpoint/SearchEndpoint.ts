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
import User from "../../Entity/Account/User";
import BaseObject from "../../Serialization/BaseObject";
import SearchResult from "../../Entity/SearchResult";

export default class SearchEndpoint extends APIEndpoint {
	private path: string = "/search";

	/**
	 * Searches for either User entities or FeedEntry entities matching a specific query.
	 * @param type The type of entity to look for.
	 * @param query The query to use.
	 * @param limit The maximum amount of results to return.
	 */
	public get(type: "post" | "user", query: string, limit?: number): Promise<SearchResult> {
		limit = limit || 15;

		return new Promise<SearchResult>((resolve, reject) => {
			this.api.handleRequestWithPromise(this.path, "GET", {
				type, query, limit
			}).then(value => {
				const result = new SearchResult();
				result.type = type;

				if (type === "post") {
					result.feedEntries = BaseObject.convertArray(FeedEntry, value);
				} else if (type === "user") {
					result.users = BaseObject.convertArray(User, value);
				}

				return resolve(result);
			}).catch(reason => {
				return reject(reason);
			})
		});
	}
}