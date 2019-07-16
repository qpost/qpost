import React, {Component} from "react";

export default class SidebarFooter extends Component<any, any> {
	render(): React.ReactElement<any, string | React.JSXElementConstructor<any>> | string | number | {} | React.ReactNodeArray | React.ReactPortal | boolean | null | undefined {
		return <div className={"sidebarFooter"}>
			<ul>
				<li>
					&copy; Copyright 2018-{new Date().getFullYear()} Gigadrive
				</li>

				<li>
					<a href={"https://gigadrivegroup.com/legal/contact"} target={"_blank"}>
						Contact
					</a>
				</li>

				<li>
					<a href={"https://gigadrivegroup.com/legal/terms-of-service"} target={"_blank"}>
						Terms
					</a>
				</li>

				<li>
					<a href={"https://gigadrivegroup.com/legal/privacy-policy"} target={"_blank"}>
						Privacy
					</a>
				</li>

				<li>
					<a href={"https://gigadrivegroup.com/legal/disclaimer"} target={"_blank"}>
						Disclaimer
					</a>
				</li>
			</ul>
		</div>;
	}
}