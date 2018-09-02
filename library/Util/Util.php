<?php

use Gigadrive\Account\IPInformation;

define("DEVELOPER_MODE",(isset($_SERVER["HTTP_HOST"]) && (explode(":",$_SERVER["HTTP_HOST"])[0] == "localhost" || explode(":",$_SERVER["HTTP_HOST"])[0] == "127.0.0.1")));
define("DEFAULT_TWITTER_IMAGE","https://qpost.gigadrivegroup.com/android-chrome-192x192.png");

define("NAV_HOME","NAV_HOME");
define("NAV_PROFILE","NAV_PROFILE");
define("NAV_NOTIFICATIONS","NAV_NOTIFICATIONS");
define("NAV_MESSAGES","NAV_MESSAGES");
define("NAV_ACCOUNT","NAV_ACCOUNT");

define("ACCOUNT_NAV_HOME","ACCOUNT_NAV_HOME");
define("ACCOUNT_NAV_PRIVACY","ACCOUNT_NAV_PRIVACY");
define("ACCOUNT_NAV_SESSIONS","ACCOUNT_NAV_SESSIONS");
define("ACCOUNT_NAV_CHANGE_PASSWORD","ACCOUNT_NAV_CHANGE_PASSWORD");
define("ACCOUNT_NAV_LOGOUT","ACCOUNT_NAV_LOGOUT");

define("ALERT_TYPE_INFO","info");
define("ALERT_TYPE_WARNING","warning");
define("ALERT_TYPE_DANGER","danger");
define("ALERT_TYPE_SUCCESS","success");
define("ALERT_TYPE_SECONDARY","secondary");
define("ALERT_TYPE_LIGHT","light");
define("ALERT_TYPE_PRIMARY","primary");

define("PROFILE_TAB_FEED","PROFILE_TAB_FEED");
define("PROFILE_TAB_FOLLOWING","PROFILE_TAB_FOLLOWING");
define("PROFILE_TAB_FOLLOWERS","PROFILE_TAB_FOLLOWERS");

define("FEED_ENTRY_TYPE_POST","POST");
define("FEED_ENTRY_TYPE_NEW_FOLLOWING","NEW_FOLLOWING");
define("FEED_ENTRY_SHARE","SHARE");

define("NOTIFICATION_TYPE_NEW_FOLLOWER","NEW_FOLLOWER");
define("NOTIFICATION_TYPE_MENTION","MENTION");
define("NOTIFICATION_TYPE_FAVORITE","FAVORITE");
define("NOTIFICATION_TYPE_SHARE","SHARE");
define("NOTIFICATION_TYPE_REPLY","REPLY");

define("PRIVACY_LEVEL_PUBLIC","PUBLIC");
define("PRIVACY_LEVEL_PRIVATE","PRIVATE");
define("PRIVACY_LEVEL_CLOSED","CLOSED");

if(DEVELOPER_MODE == true){
	error_reporting(E_ALL);
	ini_set("display_errors",1);
	ini_set("display_startup_errors",1);
} else {
	error_reporting(E_ERROR);
}

/**
 * Utility functions
 * 
 * @package Util
 * @author Gigadrive (support@gigadrivegroup.com)
 * @copyright 2016-2018 Gigadrive
 * @link https://gigadrivegroup.com/dev/technologies
 */
class Util {
	public const AD_TYPE_LEADERBOARD = "AD_TYPE_LEADERBOARD";
	public const AD_TYPE_HORIZONTAL = "AD_TYPE_LEADERBOARD";
	public const AD_TYPE_BLOCK = "AD_TYPE_BLOCK";
	public const AD_TYPE_VERTICAL = "AD_TYPE_VERTICAL";

	/**
     * Send an email via SMTP.
	 * 
	 * @access public
     * @param string $to The email address of the recipient
	 * @param string $subject The subject of the email
	 * @param string $contentHTML The content of the email with HTML code
	 * @param string $contentAlt The content of the email without HTML code
	 * @param string $toName The name of the recipient, uses $to when null
	 * @param string $fromName The name of the sender, uses "Gigadrive Group" when null
     * @return bool Returns true if the email was sent successfully
     */
	public static function sendMail($to,$subject,$contentHTML,$contentAlt,$toName = null,$fromName = null){
		$mail = new PHPMailer;
		$mail->CharSet = "UTF-8";
		$mail->Encoding = "base64";
		$mail->isSMTP();
		$mail->SMTPDebug = 0;
		$mail->Debugoutput = "html";
		$mail->Host = MAIL_HOST;
		$mail->SMTPSecure = MAIL_SECURITY;
		$mail->Port = MAIL_PORT;
		$mail->SMTPAuth = true;
		$mail->Username = MAIL_USER;
		$mail->Password = MAIL_PASSWORD;
		$mail->setFrom(MAIL_USER,is_null($fromName) ? "Gigadrive Group" : $fromName);
		$mail->addAddress($to,is_null($toName) ? $to : $toName);
		$mail->Subject = $subject;
		$mail->msgHTML($contentHTML);
		$mail->AltBody = $contentAlt;
		
		if(!$mail->send()){
			return false;
		} else {
			return true;
		}
	}

	/**
	 * Returns the user object for the currently logged in user
	 * 
	 * @access public
	 * @return User
	 */
	public static function getCurrentUser(){
		return self::isLoggedIn() ? User::getUserById($_SESSION["id"]) : null;
	}
	
	/**
     * Returns HTML Code that is converted to "x minutes ago" format via timeago.js
	 * 
	 * @access public
     * @param string $timestamp
     * @return string
     */
	public static function timeago($timestamp){
		$str = strtotime($timestamp);

		$timestamp = date("Y",$str) . "-" . date("m",$str) . "-" . date("d",$str) . "T" . date("H",$str) . ":" . date("i",$str) . ":" . date("s",$str) . "Z";

		return '<time class="timeago" datetime="' . $timestamp . '" title="' . date("d",$str) . "." . date("m",$str) . "." . date("Y",$str) . " " . date("H",$str) . ":" . date("i",$str) . ":" . date("s",$str) . ' UTC">' . $timestamp . '</time>';
	}

	/**
     * Converts ae, oe and ue to their german umlaut equivalents
	 * 
	 * @access public
     * @param string $input
     * @return string
     */
	public static function fixUmlaut($input){
		$a = [
			"ae" => "ä",
			"oe" => "ö",
			"ue" => "ü"
		];

		foreach($a as $b => $c){
			$input = str_replace($b,$c,$input);
		}

		return $input;
	}

	/**
     * Returns HTML code to use in a form for CSRF attack prevention
	 * 
	 * @access public
     * @return string
     */
	public static function insertCSRFToken(){
		if(null !== CSRF_TOKEN){
			return '<input type="hidden" name="csrf_token" value="' . self::sanatizeHTMLAttribute(CSRF_TOKEN) . '"/>';
		}

		return "";
	}

	/**
     * Returns HTML code to use for an advertisment banner
	 * 
	 * @access public
     * @param string $type Use AD_TYPE_* constants
	 * @param bool $center If true, the returned HTML code will be wrapped in a <center> element
	 * @param string[] $classes An array of css classes to be added
     * @return string
     */
	public static function renderAd($type,$center = false,$classes = null){
		if($type == Util::AD_TYPE_LEADERBOARD){
			return ($center == true ? "<center>" : "") . '<div class="' . (!is_null($classes) && is_array($classes) && count($classes) > 0 ? implode(" ",$classes) : "") . '"><script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
			<ins class="adsbygoogle"
				style="display:block"
				data-ad-client="ca-pub-6156128043207415"
				data-ad-slot="1055807482"
				data-ad-format="auto"></ins>
			<script>
			(adsbygoogle = window.adsbygoogle || []).push({});
			</script></div>' . ($center == true ? "</center>" : "");
		} else if($type == Util::AD_TYPE_VERTICAL){
			return ($center == true ? "<center>" : "") . '<div class="' . (!is_null($classes) && is_array($classes) && count($classes) > 0 ? implode(" ",$classes) : "") . '"><script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
			<ins class="adsbygoogle"
				 style="display:inline-block;width:120px;height:600px"
				 data-ad-client="ca-pub-6156128043207415"
				 data-ad-slot="1788401303"></ins>
			<script>
			(adsbygoogle = window.adsbygoogle || []).push({});
			</script></div>' . ($center == true ? "</center>" : "");
		} else if($type == Util::AD_TYPE_BLOCK){
			return ($center == true ? "<center>" : "") . '<div class="' . (!is_null($classes) && is_array($classes) && count($classes) > 0 ? implode(" ",$classes) : "") . '"><script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
			<ins class="adsbygoogle"
				style="display:inline-block;width:336px;height:280px"
				data-ad-client="ca-pub-6156128043207415"
				data-ad-slot="7069637483"></ins>
			<script>
			(adsbygoogle = window.adsbygoogle || []).push({});
			</script></div>' . ($center == true ? "</center>" : "");
		}
	}

