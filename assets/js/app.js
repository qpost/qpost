function updateTooltip(indicator,newTooltip){
	$(indicator).attr("title",newTooltip).tooltip("_fixTitle").tooltip("show");
}

function isValidURL(str) {
	let regexp = /(ftp|http|https):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?/;
	return regexp.test(str);
}

//// ads start

function adbanner_leaderboard(center,classes){
	let s = "";

	let classesString = "";
	classes.forEach(clazz => {
		classesString = classesString.concat(clazz + " ");
	});

	classesString = classesString.trim();

	if(center === true) s = s.concat("<center>");

	s = s.concat('<div class="' + classesString + '">');

	s = s.concat('<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script><ins class="adsbygoogle" style="display:block" data-ad-client="ca-pub-6156128043207415" data-ad-slot="1055807482" data-ad-format="auto"></ins><script>(adsbygoogle = window.adsbygoogle || []).push({});</script>');

	s = s.concat('</div>');

	if(center === true) s = s.concat("</center>");

	return s;
}

function adbanner_block(center,classes){
	let s = "";

	let classesString = "";
	classes.forEach(clazz => {
		classesString = classesString.concat(clazz + " ");
	});

	classesString = classesString.trim();

	if(center === true) s = s.concat("<center>");

	s = s.concat('<div class="' + classesString + '">');

	s = s.concat('<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script><ins class="adsbygoogle" style="display:inline-block;width:120px;height:600px" data-ad-client="ca-pub-6156128043207415" data-ad-slot="1788401303"></ins><script>(adsbygoogle = window.adsbygoogle || []).push({});</script>');

	s = s.concat('</div>');

	if(center === true) s = s.concat("</center>");

	return s;
}

function adbanner_horizontal(center,classes){
	return adbanner_leaderboard(center,classes);
}

function adbanner_vertical(center,classes){
	let s = "";

	let classesString = "";
	classes.forEach(clazz => {
		classesString = classesString.concat(clazz + " ");
	});

	classesString = classesString.trim();

	if(center === true) s = s.concat("<center>");

	s = s.concat('<div class="' + classesString + '">');

	s = s.concat('<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script><ins class="adsbygoogle" style="display:inline-block;width:120px;height:600px" data-ad-client="ca-pub-6156128043207415" data-ad-slot="1788401303"></ins><script>(adsbygoogle = window.adsbygoogle || []).push({});</script>');

	s = s.concat('</div>');

	if(center === true) s = s.concat("</center>");

	return s;
}

/// ads end

function resetDeleteModal(){
	$("#deleteModal").html(
		'<div class="modal-dialog" role="document">' +
		'<div class="modal-content">' +
		'<div class="modal-body">' +
		'<div class="text-center">' +
		'<i class="fas fa-spinner fa-pulse"></i>' +
		'</div>' +
		'</div>' +
		'</div>' +
		'</div>'
	);
}

function isDeleteModalOpen(){
	return $("#deleteModal").hasClass('show');
}

function closeDeleteModal(){
	$("#deleteModal").modal('hide');
}

function showDeleteModal(postId){
	resetDeleteModal();
	
	let deleteModal = $("#deleteModal");
	
	$.ajax({
		url: "/scripts/postInfo",
		data: {
			csrf_token: CSRF_TOKEN,
			postId: postId
		},
		method: "POST",
		
		success: function(json){
			if(json.hasOwnProperty("id")){
				let user = json.user;
				let content = "";
				
				content = content.concat('<div class="mb-4">');
				
				content = content.concat(json.followButton);
				
				content = content.concat(
					'<div class="float-left mr-2">' +
					'<a href="/' + user.username + '" class="clearUnderline">' +
					'<img width="48" height="48" src="' + user.avatar + '" class="rounded"/>' +
					'</a>' +
					'</div>'
				);
				
				content = content.concat('<div class="ml-2">');
				
				content = content.concat(
					'<div><a href="/' + user.username + '" class="clearUnderline font-weight-bold mb-0" style="font-size:20px">' +
					user.displayName +
					'</a></div>'
				);
				
				content = content.concat(
					'<div class="text-muted" style="margin-top: -6px">' +
					'@' + user.username +
					'</div>'
				);
				
				content = content.concat('</div>');
				
				content = content.concat('</div>');
				
				let c = json.hasOwnProperty("parent") && json.parent != null ? '<div class="small text-muted">Replying to <a href="/' + json.parent.user.username + '" class="clearUnderline">@' + json.parent.user.username + '</a></div>' : "";
				
				content = content.concat(
					'<div class="mt-2">' +
					c + 
					'<p style="font-size: 27px; word-wrap: break-word;">' +
					twemoji.parse(json.text) +
					'</p>' +
					'<p class="small text-muted"><i class="far fa-clock"></i> Posted ' +
					json.time +
					'</p>' +
					'</div>'
				);
				
				deleteModal.html(
					'<div class="modal-dialog" role="document">' +
					'<div class="modal-content">' +
					'<div class="modal-header">' +
					'<h5 class="modal-title">Are you sure you want to delete this post?</h5>' +
					'</div>' +
					
					'<div class="modal-body">' +
					content +
					'</div>' +
					
					'<div class="modal-footer">' +
					'<button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>' +
					'<button type="button" class="finDel btn btn-danger" data-post-id="' + postId + '">Delete</button>' +
					'</div>' +
					'</div>' +
					'</div>'
				);
				
				loadBasic();
				
				if(!isDeleteModalOpen())
				deleteModal.modal();
			} else if(json.hasOwnProperty("error")){
				deleteModal.html(
					'<div class="modal-dialog" role="document">' +
					'<div class="modal-content">' +
					'<div class="modal-body">' +
					json.error +
					'</div>' +
					'</div>' +
					'</div>'
				);
				
				if(!isDeleteModalOpen())
				deleteModal.modal();
			} else {
				deleteModal.html(
					'<div class="modal-dialog" role="document">' +
					'<div class="modal-content">' +
					'<div class="modal-body">' +
					json +
					'</div>' +
					'</div>' +
					'</div>'
				);
				
				if(!isDeleteModalOpen())
				deleteModal.modal();
			}
		},
		
		error: function(xhr,status,error){
			console.log(xhr);
			console.log(status);
			console.log(error);
		}
	});
}

function resetMediaModal(){
	$("#mediaModal").html(
		'<div class="modal-dialog" role="document">' +
		'<div class="modal-content">' +
		'<div class="modal-body">' +
		'<div class="text-center">' +
		'<i class="fas fa-spinner fa-pulse"></i>' +
		'</div>' +
		'</div>' +
		'</div>' +
		'</div>'
	);
}

function isMediaModalOpen(){
	return $("#mediaModal").hasClass('show');
}

function closeMediaModal(){
	$("#mediaModal").modal('hide');
}

