import React, {Component} from "react";
import User from "../Entity/Account/User";
import FollowStatus from "../Util/FollowStatus";
import Auth from "../Auth/Auth";
import {Redirect} from "react-router-dom";
import API from "../API/API";
import {Button, message, Spin} from "antd";
import {Method} from "axios";

export default class FollowButton extends Component<{
	target: User,
	className?: string,
	followStatus?: number,
	size?: "small" | "large" | "default"
}, {
	redirectToEditPage: boolean,
	loading: boolean,
	followStatus: number | null,
	error: string | null
}> {
	constructor(props) {
		super(props);

		this.state = {
			redirectToEditPage: false,
			loading: !this.props.followStatus,
			followStatus: null,
			error: null
		};
	}

	click = (e) => {
		e.preventDefault();

		if (this.isCurrentUser()) {
			this.setState({
				redirectToEditPage: true
			});
		} else {
			if (!this.state.loading) {
				const followStatus: number = this.followStatus();
				let method: Method = followStatus === FollowStatus.FOLLOWING || followStatus === FollowStatus.PENDING ? "DELETE" : "POST";

				this.setState({
					loading: true
				});

				API.handleRequest("/follow", method, {to: this.props.target.getId()}, data => {
					if (data.result) {
						this.setState({
							followStatus: data.result
						});
					} else {
						message.error("An error occurred.");
					}
				}, error => {
					message.error(error);

					this.setState({
						loading: false
					});
				});
			}
		}
	};

	componentDidMount(): void {
		if (this.props.followStatus === undefined) {
			// Fetch follow status if it was not passed

			if (Auth.isLoggedIn()) {
				if (!this.isCurrentUser()) {
					API.handleRequest("/follow", "GET", {
						from: Auth.getCurrentUser().getId(),
						to: this.props.target.getId(),
						loading: false
					}, data => {
						if (data.status) {
							this.setState({
								followStatus: data.status,
								loading: false
							});
						} else {
							this.setState({
								followStatus: FollowStatus.FOLLOWING,
								loading: false
							});
						}
					}, error => {
						this.setState({
							followStatus: FollowStatus.NOT_FOLLOWING,
							loading: false
						});
					})
				}
			} else {
				// Default to not following if user is not logged in
				this.setState({
					followStatus: FollowStatus.NOT_FOLLOWING,
					loading: false
				});
			}
		}
	}

	followStatus: () => number = () => {
		return this.state.followStatus || this.props.followStatus;
	};

	firstLoad: () => boolean = () => {
		return !this.props.followStatus && this.state.loading;
	};

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
			switch (this.followStatus()) {
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

		return <Button
			className={"followButton" + (!this.state.loading ? " btn-" + color : "") + (this.props.className ? " " + this.props.className : "")}
			size={this.props.size || "default"}
			type={!this.state.loading && !this.isCurrentUser() && color === "primary" ? "primary" : "default"}
			onClick={(e) => this.click(e)} shape={"round"}>
			{(!this.state.loading && !this.firstLoad()) || this.isCurrentUser() ? text : <Spin size={"small"}/>}
		</Button>;

		/*return <button type={"button"}
					   className={"btn btn-" + color + (this.props.className ? " " + this.props.className : "")}
					   onClick={(e) => this.click(e)}>{text}</button>*/
	}

	private isCurrentUser(): boolean {
		const currentUser = Auth.getCurrentUser();
		return currentUser && currentUser.getId() === this.props.target.getId();
	}
}