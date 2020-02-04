/*
 * Copyright (C) 2018-2020 Gigadrive - All rights reserved.
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

import User from "../Account/User";
import FeedEntry from "./FeedEntry";
import {JsonProperty} from "json2typescript";
import FollowRequest from "../Account/FollowRequest";

export default class Notification {
	@JsonProperty("id", Number)
	private id: number = undefined;

	@JsonProperty("user", User)
	private user: User = undefined;

	@JsonProperty("type", String)
	private type: string = undefined;

	@JsonProperty("referencedUser", User, true)
	private referencedUser?: User = undefined;

	@JsonProperty("referencedFeedEntry", FeedEntry, true)
	private referencedFeedEntry?: FeedEntry = undefined;

	@JsonProperty("referencedFollowRequest", FollowRequest, true)
	private referencedFollowRequest?: FollowRequest = undefined;

	@JsonProperty("seen", Boolean)
	private seen: boolean = undefined;

	@JsonProperty("notified", Boolean)
	private notified: boolean = undefined;

	@JsonProperty("time", String)
	private time: string = undefined;

	public getId(): number {
		return this.id;
	}

	public getUser(): User {
		return this.user;
	}

	public getType(): string {
		return this.type;
	}

	public getReferencedUser(): User {
		return this.referencedUser;
	}

	public getReferencedFeedEntry(): FeedEntry {
		return this.referencedFeedEntry;
	}

	public getReferencedFollowRequest(): FollowRequest {
		return this.referencedFollowRequest;
	}

	public isSeen(): boolean {
		return this.seen;
	}

	public isNotified(): boolean {
		return this.notified;
	}

	public getTime(): string {
		return this.time;
	}
}