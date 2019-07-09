import React, {Component} from "react";
import LeftSidebar from "../../Component/Layout/LeftSidebar";
import PageContent from "../../Component/Layout/PageContent";
import RightSidebar from "../../Component/Layout/RightSidebar";
import {Row} from "reactstrap";

export default class HomeFeed extends Component<any, any> {
	render(): React.ReactElement<any, string | React.JSXElementConstructor<any>> | string | number | {} | React.ReactNodeArray | React.ReactPortal | boolean | null | undefined {
		return (
			<Row>
				<LeftSidebar>
					left
				</LeftSidebar>

				<PageContent>
					content
				</PageContent>

				<RightSidebar>
					right
				</RightSidebar>
			</Row>
		)
	}
}