export default class Mobile {
	private static mobile: boolean = false;

	public static isMobile(): boolean {
		return this.mobile;
	}

	public static setMobile(mobile: boolean): void {
		this.mobile = mobile;
	}
}