import React, {Component} from "react";
import {Col} from "antd";

export default class PageContent extends Component<any, any> {
	render(): React.ReactElement<any, string | React.JSXElementConstructor<any>> | string | number | {} | React.ReactNodeArray | React.ReactPortal | boolean | null | undefined {
		return (
			<Col>
				{this.props.children}
			</Col>
		)
	}
}