import $ from "jquery";
import DataModal from "./DataModal";
import Util from "../../Util";
import Base from "../Base";

export default class StatusModal implements DataModal {
	constructor() {
		$(document).on("hidden.bs.modal", "#statusModal", function (e) {
			if (typeof window["restoreUrl"] !== "undefined" && typeof window["restoreTitle"] !== "undefined") {
				history.pushState("", window["restoreTitle"], window["restoreUrl"]);
				document.title = window["restoreTitle"];

				window["restoreUrl"] = "";
				window["restoreTitle"] = "";
				window["CURRENT_STATUS_MODAL"] = 0;
			}
		});

		window["showStatusModal"] = this.show;
	}

	close(): void {
		$("#statusModal").modal("hide");
	}

	isOpen(): boolean {
		return $("#statusModal").hasClass("show");
	}

	reset(): void {
		$("#statusModal").html(
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

	show(postId: number): void {
		this.reset();

		let statusModal = $("#statusModal");

		if (window["restoreUrl"] == null || window["restoreUrl"] == "") window["restoreUrl"] = window.location.pathname;
		if (window["restoreTitle"] == null || window["restoreTitle"] == "") window["restoreTitle"] = $(document).find("title").text();

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
					window["CURRENT_STATUS_MODAL"] = json.id;
					let user = json.user;
					let content = "";

					let replies = json.replies;

					let d = json;
					let hasParent = false;

					let echoParentList = false;

					if (d.hasOwnProperty("parent") && d.parent != null) {
						content = content.concat('<ul class="list-group parents">');
						echoParentList = true;
					}

					while (d.hasOwnProperty("parent") && d.parent != null) {
						d = d.parent;
						hasParent = true;

						content = d.listHtml.concat(content);
						/*'<div class="card feedEntry my-2 statusTrigger" data-status-render="' + d.id + '" data-entry-id="' + d.id + '">' +
						'<div class="py-1 px-3">' +
						'<div class="row">' +
						'<div class="float-left">' +
						'<a href="/' + d.user.username + '" class="clearUnderline ignoreParentClick">' +
						'<img class="rounded mx-1 my-1" src="' + d.user.avatar + '" width="36" height="36"/>' +
						'</a>' +
						'</div>' +

						'<div class="float-left ml-1" style="max-width: 414px;">' +
						'<p class="mb-0 small">' +
						'<a href="/' + d.user.username + '" class="clearUnderline ignoreParentClick">' +
						'<span class="font-weight-bold">' + d.user.displayName + '</span>' +
						'</a>' +

						' <span class="text-muted font-weight-normal">@' + d.user.username + '</span> ' +

						'&bull; ' +

						d.time +
						'</p>' +

						'<p class="mb-0">' +
						twemoji.parse(d.text) +
						'</p>' +

						d.postActionButtons +
						'</div>' +
						'</div>' +
						'</div>' +
						'</div>'
						.concat(content);*/
					}

					if (echoParentList) {
						content = content.concat('</ul>');
					}

					if (hasParent == true)
						content = content.concat("<hr/>");

					content = content.concat('<div class="mb-4">');

					content = content.concat('<button type="button" class="close float-right" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button><br/>');

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
						'<div><a href="/' + user.username + '" class="clearUnderline font-weight-bold mb-0" style="font-size:20px; word-wrap: break-word;">' +
						window["twemoji"].parse(user.displayName) + (user.verified === true ? user.verifiedIcon : "") +
						'</a></div>'
					);

					content = content.concat(
						'<div class="text-muted" style="margin-top: -6px; word-wrap: break-word;">' +
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
						'</p>'
					);

					if (json.hasOwnProperty("attachments") && json.attachments.length > 0) {
						content = content.concat('<div class="mb-4 mt-2">');

						content = content.concat(json.attachmentHtml);

						content = content.concat('</div>');
					}

					content = content.concat(
						'<p class="small text-muted"><i class="far fa-clock"></i> Posted ' +
						json.time +
						' | #' + json.id + '</p>' +
						'</div>'
					);

					content = content.concat('<hr/>');

					content = content.concat(json.postActionButtons);

					if (typeof window["CURRENT_USER"] !== "undefined" && json.hasOwnProperty("postForm")) {
						content = content.concat(json.postForm);
					}

					content = content.concat('<ul class="list-group replies">');

					if (replies.length > 0) {
						replies.forEach(reply => {
							content = content.concat(reply.listHtml);
							/*content = content.concat(
								'<div class="card feedEntry my-2 statusTrigger" data-status-render="' + reply.id + '" data-entry-id="' + reply.id + '">' +
								'<div class="py-1 px-3">' +
								'<div class="row">' +
								'<div class="float-left">' +
								'<a href="/' + reply.user.username + '" class="clearUnderline ignoreParentClick">' +
								'<img class="rounded mx-1 my-1" src="' + reply.user.avatar + '" width="36" height="36"/>' +
								'</a>' +
								'</div>' +

								'<div class="float-left ml-1" style="max-width: 414px;">' +
								'<p class="mb-0 small">' +
								'<a href="/' + reply.user.username + '" class="clearUnderline ignoreParentClick">' +
								'<span class="font-weight-bold">' + reply.user.displayName + '</span>' +
								'</a>' +

								' <span class="text-muted font-weight-normal">@' + reply.user.username + '</span> ' +

								'&bull; ' +

								reply.time +
								'</p>' +

								'<p class="mb-0">' +
								twemoji.parse(reply.text) +
								'</p>' +

								reply.postActionButtons +
								'</div>' +
								'</div>' +
								'</div>' +
								'</div>'
							);*/
						});
					}

					content = content.concat('</ul>');

					statusModal.html(
						'<div class="modal-dialog" role="document">' +
						'<div class="modal-content">' +
						'<div class="modal-body">' +
						content +
						'</div>' +
						'</div>' +
						'</div>'
					);

					Base.init();

					let title = user.displayName + " on qpost: \"" + Util.limitString(json.textUnfiltered, 34, true) + "\"";

					history.pushState({postId: postId}, title, "/status/" + postId);
					document.title = title;

					if (!_this.isOpen())
						statusModal.modal();
				} else if (json.hasOwnProperty("error")) {
					statusModal.html(
						'<div class="modal-dialog" role="document">' +
						'<div class="modal-content">' +
						'<div class="modal-body">' +
						json.error +
						'</div>' +
						'</div>' +
						'</div>'
					);

					if (!_this.isOpen())
						statusModal.modal();
				} else {
					statusModal.html(
						'<div class="modal-dialog" role="document">' +
						'<div class="modal-content">' +
						'<div class="modal-body">' +
						json +
						'</div>' +
						'</div>' +
						'</div>'
					);

					if (!_this.isOpen())
						statusModal.modal();
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