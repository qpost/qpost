/*
 * Copyright (C) 2018-2020 Gigadrive - All rights reserved.
 * https://gigadrivegroup.com
 * https://qpostapp.com
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://gnu.org/licenses/>
 */

import React, {Component} from "react";
import LeftSidebar from "../../Component/Layout/LeftSidebar";
import PageContent from "../../Component/Layout/PageContent";
import RightSidebar from "../../Component/Layout/RightSidebar";
import HomeFeedProfileBox from "./HomeFeedProfileBox";
import SuggestedUsers from "../../Component/SuggestedUsers";
import FeedEntryList from "../../Component/FeedEntry/FeedEntryList";
import ContentBase from "../../Component/Layout/ContentBase";
import DummyPostForm from "../../Component/PostForm/DummyPostForm";
import SidebarFooter from "../../Parts/Footer/SidebarFooter";
import {setPageTitle} from "../../Util/Page";
import UpcomingBirthdays from "../../Component/UpcomingBirthdays";
import TrendingTopics from "../../Component/TrendingTopics";
import PageHeader from "../../Component/PageHeader";
import __ from "../../i18n/i18n";

export default class HomeFeed extends Component<any, any> {
	componentDidMount(): void {
		setPageTitle(__("home.headline"));
	}

	render(): React.ReactElement<any, string | React.JSXElementConstructor<any>> | string | number | {} | React.ReactNodeArray | React.ReactPortal | boolean | null | undefined {
		return (
			<ContentBase>
				<LeftSidebar>
					<HomeFeedProfileBox/>

					<TrendingTopics limit={5}/>
				</LeftSidebar>

				<PageContent leftSidebar rightSidebar>
					<PageHeader title={__("home.headline")} iconClass={"fas fa-home"}/>
					<DummyPostForm/>

					<FeedEntryList/>
				</PageContent>

				<RightSidebar>
					<SuggestedUsers/>
					<UpcomingBirthdays/>

					<SidebarFooter/>
				</RightSidebar>
			</ContentBase>
		)
	}
}