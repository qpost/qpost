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
import Auth from "../../Auth/Auth";
import Input from "antd/es/input";
import "antd/es/input/style";
import Button from "antd/es/button";
import "antd/es/button/style";
import Card from "antd/es/card";
import "antd/es/card/style";
import PostForm from "./PostForm";

export default class DummyPostForm extends Component<any, any> {
	constructor(props) {
		super(props);
	}

	click = (e) => {
		e.preventDefault();

		PostForm.open();
	};

	render(): React.ReactElement<any, string | React.JSXElementConstructor<any>> | string | number | {} | React.ReactNodeArray | React.ReactPortal | boolean | null | undefined {
		const currentUser = Auth.getCurrentUser();

		if (!currentUser) {
			return "";
		}

		return [
			<Card className={"dummyPostForm desktopOnly mb-3 rounded"} size={"small"} onClick={(e) => this.click(e)}>
				<img src={currentUser.getAvatarURL()} className={"avatar"} alt={currentUser.getUsername()}/>

				<Input placeholder={"Post something for your followers!"} className={"fakeTextBox"}/>
			</Card>,
			<Button type={"primary"} size={"large"} shape={"circle"} className={"mobileOnly"}
					onClick={(e) => this.click(e)} style={{
				position: "fixed",
				zIndex: 500,
				bottom: 90,
				right: 30
			}}>
				<i className="fas fa-pencil-alt"/>
			</Button>
		];
	}
}