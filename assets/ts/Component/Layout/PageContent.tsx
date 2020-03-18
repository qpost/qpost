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

export default class PageContent extends Component<{
	leftSidebar?: boolean,
	rightSidebar?: boolean
}, any> {
	render(): React.ReactElement<any, string | React.JSXElementConstructor<any>> | string | number | {} | React.ReactNodeArray | React.ReactPortal | boolean | null | undefined {
		const leftSidebar: boolean = this.props.leftSidebar === true;
		const rightSidebar: boolean = this.props.rightSidebar === true;

		let xl: number = 24;
		let lg: number = 24;

		if (leftSidebar && rightSidebar) {
			// both sidebars exist
			xl = 12;
			lg = 16;
		} else if (leftSidebar && !rightSidebar) {
			// only left sidebar exists
			xl = 18;
			lg = 16;
		} else if (!leftSidebar && rightSidebar) {
			// only right sidebar exists
			xl = 18;
		}

		return (
			<Col xl={xl} lg={lg}>
				{this.props.children}
			</Col>
		)
	}
}