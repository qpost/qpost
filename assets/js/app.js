function load(){
	$('[data-toggle="tooltip"]').tooltip({
        trigger: 'hover'
    });

    $("time.timeago").timeago();

	window.addEventListener("load", function () {
		window.cookieconsent.initialise({
			"palette": {
				"popup": {
				  "background": "#237afc"
				},
				"button": {
				  "background": "#fff",
				  "text": "#237afc"
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
		let token = CSRF_TOKEN;
		console.log(token);

		if(!~e.innerHTML.indexOf("fas fa-spinner")){
			console.log("Follow button clicked");

			e.innerHTML = '<i class="fas fa-spinner fa-pulse"></i>';

			$.ajax({
				url: "/scripts/toggleFollow",
				data: {
					csrf_token: token,
					user: userID
				},
				method: "POST",

				success: function(result){
					var json = result;

					if(json.hasOwnProperty("followStatus")){
						if(json.followStatus == 1){
							console.log("following " + json.followStatus);
							e.classList.add("unfollowButton");
							e.classList.add("btn-danger");

							e.classList.remove("followButton");
							e.classList.remove("btn-primary");

							e.innerHTML = "Unfollow";
						} else if(json.followStatus == 0) {
							console.log("not following " + json.followStatus);
							e.classList.remove("unfollowButton");
							e.classList.remove("btn-danger");

							e.classList.add("followButton");
							e.classList.add("btn-primary");

							e.innerHTML = "Follow";
						}
					} else {
						console.log(result);
					}
				},

				error: function(xhr,status,error){
					console.log(xhr);
					console.log(status);
					console.log(error);
				}
			});
		}
	}
}

function setCookie(cname, cvalue, exdays) {
    var d = new Date();
    d.setTime(d.getTime() + (exdays*24*60*60*1000));
    var expires = "expires="+ d.toUTCString();
    document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
}

function saveDismiss(id){
	setCookie("registeredAlert"+id,"closed",10);
}

function number_format (number, decimals, dec_point, thousands_sep) {
    // Strip all characters but numerical ones.
    number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
    var n = !isFinite(+number) ? 0 : +number,
        prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
        sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
        dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
        s = '',
        toFixedFix = function (n, prec) {
            var k = Math.pow(10, prec);
            return '' + Math.round(n * k) / k;
        };
    // Fix for IE parseFloat(0.55).toFixed(0) = 0;
    s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
    if (s[0].length > 3) {
        s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
    }
    if ((s[1] || '').length < prec) {
        s[1] = s[1] || '';
        s[1] += new Array(prec - s[1].length + 1).join('0');
    }
    return s.join(dec);
}