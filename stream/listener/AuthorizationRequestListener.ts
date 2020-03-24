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

import AuthorizationRequestMessage from "../../assets/ts/api/src/Stream/Message/AuthorizationRequestMessage";
import ServerStreamListener from "./ServerStreamListener";
import qpostAPI from "../../assets/ts/api/src/API/qpostAPI";
import log from "../logger";
import ConnectionStatus from "../connection/ConnectionStatus";
import AuthorizationResponseMessage from "../../assets/ts/api/src/Stream/Message/AuthorizationRequestResponse";

export default class AuthorizationRequestListener extends ServerStreamListener {
	onAuthorizationRequestMessage(message: AuthorizationRequestMessage): void {
		const connection = this.getConnection();
		connection.stopIdleTimer();

		if (message.type === "client") {
			new qpostAPI(process.env.API_ENDPOINT, message.token).token.verify().then(user => {
				log.info("User connected: @" + user.getUsername() + " (ID " + user.getId() + ")");

				connection.status = ConnectionStatus.CLIENT_AUTHORIZED;
				connection.user = user;
				connection.token = message.token;

				const response = new AuthorizationResponseMessage();
				response.ok = true;
				response.message = "Authorization successful";
				response.user = user;

				connection.sendMessage(response);
			}).catch(reason => {
				log.error("Connection " + this.getConnection().id + " failed to authenticate (" + reason + ").");

				connection.startIdleTimer();

				const response = new AuthorizationResponseMessage();
				response.ok = false;
				response.message = reason;

				connection.sendMessage(response);
			});
		} else if (message.type === "server") {
			// TODO
		} else {
			const response = new AuthorizationResponseMessage();
			response.ok = false;
			response.message = "Invalid 'type' parameter value";

			connection.sendMessage(response);
		}
	}
}