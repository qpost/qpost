import React, {Component} from "react";
import LeftSidebar from "../../Component/Layout/LeftSidebar";
import PageContent from "../../Component/Layout/PageContent";
import RightSidebar from "../../Component/Layout/RightSidebar";
import HomeFeedProfileBox from "./HomeFeedProfileBox";
import SuggestedUsers from "../../Component/SuggestedUsers";
import FeedEntryList from "../../Component/FeedEntry/FeedEntryList";
import ContentBase from "../../Component/Layout/ContentBase";

export default class HomeFeed extends Component<any, any> {
	render(): React.ReactElement<any, string | React.JSXElementConstructor<any>> | string | number | {} | React.ReactNodeArray | React.ReactPortal | boolean | null | undefined {
		return (
			<ContentBase>
				<LeftSidebar>
					<HomeFeedProfileBox/>
				</LeftSidebar>

				<PageContent leftSidebar rightSidebar>
					<FeedEntryList/>
				</PageContent>

				<RightSidebar>
					<SuggestedUsers/>
					asd
				</RightSidebar>
			</ContentBase>
		)
	}
}