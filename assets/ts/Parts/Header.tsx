/*
 * Copyright (C) 2018-2019 Gigadrive - All rights reserved.
 * https://gigadrivegroup.com
 * https://qpo.st
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://gnu.org/licenses/>
 */

import React, {Component} from "react";
import MobileSider from "./Header/Mobile/MobileSider";
import DesktopHeader from "./Header/Desktop/DesktopHeader";
import MobileHeader from "./Header/Mobile/MobileHeader";
import MobileNavigation from "./Header/Mobile/MobileNavigation";
import WindowSizeListener from "react-window-size-listener";

class Header extends Component<any, {
	mobileMenu: boolean,
	id: number
}> {
	private static INSTANCE: Header = null;

	constructor(props) {
		super(props);

		this.state = {
			mobileMenu: window.innerWidth <= 768,
			id: Math.random()
		}
	}

	public static update(): void {
		if (this.INSTANCE !== null) {
			this.INSTANCE.setState({
				id: Math.random()
			});
		}
	}

	componentDidMount(): void {
		Header.INSTANCE = this;
	}

	componentWillUnmount(): void {
		Header.INSTANCE = null;
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