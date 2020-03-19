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

export default class ShareEndpoint extends APIEndpoint {
	private path: string = "/share";

	/**
	 * Create a share from the current user for the specified FeedEntry.
	 * @param post The FeedEntry to share.
	 */
	public post(post: FeedEntry | number): Promise<FeedEntry> {
		return new Promise<FeedEntry>((resolve, reject) => {
			this.api.handleRequestWithPromise(this.path, "POST", {
				post: (post instanceof FeedEntry) ? post.getId() : post
			}).then(value => {
				return resolve(BaseObject.convertObject(FeedEntry, value));
			}).catch(reason => {
				return reject(reason);
			})
		});
	}

	/**
	 * Deletes a share from the current user for the specified FeedEntry. Returns the new parent data.
	 * @param post The FeedEntry to remove the share from.
	 */
	public delete(post: FeedEntry | number): Promise<FeedEntry> {
		return new Promise<FeedEntry>((resolve, reject) => {
			this.api.handleRequestWithPromise(this.path, "DELETE", {
				post: (post instanceof FeedEntry) ? post.getId() : post
			}).then(value => {
				return resolve(BaseObject.convertObject(FeedEntry, value.parent));
			}).catch(reason => {
				return reject(reason);
			})
		})
	}
}