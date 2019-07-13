import React, {Component} from "react";
import FeedEntry from "../../Entity/Feed/FeedEntry";
import MediaFile from "../../Entity/Media/MediaFile";
import MediaFileType from "../../Entity/Media/MediaFileType";

export default class FeedEntryListItemAttachments extends Component<{
	entry: FeedEntry
}, any> {
	render(): React.ReactElement<any, string | React.JSXElementConstructor<any>> | string | number | {} | React.ReactNodeArray | React.ReactPortal | boolean | null | undefined {
		const entry: FeedEntry = this.props.entry;
		const attachments: MediaFile[] = entry.getAttachments();

		if (attachments.length > 0) {
			if (attachments.length === 1) {
				const mediaFile: MediaFile = attachments[0];

				return <div className={"d-block w-100"}>
					{mediaFile.getType() === MediaFileType.IMAGE ?
						<div className={"border border-mainColor bg-dark"} style={{
							backgroundImage: 'url("' + mediaFile.getURL() + '")',
							backgroundSize: "cover",
							backgroundPosition: "center",
							cursor: "pointer"
						}}>
							<img src={mediaFile.getURL()} style={{
								maxHeight: "500px",
								width: "100%",
								height: "100%",
								visibility: "hidden"
							}} alt={mediaFile.getId()}/>
						</div> : ""}
				</div>;
			} else if (attachments.length === 2) {
				return <div style={{
					height: "537px"
				}}>
					{attachments.map((mediaFile: MediaFile, i: number) => {
						return mediaFile.getType() === MediaFileType.IMAGE ?
							<div key={i} className={"d-inline-block"} style={{
								width: "50%",
								position: "relative",
								height: "100%"
							}}>
								<div className={"border border-mainColor bg-dark" + (i === 1 ? " border-left-0" : "")}
									 style={{
										 maxHeight: "500px",
										 height: "100%",
										 backgroundImage: 'url("' + mediaFile.getURL() + '")',
										 backgroundSize: "cover",
										 backgroundPosition: "center",
										 cursor: "pointer"
									 }}/>
							</div> : "";
					})}
				</div>;
			} else if (attachments.length === 3) {
				return <div style={{
					height: "537px"
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
							backgroundImage: 'url("' + attachments[0].getURL() + '")',
							backgroundSize: "cover",
							backgroundPosition: "center",
							cursor: "pointer"
						}}/>
					</div>

					<div className={"d-inline-block"} style={{
						width: "50%",
						height: "100%"
					}}>
						<div className={"border border-mainColor border-left-0 bg-dark mr-2"} style={{
							maxHeight: "537px",
							width: "100%",
							height: "50%",
							backgroundImage: 'url("' + attachments[1].getURL() + '")',
							backgroundSize: "cover",
							backgroundPosition: "center",
							cursor: "pointer"
						}}/>

						<div className={"border border-mainColor border-left-0 border-top-0 bg-dark mr-2"} style={{
							maxHeight: "537px",
							width: "100%",
							height: "50%",
							backgroundImage: 'url("' + attachments[2].getURL() + '")',
							backgroundSize: "cover",
							backgroundPosition: "center",
							cursor: "pointer"
						}}/>
					</div>
				</div>;
			} else if (attachments.length === 4) {
				return <div style={{
					height: "537px"
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
							backgroundImage: 'url("' + attachments[0].getURL() + '")',
							backgroundSize: "cover",
							cursor: "pointer"
						}}/>

						<div className={"border border-mainColor border-top-0 bg-dark mr-2"} style={{
							maxHeight: "500px",
							width: "100%",
							height: "50%",
							backgroundImage: 'url("' + attachments[1].getURL() + '")',
							backgroundSize: "cover",
							cursor: "pointer"
						}}/>
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
							backgroundImage: 'url("' + attachments[2].getURL() + '")',
							backgroundSize: "cover",
							cursor: "pointer"
						}}/>

						<div className={"border border-mainColor border-left-0 border-top-0 bg-dark mr-2"} style={{
							maxHeight: "500px",
							width: "100%",
							height: "50%",
							backgroundImage: 'url("' + attachments[3].getURL() + '")',
							backgroundSize: "cover",
							cursor: "pointer"
						}}/>
					</div>
				</div>;
			}
		}

		return "";
	}
}