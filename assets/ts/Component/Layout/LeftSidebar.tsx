import React, {Component} from "react";
import {Col} from "antd";

export default class LeftSidebar extends Component<any, any> {
	render(): React.ReactElement<any, string | React.JSXElementConstructor<any>> | string | number | {} | React.ReactNodeArray | React.ReactPortal | boolean | null | undefined {
		return (
			<Col lg={8} xl={6} className={"d-none d-lg-block"}>
				{this.props.children}
			</Col>
		)
	}
}