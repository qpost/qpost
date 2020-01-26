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

import React, {Component} from "react";
import $ from "jquery";
import moment from "moment";
import {DatePicker} from "antd";
import "antd/es/date-picker/style";

export default class BirthdaySelector extends Component<any, {
	birthday: string | undefined
}> {
	private static readonly inputSelector = ".form-group > input[name=\"birthday\"]";

	constructor(props) {
		super(props);

		let birthday: string | undefined = undefined;
		let loadedBirthday = $(BirthdaySelector.inputSelector).val();
		if (typeof loadedBirthday === "string" && loadedBirthday !== "") {
			birthday = loadedBirthday;
		}

		this.state = {
			birthday: birthday
		};
	}

	render(): React.ReactElement<any, string | React.JSXElementConstructor<any>> | string | number | {} | React.ReactNodeArray | React.ReactPortal | boolean | null | undefined {
		const birthday = this.state.birthday;
		const birthdayMoment = birthday ? moment(birthday) : undefined;

		return <DatePicker value={birthdayMoment} onChange={(date) => {
			if (date) {
				const value = date.format("YYYY-MM-DD");

				this.setState({
					birthday: value
				});

				$(BirthdaySelector.inputSelector).val(value);
			} else {
				this.setState({birthday: undefined});
				$(BirthdaySelector.inputSelector).val("");
			}
		}} disabledDate={current => {
			return current >= moment().subtract(13, "years") || current <= moment().subtract(120, "years");
		}}/>;
	}
}