	/**
     * Returns HTML code for a bootstrap alert
	 * 
	 * @access public
     * @param string $id A string that identifies this alert, mainly used for saving dismissal
	 * @param string $text The text displayed in this alert (may contain HTML)
	 * @param string $type Use ALERT_TYPE_* constants
	 * @param bool $dismissible If true, an icon will be displayed to dismiss this alert
	 * @param bool $saveDismiss If true, a cookie will be set on dismissal and this alert won't be shown again
     * @return string HTML code for the alert
     */
	public static function createAlert($id,$text,$type = ALERT_TYPE_INFO,$dismissible = FALSE,$saveDismiss = FALSE){
		$cookieName = "registeredAlert" . $id;

		if($dismissible == false){
			return '<div id="registeredalert' . $id . '" class="alert alert-' . $type . '">' . $text . '</div>';
		} else if($saveDismiss == false || ($saveDismiss == true && !isset($_COOKIE[$cookieName]))){
			$d = $saveDismiss == true ? ' onClick="saveDismiss(\'' . $id . '\');"' : "";
			return '<div id="registeredalert' . $id . '" class="text-left alert alert-dismissible alert-' . $type . '"><button id="registeredalertclose' . $id . '" type="button" class="close" data-dismiss="alert"' . $d . '>&times;</button>' . $text . '</div>';
		}
	}
	
	/**
     * Modifies the user's cookies to toggle nightmode on or off
	 * 
	 * @access public
     */
	public static function toggleNightMode(){
		if(Util::isUsingNightMode()){
			self::unsetCookie("nightMode");
		} else {
			self::setCookie("nightMode","yes",180);
		}
	}
	
