import $ from "jquery";

export default class Paste {
	public static init(): void {
		// allow pasting copied images in post form
		document.onpaste = function (event) {
			if (event.target) {
				if ($(event.target).hasClass("postField")) {
					const target = $(event.target);

					const select = target.parent().parent().find(".dropzone-previews");
					const previews = select && select.length ? select[0] : null;

					const items = (event.clipboardData || event["originalEvent"].clipboardData).items;

					for (let index in items) {
						const item = items[index];

						if (item.kind === "file") {
							const blob = item.getAsFile();

							if (window["dz"] && $(window["dz"].previewsContainer).length && $(window["dz"].previewsContainer)[0] == previews) { // verify it's being pasted in the proper textarea
								window["dz"].addFile(blob);
							}
						}
					}
				}
			}
		};
	}
}