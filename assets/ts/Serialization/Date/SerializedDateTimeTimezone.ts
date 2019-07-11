import SerializedDateTimeTimezoneTransition from "./SerializedDateTimeTimezoneTransition";
import SerializedDateTimeTimezoneLocation from "./SerializedDateTimeTimezoneLocation";
import {JsonObject, JsonProperty} from "json2typescript";

@JsonObject("SerializedDateTimeTimezone")
export default class SerializedDateTimeTimezone {
	@JsonProperty("name", String)
	private name: string = undefined;

	@JsonProperty("transitions", [SerializedDateTimeTimezoneTransition])
	private transitions: SerializedDateTimeTimezoneTransition[] = undefined;

	@JsonProperty("location", SerializedDateTimeTimezoneLocation)
	private location: SerializedDateTimeTimezoneLocation = undefined;

	public getName(): string {
		return this.name;
	}

	public getTransitions(): SerializedDateTimeTimezoneTransition[] {
		return this.transitions;
	}

	public getLocation(): SerializedDateTimeTimezoneLocation {
		return this.location;
	}
}