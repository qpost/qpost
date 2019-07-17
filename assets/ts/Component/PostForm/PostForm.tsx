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
import WindowSizeListener from "react-window-size-listener";
import DummyPostForm from "./DummyPostForm";
import $ from "jquery";

export default class PostForm extends Component<{
	onClose?: () => void,
	replyTo?: FeedEntry,
	visible: boolean,
	parent: DummyPostForm
}, {
	mobile: boolean
}> {
	constructor(props) {
		super(props);

		this.state = {
			mobile: window.innerWidth <= 768
		};
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

		// TODO
	};

	addPhoto = (e) => {
		e.preventDefault();

		// TODO
	};

	change = (e) => {

	};

	content = () => {
		return <div className={"postForm"}>
			<div className={"clearfix"}>
				<Button type={"link"} onClick={this.close} className={"float-left"} style={{fontSize: "20px"}}>
					<i className="fas fa-times"/>
				</Button>

				<Button type={"primary"} onClick={(e) => this.send} className={"float-right"}>
					Send
				</Button>
			</div>
			<hr/>
			<Input.TextArea rows={3} style={{resize: "none"}} id={"postFormTextarea"}
							placeholder={"Post something for your followers!"} onChange={(e) => this.change(e)}/>

			<div className={"clearfix bottom"}>
				<div className={"actionButtons"}>
					<Tooltip placement={"top"} title={"Add photos"}>
						<Button type={"link"} onClick={this.addPhoto} className={"actionButton"}>
							<i className="fas fa-images"/>
						</Button>
					</Tooltip>
				</div>

				<div className={"characterCount"}>
					300
				</div>
			</div>
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