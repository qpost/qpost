import React, {Component} from "react";
import FeedEntry from "../../../Entity/Feed/FeedEntry";
import {Col} from "reactstrap";

export default class ReplyButton extends Component<{
	entry: FeedEntry
}, any> {
	click = (e) => {
		e.preventDefault();

		// TODO
	};

	render(): React.ReactElement<any, string | React.JSXElementConstructor<any>> | string | number | {} | React.ReactNodeArray | React.ReactPortal | boolean | null | undefined {
		return <Col className={"text-center"}>
			<div className={"d-inline-block replyButton"} onClick={(e) => this.click(e)}>
				<i className={"fas fa-share"}/><span className={"number"}>{this.props.entry.getReplyCount()}</span>
			</div>
		</Col>;
	}
}