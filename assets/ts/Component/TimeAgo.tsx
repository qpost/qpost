import React, {Component} from "react";
import ReactTimeAgo from "react-timeago";

export default class TimeAgo extends Component<{
	time: string
}, any> {
	render(): React.ReactElement<any, string | React.JSXElementConstructor<any>> | string | number | {} | React.ReactNodeArray | React.ReactPortal | boolean | null | undefined {
		return <ReactTimeAgo date={this.props.time}/>;
	}
}