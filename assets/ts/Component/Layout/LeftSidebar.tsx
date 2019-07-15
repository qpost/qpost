import React, {Component} from "react";
import {Col} from "antd";

export default class LeftSidebar extends Component<any, any> {
	render(): React.ReactElement<any, string | React.JSXElementConstructor<any>> | string | number | {} | React.ReactNodeArray | React.ReactPortal | boolean | null | undefined {
		return (
			<Col lg={4} xl={3} className={"d-none d-lg-block"}>
				{this.props.children}
			</Col>
		)
	}
}