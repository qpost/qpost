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

	public toDate(): Date {
		return new Date(this.timestamp * 1000);
	}

	public toString(): string {
		const date: Date = this.toDate();

		return date.getUTCFullYear() + "-" + this.addZero(date.getUTCMonth() + 1) + "-" + this.addZero(date.getUTCDay()) + "T" + this.addZero(date.getUTCHours()) + ":" + this.addZero(date.getUTCMinutes()) + ":" + this.addZero(date.getUTCSeconds()) + "Z";
	}

	private addZero(number: number): string {
		return number < 10 && number >= 0 ? "0" + number : number.toString();
	}
}