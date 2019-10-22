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
import ContentBase from "../../Component/Layout/ContentBase";
import PageContent from "../../Component/Layout/PageContent";
import {Col, Input, Menu, Row} from "antd";
import NightMode from "../../NightMode/NightMode";
import FeedEntryList from "../../Component/FeedEntry/FeedEntryList";
import FollowerList from "../../Component/User/FollowerList";

export default class Search extends Component<any, {
	query: string,
	activeMenuPoint: string
}> {
	// TODO: Change URL when searching
	constructor(props) {
		super(props);

		this.state = {
			query: "",
			activeMenuPoint: "POSTS",
		};
	}

	render(): React.ReactElement<any, string | React.JSXElementConstructor<any>> | string | number | {} | React.ReactNodeArray | React.ReactPortal | boolean | null | undefined {
		return <ContentBase>
			<PageContent>
				<Row gutter={24}>
					<Col md={{span: 12, offset: 6}}>
						<Input.Search placeholder="enter a query" onSearch={value => {
							const query = value.trim();

							this.setState({
								query
							});
						}} enterButton size={"large"} className={"mb-3"}/>

						{this.state.query !== "" ? <div>
							<Menu onClick={(e) => {
								this.setState({
									activeMenuPoint: e.key
								});
							}} selectedKeys={[this.state.activeMenuPoint]} mode={"horizontal"}
								  theme={NightMode.isActive() ? "dark" : "light"}>
								<Menu.Item key={"POSTS"}>
									Posts
								</Menu.Item>

								<Menu.Item key={"USERS"}>
									Users
								</Menu.Item>
							</Menu>

							{this.state.activeMenuPoint === "POSTS" ?
								<FeedEntryList searchQuery={this.state.query} disableTask={true}/> :
								<FollowerList mode={"search"} query={this.state.query}/>}
						</div> : ""}
					</Col>
				</Row>
			</PageContent>
		</ContentBase>;
	}
}