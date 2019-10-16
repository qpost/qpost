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
import {Card, Col, Row} from "antd";
import Auth from "../../Auth/Auth";
import {Link} from "react-router-dom";
import Spin from "antd/es/spin";
import API from "../../API/API";
import Alert from "antd/es/alert";

export class AccountInfoPart extends Component<{
	headline: string
}, any> {
	render(): React.ReactElement<any, string | React.JSXElementConstructor<any>> | string | number | {} | React.ReactNodeArray | React.ReactPortal | boolean | null | undefined {
		return <div className={"mb-3"}>
			<p className={"mb-0 font-weight-bold"}>
				{this.props.headline}
			</p>

			<div>
				{this.props.children}
			</div>
		</div>;
	}
}

export default class Account extends Component<any, {
	loading: boolean,
	error: string | null,
	email: string | null,
	gigadriveData: any
}> {
	constructor(props) {
		super(props);

		this.state = {
			loading: true,
			error: null,
			email: null,
			gigadriveData: null
		};
	}

	componentDidMount(): void {
		API.handleRequest("/accountData", "GET", {}, data => {
			this.setState({
				loading: false,
				email: data.email,
				gigadriveData: data.gigadriveData || null
			});
		}, error => {
			this.setState({
				error
			});
		});
	}

	render(): React.ReactElement<any, string | React.JSXElementConstructor<any>> | string | number | {} | React.ReactNodeArray | React.ReactPortal | boolean | null | undefined {
		const user = Auth.getCurrentUser();
		if (!user) return "";

		const birthday = user.getBirthday();

		return <AccountBase activeKey={"HOME"}>
			{!this.state.loading ? <Card size={"small"}>
				<Row gutter={24}>
					<Col md={12} className={"mb-3"}>
						<h4>Account Info</h4>

						<AccountInfoPart headline={"Username"}>
							@{user.getUsername()}

							<Link to={"/account/username"} className={"float-right"}>
								(change)
							</Link>
						</AccountInfoPart>

						<AccountInfoPart headline={"Email Address"}>
							{this.state.email}
						</AccountInfoPart>

						<AccountInfoPart headline={"Date joined"}>
							{new Date(user.getTime()).toLocaleString()}
						</AccountInfoPart>

						<AccountInfoPart headline={"Date of birth"}>
							{birthday ? new Date(birthday).toLocaleDateString() : <em>N/A</em>}
						</AccountInfoPart>

						<AccountInfoPart headline={"Account ID"}>
							{user.getId()}
						</AccountInfoPart>

						{this.state.gigadriveData ? <div>
							<h4>Gigadrive account</h4>

							<AccountInfoPart headline={"Account ID"}>
								{this.state.gigadriveData.id}

								<a href={"https://gigadrivegroup.com/account"} target={"_blank"}
								   className={"float-right"}>
									(manage)
								</a>
							</AccountInfoPart>

							<AccountInfoPart headline={"Date joined"}>
								{new Date(this.state.gigadriveData.joinDate).toLocaleString()}
							</AccountInfoPart>
						</div> : ""}
					</Col>

					<Col md={12}>
						<h4>Connections</h4>

						<AccountInfoPart headline={"Current follow requests"}>
							<Link to={"/requests"}>
								View All
							</Link>
						</AccountInfoPart>

						<AccountInfoPart headline={"Accounts following you"}>
							<Link to={"/" + user.getUsername() + "/followers"}>
								View All
							</Link>
						</AccountInfoPart>

						<AccountInfoPart headline={"Accounts you follow"}>
							<Link to={"/" + user.getUsername() + "/following"}>
								View All
							</Link>
						</AccountInfoPart>

						<AccountInfoPart headline={"Posts you favorited"}>
							<Link to={"/" + user.getUsername() + "/favorites"}>
								View All
							</Link>
						</AccountInfoPart>

						<AccountInfoPart headline={"Accounts you blocked"}>
							<Link to={"/account/privacy/blocked"}>
								View All
							</Link>
						</AccountInfoPart>
					</Col>
				</Row>

				<div className={"mt-5"}>
					<Link to={"/account/delete"}>
						Delete your account
					</Link>
				</div>
			</Card> : this.state.error === null ? <div className={"text-center my-3"}>
				<Spin size={"large"}/>
			</div> : <Alert message={this.state.error} type="error"/>}
		</AccountBase>
	}
}