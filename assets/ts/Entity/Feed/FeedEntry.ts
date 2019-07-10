import User from "../Account/User";
import SerializedDateTime from "../../Serialization/Date/SerializedDateTime";

export default class FeedEntry {
	private id: number;
	private user: User;
	private text?: string;
	private following?: User;
	private post?: FeedEntry;
	private type: string;
	private nSFW: boolean;
	//private attachments; TODO
	private time: SerializedDateTime;
	private replyCount: number;
	private shareCount: number;
	private favoriteCount: number;

	public getId(): number {
		return this.id;
	}

	public getUser(): User {
		return this.user;
	}

	public getText(): string | undefined {
		return this.text;
	}

	public getFollowing(): User | undefined {
		return this.following;
	}

	public getPost(): FeedEntry | undefined {
		return this.post;
	}

	public getType(): string {
		return this.type;
	}

	public isNSFW(): boolean {
		return this.nSFW;
	}

	public getTime(): SerializedDateTime {
		return this.time;
	}

	public getReplyCount(): number {
		return this.replyCount;
	}

	public getShareCount(): number {
		return this.shareCount;
	}

	public getFavoriteCount(): number {
		return this.favoriteCount;
	}
}