import $ from "jquery";

export default class NightMode {
	public static init(): void {
		if (window["FORCE_DISABLE_NIGHTMODE"]) {
			return;
		}

		// load night mode
		NightMode.setActive(NightMode.isActive());
	}

	public static setActive(active: boolean): void {
		if (active) {
			$("#mainNav").addClass("bg-dark").removeClass("bg-primary");
			$("html").addClass("nightMode");

			localStorage.setItem("nightMode", "true");
		} else {
			$("#mainNav").addClass("bg-primary").removeClass("bg-dark");
			$("html").removeClass("nightMode");

			localStorage.removeItem("nightMode");
		}
	}

	public static isActive(): boolean {
		const storedValue = localStorage.getItem("nightMode");

		return !!(storedValue && storedValue === "true");
	}

	public static toggle(): void {
		this.setActive(!this.isActive());
	}

	public static spinnerColor(): string {
		return this.isActive() ? "light" : "primary";
	}
}