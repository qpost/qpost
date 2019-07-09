import SerializedDateTimeTimezoneTransition from "./SerializedDateTimeTimezoneTransition";
import SerializedDateTimeTimezoneLocation from "./SerializedDateTimeTimezoneLocation";

export default class SerializedDateTimeTimezone {
	private name: string;
	private transitions: SerializedDateTimeTimezoneTransition[];
	private location: SerializedDateTimeTimezoneLocation;

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