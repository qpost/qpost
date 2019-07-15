import React, {Component} from "react";
import Auth from "../Auth/Auth";
import MobileSider from "./Header/Mobile/MobileSider";
import DesktopHeader from "./Header/Desktop/DesktopHeader";
import MobileHeader from "./Header/Mobile/MobileHeader";
import MobileNavigation from "./Header/Mobile/MobileNavigation";
import WindowSizeListener from "react-window-size-listener";

class Header extends Component<any, {
	mobileMenu: boolean
}> {
	constructor(props) {
		super(props);

		this.state = {
			mobileMenu: window.innerWidth <= 768
		}
	}

	setIsMobileMenu = (windowWidth: number) => {
		const mobileMenuOpen = windowWidth <= 768;

		if (this.state.mobileMenu !== mobileMenuOpen) {
			this.setState({
				mobileMenu: mobileMenuOpen
			});
		}
	};

	render(): React.ReactElement<any, string | React.JSXElementConstructor<any>> | string | number | {} | React.ReactNodeArray | React.ReactPortal | boolean | null | undefined {
		const currentUser = Auth.getCurrentUser();

		if (!currentUser) {
			Auth.logout();
			return null;
		}

		this.setIsMobileMenu(window.innerWidth);

		return [
			<MobileHeader mobile={this.state.mobileMenu} key={0}/>,
			<MobileSider mobile={this.state.mobileMenu} key={1}/>,
			<DesktopHeader mobile={this.state.mobileMenu} key={2}/>,
			<MobileNavigation mobile={this.state.mobileMenu} key={3}/>,
			<WindowSizeListener onResize={windowSize => {
				this.setIsMobileMenu(windowSize.windowWidth);
			}}/>
		]
	}
}

export default Header;