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
import FeedEntry from "../../Entity/Feed/FeedEntry";
import API from "../../API/API";
import BaseObject from "../../Serialization/BaseObject";
import InfiniteScroll from "react-infinite-scroller";
import {Card, Spin} from "antd";
import FeedEntryListItem from "./FeedEntryListItem";
import {Alert} from "reactstrap";
import LoadingFeedEntryListItem from "./LoadingFeedEntryListItem";

export default class ReplyList extends Component<{
	feedEntry: FeedEntry
}, {
	entries: FeedEntry[][] | null,
	error: string | null,
	loadingMore: boolean,
	hasMore: boolean,
	loadNewTask: any,
	page: number
}> {
	public static instance: ReplyList | null = null;

	constructor(props) {
		super(props);

		this.state = {
			entries: null,
			error: null,
			loadingMore: false,
			hasMore: true,
			loadNewTask: null,
			page: 1
		};
	}

	componentDidMount(): void {
		ReplyList.instance = this;
		this.load();
	}

	componentWillUnmount(): void {
		ReplyList.instance = null;
	}

	load() {
		API.handleRequest("/replies", "GET", {
			feedEntry: this.props.feedEntry.getId(),
			page: this.state.page
		}, data => {
			const entries = this.state.entries || [];

			data.results.forEach(replyBatch => {
				const finalReplyBatch: FeedEntry[] = [];

				replyBatch.forEach(reply => {
					finalReplyBatch.push(BaseObject.convertObject(FeedEntry, reply));
				});

				entries.push(finalReplyBatch);
			});

			this.setState({
				entries,
				loadingMore: false,
				hasMore: data.results.length === 0 ? false : this.state.hasMore,
				page: this.state.page + 1
			});
		}, error => {
			this.setState({error, loadingMore: false, hasMore: false});
		});
	}

	loadMore() {
		if (!this.state.loadingMore && this.state.entries.length > 0 && this.state.hasMore) {
			this.setState({
				loadingMore: true
			});

			this.load();
		}
	}

	public prependEntry(feedEntry: FeedEntry): void {
		const entries: FeedEntry[][] = this.state.entries || [];
		const parent = feedEntry.getPost();

		if (parent.getId() !== this.props.feedEntry.getId()) {
			entries.forEach((replyBatch: FeedEntry[]) => {
				if (replyBatch.length > 0) {
					const lastReply = replyBatch[replyBatch.length - 1];

					if (parent.getId() === lastReply.getId()) {
						replyBatch.push(feedEntry);
					}
				}
			});
		} else {
			entries.unshift([feedEntry]);
		}

		this.setState({entries});
	}

	render(): React.ReactElement<any, string | React.JSXElementConstructor<any>> | string | number | {} | React.ReactNodeArray | React.ReactPortal | boolean | null | undefined {
		if (this.props.feedEntry !== null && this.props.feedEntry.getReplyCount() === 0) return "";

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
					<Card size={"small"}>
						<h4 className={"text-center-mobile"}>Replies</h4>
					</Card>

					<ul className={"list-group feedContainer"}>
						{this.state.entries.map((replyBatch: FeedEntry[], i) => {
							return <div key={i} className={"mb-2"}>
								{replyBatch.map((reply: FeedEntry, ri) => {
									return <FeedEntryListItem key={reply.getId()} entry={reply} parent={this}
															  showParentInfo={true}/>
								})}
							</div>;
						})}
					</ul>
				</InfiniteScroll>;
			} else {
				return "";
			}
		} else if (this.state.error !== null) {
			return <Alert color={"danger"}>{this.state.error}</Alert>;
		} else {
			const rows = [];
			for (let i = 0; i < 20; i++) {
				rows.push(<LoadingFeedEntryListItem key={i}/>);
			}

			return <div>
				<Card size={"small"}>
					<h4>Replies</h4>
				</Card>

				<ul className={"list-group feedContainer"}>
					{rows.map((item, i) => {
						return item;
					})}
				</ul>
			</div>;
			/*return <div className={"text-center my-3"}>
				<Spinner type={"grow"} color={NightMode.spinnerColor()} size={"lg"}/>
			</div>*/
		}
	}
}