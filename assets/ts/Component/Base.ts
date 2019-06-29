import $ from "jquery";
import NightMode from "./NightMode";
import DismissibleAlert from "./DismissibleAlert";
import NotificationAlert from "./NotificationAlert";

export default class Base {
	private static bound = false;

	private static taskRunning: boolean = false;

	public static init(): void {
		Base.startTask();

		if (!this.bound) {
			window["loadBasic"] = Base.init;
			this.bound = true;
		}

		$('[data-toggle="tooltip"]').tooltip({
			trigger: "hover"
		});

		$('[data-toggle="popover"]').popover({
			trigger: "focus",
			html: true
		});

		$("time.timeago").timeago();

		window.addEventListener("load", function () {
			window["cookieconsent"].initialise({
				"palette": {
					"popup": {
						"background": "#237afc"
					},
					"button": {
						"background": "#fff",
						"text": "#237afc"
					}
				},
				"content": {
					"href": "https://gigadrivegroup.com/legal/privacy-policy"
				}
			})
		});

		NightMode.init();
		NotificationAlert.init();

		$(".datepicker").datepicker();

		$(".birthdayDatepicker").datepicker({
			endDate: new Date(new Date().setFullYear(new Date().getFullYear() - 13))
		});

		$(".convertEmoji").html(function () {
			return window["twemoji"].parse($(this).html());
		}).removeClass("convertEmoji");

		DismissibleAlert.init();
	}

	private static startTask(): void {
		if (!Base.taskRunning) {
			Base.taskRunning = true;

			Base.task();
		}
	}

	private static task(): void {
		Base.init();

		setTimeout(Base.task, 500);
	}
}