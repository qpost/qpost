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

function toggleFollow(e,userID){
	if(typeof CRSF_TOKEN !== undefined){
		if(!~e.innerHTML.indexOf("fas fa-spinner")){
			console.log("Follow button clicked");

			e.innerHTML = '<i class="fas fa-spinner fa-pulse"></i>';

			$.ajax({
				url: "/scripts/toggleFollow",
				data: {
					csrf_token: CRSF_TOKEN,
					user: userID
				},
				success: function(result){
					var json = result;

					if(json.hasOwnProperty("following")){
						if(json.following == "true"){
							e.classList.add("unfollowButton");
							e.classList.add("btn-danger");

							e.classList.remove("followButton");
							e.classList.remove("btn-primary");

							e.innerHTML = "Unfollow";
						} else {
							e.classList.remove("unfollowButton");
							e.classList.remove("btn-danger");

							e.classList.add("followButton");
							e.classList.add("btn-primary");

							e.innerHTML = "Follow";
						}
					} else {
						console.log(result);
					}
				}
			});
		}
	}
}