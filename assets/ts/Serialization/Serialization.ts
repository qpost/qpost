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

export default class Serialization {
	/**
	 * Casts a json string or object to a specific object class
	 * @param obj The final object
	 * @param json The string or object to convert
	 * @return The final object with the converted properties
	 */
	public static toInstance<T>(obj: T, json: string | object): T {
		let jsonObject: object;
		if (typeof json === "string") {
			jsonObject = JSON.parse(json);
		} else {
			jsonObject = json;
		}

		for (const propName in jsonObject) {
			obj[propName] = jsonObject[propName]
		}

		return obj;
	}

	/**
	 * Serializes an object into a string
	 * @param object The object to be serialized
	 * @return The final string
	 */
	public static toString(object: any): string {
		return JSON.stringify(object);
	}
}