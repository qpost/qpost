import $ from "jquery";
import Util from "../../Util";

export default class TextButton {
	public static init(): void {
		$(document).on("click", ".postFormTextButton", function (e) {
			e.preventDefault();

			let postBox = $(this).parent().parent().parent().parent();

			let videoButton = $(this).parent().parent().find(".postFormVideoButton");
			let linkButton = $(this).parent().parent().find(".postFormLinkButton");

			let linkURLBox = postBox.find(".linkURL");
			let videoURLBox = postBox.find(".videoURL");

			let dropzonePreviews = postBox.find(".dropzone-previews");
			let addPhotoButton = postBox.find(".addPhoto");

			if (!Util.hasAttr(this, "disabled")) {
				$(this).prop("disabled", true);
				if (Util.hasAttr(videoButton.get(0), "disabled")) videoButton.removeAttr("disabled");
				if (Util.hasAttr(linkButton.get(0), "disabled")) linkButton.removeAttr("disabled");

				if (linkURLBox.length > 0 && !linkURLBox.hasClass("d-none")) linkURLBox.addClass("d-none");
				if (videoURLBox.length > 0 && !videoURLBox.hasClass("d-none")) videoURLBox.addClass("d-none");

				if (dropzonePreviews.length > 0 && dropzonePreviews.hasClass("d-none")) dropzonePreviews.removeClass("d-none");
				if (addPhotoButton.length > 0 && addPhotoButton.hasClass("d-none")) addPhotoButton.removeClass("d-none");
			}
		});
	}
}