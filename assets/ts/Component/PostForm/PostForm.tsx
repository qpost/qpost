import React, {Component} from "react";
import FeedEntry from "../../Entity/Feed/FeedEntry";
import {Button, Input, Modal, Tooltip} from "antd";
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
							placeholder={"Post something for your followers!"}/>

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