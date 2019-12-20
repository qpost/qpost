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
import TrendingHashtagData from "../Entity/Feed/TrendingHashtagData";
import Spin from "antd/es/spin";
import API from "../API/API";
import {Card, message} from "antd";
import BaseObject from "../Serialization/BaseObject";
import {Link} from "react-router-dom";

export default class TrendingTopics extends Component<{
	limit?: number
}, {
	loading: boolean,
	trends: TrendingHashtagData[] | null
}> {
	constructor(props) {
		super(props);

		this.state = {
			loading: true,
			trends: null
		};
	}

	componentDidMount(): void {
		API.handleRequest("/trends", "GET", {
			limit: 20
		}, data => {
			if (data.results) {
				const results: TrendingHashtagData[] = [];

				data.results.forEach(result => {
					results.push(BaseObject.convertObject(TrendingHashtagData, result));
				});

				this.setState({loading: false, trends: results});
			} else {
				this.setState({loading: false});
				message.error("An error occurred.");
			}
		}, error => {
			this.setState({loading: false});
			message.error(error);
		});
	}

	render(): React.ReactElement<any, string | React.JSXElementConstructor<any>> | string | number | {} | React.ReactNodeArray | React.ReactPortal | boolean | null | undefined {
		if (this.state.loading) {
			return <div className={"text-center my-3"}>
				<Spin size={"large"}/>
			</div>;
		} else if (this.state.trends !== null && this.state.trends.length > 0) {
			return <Card size={"small"} title={"Trending topics"} className={"card-nopadding trending-topics mb-3"}>
				<ul className={"list-group feedContainer"}>
					{this.state.trends.map((trend: TrendingHashtagData, index: number) => {
						if (index >= this.props.limit) return "";

						const tag: string = trend.getHashtag().getId();

						return <Link to={"/hashtag/" + encodeURI(tag)} className={"clearUnderline"}>
							<li className={"list-group-item px-0 py-0 feedEntry statusTrigger"} key={index}>
								<div className={"px-4 py-2"}>
									<p className={"mb-0 text-muted small"}>#{index + 1}</p>
									<p className={"mb-0 font-weight-bold"}>#{tag}</p>
									<p className={"mb-0 text-muted"}>{trend.getPostsThisWeek()} post{trend.getPostsThisWeek() !== 1 ? "s" : ""} this
										week</p>
								</div>
							</li>
						</Link>;
					})}
				</ul>
			</Card>;
		} else {
			return "";
		}
	}
}