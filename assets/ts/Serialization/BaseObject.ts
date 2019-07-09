import Serialization from "./Serialization";

export default class BaseObject {
	static convertObject<T>(type: (new () => T), object: string | object): T {
		return Serialization.toInstance(new type(), object);
	}
}