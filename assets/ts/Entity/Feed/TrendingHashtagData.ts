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
import Hashtag from "./Hashtag";

@JsonObject("TrendingHashtagData")
export default class TrendingHashtagData {
	@JsonProperty("id", Number)
	private id: number = undefined;

	@JsonProperty("postsThisWeek", Number)
	private postsThisWeek: number = undefined;

	@JsonProperty("time", String)
	private time: string = undefined;

	@JsonProperty("hashtag", Hashtag)
	private hashtag: Hashtag = undefined;

	public getId(): number {
		return this.id;
	}

	public getPostsThisWeek(): number {
		return this.postsThisWeek;
	}

	public getTime(): string {
		return this.time;
	}

	public getHashtag(): Hashtag {
		return this.hashtag;
	}
}