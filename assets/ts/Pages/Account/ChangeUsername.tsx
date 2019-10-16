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
import {Alert, Button, Card, Col, Input, message, Row} from "antd";
import {Link} from "react-router-dom";
import Auth from "../../Auth/Auth";
import API from "../../API/API";
import BaseObject from "../../Serialization/BaseObject";
import User from "../../Entity/Account/User";

export default class ChangeUsername extends Component<any, {
	username: string,
	loading: boolean
}> {
	constructor(props) {
		super(props);

		this.state = {
			username: Auth.getCurrentUser().getUsername(),
			loading: false
		};
	}


	render(): React.ReactElement<any, string | React.JSXElementConstructor<any>> | string | number | {} | React.ReactNodeArray | React.ReactPortal | boolean | null | undefined {
		const user = Auth.getCurrentUser();
		if (!user) return "";

		return <Row gutter={24}>
			<Col md={{span: 16, offset: 4}} lg={{span: 12, offset: 6}}>
				{user.isVerified() ? <Alert type={"warning"}
											message={"Changing your username will result in losing your verification status."}
											className={"mb-3"}/> : ""}

				<Card>
					<h4 className={"text-center"}>Change your username</h4>

					<Row gutter={24}>
						<Col md={{span: 12, offset: 6}}>
							<p className={"mb-3"}>
								You can only change your username every 30 days.
							</p>

							<Input addonBefore={"@"} value={this.state.username} maxLength={16}
								   disabled={this.state.loading} onChange={(e) => {
								this.setState({
									username: e.target.value
								});
							}}/>
						</Col>
					</Row>

					<div className={"mt-3 text-center"}>
						<Button type={"primary"} className={"mr-3"} loading={this.state.loading} onClick={(e) => {
							this.setState({
								loading: true
							});

							API.handleRequest("/username", "POST", {
								username: this.state.username
							}, data => {
								message.success("Your username has been changed.");
								Auth.setCurrentUser(BaseObject.convertObject(User, data));

								this.setState({
									loading: false
								});
							}, error => {
								message.error(error);

								this.setState({
									loading: false
								});
							})
						}}>
							Submit
						</Button>

						<Link to={"/account"}>
							<Button type={"default"}>
								Cancel
							</Button>
						</Link>
					</div>
				</Card>
			</Col>
		</Row>;
	}
}