function showMediaModal(mediaId,postId){
	resetMediaModal();
	
	let mediaModal = $("#mediaModal");
	
	$.ajax({
		url: "/scripts/mediaInfo",
		data: {
			csrf_token: CSRF_TOKEN,
			postId: postId,
			mediaId: mediaId
		},
		method: "POST",
		
		success: function(json){
			if(json.hasOwnProperty("post") && json.hasOwnProperty("attachment")){
				let post = json.post;
				let attachment = json.attachment;
				
				let content = "";
				
				content = content.concat(
					'<img src="' + attachment.fileUrl + '" style="max-width: 100%; max-height: 700px; width: auto; height: auto;"/>'
				);
				
				mediaModal.html(
					'<div class="modal-dialog modal-lg" role="document">' +
					'<div class="modal-content">' +
					'<div class="d-inline-block text-center bg-dark">' +
					
					content +
					
					'</div>' +
					'</div>' +
					'</div>'
				);
				
				loadBasic();
				
				if(!isMediaModalOpen())
				mediaModal.modal();
			} else if(json.hasOwnProperty("error")){
				mediaModal.html(
					'<div class="modal-dialog" role="document">' +
					'<div class="modal-content">' +
					'<div class="modal-body">' +
					json.error +
					'</div>' +
					'</div>' +
					'</div>'
				);
				
				if(!isMediaModalOpen())
				mediaModal.modal();
			} else {
				mediaModal.html(
					'<div class="modal-dialog" role="document">' +
					'<div class="modal-content">' +
					'<div class="modal-body">' +
					json +
					'</div>' +
					'</div>' +
					'</div>'
				);
				
				if(!isMediaModalOpen())
				mediaModal.modal();
			}
		},
		
		error: function(xhr,status,error){
			console.log(xhr);
			console.log(status);
			console.log(error);
		}
	});
}

function resetStatusModal(){
	$("#statusModal").html(
		'<div class="modal-dialog" role="document">' +
		'<div class="modal-content">' +
		'<div class="modal-body">' +
		'<div class="text-center">' +
		'<i class="fas fa-spinner fa-pulse"></i>' +
		'</div>' +
		'</div>' +
		'</div>' +
		'</div>'
	);
}

function isStatusModalOpen(){
	return $("#statusModal").hasClass('show');
}

function closeStatusModal(){
	$("#statusModal").modal('hide');
}

function showStatusModal(postId){
	resetStatusModal();
	
	let statusModal = $("#statusModal");
	
	if(restoreUrl == null || restoreUrl == "") restoreUrl = window.location.pathname;
	if(restoreTitle == null || restoreTitle == "") restoreTitle = $(document).find("title").text();
	
	$.ajax({
		url: "/scripts/postInfo",
		data: {
			csrf_token: CSRF_TOKEN,
			postId: postId
		},
		method: "POST",
		
		success: function(json){
			if(json.hasOwnProperty("id")){
				CURRENT_STATUS_MODAL = json.id;
				let user = json.user;
				let content = "";
				
				let replies = json.replies;
				
				let d = json;
				let hasParent = false;
				
				let echoParentList = false;
				
				if(d.hasOwnProperty("parent") && d.parent != null){
					content = content.concat('<ul class="list-group parents">');
					echoParentList = true;
				}
				
				while(d.hasOwnProperty("parent") && d.parent != null){
					d = d.parent;
					hasParent = true;
					
					content = d.listHtml.concat(content);
					/*'<div class="card feedEntry my-2 statusTrigger" data-status-render="' + d.id + '" data-entry-id="' + d.id + '">' +
					'<div class="py-1 px-3">' +
					'<div class="row">' +
					'<div class="float-left">' +
					'<a href="/' + d.user.username + '" class="clearUnderline ignoreParentClick">' +
					'<img class="rounded mx-1 my-1" src="' + d.user.avatar + '" width="36" height="36"/>' +
					'</a>' +
					'</div>' +
					
					'<div class="float-left ml-1" style="max-width: 414px;">' +
					'<p class="mb-0 small">' +
					'<a href="/' + d.user.username + '" class="clearUnderline ignoreParentClick">' +
					'<span class="font-weight-bold">' + d.user.displayName + '</span>' +
					'</a>' +
					
					' <span class="text-muted font-weight-normal">@' + d.user.username + '</span> ' +
					
					'&bull; ' +
					
					d.time +
					'</p>' +
					
					'<p class="mb-0">' +
					twemoji.parse(d.text) +
					'</p>' +
					
					d.postActionButtons +
					'</div>' +
					'</div>' +
					'</div>' +
					'</div>'
					.concat(content);*/
				}
				
				if(echoParentList){
					content = content.concat('</ul>');
				}
				
				if(hasParent == true)
				content = content.concat("<hr/>");
				
				content = content.concat('<div class="mb-4">');

				content = content.concat('<button type="button" class="close float-right" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button><br/>');

				content = content.concat(json.followButton);
				
				content = content.concat(
					'<div class="float-left mr-2">' +
					'<a href="/' + user.username + '" class="clearUnderline">' +
					'<img width="48" height="48" src="' + user.avatar + '" class="rounded"/>' +
					'</a>' +
					'</div>'
				);
				
				content = content.concat('<div class="ml-2">');
				
				content = content.concat(
					'<div><a href="/' + user.username + '" class="clearUnderline font-weight-bold mb-0" style="font-size:20px; word-wrap: break-word;">' +
					twemoji.parse(user.displayName) + (user.verified === true ? user.verifiedIcon : "") +
					'</a></div>'
				);
				
				content = content.concat(
					'<div class="text-muted" style="margin-top: -6px; word-wrap: break-word;">' +
					'@' + user.username +
					'</div>'
				);
				
				content = content.concat('</div>');
				
				content = content.concat('</div>');
				
				let c = json.hasOwnProperty("parent") && json.parent != null ? '<div class="small text-muted">Replying to <a href="/' + json.parent.user.username + '" class="clearUnderline">@' + json.parent.user.username + '</a></div>' : "";
				
				content = content.concat(
					'<div class="mt-2">' +
					c + 
					'<p style="font-size: 27px; word-wrap: break-word;">' +
					twemoji.parse(json.text) +
					'</p>'
				);
				
				if(json.hasOwnProperty("attachments") && json.attachments.length > 0){
					content = content.concat('<div class="mb-4 mt-2">');
					
					content = content.concat(json.attachmentHtml);
					
					content = content.concat('</div>');
				}
				
				content = content.concat(
					'<p class="small text-muted"><i class="far fa-clock"></i> Posted ' +
					json.time +
					' | #' + json.id + '</p>' +
					'</div>'
				);
				
				content = content.concat('<hr/>');
				
				content = content.concat(json.postActionButtons);
				
				if(typeof CURRENT_USER !== "undefined" && json.hasOwnProperty("postForm")){
					content = content.concat(json.postForm);
				}
				
				content = content.concat('<ul class="list-group replies">');
				
				if(replies.length > 0){
					replies.forEach(reply => {
						content = content.concat(reply.listHtml);
						/*content = content.concat(
							'<div class="card feedEntry my-2 statusTrigger" data-status-render="' + reply.id + '" data-entry-id="' + reply.id + '">' +
							'<div class="py-1 px-3">' +
							'<div class="row">' +
							'<div class="float-left">' +
							'<a href="/' + reply.user.username + '" class="clearUnderline ignoreParentClick">' +
							'<img class="rounded mx-1 my-1" src="' + reply.user.avatar + '" width="36" height="36"/>' +
							'</a>' +
							'</div>' +
							
							'<div class="float-left ml-1" style="max-width: 414px;">' +
							'<p class="mb-0 small">' +
							'<a href="/' + reply.user.username + '" class="clearUnderline ignoreParentClick">' +
							'<span class="font-weight-bold">' + reply.user.displayName + '</span>' +
							'</a>' +
							
							' <span class="text-muted font-weight-normal">@' + reply.user.username + '</span> ' +
							
							'&bull; ' +
							
							reply.time +
							'</p>' +
							
							'<p class="mb-0">' +
							twemoji.parse(reply.text) +
							'</p>' +
							
							reply.postActionButtons +
							'</div>' +
							'</div>' +
							'</div>' +
							'</div>'
						);*/
					});
				}
				
				content = content.concat('</ul>');
				
				statusModal.html(
					'<div class="modal-dialog" role="document">' +
					'<div class="modal-content">' +
					'<div class="modal-body">' +
					content +
					'</div>' +
					'</div>' +
					'</div>'
				);
				
				loadBasic();
				
				let title = user.displayName + " on qpost: \"" + limitString(json.textUnfiltered,34,true) + "\"";
				
				history.pushState({postId: postId},title,"/status/" + postId);
				document.title = title; 
				
				if(!isStatusModalOpen())
				statusModal.modal();
			} else if(json.hasOwnProperty("error")){
				statusModal.html(
					'<div class="modal-dialog" role="document">' +
					'<div class="modal-content">' +
					'<div class="modal-body">' +
					json.error +
					'</div>' +
					'</div>' +
					'</div>'
				);
				
				if(!isStatusModalOpen())
				statusModal.modal();
			} else {
				statusModal.html(
					'<div class="modal-dialog" role="document">' +
					'<div class="modal-content">' +
					'<div class="modal-body">' +
					json +
					'</div>' +
					'</div>' +
					'</div>'
				);
				
				if(!isStatusModalOpen())
				statusModal.modal();
			}
		},
		
		error: function(xhr,status,error){
			console.log(xhr);
			console.log(status);
			console.log(error);
		}
	});
}

