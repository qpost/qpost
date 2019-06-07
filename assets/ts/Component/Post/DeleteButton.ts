import $ from "jquery";
import Component from "../Component";
import Util from "../../Util";

export default class DeleteButton {
	public static init(): void {
		$(document).on("click", ".deleteButton", function (e) {
			e.preventDefault();

			let postId = $(this).attr("data-post-id");

			if (Util.hasAttr(this, "data-post-id") && postId) {
				if (Component.deleteModal.isOpen())
					Component.deleteModal.close();

				Component.deleteModal.show(Number.parseInt(postId));
			} else {
				console.error("No post id found");
			}
		});

		$(document).on("click", ".finDel", function (e) {
			e.preventDefault();

			let postId = $(this).attr("data-post-id");

			if (Util.hasAttr(this, "data-post-id") && postId) {
				if (Component.deleteModal.isOpen())
					Component.deleteModal.close();

				$.ajax({
					url: "/scripts/deletePost",
					data: {
						csrf_token: Util.csrfToken(),
						post: postId
					},
					method: "POST",

					success: function (json) {
						if (json.hasOwnProperty("status")) {
							if (json.status == "done") {
								$('[data-status-render="' + postId + '"]').remove();
								$('[data-entry-id="' + postId + '"]').remove();
								$('[data-post-id="' + postId + '"]').remove();
							} else {
								console.error("Invalid status: " + json.status);
							}
						} else {
							console.log(json);
						}
					},

					error: function (xhr, status, error) {
						console.log(xhr);
						console.log(status);
						console.log(error);
					}
				});
			} else {
				console.error("No post id found");
			}
		});
	}
}