	/**
     * Gets whether the user is currently using the nightmode
	 * 
	 * @access public
	 * @return bool
     */
	public static function isUsingNightMode(){
		if(isset($_COOKIE["nightMode"])){
			if($_COOKIE["nightMode"] == "yes"){
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	/**
	 * Sets a cookie for a specific amount of days to the user's browser
	 * 
	 * @access public
	 * @param string $name The cookie name
	 * @param string $value The cookie value
	 * @param int $days The time in days the cookie will last for
	 */
	public static function setCookie($name,$value,$days){
		setcookie($name,$value,time()+(60*60*24)*$days);
	}

	/**
	 * Removes a cookie from the user's browser
	 * 
	 * @access public
	 * @param string $name The cookie name
	 */
	public static function unsetCookie($name){
		setcookie($name,"",time()-3600);
	}
	
	/**
     * Gets an array of country code -> country name equivalents
	 * 
	 * @access public
     * @return array
     */
	public static function getCountryCodeArray(){
		return array(
			'AF' => 'Afghanistan',
			'AX' => 'Aland Islands',
			'AL' => 'Albania',
			'DZ' => 'Algeria',
			'AS' => 'American Samoa',
			'AD' => 'Andorra',
			'AO' => 'Angola',
			'AI' => 'Anguilla',
			'AQ' => 'Antarctica',
			'AG' => 'Antigua And Barbuda',
			'AR' => 'Argentina',
			'AM' => 'Armenia',
			'AW' => 'Aruba',
			'AU' => 'Australia',
			'AT' => 'Austria',
			'AZ' => 'Azerbaijan',
			'BS' => 'Bahamas',
			'BH' => 'Bahrain',
			'BD' => 'Bangladesh',
			'BB' => 'Barbados',
			'BY' => 'Belarus',
			'BE' => 'Belgium',
			'BZ' => 'Belize',
			'BJ' => 'Benin',
			'BM' => 'Bermuda',
			'BT' => 'Bhutan',
			'BO' => 'Bolivia',
			'BA' => 'Bosnia And Herzegovina',
			'BW' => 'Botswana',
			'BV' => 'Bouvet Island',
			'BR' => 'Brazil',
			'IO' => 'British Indian Ocean Territory',
			'BN' => 'Brunei Darussalam',
			'BG' => 'Bulgaria',
			'BF' => 'Burkina Faso',
			'BI' => 'Burundi',
			'KH' => 'Cambodia',
			'CM' => 'Cameroon',
			'CA' => 'Canada',
			'CV' => 'Cape Verde',
			'KY' => 'Cayman Islands',
			'CF' => 'Central African Republic',
			'TD' => 'Chad',
			'CL' => 'Chile',
			'CN' => 'China',
			'CX' => 'Christmas Island',
			'CC' => 'Cocos (Keeling) Islands',
			'CO' => 'Colombia',
			'KM' => 'Comoros',
			'CG' => 'Congo',
			'CD' => 'Congo, Democratic Republic',
			'CK' => 'Cook Islands',
			'CR' => 'Costa Rica',
			'CI' => 'Cote D\'Ivoire',
			'HR' => 'Croatia',
			'CU' => 'Cuba',
			'CY' => 'Cyprus',
			'CZ' => 'Czech Republic',
			'DK' => 'Denmark',
			'DJ' => 'Djibouti',
			'DM' => 'Dominica',
			'DO' => 'Dominican Republic',
			'EC' => 'Ecuador',
			'EG' => 'Egypt',
			'SV' => 'El Salvador',
			'GQ' => 'Equatorial Guinea',
			'ER' => 'Eritrea',
			'EE' => 'Estonia',
			'ET' => 'Ethiopia',
			'FK' => 'Falkland Islands (Malvinas)',
			'FO' => 'Faroe Islands',
			'FJ' => 'Fiji',
			'FI' => 'Finland',
			'FR' => 'France',
			'GF' => 'French Guiana',
			'PF' => 'French Polynesia',
			'TF' => 'French Southern Territories',
			'GA' => 'Gabon',
			'GM' => 'Gambia',
			'GE' => 'Georgia',
			'DE' => 'Germany',
			'GH' => 'Ghana',
			'GI' => 'Gibraltar',
			'GR' => 'Greece',
			'GL' => 'Greenland',
			'GD' => 'Grenada',
			'GP' => 'Guadeloupe',
			'GU' => 'Guam',
			'GT' => 'Guatemala',
			'GG' => 'Guernsey',
			'GN' => 'Guinea',
			'GW' => 'Guinea-Bissau',
			'GY' => 'Guyana',
			'HT' => 'Haiti',
			'HM' => 'Heard Island & Mcdonald Islands',
			'VA' => 'Holy See (Vatican City State)',
			'HN' => 'Honduras',
			'HK' => 'Hong Kong',
			'HU' => 'Hungary',
			'IS' => 'Iceland',
			'IN' => 'India',
			'ID' => 'Indonesia',
			'IR' => 'Iran, Islamic Republic Of',
			'IQ' => 'Iraq',
			'IE' => 'Ireland',
			'IM' => 'Isle Of Man',
			'IL' => 'Israel',
			'IT' => 'Italy',
			'JM' => 'Jamaica',
			'JP' => 'Japan',
			'JE' => 'Jersey',
			'JO' => 'Jordan',
			'KZ' => 'Kazakhstan',
			'KE' => 'Kenya',
			'KI' => 'Kiribati',
			'KR' => 'Korea',
			'KW' => 'Kuwait',
			'KG' => 'Kyrgyzstan',
			'LA' => 'Lao People\'s Democratic Republic',
			'LV' => 'Latvia',
			'LB' => 'Lebanon',
			'LS' => 'Lesotho',
			'LR' => 'Liberia',
			'LY' => 'Libyan Arab Jamahiriya',
			'LI' => 'Liechtenstein',
			'LT' => 'Lithuania',
			'LU' => 'Luxembourg',
			'MO' => 'Macao',
			'MK' => 'Macedonia',
			'MG' => 'Madagascar',
			'MW' => 'Malawi',
			'MY' => 'Malaysia',
			'MV' => 'Maldives',
			'ML' => 'Mali',
			'MT' => 'Malta',
			'MH' => 'Marshall Islands',
			'MQ' => 'Martinique',
			'MR' => 'Mauritania',
			'MU' => 'Mauritius',
			'YT' => 'Mayotte',
			'MX' => 'Mexico',
			'FM' => 'Micronesia, Federated States Of',
			'MD' => 'Moldova',
			'MC' => 'Monaco',
			'MN' => 'Mongolia',
			'ME' => 'Montenegro',
			'MS' => 'Montserrat',
			'MA' => 'Morocco',
			'MZ' => 'Mozambique',
			'MM' => 'Myanmar',
			'NA' => 'Namibia',
			'NR' => 'Nauru',
			'NP' => 'Nepal',
			'NL' => 'Netherlands',
			'AN' => 'Netherlands Antilles',
			'NC' => 'New Caledonia',
			'NZ' => 'New Zealand',
			'NI' => 'Nicaragua',
			'NE' => 'Niger',
			'NG' => 'Nigeria',
			'NU' => 'Niue',
			'NF' => 'Norfolk Island',
			'MP' => 'Northern Mariana Islands',
			'NO' => 'Norway',
			'OM' => 'Oman',
			'PK' => 'Pakistan',
			'PW' => 'Palau',
			'PS' => 'Palestinian Territory, Occupied',
			'PA' => 'Panama',
			'PG' => 'Papua New Guinea',
			'PY' => 'Paraguay',
			'PE' => 'Peru',
			'PH' => 'Philippines',
			'PN' => 'Pitcairn',
			'PL' => 'Poland',
			'PT' => 'Portugal',
			'PR' => 'Puerto Rico',
			'QA' => 'Qatar',
			'RE' => 'Reunion',
			'RO' => 'Romania',
			'RU' => 'Russian Federation',
			'RW' => 'Rwanda',
			'BL' => 'Saint Barthelemy',
			'SH' => 'Saint Helena',
			'KN' => 'Saint Kitts And Nevis',
			'LC' => 'Saint Lucia',
			'MF' => 'Saint Martin',
			'PM' => 'Saint Pierre And Miquelon',
			'VC' => 'Saint Vincent And Grenadines',
			'WS' => 'Samoa',
			'SM' => 'San Marino',
			'ST' => 'Sao Tome And Principe',
			'SA' => 'Saudi Arabia',
			'SN' => 'Senegal',
			'RS' => 'Serbia',
			'SC' => 'Seychelles',
			'SL' => 'Sierra Leone',
			'SG' => 'Singapore',
			'SK' => 'Slovakia',
			'SI' => 'Slovenia',
			'SB' => 'Solomon Islands',
			'SO' => 'Somalia',
			'ZA' => 'South Africa',
			'GS' => 'South Georgia And Sandwich Isl.',
			'ES' => 'Spain',
			'LK' => 'Sri Lanka',
			'SD' => 'Sudan',
			'SR' => 'Suriname',
			'SJ' => 'Svalbard And Jan Mayen',
			'SZ' => 'Swaziland',
			'SE' => 'Sweden',
			'CH' => 'Switzerland',
			'SY' => 'Syrian Arab Republic',
			'TW' => 'Taiwan',
			'TJ' => 'Tajikistan',
			'TZ' => 'Tanzania',
			'TH' => 'Thailand',
			'TL' => 'Timor-Leste',
			'TG' => 'Togo',
			'TK' => 'Tokelau',
			'TO' => 'Tonga',
			'TT' => 'Trinidad And Tobago',
			'TN' => 'Tunisia',
			'TR' => 'Turkey',
			'TM' => 'Turkmenistan',
			'TC' => 'Turks And Caicos Islands',
			'TV' => 'Tuvalu',
			'UG' => 'Uganda',
			'UA' => 'Ukraine',
			'AE' => 'United Arab Emirates',
			'GB' => 'United Kingdom',
			'US' => 'United States',
			'UM' => 'United States Outlying Islands',
			'UY' => 'Uruguay',
			'UZ' => 'Uzbekistan',
			'VU' => 'Vanuatu',
			'VE' => 'Venezuela',
			'VN' => 'Viet Nam',
			'VG' => 'Virgin Islands, British',
			'VI' => 'Virgin Islands, U.S.',
			'WF' => 'Wallis And Futuna',
			'EH' => 'Western Sahara',
			'YE' => 'Yemen',
			'ZM' => 'Zambia',
			'ZW' => 'Zimbabwe',
		);
	}
	
	/**
     * Returns a random string of characters
	 * 
	 * @access public
     * @param int $length The maximum length of the string (the actual length will be something between this number and the half of it)
     * @return array
     */
	public static function getRandomString($length = 16) {
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$charactersLength = strlen($characters);
		$randomString = '';
		for ($i = 0; $i < rand($length/2,$length); $i++) {
			$randomString .= $characters[rand(0, $charactersLength - 1)];
		}
		return $randomString;
	}
	
	/**
     * Generates a UUID
	 * 
	 * @access public
     * @return string
     */
	public static function gen_uuid() {
		return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
			mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),
			mt_rand( 0, 0xffff ),
			mt_rand( 0, 0x0fff ) | 0x4000,
			mt_rand( 0, 0x3fff ) | 0x8000,
			mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
		);
	}

	/**
     * Gets whether the user is currently logged in.
	 * 
	 * @access public
     * @return bool
     */
	public static function isLoggedIn(){
		if(isset($_SESSION["id"]) && !is_null($_SESSION["id"]) && !Util::isEmpty($_SESSION["id"])){
			if(!is_null(User::getUserById($_SESSION["id"]))){
				return true;
			} else {
				unset($_SESSION["id"]);
				return false;
			}
		} else {
			return false;
		}
	}

	/**
	 * Returns whether an email is available
	 * 
	 * @access public
	 * @param string $email
	 * @return bool
	 */
	public static function isEmailAvailable($email){
		$b = true;

		$mysqli = Database::Instance()->get();

		$stmt = $mysqli->prepare("SELECT COUNT(`id`) AS `count` FROM `users` WHERE `email` = ?");
		$stmt->bind_param("s",$email);
		if($stmt->execute()){
			$result = $stmt->get_result();

			if($result->num_rows){
				$row = $result->fetch_assoc();

				if($row["count"] > 0){
					$b = false;
				} else {
					$b = true;
				}
			}
		}
		$stmt->close();

		return $b;
	}

	/**
	 * Returns whether an username is available
	 * 
	 * @access public
	 * @param string $username
	 * @param Lime\App $app
	 * @return bool
	 */
	public static function isUsernameAvailable($username,$app = null){
		$b = true;

		$lowerName = strtolower($username);

		if(!is_null($app)){
			foreach($app->routes as $route => $closure){
				$lowerRoute = strtolower($route);

				if($lowerRoute == $lowerName) return false;
				if(strlen($lowerRoute) > 1 && substr(1,strlen($lowerRoute)) == $lowerName) return false;
				if(str_replace("/","",str_replace(":","",$lowerRoute)) == $lowerName) return false;
			}
		}

		$mysqli = Database::Instance()->get();

		$stmt = $mysqli->prepare("SELECT COUNT(`id`) AS `count` FROM `users` WHERE `username` = ?");
		$stmt->bind_param("s",$username);
		if($stmt->execute()){
			$result = $stmt->get_result();

			if($result->num_rows){
				$row = $result->fetch_assoc();

				if($row["count"] > 0){
					$b = false;
				} else {
					$b = true;
				}
			}
		}
		$stmt->close();

		return $b;
	}

	/**
     * Gets the current user's IP address
	 * 
	 * @access public
     * @return string The IP address (might be an IPv6 address)
     */
	public static function getIP(){
		$ip = "undefined";
		
		if (isset($_SERVER['HTTP_CLIENT_IP']) && !Util::isEmpty($_SERVER['HTTP_CLIENT_IP'])) {
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		} elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && !Util::isEmpty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} else if(isset($_SERVER['REMOTE_ADDR'])) {
			$ip = $_SERVER['REMOTE_ADDR'];
		}
		
		return $ip;
	}

