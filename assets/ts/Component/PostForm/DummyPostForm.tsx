import React, {Component} from "react";
import Auth from "../../Auth/Auth";
import {Card, Input} from "antd";

export default class DummyPostForm extends Component<any, any> {
	render(): React.ReactElement<any, string | React.JSXElementConstructor<any>> | string | number | {} | React.ReactNodeArray | React.ReactPortal | boolean | null | undefined {
		const currentUser = Auth.getCurrentUser();

		if (!currentUser) {
			return "";
		}

		return <Card className={"dummyPostForm desktopOnly mb-3 rounded"} size={"small"} onClick={(e) => {
			e.preventDefault();

			// TODO: Open actual post form
		}}>
			<img src={currentUser.getAvatarURL()} className={"avatar"} alt={currentUser.getUsername()}/>

			<Input placeholder={"Post something for your followers!"} className={"fakeTextBox"}/>
		</Card>;
	}
}