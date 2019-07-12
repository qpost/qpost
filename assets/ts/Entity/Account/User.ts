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
}