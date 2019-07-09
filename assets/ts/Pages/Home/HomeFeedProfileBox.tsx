import React, {Component} from "react";
import Auth from "../../Auth/Auth";
import {Link} from "react-router-dom";

export default class HomeFeedProfileBox extends Component<any, any> {
	render(): React.ReactElement<any, string | React.JSXElementConstructor<any>> | string | number | {} | React.ReactNodeArray | React.ReactPortal | boolean | null | undefined {
		const currentUser = Auth.getCurrentUser();

		if(!currentUser){
			Auth.logout();
			return null;
		}

		return <div className="homeFeedProfileBox card mb-3">
			<div className="px-2 py-2">
				<div className="d-block" style={{height: "50px"}}>
					<Link to={"/" + currentUser.getUsername()} className="clearUnderline float-left">
						<img src={currentUser.getAvatarURL()} className="rounded" width="48" height="48" alt={currentUser.getUsername()}/>
					</Link>

					<div className="ml-2 float-left mt-1">
						<Link to={"/" + currentUser.getUsername()} className="clearUnderline float-left">
							<div className="font-weight-bold"
								style={{
									maxWidth: "70px",
									overflow: "hidden",
									textOverflow: "ellipsis",
									whiteSpace: "nowrap",
									wordWrap: "normal",
									marginTop: "-5px"
								}}>
								{currentUser.getDisplayName()}{/*TODO: Add verified badge*/}
							</div>

							<div className="text-muted small"
								 style={{
									 maxWidth: "70px",
									 overflow: "hidden",
									 textOverflow: "ellipsis",
									 whiteSpace: "nowrap",
									 wordWrap: "normal",
									 marginTop: "-5px"
								 }}>
								@{currentUser.getUsername()}
							</div>
						</Link>
					</div>

					{/*{{followButton(currentUser,true, ["float-right", "mt-2", "btn-sm"],false)}}*/}
					{/* TODO: Add follow button	*/}
				</div>

				<hr className="mb-2 mt-3"/>

				<div>
					<Link to={"/" + currentUser.getUsername()} className="clearUnderline mb-1">
						<div style={{height: "24px"}}>
							<div className="text-muted text-uppercase small float-left pt-1">
								Posts
							</div>

							<div className="font-weight-bold text-uppercase float-right">
								{currentUser.getPostCount()} {/* TODO: Add formatNumberShort implementation */}
							</div>
						</div>
					</Link>

					<Link to={"/" + currentUser.getUsername() + "/following"} className="clearUnderline mb-1">
						<div style={{height: "24px"}}>
							<div className="text-muted text-uppercase small float-left pt-1">
								Following
							</div>

							<div className="font-weight-bold text-uppercase float-right">
								{currentUser.getFollowingCount()} {/* TODO: Add formatNumberShort implementation */}
							</div>
						</div>
					</Link>

					<Link to={"/" + currentUser.getUsername() + "/followers"} className="clearUnderline mb-1">
						<div style={{height: "24px"}}>
							<div className="text-muted text-uppercase small float-left pt-1">
								Followers
							</div>

							<div className="font-weight-bold text-uppercase float-right">
								{currentUser.getFollowerCount()} {/* TODO: Add formatNumberShort implementation */}
							</div>
						</div>
					</Link>
				</div>
			</div>
		</div>
	}
}