import React, {Component} from "react";
import {Layout, Menu} from "antd";
import NightMode from "../../../NightMode/NightMode";
import {Link} from "react-router-dom";

export default class MobileNavigation extends Component<{
	mobile: boolean,
	key: any
}, any> {
	render(): React.ReactElement<any, string | React.JSXElementConstructor<any>> | string | number | {} | React.ReactNodeArray | React.ReactPortal | boolean | null | undefined {
		return <Layout.Header
			className={"mobileNav"}
			style={{
				position: "fixed",
				zIndex: 1,
				width: "100%",
				bottom: 0,
				display: !this.props.mobile ? "none" : "block"
			}}>
			<div style={{
				textAlign: "center"
			}}>
				<Menu
					theme={NightMode.isActive() ? "dark" : "light"}
					mode={"horizontal"}
					selectable={false}
					inlineCollapsed={false}
					style={{
						lineHeight: "64px"
					}}>
					<Menu.Item key={0}>
						<Link to={"/"}>
							<i className={"fas fa-home"}/>
						</Link>
					</Menu.Item>

					<Menu.Item key={1}>
						<Link to={"/search"}>
							<i className={"fas fa-search"}/>
						</Link>
					</Menu.Item>

					<Menu.Item key={2}>
						<Link to={"/notifications"}>
							<i className={"fas fa-bell"}/>
						</Link>
					</Menu.Item>

					<Menu.Item key={3}>
						<Link to={"/messages"}>
							<i className={"fas fa-envelope"}/>
						</Link>
					</Menu.Item>
				</Menu>
			</div>
		</Layout.Header>;
	}
}