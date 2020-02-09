/*
 * Copyright (C) 2018-2020 Gigadrive - All rights reserved.
 * https://gigadrivegroup.com
 * https://qpo.st
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

//import Serialization from "./Serialization";
import {JsonConvert} from "json2typescript";
import {PropertyMatchingRule} from "json2typescript/src/json2typescript/json-convert-enums";
import FeedEntry from "../Entity/Feed/FeedEntry";
import Auth from "../Auth/Auth";
import User from "../Entity/Account/User";
import Notification from "../Entity/Feed/Notification";

export default class BaseObject {
	private static jsonConvert: JsonConvert;

	static convertObject<T>(type: (new () => T), object: string | object): T {
		if (typeof object === "string") {
			object = JSON.parse(object);
		}

		const result = this.getJsonConverter().deserializeObject(object, type);
		const currentUser = Auth.getCurrentUser();

		if (currentUser) {
			if (result instanceof FeedEntry) {
				const user = result.getUser();

				if (user) {
					user.saveToStorage();

					if (user.getId() === currentUser.getId()) {
						Auth.setCurrentUser(user);
					}
				}
			} else if (result instanceof User) {
				result.saveToStorage();

				if (result.getId() === currentUser.getId()) {
					Auth.setCurrentUser(result);
				}
			} else if (result instanceof Notification) {
				let user = result.getUser();

				if (user) {
					user.saveToStorage();

					if (user.getId() === currentUser.getId()) {
						Auth.setCurrentUser(user);
					}
				}
			}
		}

		return result;
	}

	static convertArray<T>(type: (new () => T), object: string | any[]): T[] {
		let parsed: any[];

		if (typeof object === "string") {
			parsed = JSON.parse(object);
		} else {
			parsed = object;
		}

		const result = this.getJsonConverter().deserializeArray(parsed, type);
		const currentUser = Auth.getCurrentUser();

		if (currentUser) {
			result.forEach(r => {
				if (r instanceof FeedEntry) {
					const user = r.getUser();

					if (user) {
						user.saveToStorage();

						if (user.getId() === currentUser.getId()) {
							Auth.setCurrentUser(user);
						}
					}
				} else if (r instanceof User) {
					r.saveToStorage();

					if (r.getId() === currentUser.getId()) {
						Auth.setCurrentUser(r);
					}
				} else if (r instanceof Notification) {
					let user = r.getUser();

					if (user) {
						user.saveToStorage();

						if (user.getId() === currentUser.getId()) {
							Auth.setCurrentUser(user);
						}
					}
				}
			})
		}

		return result;
	}

	private static getJsonConverter(): JsonConvert {
		if (!this.jsonConvert) {
			this.jsonConvert = new JsonConvert();
			this.jsonConvert.propertyMatchingRule = PropertyMatchingRule.CASE_INSENSITIVE;
		}

		return this.jsonConvert;
	}
}