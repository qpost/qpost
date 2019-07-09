import SerializedDateTimeTimezone from "./SerializedDateTimeTimezone";

export default class SerializedDateTime {
	private timezone: SerializedDateTimeTimezone;
	private offset: number;
	private timestamp: number;

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