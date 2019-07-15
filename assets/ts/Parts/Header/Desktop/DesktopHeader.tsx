import React, {Component} from "react";
import Logo from "../../../../img/navlogo.png";
import {Layout, Menu} from "antd";
import NightMode from "../../../NightMode/NightMode";
import {Link} from "react-router-dom";
import Auth from "../../../Auth/Auth";

export default class DesktopHeader extends Component<{
	mobile: boolean,
	key: any
}, any> {
	render(): React.ReactElement<any, string | React.JSXElementConstructor<any>> | string | number | {} | React.ReactNodeArray | React.ReactPortal | boolean | null | undefined {
		const currentUser = Auth.getCurrentUser();

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
				<Menu.Item key={0}>
					<Link to={"/"} className={"clearUnderline"}>
						home
					</Link>
				</Menu.Item>

				<Menu.Item key={1}>
					<Link to={"/" + currentUser.getUsername()} className={"clearUnderline"}>
						my profile
					</Link>
				</Menu.Item>

				<Menu.Item key={2}>
					<Link to={"/notifications"} className={"clearUnderline"}>
						notifications
					</Link>
				</Menu.Item>

				<Menu.Item key={3}>
					<Link to={"/messages"} className={"clearUnderline"}>
						messages
					</Link>
				</Menu.Item>
			</Menu>
		</Layout.Header>;
	}
}