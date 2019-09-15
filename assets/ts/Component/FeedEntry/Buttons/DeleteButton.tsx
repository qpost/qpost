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

export default class DeleteButton extends Component<{
	entry: FeedEntry
}, any> {
	click = (e) => {
		e.preventDefault();
		e.stopPropagation();

		// TODO
	};

	render(): React.ReactElement<any, string | React.JSXElementConstructor<any>> | string | number | {} | React.ReactNodeArray | React.ReactPortal | boolean | null | undefined {
		const currentUser = Auth.getCurrentUser();
		if (!currentUser || currentUser.getId() !== this.props.entry.getUser().getId()) {
			return "";
		}

		return <div className={"d-inline-block deleteButton"}
					onClick={(e) => this.click(e)}>
			<i className={"fas fa-trash-alt"}/>
		</div>;
	}
}