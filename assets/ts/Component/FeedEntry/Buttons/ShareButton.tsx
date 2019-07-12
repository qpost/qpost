import React, {Component} from "react";
import FeedEntry from "../../../Entity/Feed/FeedEntry";
import Auth from "../../../Auth/Auth";
import {formatNumberShort} from "../../../Util/Format";

export default class ShareButton extends Component<{
	entry: FeedEntry
}, {
	shared: boolean
}> {
	constructor(props) {
		super(props);

		this.state = {
			shared: this.props.entry.isShared()
		}
	}

	click = (e) => {
		e.preventDefault();

		if (!this.isSelf()) {
			// TODO
		}
	};

	render(): React.ReactElement<any, string | React.JSXElementConstructor<any>> | string | number | {} | React.ReactNodeArray | React.ReactPortal | boolean | null | undefined {
		return <div
			className={"d-inline-block shareButton" + (this.state.shared ? " active" : this.isSelf() ? " inactive" : "")}
			onClick={(e) => this.click(e)}>
			<i className={"fas fa-retweet"}/><span
			className={"number"}>{formatNumberShort(this.props.entry.getShareCount())}</span>
		</div>;
	}

	private isSelf() {
		let self: boolean = false;
		const currentUser = Auth.getCurrentUser();

		if (currentUser && this.props.entry.getUser().getId() === currentUser.getId()) {
			self = true;
		}

		return self;
	}
}