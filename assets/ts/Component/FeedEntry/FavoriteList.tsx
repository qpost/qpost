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
import {Alert} from "reactstrap";
import FeedEntryListItem from "./FeedEntryListItem";
import API from "../../API";
import LoadingFeedEntryListItem from "./LoadingFeedEntryListItem";
import Empty from "antd/es/empty";
import "antd/es/empty/style";
import InfiniteScroll from "react-infinite-scroller";
import {Spin} from "antd";
import User from "../../api/src/Entity/User";
import Favorite from "../../api/src/Entity/Favorite";
import __ from "../../i18n/i18n";
import HorizontalAd from "../Advertisment/HorizontalAd";

export default class FavoriteList extends Component<{
	user?: User
}, {
	favorites: Favorite[] | null,
	error: string | null,
	loadingMore: boolean,
	hasMore: boolean
}> {
	public static instance: FavoriteList | null = null;

	constructor(props) {
		super(props);

		this.state = {
			favorites: null,
			error: null,
			loadingMore: false,
			hasMore: true
		}
	}

	componentDidMount(): void {
		FavoriteList.instance = this;
		this.load();
	}

	componentWillUnmount(): void {
		FavoriteList.instance = null;
	}

	public prependEntry(favorite: Favorite): void {
		const favorites: Favorite[] = this.state.favorites || [];

		favorites.unshift(favorite);

		this.setState({favorites});
	}

	load(max?: number) {
		API.i.favorite.list(this.props.user, max).then(favorites => {
			favorites.forEach(result => favorites.push(result));

			this.setState({
				favorites,
				loadingMore: false,
				hasMore: favorites.length === 0 ? false : this.state.hasMore
			});
		}).catch(reason => {
			this.setState({error: reason, loadingMore: false, hasMore: false});
		});
	}

	loadMore() {
		if (!this.state.loadingMore && this.state.favorites.length > 0 && this.state.hasMore) {
			const lastId = this.state.favorites[this.state.favorites.length - 1].getId();

			this.setState({
				loadingMore: true
			});

			this.load(lastId);
		}
	}

	render(): React.ReactElement<any, string | React.JSXElementConstructor<any>> | string | number | {} | React.ReactNodeArray | React.ReactPortal | boolean | null | undefined {
		if (this.state.favorites !== null) {
			if (this.state.favorites.length > 0) {
				return <InfiniteScroll
					pageStart={1}
					loadMore={() => {
						this.loadMore();
					}}
					hasMore={this.state.hasMore}
					loader={<div className={"text-center my-3" + (!this.state.loadingMore ? " d-none" : "")}>
						<Spin size={"large"}/>
					</div>}
					initialLoad={false}
				>
					<ul className={"list-group feedContainer"}>
						{this.state.favorites.map((favorite, i) => {
							const maxAds = 10;
							const adIndexes: number[] = [];
							for (let j = 0; j < maxAds; j++) {
								adIndexes.push(15 * j + 5);
							}

							const components = [];

							if (adIndexes.indexOf(i) !== -1) {
								components.push(<HorizontalAd marginDirection={"y"}/>);
							}

							components.push(<FeedEntryListItem key={"favorite-" + favorite.getId()}
															   entry={favorite.getFeedEntry()} parent={this}
															   showParentInfo={true}/>);

							return components;
						})}
					</ul>
				</InfiniteScroll>;
			} else {
				return <div className={"mt-3"}>
					<Empty description={__("entryList.empty")}/>
				</div>;
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