import $ from "jquery";
import Util from "../../Util";

export default class CharacterCount {
	public static init(): void {
		$(document).on("change keyup keydown paste", ".postField", function (e) {
			if ((e.ctrlKey || e.metaKey) && (e.keyCode == 13 || e.keyCode == 10)) {
				// click post button
				$(this).parent().parent().find(".postButton").click();
			} else {
				const $this = $(this);
				const limit = Util.postCharacterLimit();
				const value = $this.val();

				if (value) {
					const used = value["length"];
					const left: number = limit - used;
					const counter = $(this).parent().parent().find(".postCharacterCounter");

					if (left > 0) {
						if (left > limit / 2) {
							if (left == 1) {
								counter.html(left.toString());
							} else {
								counter.html(left.toString());
							}
						} else {
							if (left == 1) {
								counter.html("<span style=\"color: #F94F12;\">" + left + "</span>");
							} else {
								counter.html("<span style=\"color: #F94F12;\">" + left + "</span>");
							}
						}
					} else if (left == 0) {
						counter.html("<span style=\"color: #FF0000; font-weight: bold\">0</span>");
					} else {
						if (left == 1) {
							counter.html("<span style=\"color: #FF0000; font-weight: bold\">" + left + "</span>");
						} else {
							counter.html("<span style=\"color: #FF0000; font-weight: bold\">" + left + "</span>");
						}
					}
				}
			}
		});
	}
}