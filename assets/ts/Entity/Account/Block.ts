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
import User from "./User";

@JsonObject("Block")
export default class Block {
	@JsonProperty("id", Number)
	private id: number = undefined;

	@JsonProperty("user", User)
	private user: User = undefined;

	@JsonProperty("target", User)
	private target: User = undefined;

	@JsonProperty("time", String)
	private time: string = undefined;

	public getId(): number {
		return this.id;
	}

	public getUser(): User {
		return this.user;
	}

	public getTarget(): User {
		return this.target;
	}

	public getTime(): string {
		return this.time;
	}
}