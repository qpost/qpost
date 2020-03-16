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

import {JsonObject, JsonProperty} from "json2typescript";
import IpStackResult from "./IpStackResult";

@JsonObject("Token")
export default class Token {
	@JsonProperty("id", String)
	private id: string = undefined;

	@JsonProperty("lastIP", String)
	private lastIP: string = undefined;

	@JsonProperty("userAgent", String)
	private userAgent: string = undefined;

	@JsonProperty("time", String)
	private time: string = undefined;

	@JsonProperty("lastAccessTime", String)
	private lastAccessTime: string = undefined;

	@JsonProperty("expiry", String)
	private expiry: string = undefined;

	@JsonProperty("ipStackResult", IpStackResult, true)
	private ipStackResult: IpStackResult | null = undefined;

	@JsonProperty("notifications", Boolean)
	private notifications: boolean = undefined;

	public getId(): string {
		return this.id;
	}

	public getLastIP(): string {
		return this.lastIP;
	}

	public getUserAgent(): string {
		return this.userAgent;
	}

	public getTime(): string {
		return this.time;
	}

	public getLastAccessTime(): string {
		return this.lastAccessTime;
	}

	public getExpiry(): string {
		return this.expiry;
	}

	public getIPStackResult(): IpStackResult | null {
		return this.ipStackResult;
	}

	public hasNotifications(): boolean {
		return this.notifications;
	}
}