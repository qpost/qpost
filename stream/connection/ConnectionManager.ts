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

import Connection from "./Connection";

export default class ConnectionManager {
	private static connections: Connection[] = [];

	public static getConnection(id: string): Connection {
		for (let connection of this.connections) {
			if (connection.id === id) {
				return connection;
			}
		}

		const connection = new Connection(id);
		this.connections.push(connection);

		return connection;
	}

	public static getConnections(): Connection[] {
		return this.connections;
	}

	public static unregister(connection: Connection): void {
		// https://stackoverflow.com/a/15295806/4117923
		const index = this.connections.indexOf(connection, 0);
		if (index > -1) {
			this.connections.splice(index, 1);
		}
	}
}