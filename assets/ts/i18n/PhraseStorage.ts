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

export default class PhraseStorage {
	public static phrases: {} = {};

	public static loadPhrases(url?): Promise<{}> {
		url = url || "/translation.json";

		return new Promise<{}>((resolve, reject) => {
			$.ajax(url, {
				success: data => {
					this.phrases = data;
					resolve(this.phrases);
				},
				error: jqXHR => {
					reject("Failed to load translations.");
				}
			})
		})
	}
}