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

export default class StatusEndpoint extends APIEndpoint {
	private path: string = "/status";

	/**
	 * Gets a FeedEntry with type POST or REPLY by it's ID
	 * @param id The id to look for.
	 */
	public get(id: number): Promise<FeedEntry> {
		return new Promise<FeedEntry>((resolve, reject) => {
			this.api.handleRequestWithPromise(this.path, "GET", {id}).then(value => {
				return resolve(BaseObject.convertObject(FeedEntry, value));
			}).catch(reason => {
				return reject(reason);
			})
		});
	}

	/**
	 * Creates a new FeedEntry with type POST or REPLY (depending on whether the parent parameter is present) for the current user.
	 * @param message The message value for the new FeedEntry.
	 * @param nsfw Whether this FeedEntry contains adult content or not.
	 * @param attachments The images to attach to this FeedEntry (base64 strings).
	 * @param parent The parent of the new FeedEntry
	 */
	public post(message: string, nsfw: boolean, attachments: string[], parent?: FeedEntry | number): Promise<FeedEntry> {
		let data = {message, nsfw, attachments};

		if (parent) data["parent"] = (parent instanceof FeedEntry) ? parent.getId() : parent;

		return new Promise<FeedEntry>((resolve, reject) => {
			this.api.handleRequestWithPromise(this.path, "POST", data).then(value => {
				return resolve(BaseObject.convertObject(FeedEntry, value));
			}).catch(reason => {
				return reject(reason);
			});
		});
	}

	/**
	 * Deletes a FeedEntry entity. Use the Share endpoint to delete FeedEntry entities with type SHARE.
	 * @param id The id of the FeedEntry to delete.
	 */
	public delete(id: FeedEntry | number): Promise<void> {
		return new Promise<void>((resolve, reject) => {
			this.api.handleRequestWithPromise(this.path, "DELETE", {
				id: (id instanceof FeedEntry) ? id.getId() : id
			}).then(() => {
				return resolve();
			}).catch(reason => {
				return reject(reason);
			})
		})
	}
}