import React, {Component} from "react";
import NightMode from "./NightMode/NightMode";
import LoadingScreen from "./Component/LoadingScreen";
import API from "./API/API";

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
			if (data.status && data.status === "Token valid") {
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
			return <div>asd</div>
		} else if (this.state.error !== null) {
			return <div>{this.state.error}</div>; // TODO
		} else {
			return <LoadingScreen/>
		}
	}
}