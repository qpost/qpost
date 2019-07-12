import React, {Component} from "react";
import FeedEntry from "../../../Entity/Feed/FeedEntry";
import {Col} from "reactstrap";

export default class FavoriteButton extends Component<{
	entry: FeedEntry
}, {
	favorited: boolean
}> {
	constructor(props) {
		super(props);

		this.state = {
			favorited: this.props.entry.isFavorited()
		};
	}

	click = (e) => {
		e.preventDefault();

		// TODO
	};

	render(): React.ReactElement<any, string | React.JSXElementConstructor<any>> | string | number | {} | React.ReactNodeArray | React.ReactPortal | boolean | null | undefined {
		return <Col className={"text-center"}>
			<div className={"d-inline-block favoriteButton" + (this.state.favorited ? " active" : "")}
				 onClick={(e) => this.click(e)}>
				<i className={"fas fa-star"}/><span className={"number"}>{this.props.entry.getFavoriteCount()}</span>
			</div>
		</Col>;
	}
}