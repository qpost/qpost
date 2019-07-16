import React, {Component} from "react";
import Auth from "../../Auth/Auth";
import {Button, Card, Input} from "antd";

export default class DummyPostForm extends Component<any, any> {
	click = (e) => {
		e.preventDefault();

		// TODO: Open actual post form
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
			</Button>
		];
	}
}