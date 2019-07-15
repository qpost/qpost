import React, {Component} from "react";
import {Col} from "antd";

export default class PageContent extends Component<{
	leftSidebar?: boolean,
	rightSidebar?: boolean
}, any> {
	render(): React.ReactElement<any, string | React.JSXElementConstructor<any>> | string | number | {} | React.ReactNodeArray | React.ReactPortal | boolean | null | undefined {
		const leftSidebar: boolean = this.props.leftSidebar === true;
		const rightSidebar: boolean = this.props.rightSidebar === true;

		let xl: number = 24;
		let lg: number = 24;

		if (leftSidebar && rightSidebar) {
			// both sidebars exist
			xl = 12;
			lg = 16;
		} else if (leftSidebar && !rightSidebar) {
			// only left sidebar exists
			xl = 18;
			lg = 16;
		} else if (!leftSidebar && rightSidebar) {
			// only right sidebar exists
			xl = 18;
		}

		return (
			<Col xl={xl} lg={lg}>
				{this.props.children}
			</Col>
		)
	}
}