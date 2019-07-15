import React, {Component} from "react";
import {Button, Layout} from "antd";
import Logo from "../../../../img/navlogo.png";
import {Link} from "react-router-dom";

export default class MobileHeader extends Component<{
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
				display: !this.props.mobile ? "none" : "block"
			}}>
			<div style={{
				textAlign: "center"
			}}>
				<Button id={"mobileSiderTrigger"} ghost={true} type={"link"} style={{
					float: "left",
					lineHeight: "64px"
				}}>
					<i className="fas fa-bars"/>
				</Button>

				<Link to={"/"} className={"clearUnderline"}>
					<img src={Logo} style={{height: "30px"}} alt={"qpost Logo"}/>
				</Link>

				<Button ghost={true} type={"link"} style={{
					float: "right",
					lineHeight: "64px"
				}}>
					<i className="fas fa-bars"/>
				</Button>
			</div>
		</Layout.Header>;
	}
}