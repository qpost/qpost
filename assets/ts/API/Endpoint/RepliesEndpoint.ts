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
import BaseObject from "../../Serialization/BaseObject";

export default class RepliesEndpoint extends APIEndpoint {
	private path: string = "/replies";

	/**
	 * Gets the reply batches for a specific FeedEntry entity.
	 * @param feedEntry The FeedEntry to look for.
	 * @param page The page to return.
	 */
	public get(feedEntry: FeedEntry | number, page: number): Promise<FeedEntry[][]> {
		return new Promise<FeedEntry[][]>((resolve, reject) => {
			this.api.handleRequestWithPromise(this.path, "GET", {
				feedEntry: (feedEntry instanceof FeedEntry) ? feedEntry.getId() : feedEntry,
				page
			}).then(value => {
				const results: FeedEntry[][] = [];

				for (let i in value) {
					const thread = value[i];
					results.push(BaseObject.convertArray(FeedEntry, thread));
				}

				return resolve(results);
			}).catch(reason => {
				return reject(reason);
			});
		});
	}
}