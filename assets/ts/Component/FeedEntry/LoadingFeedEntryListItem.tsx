import React, {Component} from "react";
import Skeleton from "antd/es/skeleton";
import "antd/es/skeleton/style";

export default class LoadingFeedEntryListItem extends Component<any, any> {
	render(): React.ReactElement<any, string | React.JSXElementConstructor<any>> | string | number | {} | React.ReactNodeArray | React.ReactPortal | boolean | null | undefined {
		return <li className={"list-group-item px-0 py-0 feedEntry statusTrigger"}>
			<div className={"px-4 py-2"}>
				<Skeleton loading active avatar={{
					size: "large",
					shape: "square"
				}}
						  paragraph={{
							  rows: 4
						  }}/>
			</div>
		</li>;
	}
}