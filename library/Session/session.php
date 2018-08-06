<?php

//
// -----------------------------------------------------------------------------------------------------------------
// SESSION FUNCTIONS START
// -----------------------------------------------------------------------------------------------------------------
//

function openSession($path,$name){
	/*$s = mysqli_query($link,"INSERT INTO `sessions` (`id`,`data`,`lastAccessTime`,`ip`) VALUES('" . mysqli_real_escape_string($link,$name) . "','',CURRENT_TIMESTAMP,'" . mysqli_real_escape_string($link,getIP()) . "') ON DUPLICATE KEY UPDATE `lastAccessTime` = CURRENT_TIMESTAMP");

	if($s){
		return true;
	} else {
		return false;
	}*/
	return true;
}

function closeSession(){
	// no need to close the session
	return true;
}

function readSession($sessionId){
	$mysqli = \Database::Instance()->get();
	$stmt = $mysqli->prepare("SELECT `id`,`data` FROM `sessions` WHERE `id` = ? AND `is_active` = 1");
	$stmt->bind_param("s",$sessionId);
	$stmt->execute();
	$result = $stmt->get_result();

	$update = false;
	if($result->num_rows){
		$row = $result->fetch_assoc();
		$stmt->close();
		$update = true;
	} else {
		$stmt->close();
		return "";
	}

	if($update){
		$stmt = $mysqli->prepare("UPDATE `sessions` SET `lastAccessTime` = CURRENT_TIMESTAMP, `is_active` = 1 WHERE `id` = ?");
		$stmt->bind_param("s",$sessionId);
		$stmt->execute();
		$stmt->close();
	}

	if(isset($row)){
		return $row["data"];
	} else {
		return "";
	}
}

function writeSession($sessionId, $data){
	$mysqli = \Database::Instance()->get();
	$ip = Util::getIP();

	$userAgent = $_SERVER["HTTP_USER_AGENT"];
	
	$stmt = $mysqli->prepare("INSERT INTO `sessions` (`id`,`data`,`lastAccessTime`,`userAgent`,`ip`) VALUES(?,?,CURRENT_TIMESTAMP,?,?) ON DUPLICATE KEY UPDATE `data` = ?, `lastAccessTime` = CURRENT_TIMESTAMP, `userAgent` = ?, `ip` = ?, `is_active` = 1");
	$stmt->bind_param("sssssss",$sessionId,$data,$userAgent,$ip,$data,$userAgent,$ip);
	if($stmt->execute()){
		$stmt->close();
		return true;
	} else {
		$stmt->close();
		return false;
	}
}

function destroySession($sessionId){
	$mysqli = \Database::Instance()->get();

	$stmt = $mysqli->prepare("UPDATE `sessions` SET `is_active` = 0 WHERE `id` = ?");
	$stmt->bind_param("s",$sessionId);
	$stmt->execute();
	$stmt->close();

	setcookie(session_name(),"",time()-3600);

	return true;
}

function garbageSession($lifetime){
	$mysqli = \Database::Instance()->get();

	$stmt = $mysqli->prepare("UPDATE `sessions` SET `is_active` = 0 WHERE `lastAccessTime` < DATE_SUB(NOW(), INVERVAL ? SECOND) AND `is_active` = 0");
	$stmt->bind_param("i",$lifetime);
	$stmt->execute();
	$stmt->close();

	return true;
}

session_set_save_handler("openSession","closeSession","readSession","writeSession","destroySession","garbageSession");
session_start();

//
// -----------------------------------------------------------------------------------------------------------------
// SESSION FUNCTIONS END
// -----------------------------------------------------------------------------------------------------------------
//