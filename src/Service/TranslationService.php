<?php
/*
 * Copyright (C) 2018-2020 Gigadrive - All rights reserved.
 * https://gigadrivegroup.com
 * https://qpostapp.com
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

namespace qpost\Service;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Intl\Exception\MissingResourceException;
use Symfony\Component\Intl\Locales;
use Symfony\Contracts\Translation\TranslatorInterface;
use function array_key_exists;
use function file_exists;
use function file_get_contents;
use function is_null;
use function json_decode;

class TranslationService {
	/**
	 * @var TranslatorInterface $translator
	 */
	public $translator;
	/**
	 * @var TokenService $tokenService
	 */
	private $tokenService;
	/**
	 * @var RequestStack $requestStack
	 */
	private $requestStack;
	/**
	 * @var LoggerInterface $logger
	 */
	private $logger;

	public function __construct(TokenService $tokenService, RequestStack $requestStack, TranslatorInterface $translator, LoggerInterface $logger) {
		$this->tokenService = $tokenService;
		$this->requestStack = $requestStack;
		$this->translator = $translator;
		$this->logger = $logger;
	}

	public function getCurrentLanguage(): string {
		// First: check current user setting
		$currentToken = $this->tokenService->getCurrentToken();
		if ($currentToken) {
			$user = $currentToken->getUser();

			$lang = $user->getInterfaceLanguage();
			if ($lang && $this->isValidLanguage($lang)) return $lang;
		}

		// Fallback: browser language
		return $this->getCurrentBrowserLanguage();
	}

	public function isValidLanguage(string $code): bool {
		return array_key_exists($code, $this->getAvailableLanguages());
	}

	/**
	 * Gets all currently available languages.
	 * code => Name array
	 * @return array
	 * @author Mehdi Baaboura <mbaaboura@gigadrivegroup.com>
	 */
	public function getAvailableLanguages(): array {
		$languages = [];

		$path = __DIR__ . "/../../translations/locales.json";
		if (!file_exists($path)) return $languages;

		$localeObjects = @json_decode(file_get_contents($path), true);
		if (!$localeObjects) return $languages;

		foreach ($localeObjects as $locale) {
			$code = $locale["code"];
			$name = $locale["name"];
			$localizedName = $locale["localizedName"];

			if ($this->getLanguageProgress($code) < 40) continue;

			if (is_null($localizedName) || $localizedName === "") {
				try {
					$localizedName = Locales::getName($code, $code);
				} catch (MissingResourceException $ignored) {
				}
			}

			$languages[$code] = $name . (($localizedName !== "" && $localizedName !== $name) ? " (" . $localizedName . ")" : "");
		}

		return $languages;
	}

	public function getLanguageProgress(string $code): int {
		if ($code === "en") return 100;

		return 100; // TODO
	}

	public function getCurrentBrowserLanguage(): string {
		return "en"; // TODO
	}
}