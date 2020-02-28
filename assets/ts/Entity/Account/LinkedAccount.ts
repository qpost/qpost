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

import {JsonObject, JsonProperty} from "json2typescript";

@JsonObject("LinkedAccount")
export default class LinkedAccount {
	@JsonProperty("id", Number)
	private id: number = undefined;

	@JsonProperty("service", String)
	private service: string = undefined;

	@JsonProperty("linkedUserId", String)
	private linkedUserId: string = undefined;

	@JsonProperty("linkedUserName", String)
	private linkedUserName: string = undefined;

	@JsonProperty("linkedUserAvatar", String, true)
	private linkedUserAvatar: string | null = undefined;

	@JsonProperty("time", String)
	private time: string = undefined;

	@JsonProperty("lastUpdate", String)
	private lastUpdate: string = undefined;

	public getId(): number {
		return this.id;
	}

	public getService(): string {
		return this.service;
	}

	public getLinkedUserId(): string {
		return this.linkedUserId;
	}

	public getLinkedUserName(): string {
		return this.linkedUserName;
	}

	public getLinkedUserAvatar(): string {
		return this.linkedUserAvatar;
	}

	public getTime(): string {
		return this.time;
	}

	public getLastUpdate(): string {
		return this.lastUpdate;
	}
}