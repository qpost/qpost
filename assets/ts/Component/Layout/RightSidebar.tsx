import React, {Component} from "react";

export default class RightSidebar extends Component<any, any> {
	render(): React.ReactElement<any, string | React.JSXElementConstructor<any>> | string | number | {} | React.ReactNodeArray | React.ReactPortal | boolean | null | undefined {
		return (
			<div className={"col-xl-3 d-none d-xl-block"}>
				{this.props.children}
			</div>
		)
	}
}