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

import User from "../api/src/Entity/User";

export default class StoredToken {
	private readonly id: string;
	private user: User;

	constructor(id: string, user: User) {
		this.id = id;
		this.user = user;
	}

	public getId(): string {
		return this.id;
	}

	public getUser(): User {
		return this.user;
	}

	public setUser(user: User): void {
		this.user = user;
	}
}