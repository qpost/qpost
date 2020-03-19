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
import Block from "../../Entity/Account/Block";
import User from "../../Entity/Account/User";
import BaseObject from "../../Serialization/BaseObject";

export default class BlockEndpoint extends APIEndpoint {
	private path: string = "/block";

	/**
	 * Gets a Block entity that goes from the current user to the specified target.
	 * @param target The target to look for.
	 */
	public get(target: User | number): Promise<Block> {
		return new Promise<Block>((resolve, reject) => {
			return this.api.handleRequestWithPromise(this.path, "GET", {
				target: target instanceof User ? target.getId() : target
			}).then(value => {
				return resolve(BaseObject.convertObject(Block, value));
			}).catch(reason => {
				return reject(reason);
			});
		});
	}

	/**
	 * Creates a Block entity that goes from the current user to the specified target.
	 * @param target The target to use.
	 */
	public post(target: User | number): Promise<Block> {
		return new Promise<Block>((resolve, reject) => {
			return this.api.handleRequestWithPromise(this.path, "POST", {
				target: target instanceof User ? target.getId() : target
			}).then(value => {
				return resolve(BaseObject.convertObject(Block, value));
			}).catch(reason => {
				return reject(reason);
			});
		})
	}

	/**
	 * Deletes a Block entity that goes from the current user to the specified target.
	 * @param target The target to look for.
	 */
	public delete(target: User | number): Promise<void> {
		return new Promise<void>((resolve, reject) => {
			return this.api.handleRequestWithPromise(this.path, "DELETE", {
				target: target instanceof User ? target.getId() : target
			}).then(() => {
				return resolve();
			}).catch(reason => {
				return reject(reason);
			});
		});
	}

	/**
	 * Gets Block entities that go from the current user.
	 * @param max The maximum Block ID to look for.
	 */
	public list(max?: number): Promise<Block[]> {
		return new Promise<Block[]>((resolve, reject) => {
			return this.api.handleRequestWithPromise("/blocks", "GET", max ? {max} : {}).then(value => {
				return resolve(BaseObject.convertArray(Block, value));
			}).catch(reason => {
				return reject(reason);
			})
		});
	}
}