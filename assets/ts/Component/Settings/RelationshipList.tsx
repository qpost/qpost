/*
 * Copyright (C) 2018-2020 Gigadrive - All rights reserved.
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
import API from "../../API/API";
import Block from "../../Entity/Account/Block";
import BaseObject from "../../Serialization/BaseObject";
import Follower from "../../Entity/Account/Follower";
import Spin from "antd/es/spin";
import {Empty, Typography} from "antd";
import "antd/es/typography/style";
import User from "../../Entity/Account/User";
import {formatNumberShort} from "../../Util/Format";
import FollowButton from "../FollowButton";
import FollowStatus from "../../Util/FollowStatus";
import VerifiedBadge from "../VerifiedBadge";
import InfiniteScroll from "react-infinite-scroller";

export default class RelationshipList extends Component<{
	type: "BLOCKED" | "FOLLOWERS" | "FOLLOWING"
}, {
	followers: Follower[] | null,
	blocks: Block[] | null,
	error: null,
	loading: boolean
}> {
	constructor(props) {
		super(props);

		this.state = {
			followers: null,
			blocks: null,
			error: null,
			loading: false
		};
	}

	componentDidMount(): void {
		this.load();
	}

	load = () => {
		if (this.state.loading) return;

		this.setState({
			loading: true
		});

		if (this.props.type === "BLOCKED") {
			const param = {};

			if (this.state.blocks && this.state.blocks.length) {
				const lastUser: Block = this.state.blocks[this.state.blocks.length - 1];

				param["max"] = lastUser.getId();
			}

			API.handleRequest("/blocks", "GET", {}, data => {
				const blocks: Block[] = this.state.blocks || [];

				BaseObject.convertArray(Block, data.results).forEach(block => blocks.push(block));

				this.setState({
					blocks,
					loading: false
				});
			})
		} else {
			const param = {
				[this.props.type === "FOLLOWING" ? "from" : "to"]: window["CURRENT_USER_ID"]
			};

			if (this.state.followers && this.state.followers.length) {
				const lastUser: Follower = this.state.followers[this.state.followers.length - 1];

				param["max"] = lastUser.getId();
			}

			API.handleRequest("/follows", "GET", param, data => {
				const followers: Follower[] = this.state.followers || [];

				BaseObject.convertArray(Follower, data.results).forEach(follower => followers.push(follower));

				this.setState({
					followers,
					loading: false
				});
			})
		}
	};

	render(): React.ReactElement<any, string | React.JSXElementConstructor<any>> | string | number | {} | React.ReactNodeArray | React.ReactPortal | boolean | null | undefined {
		if ((!this.state.followers && !this.state.blocks)) {
			return <div className={"text-center my-3"}>
				<Spin size={"large"}/>
			</div>;
		}

		if ((this.state.followers || this.state.blocks).length === 0) {
			return <Empty description={"Nothing found."}/>
		}

		return <InfiniteScroll
			pageStart={1}
			loadMore={() => {
				this.load();
			}}
			hasMore={true}
			loader={<div className={"text-center my-3" + (!this.state.loading ? " d-none" : "")}>
				<Spin size={"large"}/>
			</div>}
			initialLoad={false}
		>
			<table className={"table"}>
				{this.state.followers ? this.state.followers.map(follower => {
					return this.renderRow(this.props.type === "FOLLOWING" ? follower.getReceiver() : follower.getSender());
				}) : ""}

				{this.state.blocks ? this.state.blocks.map(block => {
					return this.renderRow(block.getTarget());
				}) : ""}
			</table>
		</InfiniteScroll>;

		// return (this.state.followers || this.state.blocks).length;
	}

	renderRow(user: User) {
		return <tr>
			<td>
				<div className={"clearfix"}>
					<a href={"/" + user.getUsername()} className={"clearUnderline float-left"}>
						<img src={user.getAvatarURL()} className={"rounded rounded-mainColor"} alt={user.getUsername()}
							 style={{
								 width: "48px",
								 height: "48px"
							 }}/>
					</a>

					<a href={"/" + user.getUsername()} className={"clearUnderline"}>
						<Typography.Paragraph ellipsis className={"float-left ml-2 mb-0"}>
							<p className={"text-white font-weight-bold mb-0"}>
								{user.getDisplayName()}<VerifiedBadge target={user}/>
							</p>

							<p className={"text-muted mb-0 mt-n1"}>
								@{user.getUsername()}
							</p>
						</Typography.Paragraph>
					</a>
				</div>
			</td>

			<td className={"text-center d-none d-lg-table-cell"}>
				<p className={"mb-0 font-weight-bold"}>{formatNumberShort(user.getTotalPostCount())}</p>
				<p className={"mb-0 small text-muted text-uppercase"}>posts</p>
			</td>

			<td className={"text-center d-none d-md-table-cell"}>
				<p className={"mb-0 font-weight-bold"}>{formatNumberShort(user.getFollowerCount())}</p>
				<p className={"mb-0 small text-muted text-uppercase"}>followers</p>
			</td>

			<td style={{
				width: "15%"
			}}>
				<FollowButton className={"mt-2"} block target={user}
							  followStatus={this.props.type === "FOLLOWING" ? FollowStatus.FOLLOWING : this.props.type === "BLOCKED" ? FollowStatus.BLOCKED : undefined}/>
			</td>
		</tr>;
	}
}