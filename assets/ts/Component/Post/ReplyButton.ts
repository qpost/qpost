import $ from "jquery";
import Component from "../Component";
import Util from "../../Util";

export default class ReplyButton {
	public static init(): void {
		$(document).on("click", ".replyButton", function (e) {
			e.preventDefault();

			let postId = $(this).attr("data-reply-id");

			if (Util.hasAttr(this, "data-reply-id") && postId) {
				if (window.location.pathname.endsWith(postId)) {
					$("#statusModalPostField").focus();
				} else {
					Component.statusModal.show(Number.parseInt(postId));
				}
			}
		});
	}
}