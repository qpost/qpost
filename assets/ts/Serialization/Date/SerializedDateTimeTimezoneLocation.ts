export default class SerializedDateTimeTimezoneLocation {
	private country_code: string;
	private latitude: number;
	private longitude: number;
	private comments: string;

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