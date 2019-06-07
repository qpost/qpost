import $ from "jquery";
import Util from "../Util";
import Base from "./Base";

export default class HomeFeed {
	public static init(): void {
		this.loadNew();

		$(document).on("click", ".homeFeedLoadMore", function (e) {
			const $this = $(e.currentTarget);
			const oldHtml = $this.html();

			$this.html('<i class="fas fa-spinner fa-pulse"></i>');

			$.ajax({
				url: "/scripts/extendHomeFeed",
				data: {
					csrf_token: Util.csrfToken(),
					mode: "loadOld",
					firstPost: window["HOME_FEED_FIRST_POST"]
				},
				method: "POST",

				success: function (result) {
					let json = result;

					if (json.hasOwnProperty("result")) {
						let newHtml = "";
						let adcount = 10;

						if (json.result.length > 0) {
							let i;
							for (i = 0; i < json.result.length; i++) {
								let post = json.result[i];

								let postId = post.id;

								if (i == json.result.length - 1) {
									window["HOME_FEED_FIRST_POST"] = postId;
								}

								newHtml = newHtml.concat(post.listHtml);

								adcount--;
								if (adcount == 0) {
									newHtml = newHtml.concat('<div class="my-3 text-center"><div class="advertisment leaderboard"></div></div>');
									adcount = 10;
								}
							}

							if ($(".feedEntry").length) {
								$(".feedContainer").append(newHtml);
							} else {
								$(".feedContainer").html(newHtml);
							}

							$(".homeFeedLoadMore").html(oldHtml);

							Base.init();
						} else {
							$(".homeFeedLoadMore").html('<b>Oops!</b><br/>It seems there is nothing else to load for your home feed.');
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
		});
	}

	private static loadNew(): void {
		if (typeof window["HOME_FEED_LAST_POST"] == "undefined") {
			setTimeout(this.loadNew, 5000);
			return;
		}

		const _this = this;

		if ($(".homePostField").length) {
			$.ajax({
				url: "/scripts/extendHomeFeed",
				data: {
					csrf_token: Util.csrfToken(),
					mode: "loadNew",
					lastPost: window["HOME_FEED_LAST_POST"]
				},
				method: "POST",

				success: function (result) {
					let json = result;

					if (json.hasOwnProperty("result")) {
						let newHtml = "";

						let a = true;

						json.result.forEach(post => {
							let postId = post.id;

							if (a == true) {
								window["HOME_FEED_LAST_POST"] = postId;
								a = false;
							}

							newHtml = newHtml.concat(post.listHtml);
						});

						if ($(".feedEntry").length) {
							$(".feedContainer").prepend(newHtml);
						} else {
							$(".feedContainer").html(newHtml);
						}

						Base.init();

						setTimeout(_this.loadNew, 5000);
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
		} else {
			setTimeout(_this.loadNew, 5000);
		}
	}
}