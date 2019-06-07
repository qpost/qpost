import $ from "jquery";

export default class NSFWInfo {
	public static init(): void {
		$(document).on("click", ".nsfwInfo", (e) => {
			e.preventDefault();

			// show nsfw content
			$(e.currentTarget).addClass("d-none").parent().find(".hiddenNSFW").removeClass("d-none");
		});
	}
}