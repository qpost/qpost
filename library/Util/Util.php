<?php

define("DEVELOPER_MODE",(isset($_SERVER["HTTP_HOST"]) && (explode(":",$_SERVER["HTTP_HOST"])[0] == "localhost" || explode(":",$_SERVER["HTTP_HOST"])[0] == "127.0.0.1")));
define("DEFAULT_TWITTER_IMAGE","https://gigadrivegroup.com/android-chrome-192x192.png");

define("NAV_HOME","NAV_HOME");
define("NAV_PROFILE","NAV_PROFILE");
define("NAV_NOTIFICATIONS","NAV_NOTIFICATIONS");
define("NAV_MESSAGES","NAV_MESSAGES");
define("NAV_ACCOUNT","NAV_ACCOUNT");

define("ACCOUNT_NAV_HOME","ACCOUNT_NAV_HOME");
define("ACCOUNT_NAV_PRIVACY","ACCOUNT_NAV_PRIVACY");
define("ACCOUNT_NAV_LOGOUT","ACCOUNT_NAV_LOGOUT");

define("AD_TYPE_LEADERBOARD","adLeaderboard");
define("AD_TYPE_HORIZONTAL","adLeaderboard");
define("AD_TYPE_BLOCK","adBlock");
define("AD_TYPE_VERTICAL","adVertical");

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

define("NOTIFICATION_TYPE_NEW_FOLLOWER","NEW_FOLLOWER");
define("NOTIFICATION_TYPE_MENTION","MENTION");

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
     * @return string
     */
	public static function renderAd($type,$center = false){
		if($type == AD_TYPE_LEADERBOARD){
			return ($center == true ? "<center>" : "") . '<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
			<ins class="adsbygoogle"
				style="display:block"
				data-ad-client="ca-pub-6156128043207415"
				data-ad-slot="1055807482"
				data-ad-format="auto"></ins>
			<script>
			(adsbygoogle = window.adsbygoogle || []).push({});
			</script>' . ($center == true ? "</center>" : "");
		} else if($type == AD_TYPE_VERTICAL){
			return ($center == true ? "<center>" : "") . '<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
			<ins class="adsbygoogle"
				 style="display:inline-block;width:120px;height:600px"
				 data-ad-client="ca-pub-6156128043207415"
				 data-ad-slot="1788401303"></ins>
			<script>
			(adsbygoogle = window.adsbygoogle || []).push({});
			</script>' . ($center == true ? "</center>" : "");
		} else if($type == AD_TYPE_BLOCK){
			return ($center == true ? "<center>" : "") . '<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
			<ins class="adsbygoogle"
				style="display:inline-block;width:336px;height:280px"
				data-ad-client="ca-pub-6156128043207415"
				data-ad-slot="7069637483"></ins>
			<script>
			(adsbygoogle = window.adsbygoogle || []).push({});
		</script>' . ($center == true ? "</center>" : "");
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
		return isset($_SESSION["id"]) && !is_null($_SESSION["id"]) && !empty($_SESSION["id"]);
	}

	/**
     * Gets the current user's IP address
	 * 
	 * @access public
     * @return string The IP address (might be an IPv6 address)
     */
	public static function getIP(){
		$ip = "undefined";
		
		if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} else {
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
		curl_close($curl);

		if(Util::contains($result,"ERROR: ") == false){
			return ["result" => $result, "url" => sprintf(GIGADRIVE_CDN_UPLOAD_FINAL_URL,$result)];
		} else {
			return null;
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
     * Checks whether a specific email address is available or not
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
				}
			}
		}
		$stmt->close();

		return $b;
	}

	/**
     * Checks whether a specific username is available or not
	 * 
	 * @access public
     * @param string $email
     * @return bool
     */
	public static function isUsernameAvailable($username){
		$b = true;
		$mysqli = Database::Instance()->get();

		$stmt = $mysqli->prepare("SELECT COUNT(`id`) AS `count` FROM `users` WHERE `username` = ?");
		$stmt->bind_param("s",$username);
		if($stmt->execute()){
			$result = $stmt->get_result();
			if($result->num_rows){
				$row = $result->fetch_assoc();
				if($row["count"] > 0){
					$b = false;
				}
			}
		}
		$stmt->close();

		return $b;
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
	 * Converts \n characters to <br/>
	 * 
	 * @access public
	 * @param string $string
	 * @return string
	 */
	public static function convertLineBreaksToHTML($string){
		$s = trim(str_replace("\n","<br/>",$string));

		// TODO: Fix line breaking spamming
		while(self::contains($s,"<br/><br/>")){
			$s = str_replace("<br/><br/>","<br/>",$s);
		}

		return $s;
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
	
		return $paginator;
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
				if($array[$i] == $var){
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
		while(($i = self::indexOf($array,$var)) && ($i != -1)){
			unset($array[$i]);
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
	 */
	public static function followButton($user,$defaultToEdit = false,$classes = null){
		if(is_object($user))
			$user = $user->getId();

		$classString = !is_null($classes) && is_array($classes) && count($classes) > 0 ? " " . implode(" ",$classes) : "";

		if(self::isLoggedIn()){
			$currentUser = Util::getCurrentUser();

			if($defaultToEdit && $currentUser->getId() == $user){
				return '<a href="/edit" class="btn btn-light' . $classString . '">Edit profile</a>';
			} else if($currentUser->getId() != $user){
				if($currentUser->isFollowing($user)){
					return '<button type="button" class="unfollowButton btn btn-danger' . $classString . '" data-user-id="' . $user . '" onclick="toggleFollow(this,' . $user . ');">Unfollow</button>';
				} else {
					return '<button type="button" class="followButton btn btn-primary' . $classString . '" data-user-id="' . $user . '" onclick="toggleFollow(this,' . $user . ');">Follow</button>';
				}
			}
		}
	}
}