	/**
     * Removes outdated files and folders from the tmp folder (this check is being done automatically, there is no need to call this function!)
	 * 
	 * @access public
     */
	public static function cleanupTempFolder(){
		$files = glob($_SERVER["DOCUMENT_ROOT"] . "/tmp/*");
		$now = time();

		foreach($files as $file){
			if(is_file($file) && basename($file) != ".keep"){
				if($now - filemtime($file) >= 60*60*24){
					unlink($file);
				}
			}
		}
	}

	/**
	 * Gets whether a string starts with another
	 * 
	 * @access public
	 * @param string $string The string in subject
	 * @param string $start The string to be checked whether it is the start of $string
	 * @param bool $ignoreCase If true, the case of the strings won't affect the result
	 * @return bool
	 */
	public static function startsWith($string,$start,$ignoreCase = false){
		if(strlen($start) <= strlen($string)){
			if($ignoreCase == true){
				return substr($string,0,strlen($start)) == $start;
			} else {
				return strtolower(substr($string,0,strlen($start))) == strtolower($start);
			}
		} else {
			return false;
		}
	}

	/**
     * Stores a file on the Gigadrive CDN to use in the future on a specified path and returns the full final URL
	 * 
	 * @access public
     * @param string $path The path the file should have on the CDN
	 * @param string $file The path of the file to be uploaded
     * @return string
     */
	public static function storeFileOnCDN($path,$file){
		try {
			$curl = curl_init();
			curl_setopt_array($curl,array(
				CURLOPT_URL => GIGADRIVE_CDN_UPLOAD_SCRIPT,
				CURLOPT_POST => 1,
				CURLOPT_POSTFIELDS => array(
					"path" => $path,
					"secret" => GIGADRIVE_CDN_UPLOAD_SCRIPT_SECRET,
					"file_contents" => curl_file_create(realpath($file))
				),
				CURLOPT_RETURNTRANSFER => 1
			));
	
			$result = curl_exec($curl);

			if($result === false){
				throw new Exception(curl_error($curl),curl_errno($curl));
			}

			curl_close($curl);
	
			if(Util::contains($result,"ERROR: ") == false){
				return ["result" => $result, "url" => sprintf(GIGADRIVE_CDN_UPLOAD_FINAL_URL,$path . $result)];
			} else {
				return ["error" => $result];
			}
		} catch(Exception $e){
			return ["error" => $e->getMessage()];
		}
	}

	/**
     * Checks whether a string contains another string
	 * 
	 * @access public
     * @param string $string The full string
	 * @param string $check The substring to be checked
	 * @return bool
     */
	public static function contains($string,$check){
		return strpos($string,$check) !== false;
	}

	/**
	 * Returns wheter a string or array is empty
	 * 
	 * @access public
	 * @param string|array $var
	 * @return bool
	 */
	public static function isEmpty($var){
		if(is_array($var)){
			return count($var) == 0;
		} else if(is_string($var)){
			return $var == "" || trim($var) == "" || str_replace(" ","",str_replace(" ","",$var)) == "" || strlen($var) == 0;
		} else {
			return is_null($var) || empty($var);
		}
	}

	/**
     * Limits a string to a specific length and adds "..." to the end if needed
	 * 
	 * @access public
     * @param string $string
	 * @param int $length
	 * @param bool $addDots
     * @return string
     */
	public static function limitString($string,$length,$addDots = false){
		if(strlen($string) > $length)
			$string = substr($string,0,($addDots ? $length-3 : $length)) . ($addDots ? "..." : "");

		return $string;
	}

	/**
	 * Returns HTML code for a verified badge
	 * 
	 * @access public
	 * @param int $size The size of the badge in px (possible values are 16, 20, 24, 32, 48, 64, 128, 256 and 512)
	 * @return string
	 */
	public static function getVerifiedBadge($size = 16){
		return '<img src="/assets/img/verified/' . $size . '.png"/>';
	}

	/**
	 * Returns a sanatized string that avoids prepending or traling spaces and XSS attacks
	 * 
	 * @access public
	 * @param string $string The string to sanatize
	 * @return string
	 */
	public static function sanatizeString($string){
		return trim(htmlentities($string));
	}

	/**
	 * Opposite of sanatizeString()
	 * 
	 * @access public
	 * @param string $string
	 * @return string
	 */
	public static function desanatizeString($string){
		return html_entity_decode($string);
	}

	/**
	 * Returns a sanatzied string to use in HTML attributes (avoids problems with quotations)
	 * 
	 * @access public
	 * @param string $string The string to sanatize
	 * @return string
	 */
	public static function sanatizeHTMLAttribute($string){
		return trim(htmlspecialchars($string, ENT_QUOTES | ENT_HTML5, "UTF-8"));
	}

	/**
	 * Returns whether a string is valid json
	 * 
	 * @access public
	 * @return bool
	 */
	public static function isValidJSON($string){
		if(Util::isEmpty($string)) return false;
		if(!self::startsWith($string,"{") && !self::startsWith($string,"[")) return false;

		@json_decode($string);
		return (json_last_error() == JSON_ERROR_NONE);
	}

	/**
	 * Converts \n characters to <br/>
	 * 
	 * @access public
	 * @param string $string
	 * @return string
	 */
	public static function convertLineBreaksToHTML($string){
		return preg_replace("/(<br {0,}\/{0,1}>(\\r|\\n){0,}){2,}/","<br class=\"reduced\" />",nl2br($string));
		/*$s = trim(str_replace("\n","<br/>",$string));

		// TODO: Fix line breaking spamming
		while(self::contains($s,"<br/><br/>")){
			$s = str_replace("<br/><br/>","<br/>",$s);
		}

		return $s;*/
	}

	/**
	 * Returns code for a paginator
	 * 
	 * @access public
	 * @param int $page
	 * @param int $itemsPerPage
	 * @param int $total
	 * @param string $urlPattern
	 * @return JasonGrimes\Paginator
	 */
	public static function paginate($page,$itemsPerPage,$total,$urlPattern){
		$paginator = new JasonGrimes\Paginator($total,$itemsPerPage,$page,$urlPattern);

		$p = "";

		if ($paginator->getNumPages() > 1){
			
			$p .= '<nav aria-label="Page navigation example"><ul class="pagination justify-content-center mt-3">';

			if ($paginator->getPrevUrl())
				$p .= '<li class="page-item"><a class="page-link" href="' . $paginator->getPrevUrl() . '">&laquo; Previous</a></li>';

			foreach ($paginator->getPages() as $page){
				if($page["url"]){
					$p .= '<li class="page-item' . ($page["isCurrent"] ? " active" : "") . '"><a class="page-link" href="' . $page["url"] . '">' . $page["num"] . '</a></li>';
				} else {
					$p .= '<li class="page-item disabled"><a class="page-link" href="#">' . $page["num"] . '</a></li>';
				}
			}

			if ($paginator->getNextUrl())
				$p .= '<li class="page-item"><a class="page-link" href="' . $paginator->getNextUrl() . '">Next &raquo;</a></li>';

			$p .= '</nav>';

		}
	
		return $p;
	}

	/**
	 * Returns the index of a variable in an array
	 * 
	 * @access public
	 * @param array $array
	 * @param mixed $var
	 * @return int Returns -1 if the variable could not be found in the array
	 */
	public static function indexOf($array,$var){
		if(count($array) > 0 && in_array($var,$array)){
			for($i = 0; $i < count($array); $i++) { 
				if(isset($array[$i]) && $array[$i] == $var){
					return $i;
				}
			}
		}

		return -1;
	}

