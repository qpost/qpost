import React, {Component} from "react";
import User from "../Entity/Account/User";
import FollowStatus from "../Util/FollowStatus";
import Auth from "../Auth/Auth";
import {Redirect} from "react-router-dom";
import API from "../API/API";

export default class FollowButton extends Component<{
	target: User,
	className?: string,
	followStatus?: number
}, {
	redirectToEditPage: boolean,
	loading: boolean,
	followStatus: number | null
}> {
	constructor(props) {
		super(props);

		this.state = {
			redirectToEditPage: false,
			loading: true,
			followStatus: null
		};
	}

	click = (e) => {
		e.preventDefault();

		if (this.isCurrentUser()) {
			this.setState({
				redirectToEditPage: true
			});
		} else {
			// TODO
		}
	};

	componentDidMount(): void {
		if (this.props.followStatus === undefined) {
			// Fetch follow status if it was not passed

			if (Auth.isLoggedIn()) {
				if (!this.isCurrentUser()) {
					API.handleRequest("/follow", "GET", {
						from: Auth.getCurrentUser().getId(),
						to: this.props.target.getId()
					}, data => {
						if (data.status) {
							this.setState({
								followStatus: data.status
							});
						} else {
							this.setState({
								followStatus: FollowStatus.FOLLOWING
							});
						}
					}, error => {
						this.setState({
							followStatus: FollowStatus.NOT_FOLLOWING
						});
					})
				}
			} else {
				// Default to not following if user is not logged in
				this.setState({
					followStatus: FollowStatus.NOT_FOLLOWING
				});
			}
		}
	}

	render(): React.ReactElement<any, string | React.JSXElementConstructor<any>> | string | number | {} | React.ReactNodeArray | React.ReactPortal | boolean | null | undefined {
		if (this.state.redirectToEditPage) {
			return <Redirect to={"/edit"}/>
		}

		let color: string = "";
		let text: string = "";

		if (this.isCurrentUser()) {
			color = "light";
			text = "Edit profile";
		} else {
			const followStatus: number = this.state.followStatus || this.props.followStatus;

			switch (followStatus) {
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

	private isCurrentUser(): boolean {
		const currentUser = Auth.getCurrentUser();
		return currentUser && currentUser.getId() === this.props.target.getId();
	}
}