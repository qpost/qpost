/*
 * Copyright (C) 2018-2020 Gigadrive - All rights reserved.
 * https://gigadrivegroup.com
 * https://qpostapp.com
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
import Col from "antd/es/col";
import "antd/es/col/style";
import LeftSidebar from "./LeftSidebar";
import SidebarStickyContent from "./SidebarStickyContent";
import RightSidebarContent from "./RightSidebarContent";

export default class RightSidebar extends Component<any, {
	randomizer: number
}> {
	public static INSTANCE: RightSidebar | null = null;

	public static update(): void {
		if (this.INSTANCE !== null) {
			this.INSTANCE.setState({
				randomizer: Math.random()
			});
		}
	}

	componentDidMount() {
		RightSidebar.INSTANCE = this;
		LeftSidebar.update();
	}

	componentWillUnmount() {
		if (RightSidebar.INSTANCE === this) {
			RightSidebar.INSTANCE = null;

			LeftSidebar.update();
		}
	}

	render(): React.ReactElement<any, string | React.JSXElementConstructor<any>> | string | number | {} | React.ReactNodeArray | React.ReactPortal | boolean | null | undefined {
		return (
			<Col xl={6} className={"d-none d-xl-block"}>
				<SidebarStickyContent>
					{RightSidebarContent.INSTANCE ? RightSidebarContent.INSTANCE.props.children : ""}
					{this.props.children}
				</SidebarStickyContent>
			</Col>
		)
	}
}