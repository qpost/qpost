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
import Auth from "../../Auth/Auth";
import Level from "../../Entity/Account/PrivacyLevel";
import {Button, Card, message, Radio} from "antd";
import "antd/es/radio/style";
import ReturnHeader from "../../Component/ReturnHeader";
import API from "../../API/API";
import BaseObject from "../../Serialization/BaseObject";
import User from "../../Entity/Account/User";

export default class PrivacyLevel extends Component<any, {
	privacyLevel: string,
	loading: boolean
}> {
	constructor(props) {
		super(props);

		this.state = {
			privacyLevel: Auth.getCurrentUser().getPrivacyLevel(),
			loading: false
		};
	}

	render(): React.ReactElement<any, string | React.JSXElementConstructor<any>> | string | number | {} | React.ReactNodeArray | React.ReactPortal | boolean | null | undefined {
		const radioStyle = {
			display: "block",
			height: "30px",
			lineHeight: "30px"
		};

		return <AccountBase activeKey={"PRIVACY"}>
			<ReturnHeader className={"mb-2"}/>

			<Card size={"small"} title={"Privacy level"}>
				<div className={"mb-3"}>
					<Radio.Group onChange={(e) => {
						const newLevel = e.target.value;

						this.setState({
							privacyLevel: newLevel
						});
					}} value={this.state.privacyLevel}>
						<Radio style={radioStyle} value={Level.PUBLIC}>
							<b>Public</b> - everyone can see your profile and interact with your content
						</Radio>

						<Radio style={radioStyle} value={Level.PRIVATE}>
							<b>Private</b> - only your followers can see your profile and interact with your content,
							new followers have to be confirmed
						</Radio>

						<Radio style={radioStyle} value={Level.CLOSED}>
							<b>Closed</b> - only you can see your profile, nobody can interact with your content
						</Radio>
					</Radio.Group>
				</div>

				<Button type={"primary"} loading={this.state.loading} onClick={(e) => {
					if (!this.state.loading) {
						this.setState({loading: true});

						API.handleRequest("/privacyLevel", "POST", {
							level: this.state.privacyLevel
						}, data => {
							Auth.setCurrentUser(BaseObject.convertObject(User, data.result));

							this.setState({loading: false});
							message.success("The changes have been saved.");
						}, error => {
							this.setState({loading: false});

							message.error(error);
						});
					}
				}}>Save changes</Button>
			</Card>
		</AccountBase>;
	}
}