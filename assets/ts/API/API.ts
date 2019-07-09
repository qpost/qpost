import axios, {AxiosInstance, Method} from "axios";
import Auth from "../Auth/Auth";

export default class API {
	/**
	 * The axios instance to be used.
	 */
	private static http: AxiosInstance = axios.create({
		baseURL: window.location.hostname === "localhost" || window.location.hostname === "127.0.0.1" ? "http://localhost:8000/api" : "https://qpo.st/api",
		headers: Auth.isLoggedIn() ? {"Authorization": "Token " + Auth.getToken()} : {}
	});

	/**
	 * Creates a request to the qpost API server.
	 *
	 * @param url The url to be requested.
	 * @param method The HTTP method to be used.
	 * @param data The response data as an object.
	 * @param callback The callback to be executed on a successful request.
	 * @param errorCallback The callback to be executed if the request fails.
	 */
	public static handleRequest(url: string, method?: Method, data?: any, callback?: (data: any) => void, errorCallback?: (error: string) => void): void {
		if (url) {
			method = method || "GET";

			this.http.request({
				method,
				url,
				data: method !== "GET" ? data : {},
				params: method === "GET" ? data : {}
			}).then(response => {
				if (response.data || (Math.floor(response.status / 100) == 2)) {
					if (response.data.error) {
						if (errorCallback) {
							errorCallback(response.data.error);
						}
					} else {
						if (callback) {
							callback(response.data);
						}
					}
				} else {
					if (errorCallback) {
						errorCallback("An error occurred.");
					}
				}
			}).catch(error => {
				if (errorCallback) {
					errorCallback("An error occurred.");
				}
			})
		} else {
			throw new Error("No URL specified");
		}
	}
}