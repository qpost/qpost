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
import User from "../../Entity/Account/User";
import Follower from "../../Entity/Account/Follower";
import API from "../../API/API";
import BaseObject from "../../Serialization/BaseObject";
import InfiniteScroll from "react-infinite-scroller";
import {Row, Spin} from "antd";
import Empty from "antd/es/empty";
import {Alert} from "reactstrap";
import FollowerListItem from "./FollowerListItem";
import LoadingFollowerListItem from "./LoadingFollowerListItem";

export default class FollowerList extends Component<{
	user: User,
	mode: "from" | "to"
}, {
	followers: Follower[] | null,
	error: string | null,
	loadingMore: boolean,
	hasMore: boolean
}> {
	public static instance: FollowerList | null = null;

	constructor(props) {
		super(props);

		this.state = {
			followers: null,
			error: null,
			loadingMore: false,
			hasMore: true
		};
	}

	componentDidMount(): void {
		FollowerList.instance = this;
		this.load();
	}

	componentWillUnmount(): void {
		FollowerList.instance = null;
	}

	public prependUser(user: Follower): void {
		const followers: Follower[] = this.state.followers || [];

		followers.unshift(user);

		this.setState({followers});
	}

	load(max?: number) {
		const parameters = this.props.user ? {
			[this.props.mode]: this.props.user.getId()
		} : {};

		if (max) parameters["max"] = max;

		API.handleRequest("/follows", "GET", parameters, data => {
			let followers: Follower[] = this.state.followers || [];

			data.results.forEach(result => followers.push(BaseObject.convertObject(Follower, result)));

			this.setState({
				followers,
				loadingMore: false,
				hasMore: data.results.length === 0 ? false : this.state.hasMore
			});
		}, error => {
			this.setState({error, loadingMore: false, hasMore: false});
		});
	}

	loadMore() {
		if (!this.state.loadingMore && this.state.followers.length > 0 && this.state.hasMore) {
			const lastId = this.state.followers[this.state.followers.length - 1].getId();

			this.setState({
				loadingMore: true
			});

			this.load(lastId);
		}
	}

	render(): React.ReactElement<any, string | React.JSXElementConstructor<any>> | string | number | {} | React.ReactNodeArray | React.ReactPortal | boolean | null | undefined {
		if (this.state.followers !== null) {
			if (this.state.followers.length > 0) {
				return <InfiniteScroll
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
							{this.state.followers.map((follower, i) => {
								return <FollowerListItem key={i}
														 user={this.props.mode === "from" ? follower.getReceiver() : follower.getSender()}/>;
							})}
						</Row>
					</ul>
				</InfiniteScroll>;
			} else {
				return <Empty image={Empty.PRESENTED_IMAGE_SIMPLE}/>;
			}
		} else if (this.state.error !== null) {
			return <Alert color={"danger"}>{this.state.error}</Alert>;
		} else {
			const rows = [];
			for (let i = 0; i < 20; i++) {
				rows.push(<LoadingFollowerListItem key={i}/>);
			}

			return <ul className={"list-group feedContainer"}>
				<Row gutter={24}>
					{rows.map((item, i) => {
						return item;
					})}
				</Row>
			</ul>;
			/*return <div className={"text-center my-3"}>
				<Spinner type={"grow"} color={NightMode.spinnerColor()} size={"lg"}/>
			</div>*/
		}
	}
}