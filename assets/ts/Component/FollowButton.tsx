import $ from "jquery";
import Util from "../Util";

export default class FollowButton {
	public static init(): void {
		$(document).on("click", ".followButton[data-user-id],.unfollowButton[data-user-id],.pendingButton[data-user-id]", (e) => {
			let token = Util.csrfToken();
			const $this = $(e.currentTarget);

			if (!~$this.html().indexOf("fas fa-spinner")) {
				$this.html(<i class="fas fa-spinner fa-pulse"/>);

				if (Util.hasAttr(e.currentTarget, "data-user-id")) {
					const userID = $this.attr("data-user-id");

					if (userID) {
						$.ajax({
							url: "/scripts/toggleFollow",
							data: {
								csrf_token: token,
								user: userID
							},
							method: "POST",

							success: function (result) {
								let json = result;

								if (json.hasOwnProperty("followStatus")) {
									if (json.followStatus == 1) {
										$this.addClass("unfollowButton");
										$this.addClass("btn-danger");

										$this.removeClass("pendingButton");
										$this.removeClass("btn-warning");

										$this.removeClass("followButton");
										$this.removeClass("btn-primary");

										$this.html("Unfollow");
									} else if (json.followStatus == 0) {
										$this.removeClass("unfollowButton");
										$this.removeClass("btn-danger");

										$this.removeClass("pendingButton");
										$this.removeClass("btn-warning");

										$this.addClass("followButton");
										$this.addClass("btn-primary");

										$this.html("Follow");
									} else if (json.followStatus == 2) {
										$this.removeClass("unfollowButton");
										$this.removeClass("btn-danger");

										$this.addClass("pendingButton");
										$this.addClass("btn-warning");

										$this.removeClass("followButton");
										$this.removeClass("btn-primary");

										$this.html("Pending");
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
				}
			}
		});
	}
}