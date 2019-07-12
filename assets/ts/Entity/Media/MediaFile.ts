import SerializedDateTime from "../../Serialization/Date/SerializedDateTime";
import {JsonObject, JsonProperty} from "json2typescript";

@JsonObject("MediaFile")
export default class MediaFile {
	@JsonProperty("id", String)
	private id: string = undefined;

	@JsonProperty("sha256", String)
	private sha256: string = undefined;

	@JsonProperty("url", String)
	private url: string = undefined;

	@JsonProperty("type", String)
	private type: string = undefined;

	@JsonProperty("time", SerializedDateTime)
	private time: SerializedDateTime = undefined;

	public getId(): string {
		return this.id;
	}

	public getSHA265(): string {
		return this.sha256;
	}

	public getURL(): string {
		return this.url;
	}

	public getType(): string {
		return this.type;
	}

	public getTime(): SerializedDateTime {
		return this.time;
	}
}