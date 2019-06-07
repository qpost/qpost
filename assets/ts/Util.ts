import $ from "jquery";

export default class Util {
	public static isValidURL(str: string): boolean {
		let regexp = /(ftp|http|https):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?/;
		return regexp.test(str);
	}

	public static updateTooltip(indicator, newTooltip: string): void {
		// TODO: Check for fixTitle alternative
		//$(indicator).attr("title",newTooltip).tooltip("_fixTitle").tooltip("show");
	}

	public static limitString(string, length, addDots = true): string {
		if (addDots) {
			length = length - 3;
			if (length < 1) length = 1;
		}

		if (string.length > length) {
			return string.substr(0, length) + (addDots ? "..." : "");
		} else {
			return string;
		}
	}

	public static hasAttr(element: HTMLElement, attribute: string): boolean {
		const $element = $(element);

		return typeof $element.attr(attribute) !== typeof undefined;
	}

	public static setCookie(cname: string, cvalue: string, exdays: number): void {
		var d = new Date();
		d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
		var expires = "expires=" + d.toUTCString();
		document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
	}

	// https://stackoverflow.com/a/5968306
	public static getCookie(name: string): string | null {
		var dc = document.cookie;
		var prefix = name + "=";
		var begin = dc.indexOf("; " + prefix);
		let end = dc.length - 1;
		if (begin == -1) {
			begin = dc.indexOf(prefix);
			if (begin != 0) return null;
		} else {
			begin += 2;
			end = document.cookie.indexOf(";", begin);
			if (end == -1) {
				end = dc.length;
			}
		}
		// because unescape has been deprecated, replaced with decodeURI
		//return unescape(dc.substring(begin + prefix.length, end));
		return decodeURI(dc.substring(begin + prefix.length, end));
	}

	public static hasCookie(name: string): boolean {
		return this.getCookie(name) != null;
	}

	public static formatNumber(number, decimals: number, dec_point: string, thousands_sep: string): string {
		// Strip all characters but numerical ones.
		number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
		let n = !isFinite(+number) ? 0 : +number,
			prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
			sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
			dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
			toFixedFix = function (n, prec) {
				var k = Math.pow(10, prec);
				return '' + Math.round(n * k) / k;
			};
		// Fix for IE parseFloat(0.55).toFixed(0) = 0;
		let s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
		if (s[0].length > 3) {
			s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
		}
		if ((s[1] || '').length < prec) {
			s[1] = s[1] || '';
			s[1] += new Array(prec - s[1].length + 1).join('0');
		}
		return s.join(dec);
	}

	public static hasNotificationPermissions(): boolean {
		return Notification.permission === "granted";
	}

	public static csrfToken() {
		return window["CSRF_TOKEN"];
	}

	public static postCharacterLimit() {
		return window["POST_CHARACTER_LIMIT"];
	}

	public static nodeToHTML(node) {
		return node.outerHTML || new XMLSerializer().serializeToString(node);
	}
}