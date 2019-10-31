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
import {Row} from "antd";
import PrivacyHomeIcon from "./PrivacyHomeIcon";
import AccountBase from "./AccountBase";

export default class Privacy extends Component<any, any> {
	constructor(props) {
		super(props);

		this.state = {};
	}

	render(): React.ReactElement<any, string | React.JSXElementConstructor<any>> | string | number | {} | React.ReactNodeArray | React.ReactPortal | boolean | null | undefined {
		return <AccountBase activeKey={"PRIVACY"}>
			<Row gutter={24}>
				<PrivacyHomeIcon iconClass={"fas fa-sliders-h"} title={"Privacy level"}
								 description={"Change who may view your content."} path={"/account/privacy/level"}/>
				<PrivacyHomeIcon iconClass={"fas fa-globe"} title={"Sessions"}
								 description={"Manage where you are logged into qpost."} path={"/account/sessions"}/>
				<PrivacyHomeIcon iconClass={"fas fa-ban"} title={"Blocked"}
								 description={"Manage the users you have blocked."} path={"/account/privacy/blocked"}/>
				<PrivacyHomeIcon iconClass={"fas fa-info"} title={"Follow requests"}
								 description={"Manage the follow requests you received."}
								 path={"/account/privacy/requests"}/>
			</Row>
		</AccountBase>;
	}
}