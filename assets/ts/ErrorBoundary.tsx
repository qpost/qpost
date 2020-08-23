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
import __ from "./i18n/i18n";

export default class ErrorBoundary extends Component<any, {
	crashed: boolean
}> {
	constructor(props) {
		super(props);

		this.state = {
			crashed: false
		};
	}

	componentDidCatch(error: Error, errorInfo: React.ErrorInfo): void {
		this.setState({
			crashed: true
		});
	}

	render(): React.ReactElement<any, string | React.JSXElementConstructor<any>> | string | number | {} | React.ReactNodeArray | React.ReactPortal | boolean | null | undefined {
		if (this.state.crashed) {
			return <div className={"text-center mt-5"}>
				<div className={"mb-3"}>
					<h4>{__("error.general")}</h4>
				</div>

				<Button type={"primary"} onClick={(e) => {
					window.location.reload();
				}}>
					{__("error.general.refresh")}
				</Button>
			</div>;
		}

		return this.props.children;
	}
}