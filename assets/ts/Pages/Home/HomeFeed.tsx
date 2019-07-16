import React, {Component} from "react";
import LeftSidebar from "../../Component/Layout/LeftSidebar";
import PageContent from "../../Component/Layout/PageContent";
import RightSidebar from "../../Component/Layout/RightSidebar";
import HomeFeedProfileBox from "./HomeFeedProfileBox";
import SuggestedUsers from "../../Component/SuggestedUsers";
import FeedEntryList from "../../Component/FeedEntry/FeedEntryList";
import ContentBase from "../../Component/Layout/ContentBase";
import SidebarStickyContent from "../../Component/Layout/SidebarStickyContent";
import DummyPostForm from "../../Component/PostForm/DummyPostForm";

export default class HomeFeed extends Component<any, any> {
	render(): React.ReactElement<any, string | React.JSXElementConstructor<any>> | string | number | {} | React.ReactNodeArray | React.ReactPortal | boolean | null | undefined {
		return (
			<ContentBase>
				<LeftSidebar>
					<SidebarStickyContent>
						<HomeFeedProfileBox/>
					</SidebarStickyContent>
				</LeftSidebar>

				<PageContent leftSidebar rightSidebar>
					<DummyPostForm/>

					<FeedEntryList/>
				</PageContent>

				<RightSidebar>
					<SidebarStickyContent>
						<SuggestedUsers/>
					</SidebarStickyContent>
				</RightSidebar>
			</ContentBase>
		)
	}
}