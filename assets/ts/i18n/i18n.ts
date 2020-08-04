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

import PhraseStorage from "./PhraseStorage";

export default function __(identifier: string, parameters?: {}): string {
	if (!PhraseStorage.phrases.hasOwnProperty(identifier)) return identifier;

	let value: string = PhraseStorage.phrases[identifier];

	if (parameters) {
		Object.keys(parameters).forEach(paramId => {
			const paramValue = parameters[paramId];

			value = value.replace(paramId, paramValue);
		});
	}

	return value;
}