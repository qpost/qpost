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
import {Alert} from "reactstrap";
import FeedEntryListItem from "./FeedEntryListItem";
import FeedEntry from "../../Entity/Feed/FeedEntry";
import User from "../../Entity/Account/User";
import API from "../../API/API";
import BaseObject from "../../Serialization/BaseObject";
import LoadingFeedEntryListItem from "./LoadingFeedEntryListItem";
import Empty from "antd/es/empty";
import "antd/es/empty/style";

export default class FeedEntryList extends Component<{
	user?: User
}, {
	entries: FeedEntry[] | null,
	error: string | null
}> {
	constructor(props) {
		super(props);

		this.state = {
			entries: null,
			error: null
		}
	}

	componentDidMount(): void {
		const parameters = {};

		API.handleRequest("/feed", "GET", parameters, data => {
			let entries: FeedEntry[] = [];

			data.results.forEach(result => entries.push(BaseObject.convertObject(FeedEntry, result)));

			this.setState({entries});
		}, error => {
			this.setState({error});
		});
	}

	render(): React.ReactElement<any, string | React.JSXElementConstructor<any>> | string | number | {} | React.ReactNodeArray | React.ReactPortal | boolean | null | undefined {
		if (this.state.entries !== null) {
			if (this.state.entries.length > 0) {
				return <ul className={"list-group feedContainer"}>
					{this.state.entries.map((entry, i) => {
						return <FeedEntryListItem key={i} entry={entry}/>
					})}
				</ul>;
			} else {
				return <Empty image={Empty.PRESENTED_IMAGE_SIMPLE}/>;
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