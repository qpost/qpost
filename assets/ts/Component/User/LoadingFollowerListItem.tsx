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

import React, {Component} from "react";
import {Card, Col} from "antd";
import Skeleton from "antd/es/skeleton";
import {FollowerListItemColProps} from "./FollowerListItem";

export default class LoadingFollowerListItem extends Component {
	render(): React.ReactElement<any, string | React.JSXElementConstructor<any>> | string | number | {} | React.ReactNodeArray | React.ReactPortal | boolean | null | undefined {
		return <Col {...FollowerListItemColProps}>
			<Card size={"small"}>
				<Skeleton loading active avatar={{
					size: "large",
					shape: "square"
				}}
						  paragraph={{
							  rows: 4
						  }}/>
			</Card>
		</Col>;
	}
}