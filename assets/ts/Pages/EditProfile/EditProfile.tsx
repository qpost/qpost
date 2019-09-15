/*
 * Copyright (C) 2018-2019 Gigadrive - All rights reserved.
 * https://gigadrivegroup.com
 * https://qpo.st
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
import ContentBase from "../../Component/Layout/ContentBase";
import PageContent from "../../Component/Layout/PageContent";
import {Button, Card, Col, DatePicker, Input, Row, Upload} from "antd";
import "antd/es/button/style";
import "antd/es/input/style";
import "antd/es/form/style";
import "antd/es/date-picker/style";
import "antd/es/card/style";
import Auth from "../../Auth/Auth";
import TextArea from "antd/es/input/TextArea";
import moment from "moment";
import {RcFile, UploadChangeParam} from "antd/es/upload";
import "antd/es/upload/style";
import PostFormUploadItem from "../../Component/PostForm/PostFormUploadItem";
import Icon from "antd/es/icon";
import AntMessage from "antd/es/message";
import API from "../../API/API";
import BaseObject from "../../Serialization/BaseObject";
import User from "../../Entity/Account/User";

export default class EditProfile extends Component<any, {
	displayName: string | undefined,
	bio: string | undefined,
	birthday: string | undefined,
	avatar: string | PostFormUploadItem | undefined,
	loading: boolean
}> {
	constructor(props) {
		super(props);

		const user = Auth.getCurrentUser();

		let avatar = user.getAvatarURL();
		if (avatar && avatar === "https://cdn.gigadrivegroup.com/defaultAvatar.png") {
			avatar = undefined;
		}

		this.state = {
			displayName: user.getDisplayName(),
			bio: user.getBio(),
			birthday: user.getBirthday(),
			avatar,
			loading: false
		};
	}

	uploadChange = (info: UploadChangeParam) => {
	};

	beforeUpload = (file: RcFile, FileList: RcFile[]) => {
		const size: number = file.size;
		const type: string = file.type;

		if (!(type === "image/jpeg" || type === "image/png" || type === "image/gif")) {
			AntMessage.error("Invalid file type.");
			return false;
		}

		if (!(size / 1024 / 1024 < 2)) {
			AntMessage.error("Images must be smaller than 2MB.");
			return false;
		}

		const item: PostFormUploadItem = new PostFormUploadItem();
		item.uid = file.uid;
		item.size = size;
		item.type = type;

		const reader = new FileReader();
		this.setState({loading: true});
		reader.addEventListener("load", () => {
			const result: string = typeof reader.result === "string" ? reader.result : null;
			if (result) {
				item.dataURL = result;
				item.base64 = item.dataURL.replace(/^data:image\/(png|jpg|jpeg|gif);base64,/, "");

				this.setState({avatar: item, loading: false});
			} else {
				this.setState({loading: false});
			}
		});
		reader.readAsDataURL(file);

		return false;
	};

	render(): React.ReactElement<any, string | React.JSXElementConstructor<any>> | string | number | {} | React.ReactNodeArray | React.ReactPortal | boolean | null | undefined {
		const labelCol = {xs: {span: 6, offset: 0}, md: {span: 3, offset: 0}};
		const wrapperCol = {xs: {span: 18, offset: 0}, md: {span: 21, offset: 0}};
		// const user = Auth.getCurrentUser();
		const birthday = this.state.birthday;
		const birthdayMoment = birthday ? moment(birthday) : undefined;
		let avatar = this.state.avatar;
		if (avatar instanceof PostFormUploadItem) {
			avatar = avatar.dataURL;
		}

		return <ContentBase>
			<PageContent>
				<Card>
					<Row className={"mb-3"}>
						<Col {...labelCol}>
							Display name
						</Col>

						<Col {...wrapperCol}>
							<Input value={this.state.displayName} onChange={(e) => {
								const value = e.target.value;

								this.setState({
									displayName: value
								});
							}}/>
						</Col>
					</Row>

					<Row className={"mb-3"}>
						<Col {...labelCol}>
							Bio
						</Col>

						<Col {...wrapperCol}>
							<TextArea value={this.state.bio} autosize={{minRows: 3, maxRows: 3}}
									  style={{resize: "none"}} maxLength={200} onChange={(e) => {
								const value = e.target.value;

								this.setState({
									bio: value
								});
							}}/>
						</Col>
					</Row>

					<Row className={"mb-3"}>
						<Col {...labelCol}>
							Birthday
						</Col>

						<Col {...wrapperCol}>
							<DatePicker value={birthdayMoment} onChange={(date) => {
								if (date) {
									const value = date.format("YYYY-MM-DD");

									this.setState({
										birthday: value
									});
								} else {
									this.setState({birthday: undefined});
								}
							}}/>
						</Col>
					</Row>

					<Row className={"mb-3"}>
						<Col {...labelCol}>
							Profile picture
						</Col>

						<Col {...wrapperCol}>
							<Upload
								name={"image-upload"}
								listType={"picture-card"}
								className={"uploader"}
								showUploadList={false}
								action={"https://qpo.st"}
								beforeUpload={this.beforeUpload}
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
									<div className="ant-upload-text">Upload</div>
								</div>}
							</Upload>

							{avatar ?
								<Button type={"danger"} className={"customDangerButton"} disabled={this.state.loading}
										onClick={(e) => {
											e.preventDefault();

											if (!this.state.loading) {
												this.setState({avatar: undefined});
											}
										}}>
									Delete profile picture
								</Button> : ""}
						</Col>
					</Row>

					<Row>
						<Col {...labelCol}>
							&nbsp;
						</Col>

						<Col {...wrapperCol}>
							<Button type={"primary"} loading={this.state.loading} onClick={(e) => {
								e.preventDefault();

								if (!this.state.loading) {
									const displayName = this.state.displayName;
									const bio = this.state.bio;
									const birthday = this.state.birthday;
									let avatar = this.state.avatar;

									// Validate fields
									if (displayName.length === 0) {
										AntMessage.error("Please enter a display name.");
										return;
									}

									if (displayName.length > 24) {
										AntMessage.error("The display name may not be longer than 24 characters.");
										return;
									}

									if (bio && bio.length > 200) {
										AntMessage.error("The bio may not be longer than 200 characters.");
										return;
									}

									if (birthday && !(new Date(birthday))) {
										AntMessage.error("Please enter a valid birthday.");
										return;
									}

									this.setState({loading: true});

									API.handleRequest("/user", "POST", {
										displayName,
										bio: bio || null,
										birthday: birthday || null,
										avatar: (avatar instanceof PostFormUploadItem) ? avatar.base64 : null
									}, data => {
										if (data.hasOwnProperty("result")) {
											const user = BaseObject.convertObject(User, data.result);
											Auth.setCurrentUser(user);

											AntMessage.success("Your changes have been saved.");
										} else {
											AntMessage.error("An error occurred.");
										}

										this.setState({loading: false});
									}, error => {
										AntMessage.error(error);
										this.setState({loading: false});
									});
								}
							}}>
								Save changes
							</Button>
						</Col>
					</Row>
				</Card>
			</PageContent>
		</ContentBase>;
	}
}