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

// Load environment variables
const path = require("path");
require("dotenv").config({
	path: path.resolve(__dirname, "../.env.local")
});

// Load constants
const version = require(path.resolve(__dirname, "../package.json")).version || "1.0.0";
const log = require("./logger");
const express = require("express");
const port = process.env.PORT || 8993;

log.info("Starting Stream API for qpost v" + version);

// Start express
const app = express();
app.set("port", port);

const http = require("http").Server(app);
const io = require("socket.io")(http);

const server = http.listen(port, () => {
	log.info("Listening on port " + port);
});

// Connection listener
io.on("connection", socket => {
	log.info("Incoming connection");

	socket.on("message", message => {
		log.debug("Incoming message: " + message);
	});
});