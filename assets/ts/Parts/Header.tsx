import React, {Component} from "react";
import Auth from "../Auth/Auth";
import {Link} from "react-router-dom";
import NightMode from "../NightMode/NightMode";
import Logo from "../../img/navlogo.png";
import {Avatar, Button, Icon, Layout, Menu} from "antd";
import windowSize from "react-window-size";

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
			</Menu.Item>
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

		const {Header, Sider} = Layout;

		return [<Sider
			key={0}
			className={!this.state.mobileMenu ? "d-none" : ""}
			breakpoint="lg"
			collapsedWidth="0"
			onBreakpoint={broken => {
				this.setState({
					mobileMenu: broken
				});
				console.log(broken);
			}}
			onCollapse={(collapsed, type) => {
				console.log(collapsed, type);
			}}>
			<img src={Logo} style={{height: "30px"}} alt={"qpost Logo"}/>
			<Menu theme={NightMode.isActive() ? "dark" : "light"} mode="inline" selectable={false}>
				<Menu.Item key="1">
					<Icon type="user"/>
					<span className="nav-text">nav 1</span>
				</Menu.Item>
				<Menu.Item key="2">
					<Icon type="video-camera"/>
					<span className="nav-text">nav 2</span>
				</Menu.Item>
				<Menu.Item key="3">
					<Icon type="upload"/>
					<span className="nav-text">nav 3</span>
				</Menu.Item>
				<Menu.Item key="4">
					<Icon type="user"/>
					<span className="nav-text">nav 4</span>
				</Menu.Item>
			</Menu>
		</Sider>,
			<Header
				key={1}
				className={this.state.mobileMenu ? "d-none" : ""}
				hasSider={true}
				style={{
					position: "fixed",
					zIndex: 1,
					width: "100%",
					top: 0
				}}>
				<img src={Logo} style={{height: "30px"}} alt={"qpost Logo"}/>

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
			</Header>]
	}
}

export default windowSize(Header);