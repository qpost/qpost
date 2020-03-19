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
import FollowRequest from "../../Entity/Account/FollowRequest";
import BaseObject from "../../Serialization/BaseObject";

export default class FollowRequestEndpoint extends APIEndpoint {
	private path: string = "/followRequest";

	/**
	 * Gets FollowRequest entities, which represent the currently active follow requests for the current user.
	 * @param max The maximum FollowRequest ID to look for.
	 */
	public list(max?: number): Promise<FollowRequest[]> {
		return new Promise<FollowRequest[]>((resolve, reject) => {
			this.api.handleRequestWithPromise(this.path, "GET", max ? {max} : {}).then(value => {
				return resolve(BaseObject.convertArray(FollowRequest, value));
			}).catch(reason => {
				return reject(reason);
			})
		});
	}

	/**
	 * Deletes a FollowRequest entity by either accepting or declining it.
	 * @param id The request to accept/decline.
	 * @param action Whether to accept or decline the request.
	 */
	public delete(id: FollowRequest | number, action: "accept" | "decline"): Promise<void> {
		return new Promise<void>((resolve, reject) => {
			this.api.handleRequestWithPromise(this.path, "DELETE", {
				id: (id instanceof FollowRequest) ? id.getId() : id,
				action
			}).then(() => {
				return resolve();
			}).catch(reason => {
				return reject(reason);
			});
		});
	}
}