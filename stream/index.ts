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

const env = process.env.APP_ENV || "dev";

const winston = require("winston");
const consoleFormat = require("winston-console-format").consoleFormat;

// Init logger
const log = winston.createLogger({
	level: env === "dev" ? "debug" : "info",
	format: winston.format.combine(
		winston.format.timestamp(),
		winston.format.errors({ stack: true }),
		winston.format.splat(),
		winston.format.json()
	),
	transports: [
		new winston.transports.Console({
			format: winston.format.combine(
				winston.format.colorize({ all: true }),
				winston.format.padLevels(),
				consoleFormat({
					showMeta: true,
					metaStrip: ["timestamp", "service"],
					inspectOptions: {
						depth: Infinity,
						colors: true,
						maxArrayLength: Infinity,
						breakLength: 120,
						compact: Infinity
					}
				})
			)
		})
	]
});

log.info("Hello world!");