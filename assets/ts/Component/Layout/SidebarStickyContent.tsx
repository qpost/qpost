import React, {Component} from "react";
import {Affix} from "antd";

export default class SidebarStickyContent extends Component<any, any> {
	constructor(props) {
		super(props);
	}

	render(): React.ReactElement<any, string | React.JSXElementConstructor<any>> | string | number | {} | React.ReactNodeArray | React.ReactPortal | boolean | null | undefined {
		return <Affix offsetTop={70}>
			{this.props.children}
		</Affix>;
	}
}