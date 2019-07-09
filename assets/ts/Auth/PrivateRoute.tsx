import React from "react";
import {Redirect, Route, RouteComponentProps, RouteProps} from "react-router-dom";
import Auth from "./Auth";

interface IPrivateRouteProps extends RouteProps {
	component: React.ComponentType<RouteComponentProps<any>> | React.ComponentType<any>
}

type RenderComponent = (props: RouteComponentProps<any>) => React.ReactNode;

export default class PrivateRoute extends Route<IPrivateRouteProps> {
	render() {
		const {component: Component, ...rest}: IPrivateRouteProps = this.props;

		const renderComponent: RenderComponent = (props) => (
			Auth.isLoggedIn()
				? <Component {...props} />
				: <Redirect to='/login'/>
		);

		return (
			<Route {...rest} render={renderComponent}/>
		);
	}
}