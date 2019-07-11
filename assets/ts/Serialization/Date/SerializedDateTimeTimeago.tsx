import React, {Component} from "react";
import SerializedDateTime from "./SerializedDateTime";
import TimeAgo from "react-timeago";

export default class SerializedDateTimeTimeago extends Component<{
	time: SerializedDateTime
}, any> {
	constructor(props) {
		super(props);
	}

	render(): React.ReactElement<any, string | React.JSXElementConstructor<any>> | string | number | {} | React.ReactNodeArray | React.ReactPortal | boolean | null | undefined {
		return <TimeAgo date={this.props.time.toString()}/>;
	}
}