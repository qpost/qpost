/*
 * Copyright (C) 2018-2020 Gigadrive - All rights reserved.
 * https://gigadrivegroup.com
 * https://qpostapp.com
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://gnu.org/licenses/>
 */

import React, {Component} from "react";
import {Button, Upload} from "antd";
import Icon from "antd/es/icon";
import PostFormUploadItem from "../PostForm/PostFormUploadItem";
import $ from "jquery";
import {RcFile, UploadChangeParam} from "antd/es/upload";
import AntMessage from "antd/es/message";
import __ from "../../i18n/i18n";

export default class HeaderSelector extends Component<any, {
	header: string | PostFormUploadItem | undefined,
	headerModified: boolean
}> {
	private static readonly inputSelector = ".form-group > input[name=\"header\"]";

	constructor(props) {
		super(props);

		let header: string | undefined = undefined;
		let loadedHeader = $(HeaderSelector.inputSelector).val();
		if (typeof loadedHeader === "string" && loadedHeader !== "") {
			header = loadedHeader;
		}

		this.state = {
			header,
			headerModified: false
		};
	}

	uploadChange = (info: UploadChangeParam) => {
	};

	beforeHeaderUpload = (file: RcFile, FileList: RcFile[]) => {
		const size: number = file.size;
		const type: string = file.type;

		if (!(type === "image/jpeg" || type === "image/png" || type === "image/gif")) {
			AntMessage.error("Invalid file type.");
			return false;
		}

		const limit = 5;

		if (!(size / 1024 / 1024 < limit)) {
			AntMessage.error(__("postForm.imageTooBig", {
				"%size%": limit + "MB"
			}));
			return false;
		}

		const item: PostFormUploadItem = new PostFormUploadItem();
		item.uid = file.uid;
		item.size = size;
		item.type = type;

		const reader = new FileReader();
		reader.addEventListener("load", () => {
			const result: string = typeof reader.result === "string" ? reader.result : null;
			if (result) {
				item.dataURL = result;
				item.base64 = item.dataURL.replace(/^data:image\/(png|jpg|jpeg|gif);base64,/, "");

				this.setState({
					header: item,
					headerModified: true
				});

				$(HeaderSelector.inputSelector).val(item.base64);
			}
		});
		reader.readAsDataURL(file);

		return false;
	};

	render(): React.ReactElement<any, string | React.JSXElementConstructor<any>> | string | number | {} | React.ReactNodeArray | React.ReactPortal | boolean | null | undefined {
		let header = this.state.header;
		if (header instanceof PostFormUploadItem) {
			header = header.dataURL;
		}

		return <div>
			<Upload
				name={"image-upload"}
				listType={"picture-card"}
				className={"uploader"}
				showUploadList={false}
				action={"https://qpo.st"}
				beforeUpload={this.beforeHeaderUpload}
				onChange={this.uploadChange}
			>
				{header ? <div style={{
					width: "500px",
					height: "167px",
					backgroundImage: "url(\"" + header + "\")",
					backgroundRepeat: "no-repeat",
					backgroundPosition: "center",
					backgroundSize: "cover"
				}}/> : <div>
					<Icon type={"plus"}/>
					<div className="ant-upload-text">{__("avatarSelector.upload")}</div>
				</div>}
			</Upload>

			{header ?
				<Button type={"danger"} className={"customDangerButton"}
						onClick={(e) => {
							e.preventDefault();

							this.setState({
								header: undefined,
								headerModified: true
							});

							$(HeaderSelector.inputSelector).val("");
						}}>
					{__("headerSelector.delete")}
				</Button> : ""}
		</div>;
	}
}