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
import {Row} from "reactstrap";
import {Link, Redirect} from "react-router-dom";
import FeedEntry from "../../Entity/Feed/FeedEntry";
import User from "../../Entity/Account/User";
import FeedEntryType from "../../Entity/Feed/FeedEntryType";
import VerifiedBadge from "../VerifiedBadge";
import FeedEntryActionButtons from "./Buttons/FeedEntryActionButtons";
import TimeAgo from "../TimeAgo";
import FeedEntryListItemAttachments from "./FeedEntryListItemAttachments";
import FeedEntryText from "./FeedEntryText";
import FeedEntryList from "./FeedEntryList";

export default class FeedEntryListItem extends Component<{
	entry: FeedEntry,
	parent?: FeedEntryList,
	hideButtons?: boolean,
	hideAttachments?: boolean
}, {
	nsfwWarningActive: boolean,
	redirect: boolean
}> {
	constructor(props) {
		super(props);

		this.state = {
			nsfwWarningActive: this.props.entry.isNSFW(),
			redirect: false
		};
	}

	redirect = () => {
		this.setState({
			redirect: true
		});
	};

	render(): React.ReactElement<any, string | React.JSXElementConstructor<any>> | string | number | {} | React.ReactNodeArray | React.ReactPortal | boolean | null | undefined {
		const entry: FeedEntry = this.props.entry;
		const user: User = entry.getUser();

		if (this.state.redirect) {
			return <Redirect to={"/status/" + entry.getId()}/>;
		}

		// TODO: Add NSFW warning
		// TODO: Add attachments
		switch (entry.getType()) {
			case FeedEntryType.POST:
				return <li className={"list-group-item px-0 py-0 feedEntry statusTrigger"} onClick={(e) => {
					e.preventDefault();
					this.redirect();
				}}>
					<div className={"px-4 py-2"}>
						<Row>
							<div className={"float-left"}>
								<Link to={"/" + user.getUsername()} className={"clearUnderline float-left"}>
									<img className={"rounded mx-1 my-1"} src={entry.getUser().getAvatarURL()} width={40}
										 height={40} alt={entry.getUser().getUsername()}/>
								</Link>

								<p className={"float-left ml-1 mb-0"}>
									<Link to={"/" + user.getUsername()} className={"clearUnderline"}>
										<span className={"font-weight-bold convertEmoji mr-2"}>
											{user.getDisplayName()}<VerifiedBadge target={user}/>
										</span>
									</Link>

									<span className={"text-muted font-weight-normal"}>
										@{user.getUsername()}
									</span>

									<br/>

									<span className={"small text-muted"}>
										<i className={"far fa-clock"}/> <TimeAgo
										time={entry.getTime()}/>
									</span>
								</p>
							</div>

							{entry.getText() !== null ? <div className="float-left ml-1 my-2 w-100">
								<p className={"mb-0 convertEmoji"} style={{wordWrap: "break-word"}}>
									<FeedEntryText feedEntry={entry}/>
								</p>
							</div> : ""}

							{this.props.hideAttachments && this.props.hideAttachments === true ? "" :
								<FeedEntryListItemAttachments entry={entry}/>}

							{this.props.hideButtons && this.props.hideButtons === true ? "" :
								<FeedEntryActionButtons entry={entry} parent={this}/>}
						</Row>
					</div>
				</li>;
			case FeedEntryType.SHARE:

			case FeedEntryType.NEW_FOLLOWING:

			default:
				return "";
		}
	}
}