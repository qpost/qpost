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

import {sleep} from "./Thread";

export default class Storage {
	public static readonly SESSION_TRENDING_TOPICS = "trendingTopics";
	public static readonly SESSION_UPCOMING_BIRTHDAYS = "upcomingBirthdays";
	public static readonly SESSION_SUGGESTED_USERS = "suggestedUsers";
	public static readonly SESSION_FEED_ENTRY_LIST = "feedEntryList";
	public static readonly SESSION_USER = "user";

	public static sessionGet(key) {
		const stringValue = window.sessionStorage.getItem(key);

		if (stringValue !== null) {
			const value = JSON.parse(stringValue);
			const expirationDate = new Date(value.expirationDate);
			if (expirationDate > new Date()) {
				return value.value;
			} else {
				window.sessionStorage.removeItem(key);
			}
		}

		return null;
	}

	public static sessionSet(key, value, expirationInMin = 10): void {
		const expirationDate = new Date(new Date().getTime() + (60000 * expirationInMin));

		const newValue = {
			value: value,
			expirationDate: expirationDate.toISOString()
		};

		window.sessionStorage.setItem(key, JSON.stringify(newValue))
	}

	public static clean(): void {
		for (let i = 0; i < window.sessionStorage.length; i++) {
			Storage.sessionGet(window.sessionStorage.key(i)); // call get, which automatically removes expired items
		}
	}

	public static cleanTask(): void {
		// periodically clean expired items to avoid storage limit

		// https://stackoverflow.com/a/48882182/4117923
		(async () => {
			Storage.clean();
			await sleep(3000);
			Storage.cleanTask();
		})();
	}
}