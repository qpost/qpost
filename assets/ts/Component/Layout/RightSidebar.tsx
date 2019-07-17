import React, {Component} from "react";
import Col from "antd/es/col";
import "antd/es/col/style";

export default class RightSidebar extends Component<any, any> {
	render(): React.ReactElement<any, string | React.JSXElementConstructor<any>> | string | number | {} | React.ReactNodeArray | React.ReactPortal | boolean | null | undefined {
		return (
			<Col xl={6} className={"d-none d-xl-block"}>
				{this.props.children}
			</Col>
		)
	}
}