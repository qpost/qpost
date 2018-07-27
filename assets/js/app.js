function loadCookieConsent(){
	window.addEventListener("load", function () {
		window.cookieconsent.initialise({
			"palette": {
				"popup": {
					"background": "#3c404d",
					"text": "#d6d6d6"
				},
				"button": {
					"background": "#8bed4f"
				}
			},
			"content": {
				"href": "https://gigadrivegroup.com/legal/privacy-policy"
			}
		})
	});
}