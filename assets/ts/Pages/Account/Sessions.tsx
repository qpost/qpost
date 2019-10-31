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
import {Button, Card, Col, message, Row} from "antd";
import Auth from "../../Auth/Auth";
import Spin from "antd/es/spin";
import API from "../../API/API";
import Alert from "antd/es/alert";
import Token from "../../Entity/Account/Token";
import BaseObject from "../../Serialization/BaseObject";
import {UAParser} from "ua-parser-js";
import {convertUserAgentToIconClass} from "../../Util/Format";
import IpStackResult from "../../Entity/Account/IpStackResult";
import TimeAgo from "../../Component/TimeAgo";

export default class Sessions extends Component<any, {
	loading: boolean,
	error: string | null,
	tokens: Token[],
	loadingTokens: string[]
}> {
	constructor(props) {
		super(props);

		this.state = {
			loading: true,
			error: null,
			tokens: [],
			loadingTokens: []
		};
	}

	componentDidMount(): void {
		API.handleRequest("/token", "GET", {}, data => {
			const tokens = this.state.tokens;

			data.results.forEach(token => {
				tokens.push(BaseObject.convertObject(Token, token));
			});

			this.setState({
				loading: false,
				tokens
			});
		}, error => {
			this.setState({
				error
			});
		});
	}

	isLoading(id: string) {
		return this.state.loadingTokens.includes(id);
	}

	setLoading(id: string, loading: boolean, remove?: boolean) {
		const tokens = this.state.tokens;
		const loadingTokens = this.state.loadingTokens;

		if (loading && !this.isLoading(id)) {
			loadingTokens.push(id);
		} else if (!loading) {
			const index = loadingTokens.indexOf(id, 0);
			if (index > -1) {
				loadingTokens.splice(index, 1);
			}
		}

		if (remove === true) {
			let index = -1;

			for (let i = 0; i < tokens.length; i++) {
				const token = tokens[i];
				if (token.getId() === id) {
					index = i;
					break;
				}
			}

			if (index > -1) {
				tokens.splice(index, 1);
			}
		}

		this.setState({
			loadingTokens,
			tokens
		});
	}

	render(): React.ReactElement<any, string | React.JSXElementConstructor<any>> | string | number | {} | React.ReactNodeArray | React.ReactPortal | boolean | null | undefined {
		const user = Auth.getCurrentUser();
		if (!user) return "";

		return <AccountBase activeKey={"SESSIONS"}>
			{!this.state.loading ? <Row gutter={24}>
				{this.state.tokens.map((token: Token, index: number) => {
					const userAgentString = token.getUserAgent();
					const userAgent: UAParser = userAgentString ? new UAParser(token.getUserAgent()) : null;

					const location: IpStackResult | null = token.getIPStackResult();

					const current: boolean = token.getId() === Auth.getToken();

					return <Col md={12}>
						<Card size={"small"} key={index} className={"mb-md-2"}>
							<div className={"clearfix"}>
								<div className={"float-left"}>
									<i className={convertUserAgentToIconClass(userAgent)} style={{fontSize: "3em"}}/>
								</div>

								<div className={"float-left ml-3"}>
									{userAgent.getBrowser().name + " " + userAgent.getBrowser().version + " | " + userAgent.getOS().name + " " + userAgent.getOS().version}<br/>
									{location ? location.getCity() + ", " + location.getCountryName() : ""}
								</div>
							</div>

							<div className={"my-3"}>
								Last accessed <TimeAgo time={token.getLastAccessTime()}/>
							</div>

							<Button type={current ? "dashed" : "danger"}
									className={!current ? "customDangerButton" : ""}
									loading={this.isLoading(token.getId())}
									onClick={(e) => {
										e.preventDefault();

										if (!current && !this.isLoading(token.getId())) {
											this.setLoading(token.getId(), true);

											API.handleRequest("/token", "DELETE", {
												id: token.getId()
											}, data => {
												message.success("The session has been killed.");
												this.setLoading(token.getId(), false, true);
											}, error => {
												message.error(error);
												this.setLoading(token.getId(), false);
											});
										}
									}}>
								{current ? "Current session" : "Logout"}
							</Button>
						</Card>
					</Col>;
				})}
			</Row> : this.state.error === null ? <div className={"text-center my-3"}>
				<Spin size={"large"}/>
			</div> : <Alert message={this.state.error} type="error"/>}
		</AccountBase>
	}
}