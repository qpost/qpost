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
import Token from "../../Entity/Account/Token";
import API from "../API";
import BaseObject from "../../Serialization/BaseObject";
import User from "../../Entity/Account/User";

export default class TokenEndpoint extends APIEndpoint {
	private path: string = "/token";

	/**
	 * Gets all active Token entities for the current user.
	 */
	public list(): Promise<Token[]> {
		return new Promise<Token[]>((resolve, reject) => {
			API.handleRequestWithPromise(this.path).then(value => {
				return resolve(BaseObject.convertArray(Token, value));
			}).catch(reason => {
				return reject(reason);
			})
		});
	}

	/**
	 * Invalidates a specific token for the current user.
	 * @param id The token to invalidate.
	 */
	public delete(id: string): Promise<void> {
		return new Promise<void>((resolve, reject) => {
			API.handleRequestWithPromise(this.path, "DELETE", {id}).then(value => {
				return resolve();
			}).catch(reason => {
				return reject(reason);
			});
		})
	}

	/**
	 * Verifies the current token and returns the associated user.
	 */
	public verify(): Promise<User> {
		return new Promise<User>((resolve, reject) => {
			API.handleRequestWithPromise(this.path + "/verify", "POST").then(value => {
				return resolve(BaseObject.convertObject(User, value.user));
			}).catch(reason => {
				return reject(reason);
			})
		})
	}
}