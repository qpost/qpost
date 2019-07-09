import React, {Component} from "react";
import NightMode from "./NightMode/NightMode";
import LoadingScreen from "./Component/LoadingScreen";
import API from "./API/API";
import {BrowserRouter as Router, Route, Switch} from "react-router-dom";
import BaseObject from "./Serialization/BaseObject";
import Auth from "./Auth/Auth";
import User from "./Entity/Account/User";
import Header from "./Parts/Header";

export default class App extends Component<any, any> {
	constructor(props) {
		super(props);

		this.state = {
			validatedLogin: false,
			error: null
		}
	}

	public static init(): void {
		NightMode.init();
	}

	componentDidMount(): void {
		API.handleRequest("/token/verify", "POST", {}, (data => {
			if (data.status && data.status === "Token valid" && data.user) {
				Auth.setCurrentUser(BaseObject.convertObject(User, data.user));

				this.setState({
					validatedLogin: true
				})
			} else {
				this.setState({
					error: "Authentication failed."
				})
			}
		}), (error => {
			this.setState({
				error
			})
		}));
	}

	render(): React.ReactElement<any, string | React.JSXElementConstructor<any>> | string | number | {} | React.ReactNodeArray | React.ReactPortal | boolean | null | undefined {
		if (this.state.validatedLogin === true) {
			return (
				<Router>
					<div className={"h-100"}>
						{/* SCROLL TO TOP ON ROUTE CHANGE */}
						<Route component={() => {
							window.scrollTo(0, 0);
							return null;
						}}/>

						<Header/>

						<Switch>

						</Switch>
					</div>
				</Router>
			);
		} else if (this.state.error !== null) {
			return <div>{this.state.error}</div>; // TODO
		} else {
			return <LoadingScreen/>
		}
	}
}