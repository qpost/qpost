import React, {Component} from "react";
import FeedEntry from "../../../Entity/Feed/FeedEntry";
import Auth from "../../../Auth/Auth";

export default class DeleteButton extends Component<{
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
		const currentUser = Auth.getCurrentUser();
		if (!currentUser || currentUser.getId() !== this.props.entry.getUser().getId()) {
			return "";
		}

		return <div className={"d-inline-block deleteButton" + (this.state.favorited ? " active" : "")}
					onClick={(e) => this.click(e)}>
			<i className={"fas fa-trash-alt"}/>
		</div>;
	}
}