/*
 * Copyright (C) 2018-2020 Gigadrive - All rights reserved.
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
import {Alert} from "reactstrap";
import FeedEntryListItem from "./FeedEntryListItem";
import FeedEntry from "../../Entity/Feed/FeedEntry";
import User from "../../Entity/Account/User";
import API from "../../API/API";
import BaseObject from "../../Serialization/BaseObject";
import LoadingFeedEntryListItem from "./LoadingFeedEntryListItem";
import Empty from "antd/es/empty";
import "antd/es/empty/style";
import InfiniteScroll from "react-infinite-scroller";
import {Spin} from "antd";

export default class FeedEntryList extends Component<{
	user?: User,
	searchQuery?: string,
	disableTask?: boolean
}, {
	entries: FeedEntry[] | null,
	error: string | null,
	loadingMore: boolean,
	hasMore: boolean,
	loadNewTask: any,
	privateWarning: boolean
}> {
	public static instance: FeedEntryList | null = null;

	constructor(props) {
		super(props);

		this.state = {
			entries: null,
			error: null,
			loadingMore: false,
			hasMore: true,
			loadNewTask: null,
			privateWarning: false
		}
	}

	componentDidMount(): void {
		FeedEntryList.instance = this;
		this.load();
	}

	componentWillUnmount(): void {
		if (this.state.loadNewTask) {
			clearTimeout(this.state.loadNewTask);
		}

		FeedEntryList.instance = null;
	}

	componentDidUpdate(prevProps: Readonly<{ user?: User; searchQuery?: string }>, prevState: Readonly<{ entries: FeedEntry[] | null; error: string | null; loadingMore: boolean; hasMore: boolean; loadNewTask: any }>, snapshot?: any): void {
		if (this.props.user !== prevProps.user || this.props.searchQuery !== prevProps.searchQuery) {
			this.setState({
				entries: null,
				error: null,
				loadingMore: true,
				hasMore: true
			});

			this.load();
		}
	}

	public prependEntry(feedEntry: FeedEntry): void {
		const entries: FeedEntry[] = this.state.entries || [];

		entries.unshift(feedEntry);

		this.setState({entries});
	}

	loadNew() {
		if (this.state.entries === null || this.state.entries.length === 0) return;

		const parameters = this.props.user ? {
			user: this.props.user.getId()
		} : {};

		parameters["min"] = this.state.entries[0].getId();

		API.handleRequest("/feed", "GET", parameters, data => {
			let entries: FeedEntry[] = [];

			data.results.forEach(result => {
				const feedEntry: FeedEntry = BaseObject.convertObject(FeedEntry, result);

				for (let i = 0; i < this.state.entries.length; i++) {
					const entry = this.state.entries[i];
					if (entry.getId() === feedEntry.getId()) return;
				}

				entries.push(feedEntry);
			});

			if (this.state.entries) {
				this.state.entries.forEach(entry => entries.push(entry));
			}

			this.setState({
				entries,
				loadingMore: false
			});

			this.loadNewTask();
		}, error => {
			this.setState({error, loadingMore: false});
			this.loadNewTask();
		});
	}

	load(max?: number) {
		const parameters = this.props.user ? {
			user: this.props.user.getId()
		} : {};

		if (max) parameters["max"] = max;
		if (this.props.searchQuery) {
			parameters["type"] = "post";
			parameters["query"] = this.props.searchQuery;
			if (this.state.entries && this.state.entries.length != 0) parameters["offset"] = this.state.entries.length;
		}

		API.handleRequest(this.props.searchQuery ? "/search" : "/feed", "GET", parameters, data => {
			let entries: FeedEntry[] = this.state.entries || [];

			data.results.forEach(result => entries.push(BaseObject.convertObject(FeedEntry, result)));

			this.setState({
				entries,
				loadingMore: false,
				hasMore: data.results.length === 0 ? false : this.state.hasMore
			});

			this.loadNewTask();
		}, error => {
			if (error === "You are not allowed to view this resource.") {
				this.setState({error, loadingMore: false, hasMore: false, privateWarning: true});
			} else {
				this.setState({error, loadingMore: false, hasMore: false});
			}
		});
	}

	private loadNewTask(): void {
		if (FeedEntryList.instance !== this || this.props.searchQuery || this.props.disableTask) return;

		this.setState({
			loadNewTask: setTimeout(() => {
				this.loadNew();
			}, 5000)
		});
	}

	loadMore() {
		if (!this.state.loadingMore && this.state.entries.length > 0 && this.state.hasMore) {
			const lastId = this.state.entries[this.state.entries.length - 1].getId();

			this.setState({
				loadingMore: true
			});

			this.load(lastId);
		}
	}

	render(): React.ReactElement<any, string | React.JSXElementConstructor<any>> | string | number | {} | React.ReactNodeArray | React.ReactPortal | boolean | null | undefined {
		if (this.state.privateWarning) {
			return <div className={"text-center my-5"}>
				<h4>This user has set their profile private.</h4>

				<p>You need to be a follower to view their posts.</p>
			</div>;
		}

		if (this.state.entries !== null) {
			if (this.state.entries.length > 0) {
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
						{this.state.entries.map((entry, i) => {
							return <FeedEntryListItem key={entry.getId()} entry={entry} parent={this}
													  showParentInfo={true}/>
						})}
					</ul>
				</InfiniteScroll>;
			} else {
				return <Empty description={"No posts found."}/>;
			}
		} else if (this.state.error !== null) {
			return <Alert color={"danger"}>{this.state.error}</Alert>;
		} else {
			const rows = [];
			for (let i = 0; i < 20; i++) {
				rows.push(<LoadingFeedEntryListItem key={i}/>);
			}

			return <ul className={"list-group feedContainer"}>
				{rows.map((item, i) => {
					return item;
				})}
			</ul>;
			/*return <div className={"text-center my-3"}>
				<Spinner type={"grow"} color={NightMode.spinnerColor()} size={"lg"}/>
			</div>*/
		}
	}
}