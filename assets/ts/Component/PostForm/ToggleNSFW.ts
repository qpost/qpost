import $ from "jquery";
import Util from "../../Util";

export default class ToggleNSFW {
	public static init(): void {
		$(document).on("click", ".toggleNSFW", (e) => {
			e.preventDefault();

			if ($(this).hasClass("text-success")) {
				// toggle on
				$(this).removeClass("text-success").addClass("text-danger");
				Util.updateTooltip(e.currentTarget, "NSFW: on");
			} else {
				// toggle off
				$(this).removeClass("text-danger").addClass("text-success");
				Util.updateTooltip(e.currentTarget, "NSFW: off");
			}
		});
	}
}