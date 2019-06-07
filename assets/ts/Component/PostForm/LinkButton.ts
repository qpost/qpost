import $ from "jquery";
import Util from "../../Util";

export default class LinkButton {
	public static init(): void {
		$(document).on("click", ".postFormLinkButton", function (e) {
			e.preventDefault();

			let postBox = $(this).parent().parent().parent().parent();

			let textButton = $(this).parent().parent().find(".postFormTextButton");
			let videoButton = $(this).parent().parent().find(".postFormVideoButton");

			let linkURLBox = postBox.find(".linkURL");
			let videoURLBox = postBox.find(".videoURL");

			let dropzonePreviews = postBox.find(".dropzone-previews");
			let addPhotoButton = postBox.find(".addPhoto");

			if (!Util.hasAttr(this, "disabled")) {
				$(this).prop("disabled", true);
				if (Util.hasAttr(textButton.get(0), "disabled")) textButton.removeAttr("disabled");
				if (Util.hasAttr(videoButton.get(0), "disabled")) videoButton.removeAttr("disabled");

				if (linkURLBox.length > 0 && linkURLBox.hasClass("d-none")) linkURLBox.removeClass("d-none");
				if (videoURLBox.length > 0 && !videoURLBox.hasClass("d-none")) videoURLBox.addClass("d-none");

				if (dropzonePreviews.length > 0 && !dropzonePreviews.hasClass("d-none")) dropzonePreviews.addClass("d-none");
				if (addPhotoButton.length > 0 && !addPhotoButton.hasClass("d-none")) addPhotoButton.addClass("d-none");
			}
		});
	}
}