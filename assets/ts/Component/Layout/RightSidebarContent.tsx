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

import {Component} from "react";
import RightSidebar from "./RightSidebar";
import LeftSidebar from "./LeftSidebar";

export default class RightSidebarContent extends Component<any, any> {
	public static INSTANCE: RightSidebarContent | null = null;

	componentDidMount() {
		RightSidebarContent.INSTANCE = this;
		RightSidebar.update();
		LeftSidebar.update();
	}

	componentWillUnmount() {
		if (RightSidebarContent.INSTANCE === this) {
			RightSidebarContent.INSTANCE = null;

			RightSidebar.update();
			LeftSidebar.update();
		}
	}

	render() {
		return "";
	}
}