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
import FollowRequest from "../../Entity/Account/FollowRequest";
import API from "../../API/API";
import BaseObject from "../../Serialization/BaseObject";
import LoadingFollowerListItem from "../../Component/User/LoadingFollowerListItem";
import ReturnHeader from "../../Component/ReturnHeader";
import InfiniteScroll from "react-infinite-scroller";
import {Row, Spin} from "antd";
import FollowerListItem from "../../Component/User/FollowerListItem";
import Empty from "antd/es/empty";
import {Alert} from "reactstrap";

export default class FollowRequests extends Component<any, {
	requests: FollowRequest[] | null,
	error: string | null,
	loadingMore: boolean,
	hasMore: boolean
}> {
	public static instance: FollowRequests | null = null;

	constructor(props) {
		super(props);

		this.state = {
			requests: null,
			error: null,
			loadingMore: false,
			hasMore: true
		};
	}

	componentDidMount(): void {
		FollowRequests.instance = this;
		this.load();
	}

	componentWillUnmount(): void {
		FollowRequests.instance = null;
	}

	load(max?: number) {
		const parameters = {};

		if (max) parameters["max"] = max;

		API.handleRequest("/followRequest", "GET", parameters, data => {
			let requests: FollowRequest[] = this.state.requests || [];

			data.results.forEach(result => requests.push(BaseObject.convertObject(FollowRequest, result)));

			this.setState({
				requests,
				loadingMore: false,
				hasMore: data.results.length === 0 ? false : this.state.hasMore
			});
		}, error => {
			this.setState({error, loadingMore: false, hasMore: false});
		});
	}

	loadMore() {
		if (!this.state.loadingMore && this.state.requests.length > 0 && this.state.hasMore) {
			const lastId = this.state.requests[this.state.requests.length - 1].getId();

			this.setState({
				loadingMore: true
			});

			this.load(lastId);
		}
	}

	render(): React.ReactElement<any, string | React.JSXElementConstructor<any>> | string | number | {} | React.ReactNodeArray | React.ReactPortal | boolean | null | undefined {
		const rows = [];
		for (let i = 0; i < 20; i++) {
			rows.push(<LoadingFollowerListItem key={i}/>);
		}

		return <AccountBase activeKey={"PRIVACY"}>
			<ReturnHeader className={"mb-2"}/>

			<h4>Follow requests</h4>

			{this.state.requests !== null ? <div>
				{this.state.requests.length > 0 ? <InfiniteScroll
					pageStart={1}
					loadMore={() => {
						this.loadMore();
					}}
					hasMore={this.state.hasMore}
					loader={<div className={"text-center my-3" + (!this.state.loadingMore ? " d-none" : "")}>
						<Spin size={"large"}/>
					</div>}
					initialLoad={false}
				>
					<ul className={"list-group feedContainer"}>
						<Row gutter={24}>
							{this.state.requests.map((request, i) => {
								return <FollowerListItem key={i} user={request.getSender()}
														 requestId={request.getId()}/>;
							})}
						</Row>
					</ul>
				</InfiniteScroll> : <Empty description={"No users found."}/>}
			</div> : this.state.error !== null ? <Alert color={"danger"}>{this.state.error}</Alert> :
				<ul className={"list-group feedContainer"}>
					<Row gutter={24}>
						{rows.map((item, i) => {
							return item;
						})}
					</Row>
				</ul>}
		</AccountBase>;
	}
}