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

export default class Storage {
	public static readonly SESSION_TRENDING_TOPICS = "trendingTopics";
	public static readonly SESSION_UPCOMING_BIRTHDAYS = "upcomingBirthdays";
	public static readonly SESSION_SUGGESTED_USERS = "suggestedUsers";

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
}