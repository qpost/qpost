import React, {Component} from "react";
import Auth from "../../Auth/Auth";
import {Button, Card, Input} from "antd";
import PostForm from "./PostForm";

export default class DummyPostForm extends Component<any, {
	formOpen: boolean
}> {
	constructor(props) {
		super(props);

		this.state = {
			formOpen: false
		};
	}

	click = (e) => {
		e.preventDefault();

		this.setState({
			formOpen: true
		});
	};

	close = () => {
		this.setState({
			formOpen: false
		});
		console.log("close", this);
	};

	render(): React.ReactElement<any, string | React.JSXElementConstructor<any>> | string | number | {} | React.ReactNodeArray | React.ReactPortal | boolean | null | undefined {
		const currentUser = Auth.getCurrentUser();

		if (!currentUser) {
			return "";
		}

		return [
			<Card className={"dummyPostForm desktopOnly mb-3 rounded"} size={"small"} onClick={(e) => this.click(e)}>
				<img src={currentUser.getAvatarURL()} className={"avatar"} alt={currentUser.getUsername()}/>

				<Input placeholder={"Post something for your followers!"} className={"fakeTextBox"}/>
			</Card>,
			<Button type={"primary"} size={"large"} shape={"circle"} className={"mobileOnly"}
					onClick={(e) => this.click(e)} style={{
				position: "fixed",
				zIndex: 500,
				bottom: 90,
				right: 30
			}}>
				<i className="fas fa-pencil-alt"/>
			</Button>,
			<PostForm visible={this.state.formOpen} parent={this} onClose={this.close}/>
		];
	}
}