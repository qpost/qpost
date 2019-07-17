/*
 * Copyright (C) 2018-2019 Gigadrive - All rights reserved.
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

export default class BaseObject {
	private static jsonConvert: JsonConvert;

	static convertObject<T>(type: (new () => T), object: string | object): T {
		if (typeof object === "string") {
			object = JSON.parse(object);
		}

		return this.getJsonConverter().deserializeObject(object, type);
	}

	private static getJsonConverter(): JsonConvert {
		if (!this.jsonConvert) {
			this.jsonConvert = new JsonConvert();
			this.jsonConvert.propertyMatchingRule = PropertyMatchingRule.CASE_INSENSITIVE;
		}

		return this.jsonConvert;
	}
}