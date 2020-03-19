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
import Favorite from "../../Entity/Feed/Favorite";
import FeedEntry from "../../Entity/Feed/FeedEntry";
import BaseObject from "../../Serialization/BaseObject";
import User from "../../Entity/Account/User";

export default class FavoriteEndpoint extends APIEndpoint {
	private path: string = "/favorite";

	/**
	 * Creates a Favorite entity for the specified FeedEntry from the current user.
	 * @param post The FeedEntry to favorite.
	 */
	public post(post: FeedEntry | number): Promise<Favorite> {
		return new Promise<Favorite>((resolve, reject) => {
			this.api.handleRequestWithPromise(this.path, "POST", {
				post: (post instanceof FeedEntry) ? post.getId() : post
			}).then(value => {
				return resolve(BaseObject.convertObject(Favorite, value));
			}).catch(reason => {
				return reject(reason);
			})
		});
	}

	/**
	 * Deletes a Favorite entity for the specified FeedEntry from the current user.
	 * @param post The FeedEntry to unfavorite.
	 */
	public delete(post: FeedEntry | number): Promise<FeedEntry> {
		return new Promise<FeedEntry>((resolve, reject) => {
			this.api.handleRequestWithPromise(this.path, "DELETE", {
				post: (post instanceof FeedEntry) ? post.getId() : post
			}).then(value => {
				return resolve(BaseObject.convertObject(FeedEntry, value));
			}).catch(reason => {
				return reject(reason);
			});
		});
	}

	/**
	 * Gets all Favorite entities from the specified user.
	 * @param user The user to look for.
	 * @param max The maximum Favorite ID to look for.
	 */
	public list(user: User | number, max?: number): Promise<Favorite[]> {
		let data = {
			user: (user instanceof User) ? user.getId() : user
		};

		data["max"] = max;

		return new Promise<Favorite[]>((resolve, reject) => {
			this.api.handleRequestWithPromise("/favorites", "GET", data).then(value => {
				return resolve(BaseObject.convertArray(Favorite, value));
			}).catch(reason => {
				return reject(reason);
			})
		});
	}
}