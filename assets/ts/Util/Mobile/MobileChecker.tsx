import React, {Component} from "react";
import WindowSizeListener from "react-window-size-listener";
import Mobile from "./Mobile";

class MobileChecker extends Component<any, any> {
	render(): React.ReactElement<any, string | React.JSXElementConstructor<any>> | string | number | {} | React.ReactNodeArray | React.ReactPortal | boolean | null | undefined {
		Mobile.setMobile(window.innerWidth <= 768);

		return <WindowSizeListener onResize={windowSize => {
			Mobile.setMobile(windowSize.windowWidth <= 768);
		}}/>;
	}
}

export default MobileChecker;