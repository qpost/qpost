/*
 * Copyright (C) 2018-2020 Gigadrive - All rights reserved.
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
import SidebarStickyContent from "./Layout/SidebarStickyContent";
import NightMode from "../NightMode/NightMode";

export default class PageHeader extends Component<{
	title: string,
	iconClass?: string,
	className?: string
}, any> {
	render(): React.ReactElement<any, string | React.JSXElementConstructor<any>> | string | number | {} | React.ReactNodeArray | React.ReactPortal | boolean | null | undefined {
		return <div className={"page-header-sticky-wrapper"}>
			<SidebarStickyContent hideOnMobile={true}>
				<div style={{
					paddingTop: "6px",
					marginTop: "-6px",
					backgroundColor: NightMode.isActive() ? "#000b17" : "#F0F2F5"
				}}>
					<div
						className={"ant-card ant-card-bordered ant-card-small rounded-none rounded-top page-header" + (this.props.className || "")}
						style={{
							zIndex: 1999,
							borderColor: NightMode.isActive() ? "#001020" : "#DFDFDF",
							cursor: "pointer"
						}}
						onClick={event => {
							event.preventDefault();

							$("html,body").animate({scrollTop: 0}, "slow");
						}}
					>
						<div className={"ant-card-body"} style={{
							padding: "15px",
							fontSize: "16px"
						}}>
							{this.props.iconClass ? <i className={this.props.iconClass + " mr-2"}/> : ""}
							{this.props.title}
						</div>
					</div>
				</div>
			</SidebarStickyContent>
		</div>;
	}
}