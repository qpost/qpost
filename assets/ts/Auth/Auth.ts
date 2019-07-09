import Cookies from "js-cookie";
import User from "../Entity/Account/User";

export default class Auth {
	private static currentUser?: User;

	public static getCurrentUser(): User | undefined {
		return this.currentUser;
	}

	public static setCurrentUser(user?: User): void {
		this.currentUser = user;
	}

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

	public static logout(): void {
		this.setToken(undefined);
		this.setCurrentUser(undefined);

		window.location.href = "/";
	}
}