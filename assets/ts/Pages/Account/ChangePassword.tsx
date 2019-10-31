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
import AccountBase from "./AccountBase";
import {Button, Card, Col, Input, message, Row} from "antd";
import ReturnHeader from "../../Component/ReturnHeader";
import API from "../../API/API";
import Auth from "../../Auth/Auth";
import BaseObject from "../../Serialization/BaseObject";
import User from "../../Entity/Account/User";

export default class ChangePassword extends Component<any, {
	oldPassword: string,
	newPassword: string,
	newPassword2: string,
	loading: boolean
}> {
	constructor(props) {
		super(props);

		this.state = {
			oldPassword: "",
			newPassword: "",
			newPassword2: "",
			loading: false
		};
	}

	render(): React.ReactElement<any, string | React.JSXElementConstructor<any>> | string | number | {} | React.ReactNodeArray | React.ReactPortal | boolean | null | undefined {
		const labelCol = {xs: {span: 6, offset: 0}, md: {span: 3, offset: 0}};
		const wrapperCol = {xs: {span: 18, offset: 0}, md: {span: 21, offset: 0}};
		const labelStyle = {lineHeight: "25px"};

		return <AccountBase activeKey={"HOME"}>
			<ReturnHeader className={"mb-3"}/>

			<Card size={"small"} title={"Change password"}>
				<Row className={"mb-3"}>
					<Col {...labelCol} style={labelStyle}>
						Current password
					</Col>

					<Col {...wrapperCol}>
						<Input.Password value={this.state.oldPassword} onChange={(e) => {
							const value = e.target.value;

							this.setState({
								oldPassword: value
							});
						}} visibilityToggle={false}/>
					</Col>
				</Row>

				<Row className={"mb-3"}>
					<Col {...labelCol} style={labelStyle}>
						New password
					</Col>

					<Col {...wrapperCol}>
						<Input.Password value={this.state.newPassword} onChange={(e) => {
							const value = e.target.value;

							this.setState({
								newPassword: value
							});
						}} visibilityToggle={false}/>
					</Col>
				</Row>

				<Row className={"mb-3"}>
					<Col {...labelCol} style={labelStyle}>
						Repeat new password
					</Col>

					<Col {...wrapperCol}>
						<Input.Password value={this.state.newPassword2} onChange={(e) => {
							const value = e.target.value;

							this.setState({
								newPassword2: value
							});
						}} visibilityToggle={false}/>
					</Col>
				</Row>

				<Row>
					<Col {...labelCol}>
						&nbsp;
					</Col>

					<Col {...wrapperCol}>
						<Button type={"primary"} loading={this.state.loading} onClick={(e) => {
							if (!this.state.loading) {
								if (this.state.newPassword === this.state.newPassword2) {
									this.setState({loading: true});

									API.handleRequest("/password", "POST", this.state, data => {
										this.setState({
											loading: false,
											oldPassword: "",
											newPassword: "",
											newPassword2: ""
										});
										Auth.setCurrentUser(BaseObject.convertObject(User, data.result));
										message.success("Your password has been changed.");
									}, error => {
										this.setState({loading: false});
										message.error(error);
									});
								} else {
									message.error("The new passwords don't match.");
								}
							}
						}}>
							Save changes
						</Button>
					</Col>
				</Row>
			</Card>
		</AccountBase>;
	}
}