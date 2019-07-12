//import Serialization from "./Serialization";
import {JsonConvert} from "json2typescript";
import {PropertyMatchingRule} from "json2typescript/src/json2typescript/json-convert-enums";

export default class BaseObject {
	private static jsonConvert: JsonConvert;

	static convertObject<T>(type: (new () => T), object: string | object): T {
		if (typeof object === "string") {
			object = JSON.parse(object);
		}

		const deserialized: T = this.getJsonConverter().deserializeObject(object, type);

		console.log(deserialized);

		return deserialized;
		//return Serialization.toInstance(new type(), object);
	}

	private static getJsonConverter(): JsonConvert {
		if (!this.jsonConvert) {
			this.jsonConvert = new JsonConvert();
			this.jsonConvert.propertyMatchingRule = PropertyMatchingRule.CASE_INSENSITIVE;
		}

		return this.jsonConvert;
	}
}