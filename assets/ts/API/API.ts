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

import axios, {AxiosInstance, Method} from "axios";
import Auth from "../Auth/Auth";

export default class API {
	/**
	 * The axios instance to be used.
	 */
	private static http: AxiosInstance = axios.create({
		baseURL: window.location.protocol + "//" + window.location.host + "/api",
		headers: Auth.isLoggedIn() ? {"Authorization": "Bearer " + Auth.getToken()} : {}
	});

	/**
	 * Creates a request to the qpost API server.
	 *
	 * @param url The url to be requested.
	 * @param method The HTTP method to be used.
	 * @param data The response data as an object.
	 * @param callback The callback to be executed on a successful request.
	 * @param errorCallback The callback to be executed if the request fails.
	 */
	public static handleRequest(url: string, method?: Method, data?: any, callback?: (data: any) => void, errorCallback?: (error: string) => void): void {
		if (url) {
			method = method || "GET";

			this.http.request({
				method,
				url,
				data: method !== "GET" ? data : {},
				params: method === "GET" ? data : {}
			}).then(response => {
				if (response.data || (Math.floor(response.status / 100) == 2)) {
					if (response.data.error) {
						if (errorCallback) {
							errorCallback(response.data.error);
						}
					} else {
						if (callback) {
							callback(response.data);
						}
					}
				} else {
					if (errorCallback) {
						errorCallback("An error occurred.");
					}
				}
			}).catch(error => {
				console.error(error);
				const response = error.response;

				let errorMessage = "An error occurred.";
				if (response && response.data && response.data.error) {
					errorMessage = response.data.error;
				}

				if (errorCallback) {
					errorCallback(errorMessage);
				}
			})
		} else {
			throw new Error("No URL specified");
		}
	}
}