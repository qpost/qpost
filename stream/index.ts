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

// Load environment variables
import path from "path";
import dotenv from "dotenv";
import log from "./logger";
import express from "express";
import {Server} from "http";
import SocketIO from "socket.io";
import ConnectionManager from "./connection/ConnectionManager";
import ConnectionStatus from "./connection/ConnectionStatus";

const __dirname = path.resolve('');

dotenv.config({
	path: path.resolve(__dirname, "../.env.local")
});

// Load constants
const version = process.env.VERSION || "1.0.0";
const port = process.env.PORT || 8993;

log.info("Starting Stream API for qpost v" + version);

// Start express
const app = express();
app.set("port", port);

const http = new Server(app);
const io: SocketIO.Server = SocketIO(http);

const server = http.listen(port, () => {
	log.info("Listening on port " + port);
});

// Connection listener
io.on("connection", socket => {
	log.debug("Connected: " + socket.id);

	const connection = ConnectionManager.getConnection(socket.id);
	connection.idleTimer = setTimeout(() => {
		if (connection.status === ConnectionStatus.UNAUTHORIZED) {
			socket.disconnect(true);
		}
	}, 3000);

	socket.on("message", message => {
		log.debug("Incoming message: " + message);
	});

	socket.on("disconnect", reason => {
		log.debug("Disconnected: " + socket.id + " (" + reason + ")");

		const connection = ConnectionManager.getConnection(socket.id);

		if (connection.idleTimer && typeof connection.idleTimer === "number") {
			clearTimeout(connection.idleTimer);
			connection.idleTimer = undefined;
		}

		ConnectionManager.unregister(connection);
	});
});