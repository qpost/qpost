import ClickEvent = JQuery.ClickEvent;
import $ from "jquery";
import Util from "../Util";

export default class DismissibleAlert {
	public static init(): void {
		const idPrefix = "registeredalert";

		$(".alert-dismissible[id^='" + idPrefix + "'] button.close").on("click", (e: ClickEvent) => {
			let id = $(e.currentTarget).closest(".alert").attr("id");

			console.log(id);

			if (id) {
				id = id.substr(idPrefix.length);

				Util.setCookie("registeredAlert" + id, "closed", 30);
			}
		});
	}
}