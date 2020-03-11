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
import Logo from "../../img/qpost-blue-small.png";
import {Spin} from "antd";

export default class LoadingScreen extends Component<any, any> {
	render() {
		return (
			<div className={"vertical-container"}>
				<div className={"text-center vertical-center"}>
					<div className={"mb-3"}>
						<img src={Logo} height={60} alt={"Logo"}/>
					</div>

					<Spin size={"large"}/>
				</div>
			</div>
		);
	}
}