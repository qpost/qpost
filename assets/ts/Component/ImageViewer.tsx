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
import ImgsViewer from "react-images-viewer";
import {Spin} from "antd";
import FeedEntry from "../api/src/Entity/FeedEntry";
import __ from "../i18n/i18n";

export default class ImageViewer extends Component<any, {
	visible: boolean,
	feedEntry: FeedEntry,
	index: number
}> {
	private static INSTANCE: ImageViewer = null;

	constructor(props) {
		super(props);

		this.state = {
			visible: false,
			feedEntry: null,
			index: 0
		};
	}

	public static show(feedEntry: FeedEntry, index?: number): void {
		if (this.INSTANCE !== null) {
			if (!index) index = 0;

			this.INSTANCE.setState({
				visible: true,
				feedEntry,
				index
			});
		}
	}

	public static close(): void {
		if (this.INSTANCE !== null) {
			this.INSTANCE.setState({
				visible: false,
				feedEntry: null,
				index: 0
			});
		}
	}

	componentDidMount(): void {
		if (ImageViewer.INSTANCE === null) {
			ImageViewer.INSTANCE = this;
		}
	}

	componentWillUnmount(): void {
		if (ImageViewer.INSTANCE === this) {
			ImageViewer.INSTANCE = null;
		}
	}

	fixIndex(index: number, imageLength: number): number {
		if (index <= -1) index = imageLength - 1;
		if (index >= imageLength) index = 0;

		return index;
	}

	render(): React.ReactElement<any, string | React.JSXElementConstructor<any>> | string | number | {} | React.ReactNodeArray | React.ReactPortal | boolean | null | undefined {
		if (!this.state.visible || !this.state.feedEntry || !this.state.feedEntry.getAttachments() || this.state.feedEntry.getAttachments().length === 0) {
			return "";
		}

		const images = [];
		this.state.feedEntry.getAttachments().forEach(mediaFile => {
			images.push({
				src: mediaFile.getURL()
			});
		});

		return <ImgsViewer
			imgs={images}
			currImg={this.state.index}
			isOpen={this.state.visible}
			backdropCloseable={true}
			closeBtnTitle={__("imageViewer.close")}
			leftArrowTitle={__("imageViewer.previous")}
			rightArrowTitle={__("imageViewer.next")}
			onClickPrev={() => {
				this.setState({
					index: this.fixIndex(this.state.index - 1, images.length)
				});
			}}
			onClickNext={() => {
				this.setState({
					index: this.fixIndex(this.state.index + 1, images.length)
				});
			}}
			spinner={() => {
				return <Spin size={"large"}/>;
			}}
			onClose={() => {
				this.setState({
					visible: false,
					feedEntry: null,
					index: 0
				});
			}}
		/>;
	}
}