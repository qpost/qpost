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

import ConnectionStatus from "./ConnectionStatus";
import StreamListenerManager from "../../assets/ts/api/src/Stream/Listener/StreamListenerManager";
import AuthorizationRequestListener from "../listener/AuthorizationRequestListener";
import {Socket} from "socket.io";
import User from "../../assets/ts/api/src/Entity/User";
import StreamMessage from "../../assets/ts/api/src/Stream/Message/StreamMessage";
import BaseObject from "../../assets/ts/api/src/BaseObject";

export default class Connection {
	public id: string;
	public status: ConnectionStatus;
	public idleTimer: number;
	public timeConnected: number;
	public listenerManager: StreamListenerManager;
	public socket: Socket;
	public token: string;
	public user: User;

	constructor(id: string) {
		this.id = id;
		this.status = ConnectionStatus.UNAUTHORIZED;
		this.idleTimer = undefined;
		this.timeConnected = new Date().getTime();
		this.listenerManager = new StreamListenerManager();

		this.registerListeners();
		this.startIdleTimer();
	}

	public startIdleTimer(): void {
		this.stopIdleTimer();

		this.idleTimer = setTimeout(() => {
			if (this.status === ConnectionStatus.UNAUTHORIZED) {
				this.socket.disconnect(true);
			}
		}, 5000);
	}

	public stopIdleTimer(): void {
		if (this.idleTimer) {
			clearTimeout(this.idleTimer);
			this.idleTimer = undefined;
		}
	}

	public sendMessage(message: StreamMessage): void {
		this.socket.write(BaseObject.serializeObject(message));
	}

	private registerListeners(): void {
		this.listenerManager.registerListener(new AuthorizationRequestListener(this));
	}
}