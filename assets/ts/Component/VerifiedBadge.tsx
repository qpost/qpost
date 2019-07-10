import User from "../Entity/Account/User";
import React, {Component} from "react";

export default class VerifiedBadge extends Component<{ target: User }, any> {
	render(): React.ReactElement<any, string | React.JSXElementConstructor<any>> | string | number | {} | React.ReactNodeArray | React.ReactPortal | boolean | null | undefined {
		if (this.props.target.isVerified()) {
			return <span className="ml-1 small" data-placement="right" data-toggle="tooltip" data-html="true"
						 title="This account has has been confirmed as an authentic page for this public figure, media company or brand."><i
				className="fas fa-check-circle text-mainColor"/></span>;
		}

		return "";
	}
}