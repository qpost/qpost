import Util from "../Util";

export default class NotificationAlert {
	public static init(): void {
		let pointer = ".notificationPermissionAlert";

		if ($(pointer).length > 0) {
			if (!Util.hasNotificationPermissions() && (!Util.hasCookie("ignoreNotificationAlert") || Util.getCookie("ignoreNotificationAlert") != "true")) {
				$(pointer).removeClass("d-none");
			}
		}

		$(document).on("click", ".hideNotifications", function (e) {
			e.preventDefault();

			$(".notificationPermissionAlert").addClass("d-none");
			Util.setCookie("ignoreNotificationAlert", "true", 7);
		});

		$(document).on("click", ".enableNotifications", function (e) {
			e.preventDefault();

			$(".notificationPermissionAlert").addClass("d-none");
			Notification.requestPermission();
		});
	}
}