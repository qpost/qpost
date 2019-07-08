<?php

namespace qpost\Twig;

use DateTime;
use qpost\Account\Follower;
use qpost\Account\PrivacyLevel;
use qpost\Account\User;
use qpost\Util\Util;
use Twig\Extension\AbstractExtension;
use Twig\Markup;
use Twig\TwigFunction;

class TwigExtension extends AbstractExtension {
	public function getFunctions() {
		return [
			new TwigFunction("insertCSRF", function () {
				if (null !== CSRF_TOKEN) {
					return new Markup('<input type="hidden" name="csrf_token" value="' . Util::sanatizeHTMLAttribute(CSRF_TOKEN) . '"/>', "UTF-8");
				}

				return "";
			}),

			new TwigFunction("sanatizeHTMLAttribute", function ($content) {
				return Util::sanatizeHTMLAttribute($content);
			}),

			new TwigFunction("countryCodes", function () {
				return Util::getCountryCodeArray();
			}),

			new TwigFunction("loggedIn", function () {
				return Util::isLoggedIn();
			}),

			new TwigFunction("currentUser", function () {
				return Util::getCurrentUser();
			}),

			new TwigFunction("verifiedBadge", function ($size = 16) {
				return new Markup(Util::getVerifiedBadge($size), "UTF-8");
			}),

			new TwigFunction("followButton", function ($user, $defaultToEdit = false, $classes = null, $showBlocked = true) {
				return new Markup(Util::followButton($user, $defaultToEdit, $classes, $showBlocked), "UTF-8");
			}),

			new TwigFunction("linkWarning", function ($url) {
				return Util::linkWarning($url);
			}),

			new TwigFunction("convertLinks", function ($string) {
				return Util::convertLinks($string);
			}),

			new TwigFunction("convertMentions", function ($string) {
				return Util::convertMentions($string);
			}),

			new TwigFunction("convertHashtags", function ($string) {
				return Util::convertHashtags($string);
			}),

			new TwigFunction("convertPost", function ($string) {
				return Util::convertPost($string);
			}),

			new TwigFunction("convertLineBreaksToHTML", function ($string) {
				return new Markup(Util::convertLineBreaksToHTML($string), "UTF-8");
			}),

			new TwigFunction("fixString", function ($string) {
				return Util::fixString($string);
			}),

			new TwigFunction("formatNumberShort", function ($number) {
				return Util::formatNumberShort($number);
			}),

			new TwigFunction("createAlert", function ($id, $text, $type = "info", $dismissible = FALSE, $saveDismiss = FALSE) {
				return new Markup(Util::createAlert($id, $text, $type, $dismissible, $saveDismiss), "UTF-8");
			}),

			new TwigFunction("renderCreatePostForm", function ($classes = null, $includeExtraOptions = true) {
				return new Markup(Util::renderCreatePostForm($classes, $includeExtraOptions), "UTF-8");
			}),

			new TwigFunction("maySeeBio", function (User $u) {
				return ($u->getPrivacyLevel() == PrivacyLevel::PUBLIC || (Util::isLoggedIn() && Follower::isFollowing(Util::getCurrentUser(), $u))) && (!is_null($u->getBio()));
			}),

			new TwigFunction("timeago", function ($timestamp) {
				$str = null;
				if ($timestamp instanceof DateTime) {
					$str = $timestamp->getTimestamp();
				} else {
					$str = is_string($timestamp) ? strtotime($timestamp) : $timestamp;
				}

				$timestamp = date("Y", $str) . "-" . date("m", $str) . "-" . date("d", $str) . "T" . date("H", $str) . ":" . date("i", $str) . ":" . date("s", $str) . "Z";

				return new Markup('<time class="timeago" datetime="' . $timestamp . '" title="' . date("d", $str) . "." . date("m", $str) . "." . date("Y", $str) . " " . date("H", $str) . ":" . date("i", $str) . ":" . date("s", $str) . ' UTC">' . $timestamp . '</time>', "UTF-8");
			}),

			new TwigFunction("fixUmlaut", function ($string) {
				return Util::fixUmlaut($string);
			}),

			new TwigFunction("paginate", function ($page, $itemsPerPage, $total, $urlPattern) {
				return new Markup(Util::paginate($page, $itemsPerPage, $total, $urlPattern), "UTF-8");
			})
		];
	}
}