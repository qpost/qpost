import React, {Component} from "react";
import Logo from "../../../../img/navlogo.png";
import {Layout, Menu} from "antd";
import NightMode from "../../../NightMode/NightMode";
import {Link} from "react-router-dom";

export default class DesktopHeader extends Component<{
	mobile: boolean,
	key: any
}, any> {
	render(): React.ReactElement<any, string | React.JSXElementConstructor<any>> | string | number | {} | React.ReactNodeArray | React.ReactPortal | boolean | null | undefined {
		return <Layout.Header
			hasSider={true}
			className={"mainNav"}
			style={{
				position: "fixed",
				zIndex: 1,
				width: "100%",
				top: 0,
				display: this.props.mobile ? "none" : "block"
			}}>

			<Link to={"/"} className={"clearUnderline"}>
				<img src={Logo} style={{height: "30px"}} alt={"qpost Logo"}/>
			</Link>

			<Menu
				theme={NightMode.isActive() ? "dark" : "light"}
				mode={"horizontal"}
				selectable={false}
				style={{
					lineHeight: "64px",
					float: "right"
				}}>
				<Menu.Item key={0}>Nav 1</Menu.Item>
				<Menu.Item key={1}>Nav 2</Menu.Item>
				<Menu.Item key={3}>Nav 3</Menu.Item>
			</Menu>
		</Layout.Header>;
	}
}