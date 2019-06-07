import $ from "jquery";
import DataModal from "./DataModal";
import Util from "../../Util";
import Base from "../Base";

export default class DeleteModal implements DataModal {
	close(): void {
		$("#deleteModal").modal("hide");
	}

	isOpen(): boolean {
		return $("#deleteModal").hasClass("show");
	}

	reset(): void {
		$("#deleteModal").html(
			<div class={"modal-dialog"} role={"document"}>
				<div class={"modal-content"}>
					<div class={"modal-body"}>
						<div class={"text-center"}>
							<i class={"fas fa-spinner fa-pulse"}></i>
						</div>
					</div>
				</div>
			</div>
		);
	}

	show(postId: number): void {
		this.reset();

		let deleteModal = $("#deleteModal");

		const _this = this;

		$.ajax({
			url: "/scripts/postInfo",
			data: {
				csrf_token: Util.csrfToken(),
				postId: postId
			},
			method: "POST",

			success: function (json) {
				if (json.hasOwnProperty("id")) {
					let user = json.user;
					let content = "";

					content = content.concat('<div class="mb-4">');

					content = content.concat(json.followButton);

					content = content.concat(
						'<div class="float-left mr-2">' +
						'<a href="/' + user.username + '" class="clearUnderline">' +
						'<img width="48" height="48" src="' + user.avatar + '" class="rounded"/>' +
						'</a>' +
						'</div>'
					);

					content = content.concat('<div class="ml-2">');

					content = content.concat(
						'<div><a href="/' + user.username + '" class="clearUnderline font-weight-bold mb-0" style="font-size:20px">' +
						user.displayName +
						'</a></div>'
					);

					content = content.concat(
						'<div class="text-muted" style="margin-top: -6px">' +
						'@' + user.username +
						'</div>'
					);

					content = content.concat('</div>');

					content = content.concat('</div>');

					let c = json.hasOwnProperty("parent") && json.parent != null ? '<div class="small text-muted">Replying to <a href="/' + json.parent.user.username + '" class="clearUnderline">@' + json.parent.user.username + '</a></div>' : "";

					content = content.concat(
						'<div class="mt-2">' +
						c +
						'<p style="font-size: 27px; word-wrap: break-word;">' +
						window["twemoji"].parse(json.text) +
						'</p>' +
						'<p class="small text-muted"><i class="far fa-clock"></i> Posted ' +
						json.time +
						'</p>' +
						'</div>'
					);

					deleteModal.html(
						'<div class="modal-dialog" role="document">' +
						'<div class="modal-content">' +
						'<div class="modal-header">' +
						'<h5 class="modal-title">Are you sure you want to delete this post?</h5>' +
						'</div>' +

						'<div class="modal-body">' +
						content +
						'</div>' +

						'<div class="modal-footer">' +
						'<button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>' +
						'<button type="button" class="finDel btn btn-danger" data-post-id="' + postId + '">Delete</button>' +
						'</div>' +
						'</div>' +
						'</div>'
					);

					Base.init();

					if (!_this.isOpen())
						deleteModal.modal();
				} else if (json.hasOwnProperty("error")) {
					deleteModal.html(
						'<div class="modal-dialog" role="document">' +
						'<div class="modal-content">' +
						'<div class="modal-body">' +
						json.error +
						'</div>' +
						'</div>' +
						'</div>'
					);

					if (!_this.isOpen())
						deleteModal.modal();
				} else {
					deleteModal.html(
						'<div class="modal-dialog" role="document">' +
						'<div class="modal-content">' +
						'<div class="modal-body">' +
						json +
						'</div>' +
						'</div>' +
						'</div>'
					);

					if (!_this.isOpen())
						deleteModal.modal();
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