import React, {Component} from "react";
import Auth from "../Auth/Auth";
import {Link} from "react-router-dom";
import Logo from "../../img/navlogo.png";
import {Avatar, Button, Menu} from "antd";
import windowSize from "react-window-size";
import MobileSider from "./Header/Mobile/MobileSider";
import DesktopHeader from "./Header/Desktop/DesktopHeader";
import MobileHeader from "./Header/Mobile/MobileHeader";
import MobileNavigation from "./Header/Mobile/MobileNavigation";

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

	setIsMobileMenu = (windowWidth: number) => {
		const mobileMenuOpen = windowWidth <= 768;

		if (this.state.mobileMenu !== mobileMenuOpen) {
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

		this.setIsMobileMenu(this.props.windowWidth);

		return [
			<MobileHeader mobile={this.state.mobileMenu} key={0}/>,
			<MobileSider mobile={this.state.mobileMenu} key={1}/>,
			<DesktopHeader mobile={this.state.mobileMenu} key={2}/>,
			<MobileNavigation mobile={this.state.mobileMenu} key={3}/>
		]
	}
}

export default windowSize(Header);