	/**
	 * Removes an entry from an array and returns the new array
	 * 
	 * @access public
	 * @param array $array
	 * @param mixed $var
	 * @return array
	 */
	public static function removeFromArray($array,$var){
		$i = self::indexOf($array,$var);

		while($i != -1){
			unset($array[$i]);

			$i = self::indexOf($array,$var);
		}

		return $array;
	}

	/**
	 * Returns HTML code for a follow button
	 * 
	 * @access public
	 * @param int|User $user The user to follow
	 * @param bool $defaultToEdit If true, an "Edit Profile" button will be returned if $user is the currently logged in user
	 * @param array $classes The CSS classes to be added to the button
	 * @param bool $showBlocked If true, a "Blocked" button will be returned if the $user is blocked by the currently logged in user
	 */
	public static function followButton($user,$defaultToEdit = false,$classes = null,$showBlocked = true){
		if(is_object($user))
			$user = $user->getId();

		$classString = !is_null($classes) && is_array($classes) && count($classes) > 0 ? " " . implode(" ",$classes) : "";

		if(self::isLoggedIn()){
			$currentUser = Util::getCurrentUser();

			if($currentUser->hasBlocked($user)){
				if($showBlocked){
					return '<button type="button" class="btn btn-danger' . $classString . '">Blocked</button>';
				}
			} else {
				if($defaultToEdit && $currentUser->getId() == $user){
					return '<a href="/edit" class="btn btn-light' . $classString . '">Edit profile</a>';
				} else if($currentUser->getId() != $user){
					if(User::getUserById($user)->getPrivacyLevel() == "PUBLIC"){
						if($currentUser->isFollowing($user)){
							return '<button type="button" class="unfollowButton btn btn-danger' . $classString . '" data-user-id="' . $user . '" onclick="toggleFollow(this,' . $user . ');">Unfollow</button>';
						} else {
							return '<button type="button" class="followButton btn btn-primary' . $classString . '" data-user-id="' . $user . '" onclick="toggleFollow(this,' . $user . ');">Follow</button>';
						}
					} else if(User::getUserById($user)->getPrivacyLevel() == "PRIVATE"){
						if($currentUser->isFollowing($user)){
							return '<button type="button" class="unfollowButton btn btn-danger' . $classString . '" data-user-id="' . $user . '" onclick="toggleFollow(this,' . $user . ');">Unfollow</button>';
						} else {
							if($currentUser->hasSentFollowRequest($user)){
								return '<button type="button" class="pendingButton btn btn-warning' . $classString . '" data-user-id="' . $user . '" onclick="toggleFollow(this,' . $user . ');">Pending</button>';
							} else {
								return '<button type="button" class="followButton btn btn-primary' . $classString . '" data-user-id="' . $user . '" onclick="toggleFollow(this,' . $user . ');">Follow</button>';
							}
						}
					} else if(User::getUserById($user)->getPrivacyLevel() == "CLOSED"){
						return "";
					}
				}
			}
		}
	}

	/**
	 * Returns a link that shows a warning for $link, returns $link if the host of the link is equal to HTTP_HOST
	 * 
	 * @access public
	 * @param string $link
	 * @return string
	 */
	public static function linkWarning($link){
		if(isset($_SERVER["HTTP_HOST"]) && $_SERVER["HTTP_HOST"] != parse_url($link,PHP_URL_HOST)){
			return "/out?link=" . urlencode($link);
		} else {
			return $link;
		}
	}

	/**
	 * Returns an array of data used in a JSON API for a post
	 * 
	 * @access public
	 * @param int $postId
	 * @param int $parentDepth
	 * @param int $parentMaxContentWidth
	 * @param bool $small
	 * @return array
	 */
	public static function postJsonData($postId,$parentDepth = 0,$maxContentWidth = 658,$parentMaxContentWidth = 658,$small = false){
		if(is_object($postId)) $postId = $postId->getId();
		$post = !is_null($postId) ? FeedEntry::getEntryById($postId) : null;
		if(!is_null($post)){
			$attachments = [];

			foreach($post->getAttachments() as $attachmentId)
				array_push($attachments,self::mediaJsonData($attachmentId,$postId));

			return [
				"id" => $post->getId(),
				"user" => self::userJsonData($post->getUser()),
				"text" => Util::convertPost($post->getText()),
				"textUnfiltered" => Util::sanatizeString($post->getText()),
				"time" => Util::timeago($post->getTime()),
				"shares" => $post->getShares(),
				"favorites" => $post->getFavorites(),
				"attachments" => $attachments,
				"postActionButtons" => self::getPostActionButtons($post),
				"listHtml" => $post->toListHTML($small,$maxContentWidth),
				"attachmentHtml" => Util::renderAttachmentEmbeds($post->getAttachmentObjects(),$postId),
				"parent" => ($parentDepth <= MAX_PARENT_DEPTH && !is_null($post->getPostId()) ? self::postJsonData($post->getPostId(),$parentDepth+1,$parentMaxContentWidth,$parentMaxContentWidth,true) : null)
			];
		} else {
			return null;
		}
	}

	/**
	 * Returns an array of data used in a JSON API for a media file
	 * 
	 * @access public
	 * @param string $mediaId
	 * @param int $postId
	 * @return array
	 */
	public static function mediaJsonData($mediaId,$postId = null){
		if(is_object($mediaId)) $mediaId = $mediaId->getId();
		$mediaFile = !is_null($mediaId) ? MediaFile::getMediaFileFromID($mediaId) : null;
		if(!is_null($mediaFile)){
			return [
				"id" => $mediaFile->getId(),
				"sha256" => $mediaFile->getSHA256(),
				"fileUrl" => $mediaFile->getURL()
			];
		} else {
			return null;
		}
	}

	/**
	 * Returns an array of data used in a JSON API for a user
	 * 
	 * @access public
	 * @param int $userId
	 * @return array
	 */
	public static function userJsonData($userId){
		if(is_object($userId)) $userId = $userId->getId();
		$user = !is_null($userId) ? User::getUserById($userId) : null;
		if(!is_null($user)){
			return [
				"id" => $user->getId(),
				"displayName" => $user->getDisplayName(),
				"username" => $user->getUsername(),
				"avatar" => $user->getAvatarURL(),
				"bio" => $user->getBio()
			];
		} else {
			return null;
		}
	}

	/**
	 * Converts links in a string to HTML links
	 * 
	 * @access public
	 * @return string
	 */
	public static function convertLinks($string){
		return preg_replace("!(\s|^)((https?://|www\.)+[a-z0-9_./?=&-]+)!i", " <a href=\"$2\" class=\"filterLink ignoreParentClick\">$2</a> ",$string);
	}

	/**
	 * Converts hashtags in a string to HTML links
	 * 
	 * @access public
	 * @return string
	 */
	public static function convertHashtags($string){
		return str_replace("/#","/", preg_replace("/(?:^|\s)#(\w+)/", " <a href=\"/search?query=" . urlencode("#") . "$1\" class=\"ignoreParentClick\">#$1</a>", $string));
	}

	/**
	 * Converts mentions in a string to HTML links
	 * 
	 * @access public
	 * @return string
	 */
	public static function convertMentions($string){
		//return preg_replace("/@(\w+)/i", "<a href=\"/$1\" class=\"ignoreParentClick\">$0</a>", $string);

		$mentions = self::getUsersMentioned($string);

		foreach($mentions as $u){
			$string = str_ireplace("@" . $u->getUsername(),'<a href="/' . $u->getUsername() . '" class="ignoreParentClick" data-no-instant>@' . $u->getUsername() . '</a>',$string);
		}

		return $string;
	}

	/**
	 * Converts URLs, hashtags, mentions and line breaks in a post text to HTML links
	 * 
	 * @access public
	 * @return string
	 */
	public static function convertPost($string){
		return trim(self::convertLinks(self::convertHashtags(self::convertMentions(self::convertLineBreaksToHTML(self::sanatizeString($string))))));
	}

	/**
	 * Returns an array of users mentioned in a post
	 * 
	 * @access public
	 * @param string $string
	 * @return User[]
	 */
	public static function getUsersMentioned($string){
		$a = [];
		$ids = [];

		foreach(explode(" ",$string) as $s){
			if(self::startsWith($s,"@") && strlen($s) >= 2){
				$name = substr($s,1,strlen($s));

				$u = User::getUserByUsername($name);
				if(!is_null($u)){
					if(!in_array($u->getId(),$ids)){
						array_push($a,$u);
						array_push($ids,$u->getId());
					}
				}
			}
		}

		return $a;
	}

