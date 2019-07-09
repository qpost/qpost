import SerializedDateTime from "../../Serialization/Date/SerializedDateTime";

export default class User {
	private id: number;
	private displayName: string;
	private username: string;
	private avatarURL: string;
	private bio?: string;
	private birthday?: SerializedDateTime;
	private privacyLevel: string;
	private time: SerializedDateTime;
	private verified: boolean;
	private suspended: boolean;
	private postCount: number;
	private replyCount: number;
	private shareCount: number;
	private followingPostCount: number;
	private totalPostCount: number;
	private followingCount: number;
	private followerCount: number;

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

	public getBirthday(): SerializedDateTime | undefined {
		return this.birthday;
	}

	public getPrivacyLevel(): string {
		return this.privacyLevel;
	}

	public getTime(): SerializedDateTime | undefined {
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