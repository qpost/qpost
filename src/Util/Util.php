<?php
/**
 * Copyright (C) 2018-2020 Gigadrive - All rights reserved.
 * https://gigadrivegroup.com
 * https://qpo.st
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://gnu.org/licenses/>
 */

namespace qpost\Util;

use function array_count_values;
use function array_keys;
use function count;
use function explode;
use function is_array;
use function is_null;
use function preg_match_all;
use function rand;
use function str_replace;
use function strlen;
use function strtoupper;
use function substr;
use function trim;
use const PHP_EOL;

class Util {
	/**
	 * Returns a random string of characters
	 *
	 * @access public
	 * @param int $length The maximum length of the string (the actual length will be something between this number and the half of it)
	 * @return string
	 */
	public static function getRandomString($length = 16): string {
		$characters = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
		$charactersLength = strlen($characters);
		$randomString = "";
		for ($i = 0; $i < rand($length / 2, $length); $i++) {
			$randomString .= $characters[rand(0, $charactersLength - 1)];
		}
		return $randomString;
	}

	/**
	 * Returns a string that fixes exploits like a zero-width space
	 *
	 * @access public
	 * @param string $string
	 * @return string
	 */
	public static function fixString($string): string {
		return preg_replace('/[\x00-\x09\x0B\x0C\x0E-\x1F\x7F]/', '', str_replace("\xE2\x80\x8B", "", str_replace("\xE2\x80\xAE", "", $string)));
	}

	/**
	 * Returns wheter a string or array is empty
	 *
	 * @access public
	 * @param string|array $var
	 * @return bool
	 */
	public static function isEmpty($var): bool {
		if (is_array($var)) {
			return count($var) == 0;
		} else if (is_string($var)) {
			return $var == "" || trim($var) == "" || str_replace(" ", "", str_replace(" ", "", $var)) == "" || strlen($var) == 0;
		} else {
			return is_null($var) || empty($var);
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
	public static function contains($string, $check): bool {
		return strpos($string, $check) !== false;
	}

	/**
	 * Returns a sanatized string that avoids prepending or traling spaces and XSS attacks
	 *
	 * @access public
	 * @param string $string The string to sanatize
	 * @return string
	 */
	public static function sanatizeString($string): string {
		return trim(htmlentities($string));
	}

	/**
	 * Opposite of sanatizeString()
	 *
	 * @access public
	 * @param string $string
	 * @return string
	 */
	public static function desanatizeString($string): string {
		return html_entity_decode($string);
	}

	/**
	 * Returns a sanatzied string to use in HTML attributes (avoids problems with quotations)
	 *
	 * @access public
	 * @param string $string The string to sanatize
	 * @return string
	 */
	public static function sanatizeHTMLAttribute($string): string {
		return trim(htmlspecialchars($string, ENT_QUOTES | ENT_HTML5, "UTF-8"));
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
	public static function limitString($string, $length, $addDots = false) {
		if (strlen($string) > $length)
			$string = substr($string, 0, ($addDots ? $length - 3 : $length)) . ($addDots ? "..." : "");
		return $string;
	}

	/**
	 * Finds all URLs in a string
	 *
	 * @param string $text The string to check
	 * @return string[] The URLs that were found
	 */
	public static function getURLsInString(string $text): array {
		// https://stackoverflow.com/a/23367301/4117923

		$pattern = "~[a-z]+://\S+~";
		if (preg_match_all($pattern, $text, $out)) {
			return $out[0];
		}

		return [];
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
	public static function startsWith(string $string, string $start, bool $ignoreCase = false): bool {
		if (strlen($start) <= strlen($string)) {
			if ($ignoreCase == true) {
				return substr($string, 0, strlen($start)) == $start;
			} else {
				return strtolower(substr($string, 0, strlen($start))) == strtolower($start);
			}
		} else {
			return false;
		}
	}

	/**
	 * Gets whether a string ends with another
	 *
	 * @access public
	 * @param string $string The string in subject
	 * @param string $end The string to be checked whether it is the end of $string
	 * @return bool
	 */
	public static function endsWith(string $string, string $end): bool {
		$length = strlen($end);
		return $length === 0 ? true : (substr($string, -$length) === $end);
	}

	/**
	 * @param string $text
	 * @return array
	 */
	public static function extractHashtags(string $text): array {
		$results = [];

		foreach (explode(" ", str_replace(PHP_EOL, " ", $text)) as $part) {
			if (!self::startsWith($part, "#")) continue;

			// https://stackoverflow.com/a/16609221/4117923
			preg_match_all("/(#\w+)/u", $part, $matches);
			if ($matches && is_array($matches)) {
				$hashtagsArray = array_count_values($matches[0]);
				$hashtags = array_keys($hashtagsArray);

				foreach ($hashtags as $hashtag) {
					if (!self::startsWith($hashtag, "#")) continue;
					$hashtag = substr($hashtag, 1);

					if (strlen($hashtag) > 64) continue;

					if (count($results) > 0) {
						// filter duplicates
						foreach ($results as $result) {
							if (strtoupper($result) === $hashtag) continue;
						}
					}

					$results[] = $hashtag;
				}
			}
		}

		return $results;
	}

	/**
	 * @param int $number
	 * @return bool
	 */
	public static function isEven(int $number): bool {
		return $number % 2 === 0;
	}

	/**
	 * @param int $number
	 * @return bool
	 */
	public static function isOdd(int $number): bool {
		return !self::isEven($number);
	}
}