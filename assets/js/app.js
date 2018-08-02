function loadHomeFeed(){
	if($("#homePostField").length && $("#homeCharacterCounter").length){
		$.ajax({
			url: "/scripts/extendHomeFeed",
			data: {
				csrf_token: CSRF_TOKEN,
				mode: "loadNew",
				lastPost: HOME_FEED_LAST_POST
			},
			method: "POST",

			success: function(result){
				let json = result;

				if(json.hasOwnProperty("result")){
					let newHtml = "";

					let a = true;

					json.result.forEach(post => {
						let postId = post.id;

						if(a == true){
							HOME_FEED_LAST_POST = postId;
							a = false;
						}

						let postTime = post.time;
						let postText = post.text;
							
						let userName = post.userName;
						let userDisplayName = post.userDisplayName;
						let userAvatar = post.userAvatar;

						let postActionButtons = post.postActionButtons;

						if(post.hasOwnProperty("shared")){
							let shared = post.shared;

							let sharedId = shared.id;
							let sharedText = shared.text;
							let sharedTime = shared.time;
							let sharedUserId = shared.userId;
							let sharedUserName = shared.userName;
							let sharedUserDisplayName = shared.userDisplayName;
							let sharedAvatar = shared.userAvatar;

							newHtml = newHtml.concat(
								'<div class="card feedEntry mb-2" data-entry-id="' + postId + '">' +
									'<div class="card-body">' +
										'<div class="small text-muted">' +
											'<i class="fas fa-share-alt text-primary"></i> Shared by <a href="/' + userName + '" class="clearUnderline">' + userDisplayName + '</a> &bull; ' + postTime +
										'</div>' +
										'<div class="row">' +
											'<div class="col-1">' +
												'<a href="/' + sharedUserName + '" class="clearUnderline">' +
													'<img class="rounded mx-1 my-1" src="' + sharedAvatar + '" width="40" height="40"/>' +
												'</a>' +
											'</div>' +

											'<div class="col-11">' +
												'<p class="mb-0">' +
													'<a href="/' + sharedUserName + '" class="clearUnderline">' +
														'<span class="font-weight-bold">' + sharedUserDisplayName + '</span>' +
													'</a>' +

													' <span class="text-muted font-weight-normal">@' + sharedUserName + '</span> ' +

													'&bull; ' +

													sharedTime +
												'</p>' +

												'<p class="mb-0 convertEmoji">' +
													twemoji.parse(sharedText) +
												'</p>' +

												postActionButtons +
											'</div>' +
										'</div>' +
									'</div>' +
								'</div>'
							);
						} else {
							newHtml = newHtml.concat(
								'<div class="card feedEntry mb-2" data-entry-id="' + postId + '">' +
									'<div class="card-body">' +
										'<div class="row">' +
											'<div class="col-1">' +
												'<a href="/' + userName + '" class="clearUnderline">' +
													'<img class="rounded mx-1 my-1" src="' + userAvatar + '" width="40" height="40"/>' +
												'</a>' +
											'</div>' +
							
											'<div class="col-11">' +
												'<p class="mb-0">' +
													'<a href="/' + userName + '" class="clearUnderline">' +
														'<span class="font-weight-bold">' + userDisplayName + '</span>' +
													'</a>' +
							
													' <span class="text-muted font-weight-normal">@' + userName + '</span>' +
							
													' &bull; ' +
							
													postTime +
												'</p>' +
							
												'<p class="mb-0 convertEmoji">' +
													twemoji.parse(postText) +
												'</p>' +

												postActionButtons +
											'</div>' +
										'</div>' +
									'</div>' +
								'</div>');
						}
					});

					if($(".feedEntry").length){
						$(".feedContainer").prepend(newHtml);
					} else {
						$(".feedContainer").html(newHtml);
					}

					loadBasic();

					setTimeout(loadHomeFeed,5000);
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
	} else {
		setTimeout(loadHomeFeed,5000);
	}
}
loadHomeFeed();

function loadOldHomeFeed(){
	if($("#homePostField").length && $("#homeCharacterCounter").length){
		let oldHtml = $(".homeFeedLoadMore").html();

		$(".homeFeedLoadMore").html('<i class="fas fa-spinner fa-pulse"></i>');

		$.ajax({
			url: "/scripts/extendHomeFeed",
			data: {
				csrf_token: CSRF_TOKEN,
				mode: "loadOld",
				firstPost: HOME_FEED_FIRST_POST
			},
			method: "POST",

			success: function(result){
				let json = result;

				if(json.hasOwnProperty("result")){
					let newHtml = "";

					if(json.result.length > 0){
						let i;
						for(i = 0; i < json.result.length; i++){
							let post = json.result[i];

							let postId = post.id;

							if(i == json.result.length-1){
								HOME_FEED_FIRST_POST = postId;
							}

							let postTime = post.time;
							let postText = post.text;
								
							let userName = post.userName;
							let userDisplayName = post.userDisplayName;
							let userAvatar = post.userAvatar;

							let postActionButtons = post.postActionButtons;

							if(post.hasOwnProperty("shared")){
								let shared = post.shared;

								let sharedId = shared.id;
								let sharedText = shared.text;
								let sharedTime = shared.time;
								let sharedUserId = shared.userId;
								let sharedUserName = shared.userName;
								let sharedUserDisplayName = shared.userDisplayName;
								let sharedAvatar = shared.userAvatar;

								newHtml = newHtml.concat(
									'<div class="card feedEntry mb-2" data-entry-id="' + postId + '">' +
										'<div class="card-body">' +
											'<div class="small text-muted">' +
												'<i class="fas fa-share-alt text-primary"></i> Shared by <a href="/' + userName + '" class="clearUnderline">' + userDisplayName + '</a> &bull; ' + postTime +
											'</div>' +
											'<div class="row">' +
												'<div class="col-1">' +
													'<a href="/' + sharedUserName + '" class="clearUnderline">' +
														'<img class="rounded mx-1 my-1" src="' + sharedAvatar + '" width="40" height="40"/>' +
													'</a>' +
												'</div>' +

												'<div class="col-11">' +
													'<p class="mb-0">' +
														'<a href="/' + sharedUserName + '" class="clearUnderline">' +
															'<span class="font-weight-bold">' + sharedUserDisplayName + '</span>' +
														'</a>' +

														' <span class="text-muted font-weight-normal">@' + sharedUserName + '</span> ' +

														'&bull; ' +

														sharedTime +
													'</p>' +

													'<p class="mb-0 convertEmoji">' +
														twemoji.parse(sharedText) +
													'</p>' +

													postActionButtons +
												'</div>' +
											'</div>' +
										'</div>' +
									'</div>'
								);
							} else {
								newHtml = newHtml.concat(
									'<div class="card feedEntry mb-2" data-entry-id="' + postId + '">' +
										'<div class="card-body">' +
											'<div class="row">' +
												'<div class="col-1">' +
													'<a href="/' + userName + '" class="clearUnderline">' +
														'<img class="rounded mx-1 my-1" src="' + userAvatar + '" width="40" height="40"/>' +
													'</a>' +
												'</div>' +
								
												'<div class="col-11">' +
													'<p class="mb-0">' +
														'<a href="/' + userName + '" class="clearUnderline">' +
															'<span class="font-weight-bold">' + userDisplayName + '</span>' +
														'</a>' +
								
														' <span class="text-muted font-weight-normal">@' + userName + '</span>' +
								
														' &bull; ' +
								
														postTime +
													'</p>' +
								
													'<p class="mb-0 convertEmoji">' +
														twemoji.parse(postText) +
													'</p>' +

													postActionButtons +
												'</div>' +
											'</div>' +
										'</div>' +
									'</div>');
							}
						}

						if($(".feedEntry").length){
							$(".feedContainer").append(newHtml);
						} else {
							$(".feedContainer").html(newHtml);
						}

						$(".homeFeedLoadMore").html(oldHtml);

						loadBasic();
					} else {
						$(".homeFeedLoadMore").html('<b>Oops!</b><br/>It seems there is nothing else to load for your home feed.');
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
	} else {
		setTimeout(loadHomeFeed,5000);
	}
}

function loadBasic(){
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

	$(".favoriteButton").on("click",function(e){
		e.preventDefault();

		let postId = $(this).attr("data-post-id");

		let favoritedHtml = '<i class="fas fa-star" style="color: gold"></i>';
		let unfavoritedHtml = '<i class="fas fa-star" style="color: gray"></i>';
		let loadingHtml = '<i class="fas fa-spinner fa-pulse"></i>';

		let pointer = $(this);

		if(typeof postId !== "undefined"){
			pointer.html(loadingHtml);

			$.ajax({
				url: "/scripts/toggleFavorite",
				data: {
					csrf_token: CSRF_TOKEN,
					post: postId
				},
				method: "POST",
	
				success: function(result){
					let json = result;
	
					if(json.hasOwnProperty("status")){
						if(json.status == "Favorite added"){
							pointer.html(favoritedHtml);
						} else {
							pointer.html(unfavoritedHtml);
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
	});

	$(".shareButton").on("click",function(e){
		e.preventDefault();

		let postId = $(this).attr("data-post-id");

		let sharedHtml = '<i class="fas fa-share-alt text-primary"></i>';
		let unsharedHtml = '<i class="fas fa-share-alt" style="color: gray"></i>';
		let loadingHtml = '<i class="fas fa-spinner fa-pulse"></i>';

		let pointer = $(this);

		if(typeof postId !== "undefined"){
			pointer.html(loadingHtml);

			$.ajax({
				url: "/scripts/toggleShare",
				data: {
					csrf_token: CSRF_TOKEN,
					post: postId
				},
				method: "POST",
	
				success: function(result){
					let json = result;
	
					if(json.hasOwnProperty("status")){
						if(json.status == "Share added"){
							pointer.html(sharedHtml);
						} else {
							pointer.html(unsharedHtml);
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
	});
}

$(document).ready(function(){
	load();
});

function loadPostButtons(){
	$("#homePostButton").on("click",function(e){
		e.preventDefault();
	
		let text = $("#homePostField").val();
	
		if(typeof CSRF_TOKEN !== undefined && typeof POST_CHARACTER_LIMIT !== undefined){
			let token = CSRF_TOKEN;
			let limit = POST_CHARACTER_LIMIT;
	
			let oldHtml = $("#homePostBox").html();
	
			if(text.length > 0 && text.length <= limit){
				console.log("Sending post!");
	
				$("#homePostBox").html('<div class="card-body text-center"><i class="fas fa-spinner fa-pulse"></i></div>');
	
				$.ajax({
					url: "/scripts/createPost",
					data: {
						csrf_token: token,
						text: text
					},
					method: "POST",
	
					success: function(result){
						let json = result;
	
						if(json.hasOwnProperty("post")){
							let post = json.post;
	
							if(post.hasOwnProperty("id") && post.hasOwnProperty("time") && post.hasOwnProperty("text") && post.hasOwnProperty("userName") && post.hasOwnProperty("userDisplayName") && post.hasOwnProperty("userAvatar")){
								let postId = post.id;
								let postTime = post.time;
								let postText = post.text;
								
								let userName = post.userName;
								let userDisplayName = post.userDisplayName;
								let userAvatar = post.userAvatar;
	
								HOME_FEED_LAST_POST = postId;

								let postActionButtons = '<div class="mt-1 postActionButtons">' +
								'<span data-toggle="tooltip" title="You can not share this post" data-post-id="' + postId + '">' +
								'<i class="fas fa-share-alt" style="color: gray"></i>' +
								'</span>' +

								'<span class="shareCount small text-primary ml-1 mr-1">0</span>' +

								'<span class="favoriteButton" data-post-id="<?= $sharedPost->getId() ?>" title="Add to favorites" data-toggle="tooltip">' +
								'<i class="fas fa-star" style="color: gray"></i>' +
								'</span>' +

								'<span class="favoriteCount small ml-1 mr-1" style="color: #ff960c">0</span></div>';
	
								let newHtml =
								'<div class="card feedEntry mb-2" data-entry-id="' + postId + '">' +
									'<div class="card-body">' +
										'<div class="row">' +
											'<div class="col-1">' +
												'<a href="/' + userName + '" class="clearUnderline">' +
													'<img class="rounded mx-1 my-1" src="' + userAvatar + '" width="40" height="40"/>' +
												'</a>' +
											'</div>' +
					
											'<div class="col-11">' +
												'<p class="mb-0">' +
													'<a href="/' + userName + '" class="clearUnderline">' +
														'<span class="font-weight-bold">' + userDisplayName + '</span>' +
													'</a>' +
					
													' <span class="text-muted font-weight-normal">@' + userName + '</span>' +
					
													' &bull; ' +
					
													postTime +
												'</p>' +
					
												'<p class="mb-0 convertEmoji">' +
													twemoji.parse(postText) +
												'</p>' +

												postActionButtons +
											'</div>' +
										'</div>' +
									'</div>' +
								'</div>';
	
								if($(".feedEntry").length){
									$(".feedContainer").prepend(newHtml);
								} else {
									$(".feedContainer").html(newHtml);
								}
	
								$("#homePostBox").html(oldHtml);
								$("#homePostField").val("");
								$("#homeCharacterCounter").html(POST_CHARACTER_LIMIT + " characters left");
								loadBasic();
								loadPostButtons();
							} else {
								console.log(result);
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
				})
			} else {
				console.error("Post text too long or too short!");
			}
		}
	});
	
	$("#profilePostButton").on("click",function(e){
		e.preventDefault();
	
		let text = $("#profilePostField").val();
	
		if(typeof CSRF_TOKEN !== undefined && typeof POST_CHARACTER_LIMIT !== undefined){
			let token = CSRF_TOKEN;
			let limit = POST_CHARACTER_LIMIT;
	
			let oldHtml = $("#profilePostBox").html();
	
			if(text.length > 0 && text.length <= limit){
				console.log("Sending post!");
	
				$("#profilePostBox").html('<div class="card-body text-center"><i class="fas fa-spinner fa-pulse"></i></div>');
	
				$.ajax({
					url: "/scripts/createPost",
					data: {
						csrf_token: token,
						text: text
					},
					method: "POST",
	
					success: function(result){
						let json = result;
	
						if(json.hasOwnProperty("post")){
							let post = json.post;
	
							if(post.hasOwnProperty("id") && post.hasOwnProperty("time") && post.hasOwnProperty("text") && post.hasOwnProperty("userName") && post.hasOwnProperty("userDisplayName") && post.hasOwnProperty("userAvatar")){
								let postId = post.id;
								let postTime = post.time;
								let postText = post.text;
								
								let userName = post.userName;
								let userDisplayName = post.userDisplayName;
								let userAvatar = post.userAvatar;
	
								let newHtml =
								'<div class="card feedEntry mb-2" data-entry-id="' + postId + '">' +
									'<div class="card-body">' +
										'<div class="row">' +
											'<div class="col-1">' +
												'<img class="rounded mx-1 my-1" src="' + userAvatar + '" width="40" height="40"/>' +
											'</div>' +
					
											'<div class="col-11">' +
												'<p class="mb-0">' +
													'<span class="font-weight-bold">' + userDisplayName + '</span>' +
					
													' <span class="text-muted font-weight-normal">@' + userName + '</span>' +
					
													' &bull; ' +
					
													postTime +
												'</p>' +
					
												'<p class="mb-0">' +
													postText +
												'</p>' +
											'</div>' +
										'</div>' +
									'</div>' +
								'</div>';
	
								if($(".feedEntry").length){
									$(".feedContainer").prepend(newHtml);
								} else {
									$(".feedContainer").html(newHtml);
								}
	
								$("#profilePostBox").html(oldHtml);
								$("#profilePostField").val("");
								$("#profileCharacterCounter").html(POST_CHARACTER_LIMIT + " characters left");
								loadBasic();
								loadPostButtons();
							} else {
								console.log(result);
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
				})
			} else {
				console.error("Post text too long or too short!");
			}
		}
	});
}

function load(){
	loadPostButtons();

	if($("#profilePostField").length && $("#profileCharacterCounter").length){
		if(typeof POST_CHARACTER_LIMIT !== undefined){
			$("#profilePostField").on("change keyup keydown paste",function(){
				let limit = POST_CHARACTER_LIMIT;
				let used = $("#profilePostField").val().length;
				let left = limit-used;

				if(left > 0){
					if(left > limit/2){
						if(left == 1){
							$("#profileCharacterCounter").html(left + " character left");
						} else {
							$("#profileCharacterCounter").html(left + " characters left");
						}
					} else {
						if(left == 1){
							$("#profileCharacterCounter").html("<span style=\"color: #F94F12;\">" + left + " character left</span>");
						} else {
							$("#profileCharacterCounter").html("<span style=\"color: #F94F12;\">" + left + " characters left</span>");
						}
					}
				} else if(left == 0){
					$("#profileCharacterCounter").html("<span style=\"color: #FF0000; font-weight: bold\">You have reached the character limit</span>");
				} else {
					left = left/(-1);

					if(left == 1){
						$("#profileCharacterCounter").html("<span style=\"color: #FF0000; font-weight: bold\">You are " + left + " character over the limit</span>");
					} else {
						$("#profileCharacterCounter").html("<span style=\"color: #FF0000; font-weight: bold\">You are " + left + " characters over the limit</span>");
					}
				}
			});
		}
	}

	if($("#homePostField").length && $("#homeCharacterCounter").length){
		if(typeof POST_CHARACTER_LIMIT !== undefined){
			$("#homePostField").on("change keyup keydown paste",function(){
				let limit = POST_CHARACTER_LIMIT;
				let used = $("#homePostField").val().length;
				let left = limit-used;

				if(left > 0){
					if(left > limit/2){
						if(left == 1){
							$("#homeCharacterCounter").html(left + " character left");
						} else {
							$("#homeCharacterCounter").html(left + " characters left");
						}
					} else {
						if(left == 1){
							$("#homeCharacterCounter").html("<span style=\"color: #F94F12;\">" + left + " character left</span>");
						} else {
							$("#homeCharacterCounter").html("<span style=\"color: #F94F12;\">" + left + " characters left</span>");
						}
					}
				} else if(left == 0){
					$("#homeCharacterCounter").html("<span style=\"color: #FF0000; font-weight: bold\">You have reached the character limit</span>");
				} else {
					left = left/(-1);

					if(left == 1){
						$("#homeCharacterCounter").html("<span style=\"color: #FF0000; font-weight: bold\">You are " + left + " character over the limit</span>");
					} else {
						$("#homeCharacterCounter").html("<span style=\"color: #FF0000; font-weight: bold\">You are " + left + " characters over the limit</span>");
					}
				}
			});
		}
	}
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
					let json = result;

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