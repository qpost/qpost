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

import StoredToken from "./StoredToken";
import qpostAPI from "../api/src/API/qpostAPI";
import Auth from "./Auth";
import API from "../API";
import Cookies from "js-cookie";
import User from "../api/src/Entity/User";

export default class TokenStorage {
	public static tokens: StoredToken[] = [];
	public static LIMIT: number = 10;

	public static async loadTokens(): Promise<StoredToken[]> {
		return new Promise<StoredToken[]>(async (resolve) => {
			const tokens: string[] = this.getCurrentTokens();
			const final: StoredToken[] = [];

			for (const token of tokens) {
				try {
					const api = new qpostAPI(API.getBaseURL(), token);
					const user = await api.token.verify();

					if (!this.hasUser(user, final)) {
						const storedToken = new StoredToken(token, user);
						final.push(storedToken);
					}
				} catch (err) {
					console.error("Invalid token: " + token + ", skipping.");
				}
			}

			this.tokens = final;
			this.saveTokens();

			return resolve(this.tokens);
		});
	}

	private static hasUser(user: User, tokens: StoredToken[]): boolean {
		for (let token of tokens) {
			if (token.getUser().getId() === user.getId()) {
				return true;
			}
		}

		return false;
	}

	public static getNextToken(remove?: boolean): string | undefined {
		const nextToken = this.tokens.length > 1 ? this.tokens[1].getId() : undefined;

		if (nextToken && remove) {
			this.removeToken(nextToken);
			this.saveTokens();
		}

		return nextToken;
	}

	public static switchUser(token: StoredToken | string): void {
		const id = (token instanceof StoredToken) ? token.getId() : token;

		this.setCurrentToken(id);
		window.location.href = "/";
	}

	public static setCurrentToken(token?: string): void {
		const newArray = [];

		if (token) {
			newArray.push(token);
		}

		this.getCurrentTokens().forEach(value => {
			if ((token && value === token) || (newArray.indexOf(value) > -1)) return;

			newArray.push(value);
		});

		Cookies.set("qpoststoredtokens", JSON.stringify(newArray), {expires: 30});

		const ReactNativeWebView = window["ReactNativeWebView"];
		if (ReactNativeWebView) {
			ReactNativeWebView.postMessage({
				type: "token",
				token: Auth.getToken()
			});
		}
	}

	public static getCurrentTokens(): string[] {
		if (typeof Cookies.get("qpoststoredtokens") === "undefined") return [];

		return JSON.parse(Cookies.get("qpoststoredtokens"));
	}

	public static saveTokens(tokensToSave?: StoredToken[]): void {
		if (!tokensToSave) tokensToSave = this.tokens;

		const tokens: string[] = [];

		tokensToSave.forEach(token => {
			if (tokens.indexOf(token.getId()) > -1) return;

			tokens.push(token.getId());
		});

		Cookies.set("qpoststoredtokens", JSON.stringify(tokens), {expires: 30});
	}

	public static killAll(): Promise<StoredToken[]> {
		return new Promise<StoredToken[]>(async resolve => {
			for (let token of this.tokens) {
				const api = new qpostAPI(API.getBaseURL(), token.getId());

				await api.token.delete(token.getId());
			}

			this.tokens = [];
			return resolve(this.tokens);
		});
	}

	private static removeToken(id: string): void {
		const newTokens = [];

		for (let token of this.tokens) {
			if (token.getId() === id) {
				continue;
			}

			newTokens.push(token);
		}

		this.tokens = newTokens;
	}
}