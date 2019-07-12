import React, {Component} from "react";
import FeedEntry from "../../../Entity/Feed/FeedEntry";
import ReplyButton from "./ReplyButton";
import ShareButton from "./ShareButton";
import FavoriteButton from "./FavoriteButton";
import {Row} from "reactstrap";

export default class FeedEntryActionButtons extends Component<{
	entry: FeedEntry
}, any> {
	constructor(props) {
		super(props);
	}

	render(): React.ReactElement<any, string | React.JSXElementConstructor<any>> | string | number | {} | React.ReactNodeArray | React.ReactPortal | boolean | null | undefined {
		const entry: FeedEntry = this.props.entry;

		return <div className={"d-block w-100"}>
			<hr/>

			<Row style={{marginBottom: ".5rem"}}>
				<ReplyButton entry={entry}/>

				<ShareButton entry={entry}/>

				<FavoriteButton entry={entry}/>
			</Row>
		</div>;
	}
}