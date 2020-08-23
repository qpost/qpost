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
import RightSidebar from "./RightSidebar";
import SidebarStickyContent from "./SidebarStickyContent";
import RightSidebarContent from "./RightSidebarContent";

export default class LeftSidebar extends Component<{
	negativeOffset?: boolean
}, {
	randomizer: number
}> {
	public static INSTANCE: LeftSidebar | null = null;

	constructor(props) {
		super(props);

		this.state = {randomizer: Math.random()}
	}

	public static update(): void {
		if (this.INSTANCE !== null) {
			this.INSTANCE.setState({
				randomizer: Math.random()
			});
		}
	}

	componentDidMount() {
		LeftSidebar.INSTANCE = this;
	}

	componentWillUnmount() {
		if (LeftSidebar.INSTANCE === this) {
			LeftSidebar.INSTANCE = null;
		}
	}

	render(): React.ReactElement<any, string | React.JSXElementConstructor<any>> | string | number | {} | React.ReactNodeArray | React.ReactPortal | boolean | null | undefined {
		const rightSidebarContent = RightSidebar.INSTANCE ? RightSidebar.INSTANCE.props.children : null;

		return (
			<Col lg={8} xl={6} className={"d-none d-lg-block"} style={this.props.negativeOffset ? {
				marginTop: "-130px"
			} : {}}>
				<SidebarStickyContent>
					{this.props.children}

					{rightSidebarContent ? <div className={"d-none d-lg-block d-xl-none mt-3"}>
						{RightSidebarContent.INSTANCE ? RightSidebarContent.INSTANCE.props.children : ""}
						{rightSidebarContent}
					</div> : ""}
				</SidebarStickyContent>
			</Col>
		)
	}
}