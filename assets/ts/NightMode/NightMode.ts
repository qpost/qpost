/*
 * Copyright (C) 2018-2019 Gigadrive - All rights reserved.
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

import $ from "jquery";

export default class NightMode {
	public static init(): void {
		if (window["FORCE_DISABLE_NIGHTMODE"]) {
			return;
		}

		// load night mode
		NightMode.setActive(NightMode.isActive());
	}

	public static setActive(active: boolean): void {
		if (active) {
			$("#mainNav").addClass("bg-dark").removeClass("bg-primary");
			$("html").addClass("nightMode");

			localStorage.setItem("nightMode", "true");
		} else {
			$("#mainNav").addClass("bg-primary").removeClass("bg-dark");
			$("html").removeClass("nightMode");

			localStorage.removeItem("nightMode");
		}
	}

	public static isActive(): boolean {
		const storedValue = localStorage.getItem("nightMode");

		return !!(storedValue && storedValue === "true");
	}

	public static toggle(): void {
		this.setActive(!this.isActive());
		window.location.reload();
	}

	public static spinnerColor(): string {
		return this.isActive() ? "light" : "primary";
	}
}