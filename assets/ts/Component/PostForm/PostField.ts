import $ from "jquery";
import FocusEvent = JQuery.FocusEvent;

export default class PostField {
	public static init(): void {
		$(document).on("focus","textarea.postField",(e: FocusEvent) => {
			const $this = $(e.currentTarget);

			if(!$this.hasClass("active")){
				$this.addClass("active");
			}
		});
	}
}