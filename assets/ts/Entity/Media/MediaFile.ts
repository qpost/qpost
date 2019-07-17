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

@JsonObject("MediaFile")
export default class MediaFile {
	@JsonProperty("id", String)
	private id: string = undefined;

	@JsonProperty("sha256", String)
	private sha256: string = undefined;

	@JsonProperty("url", String)
	private url: string = undefined;

	@JsonProperty("type", String)
	private type: string = undefined;

	@JsonProperty("time", String)
	private time: string = undefined;

	public getId(): string {
		return this.id;
	}

	public getSHA256(): string {
		return this.sha256;
	}

	public getURL(): string {
		return this.url;
	}

	public getType(): string {
		return this.type;
	}

	public getTime(): string {
		return this.time;
	}
}