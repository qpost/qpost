export default class SerializedDateTimeTimezoneTransition {
	private ts: number;
	private time: string;
	private offset: number;
	private isdst: boolean;
	private abbr: string;

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