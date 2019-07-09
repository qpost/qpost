import React, {Component} from "react";

export default class LeftSidebar extends Component<any, any> {
	render(): React.ReactElement<any, string | React.JSXElementConstructor<any>> | string | number | {} | React.ReactNodeArray | React.ReactPortal | boolean | null | undefined {
		return (
			<div className={"col-lg-4 col-xl-3 d-none d-lg-block"}>
				{this.props.children}
			</div>
		)
	}
}