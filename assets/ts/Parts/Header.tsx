import React, {Component} from "react";
import Auth from "../Auth/Auth";
import {Link} from "react-router-dom";
import NightMode from "../NightMode/NightMode";
import Logo from "../../img/navlogo.png";

export default class Header extends Component<any, any> {
	render(): React.ReactElement<any, string | React.JSXElementConstructor<any>> | string | number | {} | React.ReactNodeArray | React.ReactPortal | boolean | null | undefined {
		const currentUser = Auth.getCurrentUser();

		if (!currentUser) {
			Auth.logout();
			return null;
		}

		const logo = <img src={Logo} style={{height: "30px"}} alt={"qpost Logo"}/>;

		return <nav id="mainNav"
					className={"navbar navbar-expand-lg navbar-dark fixed-top " + (NightMode.isActive() ? "bg-dark" : "bg-primary")}>
			<div className="container-fluid container">
				<div className="navbar-header">
					<button className="navbar-toggler" type="button" data-toggle="collapse"
							data-target="#main-navigation"
							aria-controls="main-navigation" aria-expanded="false" aria-label="Toggle navigation">
						<span className="navbar-toggler-icon"/>
					</button>

					{Auth.isLoggedIn() ? (
						<Link className="navbar-brand" to="/">
							{logo}
						</Link>
					) : (
						<a className="navbar-brand" href="/">
							{logo}
						</a>
					)}
				</div>

				<div className="collapse navbar-collapse" id="main-navigation">
					<ul className="nav navbar-nav ml-auto">
						{Auth.isLoggedIn() ? (
							<div className={"d-flex"}>
								<li className="nav-item">
									<a href="/" className="nav-link">
										home
									</a>
								</li>

								<li className="nav-item">
									<Link to={"/" + currentUser.getUsername()} className={"nav-link"}>
										my profile
									</Link>
								</li>

								<li className="nav-item">
									<Link to="/notifications" className="nav-link notificationTabMainNav">
										notifications
									</Link>
								</li>

								<li className="nav-item dropdown">
									<a href="#" className="nav-link dropdown-toggle" id="accountDropdown" role="button"
									   data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
										<img src={currentUser.getAvatarURL()} width="24" height="24"
											 className="rounded border border-white" alt={currentUser.getUsername()}/>
									</a>

									<div className="dropdown-menu dropdown-menu-right shadow"
										 aria-labelledby="accountDropdown">
										<Link to={"/" + currentUser.getUsername()} className="dropdown-item">
											<div className="font-weight-bold" style={{fontSize: "21px"}}>
												{currentUser.getDisplayName()}
											</div>
											<div className="text-muted" style={{marginTop: "-8px"}}>
												@{currentUser.getUsername()}
											</div>
										</Link>

										<div className="dropdown-divider"/>

										<Link to={"/" + currentUser.getUsername()} className="dropdown-item">
											<i className="far fa-user"/> Profile
										</Link>

										<Link to="/notifications" className="dropdown-item">
											<i className="far fa-bell"/> Notifications
										</Link>

										<Link to="/messages" className="dropdown-item">
											<i className="far fa-envelope"/> Messages
										</Link>

										<div className="dropdown-divider"/>

										<Link to={"/edit"} className="dropdown-item">
											Edit profile
										</Link>

										<Link to={"/account"} className="dropdown-item">
											Settings and privacy
										</Link>

										<a href="#" onClick={(e) => {
											e.preventDefault();
											Auth.logout();
										}} className="dropdown-item">
											Log out
										</a>

										<div className="dropdown-divider"/>

										<a href="#" onClick={(e) => {
											e.preventDefault();
											NightMode.toggle();
										}} className="dropdown-item">
											Toggle night mode
										</a>
									</div>
								</li>
							</div>
						) : (
							<div>
								<li className="nav-item">
									<Link to="/login" className="nav-link">
										log in
									</Link>
								</li>
							</div>
						)}
					</ul>
				</div>
			</div>
		</nav>
	}
}