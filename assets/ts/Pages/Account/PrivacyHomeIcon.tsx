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
import {Redirect} from "react-router";
import {Card, Col} from "antd";

export default class PrivacyHomeIcon extends Component<{
	iconClass: string,
	title: string,
	description: string,
	path: string
}, {
	redirect: boolean
}> {
	constructor(props) {
		super(props);

		this.state = {
			redirect: false
		};
	}

	render(): React.ReactElement<any, string | React.JSXElementConstructor<any>> | string | number | {} | React.ReactNodeArray | React.ReactPortal | boolean | null | undefined {
		return <Col sm={12} lg={8}>
			{this.state.redirect ? <Redirect push to={this.props.path}/> : ""}

			<Card size={"small"} className={"mb-sm-3 text-center"} style={{cursor: "pointer"}} onClick={(e) => {
				e.preventDefault();

				this.setState({
					redirect: true
				});
			}}>
				<h4><i className={this.props.iconClass}/></h4>
				<h4>{this.props.title}</h4>

				<p className={"mb-0"}>
					{this.props.description}
				</p>
			</Card>
		</Col>;
	}
}