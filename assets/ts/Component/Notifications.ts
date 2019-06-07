import $ from "jquery";

export default class Notifications {
	public static init(): void {
		this.checkForNotifications();
	}

	private static checkForNotifications() {
		const _this = this;

		$.ajax({
			url: "/scripts/desktopNotifications",
			method: "GET",

			success: function (result) {
				let unreadCount = result.unreadCount;
				if (unreadCount > 0) {
					$(".notificationTabMainNav").html("notifications <b>(" + unreadCount + ")</b>");
				} else {
					$(".notificationTabMainNav").html("notifications");
				}

				if (result.hasOwnProperty("notifications") && result.notifications.length > 0) {
					result.notifications.forEach(notificationData => {
						let title: string | null = null;
						let text: string | null = null;
						if (notificationData.type == "NEW_FOLLOWER") {
							title = notificationData.follower.displayName + " is now following you";
							text = notificationData.follower.bio != null ? notificationData.follower.bio : ""
						} else if (notificationData.type == "MENTION") {
							title = notificationData.follower.displayName + " mentioned you";
							text = notificationData.post.textUnfiltered;
						} else if (notificationData.type == "FAVORITE") {
							title = notificationData.follower.displayName + " favorited your post";
							text = notificationData.post.textUnfiltered;
						} else if (notificationData.type == "SHARE") {
							title = notificationData.follower.displayName + " shared your post";
							text = notificationData.post.textUnfiltered;
						} else if (notificationData.type == "REPLY") {
							title = notificationData.follower.displayName + " replied to your post";
							text = notificationData.post.textUnfiltered;
						}

						if (title != null && text != null) {
							let notification = new Notification(
								title,

								{
									body: text.replace("<br/>", "\n"),
									icon: notificationData.follower.avatar
								}
							);

							notification.onclick = (event) => {
								event.preventDefault();
								window.open("/notifications", "_blank");
							};
						}
					});
				}

				setTimeout(_this.checkForNotifications, 3000);
			},

			error: function (xhr, status, error) {
				console.log(xhr);
				console.log(status);
				console.log(error);

				setTimeout(_this.checkForNotifications, 3000);
			}
		});
	}
}