	/**
	 * Returns a string that fixes exploits like a zero-width space
	 * 
	 * @access public
	 * @param string $string
	 * @return string
	 */
	public static function fixString($string){
		return str_replace("\xE2\x80\x8B","",str_replace("\xE2\x80\xAE","",$string));
	}

	/**
	 * Returns a video URL stripped off of it's unneeded parameters and info
	 * 
	 * @access public
	 * @param string $url
	 * @return string
	 */
	public static function stripUnneededInfoFromVideoURL($url){
		$mediaEmbed = new MediaEmbed\MediaEmbed();

		$mediaObject = $mediaEmbed->parseUrl($url);
		if($mediaObject){
			if(isset($mediaObject->stub()["match"])){
				$match = $mediaObject->stub()["match"];

				if(isset($match[0])){
					return $match[0];
				} else {
					return $url;
				}
			} else {
				return $url;
			}
		} else {
			return $url;
		}
	}

	/**
	 * Returns HTML code to use for embedding a video
	 * 
	 * @access public
	 * @param string $url
	 * @return string
	 */
	public static function getVideoEmbedCodeFromURL($url){
		$mediaEmbed = new MediaEmbed\MediaEmbed();

		$mediaObject = $mediaEmbed->parseUrl($url);
		if($mediaObject){
			$mediaObject->setAttribute("class","embed-responsive-item");

			return sprintf('<div class="embed-responsive embed-responsive-16by9">%s</div>',$mediaObject->getEmbedCode());
		} else {
			return null;
		}

		/*$match = [];

		if(preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $url, $match)){
			// IS YOUTUBE URL
			// https://gist.github.com/ghalusa/6c7f3a00fd2383e5ef33

			$videoID = $match[1];

			return '<div class="embed-responsive embed-responsive-16by9"><iframe width="560" height="315" src="https://www.youtube.com/embed/' . $videoID . '" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe></div>';
		} else if(preg_match('%^https?:\/\/(?:www\.|player\.)?vimeo.com\/(?:channels\/(?:\w+\/)?|groups\/([^\/]*)\/videos\/|album\/(\d+)\/video\/|video\/|)(\d+)(?:$|\/|\?)(?:[?]?.*)$%im', $url, $match)){
			// IS VIMEO URL
			// https://gist.github.com/anjan011/1fcecdc236594e6d700f

			$videoID = $match[3];

			return '<div class="embed-responsive embed-responsive-16by9"><iframe src="https://player.vimeo.com/video/' . $videoID . '" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe></div>';
		} else if(preg_match('!^.+dailymotion\.com/(video|hub)/([^_]+)[^#]*(#video=([^_&]+))?|(dai\.ly/([^_]+))!', $url, $match)){
			// IS DAILYMOTION URL
			// https://stackoverflow.com/a/32831355

			$videoID = isset($match[6]) ? $match[6] : isset($match[4]) ? $match[4] : isset($match[2]) ? $match[2] : null;

			return !is_null($videoID) ? '<div class="embed-responsive embed-responsive-16by9"><iframe frameborder="0" src="//www.dailymotion.com/embed/video/' . $videoID . '" allowfullscreen allow="autoplay"></iframe></div>' : null;
		}*/
	}

	/**
	 * Returns whether or not a string is a valid, embeddable video URL
	 * 
	 * @access public
	 * @param string $url
	 * @return bool
	 */
	public static function isValidVideoURL($url){
		return filter_var($url,FILTER_VALIDATE_URL) && !is_null(self::getVideoEmbedCodeFromURL($url));
	}

	/**<div class="col-md-4 px-1 py-1">
	 * Returns HTML for a post's postActionButtons
	 * 
	 * @access public
	 * @param int|FeedEntry $post
	 * @return string
	 */
	public static function getPostActionButtons($post){
		if(!is_object($post))
			$post = FeedEntry::getEntryById($post);
		
		$postActionButtons = "";

		if(Util::isLoggedIn()){
			$currentUser = Util::getCurrentUser();
			if(is_null($currentUser)) return "";

			$postActionButtons .= '<div class="mt-1 postActionButtons ignoreParentClick float-left">';
			$postActionButtons .= '<span class="replyButton" data-toggle="tooltip" title="Reply" data-reply-id="' . $post->getId() . '">';
			$postActionButtons .= '<i class="fas fa-share"></i>';
			$postActionButtons .= '</span><span class="replyCount small text-primary mx-2">';
			$postActionButtons .= $post->getReplies();
			$postActionButtons .= '</span>';
			$postActionButtons .= '<span' . ($currentUser->getId() != $post->getUser()->getId() && $post->getUser()->getPrivacyLevel() == "PUBLIC" ? ' class="shareButton" data-toggle="tooltip" title="Share"' : ' data-toggle="tooltip" title="You can not share this post" style="opacity: 0.3"') . ' data-post-id="' . $post->getId() . '">';
			$postActionButtons .= '<i class="fas fa-share-alt' . ($currentUser->hasShared($post->getId()) ? ' text-primary' : "")  . '"' . ($currentUser->hasShared($post->getId()) ? "" : ' style="color: gray"') . '></i>';
			$postActionButtons .= '</span>';

			$postActionButtons .= '<span class="shareCount small text-primary ml-2 mr-2">';
			$postActionButtons .= $post->getShares();
			$postActionButtons .= '</span>';

			$postActionButtons .= '<span class="favoriteButton" data-post-id="' . $post->getId() . '">';
			$postActionButtons .= '<i class="fas fa-star"' . ($currentUser->hasFavorited($post->getId()) ? ' style="color: gold"' : ' style="color: gray"') . '></i>';
			$postActionButtons .= '</span>';

			$postActionButtons .= '<span class="favoriteCount small ml-2 mr-4" style="color: #ff960c">';
			$postActionButtons .= $post->getFavorites();
			$postActionButtons .= '</span>';

			if($currentUser->getId() == $post->getUserId()){
				$postActionButtons .= '<span class="deleteButton ml-2" data-post-id="' . $post->getId() . '" data-toggle="tooltip" title="Delete">';
				$postActionButtons .= '<i class="fas fa-trash-alt"></i>';
				$postActionButtons .= '</span>';
			}

			$postActionButtons .= '</div>';
		}

		return $postActionButtons;
	}

