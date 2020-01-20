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
		return this.data()["nightMode"] || false;
	}

	public static autoplayGIFs(): boolean {
		return this.data()["autoplayGifs"] || false;
	}

	public static showTrendingTopics(): boolean {
		return this.data()["showTrendingTopics"] || true;
	}

	public static showSuggestedUsers(): boolean {
		return this.data()["showSuggestedUsers"] || true;
	}

	public static showUpcomingBirthdays(): boolean {
		return this.data()["showUpcomingBirthdays"] || true;
	}

	public static showMatureWarning(): boolean {
		return this.data()["showMatureWarning"] || true;
	}

	private static data() {
		return window["APPEARANCE_SETTINGS"] || {};
	}
}