import $ from "jquery";
import Util from "../Util";

export default class BirthdayBox {
	public static init(): void {
		let birthdayBox = $(".birthdayContainer");
		if (!birthdayBox.hasClass("init") && birthdayBox.length > 0) {
			birthdayBox.addClass("init");

			let now = new Date();
			let dateString = now.getFullYear() + "-" + ("0" + (now.getMonth() + 1)).slice(-2) + "-" + ("0" + now.getDate()).slice(-2);

			$.ajax({
				url: "/scripts/loadBirthdays",
				data: {
					csrf_token: Util.csrfToken(),
					dateString: dateString
				},
				method: "POST",

				success: function (result) {
					let json = result;

					if (json.hasOwnProperty("results") && json.hasOwnProperty("html")) {
						if (json.results > 0) {
							birthdayBox.html(json.html);
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