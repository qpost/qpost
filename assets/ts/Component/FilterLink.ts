import $ from "jquery";

export default class FilterLink {
	public static init(): void {
		$(document).on("click", "a.filterLink", (e) => {
			const $this = $(e.currentTarget);

			if (typeof $this.attr("href") !== "undefined") {
				let link = $this.attr("href");

				if (link) {
					e.preventDefault();

					window.location.href = "/out?link=" + encodeURI(link);
				}
			}
		});
	}
}