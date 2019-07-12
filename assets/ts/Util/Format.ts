export function formatNumberShort(number: number): string {
	if (number <= 999) {
		return number.toString();
	} else if (number >= 1000 && number <= 999999) {
		return (number / 1000).toFixed(1) + "K";
	} else {
		return (number / 1000000).toFixed(1) + "M";
	}
}