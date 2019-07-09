import React, {Component} from "react";

export default class PageContent extends Component<any, any> {
	render(): React.ReactElement<any, string | React.JSXElementConstructor<any>> | string | number | {} | React.ReactNodeArray | React.ReactPortal | boolean | null | undefined {
		return (
			<div className={"col"}>
				{this.props.children}
			</div>
		)
	}
}