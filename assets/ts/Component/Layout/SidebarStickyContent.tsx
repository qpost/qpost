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
import "antd/es/affix/style";
import WindowSizeListener from "react-window-size-listener";
import Sticky from "react-stickynode";

export default class SidebarStickyContent extends Component<{
	hideOnMobile?: boolean
}, {
	mobile: boolean
}> {
	constructor(props) {
		super(props);

		this.state = {
			mobile: window.innerWidth <= 867
		};
	}

	setIsMobileMenu = (windowWidth: number) => {
		const mobileMenuOpen = windowWidth <= 867;

		if (this.state.mobile !== mobileMenuOpen) {
			this.setState({
				mobile: mobileMenuOpen
			});
		}
	};

	render(): React.ReactElement<any, string | React.JSXElementConstructor<any>> | string | number | {} | React.ReactNodeArray | React.ReactPortal | boolean | null | undefined {
		return [this.state.mobile === false ? <Sticky top={70} innerZ={998}>
			<div>
				{this.props.children}
			</div>
		</Sticky> : (this.props.hideOnMobile ? "" : this.props.children), <WindowSizeListener onResize={windowSize => {
			this.setIsMobileMenu(windowSize.windowWidth);
		}}/>];
	}
}