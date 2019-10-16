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

import {JsonObject, JsonProperty} from "json2typescript";

@JsonObject("User")
export default class User {
	@JsonProperty("id", Number)
	private id: number = undefined;

	@JsonProperty("displayName", String)
	private displayName: string = undefined;

	@JsonProperty("username", String)
	private username: string = undefined;

	@JsonProperty("avatarURL", String)
	private avatarURL: string = undefined;

	@JsonProperty("bio", String, true)
	private bio?: string = undefined;

	@JsonProperty("birthday", String, true)
	private birthday?: string = undefined;

	@JsonProperty("privacyLevel", String)
	private privacyLevel: string = undefined;

	@JsonProperty("time", String)
	private time: string = undefined;

	@JsonProperty("verified", Boolean)
	private verified: boolean = undefined;

	@JsonProperty("suspended", Boolean)
	private suspended: boolean = undefined;

	@JsonProperty("postCount", Number)
	private postCount: number = undefined;

	@JsonProperty("replyCount", Number)
	private replyCount: number = undefined;

	@JsonProperty("shareCount", Number)
	private shareCount: number = undefined;

	@JsonProperty("followingPostCount", Number)
	private followingPostCount: number = undefined;

	@JsonProperty("totalPostCount", Number)
	private totalPostCount: number = undefined;

	@JsonProperty("followingCount", Number)
	private followingCount: number = undefined;

	@JsonProperty("followerCount", Number)
	private followerCount: number = undefined;

	@JsonProperty("favoritesCount", Number)
	private favoritesCount: number = undefined;

	@JsonProperty("followsYou", Boolean)
	private followsYou: boolean = undefined;

	public getId(): number {
		return this.id;
	}

	public getDisplayName(): string {
		return this.displayName;
	}

	public getUsername(): string {
		return this.username;
	}

	public getAvatarURL(): string {
		return this.avatarURL;
	}

	public getBio(): string | undefined {
		return this.bio;
	}

	public getBirthday(): string | undefined {
		return this.birthday;
	}

	public getPrivacyLevel(): string {
		return this.privacyLevel;
	}

	public getTime(): string | undefined {
		return this.time;
	}

	public isVerified(): boolean {
		return this.verified;
	}

	public isSuspended(): boolean {
		return this.suspended;
	}

	public getPostCount(): number {
		return this.postCount;
	}

	public getReplyCount(): number {
		return this.replyCount;
	}

	public getShareCount(): number {
		return this.shareCount;
	}

	public getFollowingPostCount(): number {
		return this.followingPostCount;
	}

	public getTotalPostCount(): number {
		return this.totalPostCount;
	}

	public getFollowingCount(): number {
		return this.followingCount;
	}

	public getFollowerCount(): number {
		return this.followerCount;
	}

	public getFavoritesCount(): number {
		return this.favoritesCount;
	}

	public isFollowingYou(): boolean {
		return this.followsYou;
	}

	public getCharacterLimit(): number {
		return !this.isVerified() ? window["POST_CHARACTER_LIMIT"] : window["VERIFIED_POST_CHARACTER_LIMIT"];
	}
}