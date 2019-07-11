import {JsonObject, JsonProperty} from "json2typescript";

@JsonObject("SerializedDateTimeTimezoneLocation")
export default class SerializedDateTimeTimezoneLocation {
	@JsonProperty("country_code", String)
	private country_code: string = undefined;

	@JsonProperty("latitude", Number)
	private latitude: number = undefined;

	@JsonProperty("longitude", Number)
	private longitude: number = undefined;

	@JsonProperty("comments", String)
	private comments: string = undefined;

	public getCountryCode(): string {
		return this.country_code;
	}

	public getLatitude(): number {
		return this.latitude;
	}

	public getLongitude(): number {
		return this.longitude;
	}

	public getComments(): string {
		return this.comments;
	}
}