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