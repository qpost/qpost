import React, {Component} from "react";
import windowSize from "react-window-size";
import Mobile from "./Mobile";

class MobileChecker extends Component<any, any> {
	render(): React.ReactElement<any, string | React.JSXElementConstructor<any>> | string | number | {} | React.ReactNodeArray | React.ReactPortal | boolean | null | undefined {
		Mobile.setMobile(this.props.windowWidth <= 768);

		return "";
	}
}

export default windowSize(MobileChecker);