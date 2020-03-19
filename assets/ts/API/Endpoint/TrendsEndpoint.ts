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

import APIEndpoint from "./APIEndpoint";
import TrendingHashtagData from "../../Entity/Feed/TrendingHashtagData";
import BaseObject from "../../Serialization/BaseObject";

export default class TrendsEndpoint extends APIEndpoint {
	private path: string = "/trends";

	/**
	 * Gets the currently trending topics.
	 * @param limit The limit of topics to return.
	 */
	public get(limit: number): Promise<TrendingHashtagData[]> {
		return new Promise<TrendingHashtagData[]>((resolve, reject) => {
			this.api.handleRequestWithPromise(this.path, "GET", {limit}).then(value => {
				return resolve(BaseObject.convertArray(TrendingHashtagData, value));
			}).catch(reason => {
				return reject(reason);
			})
		})
	}
}