function limitString(string,length,addDots = true){
	if(addDots){
		length = length-3;
		if(length < 1) length = 1;
	}
	
	if(string.length > length){
		return string.substr(0,length) + (addDots == true ? "..." : "");
	} else {
		return string;
	}
}

function loadHomeFeed(){
	if(typeof HOME_FEED_LAST_POST == "undefined"){
		setTimeout(loadHomeFeed,5000);
		return;
	}
	
	if($(".homePostField").length){
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
						
						newHtml = newHtml.concat(post.listHtml);
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

function checkForNotifications(){
	$.ajax({
		url: "/scripts/desktopNotifications",
		method: "GET",
		
		success: function(result){
			let unreadCount = result.unreadCount;
			if(unreadCount > 0){
				$(".notificationTabMainNav").html("notifications <b>(" + unreadCount + ")</b>");
			} else {
				$(".notificationTabMainNav").html("notifications");
			}
			
			if(result.hasOwnProperty("notifications") && result.notifications.length > 0){
				result.notifications.forEach(notificationData => {
					let title = null;
					let text = null;
					if(notificationData.type == "NEW_FOLLOWER"){
						title = notificationData.follower.displayName + " is now following you";
						text = notificationData.follower.bio != null ? notificationData.follower.bio : ""
					} else if(notificationData.type == "MENTION"){
						title = notificationData.follower.displayName + " mentioned you";
						text = notificationData.post.textUnfiltered;
					} else if(notificationData.type == "FAVORITE"){
						title = notificationData.follower.displayName + " favorited your post";
						text = notificationData.post.textUnfiltered;
					} else if(notificationData.type == "SHARE"){
						title = notificationData.follower.displayName + " shared your post";
						text = notificationData.post.textUnfiltered;
					} else if(notificationData.type == "REPLY"){
						title = notificationData.follower.displayName + " replied to your post";
						text = notificationData.post.textUnfiltered;
					}
					
					if(title != null && text != null){
						let notification = new Notification(
							title,
							
							{
								body: text.replace("<br/>","\n"),
								icon: notificationData.follower.avatar
							}
						);
						
						notification.onclick = (event) => {
							event.preventDefault();
							window.open("/notifications","_blank");
						};
					}
				});
			}
			
			setTimeout(checkForNotifications,3000);
		},
		
		error: function(xhr,status,error){
			console.log(xhr);
			console.log(status);
			console.log(error);
			
			setTimeout(checkForNotifications,3000);
		}
	});
}

function loadOldHomeFeed(){
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
				let adcount = 10;
				
				if(json.result.length > 0){
					let i;
					for(i = 0; i < json.result.length; i++){
						let post = json.result[i];
						
						let postId = post.id;
						
						if(i == json.result.length-1){
							HOME_FEED_FIRST_POST = postId;
						}
						
						newHtml = newHtml.concat(post.listHtml);

						adcount--;
						if(adcount == 0){
							newHtml = newHtml.concat(adbanner_leaderboard(true,["my-3"]));
							adcount = 10;
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
}

function loadBasic(){
	console.log("load basic");

	$('[data-toggle="tooltip"]').tooltip({
		trigger: "hover"
	});
	
	$('[data-toggle="popover"]').popover({
		trigger: "focus",
		html: true
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
	
	$(".datepicker").datepicker();
	
	$(".birthdayDatepicker").datepicker({
		endDate: new Date(new Date().setFullYear(new Date().getFullYear() - 13))
	});
	
	$(".convertEmoji").html(function(){
		return twemoji.parse($(this).html());
	}).removeClass("convertEmoji");
}

$(document).ready(function(){
	load();
	loadNotificationAlert();
	resetStatusModal();
	checkForNotifications();
	loadHomeFeed();
	loadDropzone();
	loadBasic();
});

function loadNotificationAlert(){
	let pointer = ".notificationPermissionAlert";
	
	if($(pointer).length > 0){
		if(!hasNotificationPermissions() && (!hasCookie("ignoreNotificationAlert") || getCookie("ignoreNotificationAlert") != "true")){
			$(pointer).removeClass("d-none");
		}
	}
}

function hasNotificationPermissions(){
	return Notification.permission === "granted";
}

var dz = null;
function loadDropzone(){
	let postBox = $(".dropzone-previews").closest(".postBox");
	let attachmentValueField = postBox.find("input[name=\"attachmentData\"]");
	let postButton = postBox.find(".postButton");
	
	if($(".dropzone-previews").length == 0) return;
	
	dz = new Dropzone(document.body, {
		url: "/scripts/mediaUpload",
		paramName: "file",
		maxFilesize: 10,
		maxFiles: 4,
		acceptedFiles: "image/*",
		previewsContainer: ".dropzone-previews",
		clickable: ".addPhoto",
		previewTemplate: $(".preview-template").html(),
		thumbnailWidth: 100,
		thumbnailHeight: 100,
		parallelUploads: 4,
		accept: (file,done) => { return done(); },
		thumbnail: function thumbnail(file, dataUrl) {
			if (file.previewElement) {
				file.previewElement.classList.remove("dz-file-preview");
				for (var _iterator6 = file.previewElement.querySelectorAll("[data-dz-thumbnail]"), _isArray6 = true, _i6 = 0, _iterator6 = _isArray6 ? _iterator6 : _iterator6[Symbol.iterator]();;) {
					var _ref5;
					
					if (_isArray6) {
						if (_i6 >= _iterator6.length) break;
						_ref5 = _iterator6[_i6++];
					} else {
						_i6 = _iterator6.next();
						if (_i6.done) break;
						_ref5 = _i6.value;
					}
					
					var thumbnailElement = _ref5;
					
					/*thumbnailElement.alt = file.name;
					thumbnailElement.src = dataUrl;*/
					thumbnailElement.style.backgroundImage = "url('" + dataUrl + "')";
				}
				
				return setTimeout(function () {
					return file.previewElement.classList.add("dz-image-preview");
				}, 1);
			}
		},
		addedfile: function addedfile(file) {
			if(dz.getAcceptedFiles().length >= 4) return;
			
			var _this2 = this;
			
			if (this.element === this.previewsContainer) {
				this.element.classList.add("dz-started");
			}
			
			if (this.previewsContainer) {
				console.log(file.status);
				file.previewElement = Dropzone.createElement(this.options.previewTemplate.trim());
				file.previewTemplate = file.previewElement; // Backwards compatibility
				
				this.previewsContainer.appendChild(file.previewElement);
				for (var _iterator3 = file.previewElement.querySelectorAll("[data-dz-name]"), _isArray3 = true, _i3 = 0, _iterator3 = _isArray3 ? _iterator3 : _iterator3[Symbol.iterator]();;) {
					var _ref3;
					
					if (_isArray3) {
						if (_i3 >= _iterator3.length) break;
						_ref3 = _iterator3[_i3++];
					} else {
						_i3 = _iterator3.next();
						if (_i3.done) break;
						_ref3 = _i3.value;
					}
					
					var node = _ref3;
					
					node.textContent = file.name;
				}
				for (var _iterator4 = file.previewElement.querySelectorAll("[data-dz-size]"), _isArray4 = true, _i4 = 0, _iterator4 = _isArray4 ? _iterator4 : _iterator4[Symbol.iterator]();;) {
					if (_isArray4) {
						if (_i4 >= _iterator4.length) break;
						node = _iterator4[_i4++];
					} else {
						_i4 = _iterator4.next();
						if (_i4.done) break;
						node = _i4.value;
					}
					
					node.innerHTML = this.filesize(file.size);
				}
				
				if (this.options.addRemoveLinks) {
					file._removeLink = Dropzone.createElement("<a class=\"dz-remove\" href=\"javascript:undefined;\" data-dz-remove>" + this.options.dictRemoveFile + "</a>");
					file.previewElement.appendChild(file._removeLink);
				}
				
				var removeFileEvent = function removeFileEvent(e) {
					e.preventDefault();
					e.stopPropagation();
					if (file.status === Dropzone.UPLOADING) {
						return Dropzone.confirm(_this2.options.dictCancelUploadConfirmation, function () {
							return _this2.removeFile(file);
						});
					} else {
						if (_this2.options.dictRemoveFileConfirmation) {
							return Dropzone.confirm(_this2.options.dictRemoveFileConfirmation, function () {
								return _this2.removeFile(file);
							});
						} else {
							return _this2.removeFile(file);
						}
					}
				};
				
				for (var _iterator5 = file.previewElement.querySelectorAll("[data-dz-remove]"), _isArray5 = true, _i5 = 0, _iterator5 = _isArray5 ? _iterator5 : _iterator5[Symbol.iterator]();;) {
					var _ref4;
					
					if (_isArray5) {
						if (_i5 >= _iterator5.length) break;
						_ref4 = _iterator5[_i5++];
					} else {
						_i5 = _iterator5.next();
						if (_i5.done) break;
						_ref4 = _i5.value;
					}
					
					var removeLink = _ref4;
					
					removeLink.addEventListener("click", removeFileEvent);
				}
			}
		}
	});
	
	dz.on("sending",(file,xhr,formData) => {
		formData.append("csrf_token",CSRF_TOKEN);
		
		postButton.html('<i class="fas fa-spinner fa-pulse"></i>');
	});
	
	dz.on("success",(file,responseText,e) => {
		if(responseText.hasOwnProperty("ids")){
			responseText.ids.forEach(id => {
				let array = [];
				if(typeof attachmentValueField.val() !== typeof undefined && attachmentValueField.val() != ""){
					let b64 = atob(attachmentValueField.val().replace(/\s/g,""));
					array = JSON.parse(b64);
				}
				
				array.push(id);
				
				attachmentValueField.val(btoa(JSON.stringify(array)));
			});
		}
	});
	
	dz.on("queuecomplete",(progress) => {
		postButton.html("Post");
	});
	
	dz.on("error",(file,message,xhr) => {
		console.error("Dropzone encountered error",message,xhr);
	});
	
	dz.on("addedfile", (file) => {
		if(typeof file.previewElement !== "undefined"){
			let button = Dropzone.createElement("<button class=\"btn btn-danger btn-sm float-right\" style=\"margin-left: -50px; z-index: 99999; position: relative;\"><i class=\"fas fa-trash-alt\"></i></button>");
			
			button.addEventListener("click", (e) => {
				e.preventDefault();
				e.stopPropagation();
				
				if(file.status === "success"){
					let index = dz.getAcceptedFiles().indexOf(file);
					
					if(index > -1){
						let array = [];
						if(attachmentValueField.val() != "") array = JSON.parse(atob(attachmentValueField.val()));
						
						if(array.length >= index+1){
							array.splice(index,1);
						}
						
						attachmentValueField.val(btoa(JSON.stringify(array)));
					}
				}
				
				dz.removeFile(file);
			});
			
			file.previewElement.prepend(button);
			
			//dz.enqueueFile(file);
		}
	});
}

function hasAttr(element,attribute){
	return element.attr(attribute) !== false && typeof element.attr(attribute) !== typeof undefined;
}

function load(){
	Dropzone.autoDiscover = false;
	
	$(document).on("click",".enableNotifications",function(e){
		e.preventDefault();
		
		$(".notificationPermissionAlert").addClass("d-none");
		Notification.requestPermission();
	});
	
	$(document).on("click",".addPhoto",function(e){
		e.preventDefault();
		
		
	});

	$(document).on("click",".toggleNSFW",function(e){
		e.preventDefault();

		if($(this).hasClass("text-success")){
			// toggle on
			$(this).removeClass("text-success").addClass("text-danger");
			updateTooltip(this,"NSFW: on");
		} else {
			// toggle off
			$(this).removeClass("text-danger").addClass("text-success");
			updateTooltip(this,"NSFW: off");
		}
	});
	
	$(document).on('show.bs.modal', '.modal', function () {
		var zIndex = 1040 + (10 * $('.modal:visible').length);
		$(this).css('z-index', zIndex);
		setTimeout(function() {
			$('.modal-backdrop').not('.modal-stack').css('z-index', zIndex - 1).addClass('modal-stack');
		}, 0);
	});

	var LOADED_SHARES = [];
	var LOADED_FAVORITES = [];

	$(document).on("shown.bs.tooltip",".shareCount", function(e){
		let title = $(this).attr("data-original-title");
		let postID = $(this).attr("data-post-id");

		if(title === "Loading..." && postID && !LOADED_SHARES.includes(postID)){
			LOADED_SHARES.push(postID);

			let t = this;

			$.ajax({
				url: "/scripts/shareSample",
				data: {
					csrf_token: CSRF_TOKEN,
					post: postID
				},
				method: "POST",
				
				success: function(result){
					let json = result;

					if(json.hasOwnProperty("users") && json.hasOwnProperty("showMore") && json.hasOwnProperty("showMoreCount")){
						if(json.users.length > 0){
							let s = "";

							json.users.forEach(user => {
								if(s !== "") s += "<br/>";
								s += user.displayName + " (@" + user.username + ")";
							});

							if(json.showMore === true){
								s += "<br/>and " + json.showMoreCount + " more...";
							}

							updateTooltip(t,s);
						}
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

	$(document).on("shown.bs.tooltip",".favoriteCount", function(e){
		let title = $(this).attr("data-original-title");
		let postID = $(this).attr("data-post-id");

		if(title === "Loading..." && postID && !LOADED_FAVORITES.includes(postID)){
			LOADED_FAVORITES.push(postID);

			let t = this;

			$.ajax({
				url: "/scripts/favoriteSample",
				data: {
					csrf_token: CSRF_TOKEN,
					post: postID
				},
				method: "POST",
				
				success: function(result){
					let json = result;

					if(json.hasOwnProperty("users") && json.hasOwnProperty("showMore") && json.hasOwnProperty("showMoreCount")){
						if(json.users.length > 0){
							let s = "";

							json.users.forEach(user => {
								if(s !== "") s += "<br/>";
								s += user.displayName + " (@" + user.username + ")";
							});

							if(json.showMore === true){
								s += "<br/>and " + json.showMoreCount + " more...";
							}

							updateTooltip(t,s);
						}
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
	
	$(document).on('hidden.bs.modal', '.modal', function () {
		$('.modal:visible').length && $(document.body).addClass('modal-open');
	});
	
	/*$(document).on("scroll",function(e){
		let scrollValue = $(document).scrollTop();
		
		if(scrollValue >= 100){
			$(".homeFeedSidebar").attr("style","position: fixed; margin-top: -10px");
		} else {
			$(".homeFeedSidebar").removeAttr("style");
		}
	});*/
	
	$(document).on("click",".hideNotifications",function(e){
		e.preventDefault();
		
		$(".notificationPermissionAlert").addClass("d-none");
		setCookie("ignoreNotificationAlert","true",7);
	});
	
	let birthdayBox = $(".birthdayContainer");
	if(birthdayBox.length > 0){
		let now = new Date();
		let dateString = now.getFullYear() + "-" + ("0" + (now.getMonth()+1)).slice(-2) + "-" + ("0" + now.getDate()).slice(-2);
		
		$.ajax({
			url: "/scripts/loadBirthdays",
			data: {
				csrf_token: CSRF_TOKEN,
				dateString: dateString
			},
			method: "POST",
			
			success: function(result){
				let json = result;
				
				if(json.hasOwnProperty("results") && json.hasOwnProperty("html")){
					if(json.results > 0){
						birthdayBox.html(json.html);
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
	
	if($(".postField").length > 0){
		$(".postField").highlightWithinTextarea({
			highlight: (input) => {
				if(input.length > POST_CHARACTER_LIMIT){
					return [POST_CHARACTER_LIMIT,input.length];
				} else {
					return false;
				}
			}
		});
	}
	
	$(document).on("click",".postFormTextButton",function(e){
		e.preventDefault();
		
		let postBox = $(this).parent().parent().parent().parent();
		
		let textButton = $(this);
		let videoButton = $(this).parent().parent().find(".postFormVideoButton");
		let linkButton = $(this).parent().parent().find(".postFormLinkButton");
		
		let linkURLBox = postBox.find(".linkURL");
		let videoURLBox = postBox.find(".videoURL");
		
		let dropzonePreviews = postBox.find(".dropzone-previews");
		let addPhotoButton = postBox.find(".addPhoto");
		
		if(!hasAttr($(this),"disabled")){
			$(this).prop("disabled",true);
			if(hasAttr(videoButton,"disabled")) videoButton.removeAttr("disabled");
			if(hasAttr(linkButton,"disabled")) linkButton.removeAttr("disabled");
			
			if(linkURLBox.length > 0 && !linkURLBox.hasClass("d-none")) linkURLBox.addClass("d-none");
			if(videoURLBox.length > 0 && !videoURLBox.hasClass("d-none")) videoURLBox.addClass("d-none");
			
			if(dropzonePreviews.length > 0 && dropzonePreviews.hasClass("d-none")) dropzonePreviews.removeClass("d-none");
			if(addPhotoButton.length > 0 && addPhotoButton.hasClass("d-none")) addPhotoButton.removeClass("d-none");
		}
	});
	
	$(document).on("click",".postFormVideoButton",function(e){
		e.preventDefault();
		
		let postBox = $(this).parent().parent().parent().parent();
		
		let textButton = $(this).parent().parent().find(".postFormTextButton");
		let videoButton = $(this);
		let linkButton = $(this).parent().parent().find(".postFormLinkButton");
		
		let linkURLBox = postBox.find(".linkURL");
		let videoURLBox = postBox.find(".videoURL");
		
		let dropzonePreviews = postBox.find(".dropzone-previews");
		let addPhotoButton = postBox.find(".addPhoto");
		
		if(!hasAttr($(this),"disabled")){
			$(this).prop("disabled",true);
			if(hasAttr(textButton,"disabled")) textButton.removeAttr("disabled");
			if(hasAttr(linkButton,"disabled")) linkButton.removeAttr("disabled");
			
			if(linkURLBox.length > 0 && !linkURLBox.hasClass("d-none")) linkURLBox.addClass("d-none");
			if(videoURLBox.length > 0 && videoURLBox.hasClass("d-none")) videoURLBox.removeClass("d-none");
			
			if(dropzonePreviews.length > 0 && !dropzonePreviews.hasClass("d-none")) dropzonePreviews.addClass("d-none");
			if(addPhotoButton.length > 0 && !addPhotoButton.hasClass("d-none")) addPhotoButton.addClass("d-none");
		}
	});
	
	$(document).on("click",".postFormLinkButton",function(e){
		e.preventDefault();
		
		let postBox = $(this).parent().parent().parent().parent();
		
		let textButton = $(this).parent().parent().find(".postFormTextButton");
		let videoButton = $(this).parent().parent().find(".postFormVideoButton");
		let linkButton = $(this);
		
		let linkURLBox = postBox.find(".linkURL");
		let videoURLBox = postBox.find(".videoURL");
		
		let dropzonePreviews = postBox.find(".dropzone-previews");
		let addPhotoButton = postBox.find(".addPhoto");
		
		if(!hasAttr($(this),"disabled")){
			$(this).prop("disabled",true);
			if(hasAttr(textButton,"disabled")) textButton.removeAttr("disabled");
			if(hasAttr(videoButton,"disabled")) videoButton.removeAttr("disabled");
			
			if(linkURLBox.length > 0 && linkURLBox.hasClass("d-none")) linkURLBox.removeClass("d-none");
			if(videoURLBox.length > 0 && !videoURLBox.hasClass("d-none")) videoURLBox.addClass("d-none");
			
			if(dropzonePreviews.length > 0 && !dropzonePreviews.hasClass("d-none")) dropzonePreviews.addClass("d-none");
			if(addPhotoButton.length > 0 && !addPhotoButton.hasClass("d-none")) addPhotoButton.addClass("d-none");
		}
	});
	
	function handleButtonClick(postBox,postField,postCharacterCounter,linkURL,videoURL,isReply,text,replyTo,token,oldHtml,attachments,nsfw){
		$.ajax({
			url: "/scripts/createPost",
			data: {
				csrf_token: token,
				text: text,
				replyTo: replyTo,
				attachments: attachments,
				linkURL: linkURL,
				videoURL: videoURL,
				nsfw: nsfw
			},
			method: "POST",
			
			success: function(result){
				let json = result;
				
				if(json.hasOwnProperty("post")){
					let post = json.post;
					
					if(post.hasOwnProperty("id") && post.hasOwnProperty("time") && post.hasOwnProperty("text") && post.hasOwnProperty("user")){
						let postId = post.id;
						let postTime = post.time;
						let postText = post.text;
						
						let userName = post.user.username;
						let userDisplayName = post.user.displayName;
						let userAvatar = post.user.avatar;
						
						HOME_FEED_LAST_POST = postId;
						
						let postActionButtons = json.postActionButtons;
						
						let newHtml = "";
						
						if(!isReply){
							newHtml = post.listHtml;
							
							if($(".feedEntry").length){
								$(".feedContainer").prepend(newHtml);
							} else {
								$(".feedContainer").html(newHtml);
							}
						} else {
							/*newHtml =
							'<div class="card feedEntry my-2 statusTrigger" data-status-render="' + postId + '" data-entry-id="' + postId + '">' +
							'<div class="py-1 px-3">' +
							'<div class="row">' +
							'<div class="float-left">' +
							'<a href="/' + userName + '" class="clearUnderline ignoreParentClick">' +
							'<img class="rounded mx-1 my-1" src="' + userAvatar + '" width="36" height="36"/>' +
							'</a>' +
							'</div>' +
							
							'<div class="float-left ml-1" style="max-width: 414px;">' +
							'<p class="mb-0 small">' +
							'<a href="/' + userName + '" class="clearUnderline ignoreParentClick">' +
							'<span class="font-weight-bold">' + userDisplayName + '</span>' +
							'</a>' +
							
							' <span class="text-muted font-weight-normal">@' + userName + '</span> ' +
							
							'&bull; ' +
							
							postTime +
							'</p>' +
							
							'<p class="mb-0">' +
							twemoji.parse(postText) +
							'</p>' +
							
							postActionButtons +
							'</div>' +
							'</div>' +
							'</div>' +
							'</div>';*/
							newHtml = post.listHtml;
							
							if($("#statusModal .replies>.list-group-item").length){
								$("#statusModal .replies").prepend(newHtml);
							} else {
								$("#statusModal .replies").html(newHtml);
							}
						}
						
						postBox.html(oldHtml);
						postBox.find(".postField").focus();
						postField.val("");
						postBox.find(".postCharacterCounter").html(POST_CHARACTER_LIMIT);
						postBox.find("input[name=\"attachmentData\"]").val("");
						loadBasic();
						dz.destroy();
						loadDropzone();
						
						$(".dropzone-previews").html("");
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
		});
	}

	$(document).on("click",".nsfwInfo",function(e){
		e.preventDefault();

		// show nsfw content
		$(this).addClass("d-none").parent().find(".hiddenNSFW").removeClass("d-none");
	});
	
	$(document).on("click",".postButton",function(e){
		e.preventDefault();
		
		let postBox = $(this).parent().parent();
		let postField = postBox.find(".postField");
		let postCharacterCounter = $(this).parent().find(".postCharacterCounter");
		
		let textButton = postBox.find(".postFormTextButton");
		let videoButton = postBox.find(".postFormVideoButton");
		let linkButton = postBox.find(".postFormLinkButton");
		
		let linkURLBox = postBox.find(".linkURL");
		let videoURLBox = postBox.find(".videoURL");
		
		let currentMode = "TEXT";
		if(textButton.length > 0 && hasAttr(textButton,"disabled")){
			currentMode = "TEXT";
		} else if(videoButton.length > 0 && hasAttr(videoButton,"disabled")){
			currentMode = "VIDEO";
		} else if(linkButton.length > 0 && hasAttr(linkButton,"disabled")){
			currentMode = "LINK";
		}
		
		let linkURL = null;
		let videoURL = null;
		
		if(currentMode == "VIDEO" && videoURLBox.length && videoURLBox.find("input").length){
			videoURL = videoURLBox.find("input").val().trim();
			
			if(videoURL === ""){
				videoURL = null;
			}
		} else if(currentMode == "LINK" && linkURLBox.length && linkURLBox.find("input").length){
			linkURL = linkURLBox.find("input").val().trim();
			
			if(linkURL === ""){
				linkURL = null;
			}
		}
		
		if(linkURL != null && !isValidURL(linkURL)){
			return console.error("Invalid link URL");
		}
		
		if(videoURL != null && !isValidURL(videoURL)){
			return console.error("Invalid video URL");
		}
		
		let isReply = postBox.hasClass("replyForm");
		
		let text = postField.val().trim();
		
		let replyTo = isReply && CURRENT_STATUS_MODAL > 0 ? CURRENT_STATUS_MODAL : null;
		
		let attachmentValueField = $(this).closest(".postBox").find("input[name=\"attachmentData\"]");
		
		if(typeof CSRF_TOKEN !== undefined && typeof POST_CHARACTER_LIMIT !== undefined && !$(this).html().includes("<i")){
			let token = CSRF_TOKEN;
			let limit = POST_CHARACTER_LIMIT;
			
			let oldHtml = postBox.html();

			let nsfw = false;
			if(postBox.find(".toggleNSFW").length){
				if(postBox.find(".toggleNSFW").hasClass("text-danger")){
					nsfw = true;
				}
			}

			let attachments = "[]";
				
			if(currentMode == "TEXT" && attachmentValueField.length && attachmentValueField.val() != ""){
				attachments = atob(attachmentValueField.val());
			}
			
			if(text.length <= limit){
				if(text.length > 0 || videoURL != null || JSON.parse(attachments).length > 0){
					if(currentMode == "TEXT" || (currentMode == "VIDEO" && videoURL == null) || currentMode == "LINK"){
						postBox.html('<div class="card-body text-center"><i class="fas fa-spinner fa-pulse"></i></div>');
						handleButtonClick(postBox,postField,postCharacterCounter,linkURL,videoURL,isReply,text,replyTo,token,oldHtml,attachments,nsfw);
					} else if(currentMode == "VIDEO"){
						$.ajax({
							url: "/scripts/validateVideoURL",
							data: {
								csrf_token: token,
								videoURL: videoURL
							},
							method: "POST",
							
							success: function(json){
								if(json.hasOwnProperty("status")){
									if(json.status == "valid"){
										postBox.html('<div class="card-body text-center"><i class="fas fa-spinner fa-pulse"></i></div>');
										handleButtonClick(postBox,postField,postCharacterCounter,linkURL,videoURL,isReply,text,replyTo,token,oldHtml,attachments,nsfw);
									} else {
										console.error("Invalid video URL");
									}
								} else {
									console.error(json);
								}
							},
							
							error: function(xhr,status,error){
								console.log(xhr);
								console.log(status);
								console.log(error);
							}
						});
					} else {
						console.error("Invalid mode");
					}
				} else {
					console.error("Post is empty");
				}
			} else {
				console.error("Post text too long");
			}
		}
	});
	
	$(document).on("click","a.filterLink",function(e){
		e.preventDefault();
		
		if(typeof $(this).attr("href") !== "undefined"){
			let link = $(this).attr("href");
			
			window.location.href = "/out?link=" + encodeURI(link);
		}
	});
	
	$(document).on("click",".statusTrigger",function(e){
		e.preventDefault();
		
		if(typeof $(this).attr("data-status-render") !== "undefined"){
			let postId = $(this).attr("data-status-render");
			
			showStatusModal(postId);
		}
	});
	
	$(document).on("hidden.bs.modal","#statusModal",function(e){
		if(typeof restoreUrl !== "undefined" && typeof restoreTitle !== "undefined"){
			history.pushState("",restoreTitle,restoreUrl);
			document.title = restoreTitle;
			
			restoreUrl = "";
			restoreTitle = "";
			CURRENT_STATUS_MODAL = 0;
		}
	});
	
	$(document).on("click",".replyButton",function(e){
		e.preventDefault();
		
		let postId = $(this).attr("data-reply-id");
		
		if(typeof postId !== "undefined" && postId !== false){
			if(window.location.pathname.endsWith(postId)){
				$("#statusModalPostField").focus();
			} else {
				showStatusModal(postId);
			}
		}
	});
	
	$(document).on("click",".deleteButton",function(e){
		e.preventDefault();
		
		let postId = $(this).attr("data-post-id");
		
		if(typeof postId !== "undefined" && postId !== false){
			if(isStatusModalOpen())
			closeStatusModal();
			
			showDeleteModal(postId);
		} else {
			console.error("No post id found");
		}
	});
	
	$(document).on("click",".finDel",function(e){
		e.preventDefault();
		
		let postId = $(this).attr("data-post-id");
		
		if(typeof postId !== "undefined" && postId !== false){
			if(isDeleteModalOpen())
			closeDeleteModal();
			
			$.ajax({
				url: "/scripts/deletePost",
				data: {
					csrf_token: CSRF_TOKEN,
					post: postId
				},
				method: "POST",
				
				success: function(json){
					if(json.hasOwnProperty("status")){
						if(json.status == "done"){
							$('[data-status-render="' + postId + '"]').remove();
							$('[data-entry-id="' + postId + '"]').remove();
							$('[data-post-id="' + postId + '"]').remove();
						} else {
							console.error("Invalid status: " + json.status);
						}
					} else {
						console.log(json);
					}
				},
				
				error: function(xhr,status,error){
					console.log(xhr);
					console.log(status);
					console.log(error);
				}
			});
		} else {
			console.error("No post id found");
		}
	});
	
	$(document).on("click",".favoriteButton",function(e){
		e.preventDefault();
		
		let postId = $(this).attr("data-post-id");
		
		let favoritedHtml = '<a class="nav-link" style="color: gold" href="#"><i class="fas fa-star"></i> Favorite</a>';
		let unfavoritedHtml = '<a class="nav-link" style="color: ' + GRAYVAR +  '" href="#"><i class="fas fa-star"></i> Favorite</a>';
		let loadingHtml = '<a class="nav-link" style="color: ' + GRAYVAR + '" href="#"><i class="fas fa-spinner fa-pulse"></i></a>';
		
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
						let countHtml = "";

						if(json.replies > 0){
							countHtml = countHtml.concat('<div class="float-left mr-3">');
							countHtml = countHtml.concat('<i class="fas fa-share"></i> ' + json.replies + ' repl' + (json.replies != 1 ? "ies" : "y"));
							countHtml = countHtml.concat('</div>');
						}

						if(json.shares > 0){
							countHtml = countHtml.concat('<div class="float-left mr-3" data-post-id="' + postId + '" data-type="shares" data-toggle="tooltip" data-html="true" title="Loading...">');
							countHtml = countHtml.concat('<i class="fas fa-share-alt"></i> ' + json.shares + ' share' + (json.shares != 1 ? "s" : ""));
							countHtml = countHtml.concat('</div>');
						}

						if(json.favorites > 0){
							countHtml = countHtml.concat('<div class="float-left mr-3" data-post-id="' + postId + '" data-type="favorites" data-toggle="tooltip" data-html="true" title="Loading...">');
							countHtml = countHtml.concat('<i class="fas fa-star"></i> ' + json.favorites + ' favorite' + (json.favorites != 1 ? "s" : ""));
							countHtml = countHtml.concat('</div>');
						}

						let countContainer = pointer.parent().parent().find(".countContainer");

						if(countHtml != ""){
							if(!countContainer.length){
								pointer.parent().before('<div class="countContainer mt-3 mb-5 small text-muted"></div>');
								countContainer = pointer.parent().parent().find(".countContainer");
							}

							countContainer.html(countHtml);
						} else if(countContainer.length && countHtml == ""){
							countContainer.remove();
						}
						
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
	
	$(document).on("click",".shareButton",function(e){
		e.preventDefault();
		
		let postId = $(this).attr("data-post-id");
		
		let sharedHtml = '<a class="nav-link text-blue" href="#"><i class="fas fa-share-alt"></i> Share</a>';
		let unsharedHtml = '<a class="nav-link" style="color: ' + GRAYVAR + '" href="#"><i class="fas fa-share-alt"></i> Share</a>';
		let loadingHtml = '<a class="nav-link" style="color: ' + GRAYVAR + '" href="#"><i class="fas fa-spinner fa-pulse"></i></a>';
		
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
						let countHtml = "";

						if(json.replies > 0){
							countHtml = countHtml.concat('<div class="float-left mr-3">');
							countHtml = countHtml.concat('<i class="fas fa-share"></i> ' + json.replies + ' repl' + (json.replies != 1 ? "ies" : "y"));
							countHtml = countHtml.concat('</div>');
						}

						if(json.shares > 0){
							countHtml = countHtml.concat('<div class="float-left mr-3" data-post-id="' + postId + '" data-type="shares" data-toggle="tooltip" data-html="true" title="Loading...">');
							countHtml = countHtml.concat('<i class="fas fa-share-alt"></i> ' + json.shares + ' share' + (json.shares != 1 ? "s" : ""));
							countHtml = countHtml.concat('</div>');
						}

						if(json.favorites > 0){
							countHtml = countHtml.concat('<div class="float-left mr-3" data-post-id="' + postId + '" data-type="favorites" data-toggle="tooltip" data-html="true" title="Loading...">');
							countHtml = countHtml.concat('<i class="fas fa-star"></i> ' + json.favorites + ' favorite' + (json.favorites != 1 ? "s" : ""));
							countHtml = countHtml.concat('</div>');
						}

						let countContainer = pointer.parent().parent().find(".countContainer");

						if(countHtml != ""){
							if(!countContainer.length){
								pointer.parent().before('<div class="countContainer mt-3 mb-5 small text-muted"></div>');
								countContainer = pointer.parent().parent().find(".countContainer");
							}

							countContainer.html(countHtml);
						} else if(countContainer.length && countHtml == ""){
							countContainer.remove();
						}
						
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
	
	$(document).on("click",".ignoreParentClick",function(e){
		e.stopPropagation();
	});
	
	document.onpaste = function(event){
		if(event.target.classList.contains("postField")){
			const target = $(event.target);

			const select = target.parent().parent().find(".dropzone-previews");
			const previews = select && select.length ? select[0] : null;

			const items = (event.clipboardData || event.originalEvent.clipboardData).items;

			for(index in items){
				const item = items[index];

				if(item.kind === "file"){
					const blob = item.getAsFile();

					if(dz && $(dz.previewsContainer).length && $(dz.previewsContainer)[0] == previews){ // verify it's being pasted in the proper textarea
						dz.addFile(blob);
					}
				}
			}
		}
	}
	
	$(document).on("change keyup keydown paste",".postField",function(e){
		if((e.ctrlKey || e.metaKey) && (e.keyCode == 13 || e.keyCode == 10)){
			// click post button
			$(this).parent().parent().find(".postButton").click();
		} else {
			const limit = POST_CHARACTER_LIMIT;
			const used = $(this).val().length;
			const left = limit-used;
			const counter = $(this).parent().parent().find(".postCharacterCounter");
			
			if(left > 0){
				if(left > limit/2){
					if(left == 1){
						counter.html(left);
					} else {
						counter.html(left);
					}
				} else {
					if(left == 1){
						counter.html("<span style=\"color: #F94F12;\">" + left + "</span>");
					} else {
						counter.html("<span style=\"color: #F94F12;\">" + left + "</span>");
					}
				}
			} else if(left == 0){
				counter.html("<span style=\"color: #FF0000; font-weight: bold\">0</span>");
			} else {
				if(left == 1){
					counter.html("<span style=\"color: #FF0000; font-weight: bold\">" + left + "</span>");
				} else {
					counter.html("<span style=\"color: #FF0000; font-weight: bold\">" + left + "</span>");
				}
			}
		}
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
					let json = result;
					
					if(json.hasOwnProperty("followStatus")){
						if(json.followStatus == 1){
							e.classList.add("unfollowButton");
							e.classList.add("btn-danger");
							
							e.classList.remove("pendingButton");
							e.classList.remove("btn-warning");
							
							e.classList.remove("followButton");
							e.classList.remove("btn-primary");
							
							e.innerHTML = "Unfollow";
						} else if(json.followStatus == 0) {
							e.classList.remove("unfollowButton");
							e.classList.remove("btn-danger");
							
							e.classList.remove("pendingButton");
							e.classList.remove("btn-warning");
							
							e.classList.add("followButton");
							e.classList.add("btn-primary");
							
							e.innerHTML = "Follow";
						} else if(json.followStatus == 2){
							e.classList.remove("unfollowButton");
							e.classList.remove("btn-danger");
							
							e.classList.add("pendingButton");
							e.classList.add("btn-warning");
							
							e.classList.remove("followButton");
							e.classList.remove("btn-primary");
							
							e.innerHTML = "Pending";
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

// https://stackoverflow.com/a/5968306
function getCookie(name) {
	var dc = document.cookie;
	var prefix = name + "=";
	var begin = dc.indexOf("; " + prefix);
	if (begin == -1) {
		begin = dc.indexOf(prefix);
		if (begin != 0) return null;
	}
	else
	{
		begin += 2;
		var end = document.cookie.indexOf(";", begin);
		if (end == -1) {
			end = dc.length;
		}
	}
	// because unescape has been deprecated, replaced with decodeURI
	//return unescape(dc.substring(begin + prefix.length, end));
	return decodeURI(dc.substring(begin + prefix.length, end));
} 

function hasCookie(name){
	return getCookie(name) != null;
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