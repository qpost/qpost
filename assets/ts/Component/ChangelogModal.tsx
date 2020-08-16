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
import API from "../API";
import {Modal} from "antd";
import __ from "../i18n/i18n";
import TimeAgo from "./TimeAgo";

export default class ChangelogModal extends Component<any, {
	open: boolean
}> {
	private static CHANGELOG: { tag: string, description: string, time: string } | null = null;

	constructor(props) {
		super(props);

		this.state = {
			open: ChangelogModal.CHANGELOG !== null
		};
	}

	public static loadChangelog(): Promise<void> {
		return new Promise<void>(resolve => {
			API.i.handleRequestWithPromise("/changelog", "GET").then(value => {
				if (value !== "" && value !== {}) {
					this.CHANGELOG = value;
				}

				resolve();
			})
		});
	}

	render() {
		return ChangelogModal.CHANGELOG !== null ? <Modal
			title={__("changelogModal.headline", {"%version%": ChangelogModal.CHANGELOG.tag})}
			visible={this.state.open}
			onCancel={() => {
				this.setState({
					open: false
				})
			}}
			footer={
				<div className={"small text-left"}>
					<TimeAgo time={ChangelogModal.CHANGELOG.time}/><br/>
					<a href={window["GITLAB_RELEASES_URL"]} target={"_blank"}>{__("changelogModal.footer.text")}</a>
				</div>
			}
		>
			<div className={"changelogModalBody"}>
				<div dangerouslySetInnerHTML={{__html: ChangelogModal.CHANGELOG.description}}/>
			</div>
		</Modal> : "";
	}
}