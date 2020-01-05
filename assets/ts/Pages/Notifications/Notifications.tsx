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
import ContentBase from "../../Component/Layout/ContentBase";
import PageContent from "../../Component/Layout/PageContent";
import SidebarStickyContent from "../../Component/Layout/SidebarStickyContent";
import SuggestedUsers from "../../Component/SuggestedUsers";
import SidebarFooter from "../../Parts/Footer/SidebarFooter";
import RightSidebar from "../../Component/Layout/RightSidebar";
import Notification from "../../Entity/Feed/Notification";
import Alert from "antd/es/alert";
import Spin from "antd/es/spin";
import API from "../../API/API";
import BaseObject from "../../Serialization/BaseObject";
import Empty from "antd/es/empty";
import InfiniteScroll from "react-infinite-scroller";
import NotificationType from "../../Entity/Feed/NotificationType";
import NewFollowerNotification from "./NewFollowerNotification";
import FavoriteNotification from "./FavoriteNotification";
import MentionNotification from "./MentionNotification";
import ReplyNotification from "./ReplyNotification";
import ShareNotification from "./ShareNotification";
import BadgeStatus from "../../Auth/BadgeStatus";
import {setPageTitle} from "../../Util/Page";
import HomeFeedProfileBox from "../Home/HomeFeedProfileBox";
import TrendingTopics from "../../Component/TrendingTopics";
import PageHeader from "../../Component/PageHeader";
import LoadingFeedEntryListItem from "../../Component/FeedEntry/LoadingFeedEntryListItem";
import UpcomingBirthdays from "../../Component/UpcomingBirthdays";

export default class Notifications extends Component<any, {
	notifications: Notification[],
	error: string,
	loadingMore: boolean,
	hasMore: boolean
}> {
	constructor(props) {
		super(props);

		this.state = {
			notifications: null,
			error: null,
			loadingMore: false,
			hasMore: true
		}
	}

	componentDidMount(): void {
		this.load();

		setPageTitle("Notifications");
	}

	load(max?: number) {
		API.handleRequest("/notifications", "GET", max ? {max} : {}, data => {
			let notifications: Notification[] = this.state.notifications || [];

			data.results.forEach(result => notifications.push(BaseObject.convertObject(Notification, result)));

			this.setState({
				notifications,
				loadingMore: false,
				hasMore: data.results.length === 0 ? false : this.state.hasMore
			});

			BadgeStatus.update();
		}, error => {
			this.setState({error, loadingMore: false, hasMore: false});
		});
	}

	loadMore() {
		if (!this.state.loadingMore && this.state.notifications.length > 0 && this.state.hasMore) {
			const lastId = this.state.notifications[this.state.notifications.length - 1].getId();

			this.setState({
				loadingMore: true
			});

			this.load(lastId);
		}
	}

	render(): React.ReactElement<any, string | React.JSXElementConstructor<any>> | string | number | {} | React.ReactNodeArray | React.ReactPortal | boolean | null | undefined {
		const rows = [];
		for (let i = 0; i < 20; i++) {
			rows.push(<LoadingFeedEntryListItem key={i}/>);
		}

		return <ContentBase>
			<RightSidebar>
				<SidebarStickyContent>
					<HomeFeedProfileBox/>
					<TrendingTopics limit={5}/>
				</SidebarStickyContent>
			</RightSidebar>

			<PageContent leftSidebar rightSidebar>
				<PageHeader title={"Notifications"} iconClass={"fas fa-bell"}/>
				{this.state.notifications !== null ?
					<div>
						{this.state.notifications.length > 0 ? <InfiniteScroll
							pageStart={1}
							loadMore={() => {
								this.loadMore();
							}}
							hasMore={this.state.hasMore}
							loader={<div className={"text-center my-3" + (!this.state.loadingMore ? " d-none" : "")}>
								<Spin size={"large"}/>
							</div>}
							initialLoad={false}>
							{this.state.notifications.map((notification: Notification, i: number) => {
								let content = null;
								switch (notification.getType()) {
									case NotificationType.NEW_FOLLOWER:
										content = <NewFollowerNotification key={i} notification={notification}/>;
										break;
									case NotificationType.FAVORITE:
										content = <FavoriteNotification key={i} notification={notification}/>;
										break;
									case NotificationType.MENTION:
										content = <MentionNotification key={i} notification={notification}/>;
										break;
									case NotificationType.REPLY:
										content = <ReplyNotification key={i} notification={notification}/>;
										break;
									case NotificationType.SHARE:
										content = <ShareNotification key={i} notification={notification}/>;
										break;
								}

								return content || "";
							})}
						</InfiniteScroll> : <Empty image={Empty.PRESENTED_IMAGE_SIMPLE}/>}
					</div> :
					this.state.error !== null ? <Alert message={this.state.error} type="error"/> :
						<ul className={"list-group feedContainer"}>
							{rows.map((item, i) => {
								return item;
							})}
						</ul>}
			</PageContent>

			<RightSidebar>
				<SidebarStickyContent>
					<SuggestedUsers/>

					<UpcomingBirthdays/>

					<SidebarFooter/>
				</SidebarStickyContent>
			</RightSidebar>
		</ContentBase>;
	}
}