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
import FeedEntry from "../../../Entity/Feed/FeedEntry";
import Auth from "../../../Auth/Auth";
import {formatNumberShort} from "../../../Util/Format";

export default class ShareButton extends Component<{
	entry: FeedEntry
}, {
	shared: boolean
}> {
	constructor(props) {
		super(props);

		this.state = {
			shared: this.props.entry.isShared()
		}
	}

	click = (e) => {
		e.preventDefault();

		if (!this.isSelf()) {
			// TODO
		}
	};

	render(): React.ReactElement<any, string | React.JSXElementConstructor<any>> | string | number | {} | React.ReactNodeArray | React.ReactPortal | boolean | null | undefined {
		return <div
			className={"d-inline-block shareButton" + (this.state.shared ? " active" : this.isSelf() ? " inactive" : "")}
			onClick={(e) => this.click(e)}>
			<i className={"fas fa-retweet"}/><span
			className={"number"}>{formatNumberShort(this.props.entry.getShareCount())}</span>
		</div>;
	}

	private isSelf() {
		let self: boolean = false;
		const currentUser = Auth.getCurrentUser();

		if (currentUser && this.props.entry.getUser().getId() === currentUser.getId()) {
			self = true;
		}

		return self;
	}
}