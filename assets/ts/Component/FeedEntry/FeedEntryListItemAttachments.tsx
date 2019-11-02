/*
 * Copyright (C) 2018-2019 Gigadrive - All rights reserved.
 * https://gigadrivegroup.com
 * https://qpo.st
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
import FeedEntry from "../../Entity/Feed/FeedEntry";
import MediaFile from "../../Entity/Media/MediaFile";
import MediaFileType from "../../Entity/Media/MediaFileType";
import ImageViewer from "../ImageViewer";
import GifPlayer from "react-gif-player";
import {stillGIFURL} from "../../Util/Format";

export default class FeedEntryListItemAttachments extends Component<{
	entry: FeedEntry
}, any> {
	clickHandler(e, index: number) {
		e.preventDefault();
		e.stopPropagation();

		ImageViewer.show(this.props.entry, index);
	}

	render(): React.ReactElement<any, string | React.JSXElementConstructor<any>> | string | number | {} | React.ReactNodeArray | React.ReactPortal | boolean | null | undefined {
		const entry: FeedEntry = this.props.entry;
		const attachments: MediaFile[] = entry.getAttachments();

		if (attachments.length === 1) {
			const mediaFile: MediaFile = attachments[0];

			switch (mediaFile.getType()) {
				case MediaFileType.VIDEO:
					return <div className={"embed-responsive embed-responsive-16by9"}>
						<iframe src={mediaFile.getURL()} className={"embed-responsive-item"}/>
					</div>;
				case MediaFileType.IMAGE:
					if (mediaFile.getURL().endsWith(".gif")) {
						return <GifPlayer gif={mediaFile.getURL()} still={stillGIFURL(mediaFile.getURL())}/>;
					}
			}
		}

		if (attachments.length > 0) {
			if (attachments.length === 1) {
				const mediaFile: MediaFile = attachments[0];

				return <div className={"d-block w-100 float-left"}>
					{mediaFile.getType() === MediaFileType.IMAGE ?
						<div className={"border border-mainColor bg-dark"} style={{
							backgroundImage: 'url("' + stillGIFURL(mediaFile.getURL()) + '")',
							backgroundSize: "cover",
							backgroundPosition: "center",
							cursor: "pointer"
						}} onClick={(e) => this.clickHandler(e, 0)}>
							<img src={stillGIFURL(mediaFile.getURL())} style={{
								maxHeight: "500px",
								width: "100%",
								height: "100%",
								visibility: "hidden"
							}} alt={mediaFile.getId()}/>
						</div> : ""}
				</div>;
			} else if (attachments.length === 2) {
				return <div style={{
					height: "537px",
					width: "100%"
				}}>
					{attachments.map((mediaFile: MediaFile, i: number) => {
						return mediaFile.getType() === MediaFileType.IMAGE ?
							<div key={i} className={"d-inline-block"} style={{
								width: "50%",
								position: "relative",
								height: "100%"
							}} onClick={(e) => this.clickHandler(e, 0)}>
								<div className={"border border-mainColor bg-dark" + (i === 1 ? " border-left-0" : "")}
									 style={{
										 maxHeight: "500px",
										 height: "100%",
										 backgroundImage: 'url("' + stillGIFURL(mediaFile.getURL()) + '")',
										 backgroundSize: "cover",
										 backgroundPosition: "center",
										 cursor: "pointer"
									 }}/>
							</div> : "";
					})}
				</div>;
			} else if (attachments.length === 3) {
				return <div style={{
					height: "537px",
					width: "100%"
				}}>
					<div className={"d-inline-block"} style={{
						width: "50%",
						position: "relative",
						height: "100%"
					}}>
						<div className={"border border-mainColor bg-dark mr-2"} style={{
							maxHeight: "573px",
							width: "100%",
							height: "100%",
							backgroundImage: 'url("' + stillGIFURL(attachments[0].getURL()) + '")',
							backgroundSize: "cover",
							backgroundPosition: "center",
							cursor: "pointer"
						}} onClick={(e) => this.clickHandler(e, 0)}/>
					</div>

					<div className={"d-inline-block"} style={{
						width: "50%",
						height: "100%"
					}}>
						<div className={"border border-mainColor border-left-0 bg-dark mr-2"} style={{
							maxHeight: "537px",
							width: "100%",
							height: "50%",
							backgroundImage: 'url("' + stillGIFURL(attachments[1].getURL()) + '")',
							backgroundSize: "cover",
							backgroundPosition: "center",
							cursor: "pointer"
						}} onClick={(e) => this.clickHandler(e, 1)}/>

						<div className={"border border-mainColor border-left-0 border-top-0 bg-dark mr-2"} style={{
							maxHeight: "537px",
							width: "100%",
							height: "50%",
							backgroundImage: 'url("' + stillGIFURL(attachments[2].getURL()) + '")',
							backgroundSize: "cover",
							backgroundPosition: "center",
							cursor: "pointer"
						}} onClick={(e) => this.clickHandler(e, 2)}/>
					</div>
				</div>;
			} else if (attachments.length === 4) {
				return <div style={{
					height: "537px",
					width: "100%"
				}}>
					<div className={"d-inline-block"} style={{
						width: "50%",
						position: "relative",
						height: "100%"
					}}>
						<div className={"border border-mainColor bg-dark mr-2"} style={{
							maxHeight: "500px",
							width: "100%",
							height: "50%",
							backgroundImage: 'url("' + stillGIFURL(attachments[0].getURL()) + '")',
							backgroundSize: "cover",
							cursor: "pointer"
						}} onClick={(e) => this.clickHandler(e, 0)}/>

						<div className={"border border-mainColor border-top-0 bg-dark mr-2"} style={{
							maxHeight: "500px",
							width: "100%",
							height: "50%",
							backgroundImage: 'url("' + stillGIFURL(attachments[1].getURL()) + '")',
							backgroundSize: "cover",
							cursor: "pointer"
						}} onClick={(e) => this.clickHandler(e, 1)}/>
					</div>

					<div className={"d-inline-block"} style={{
						width: "50%",
						position: "relative",
						height: "100%"
					}}>
						<div className={"border border-mainColor border-left-0 bg-dark mr-2"} style={{
							maxHeight: "500px",
							width: "100%",
							height: "50%",
							backgroundImage: 'url("' + stillGIFURL(attachments[2].getURL()) + '")',
							backgroundSize: "cover",
							cursor: "pointer"
						}} onClick={(e) => this.clickHandler(e, 2)}/>

						<div className={"border border-mainColor border-left-0 border-top-0 bg-dark mr-2"} style={{
							maxHeight: "500px",
							width: "100%",
							height: "50%",
							backgroundImage: 'url("' + stillGIFURL(attachments[3].getURL()) + '")',
							backgroundSize: "cover",
							cursor: "pointer"
						}} onClick={(e) => this.clickHandler(e, 3)}/>
					</div>
				</div>;
			}
		}

		return "";
	}
}