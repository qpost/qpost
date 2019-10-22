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
import Auth from "../../Auth/Auth";
import VerifiedBadge from "../../Component/VerifiedBadge";
import {Link, Redirect} from "react-router-dom";
import API from "../../API/API";
import NightMode from "../../NightMode/NightMode";

export default class DeleteAccount extends Component<any, {
	password: string,
	loading: boolean,
	redirect: boolean
}> {
	constructor(props) {
		super(props);

		this.state = {
			password: "",
			loading: false,
			redirect: false
		};
	}

	render(): React.ReactElement<any, string | React.JSXElementConstructor<any>> | string | number | {} | React.ReactNodeArray | React.ReactPortal | boolean | null | undefined {
		const user = Auth.getCurrentUser();

		return <AccountBase activeKey={"HOME"}>
			{this.state.redirect ? <Redirect to={"/goodbye"} push/> : ""}

			<Row gutter={24}>
				<Col lg={{span: 16, offset: 4}}>
					<Card size={"small"} title={"Deleting your account"} className={"text-center"}>
						<div>
							<img className={"rounded"} width={128} height={128} alt={user.getUsername()}
								 src={user.getAvatarURL()}/>
						</div>

						<div className={"font-weight-bold"}>
							{user.getDisplayName()}<VerifiedBadge target={user}/>
						</div>

						<div className={"text-small text-muted mb-3"}>
							{"@" + user.getUsername()}
						</div>

						<div className={"mb-3"}>
							At qpost we allow you complete control of your data and as such, leave it up to you to
							decide if you want to keep your data on our servers or not.
						</div>

						<div className={"mb-3"}>
							Please make sure you are absolutely certain, that you want to delete your account as this
							process is completely <u>irreversible</u>.
						</div>

						<div className={"mb-3"}>
							This will delete all of your posts, favorites, replies, followers and anything else you have
							ever created on qpost.
						</div>

						<div className={"mb-3 font-weight-bold"}>
							If you have a Gigadrive account linked, this process will <u>NOT</u> delete your Gigadrive
							account. Check the Gigadrive website in order to completely get rid of your data.
						</div>

						<Input.Password placeholder={"Your password"} visibilityToggle={false}
										value={this.state.password} onChange={(e) => {
							this.setState({
								password: e.target.value
							});
						}}/>

						<div className={"mt-3"}>
							<Link to={"/account"} className={"clearUnderline mr-2"}>
								<Button type={"default"}>
									Cancel
								</Button>
							</Link>

							<Button type={"danger"} className={"customDangerButton"} loading={this.state.loading}
									onClick={(e) => {
										e.preventDefault();

										if (!this.state.loading) {
											this.setState({
												loading: true
											});

											API.handleRequest("/user", "DELETE", {
												password: this.state.password
											}, data => {
												NightMode.setActive(false, false);
												Auth.logout(true, true);

												window.location.href = "/goodbye";
											}, error => {
												this.setState({
													loading: false
												});
												message.error(error);
											});
										}
									}}>
								Delete Account
							</Button>
						</div>
					</Card>
				</Col>
			</Row>
		</AccountBase>;
	}
}