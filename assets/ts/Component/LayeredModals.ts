import $ from "jquery";

export default class LayeredModals {
	public static init(): void {
		$(document).on("show.bs.modal", ".modal", (e) => {
			const zIndex = 1040 + (10 * $(".modal:visible").length);

			$(e.currentTarget).css("z-index", zIndex);

			setTimeout(() => {
				$(".modal-backdrop").not(".modal-stack").css("z-index", zIndex - 1).addClass("modal-stack");
			}, 0);
		});

		$(document).on("hidden.bs.modal", ".modal", () => {
			$(".modal:visible").length && $(document.body).addClass("modal-open");
		});
	}
}