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

import {JsonObject, JsonProperty} from "json2typescript";

@JsonObject("IpStackResult")
export default class IpStackResult {
	@JsonProperty("id", Number)
	private id: number = undefined;

	@JsonProperty("ip", String, true)
	private ip: string | null = undefined;

	@JsonProperty("type", String, true)
	private type: string | null = undefined;

	@JsonProperty("continentCode", String, true)
	private continentCode: string | null = undefined;

	@JsonProperty("continentName", String, true)
	private continentName: string | null = undefined;

	@JsonProperty("countryCode", String, true)
	private countryCode: string | null = undefined;

	@JsonProperty("countryName", String, true)
	private countryName: string | null = undefined;

	@JsonProperty("regionCode", String, true)
	private regionCode: string | null = undefined;

	@JsonProperty("regionName", String, true)
	private regionName: string | null = undefined;

	@JsonProperty("city", String, true)
	private city: string | null = undefined;

	@JsonProperty("zipCode", Number, true)
	private zipCode: number | null = undefined;

	@JsonProperty("latitude", Number, true)
	private latitude: number | null = undefined;

	@JsonProperty("longitude", Number, true)
	private longitude: number | null = undefined;

	@JsonProperty("time", String)
	private time: string = undefined;

	public getId(): number {
		return this.id;
	}

	public getIP(): string | null {
		return this.ip;
	}

	public getType(): string | null {
		return this.type;
	}

	public getContinentCode(): string | null {
		return this.continentCode;
	}

	public getContinentName(): string | null {
		return this.continentName;
	}

	public getCountryCode(): string | null {
		return this.countryCode;
	}

	public getCountryName(): string | null {
		return this.countryName;
	}

	public getRegionCode(): string | null {
		return this.regionCode;
	}

	public getRegionName(): string | null {
		return this.regionName;
	}

	public getCity(): string | null {
		return this.city;
	}

	public getZipCode(): number | null {
		return this.zipCode;
	}

	public getLatitude(): number | null {
		return this.latitude;
	}

	public getLongitude(): number | number {
		return this.longitude;
	}

	public getTime(): string {
		return this.time;
	}
}