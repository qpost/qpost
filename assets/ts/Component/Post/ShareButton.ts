import Util from "../../Util";

export default class ShareButton {
	public static init(): void {
		$(document).on("click", ".shareButton", function (e) {
			e.preventDefault();

			let postId = $(this).attr("data-post-id");
			let containerId = $(this).attr("data-container-id");

			let sharedHtml = '<a class="nav-link text-blue" href="#"><i class="fas fa-share-alt"></i> Share</a>';
			let unsharedHtml = '<a class="nav-link" style="color: ' + window["GRAYVAR"] + '" href="#"><i class="fas fa-share-alt"></i> Share</a>';
			let loadingHtml = '<a class="nav-link" style="color: ' + window["GRAYVAR"] + '" href="#"><i class="fas fa-spinner fa-pulse"></i></a>';

			let pointer = $(this);

			if (typeof postId !== "undefined" && typeof containerId !== "undefined") {
				pointer.html(loadingHtml);

				$.ajax({
					url: "/scripts/toggleShare",
					data: {
						csrf_token: Util.csrfToken(),
						post: postId
					},
					method: "POST",

					success: function (result) {
						let json = result;

						if (json.hasOwnProperty("status")) {
							let countHtml = "";

							if (json.replies > 0) {
								countHtml = countHtml.concat('<div class="float-left mr-3">');
								countHtml = countHtml.concat('<i class="fas fa-share"></i> ' + json.replies + ' repl' + (json.replies != 1 ? "ies" : "y"));
								countHtml = countHtml.concat('</div>');
							}

							if (json.shares > 0) {
								countHtml = countHtml.concat('<div class="shareCount latetooltip float-left mr-3" data-post-id="' + postId + '" data-type="shares" data-toggle="tooltip" data-html="true" title="Loading...">');
								countHtml = countHtml.concat('<i class="fas fa-share-alt"></i> ' + json.shares + ' share' + (json.shares != 1 ? "s" : ""));
								countHtml = countHtml.concat('</div>');
							}

							if (json.favorites > 0) {
								countHtml = countHtml.concat('<div class="favoriteCount latetooltip float-left mr-3" data-post-id="' + postId + '" data-type="favorites" data-toggle="tooltip" data-html="true" title="Loading...">');
								countHtml = countHtml.concat('<i class="fas fa-star"></i> ' + json.favorites + ' favorite' + (json.favorites != 1 ? "s" : ""));
								countHtml = countHtml.concat('</div>');
							}

							let countContainer = pointer.parent().parent().find("#countContainer" + containerId);

							if (countHtml != "") {
								if (!countContainer.length) {
									pointer.parent().before('<div id="countContainer' + containerId + '" class="mt-3 mb-5 small text-muted"></div>');
									countContainer = pointer.parent().parent().find("#countContainer" + containerId);
								}

								countContainer.html(countHtml);
							} else if (countContainer.length && countHtml == "") {
								countContainer.remove();
							}

							if (json.status == "Share added") {
								pointer.html(sharedHtml);
							} else {
								pointer.html(unsharedHtml);
							}
						} else {
							console.log(result);
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