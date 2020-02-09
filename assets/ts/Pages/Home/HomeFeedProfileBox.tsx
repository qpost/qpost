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
import Auth from "../../Auth/Auth";
import {Link} from "react-router-dom";
import FollowButton from "../../Component/FollowButton";
import VerifiedBadge from "../../Component/VerifiedBadge";
import {formatNumberShort} from "../../Util/Format";
import {Card} from "antd";

export default class HomeFeedProfileBox extends Component<any, any> {
	render(): React.ReactElement<any, string | React.JSXElementConstructor<any>> | string | number | {} | React.ReactNodeArray | React.ReactPortal | boolean | null | undefined {
		const currentUser = Auth.getCurrentUser();

		if (!currentUser) {
			Auth.logout();
			return null;
		}

		return <Card className="homeFeedProfileBox mb-3" size={"small"}>
			<div className="d-block" style={{height: "50px"}}>
				<Link to={"/" + currentUser.getUsername()} className="clearUnderline float-left">
					<img src={currentUser.getAvatarURL()} className="rounded" width="48" height="48"
						 alt={currentUser.getUsername()}/>
				</Link>

				<div className="ml-2 float-left mt-1" style={{
					width: "calc(100% - 150px)"
				}}>
					<div style={{
						maxWidth: "calc(100% - 10px)",
						overflow: "hidden",
						textOverflow: "ellipsis",
						whiteSpace: "nowrap",
						wordWrap: "normal"
					}}>
						<Link to={"/" + currentUser.getUsername()} className="clearUnderline">
								<span className="font-weight-bold"
									  style={{
										  marginTop: "-5px",
										  maxWidth: "100%",
										  display: "inline-block",
										  overflow: "hidden",
										  textOverflow: "ellipsis",
										  whiteSpace: "nowrap",
										  wordWrap: "normal",
										  fontSize: "16px"
									  }}>
									{currentUser.getDisplayName()}<VerifiedBadge target={currentUser}/>
								</span>
						</Link>

						<br/>

						<Link to={"/" + currentUser.getUsername()} className="clearUnderline">
								<span className="text-muted"
									  style={{
										  marginTop: "-5px",
										  maxWidth: "100%",
										  display: "inline-block",
										  overflow: "hidden",
										  textOverflow: "ellipsis",
										  whiteSpace: "nowrap",
										  wordWrap: "normal"
									  }}>
									@{currentUser.getUsername()}
								</span>
						</Link>
					</div>
				</div>

				<FollowButton target={currentUser} className={"float-right mt-2"} size={"small"}/>
			</div>

			<hr className="mb-2 mt-3"/>

			<div>
				<Link to={"/" + currentUser.getUsername()} className="clearUnderline mb-1">
					<div style={{height: "24px"}}>
						<div className="text-muted text-uppercase small float-left pt-1">
							Posts
						</div>

						<div className="font-weight-bold text-uppercase float-right">
							{formatNumberShort(currentUser.getTotalPostCount())}
						</div>
					</div>
				</Link>

				<Link to={"/" + currentUser.getUsername() + "/following"} className="clearUnderline mb-1">
					<div style={{height: "24px"}}>
						<div className="text-muted text-uppercase small float-left pt-1">
							Following
						</div>

						<div className="font-weight-bold text-uppercase float-right">
							{formatNumberShort(currentUser.getFollowingCount())}
						</div>
					</div>
				</Link>

				<Link to={"/" + currentUser.getUsername() + "/followers"} className="clearUnderline mb-1">
					<div style={{height: "24px"}}>
						<div className="text-muted text-uppercase small float-left pt-1">
							Followers
						</div>

						<div className="font-weight-bold text-uppercase float-right">
							{formatNumberShort(currentUser.getFollowerCount())}
						</div>
					</div>
				</Link>
			</div>
		</Card>
	}
}