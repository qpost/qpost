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
import FeedEntry from "../../Entity/Feed/FeedEntry";
import Tooltip from "antd/es/tooltip";
import "antd/es/tooltip/style";
import Button from "antd/es/button";
import "antd/es/button/style";
import Input from "antd/es/input";
import "antd/es/input/style";
import Modal from "antd/es/modal";
import "antd/es/modal/style";
import AntMessage from "antd/es/message";
import WindowSizeListener from "react-window-size-listener";
import DummyPostForm from "./DummyPostForm";
import $ from "jquery";
import API from "../../API/API";
import BaseObject from "../../Serialization/BaseObject";
import FeedEntryList from "../FeedEntry/FeedEntryList";
import Spin from "antd/es/spin";

export default class PostForm extends Component<{
	onClose?: () => void,
	replyTo?: FeedEntry,
	visible: boolean,
	parent: DummyPostForm
}, {
	mobile: boolean,
	message: string | null,
	posting: boolean
}> {
	constructor(props) {
		super(props);

		this.state = {
			mobile: window.innerWidth <= 768,
			message: null,
			posting: false
		};
	}

	readyToPost(): boolean {
		return this.state.posting === false && this.state.message != null && this.state.message.length >= 1 && this.state.message.length <= 300;
	}

	isReply: () => boolean = () => {
		return !!this.props.replyTo;
	};

	setMobile = (windowWidth: number) => {
		const mobile = windowWidth <= 768;

		if (this.state.mobile !== mobile) {
			this.setState({
				mobile
			});
		}
	};

	close = () => {
		if (this.props.onClose !== null) {
			this.props.onClose.call(this.props.parent);
		}
	};

	send = (e) => {
		e.preventDefault();

		if (this.readyToPost()) {
			const message: string = this.state.message;

			this.setState({
				posting: true
			});

			API.handleRequest("/post", "POST", {
				message
			}, data => {
				if (data.hasOwnProperty("post")) {
					const post: FeedEntry = BaseObject.convertObject(FeedEntry, data["post"]);
					const entryList: FeedEntryList | null = FeedEntryList.instance;

					AntMessage.success("Your post has been sent.");

					if (entryList) {
						entryList.prependEntry(post);
					}

					this.close();
				} else {
					AntMessage.error("An error occurred.");
				}
			}, error => {
				AntMessage.error(error);
			});
		}
	};

	addPhoto = (e) => {
		e.preventDefault();

		// TODO
	};

	change = (e) => {
		const value = e.target.value.length > 0 ? e.target.value : null;

		this.setState({
			message: value
		});
	};

	content = () => {
		const used: number = this.state.message === null ? 0 : this.state.message.length;

		return <div className={"postForm"}>
			{this.state.posting === false ? <div>
				<div className={"clearfix"}>
					<Button type={"link"} onClick={this.close} className={"float-left"} style={{fontSize: "20px"}}>
						<i className="fas fa-times"/>
					</Button>

					<Button type={"primary"} onClick={(e) => {
						this.send(e);
					}} className={"float-right"}>
						Send
					</Button>
				</div>
				<hr/>
				<Input.TextArea rows={3} style={{resize: "none"}} id={"postFormTextarea"}
								placeholder={"Post something for your followers!"} onChange={(e) => this.change(e)}
								value={this.state.message}/>

				<div className={"clearfix bottom"}>
					<div className={"actionButtons"}>
						<Tooltip placement={"top"} title={"Add photos"}>
							<Button type={"link"} onClick={this.addPhoto} className={"actionButton"}>
								<i className="fas fa-images"/>
							</Button>
						</Tooltip>
					</div>

					<div className={"characterCount"}>
						{300 - used}
					</div>
				</div>
			</div> : <div className={"text-center my-3"}>
				<Spin size={"large"}/>
			</div>}
		</div>;
	};

	render(): React.ReactElement<any, string | React.JSXElementConstructor<any>> | string | number | {} | React.ReactNodeArray | React.ReactPortal | boolean | null | undefined {
		this.setMobile(window.innerWidth);

		if (this.props.visible) {
			$("body").addClass("disableScroll");

			setTimeout(() => {
				$("#postFormTextarea").focus();
			}, 200);

			return <div>
				{this.state.mobile ? <div key={0} className={"postFormBackdrop"}>
					{this.content()}
				</div> : ""}

				{!this.state.mobile ? <Modal
					key={1}
					title={null}
					footer={null}
					visible={this.props.visible}
					className={"desktopOnly"}
					closable={false}
					onCancel={this.close}>
					{this.content()}
				</Modal> : ""}

				<WindowSizeListener onResize={windowSize => {
					this.setMobile(windowSize.windowWidth);
				}}/>
			</div>;
		} else {
			$("body").removeClass("disableScroll");

			return "";
		}
	}
}