import React, {Component} from "react";
import {Row} from "antd";

export default class ContentBase extends Component<any, any> {
	constructor(props) {
		super(props);
	}

	render(): React.ReactElement<any, string | React.JSXElementConstructor<any>> | string | number | {} | React.ReactNodeArray | React.ReactPortal | boolean | null | undefined {
		return <Row gutter={12}>
			{this.props.children}
		</Row>
	}
}