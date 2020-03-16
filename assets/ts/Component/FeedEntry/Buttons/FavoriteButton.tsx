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
import FeedEntry from "../../../Entity/Feed/FeedEntry";
import {formatNumberShort} from "../../../Util/Format";
import {message, Spin} from "antd";
import "antd/es/spin/style";
import API from "../../../API/API";
import FeedEntryActionButtons from "./FeedEntryActionButtons";
import Auth from "../../../Auth/Auth";
import LoginSuggestionModal from "../../LoginSuggestionModal";

export default class FavoriteButton extends Component<{
	entry: FeedEntry,
	parent?: FeedEntryActionButtons,
	onEntryUpdate?: (entry: FeedEntry) => void
}, {
	favorited: boolean,
	loading: boolean,
	entry: FeedEntry
}> {
	constructor(props) {
		super(props);

		this.state = {
			favorited: this.props.entry.isFavorited(),
			loading: false,
			entry: this.props.entry
		};
	}

	click = (e) => {
		e.preventDefault();
		e.stopPropagation();

		if (Auth.isLoggedIn()) {
			if (!this.state.loading) {
				this.setState({loading: true});

				if (this.state.favorited) {
					API.favorite.delete(this.props.entry).then(feedEntry => {
						this.setState({
							favorited: !this.state.favorited,
							loading: false,
							entry: feedEntry
						});

						if (this.props.onEntryUpdate) {
							this.props.onEntryUpdate(this.state.entry);
						}
					}).catch(reason => {
						message.error(reason);
						this.setState({loading: false});
					});
				} else {
					API.favorite.post(this.props.entry).then(favorite => {
						console.log("fav", favorite.getFeedEntry());

						this.setState({
							favorited: !this.state.favorited,
							loading: false,
							entry: favorite.getFeedEntry()
						});

						if (this.props.onEntryUpdate) {
							this.props.onEntryUpdate(this.state.entry);
						}
					}).catch(reason => {
						message.error(reason);
						this.setState({loading: false});
					});
				}
			}
		} else {
			LoginSuggestionModal.open();
		}
	};

	render(): React.ReactElement<any, string | React.JSXElementConstructor<any>> | string | number | {} | React.ReactNodeArray | React.ReactPortal | boolean | null | undefined {
		return <div className={"d-inline-block favoriteButton" + (this.state.favorited ? " active" : "")}
					onClick={(e) => this.click(e)}>
			{!this.state.loading ? <i className={"fas fa-star"}/> : <Spin size={"small"}/>}<span
			className={"number"}>{formatNumberShort(this.state.entry.getFavoriteCount())}</span>
		</div>;
	}
}