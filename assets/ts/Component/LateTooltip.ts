import $ from "jquery";

export default class LateTooltip {
	public static init(): void {
		this.update();
	}

	public static update(): void {
		$(".latetooltip").tooltip().removeClass("latetooltip");
	}
}