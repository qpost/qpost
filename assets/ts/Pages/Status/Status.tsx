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
import FeedEntry from "../../Entity/Feed/FeedEntry";
import API from "../../API/API";
import BaseObject from "../../Serialization/BaseObject";
import ContentBase from "../../Component/Layout/ContentBase";
import PageContent from "../../Component/Layout/PageContent";
import RightSidebar from "../../Component/Layout/RightSidebar";
import Alert from "antd/es/alert";
import "antd/es/alert/style";
import Card from "antd/es/card";
import "antd/es/card/style";
import Skeleton from "antd/es/skeleton";
import "antd/es/skeleton/style";
import User from "../../Entity/Account/User";
import {Link} from "react-router-dom";
import VerifiedBadge from "../../Component/VerifiedBadge";
import FollowButton from "../../Component/FollowButton";
import FeedEntryListItemAttachments from "../../Component/FeedEntry/FeedEntryListItemAttachments";
import FeedEntryActionButtons from "../../Component/FeedEntry/Buttons/FeedEntryActionButtons";
import SuggestedUsers from "../../Component/SuggestedUsers";
import SidebarStickyContent from "../../Component/Layout/SidebarStickyContent";
import SidebarFooter from "../../Parts/Footer/SidebarFooter";

export default class Status extends Component<any, {
	status: FeedEntry,
	error: string | null
}> {
	constructor(props) {
		super(props);

		this.state = {
			status: null,
			error: null
		};
	}

	componentDidMount(): void {
		const id = this.props.match.params.id;

		if (id) {
			API.handleRequest("/status", "GET", {id: id}, (data) => {
				if (data.result) {
					const feedEntry = BaseObject.convertObject(FeedEntry, data.result);

					this.setState({
						status: feedEntry
					});
				} else {
					this.setState({
						error: "An error occurred."
					});
				}
			}, (error) => {
				this.setState({
					error
				});
			});
		} else {
			this.setState({
				error: "An error occurred."
			});
		}
	}

	render(): React.ReactElement<any, string | React.JSXElementConstructor<any>> | string | number | {} | React.ReactNodeArray | React.ReactPortal | boolean | null | undefined {
		const status: FeedEntry = this.state.status;
		const user: User = status ? status.getUser() : null;

		return <ContentBase>
			<PageContent rightSidebar>
				{status !== null && user !== null ? (
					<Card className={"statusPageBox"}>
						<div className={"clearfix"}>
							<Link to={"/" + user.getUsername()} className={"clearUnderline"}>
								<img src={user.getAvatarURL()} className={"rounded float-left mr-2"} width={64}
									 height={64} alt={user.getUsername()}/>
							</Link>

							<div className={"float-left nameContainer"}>
								<div className={"displayName"}>
									<Link to={"/" + user.getUsername()} className={"clearUnderline"}>
										{user.getDisplayName()}<VerifiedBadge target={user}/>
									</Link>
								</div>

								<div className={"username"}>
									<Link to={"/" + user.getUsername()} className={"clearUnderline"}>
										@{user.getUsername()}
									</Link>
								</div>
							</div>

							<div className={"float-right"}>
								<FollowButton target={user}/>
							</div>
						</div>

						{status.getText() !== null ? <div className={"text"}>
							{status.getText()}
						</div> : ""}

						{status.getAttachments().length > 0 ? <div className={"attachments"}>
							<FeedEntryListItemAttachments entry={status}/>
						</div> : ""}

						<div className={"actionButtons"}>
							<FeedEntryActionButtons entry={status}/>
						</div>
					</Card>
				) : this.state.error !== null ? (
					<Alert
						message="Error"
						description={this.state.error}
						type="error"
						showIcon
					/>
				) : (
					<Card className={"statusPageBox"}>
						<Skeleton loading active avatar={{
							size: "large",
							shape: "square"
						}}
								  paragraph={{
									  rows: 4
								  }}/>
					</Card>
				)}
			</PageContent>

			<RightSidebar>
				<SidebarStickyContent>
					<SuggestedUsers/>

					<SidebarFooter/>
				</SidebarStickyContent>
			</RightSidebar>
		</ContentBase>;
	}
}