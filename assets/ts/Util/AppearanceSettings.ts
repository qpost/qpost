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

export default class AppearanceSettings {
	public static enablesNightMode(): boolean {
		return this.readValue("nightMode", false);
	}

	public static autoplayGIFs(): boolean {
		return this.readValue("autoplayGifs", false);
	}

	public static showTrendingTopics(): boolean {
		return this.readValue("showTrends", true);
	}

	public static showSuggestedUsers(): boolean {
		return this.readValue("showSuggestedUsers", true);
	}

	public static showUpcomingBirthdays(): boolean {
		return this.readValue("showBirthdays", true);
	}

	public static showMatureWarning(): boolean {
		return this.readValue("showMatureWarning", true);
	}

	private static data() {
		return window["APPEARANCE_SETTINGS"] || {};
	}

	private static readValue(key: string, defaultValue) {
		const data = this.data();

		return data && data.hasOwnProperty(key) ? data[key] : defaultValue;
	}
}