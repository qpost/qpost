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
import "antd/es/upload/style";
import Icon from "antd/es/icon";
import PostFormUploadItem from "../PostForm/PostFormUploadItem";
import $ from "jquery";
import {RcFile, UploadChangeParam} from "antd/es/upload";
import AntMessage from "antd/es/message";
import __ from "../../i18n/i18n";

export default class AvatarSelector extends Component<any, {
	avatar: string | PostFormUploadItem | undefined,
	avatarModified: boolean
}> {
	private static readonly inputSelector = ".form-group > input[name=\"avatar\"]";

	constructor(props) {
		super(props);

		let avatar: string | undefined = undefined;
		let loadedAvatar = $(AvatarSelector.inputSelector).val();
		if (typeof loadedAvatar === "string" && loadedAvatar !== "") {
			avatar = loadedAvatar;
		}

		this.state = {
			avatar,
			avatarModified: false
		};
	}

	uploadChange = (info: UploadChangeParam) => {
	};

	beforeAvatarUpload = (file: RcFile, FileList: RcFile[]) => {
		const size: number = file.size;
		const type: string = file.type;

		if (!(type === "image/jpeg" || type === "image/png" || type === "image/gif")) {
			AntMessage.error("Invalid file type.");
			return false;
		}

		const limit = 2;

		if (!(size / 1024 / 1024 < limit)) {
			AntMessage.error(__("Images must be smaller than %size%.", {
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
					avatar: item,
					avatarModified: true
				});

				$(AvatarSelector.inputSelector).val(item.base64);
			}
		});
		reader.readAsDataURL(file);

		return false;
	};

	render(): React.ReactElement<any, string | React.JSXElementConstructor<any>> | string | number | {} | React.ReactNodeArray | React.ReactPortal | boolean | null | undefined {
		let avatar = this.state.avatar;
		if (avatar instanceof PostFormUploadItem) {
			avatar = avatar.dataURL;
		}

		return <div>
			<Upload
				name={"image-upload"}
				listType={"picture-card"}
				className={"uploader"}
				showUploadList={false}
				action={"https://qpo.st"}
				beforeUpload={this.beforeAvatarUpload}
				onChange={this.uploadChange}
			>
				{avatar ? <div style={{
					width: "300px",
					height: "300px",
					backgroundImage: "url(\"" + avatar + "\")",
					backgroundRepeat: "no-repeat",
					backgroundPosition: "center",
					backgroundSize: "cover"
				}}/> : <div>
					<Icon type={"plus"}/>
					<div className="ant-upload-text">{__("avatarSelector.upload")}</div>
				</div>}
			</Upload>

			{avatar ?
				<Button type={"danger"} className={"customDangerButton"}
						onClick={(e) => {
							e.preventDefault();

							this.setState({
								avatar: undefined,
								avatarModified: true
							});

							$(AvatarSelector.inputSelector).val("");
						}}>
					{__("avatarSelector.delete")}
				</Button> : ""}
		</div>;
	}
}