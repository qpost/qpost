import $ from "jquery";

export default class AddPhoto {
	public static init(): void {
		$(document).on("click", ".addPhoto", (e) => {
			e.preventDefault();


		});
	}
}