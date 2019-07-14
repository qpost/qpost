import React, {Component} from "react";
import Auth from "../Auth/Auth";
import {Link} from "react-router-dom";
import NightMode from "../NightMode/NightMode";
import Logo from "../../img/navlogo.png";
import {Avatar, Button, Drawer, Layout, Menu} from "antd";
import windowSize from "react-window-size";
import SubMenu from "antd/es/menu/SubMenu";

class Header extends Component<any, {
	mobileMenu: boolean
}> {
	constructor(props) {
		super(props);

		this.state = {
			mobileMenu: this.props.windowWidth <= 768
		}
	}

	renderNavLinks = () => {
		return [
			<Menu.Item key={0}>
				<Link to={"/"} className={"clearUnderline"}>
					<img src={Logo} style={{height: "30px"}} alt={"qpost Logo"}/>
				</Link>
			</Menu.Item>,
			<SubMenu key={1} title="SubMenu">
				<Menu.Item>SubMenuItem</Menu.Item>
			</SubMenu>
		];
	};

	renderUserLinks = () => {
		return <Menu.Item style={{
			float: "right",
			display: "flex",
			justifyContent: "space-around",
			alignItems: "center",
			width: "180px"
		}}>
			<Link to={"/"}>test</Link>
			{this.props.windowWidth <= 768 ? <Button onClick={() => {
				this.toggleMobileMenu();
			}} icon={"bars"} ghost={true}/> : ""}
			<Avatar src={Auth.getCurrentUser().getAvatarURL()}/>
		</Menu.Item>;
	};

	renderNavbar = () => {
		this.setIsMobileMenu(this.props.windowWidth);
		return this.renderNavLinks();
	};

	setIsMobileMenu = (windowWidth: number) => {
		const mobileMenuOpen = windowWidth <= 768;

		if (this.state.mobileMenu !== mobileMenuOpen && !mobileMenuOpen) {
			this.setState({
				mobileMenu: mobileMenuOpen
			});
		}
	};

	toggleMobileMenu = () => {
		this.setState({
			mobileMenu: !this.state.mobileMenu
		});
	};

	render(): React.ReactElement<any, string | React.JSXElementConstructor<any>> | string | number | {} | React.ReactNodeArray | React.ReactPortal | boolean | null | undefined {
		const currentUser = Auth.getCurrentUser();

		if (!currentUser) {
			Auth.logout();
			return null;
		}

		return <div>
			<Drawer
				title={"qpost"}
				placement={"left"}
				closable={true}
				onClose={() => {
					this.toggleMobileMenu();
				}}
				visible={this.state.mobileMenu}>
				<Menu>
					{this.renderNavLinks()}
				</Menu>
			</Drawer>

			<Layout.Header style={{
				position: "fixed",
				top: 0,
				zIndex: 1,
				width: "100%",
				padding: this.state.mobileMenu ? "0" : "0 10%"
			}}>
				<Menu
					theme={NightMode.isActive() ? "dark" : "light"}
					mode={"horizontal"}
					selectable={false}
					style={{
						lineHeight: "64px"
					}}>
					{this.renderNavbar()}
					{this.renderUserLinks()}
				</Menu>
			</Layout.Header>
		</div>;
	}
}

export default windowSize(Header);