	/**
	 * Returns html code of embeds for media files
	 * 
	 * @access public
	 * @param MediaFile[]|MediaFile $mediaFiles
	 * @param int $postId
	 * @return string
	 */
	public static function renderAttachmentEmbeds($mediaFiles, $postId = null){
		if(is_array($mediaFiles)){
			$s = "";

			if(count($mediaFiles) > 0){
				if(count($mediaFiles) == 1){
					$s .= '<div>';

					$mediaFile = $mediaFiles[0];

					if($mediaFile->getType() == "IMAGE"){
						$s .= '<div class="border border-primary bg-dark ignoreParentClick mr-2" style="background-image: url(\'' . $mediaFile->getThumbnailURL() . '\'); background-size: cover; ' . (!is_null($postId) ? ' cursor: pointer;" onclick="showMediaModal(\'' . $mediaFile->getId() . '\',' . $postId . ');"' : "\"") . '>
						<img src="' . $mediaFile->getThumbnailURL() . '" style="max-height: 500px; width: 100%; height: 100%; visibility: hidden;"/>
						</div>';
						//$s .= '<div class="rounded border border-primary bg-dark ignoreParentClick mr-2" style="width: 100%; background-image: url(\'' . $mediaFile->getThumbnailURL() . '\'); background-size: cover;' . (!is_null($postId) ? ' cursor: pointer;" onclick="showMediaModal(\'' . $mediaFile->getId() . '\',' . $postId . ');"' : "\"") . '></div>';
					} else if($mediaFile->getType() == "VIDEO"){
						$s .= self::getVideoEmbedCodeFromURL($mediaFile->getURL());
					} else if($mediaFile->getType() == "LINK"){
						// TODO
					}

					$s .= '</div>';
				} else if(count($mediaFiles) == 2){
					$s .= '<div style="height: 237px;">';

					$i = 1;
					foreach($mediaFiles as $mediaFile){
						if($mediaFile->getType() == "IMAGE"){
							$d = $i == 2 ? " border-left-0" : "";

							$s .= '<div class="d-inline-block" style="width: 50%; position: relative; height: 100%;">';
							//$s .= '<img src="' . $mediaFile->getThumbnailURL() . '" class="border border-primary bg-dark ignoreParentClick mr-2" style="width: 100%; height: 100%; ' . (!is_null($postId) ? ' cursor: pointer;" onclick="showMediaModal(\'' . $mediaFile->getId() . '\',' . $postId . ');"' : "\"") . '/>';
							$s .= '<div class="border border-primary' . $d . ' bg-dark ignoreParentClick mr-2" style="background-image: url(\'' . $mediaFile->getThumbnailURL() . '\'); background-size: cover; ' . (!is_null($postId) ? ' cursor: pointer;" onclick="showMediaModal(\'' . $mediaFile->getId() . '\',' . $postId . ');"' : "\"") . '>
							<img src="' . $mediaFile->getThumbnailURL() . '" style="max-height: 500px; width: 100%; height: 100%; visibility: hidden;"/>
							</div>';
							$s .= '</div>';

							$i++;
						}
					}

					$s .= '</div>';
				} else if(count($mediaFiles) == 3){
					$s .= '<div style="height: 237px;">';

					$i = 1;
					foreach($mediaFiles as $mediaFile){
						if($mediaFile->getType() == "IMAGE"){
							if($i == 1){
								$s .= '<div class="d-inline-block" style="width: 66.66666%; position: relative; height: 100%;">';
								//$s .= '<img src="' . $mediaFile->getThumbnailURL() . '" class="border border-primary bg-dark ignoreParentClick mr-2" style="width: 100%; height: 100%; ' . (!is_null($postId) ? ' cursor: pointer;" onclick="showMediaModal(\'' . $mediaFile->getId() . '\',' . $postId . ');"' : "\"") . '/>';
								$s .= '<div class="border border-primary bg-dark ignoreParentClick mr-2" style="max-height: 500px; width: 100%; height: 100%; background-image: url(\'' . $mediaFile->getThumbnailURL() . '\'); background-size: cover; ' . (!is_null($postId) ? ' cursor: pointer;" onclick="showMediaModal(\'' . $mediaFile->getId() . '\',' . $postId . ');"' : "\"") . '></div>';
								$s .= '</div>';
							} else if($i == 2){
								$s .= '<div class="d-inline-block" style="width: calc(100% / 3 - 1px); height: 100%;">';
								//$s .= '<img src="' . $mediaFile->getThumbnailURL() . '" class="border border-primary border-left-0 border-bottom-0 bg-dark ignoreParentClick mr-2" style="width: 100%; height: 50%; ' . (!is_null($postId) ? ' cursor: pointer;" onclick="showMediaModal(\'' . $mediaFile->getId() . '\',' . $postId . ');"' : "\"") . '/>';
								$s .= '<div class="border border-primary border-left-0 bg-dark ignoreParentClick mr-2" style="max-height: 500px; width: 100%; height: 50%; background-image: url(\'' . $mediaFile->getThumbnailURL() . '\'); background-size: cover; ' . (!is_null($postId) ? ' cursor: pointer;" onclick="showMediaModal(\'' . $mediaFile->getId() . '\',' . $postId . ');"' : "\"") . '></div>';
							} else if($i == 3){
								//$s .= '<img src="' . $mediaFile->getThumbnailURL() . '" class="border border-primary border-left-0 bg-dark ignoreParentClick mr-2" style="width: 100%; height: 50%; ' . (!is_null($postId) ? ' cursor: pointer;" onclick="showMediaModal(\'' . $mediaFile->getId() . '\',' . $postId . ');"' : "\"") . '/>';
								$s .= '<div class="border border-primary border-left-0 boder-top-0 bg-dark ignoreParentClick mr-2" style="max-height: 500px; width: 100%; height: 50%; background-image: url(\'' . $mediaFile->getThumbnailURL() . '\'); background-size: cover; ' . (!is_null($postId) ? ' cursor: pointer;" onclick="showMediaModal(\'' . $mediaFile->getId() . '\',' . $postId . ');"' : "\"") . '></div>';
								$s .= '</div>';
							}

							$i++;	
						}
					}

					$s .= '</div>';
				} else if(count($mediaFiles) == 4){
					$s .= '<div style="height: 237px;">';

					$i = 1;
					foreach($mediaFiles as $mediaFile){
						if($mediaFile->getType() == "IMAGE"){
							if($i == 1){
								$s .= '<div class="d-inline-block" style="width: 50%; position: relative; height: 100%;">';
								$s .= '<div class="border border-primary bg-dark ignoreParentClick mr-2" style="max-height: 500px; width: 100%; height: 50%; background-image: url(\'' . $mediaFile->getThumbnailURL() . '\'); background-size: cover; ' . (!is_null($postId) ? ' cursor: pointer;" onclick="showMediaModal(\'' . $mediaFile->getId() . '\',' . $postId . ');"' : "\"") . '></div>';
							} else if($i == 2){
								$s .= '<div class="border border-primary border-top-0 bg-dark ignoreParentClick mr-2" style="max-height: 500px; width: 100%; height: 50%; background-image: url(\'' . $mediaFile->getThumbnailURL() . '\'); background-size: cover; ' . (!is_null($postId) ? ' cursor: pointer;" onclick="showMediaModal(\'' . $mediaFile->getId() . '\',' . $postId . ');"' : "\"") . '></div>';
								$s .= '</div>';
							} else if($i == 3){
								$s .= '<div class="d-inline-block" style="width: 50%; position: relative; height: 100%;">';
								$s .= '<div class="border border-primary border-left-0 bg-dark ignoreParentClick mr-2" style="max-height: 500px; width: 100%; height: 50%; background-image: url(\'' . $mediaFile->getThumbnailURL() . '\'); background-size: cover; ' . (!is_null($postId) ? ' cursor: pointer;" onclick="showMediaModal(\'' . $mediaFile->getId() . '\',' . $postId . ');"' : "\"") . '></div>';
							} else if($i == 4){
								$s .= '<div class="border border-primary border-left-0 border-top-0 bg-dark ignoreParentClick mr-2" style="max-height: 500px; width: 100%; height: 50%; background-image: url(\'' . $mediaFile->getThumbnailURL() . '\'); background-size: cover; ' . (!is_null($postId) ? ' cursor: pointer;" onclick="showMediaModal(\'' . $mediaFile->getId() . '\',' . $postId . ');"' : "\"") . '></div>';
								$s .= '</div>';
							}

							$i++;	
						}
					}

					$s .= '</div>';
				}
			}

			return $s;
		} else {
			return self::renderThumbnails([$mediaFiles]);
		}
	}

