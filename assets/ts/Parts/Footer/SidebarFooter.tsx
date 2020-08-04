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
import __ from "../../i18n/i18n";

export default class SidebarFooter extends Component<any, any> {
	render(): React.ReactElement<any, string | React.JSXElementConstructor<any>> | string | number | {} | React.ReactNodeArray | React.ReactPortal | boolean | null | undefined {
		return <div className={"sidebarFooter"}>
			<div className={"copyright"}>
				&copy; Copyright 2018-{new Date().getFullYear()} Gigadrive
			</div>

			<ul>
				<li>
					<a href={"/about"}>
						{__("landing.footer.company.about")}
					</a>
				</li>

				<li>
					<a href={"/advertise"} target={"_blank"}>
						{__("landing.footer.company.advertise")}
					</a>
				</li>

				{/*<li>
					<a href={"/help"} target={"_blank"}>
						Help
					</a>
				</li>*/}

				<li>
					<a href={"/contact"} target={"_blank"}>
						{__("footer.contact")}
					</a>
				</li>

				<li>
					<a href={"/terms"}>
						{__("landing.footer.legal.terms")}
					</a>
				</li>

				<li>
					<a href={"/privacy"}>
						{__("landing.footer.legal.privacy")}
					</a>
				</li>

				<li>
					<a href={"/disclaimer"}>
						{__("landing.footer.legal.disclaimer")}
					</a>
				</li>

				<li>
					<a href={"/apidocs"}>
						{__("footer.developers")}
					</a>
				</li>
			</ul>
		</div>;
	}
}