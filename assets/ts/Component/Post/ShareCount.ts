import $ from "jquery";
import Util from "../../Util";

export default class ShareCount {
	public static init(): void {
		$(document).on("shown.bs.tooltip", ".shareCount", function (e) {
			let title = $(this).attr("data-original-title");
			let postID = $(this).attr("data-post-id");

			if (title === "Loading..." && postID && !window["LOADED_SHARES"].includes(postID)) {
				window["LOADED_SHARES"].push(postID);

				let t = this;

				$.ajax({
					url: "/scripts/shareSample",
					data: {
						csrf_token: Util.csrfToken(),
						post: postID
					},
					method: "POST",

					success: function (result) {
						let json = result;

						if (json.hasOwnProperty("users") && json.hasOwnProperty("showMore") && json.hasOwnProperty("showMoreCount")) {
							if (json.users.length > 0) {
								let s = "";

								json.users.forEach(user => {
									if (s !== "") s += "<br/>";
									s += user.displayName + " (@" + user.username + ")";
								});

								if (json.showMore === true) {
									s += "<br/>and " + json.showMoreCount + " more...";
								}

								Util.updateTooltip(t, s);
							}
						}
					},

					error: function (xhr, status, error) {
						console.log(xhr);
						console.log(status);
						console.log(error);
					}
				});
			}
		});
	}
}