	/**
	 * Returns HTML code for a post form with specific parameters
	 * 
	 * @access public
	 * @param int $replyTo The id of the post that is being replied to, null if it's a standalone post
	 * @param string[] $classes An array of css classes attached to the box
	 * @param bool $includeExtraOptions Whether or not to include extra options and tabs for media sharing etc.
	 */
	public static function renderCreatePostForm($classes = null,$includeExtraOptions = true){
		if(!self::isLoggedIn() || is_null(self::getCurrentUser()))
			return "";

		$isReply = !is_null($classes) && is_array($classes) && count($classes) > 0 && in_array("replyForm",$classes);

		$placeholder = !$isReply ? "Post something for your followers!" : "Post your reply";

		$formId = rand(1,getrandmax());

		/*$popoverHtml = "";

		$popoverHtml .= '<div class="addElementGroupContainer">';
		$popoverHtml .= '<div class="addElementGroup">';
		$popoverHtml .= '<a href="#" class="addImage clearUnderline">';
		$popoverHtml .= '<i class="float-left fas fa-images addElementIcon"></i> <div class="addElementText">Picture</div>';
		$popoverHtml .= '</a>';
		$popoverHtml .= '</div>';
		$popoverHtml .= '</div>';

		$popoverHtml .= '<div class="addElementGroupContainer">';
		$popoverHtml .= '<div class="addElementGroup">';
		$popoverHtml .= '<a href="#" class="addImage clearUnderline">';
		$popoverHtml .= '<i class="float-left fas fa-video addElementIcon"></i> <div class="addElementText">Video</div>';
		$popoverHtml .= '</a>';
		$popoverHtml .= '</div>';
		$popoverHtml .= '</div>';
		
		$popoverHtml .= '<div class="addElementGroupContainer">';
		$popoverHtml .= '<div class="addElementGroup">';
		$popoverHtml .= '<a href="#" class="addImage clearUnderline">';
		$popoverHtml .= '<i class="float-left fas fa-volume-up addElementIcon"></i> <div class="addElementText">Audio</div>';
		$popoverHtml .= '</a>';
		$popoverHtml .= '</div>';
		$popoverHtml .= '</div>';

		$popoverHtml .= '<div class="addElementGroupContainer">';
		$popoverHtml .= '<div class="addElementGroup">';
		$popoverHtml .= '<a href="#" class="addImage clearUnderline">';
		$popoverHtml .= '<i class="float-left fas fa-link addElementIcon"></i> <div class="addElementText">Link</div>';
		$popoverHtml .= '</a>';
		$popoverHtml .= '</div>';
		$popoverHtml .= '</div>';*/

		$emojiPopover = "asd";

		$box = "";

		//
		// V1
		//
		/*$box .= '<div class="card postBox' . (!is_null($classes) && is_array($classes) && count($classes) > 0 ? " " . implode(" ",$classes) : "") . '">';
		$box .= '<div class="card-body">';
		$box .= '<textarea id="postField' . $formId . '" class="form-control postField" placeholder="' . $placeholder . '"></textarea>';

		//$box .= '<button type="button" class="btn btn-info btn-sm float-left mt-2 rounded-circle addElement" data-toggle="popover" title="Add an element" data-content="' . self::sanatizeHTMLAttribute($popoverHtml) . '" data-placement="bottom"><i class="fas fa-plus"></i></button>';

		if($includeExtraOptions){
			$faces = ["tired","suprise","smile-wink","smile-beam","sad-tear","sad-cry","meh-rolling-eyes","meh-blank","meh","grin-wink","grin-stars","grin-squint-tears","grin-squint","grin-hearts","grin-beam-sweat","grin-beam","grin-alt","grin","smile","laugh-wink","laugh-squint","laugh-beam","laugh","kiss-wink-heart","kiss-beam","kiss","grin-tongue-wink","grin-tongue-squint","grin-tongue","grin-tears","grimace","frown-open","flushed","angry","dizzy"];

			$linkColor = self::isUsingNightMode() ? "light" : "primary";

			$box .= '<div class="dropzone-previews row ml-2"></div>';

			$box .= '<div class="float-left mt-2">';
			$box .= '<button type="button" class="btn btn-link text-' . $linkColor . ' mb-0 addMediaAttachment" data-toggle="tooltip" title="Add photo"><i class="fas fa-images"></i></button>';
			//$box .= '<button id="emojiPicker' . $formId . '" type="button" class="btn btn-link mb-0 emojiPicker" data-toggle="tooltip" title="Add emoji"><i class="fas fa-' . $faces[rand(0,count($faces)-1)] . '"></i></button>';
			$box .= '</div>';

			$box .= '<input type="hidden" name="attachmentData" value=""/>';
		}

		$box .= '<button type="button" class="btn btn-primary btn-sm float-right mb-0 mt-2 postButton">Post</button>';

		$box .= '<div class="mb-0 mt-3 mr-3 text-right float-right small postCharacterCounter">';
		$box .= POST_CHARACTER_LIMIT . ' characters left';
		$box .= '</div>';

		$box .= '</div>';
		$box .= '</div>';*/

		//
		// V2
		//

		$linkColor = self::isUsingNightMode() ? "light" : "primary";

		$box .= '<div class="postBox card card-sm card-social-post' . (!is_null($classes) && is_array($classes) && count($classes) > 0 ? " " . implode(" ",$classes) : "") . '">';

		if($includeExtraOptions){
			$box .= '<div class="p-0">';

			$box .= '<ul class="list-inline m-0" class="listPostActions">';

			$box .= '<li class="list-inline-item"><button style="font-size: 24px" disabled type="button" class="postFormTextButton clearUnderline btn btn-link text-' . $linkColor . '" data-toggle="tooltip" title="Update status"><i class="fas fa-font"></i></button></li>';
			$box .= '<li class="list-inline-item"><button style="font-size: 24px" type="button" class="postFormVideoButton clearUnderline btn btn-link text-' . $linkColor . '" data-toggle="tooltip" title="Share video"><i class="fas fa-video"></i></button></li>';
			$box .= '<li class="list-inline-item"><button style="font-size: 24px" type="button" class="d-none postFormLinkButton clearUnderline btn btn-link text-' . $linkColor . '" data-toggle="tooltip" title="Share link"><i class="fas fa-link"></i></button></li>';

			$box .= '</ul>';

			$box .= '</div>';
		}

		$box .= '<textarea id="postField' . $formId . '" class="rounded-0 form-control postField" placeholder="' . $placeholder . '"></textarea>';

		if($includeExtraOptions){
			$box .= '<div class="row videoURL my-3 mx-2 d-none">';
			$box .= '<div class="col-12">';
			$box .= '<input type="text" class="form-control" placeholder="Add the URL of a video" style="width: 100%"/>';
			$box .= '</div>';
			$box .= '</div>';

			$box .= '<div class="row linkURL my-3 mx-2 d-none">';
			$box .= '<div class="col-12">';
			$box .= '<input type="text" class="form-control" placeholder="Add an URL to share" style="width: 100%"/>';
			$box .= '</div>';
			$box .= '</div>';
		}

		$box .= '<div class="pb-2 px-2 d-block">';

		if($includeExtraOptions){
			$faces = ["tired","suprise","smile-wink","smile-beam","sad-tear","sad-cry","meh-rolling-eyes","meh-blank","meh","grin-wink","grin-stars","grin-squint-tears","grin-squint","grin-hearts","grin-beam-sweat","grin-beam","grin-alt","grin","smile","laugh-wink","laugh-squint","laugh-beam","laugh","kiss-wink-heart","kiss-beam","kiss","grin-tongue-wink","grin-tongue-squint","grin-tongue","grin-tears","grimace","frown-open","flushed","angry","dizzy"];

			$box .= '<div class="dropzone-previews row ml-2"></div>';

			$box .= '<div class="float-left mt-2">';
			$box .= '<button type="button" class="btn btn-link text-' . $linkColor . ' mb-0 addPhoto" data-toggle="tooltip" title="Add photo"><i class="fas fa-images"></i></button>';
			//$box .= '<button id="emojiPicker' . $formId . '" type="button" class="btn btn-link mb-0 emojiPicker" data-toggle="tooltip" title="Add emoji"><i class="fas fa-' . $faces[rand(0,count($faces)-1)] . '"></i></button>';
			$box .= '</div>';

			$box .= '<input type="hidden" name="attachmentData" value=""/>';
		}

		$box .= '<button type="button" class="btn btn-primary float-right mb-0 mt-2 postButton">Post</button>';

		$box .= '<div class="mb-0 mt-3 mr-3 text-right float-right postCharacterCounter" style="font-size: 15px">';
		$box .= POST_CHARACTER_LIMIT;
		$box .= '</div>';

		$box .= '</div>';

		$box .= '</div>';

		return $box;
	}
}
$ipinfo = IPInformation::getInformationFromIP(Util::getIP());
/*if($ipinfo !== null && (isset($_SERVER["PATH_INFO"]) ? $_SERVER["PATH_INFO"] : strtok($_SERVER["REQUEST_URI"],'?')) !== "/banned/vpn"){
	if(((double)($ipinfo->getVPNCheckResult())) >= 0.90){
		header("Location: /banned/vpn");
		shutdown();
		exit();
	}
}*/