import React, {Component} from "react";
import FeedEntry from "../Entity/Feed/FeedEntry";
import FeedEntryType from "../Entity/Feed/FeedEntryType";
import {Row} from "reactstrap";
import {Link} from "react-router-dom";
import User from "../Entity/Account/User";
import VerifiedBadge from "./VerifiedBadge";

export default class FeedEntryListItem extends Component<{
	entry: FeedEntry
}, {
	nsfwWarningActive: boolean
}> {
	constructor(props) {
		super(props);

		this.state = {
			nsfwWarningActive: this.props.entry.isNSFW()
		};
	}

	render(): React.ReactElement<any, string | React.JSXElementConstructor<any>> | string | number | {} | React.ReactNodeArray | React.ReactPortal | boolean | null | undefined {
		const entry: FeedEntry = this.props.entry;
		const user: User = entry.getUser();
		console.log(entry, user);

		// TODO: Add NSFW warning
		// TODO: Add attachments
		switch (entry.getType()) {
			case FeedEntryType.POST:
				return <li className={"list-group-item px-0 py-0 feedEntry statusTrigger"}>
					<div className={"px-4 py-2"}>
						<Row>
							<div className={"float-left"}>
								<Link to={"/" + user.getUsername()} className={"clearUnderline float-left"}>
									<img className={"rounded mx-1 my-1"} src={entry.getUser().getAvatarURL()} width={40}
										 height={40} alt={entry.getUser().getUsername()}/>
								</Link>

								<p className={"float-left ml-1 mb-0"}>
									<Link to={"/" + user.getUsername()} className={"clearUnderline"}>
										<span className={"font-weight-bold convertEmoji"}>
											{user.getDisplayName()}<VerifiedBadge target={user}/>
										</span>
									</Link>

									<span className={"text-muted font-weight-normal"}>
										@{user.getUsername()}
									</span>

									<br/>

									<span className={"small text-muted"}>
										<i className={"far fa-clock"}/> {entry.getTime().getTimestamp()} {/* TODO: Add timeago */}
									</span>
								</p>
							</div>


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