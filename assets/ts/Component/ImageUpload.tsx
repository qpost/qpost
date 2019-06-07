import $ from "jquery";
import Dropzone from "dropzone";
import Util from "../Util";

export default class ImageUpload {
	public static init(): void {
		window["dz"] = null;

		setTimeout(() => {
			let postBox = $(".dropzone-previews").closest(".postBox");
			let attachmentValueField = postBox.find("input[name=\"attachmentData\"]");
			let postButton = postBox.find(".postButton");

			if ($(".dropzone-previews").length == 0) return;

			window["dz"] = new Dropzone(document.body, {
				url: "/scripts/mediaUpload",
				paramName: "file",
				maxFilesize: 10,
				maxFiles: 4,
				acceptedFiles: "image/*",
				previewsContainer: ".dropzone-previews",
				clickable: ".addPhoto",
				previewTemplate: $(".preview-template").html(),
				thumbnailWidth: 100,
				thumbnailHeight: 100,
				parallelUploads: 4,
				accept: (file, done) => {
					return done();
				},
				thumbnail: function thumbnail(file, dataUrl) {
					if (file.previewElement) {
						file.previewElement.classList.remove("dz-file-preview");
						for (var _iterator6 = file.previewElement.querySelectorAll("[data-dz-thumbnail]"), _isArray6 = true, _i6 = 0, _iterator6: NodeListOf<Element> = _isArray6 ? _iterator6 : _iterator6[Symbol.iterator](); ;) {
							var _ref5;

							if (_isArray6) {
								if (_i6 >= _iterator6.length) break;
								_ref5 = _iterator6[_i6++];
							} else {
								_i6 = _iterator6["next"]();
								if (_i6["done"]) break;
								_ref5 = _i6["value"];
							}

							var thumbnailElement = _ref5;

							/*thumbnailElement.alt = file.name;
							thumbnailElement.src = dataUrl;*/
							thumbnailElement.style.backgroundImage = "url('" + dataUrl + "')";
						}

						return setTimeout(function () {
							return file.previewElement.classList.add("dz-image-preview");
						}, 1);
					}

					return "";
				},
				addedfile: function addedfile(file) {
					if (window["dz"].getAcceptedFiles().length >= 4) return;

					var _this2 = this;

					if (this["element"] === this.previewsContainer) {
						this["element"].classList.add("dz-started");
					}

					if (this.previewsContainer) {
						file.previewElement = Dropzone.createElement(this["options"].previewTemplate.trim());
						file.previewTemplate = file.previewElement; // Backwards compatibility

						this.previewsContainer["appendChild"](file.previewElement);
						for (var _iterator3 = file.previewElement.querySelectorAll("[data-dz-name]"), _isArray3 = true, _i3 = 0, _iterator3: NodeListOf<Element> = _isArray3 ? _iterator3 : _iterator3[Symbol.iterator](); ;) {
							var _ref3;

							if (_isArray3) {
								if (_i3 >= _iterator3.length) break;
								_ref3 = _iterator3[_i3++];
							} else {
								_i3 = _iterator3["next"]();
								if (_i3["done"]) break;
								_ref3 = _i3["value"];
							}

							var node = _ref3;

							node.textContent = file.name;
						}
						for (var _iterator4 = file.previewElement.querySelectorAll("[data-dz-size]"), _isArray4 = true, _i4 = 0, _iterator4: NodeListOf<Element> = _isArray4 ? _iterator4 : _iterator4[Symbol.iterator](); ;) {
							if (_isArray4) {
								if (_i4 >= _iterator4.length) break;
								node = _iterator4[_i4++];
							} else {
								_i4 = _iterator4["next"]();
								if (_i4["done"]) break;
								node = _i4["value"];
							}

							node.innerHTML = this["filesize"](file.size);
						}

						if (this["options"].addRemoveLinks) {
							file["_removeLink"] = Dropzone.createElement("<a class=\"dz-remove\" href=\"javascript:undefined;\" data-dz-remove>" + this["options"].dictRemoveFile + "</a>");
							file.previewElement.appendChild(file["_removeLink"]);
						}

						var removeFileEvent = function removeFileEvent(e) {
							e.preventDefault();
							e.stopPropagation();
							if (file.status === Dropzone.UPLOADING) {
								return Dropzone.confirm(_this2["options"].dictCancelUploadConfirmation, function () {
									return _this2["removeFile"](file);
								});
							} else {
								if (_this2["options"].dictRemoveFileConfirmation) {
									return Dropzone.confirm(_this2["options"].dictRemoveFileConfirmation, function () {
										return _this2["removeFile"](file);
									});
								} else {
									return _this2["removeFile"](file);
								}
							}
						};

						for (var _iterator5 = file.previewElement.querySelectorAll("[data-dz-remove]"), _isArray5 = true, _i5 = 0, _iterator5: NodeListOf<Element> = _isArray5 ? _iterator5 : _iterator5[Symbol.iterator](); ;) {
							var _ref4;

							if (_isArray5) {
								if (_i5 >= _iterator5.length) break;
								_ref4 = _iterator5[_i5++];
							} else {
								_i5 = _iterator5["next"]();
								if (_i5["done"]) break;
								_ref4 = _i5["value"];
							}

							var removeLink = _ref4;

							removeLink.addEventListener("click", removeFileEvent);
						}
					}
				}
			});

			window["dz"].on("sending", (file, xhr, formData) => {
				formData.append("csrf_token", Util.csrfToken());

				postButton.html('<i class="fas fa-spinner fa-pulse"></i>');
			});

			window["dz"].on("success", (file, responseText, e) => {
				if (responseText.hasOwnProperty("ids")) {
					responseText.ids.forEach((id: string) => {
						let array: string[] = [];
						if (typeof attachmentValueField.val() !== typeof undefined && attachmentValueField.val() != "") {
							let b64 = atob((attachmentValueField.val() || "").toString().replace(/\s/g, ""));
							array = JSON.parse(b64);
						}

						array.push(id);

						attachmentValueField.val(btoa(JSON.stringify(array)));
					});
				}
			});

			window["dz"].on("queuecomplete", (progress) => {
				postButton.html("Post");
			});

			window["dz"].on("error", (file, message, xhr) => {
				console.error("Dropzone encountered error", message, xhr);
			});

			window["dz"].on("addedfile", (file) => {
				if (typeof file.previewElement !== "undefined") {
					let button = Dropzone.createElement("<button class=\"btn btn-danger btn-sm float-right\" style=\"margin-left: -50px; z-index: 99999; position: relative;\"><i class=\"fas fa-trash-alt\"></i></button>");

					button.addEventListener("click", (e) => {
						e.preventDefault();
						e.stopPropagation();

						if (file.status === "success") {
							let index = window["dz"].getAcceptedFiles().indexOf(file);

							if (index > -1) {
								let array = [];
								if (attachmentValueField.val() != "") array = JSON.parse(atob((attachmentValueField.val() || "").toString()));

								if (array.length >= index + 1) {
									array.splice(index, 1);
								}

								attachmentValueField.val(btoa(JSON.stringify(array)));
							}
						}

						window["dz"].removeFile(file);
					});

					file.previewElement.prepend(button);

					//dz.enqueueFile(file);
				}
			});
		}, 500);
	}
}