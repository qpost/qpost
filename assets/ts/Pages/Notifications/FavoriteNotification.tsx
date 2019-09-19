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
import Notification from "../../Entity/Feed/Notification";
import {Card} from "antd";
import {Link} from "react-router-dom";
import VerifiedBadge from "../../Component/VerifiedBadge";
import FeedEntryListItem from "../../Component/FeedEntry/FeedEntryListItem";

export default class FavoriteNotification extends Component<{
	notification: Notification
}, any> {
	constructor(props) {
		super(props);
	}

	render(): React.ReactElement<any, string | React.JSXElementConstructor<any>> | string | number | {} | React.ReactNodeArray | React.ReactPortal | boolean | null | undefined {
		const notification = this.props.notification;
		const user = notification.getReferencedUser();
		const feedEntry = notification.getReferencedFeedEntry();

		if (feedEntry && user) {
			return <Card size={"small"} className={!notification.isSeen() ? "unseenNotification" : ""}>
				<p className={"mb-0"}>
					<i className={"fas fa-star text-warning"}/> <Link to={"/profile/" + user.getUsername()}
																	  className={"font-weight-bold clearUnderline"}><img
					src={user.getAvatarURL()} width={24} height={24} className={"rounded mr-1"}
					alt={user.getUsername()}/>{user.getDisplayName()}</Link><VerifiedBadge target={user}/> favorited
					your post.
				</p>

				<hr/>

				<FeedEntryListItem entry={feedEntry} hideAttachments={true} hideButtons={true}/>
			</Card>;
		} else {
			return "";
		}
	}
}