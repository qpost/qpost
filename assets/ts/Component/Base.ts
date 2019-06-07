import $ from "jquery";

export default class Base {
	private static bound = false;

	public static init(): void {
		if (!this.bound) {
			window["loadBasic"] = this.init;
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

		$(".datepicker").datepicker();

		$(".birthdayDatepicker").datepicker({
			endDate: new Date(new Date().setFullYear(new Date().getFullYear() - 13))
		});

		$(".convertEmoji").html(function () {
			return window["twemoji"].parse($(this).html());
		}).removeClass("convertEmoji");
	}
}