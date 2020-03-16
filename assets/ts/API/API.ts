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
import BadgeStatusEndpoint from "./Endpoint/BadgeStatusEndpoint";
import BirthdaysEndpoint from "./Endpoint/BirthdaysEndpoint";
import BlockEndpoint from "./Endpoint/BlockEndpoint";
import FavoriteEndpoint from "./Endpoint/FavoriteEndpoint";
import FeedEndpoint from "./Endpoint/FeedEndpoint";
import FollowEndpoint from "./Endpoint/FollowEndpoint";
import NotificationsEndpoint from "./Endpoint/NotificationsEndpoint";
import RepliesEndpoint from "./Endpoint/RepliesEndpoint";
import SearchEndpoint from "./Endpoint/SearchEndpoint";
import ShareEndpoint from "./Endpoint/ShareEndpoint";
import StatusEndpoint from "./Endpoint/StatusEndpoint";
import SuggestedUsersEndpoint from "./Endpoint/SuggestedUsersEndpoint";
import TokenEndpoint from "./Endpoint/TokenEndpoint";
import TrendsEndpoint from "./Endpoint/TrendsEndpoint";
import UserEndpoint from "./Endpoint/UserEndpoint";
import FollowersYouKnowEndpoint from "./Endpoint/FollowersYouKnowEndpoint";
import FollowRequestEndpoint from "./Endpoint/FollowRequestEndpoint";

export default class API {
	public static readonly badgeStatus: BadgeStatusEndpoint = new BadgeStatusEndpoint();
	public static readonly birthdays: BirthdaysEndpoint = new BirthdaysEndpoint();
	public static readonly block: BlockEndpoint = new BlockEndpoint();
	public static readonly favorite: FavoriteEndpoint = new FavoriteEndpoint();
	public static readonly feed: FeedEndpoint = new FeedEndpoint();
	public static readonly follow: FollowEndpoint = new FollowEndpoint();
	public static readonly followersYouKnow: FollowersYouKnowEndpoint = new FollowersYouKnowEndpoint();
	public static readonly followRequest: FollowRequestEndpoint = new FollowRequestEndpoint();
	public static readonly notifications: NotificationsEndpoint = new NotificationsEndpoint();
	public static readonly replies: RepliesEndpoint = new RepliesEndpoint();
	public static readonly search: SearchEndpoint = new SearchEndpoint();
	public static readonly share: ShareEndpoint = new ShareEndpoint();
	public static readonly status: StatusEndpoint = new StatusEndpoint();
	public static readonly suggestedUsers: SuggestedUsersEndpoint = new SuggestedUsersEndpoint();
	public static readonly token: TokenEndpoint = new TokenEndpoint();
	public static readonly trends: TrendsEndpoint = new TrendsEndpoint();
	public static readonly user: UserEndpoint = new UserEndpoint();

	/**
	 * The axios instance to be used.
	 */
	public static readonly http: AxiosInstance = axios.create({
		baseURL: window.location.protocol + "//" + window.location.host + "/api",
		headers: Auth.isLoggedIn() ? {"Authorization": "Bearer " + Auth.getToken()} : {}
	});

	/**
	 * Creates a request to the qpost API server.
	 *
	 * @param url The url to be requested.
	 * @param method The HTTP method to be used.
	 * @param data The request data as an object.
	 * @param callback The callback to be executed on a successful request.
	 * @param errorCallback The callback to be executed if the request fails.
	 * @deprecated
	 */
	public static handleRequest(url: string, method?: Method, data?: any, callback?: (data: any) => void, errorCallback?: (error: string) => void): void {
		this.handleRequestWithPromise(url, method, data).then(value => {
			if (callback) {
				callback(value);
			}
		}).catch(reason => {
			if (errorCallback) {
				errorCallback(reason);
			}
		});
	}

	/**
	 * Creates a request to the qpost API server with a Promise return.
	 *
	 * @param url The url to be requested.
	 * @param method The HTTP method to be used.
	 * @param data The request data as an object.
	 */
	public static handleRequestWithPromise(url: string, method?: Method, data?: any): Promise<any> {
		method = method || "GET";
		data = data || {};

		return new Promise<any>((resolve, reject) => {
			if (!url) {
				return reject("No URL specified.");
			}

			this.http.request({
				method,
				url,
				data: method !== "GET" ? data : {},
				params: method === "GET" ? data : {}
			}).then(response => {
				if (response.data || (Math.floor(response.status / 100) == 2)) {
					if (response.data.error) {
						return reject(response.data.error);
					} else {
						resolve(response.data);
					}
				} else {
					reject("An error occurred.");
				}
			}).catch(error => {
				console.error(error);
				const response = error.response;

				let errorMessage = "An error occurred.";
				if (response && response.data && response.data.error) {
					errorMessage = response.data.error;
				}

				reject(errorMessage);
			})
		});
	}
}