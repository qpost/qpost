import $ from "jquery";

export default class Utility {
	public static init(): void {
		$(document).on("click", ".ignoreParentClick", function (e) {
			e.stopPropagation();
		});
	}
}