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
import {Modal} from "antd";
import __ from "../i18n/i18n";

export default class LoginSuggestionModal extends Component<any, {
	open: boolean
}> {
	private static INSTANCE: LoginSuggestionModal = null;

	constructor(props) {
		super(props);

		this.state = {
			open: false
		};
	}

	public static open(): void {
		if (this.INSTANCE !== null) {
			this.INSTANCE.setState({
				open: true
			});
		}
	}

	componentDidMount(): void {
		if (LoginSuggestionModal.INSTANCE === null) {
			LoginSuggestionModal.INSTANCE = this;
		}
	}

	componentWillUnmount(): void {
		if (LoginSuggestionModal.INSTANCE === this) {
			LoginSuggestionModal.INSTANCE = null;
		}
	}

	render(): React.ReactElement<any, string | React.JSXElementConstructor<any>> | string | number | {} | React.ReactNodeArray | React.ReactPortal | boolean | null | undefined {
		return <Modal
			title={__("loginPrompt.headline")}
			visible={this.state.open}
			onOk={() => {
				window.location.href = "/login";
			}}
			onCancel={() => {
				this.setState({
					open: false
				})
			}}
		>
			<h4 className={"text-center text-primary"}>
				<i className="fas fa-user-plus"/>
			</h4>

			<h4 className={"text-center mb-0"}>
				{__("loginPrompt.description")}
			</h4>
		</Modal>;
	}
}