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
import Block from "../../Entity/Account/Block";
import API from "../../API/API";
import BaseObject from "../../Serialization/BaseObject";
import {Alert} from "reactstrap";
import Empty from "antd/es/empty";
import {Row, Spin} from "antd";
import FollowerListItem from "../../Component/User/FollowerListItem";
import InfiniteScroll from "react-infinite-scroller";
import LoadingFollowerListItem from "../../Component/User/LoadingFollowerListItem";

export default class Blocked extends Component<any, {
	blocks: Block[] | null,
	error: string | null,
	loadingMore: boolean,
	hasMore: boolean
}> {
	public static instance: Blocked | null = null;

	constructor(props) {
		super(props);

		this.state = {
			blocks: null,
			error: null,
			loadingMore: false,
			hasMore: true
		};
	}

	componentDidMount(): void {
		Blocked.instance = this;
		this.load();
	}

	componentWillUnmount(): void {
		Blocked.instance = null;
	}

	public prependUser(block: Block): void {
		const blocks: Block[] = this.state.blocks || [];

		blocks.unshift(block);

		this.setState({blocks});
	}

	load(max?: number) {
		const parameters = {};

		if (max) parameters["max"] = max;

		API.handleRequest("/blocks", "GET", parameters, data => {
			let blocks: Block[] = this.state.blocks || [];

			data.results.forEach(result => blocks.push(BaseObject.convertObject(Block, result)));

			this.setState({
				blocks,
				loadingMore: false,
				hasMore: data.results.length === 0 ? false : this.state.hasMore
			});
		}, error => {
			this.setState({error, loadingMore: false, hasMore: false});
		});
	}

	loadMore() {
		if (!this.state.loadingMore && this.state.blocks.length > 0 && this.state.hasMore) {
			const lastId = this.state.blocks[this.state.blocks.length - 1].getId();

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
			<h4>Blocked accounts</h4>

			{this.state.blocks !== null ? <div>
				{this.state.blocks.length > 0 ? <InfiniteScroll
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
							{this.state.blocks.map((block, i) => {
								return <FollowerListItem key={i} user={block.getTarget()}/>;
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