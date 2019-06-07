import $ from "jquery";
import Component from "../Component";

export default class StatusTrigger {
	public static init(): void {
		$(document).on("click", ".statusTrigger", function (e) {
			e.preventDefault();

			if (typeof $(this).attr("data-status-render") !== "undefined") {
				let postId = $(this).attr("data-status-render");

				if (postId) {
					Component.statusModal.show(Number.parseInt(postId));
				}
			}
		});
	}
}