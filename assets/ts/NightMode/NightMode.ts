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

import $ from "jquery";
import AppearanceSettings from "../Util/AppearanceSettings";

export default class NightMode {
	public static init(): void {
		if (window["FORCE_DISABLE_NIGHTMODE"]) {
			return;
		}

		// load night mode
		NightMode.setActive(NightMode.isActive());
	}

	public static setActive(active: boolean, updateClass?: boolean): void {
		if (typeof updateClass === "undefined") updateClass = true;

		if (active) {
			if (updateClass) {
				$("#mainNav").addClass("bg-dark").removeClass("bg-primary");
				$("html").addClass("nightMode");
			}

			localStorage.setItem("nightMode", "true");
		} else {
			if (updateClass) {
				$("#mainNav").addClass("bg-primary").removeClass("bg-dark");
				$("html").removeClass("nightMode");
			}

			localStorage.removeItem("nightMode");
		}
	}

	public static isActive(): boolean {
		return AppearanceSettings.enablesNightMode();
	}

	public static toggle(): void {
		this.setActive(!this.isActive(), false);

		window.location.reload();
	}

	public static spinnerColor(): string {
		return this.isActive() ? "light" : "primary";
	}
}