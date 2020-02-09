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
import Auth from "../Auth/Auth";
import {Button, Card} from "antd";
import PostForm from "./PostForm/PostForm";

export default class FirstPostEncouragement extends Component<any, {
	show: boolean
}> {
	private static INSTANCE: FirstPostEncouragement = null;

	constructor(props) {
		super(props);

		let show: boolean = false;
		const user = Auth.getCurrentUser();
		if (user && user.getTotalPostCount() === 0) {
			show = true;
		}

		this.state = {
			show
		};
	}

	public static hide(): void {
		if (this.INSTANCE !== null) {
			this.INSTANCE.setState({
				show: false
			});
		}
	}

	componentDidMount(): void {
		if (FirstPostEncouragement.INSTANCE === null) {
			FirstPostEncouragement.INSTANCE = this;
		}
	}

	componentWillUnmount(): void {
		if (FirstPostEncouragement.INSTANCE === this) {
			FirstPostEncouragement.INSTANCE = null;
		}
	}

	render(): React.ReactElement<any, string | React.JSXElementConstructor<any>> | string | number | {} | React.ReactNodeArray | React.ReactPortal | boolean | null | undefined {
		if (!this.state.show) {
			return "";
		}

		return <div className={"mb-3"}>
			<Card size={"small"}>
				<Button type={"primary"} className={"float-right"} onClick={(e) => {
					e.preventDefault();

					PostForm.open("Hey, I'm currently setting up my qpost profile! #myFirstPost");
				}}>
					Create your first post
				</Button>

				<h4 className={"mb-0"}>Welcome to qpost!</h4>
			</Card>
		</div>;
	}
}