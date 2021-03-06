/*
 * Copyright (C) 2018-2020 Gigadrive - All rights reserved.
 * https://gigadrivegroup.com
 * https://qpostapp.com
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
import ReplyButton from "./ReplyButton";
import ShareButton from "./ShareButton";
import FavoriteButton from "./FavoriteButton";
import DeleteButton from "./DeleteButton";
import FeedEntryListItem from "../FeedEntryListItem";
import FeedEntry from "../../../api/src/Entity/FeedEntry";

export default class FeedEntryActionButtons extends Component<{
	entry: FeedEntry,
	parent?: FeedEntryListItem,
	reduceMargin?: boolean,
	onEntryUpdate?: (entry: FeedEntry) => void
}, any> {
	constructor(props) {
		super(props);
	}

	render(): React.ReactElement<any, string | React.JSXElementConstructor<any>> | string | number | {} | React.ReactNodeArray | React.ReactPortal | boolean | null | undefined {
		const entry: FeedEntry = this.props.entry;

		return <div className={"row w-100 ml-0"}>
			<div className={"d-inline-block"} onClick={(e) => e.stopPropagation()}>
				<div className={"feedEntryButtonHolder" + (this.props.reduceMargin ? " reduceMargin" : "")}
					 style={this.props.parent ? {
						 marginRight: "-15px",
						 marginLeft: "-15px"
					 } : {}}>
					<ReplyButton entry={entry} parent={this}/>

					<ShareButton entry={entry} parent={this} onEntryUpdate={(entry) => {
						if (this.props.onEntryUpdate) {
							this.props.onEntryUpdate(entry);
						}
					}}/>

					<FavoriteButton entry={entry} parent={this} onEntryUpdate={(entry) => {
						if (this.props.onEntryUpdate) {
							this.props.onEntryUpdate(entry);
						}
					}}/>

					<DeleteButton entry={entry} parent={this}/>
				</div>
			</div>
		</div>;
	}
}