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

import {Alert, Button, Card, Col, message, Row, Spin} from "antd";
import API from "../../API";
import React, {Component} from "react";
import {UAParser} from "ua-parser-js";
import Auth from "../../Auth/Auth";
import {convertUserAgentToIconClass} from "../../Util/Format";
import TimeAgo from "../TimeAgo";
import Token from "../../api/src/Entity/Token";
import IPStackResult from "../../api/src/Entity/IPStackResult";

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
		API.i.token.list().then(tokens => {
			this.setState({
				loading: false,
				tokens
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
		return <div>
			{!this.state.loading ? <Row gutter={24}>
				{this.state.tokens.map((token: Token, index: number) => {
					const userAgentString = token.getUserAgent();
					const userAgent: UAParser = userAgentString ? new UAParser(token.getUserAgent()) : null;

					const location: IPStackResult | null = token.getIPStackResult();

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
								<p className={"mb-0"}>
									Last accessed <TimeAgo time={token.getLastAccessTime()}/>
								</p>

								<p className={"mb-0"}>
									Notifications are {token.hasNotifications() ?
									<span className={"text-success"}>enabled</span> :
									<span className={"text-danger"}>disabled</span>}.
								</p>
							</div>

							<Button type={current ? "dashed" : "danger"}
									className={!current ? "customDangerButton" : ""}
									loading={this.isLoading(token.getId())}
									onClick={(e) => {
										e.preventDefault();

										if (!current && !this.isLoading(token.getId())) {
											this.setLoading(token.getId(), true);

											API.i.token.delete(token.getId()).then(() => {
												message.success("The session has been killed.");
												this.setLoading(token.getId(), false, true);
											}).catch(reason => {
												message.error(reason);
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
		</div>
	}
}