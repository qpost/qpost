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
import Follower from "../../Entity/Account/Follower";
import API from "../API";
import User from "../../Entity/Account/User";
import BaseObject from "../../Serialization/BaseObject";

export default class FollowEndpoint extends APIEndpoint {
	private path: string = "/follow";

	/**
	 * Gets information about a specific Follower entity, that matches the passed parameters. Potentially returns a FollowStatus.
	 * @param from The sender to look for.
	 * @param to The receiver to look for.
	 */
	public get(from: User | number, to: User | number): Promise<Follower | number> {
		return new Promise<Follower | number>((resolve, reject) => {
			API.handleRequestWithPromise(this.path, "GET", {
				from: (from instanceof User) ? from.getId() : from,
				to: (to instanceof User) ? to.getId() : to
			}).then(value => {
				if (value.status) {
					return resolve(value.status);
				} else {
					return resolve(BaseObject.convertObject(Follower, value));
				}
			}).catch(reason => {
				return reject(reason);
			})
		});
	}

	/**
	 * Creates a Follower entity from the current user to the specified target. Returns a FollowStatus.
	 * @param to The target to follow.
	 */
	public post(to: User | number): Promise<number> {
		return new Promise<number>((resolve, reject) => {
			API.handleRequestWithPromise(this.path, "POST", {
				to: (to instanceof User) ? to.getId() : to
			}).then(value => {
				return resolve(value.status);
			}).catch(reason => {
				return reject(reason);
			});
		})
	}

	/**
	 * Deletes a Follow entity from the current user to the specified target. Returns a FollowStatus.
	 * @param to The target to follow.
	 */
	public delete(to: User | number): Promise<number> {
		return new Promise<number>((resolve, reject) => {
			API.handleRequestWithPromise(this.path, "DELETE", {
				to: (to instanceof User) ? to.getId() : to
			}).then(value => {
				return resolve(value.status);
			}).catch(reason => {
				return reject(reason);
			})
		});
	}

	/**
	 * Gets a list of Follower entities matching the specified parameters.
	 * @param from The sender to look for.
	 * @param to The receiver to look for.
	 * @param max The maximum Follower ID to look for.
	 */
	public list(from?: User | number | null, to?: User | number | null, max?: number): Promise<Follower[]> {
		let data = {};
		if (from) data["from"] = (from instanceof User) ? from.getId() : from;
		if (to) data["to"] = (to instanceof User) ? to.getId() : to;
		if (max) data["max"] = max;

		return new Promise<Follower[]>((resolve, reject) => {
			API.handleRequestWithPromise("/follows", "GET", data).then(value => {
				return resolve(BaseObject.convertArray(Follower, value));
			}).catch(reason => {
				return reject(reason);
			})
		})
	}
}