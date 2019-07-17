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

export default class SidebarFooter extends Component<any, any> {
	render(): React.ReactElement<any, string | React.JSXElementConstructor<any>> | string | number | {} | React.ReactNodeArray | React.ReactPortal | boolean | null | undefined {
		return <div className={"sidebarFooter"}>
			<ul>
				<li>
					&copy; Copyright 2018-{new Date().getFullYear()} Gigadrive
				</li>

				<li>
					<a href={"https://gigadrivegroup.com/legal/contact"} target={"_blank"}>
						Contact
					</a>
				</li>

				<li>
					<a href={"https://gigadrivegroup.com/legal/terms-of-service"} target={"_blank"}>
						Terms
					</a>
				</li>

				<li>
					<a href={"https://gigadrivegroup.com/legal/privacy-policy"} target={"_blank"}>
						Privacy
					</a>
				</li>

				<li>
					<a href={"https://gigadrivegroup.com/legal/disclaimer"} target={"_blank"}>
						Disclaimer
					</a>
				</li>
			</ul>
		</div>;
	}
}