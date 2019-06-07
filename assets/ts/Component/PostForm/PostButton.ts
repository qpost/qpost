import $ from "jquery";
import Util from "../../Util";
import Base from "../Base";
import ImageUpload from "../ImageUpload";

export default class PostButton {
	public static init(): void {
		const _this = this;

		$(document).on("click", ".postButton", function (e) {
			e.preventDefault();

			let postBox = $(this).parent().parent();
			let postField = postBox.find(".postField");
			let postCharacterCounter = $(this).parent().find(".postCharacterCounter");

			let textButton = postBox.find(".postFormTextButton");
			let videoButton = postBox.find(".postFormVideoButton");
			let linkButton = postBox.find(".postFormLinkButton");

			let linkURLBox = postBox.find(".linkURL");
			let videoURLBox = postBox.find(".videoURL");

			let currentMode = "TEXT";
			if (textButton.length > 0 && Util.hasAttr(textButton.get(0), "disabled")) {
				currentMode = "TEXT";
			} else if (videoButton.length > 0 && Util.hasAttr(videoButton.get(0), "disabled")) {
				currentMode = "VIDEO";
			} else if (linkButton.length > 0 && Util.hasAttr(linkButton.get(0), "disabled")) {
				currentMode = "LINK";
			}

			let linkURL;
			let videoURL;

			if (currentMode == "VIDEO" && videoURLBox.length && videoURLBox.find("input").length) {
				videoURL = (videoURLBox.find("input").val() || "").toString().trim();

				if (videoURL === "") {
					videoURL = null;
				}
			} else if (currentMode == "LINK" && linkURLBox.length && linkURLBox.find("input").length) {
				linkURL = (linkURLBox.find("input").val() || "").toString().trim();

				if (linkURL === "") {
					linkURL = null;
				}
			}

			if (linkURL != null && !Util.isValidURL(linkURL)) {
				return console.error("Invalid link URL");
			}

			if (videoURL != null && !Util.isValidURL(videoURL)) {
				return console.error("Invalid video URL");
			}

			let isReply = postBox.hasClass("replyForm");

			let text = (postField.val() || "").toString().trim();

			let replyTo = isReply && window["CURRENT_STATUS_MODAL"] > 0 ? window["CURRENT_STATUS_MODAL"] : null;

			let attachmentValueField = $(this).closest(".postBox").find("input[name=\"attachmentData\"]");

			if (typeof Util.csrfToken() !== undefined && typeof Util.postCharacterLimit() !== undefined && !$(this).html().includes("<i")) {
				let token = Util.csrfToken();
				let limit = Util.postCharacterLimit();

				let oldHtml = postBox.html();

				let nsfw = false;
				if (postBox.find(".toggleNSFW").length) {
					if (postBox.find(".toggleNSFW").hasClass("text-danger")) {
						nsfw = true;
					}
				}

				let attachments = "[]";

				if (currentMode == "TEXT" && attachmentValueField.length && attachmentValueField.val() != "") {
					attachments = atob((attachmentValueField.val() || "").toString());
				}

				if (text.length <= limit) {
					if (text.length > 0 || videoURL != null || JSON.parse(attachments).length > 0) {
						if (currentMode == "TEXT" || (currentMode == "VIDEO" && videoURL == null) || currentMode == "LINK") {
							postBox.html('<div class="card-body text-center"><i class="fas fa-spinner fa-pulse"></i></div>');
							_this.handleButtonClick(postBox, postField, postCharacterCounter, linkURL, videoURL, isReply, text, replyTo, token, oldHtml, attachments, nsfw);
						} else if (currentMode == "VIDEO") {
							$.ajax({
								url: "/scripts/validateVideoURL",
								data: {
									csrf_token: token,
									videoURL: videoURL
								},
								method: "POST",

								success: function (json) {
									if (json.hasOwnProperty("status")) {
										if (json.status == "valid") {
											postBox.html('<div class="card-body text-center"><i class="fas fa-spinner fa-pulse"></i></div>');
											_this.handleButtonClick(postBox, postField, postCharacterCounter, linkURL, videoURL, isReply, text, replyTo, token, oldHtml, attachments, nsfw);
										} else {
											console.error("Invalid video URL");
										}
									} else {
										console.error(json);
									}
								},

								error: function (xhr, status, error) {
									console.log(xhr);
									console.log(status);
									console.log(error);
								}
							});
						} else {
							console.error("Invalid mode");
						}
					} else {
						console.error("Post is empty");
					}
				} else {
					console.error("Post text too long");
				}
			}
		});
	}

	private static handleButtonClick(postBox, postField, postCharacterCounter, linkURL, videoURL, isReply, text, replyTo, token, oldHtml, attachments, nsfw) {
		$.ajax({
			url: "/scripts/createPost",
			data: {
				csrf_token: token,
				text: text,
				replyTo: replyTo,
				attachments: attachments,
				linkURL: linkURL,
				videoURL: videoURL,
				nsfw: nsfw
			},
			method: "POST",

			success: function (result) {
				let json = result;

				if (json.hasOwnProperty("post")) {
					let post = json.post;

					if (post.hasOwnProperty("id") && post.hasOwnProperty("time") && post.hasOwnProperty("text") && post.hasOwnProperty("user")) {
						let postId = post.id;

						window["HOME_FEED_LAST_POST"] = postId;

						let newHtml = "";

						if (!isReply) {
							newHtml = post.listHtml;

							if ($(".feedEntry").length) {
								$(".feedContainer").prepend(newHtml);
							} else {
								$(".feedContainer").html(newHtml);
							}
						} else {
							/*newHtml =
							'<div class="card feedEntry my-2 statusTrigger" data-status-render="' + postId + '" data-entry-id="' + postId + '">' +
							'<div class="py-1 px-3">' +
							'<div class="row">' +
							'<div class="float-left">' +
							'<a href="/' + userName + '" class="clearUnderline ignoreParentClick">' +
							'<img class="rounded mx-1 my-1" src="' + userAvatar + '" width="36" height="36"/>' +
							'</a>' +
							'</div>' +

							'<div class="float-left ml-1" style="max-width: 414px;">' +
							'<p class="mb-0 small">' +
							'<a href="/' + userName + '" class="clearUnderline ignoreParentClick">' +
							'<span class="font-weight-bold">' + userDisplayName + '</span>' +
							'</a>' +

							' <span class="text-muted font-weight-normal">@' + userName + '</span> ' +

							'&bull; ' +

							postTime +
							'</p>' +

							'<p class="mb-0">' +
							twemoji.parse(postText) +
							'</p>' +

							postActionButtons +
							'</div>' +
							'</div>' +
							'</div>' +
							'</div>';*/
							newHtml = post.listHtml;

							if ($("#statusModal .replies>.list-group-item").length) {
								$("#statusModal .replies").prepend(newHtml);
							} else {
								$("#statusModal .replies").html(newHtml);
							}
						}

						postBox.html(oldHtml);
						postBox.find(".postField").focus();
						postField.val("");
						postBox.find(".postCharacterCounter").html(Util.postCharacterLimit());
						postBox.find("input[name=\"attachmentData\"]").val("");
						Base.init();
						window["dz"].destroy();
						ImageUpload.init();

						$(".dropzone-previews").html("");
					} else {
						console.log(result);
					}
				} else {
					console.log(result);
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