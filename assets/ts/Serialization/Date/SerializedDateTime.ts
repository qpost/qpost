import SerializedDateTimeTimezone from "./SerializedDateTimeTimezone";
import {JsonObject, JsonProperty} from "json2typescript";

@JsonObject("SerializedDateTime")
export default class SerializedDateTime {
	@JsonProperty("timezone", SerializedDateTimeTimezone)
	private timezone: SerializedDateTimeTimezone = undefined;

	@JsonProperty("offset", Number)
	private offset: number = undefined;

	@JsonProperty("timestamp", Number)
	private timestamp: number = undefined;

	public getTimezone(): SerializedDateTimeTimezone {
		return this.timezone;
	}

	public getOffset(): number {
		return this.offset;
	}

	public getTimestamp(): number {
		return this.timestamp;
	}
}