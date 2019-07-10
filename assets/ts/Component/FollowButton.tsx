import React, {Component} from "react";
import User from "../Entity/Account/User";
import FollowStatus from "../Util/FollowStatus";
import Auth from "../Auth/Auth";
import {Redirect} from "react-router-dom";

export default class FollowButton extends Component<{
	target: User,
	className?: string
}, {
	redirectToEditPage: boolean
}> {
	constructor(props) {
		super(props);

		this.state = {
			redirectToEditPage: false
		};
	}

	click = (e) => {
		e.preventDefault();

		const currentUser = Auth.getCurrentUser();
		if (currentUser && currentUser.getId() === this.props.target.getId()) {
			this.setState({
				redirectToEditPage: true
			});
		} else {
			// TODO
		}
	};

	render(): React.ReactElement<any, string | React.JSXElementConstructor<any>> | string | number | {} | React.ReactNodeArray | React.ReactPortal | boolean | null | undefined {
		if (this.state.redirectToEditPage) {
			return <Redirect to={"/edit"}/>
		}

		let color: string = "";
		let text: string = "";

		const currentUser = Auth.getCurrentUser();

		if (currentUser && currentUser.getId() === this.props.target.getId()) {
			color = "light";
			text = "Edit profile";
		} else {
			switch (this.props.target.getFollowStatus()) {
				case FollowStatus.FOLLOWING:
					color = "danger";
					text = "Unfollow";
					break;

				case FollowStatus.PENDING:
					color = "warning";
					text = "Pending";
					break;

				default:
					color = "primary";
					text = "Follow";
					break;
			}
		}

		return <button type={"button"}
					   className={"btn btn-" + color + (this.props.className ? " " + this.props.className : "")}
					   onClick={(e) => this.click(e)}>{text}</button>
	}
}