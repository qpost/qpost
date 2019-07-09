import Cookies from "js-cookie";

export default class Auth {
	public static isLoggedIn(): boolean {
		return !!this.getToken();
	}

	public static getToken(): string | undefined {
		return Cookies.get("sesstoken");
	}

	public static setToken(token?: string) {
		if (token) {
			Cookies.set("sesstoken", token, {
				expires: 30
			});
		} else {
			Cookies.remove("sesstoken");
		}
	}
}