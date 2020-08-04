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
import {Button} from "antd";
import __ from "../i18n/i18n";

export default class ReturnHeader extends Component<{
	className?: string
}, any> {
	render(): React.ReactElement<any, string | React.JSXElementConstructor<any>> | string | number | {} | React.ReactNodeArray | React.ReactPortal | boolean | null | undefined {
		return <div className={"ant-card mb-3 ant-card-bordered ant-card-small" + (this.props.className || "")}>
			<div className={"ant-card-body"} style={{padding: "4px"}}>
				<Button className={"mr-2"} type={"link"} onClick={(e) => {
					e.preventDefault();
					window.history.back();
				}}>
					<i className={"fas fa-arrow-left"}/>
				</Button>{__("goBack.headline")}
			</div>
		</div>;
	}
}