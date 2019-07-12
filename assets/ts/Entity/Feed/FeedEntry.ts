import User from "../Account/User";
import {JsonObject, JsonProperty} from "json2typescript";
import MediaFile from "../Media/MediaFile";

@JsonObject("FeedEntry")
export default class FeedEntry {
	@JsonProperty("id", Number)
	private id: number = undefined;

	@JsonProperty("user", User)
	private user: User = undefined;

	@JsonProperty("text", String, true)
	private text?: string = undefined;

	@JsonProperty("following", User, true)
	private following?: User = undefined;

	@JsonProperty("post", FeedEntry, true)
	private post?: FeedEntry = undefined;

	@JsonProperty("type", String)
	private type: string = undefined;

	@JsonProperty("nSFW", Boolean)
	private nsfw: boolean = undefined;

	@JsonProperty("attachments", [MediaFile])
	private attachments: MediaFile[] = undefined;

	@JsonProperty("time", String)
	private time: string = undefined;

	@JsonProperty("replyCount", Number)
	private replyCount: number = undefined;

	@JsonProperty("shareCount", Number)
	private shareCount: number = undefined;

	@JsonProperty("favoriteCount", Number)
	private favoriteCount: number = undefined;

	@JsonProperty("shared", Boolean)
	private shared: boolean = undefined;

	@JsonProperty("favorited", Boolean)
	private favorited: boolean = undefined;

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
		return this.nsfw;
	}

	public getAttachments(): MediaFile[] {
		return this.attachments;
	}

	public getTime(): string {
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

	public isShared(): boolean {
		return this.shared;
	}

	public isFavorited(): boolean {
		return this.favorited;
	}
}