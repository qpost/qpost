import $ from "jquery";
import Util from "../../Util";

export default class Highlight {
	public static init(): void {
		const postField = $(".postField");

		if (postField.length > 0) {
			postField["highlightWithinTextarea"]({
				highlight: (input) => {
					if (input.length > Util.postCharacterLimit()) {
						return [Util.postCharacterLimit(), input.length];
					} else {
						return false;
					}
				}
			});
		}
	}
}