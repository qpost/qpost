import DataModal from "./DataModal";
import Util from "../../Util";
import Base from "../Base";
import ClickEvent = JQuery.ClickEvent;

export default class MediaModal implements DataModal {
	constructor() {
		$(document).on("click", ".mediaModalTrigger[data-media-id][data-post-id]", (e: ClickEvent) => {
			e.preventDefault();
			const $this = $(e.currentTarget);

			if (Util.hasAttr(e.currentTarget, "data-media-id") && Util.hasAttr(e.currentTarget, "data-post-id")) {
				const mediaId = $this.attr("data-media-id");
				const postId = $this.attr("data-post-id");

				if (mediaId && postId) {
					this.show(Number.parseInt(postId), mediaId);
				}
			}
		});
	}

	close(): void {
		$("#mediaModal").modal("hide");
	}

	isOpen(): boolean {
		return $("#mediaModal").hasClass("show");
	}

	reset(): void {
		$("#mediaModal").html(
			'<div class="modal-dialog" role="document">' +
			'<div class="modal-content">' +
			'<div class="modal-body">' +
			'<div class="text-center">' +
			'<i class="fas fa-spinner fa-pulse"></i>' +
			'</div>' +
			'</div>' +
			'</div>' +
			'</div>'
		);
	}

	show(postId: number, mediaId: string): void {
		this.reset();

		let mediaModal = $("#mediaModal");
		const _this = this;

		$.ajax({
			url: "/scripts/mediaInfo",
			data: {
				csrf_token: Util.csrfToken(),
				postId: postId,
				mediaId: mediaId
			},
			method: "POST",

			success: function (json) {
				if (json.hasOwnProperty("post") && json.hasOwnProperty("attachment")) {
					let attachment = json.attachment;

					let content = "";

					content = content.concat(
						'<img src="' + attachment.fileUrl + '" style="max-width: 100%; max-height: 700px; width: auto; height: auto;"/>'
					);

					mediaModal.html(
						'<div class="modal-dialog modal-lg" role="document">' +
						'<div class="modal-content">' +
						'<div class="d-inline-block text-center bg-dark">' +

						content +

						'</div>' +
						'</div>' +
						'</div>'
					);

					Base.init();

					if (!_this.isOpen())
						mediaModal.modal();
				} else if (json.hasOwnProperty("error")) {
					mediaModal.html(
						'<div class="modal-dialog" role="document">' +
						'<div class="modal-content">' +
						'<div class="modal-body">' +
						json.error +
						'</div>' +
						'</div>' +
						'</div>'
					);

					if (!_this.isOpen())
						mediaModal.modal();
				} else {
					mediaModal.html(
						'<div class="modal-dialog" role="document">' +
						'<div class="modal-content">' +
						'<div class="modal-body">' +
						json +
						'</div>' +
						'</div>' +
						'</div>'
					);

					if (!_this.isOpen())
						mediaModal.modal();
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