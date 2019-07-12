import React, {Component} from "react";
import FeedEntry from "../../../Entity/Feed/FeedEntry";
import {Col} from "reactstrap";

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

		// TODO
	};

	render(): React.ReactElement<any, string | React.JSXElementConstructor<any>> | string | number | {} | React.ReactNodeArray | React.ReactPortal | boolean | null | undefined {
		return <Col className={"text-center"}>
			<div className={"d-inline-block shareButton" + (this.state.shared ? " active" : "")}
				 onClick={(e) => this.click(e)}>
				<i className={"fas fa-retweet"}/><span className={"number"}>{this.props.entry.getShareCount()}</span>
			</div>
		</Col>;
	}
}