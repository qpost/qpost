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

import {UAParser} from "ua-parser-js";

export function formatNumberShort(number: number): string {
	if (number <= 999) {
		return number.toString();
	} else if (number >= 1000 && number <= 999999) {
		return (number / 1000).toFixed(1) + "K";
	} else {
		return (number / 1000000).toFixed(1) + "M";
	}
}

export function placeZeroBelowTen(number: number): string {
	return (number < 10 ? "0" : "") + number;
}

export function limitString(string: string, length: number, addDots?: boolean) {
	if (typeof addDots === "undefined") addDots = false;

	if (string.length > length)
		string = string.substr(0, (addDots ? length - 3 : length)) + (addDots ? "..." : "");

	return string;
}

export function cacheImage(url: string): string {
	return "https://images.weserv.nl/?url=" + encodeURI(url);
}

export function stillGIFURL(url: string): string {
	url = url.toLowerCase();

	return url.endsWith(".gif") ? cacheImage(url) : url;
}

export function convertUserAgentToIconClass(userAgent: UAParser): string {
	const browserName: string | undefined = userAgent.getBrowser().name;
	if (browserName !== undefined) {
		if (browserName.includes("Edge")) {
			return "fab fa-edge"
		} else if (browserName.includes("Opera")) {
			return "fab fa-opera"
		} else if (browserName.includes("Chrome")) {
			return "fab fa-chrome";
		} else if (browserName.includes("Internet Explorer")) {
			return "fab fa-internet-explorer";
		} else if (browserName.includes("Safari")) {
			return "fab fa-safari";
		} else if (browserName.includes("Firefox")) {
			return "fab fa-firefox";
		}
	}

	const vendorName: string | undefined = userAgent.getDevice().vendor;
	if (vendorName !== undefined) {
		if (vendorName.includes("Amazon")) {
			return "fab fa-amazon";
		} else if (vendorName.includes("Apple")) {
			return "fab fa-apple";
		}
	}

	const osName: string | undefined = userAgent.getOS().name;
	if (osName !== undefined) {
		if (osName.includes("Windows")) {
			return "fab fa-windows";
		} else if (osName.includes("Nintendo")) {
			return "fab fa-nintendo-switch";
		} else if (osName.includes("Playstation")) {
			return "fab fa-playstation";
		}
	}

	return "fas fa-globe";
}