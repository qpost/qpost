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
import {Link} from "react-router-dom";

export default class SidebarFooter extends Component<any, any> {
	render(): React.ReactElement<any, string | React.JSXElementConstructor<any>> | string | number | {} | React.ReactNodeArray | React.ReactPortal | boolean | null | undefined {
		return <div className={"sidebarFooter"}>
			<div className={"copyright"}>
				&copy; Copyright 2018-{new Date().getFullYear()} Gigadrive
			</div>

			<ul>
				<li>
					<Link to={"/about"}>
						About
					</Link>
				</li>

				<li>
					<a href={"/advertise"} target={"_blank"}>
						Advertise
					</a>
				</li>

				{/*<li>
					<a href={"/help"} target={"_blank"}>
						Help
					</a>
				</li>*/}

				<li>
					<a href={"/contact"} target={"_blank"}>
						Contact
					</a>
				</li>

				<li>
					<a href={"/terms"}>
						Terms
					</a>
				</li>

				<li>
					<a href={"/privacy"}>
						Privacy
					</a>
				</li>

				<li>
					<a href={"/disclaimer"}>
						Disclaimer
					</a>
				</li>

				<li>
					<a href={"/apidocs"}>
						Developers
					</a>
				</li>
			</ul>
		</div>;
	}
}