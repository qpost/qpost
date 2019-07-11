import {JsonObject, JsonProperty} from "json2typescript";

@JsonObject("SerializedDateTimeTimezoneTransition")
export default class SerializedDateTimeTimezoneTransition {
	@JsonProperty("ts", Number)
	private ts: number = undefined;

	@JsonProperty("time", String)
	private time: string = undefined;

	@JsonProperty("offset", Number)
	private offset: number = undefined;

	@JsonProperty("isdst", Boolean)
	private isdst: boolean = undefined;

	@JsonProperty("abbr", String)
	private abbr: string = undefined;

	public getTimestamp(): number {
		return this.ts;
	}

	public getTime(): string {
		return this.time;
	}

	public getOffset(): number {
		return this.offset;
	}

	public isDST(): boolean {
		return this.isdst;
	}

	public getAbbreviation(): string {
		return this.abbr;
	}
}