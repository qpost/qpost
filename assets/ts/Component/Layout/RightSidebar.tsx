import React, {Component} from "react";
import {Col} from "antd";
import SidebarStickyContent from "./SidebarStickyContent";

export default class RightSidebar extends Component<any, any> {
	render(): React.ReactElement<any, string | React.JSXElementConstructor<any>> | string | number | {} | React.ReactNodeArray | React.ReactPortal | boolean | null | undefined {
		return (
			<Col xl={6} className={"d-none d-xl-block"}>
				<SidebarStickyContent>
					{this.props.children}
				</SidebarStickyContent>
			</Col>
		)
	}
}